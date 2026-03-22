<?php

namespace App\Http\Controllers;

use App\Models\Channel;
use App\Models\ContentPillar;
use App\Models\Post;
use Carbon\Carbon;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class CalendarController extends Controller
{
    public function index(): View
    {
        $companyId = (int) Auth::user()->company_id;

        $channels = Channel::query()
            ->where('company_id', $companyId)
            ->orderBy('name')
            ->get();

        $pillars = ContentPillar::query()
            ->where('company_id', $companyId)
            ->orderBy('name')
            ->get();

        $unscheduledBase = Post::query()
            ->where('company_id', $companyId)
            ->whereNull('scheduled_at')
            ->where('status', '!=', 'published');

        $unscheduledCount = (clone $unscheduledBase)->count();

        $unscheduledPosts = (clone $unscheduledBase)
            ->with('channel')
            ->orderByDesc('updated_at')
            ->limit(75)
            ->get();

        $pillarBalance = $this->pillarBalanceForMonth($companyId, $pillars, Carbon::now());

        return view('calendar.index', [
            'channels' => $channels,
            'pillars' => $pillars,
            'unscheduledPosts' => $unscheduledPosts,
            'unscheduledCount' => $unscheduledCount,
            'pillarBalance' => $pillarBalance,
            'platformOptions' => ChannelController::platformOptions(),
            'platformKeys' => ChannelController::platformKeys(),
        ]);
    }

    public function events(Request $request): JsonResponse
    {
        $companyId = (int) Auth::user()->company_id;

        try {
            $start = Carbon::parse($request->query('start'))->startOfDay();
            $end = Carbon::parse($request->query('end'))->endOfDay();
        } catch (\Throwable) {
            $start = Carbon::now()->startOfMonth()->startOfDay();
            $end = Carbon::now()->endOfMonth()->endOfDay();
        }

        $channelIdRaw = $request->query('channel_id');
        $pillarRaw = $request->query('pillar');
        $statusRaw = $request->query('status');

        $query = Post::query()
            ->where('company_id', $companyId)
            ->where(function ($q) {
                $q->whereNotNull('scheduled_at')->orWhereNotNull('published_at');
            })
            ->with('channel');

        if ($channelIdRaw !== null && $channelIdRaw !== '') {
            $cid = (int) $channelIdRaw;
            if (Channel::query()->where('company_id', $companyId)->whereKey($cid)->exists()) {
                $query->where('channel_id', $cid);
            }
        }

        if (is_string($pillarRaw) && $pillarRaw !== '') {
            $query->where('content_pillar', $pillarRaw);
        }

        $allowedStatus = ['draft', 'in_review', 'approved', 'scheduled', 'published', 'rejected'];
        if (is_string($statusRaw) && $statusRaw !== '' && $statusRaw !== 'all' && in_array($statusRaw, $allowedStatus, true)) {
            $query->where('status', $statusRaw);
        }

        $posts = $query->get();

        $events = [];
        foreach ($posts as $post) {
            $eventStart = $post->scheduled_at ?? $post->published_at;
            if ($eventStart === null) {
                continue;
            }
            if ($eventStart->lt($start) || $eventStart->gt($end)) {
                continue;
            }

            $channel = $post->channel;
            $color = $channel?->color ?? '#5F63F2';

            $rawTitle = $post->title;
            if ($rawTitle === null || $rawTitle === '') {
                $cap = (string) ($post->caption_en ?? '');
                $title = $cap !== ''
                    ? mb_substr($cap, 0, 40).(mb_strlen($cap) > 40 ? '…' : '')
                    : 'Untitled';
            } else {
                $title = mb_strlen($rawTitle) > 40
                    ? mb_substr($rawTitle, 0, 40).'…'
                    : $rawTitle;
            }

            $events[] = [
                'id' => (string) $post->id,
                'title' => $title,
                'start' => $eventStart->toIso8601String(),
                'color' => $color,
                'backgroundColor' => $color,
                'borderColor' => $color,
                'editable' => $post->status !== 'published',
                'classNames' => $post->status === 'published' ? ['fc-event-published'] : [],
                'extendedProps' => [
                    'status' => $post->status,
                    'post_type' => $post->post_type,
                    'channel_name' => $channel?->name ?? '',
                    'pillar' => $post->content_pillar,
                    'platform' => $channel?->platform ?? 'custom',
                    'caption_en' => $post->caption_en,
                    'scheduled_at' => $post->scheduled_at?->toIso8601String(),
                    'published_at' => $post->published_at?->toIso8601String(),
                    'post_id' => $post->id,
                    'title_full' => $post->title,
                ],
            ];
        }

        return response()->json($events);
    }

    public function reschedule(Request $request, Post $post): JsonResponse
    {
        if ($post->company_id !== Auth::user()->company_id) {
            return response()->json(['message' => 'Forbidden'], 403);
        }

        if ($post->status === 'published') {
            return response()->json(['message' => 'Cannot reschedule published posts'], 422);
        }

        $validated = $request->validate([
            'scheduled_at' => ['required', 'date'],
        ]);

        $post->scheduled_at = Carbon::parse($validated['scheduled_at']);
        $post->save();

        return response()->json(['success' => true]);
    }

    /**
     * @param  Collection<int, ContentPillar>  $pillars
     * @return array{total: int, rows: list<array{name: string, color: string, actual: float, target: float, count: int, ok: bool}>, none_count: int}
     */
    private function pillarBalanceForMonth(int $companyId, $pillars, Carbon $month): array
    {
        $monthStart = $month->copy()->startOfMonth()->startOfDay();
        $monthEnd = $month->copy()->endOfMonth()->endOfDay();

        $posts = Post::query()
            ->where('company_id', $companyId)
            ->where(function ($q) {
                $q->whereNotNull('scheduled_at')->orWhereNotNull('published_at');
            })
            ->get();

        $inMonth = $posts->filter(function (Post $post) use ($monthStart, $monthEnd) {
            $d = $post->scheduled_at ?? $post->published_at;

            return $d !== null && $d->between($monthStart, $monthEnd);
        });

        $total = $inMonth->count();

        $rows = [];
        foreach ($pillars as $pillar) {
            $name = $pillar->name;
            $count = $inMonth->filter(fn (Post $p) => $p->content_pillar === $name)->count();
            $actual = $total > 0 ? round($count / $total * 100, 1) : 0.0;
            $target = (float) ($pillar->target_percentage ?? 0);
            $diff = abs($actual - $target);
            $rows[] = [
                'name' => $name,
                'color' => $pillar->color ?? '#94a3b8',
                'actual' => $actual,
                'target' => $target,
                'count' => $count,
                'ok' => $diff <= 5.0,
            ];
        }

        $noneCount = $inMonth->filter(fn (Post $p) => $p->content_pillar === null || $p->content_pillar === '')->count();

        return [
            'total' => $total,
            'rows' => $rows,
            'none_count' => $noneCount,
        ];
    }
}
