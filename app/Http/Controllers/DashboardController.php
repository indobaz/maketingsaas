<?php

namespace App\Http\Controllers;

use App\Models\Channel;
use App\Models\ContentPillar;
use App\Models\FollowerSnapshot;
use App\Models\Post;
use App\Models\PostComment;
use App\Models\Task;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class DashboardController extends Controller
{
    public function index(Request $request)
    {
        $companyId = (int) $request->user()->company_id;

        $postCount = Post::query()->where('company_id', $companyId)->count();
        $channelCount = Channel::query()->where('company_id', $companyId)->count();
        $channelsActive = Channel::query()->where('company_id', $companyId)->where('status', 'active')->count();
        $channelsArchived = Channel::query()->where('company_id', $companyId)->where('status', 'archived')->count();

        $tasksDueToday = Task::query()
            ->where('company_id', $companyId)
            ->whereDate('due_date', today())
            ->count();

        $teamCount = User::query()
            ->where('company_id', $companyId)
            ->where('status', 'active')
            ->count();

        $teamPreview = User::query()
            ->where('company_id', $companyId)
            ->where('status', 'active')
            ->orderByRaw('COALESCE(name, email) asc')
            ->limit(3)
            ->get();

        $thisWeekStart = Carbon::now()->startOfWeek();
        $thisWeekEnd = Carbon::now()->endOfWeek();
        $lastWeekStart = Carbon::now()->subWeek()->startOfWeek();
        $lastWeekEnd = Carbon::now()->subWeek()->endOfWeek();

        $thisWeekPosts = Post::query()
            ->where('company_id', $companyId)
            ->whereBetween('created_at', [$thisWeekStart, $thisWeekEnd])
            ->count();

        $lastWeekPosts = Post::query()
            ->where('company_id', $companyId)
            ->whereBetween('created_at', [$lastWeekStart, $lastWeekEnd])
            ->count();

        $publishedThisMonth = Post::query()
            ->where('company_id', $companyId)
            ->where('status', 'published')
            ->whereNotNull('published_at')
            ->whereBetween('published_at', [Carbon::now()->startOfMonth(), Carbon::now()->endOfMonth()])
            ->count();

        $postsSparklineLast7Days = $this->postsCreatedPerDayLast7Days($companyId);

        $followerGrowth = $this->followerGrowthByChannel($companyId);

        $postsByStatus = [
            'draft' => Post::query()->where('company_id', $companyId)->where('status', 'draft')->count(),
            'in_review' => Post::query()->where('company_id', $companyId)->where('status', 'in_review')->count(),
            'approved' => Post::query()->where('company_id', $companyId)->where('status', 'approved')->count(),
            'scheduled' => Post::query()->where('company_id', $companyId)->where('status', 'scheduled')->count(),
            'published' => Post::query()->where('company_id', $companyId)->where('status', 'published')->count(),
            'rejected' => Post::query()->where('company_id', $companyId)->where('status', 'rejected')->count(),
        ];

        $postsByPillar = $this->postsByPillarForChart($companyId);

        $recentActivity = PostComment::query()
            ->whereHas('post', fn ($q) => $q->where('company_id', $companyId))
            ->with(['user', 'post.channel'])
            ->orderByDesc('created_at')
            ->limit(10)
            ->get();

        $upcomingPostsQuery = Post::query()
            ->where('company_id', $companyId)
            ->whereNotNull('scheduled_at')
            ->where('scheduled_at', '>=', now());

        $upcomingPostsCount = (clone $upcomingPostsQuery)->count();

        $upcomingPosts = (clone $upcomingPostsQuery)
            ->with('channel')
            ->orderBy('scheduled_at')
            ->limit(5)
            ->get();

        return view('dashboard', [
            'postCount' => $postCount,
            'channelCount' => $channelCount,
            'channelsActive' => $channelsActive,
            'channelsArchived' => $channelsArchived,
            'tasksDueToday' => $tasksDueToday,
            'teamCount' => $teamCount,
            'teamPreview' => $teamPreview,
            'thisWeekPosts' => $thisWeekPosts,
            'lastWeekPosts' => $lastWeekPosts,
            'publishedThisMonth' => $publishedThisMonth,
            'postsSparklineLast7Days' => $postsSparklineLast7Days,
            'followerGrowth' => $followerGrowth,
            'postsByStatus' => $postsByStatus,
            'postsByPillar' => $postsByPillar,
            'recentActivity' => $recentActivity,
            'upcomingPosts' => $upcomingPosts,
            'upcomingPostsCount' => $upcomingPostsCount,
        ]);
    }

    /**
     * @return list<int>
     */
    private function postsCreatedPerDayLast7Days(int $companyId): array
    {
        $counts = [];
        for ($i = 6; $i >= 0; $i--) {
            $day = Carbon::now()->subDays($i)->toDateString();
            $counts[] = (int) Post::query()
                ->where('company_id', $companyId)
                ->whereDate('created_at', $day)
                ->count();
        }

        return $counts;
    }

    /**
     * @return list<array{id: int, name: string, series: list<int>}>
     */
    private function followerGrowthByChannel(int $companyId): array
    {
        $channels = Channel::query()
            ->where('company_id', $companyId)
            ->orderBy('name')
            ->get();

        $dayStrings = [];
        for ($i = 6; $i >= 0; $i--) {
            $dayStrings[] = Carbon::now()->subDays($i)->toDateString();
        }

        $result = [];
        foreach ($channels as $channel) {
            $snaps = FollowerSnapshot::query()
                ->where('channel_id', $channel->id)
                ->where('recorded_date', '>=', Carbon::now()->subDays(6)->startOfDay())
                ->orderBy('recorded_date')
                ->get()
                ->keyBy(fn ($s) => $s->recorded_date->format('Y-m-d'));

            $series = [];
            $carry = (int) $channel->followers_count;
            foreach ($dayStrings as $d) {
                if ($snaps->has($d)) {
                    $carry = (int) $snaps->get($d)->follower_count;
                }
                $series[] = $carry;
            }

            $result[] = [
                'id' => $channel->id,
                'name' => $channel->name,
                'series' => $series,
            ];
        }

        return $result;
    }

    /**
     * @return list<array{name: string, count: int, color: string}>
     */
    private function postsByPillarForChart(int $companyId): array
    {
        $pillarColors = ContentPillar::query()
            ->where('company_id', $companyId)
            ->pluck('color', 'name');

        $rows = Post::query()
            ->where('company_id', $companyId)
            ->select('content_pillar', DB::raw('count(*) as aggregate'))
            ->groupBy('content_pillar')
            ->get();

        $out = [];
        foreach ($rows as $row) {
            $name = $row->content_pillar;
            $label = ($name === null || $name === '') ? 'Unassigned' : $name;
            $color = ($name && $pillarColors->has($name))
                ? (string) $pillarColors->get($name)
                : '#94a3b8';
            $out[] = [
                'name' => $label,
                'count' => (int) $row->aggregate,
                'color' => $color,
            ];
        }

        usort($out, fn ($a, $b) => $b['count'] <=> $a['count']);

        return $out;
    }
}
