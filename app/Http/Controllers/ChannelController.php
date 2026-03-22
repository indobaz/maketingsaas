<?php

namespace App\Http\Controllers;

use App\Models\Channel;
use App\Models\FollowerSnapshot;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class ChannelController extends Controller
{
    /** @return array<string, string> value => label with emoji */
    public static function platformOptions(): array
    {
        return [
            'instagram' => '📸 Instagram',
            'youtube' => '🎥 YouTube',
            'linkedin' => '💼 LinkedIn',
            'tiktok' => '🎵 TikTok',
            'facebook' => '👤 Facebook',
            'twitter' => '🐦 Twitter/X',
            'pinterest' => '📌 Pinterest',
            'snapchat' => '👻 Snapchat',
            'whatsapp' => '💬 WhatsApp Business',
            'custom' => '⚙️ Custom',
        ];
    }

    /** @return array<int, string> */
    public static function platformKeys(): array
    {
        return array_keys(self::platformOptions());
    }

    public function index(): View
    {
        $companyId = Auth::user()->company_id;

        $channels = Channel::query()
            ->where('company_id', $companyId)
            ->with('latestFollowerSnapshot')
            ->orderByDesc('created_at')
            ->get();

        return view('channels.index', [
            'channels' => $channels,
            'platformOptions' => self::platformOptions(),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $this->validatedChannelPayload($request);

        Channel::create([
            'company_id' => Auth::user()->company_id,
            'name' => $validated['name'],
            'platform' => $validated['platform'],
            'handle' => $validated['handle'] ?? null,
            'color' => $validated['color'] ?? '#5F63F2',
            'status' => 'active',
            'api_connected' => false,
            'followers_count' => 0,
            'notes' => $validated['notes'] ?? null,
        ]);

        return back()->with('success', 'Channel added successfully');
    }

    public function update(Request $request, Channel $channel): RedirectResponse
    {
        $this->assertChannelInCompany($channel);

        $validated = $this->validatedChannelPayload($request);

        $channel->update([
            'name' => $validated['name'],
            'platform' => $validated['platform'],
            'handle' => $validated['handle'] ?? null,
            'color' => $validated['color'] ?? '#5F63F2',
            'notes' => $validated['notes'] ?? null,
        ]);

        return back()->with('success', 'Channel updated');
    }

    public function destroy(Channel $channel): RedirectResponse
    {
        $this->assertChannelInCompany($channel);

        $user = Auth::user();
        if (! in_array($user->role, ['owner', 'admin'], true)) {
            abort(403);
        }

        $channel->update(['status' => 'archived']);

        return back()->with('success', 'Channel archived');
    }

    public function updateFollowers(Request $request, Channel $channel): RedirectResponse
    {
        $this->assertChannelInCompany($channel);

        $validated = $request->validate([
            'followers_count' => ['required', 'integer', 'min:0'],
        ]);

        $channel->update([
            'followers_count' => $validated['followers_count'],
        ]);

        FollowerSnapshot::create([
            'channel_id' => $channel->id,
            'company_id' => $channel->company_id,
            'follower_count' => $validated['followers_count'],
            'recorded_date' => now()->toDateString(),
        ]);

        return back()->with('success', 'Follower count updated');
    }

    private function assertChannelInCompany(Channel $channel): void
    {
        abort_unless(
            $channel->company_id === Auth::user()->company_id,
            403
        );
    }

    /**
     * @return array{name: string, platform: string, handle: ?string, color: ?string, notes: ?string}
     */
    private function validatedChannelPayload(Request $request): array
    {
        return $request->validate([
            'name' => ['required', 'string', 'max:100'],
            'platform' => ['required', 'string', Rule::in(self::platformKeys())],
            'handle' => ['nullable', 'string', 'max:100'],
            'color' => ['nullable', 'string', 'regex:/^#[0-9A-Fa-f]{6}$/'],
            'notes' => ['nullable', 'string'],
        ]);
    }
}
