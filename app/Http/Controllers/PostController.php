<?php

namespace App\Http\Controllers;

use App\Models\Campaign;
use App\Models\Channel;
use App\Models\ContentPillar;
use App\Models\Post;
use App\Models\Task;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;

class PostController extends Controller
{
    private const POST_TYPES = ['static_image', 'carousel', 'short_video', 'long_video', 'story', 'text_post', 'poll', 'ugc'];

    private const PRIMARY_GOALS = ['awareness', 'lead_gen', 'engagement', 'traffic'];

    public function index(Request $request): View
    {
        $companyId = Auth::user()->company_id;

        $statusKeys = ['draft', 'in_review', 'approved', 'scheduled', 'published', 'rejected'];

        $statusCounts = [
            'all' => Post::query()->where('company_id', $companyId)->count(),
        ];
        foreach ($statusKeys as $key) {
            $statusCounts[$key] = Post::query()
                ->where('company_id', $companyId)
                ->where('status', $key)
                ->count();
        }

        $filter = $request->query('status', 'all');
        if ($filter !== 'all' && ! in_array($filter, $statusKeys, true)) {
            $filter = 'all';
        }

        $search = trim((string) $request->query('search', ''));
        $channelIdRaw = $request->query('channel_id');
        $pillarRaw = $request->query('pillar');
        $postTypeRaw = $request->query('post_type');
        $dateFromRaw = $request->query('date_from');
        $dateToRaw = $request->query('date_to');

        $channelId = $channelIdRaw !== null && $channelIdRaw !== '' ? (int) $channelIdRaw : null;
        $pillar = is_string($pillarRaw) && $pillarRaw !== '' ? $pillarRaw : null;
        $postType = is_string($postTypeRaw) && $postTypeRaw !== '' ? $postTypeRaw : null;
        $dateFrom = is_string($dateFromRaw) && $dateFromRaw !== '' ? $dateFromRaw : null;
        $dateTo = is_string($dateToRaw) && $dateToRaw !== '' ? $dateToRaw : null;

        $filters = [
            'search' => $search,
            'status' => $filter,
            'channel_id' => $channelId ?? '',
            'pillar' => $pillar ?? '',
            'post_type' => $postType ?? '',
            'date_from' => $dateFrom ?? '',
            'date_to' => $dateTo ?? '',
        ];

        $hasActiveFilters = $search !== ''
            || $channelId !== null
            || $pillar !== null
            || $postType !== null
            || $dateFrom !== null
            || $dateTo !== null
            || $filter !== 'all';

        $postsQuery = Post::query()
            ->where('company_id', $companyId)
            ->with(['channel', 'creator']);

        if ($search !== '') {
            $escaped = addcslashes($search, '%_\\');
            $like = '%'.$escaped.'%';
            $postsQuery->where(function ($q) use ($like) {
                $q->where('title', 'like', $like)
                    ->orWhere('caption_en', 'like', $like)
                    ->orWhere('caption_ar', 'like', $like);
            });
        }

        if ($channelId !== null) {
            $validChannel = Channel::query()
                ->where('company_id', $companyId)
                ->whereKey($channelId)
                ->exists();
            if ($validChannel) {
                $postsQuery->where('channel_id', $channelId);
            }
        }

        if ($pillar !== null) {
            $postsQuery->where('content_pillar', $pillar);
        }

        $allowedPostTypes = array_merge(self::POST_TYPES, ['static', 'reel', 'shorts']);
        if ($postType !== null && in_array($postType, $allowedPostTypes, true)) {
            $postsQuery->where('post_type', $postType);
        }

        if ($dateFrom !== null) {
            try {
                $postsQuery->where('created_at', '>=', Carbon::parse($dateFrom)->startOfDay());
            } catch (\Throwable) {
                // ignore invalid date
            }
        }
        if ($dateTo !== null) {
            try {
                $postsQuery->where('created_at', '<=', Carbon::parse($dateTo)->endOfDay());
            } catch (\Throwable) {
                // ignore invalid date
            }
        }

        if ($filter !== 'all') {
            $postsQuery->where('status', $filter);
        }

        $posts = $postsQuery->orderByDesc('created_at')->paginate(20)->withQueryString();

        $channels = Channel::query()
            ->where('company_id', $companyId)
            ->orderBy('name')
            ->get();

        $pillars = ContentPillar::query()
            ->where('company_id', $companyId)
            ->orderBy('name')
            ->get();

        $stats = [
            'total' => $statusCounts['all'],
            'published' => $statusCounts['published'],
            'in_review' => $statusCounts['in_review'],
            'drafts' => $statusCounts['draft'],
        ];

        $retainedFilters = array_filter([
            'search' => $search !== '' ? $search : null,
            'channel_id' => $channelId,
            'pillar' => $pillar,
            'post_type' => $postType,
            'date_from' => $dateFrom,
            'date_to' => $dateTo,
        ], fn ($v) => $v !== null && $v !== '');

        return view('posts.index', [
            'posts' => $posts,
            'channels' => $channels,
            'pillars' => $pillars,
            'filters' => $filters,
            'filter' => $filter,
            'statusCounts' => $statusCounts,
            'stats' => $stats,
            'hasActiveFilters' => $hasActiveFilters,
            'retainedFilters' => $retainedFilters,
            'platformOptions' => ChannelController::platformOptions(),
        ]);
    }

    public static function platformCaptionLimits(): array
    {
        return [
            'instagram' => 2200,
            'youtube' => 5000,
            'linkedin' => 3000,
            'tiktok' => 2200,
            'facebook' => 2200,
            'twitter' => 280,
            'pinterest' => 2200,
            'snapchat' => 2200,
            'whatsapp' => 2200,
            'custom' => 2200,
        ];
    }

    public function create(): View
    {
        $companyId = Auth::user()->company_id;

        $channels = Channel::query()
            ->where('company_id', $companyId)
            ->where('status', 'active')
            ->orderBy('name')
            ->get();

        $pillars = ContentPillar::query()
            ->where('company_id', $companyId)
            ->orderBy('name')
            ->get();

        return view('posts.create', [
            'channels' => $channels,
            'pillars' => $pillars,
            'platformCaptionLimits' => self::platformCaptionLimits(),
            'platformOptions' => ChannelController::platformOptions(),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $user = Auth::user();
        $companyId = (int) $user->company_id;

        $this->applySubmitMode($request);

        $validated = $this->validatedPost($request, $companyId, null);

        Post::create(array_merge(
            $this->payloadFromValidated($validated),
            [
                'company_id' => $companyId,
                'campaign_id' => null,
                'platform_post_id' => null,
                'published_at' => null,
                'created_by' => $user->id,
            ]
        ));

        return redirect()->route('content.index')->with('success', 'Post draft created successfully!');
    }

    public function edit(Post $post): View
    {
        $this->assertPostInCompany($post);

        $post->load(['channel', 'comments.user', 'creator']);

        $companyId = Auth::user()->company_id;

        $channels = Channel::query()
            ->where('company_id', $companyId)
            ->where('status', 'active')
            ->orderBy('name')
            ->get();

        if ($post->channel && ! $channels->contains('id', $post->channel->id)) {
            $channels = $channels->prepend($post->channel)->values();
        }

        $pillars = ContentPillar::query()
            ->where('company_id', $companyId)
            ->orderBy('name')
            ->get();

        $canModify = $this->userCanModifyPost($post);

        if ($post->status !== 'published' && ! $canModify) {
            abort(403);
        }

        $user = Auth::user();

        $linkedTasks = Task::query()
            ->where('post_id', $post->id)
            ->where('company_id', $companyId)
            ->with('assignee')
            ->orderByRaw('due_date IS NULL, due_date asc')
            ->get();

        $postsForTaskLink = Post::query()
            ->where('company_id', $companyId)
            ->where('status', '!=', 'published')
            ->with('channel')
            ->orderByRaw("FIELD(status, 'draft','in_review','approved','scheduled','rejected')")
            ->orderByDesc('updated_at')
            ->get();

        if ($post->status === 'published' && ! $postsForTaskLink->contains('id', $post->id)) {
            $post->loadMissing('channel');
            $postsForTaskLink = $postsForTaskLink->prepend($post)->values();
        }

        $campaignsForTaskLink = Campaign::query()
            ->where('company_id', $companyId)
            ->orderBy('name')
            ->get();

        $teamMembersForTasks = User::query()
            ->where('company_id', $companyId)
            ->orderByRaw('COALESCE(name, email) asc')
            ->get();

        return view('posts.edit', [
            'post' => $post,
            'channels' => $channels,
            'pillars' => $pillars,
            'platformCaptionLimits' => self::platformCaptionLimits(),
            'platformOptions' => ChannelController::platformOptions(),
            'canModify' => $canModify && $post->status !== 'published',
            'isAdminOrOwner' => in_array($user->role, ['admin', 'owner'], true),
            'isCreator' => $post->created_by !== null && (int) $post->created_by === (int) $user->id,
            'linkedTasks' => $linkedTasks,
            'postsForTaskLink' => $postsForTaskLink,
            'campaignsForTaskLink' => $campaignsForTaskLink,
            'teamMembersForTasks' => $teamMembersForTasks,
            'prefillTaskPostId' => $post->id,
        ]);
    }

    public function update(Request $request, Post $post): RedirectResponse
    {
        $this->assertPostInCompany($post);

        if ($post->status === 'published') {
            abort(403);
        }

        if (! $this->userCanModifyPost($post)) {
            abort(403);
        }

        $companyId = (int) Auth::user()->company_id;

        $this->applySubmitMode($request);

        $validated = $this->validatedPost($request, $companyId, $post);

        $post->update($this->payloadFromValidated($validated));

        return redirect()->route('content.index')->with('success', 'Post updated');
    }

    public function destroy(Post $post): RedirectResponse
    {
        $this->assertPostInCompany($post);

        if ($post->status === 'published') {
            abort(403);
        }

        if (! $this->userCanModifyPost($post)) {
            abort(403);
        }

        $post->delete();

        return redirect()->route('content.index')->with('success', 'Post deleted');
    }

    private function applySubmitMode(Request $request): void
    {
        if ($request->filled('submit_mode')) {
            if ($request->input('submit_mode') === 'draft') {
                $request->merge(['status' => 'draft']);
            } elseif ($request->input('submit_mode') === 'review') {
                $request->merge(['status' => 'in_review']);
            } elseif ($request->input('submit_mode') === 'schedule') {
                $request->merge(['status' => 'scheduled']);
            }
        }
    }

    private function validatedPost(Request $request, int $companyId, ?Post $existing): array
    {
        $this->convertEmptyStringsToNull($request, [
            'utm_url', 'media_url', 'video_url', 'thumbnail_url', 'thumbnail_b_url',
            'trending_audio_url', 'pdf_url', 'sticker_link_url', 'original_post_url',
            'primary_goal', 'ugc_permission', 'target_audience', 'content_pillar',
        ]);
        if ($request->input('story_sequence') === '') {
            $request->merge(['story_sequence' => null]);
        }

        $data = $request->validate([
            'channel_id' => [
                'required',
                'integer',
                Rule::exists('channels', 'id')->where(function ($q) use ($companyId, $existing) {
                    $q->where('company_id', $companyId)
                        ->where(function ($q2) use ($existing) {
                            $q2->where('status', 'active');
                            if ($existing) {
                                $q2->orWhere('id', $existing->channel_id);
                            }
                        });
                }),
            ],
            'title' => ['nullable', 'string', 'max:255'],
            'caption_en' => ['nullable', 'string', 'max:5000'],
            'caption_ar' => ['nullable', 'string', 'max:5000'],
            'content_pillar' => [
                'nullable',
                'string',
                'max:100',
                Rule::exists('content_pillars', 'name')->where('company_id', $companyId),
            ],
            'media_url' => [
                'nullable',
                'string',
                'max:500',
                'url',
                Rule::requiredIf(fn () => ($request->input('post_type') ?? 'static_image') === 'static_image'),
            ],
            'brief' => ['nullable', 'string', 'max:2000'],
            'status' => ['required', Rule::in(['draft', 'in_review', 'scheduled'])],
            'scheduled_at' => ['nullable', 'required_if:status,scheduled', 'date', 'after:now'],
            'post_type' => ['nullable', Rule::in(self::POST_TYPES)],
            'primary_goal' => ['nullable', Rule::in(self::PRIMARY_GOALS)],
            'target_audience' => ['nullable', 'string', 'max:500'],
            'utm_url' => ['nullable', 'string', 'max:500', 'url'],
            'video_url' => [
                'nullable',
                'string',
                'max:500',
                'url',
                Rule::requiredIf(fn () => in_array($request->input('post_type'), ['short_video', 'long_video'], true)),
            ],
            'thumbnail_url' => ['nullable', 'string', 'max:500', 'url'],
            'thumbnail_b_url' => ['nullable', 'string', 'max:500', 'url'],
            'hook_line' => ['nullable', 'string', 'max:200'],
            'short_video_hook' => ['nullable', 'string', 'max:200'],
            'static_image_headline' => ['nullable', 'string', 'max:500'],
            'thread_hook_line' => ['nullable', 'string', 'max:150'],
            'script' => ['nullable', 'string', 'max:65535'],
            'on_screen_text' => ['nullable', 'string', 'max:65535'],
            'trending_audio_url' => ['nullable', 'string', 'max:500', 'url'],
            'slide_urls' => ['nullable', 'array', 'max:10'],
            'slide_urls.*' => ['nullable', 'string', 'max:500', 'url'],
            'slide_headlines' => ['nullable', 'array', 'max:10'],
            'slide_headlines.*' => ['nullable', 'string', 'max:200'],
            'slide_copy' => ['nullable', 'array', 'max:10'],
            'slide_copy.*' => ['nullable', 'string', 'max:5000'],
            'alt_texts' => ['nullable', 'array', 'max:10'],
            'alt_texts.*' => ['nullable', 'string', 'max:500'],
            'cta_slide' => ['nullable', 'string', 'max:65535'],
            'pdf_url' => ['nullable', 'string', 'max:500', 'url'],
            'video_title' => ['nullable', 'string', 'max:255'],
            'seo_description' => ['nullable', 'string', 'max:65535'],
            'chapters' => ['nullable', 'string', 'max:65535'],
            'end_screen_plan' => ['nullable', 'string', 'max:65535'],
            'geo_tag' => ['nullable', 'string', 'max:200'],
            'product_tags' => ['nullable', 'string', 'max:65535'],
            'story_asset_urls' => ['nullable', 'array', 'max:10'],
            'story_asset_urls.*' => ['nullable', 'string', 'max:500', 'url'],
            'sticker_type' => ['nullable', Rule::in(['none', 'poll', 'quiz', 'question', 'link'])],
            'sticker_text' => ['nullable', 'string', 'max:65535'],
            'sticker_link_url' => ['nullable', 'string', 'max:500', 'url'],
            'story_sequence' => ['nullable', 'integer', 'min:0', 'max:999999'],
            'thread_posts' => ['nullable', 'array', 'max:10'],
            'thread_posts.*' => ['nullable', 'string', 'max:5000'],
            'poll_question' => [
                'nullable',
                'string',
                'max:500',
                Rule::requiredIf(fn () => $request->input('post_type') === 'poll'),
            ],
            'poll_options' => ['nullable', 'array', 'max:4'],
            'poll_options.*' => ['nullable', 'string', 'max:200'],
            'original_post_url' => [
                'nullable',
                'string',
                'max:500',
                'url',
                Rule::requiredIf(fn () => $request->input('post_type') === 'ugc'),
            ],
            'ugc_creator_handle' => ['nullable', 'string', 'max:200'],
            'ugc_permission' => ['nullable', Rule::in(['asked', 'granted', 'not_required'])],
            'video_instructions' => ['nullable', 'string', 'max:500'],
            'duration' => ['nullable', 'string', 'max:20'],
        ]);

        $data['post_type'] = $data['post_type'] ?? 'static_image';

        $data['hook_line'] = match ($data['post_type']) {
            'short_video' => $data['short_video_hook'] ?? $data['hook_line'] ?? null,
            'static_image' => $data['static_image_headline'] ?? null,
            'text_post' => $data['thread_hook_line'] ?? null,
            default => $data['hook_line'] ?? null,
        };
        unset($data['short_video_hook'], $data['static_image_headline'], $data['thread_hook_line']);

        if (($data['post_type'] ?? '') === 'poll') {
            $opts = $this->normalizedStringArray($data['poll_options'] ?? null);
            if ($opts === null || count($opts) < 2 || count($opts) > 4) {
                throw ValidationException::withMessages([
                    'poll_options' => 'Provide between 2 and 4 poll options.',
                ]);
            }
            $data['poll_options'] = $opts;
        }

        if (($data['post_type'] ?? '') === 'carousel') {
            $packed = $this->normalizedCarouselSlides($data);
            if ($packed['slide_urls'] === null || $packed['slide_urls'] === []) {
                throw ValidationException::withMessages([
                    'slide_urls' => 'Add at least one slide with a URL.',
                ]);
            }
            $data['slide_urls'] = $packed['slide_urls'];
            $data['slide_headlines'] = $packed['slide_headlines'];
            $data['slide_copy'] = $packed['slide_copy'];
            $data['alt_texts'] = $packed['alt_texts'];
        }

        return $data;
    }

    /**
     * @param  array<string, mixed>  $validated
     * @return array<string, mixed>
     */
    private function payloadFromValidated(array $validated): array
    {
        $type = $validated['post_type'] ?? 'static_image';

        $carouselPack = $this->normalizedCarouselSlides($validated);
        $storyAssets = $this->normalizedStringArray($validated['story_asset_urls'] ?? null);
        $threadPosts = $this->normalizedStringArray($validated['thread_posts'] ?? null);
        $pollOptions = $this->normalizedStringArray($validated['poll_options'] ?? null);

        $payload = [
            'channel_id' => $validated['channel_id'],
            'title' => $validated['title'] ?? null,
            'caption_en' => $validated['caption_en'] ?? null,
            'caption_ar' => $validated['caption_ar'] ?? null,
            'content_pillar' => $validated['content_pillar'] ?? null,
            'status' => $validated['status'],
            'brief' => $validated['brief'] ?? null,
            'scheduled_at' => $validated['status'] === 'scheduled' ? $validated['scheduled_at'] : null,
            'post_type' => $type,
            'primary_goal' => $validated['primary_goal'] ?? null,
            'target_audience' => $validated['target_audience'] ?? null,
            'utm_url' => $validated['utm_url'] ?? null,
            'script' => in_array($type, ['short_video', 'long_video'], true) ? ($validated['script'] ?? null) : null,
            'on_screen_text' => $type === 'short_video' ? ($validated['on_screen_text'] ?? null) : null,
            'trending_audio_url' => $type === 'short_video' ? ($validated['trending_audio_url'] ?? null) : null,
            'cta_slide' => $type === 'carousel' ? ($validated['cta_slide'] ?? null) : null,
            'pdf_url' => $type === 'carousel' ? ($validated['pdf_url'] ?? null) : null,
            'video_title' => $type === 'long_video' ? ($validated['video_title'] ?? null) : null,
            'seo_description' => $type === 'long_video' ? ($validated['seo_description'] ?? null) : null,
            'chapters' => $type === 'long_video' ? ($validated['chapters'] ?? null) : null,
            'end_screen_plan' => $type === 'long_video' ? ($validated['end_screen_plan'] ?? null) : null,
            'geo_tag' => $type === 'static_image' ? ($validated['geo_tag'] ?? null) : null,
            'product_tags' => $type === 'static_image' ? ($validated['product_tags'] ?? null) : null,
            'sticker_type' => $type === 'story' ? $this->normalizeStickerType($validated['sticker_type'] ?? null) : null,
            'sticker_text' => $type === 'story' ? ($validated['sticker_text'] ?? null) : null,
            'sticker_link_url' => $type === 'story' ? ($validated['sticker_link_url'] ?? null) : null,
            'story_sequence' => $type === 'story' && array_key_exists('story_sequence', $validated) && $validated['story_sequence'] !== null
                ? (int) $validated['story_sequence']
                : null,
            'poll_question' => $type === 'poll' ? ($validated['poll_question'] ?? null) : null,
            'poll_options' => $type === 'poll' ? $pollOptions : null,
            'original_post_url' => $type === 'ugc' ? ($validated['original_post_url'] ?? null) : null,
            'ugc_creator_handle' => $type === 'ugc' ? ($validated['ugc_creator_handle'] ?? null) : null,
            'ugc_permission' => $type === 'ugc' ? ($validated['ugc_permission'] ?? null) : null,
            'video_instructions' => in_array($type, ['short_video', 'long_video'], true)
                ? ($validated['video_instructions'] ?? null)
                : null,
            'duration' => $type === 'long_video' ? ($validated['duration'] ?? null) : null,
        ];

        $payload['media_url'] = $type === 'static_image'
            ? ($validated['media_url'] ?? null)
            : null;

        $payload['video_url'] = in_array($type, ['short_video', 'long_video'], true)
            ? ($validated['video_url'] ?? null)
            : null;

        $payload['thumbnail_url'] = in_array($type, ['short_video', 'long_video'], true)
            ? ($validated['thumbnail_url'] ?? null)
            : null;
        $payload['thumbnail_b_url'] = $type === 'long_video'
            ? ($validated['thumbnail_b_url'] ?? null)
            : null;
        $payload['hook_line'] = in_array($type, ['short_video', 'static_image', 'text_post'], true)
            ? ($validated['hook_line'] ?? null)
            : null;

        $payload['slide_urls'] = $type === 'carousel' ? $carouselPack['slide_urls'] : null;
        $payload['slide_headlines'] = $type === 'carousel' ? $carouselPack['slide_headlines'] : null;
        $payload['slide_copy'] = $type === 'carousel' ? $carouselPack['slide_copy'] : null;
        $payload['alt_texts'] = $type === 'carousel' ? $carouselPack['alt_texts'] : null;
        $payload['carousel_urls'] = $type === 'carousel' ? $carouselPack['slide_urls'] : null;

        $payload['story_asset_urls'] = $type === 'story' ? $storyAssets : null;
        $payload['thread_posts'] = $type === 'text_post' ? $threadPosts : null;

        return $payload;
    }

    /**
     * @return array{slide_urls: ?array, slide_headlines: ?array, slide_copy: ?array, alt_texts: ?array}
     */
    private function normalizedCarouselSlides(array $validated): array
    {
        $urls = $validated['slide_urls'] ?? [];
        $headlines = $validated['slide_headlines'] ?? [];
        $copy = $validated['slide_copy'] ?? [];
        $alts = $validated['alt_texts'] ?? [];

        if (! is_array($urls)) {
            $urls = [];
        }
        $max = max(count($urls), count($headlines), count($copy), count($alts));

        $outUrls = [];
        $outHeadlines = [];
        $outCopy = [];
        $outAlts = [];

        for ($i = 0; $i < $max; $i++) {
            $u = trim((string) ($urls[$i] ?? ''));
            if ($u === '') {
                continue;
            }
            $outUrls[] = $u;
            $outHeadlines[] = $this->nullIfEmpty(trim((string) ($headlines[$i] ?? '')));
            $outCopy[] = $this->nullIfEmpty(trim((string) ($copy[$i] ?? '')));
            $outAlts[] = $this->nullIfEmpty(trim((string) ($alts[$i] ?? '')));
        }

        if ($outUrls === []) {
            return [
                'slide_urls' => null,
                'slide_headlines' => null,
                'slide_copy' => null,
                'alt_texts' => null,
            ];
        }

        return [
            'slide_urls' => $outUrls,
            'slide_headlines' => $this->trimParallelArray($outHeadlines, count($outUrls)),
            'slide_copy' => $this->trimParallelArray($outCopy, count($outUrls)),
            'alt_texts' => $this->trimParallelArray($outAlts, count($outUrls)),
        ];
    }

    /**
     * @param  array<int, string|null>  $arr
     * @return array<int, string|null>
     */
    private function trimParallelArray(array $arr, int $len): array
    {
        $arr = array_pad(array_slice($arr, 0, $len), $len, null);

        return $arr;
    }

    private function nullIfEmpty(string $s): ?string
    {
        return $s === '' ? null : $s;
    }

    private function convertEmptyStringsToNull(Request $request, array $keys): void
    {
        foreach ($keys as $key) {
            if ($request->input($key) === '') {
                $request->merge([$key => null]);
            }
        }
    }

    private function normalizeStickerType(?string $value): ?string
    {
        if ($value === null || $value === '' || $value === 'none') {
            return null;
        }

        return $value;
    }

    /**
     * @param  array<int, string|null>|null  $items
     * @return array<int, string>|null
     */
    private function normalizedStringArray(?array $items): ?array
    {
        if ($items === null) {
            return null;
        }
        $filtered = array_values(array_filter($items, fn ($s) => is_string($s) && trim($s) !== ''));

        return $filtered === [] ? null : array_map(fn ($s) => trim((string) $s), $filtered);
    }

    private function assertPostInCompany(Post $post): void
    {
        abort_unless($post->company_id === Auth::user()->company_id, 403);
    }

    private function userCanModifyPost(Post $post): bool
    {
        $user = Auth::user();

        if ((int) $post->created_by === (int) $user->id) {
            return true;
        }

        return in_array($user->role, ['admin', 'owner'], true);
    }
}
