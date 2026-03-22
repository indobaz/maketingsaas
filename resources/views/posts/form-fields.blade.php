@php
    /** @var \App\Models\Post|null $post */
    $post = $post ?? null;
    $allowedFormStatus = ['draft', 'in_review', 'scheduled'];
    $statusValue = old('status', $post?->status ?? 'draft');
    if (! in_array($statusValue, $allowedFormStatus, true)) {
        $statusValue = 'draft';
    }
    $scheduledVal = old('scheduled_at', $post?->scheduled_at ? $post->scheduled_at->format('Y-m-d\TH:i') : '');
    $platformKeys = array_keys($platformCaptionLimits);

    $validPostTypes = ['static_image', 'carousel', 'short_video', 'long_video', 'story', 'text_post', 'poll', 'ugc'];
    $rawPostType = old('post_type', $post?->post_type ?? 'static_image');
    $legacyMap = ['static' => 'static_image', 'reel' => 'short_video', 'shorts' => 'short_video'];
    $postType = $legacyMap[$rawPostType] ?? $rawPostType;
    if (! in_array($postType, $validPostTypes, true)) {
        $postType = 'static_image';
    }

    $slideUrls = old('slide_urls', $post?->slide_urls ?? []);
    if (! is_array($slideUrls)) {
        $slideUrls = [];
    }
    $slideCopy = old('slide_copy', $post?->slide_copy ?? []);
    if (! is_array($slideCopy)) {
        $slideCopy = [];
    }
    $altTexts = old('alt_texts', $post?->alt_texts ?? []);
    if (! is_array($altTexts)) {
        $altTexts = [];
    }
    $slideHeadlinesArr = old('slide_headlines', $post?->slide_headlines ?? []);
    if (! is_array($slideHeadlinesArr)) {
        $slideHeadlinesArr = [];
    }
    $carouselSlideCount = max(3, count($slideUrls), count($slideCopy), count($altTexts));
    $slideUrls = array_pad(array_values($slideUrls), $carouselSlideCount, '');
    $slideCopy = array_pad(array_values($slideCopy), $carouselSlideCount, '');
    $altTexts = array_pad(array_values($altTexts), $carouselSlideCount, '');
    $carouselHook = old('slide_headlines.0', $slideHeadlinesArr[0] ?? '');

    $storyAssets = old('story_asset_urls', $post?->story_asset_urls ?? []);
    if (! is_array($storyAssets)) {
        $storyAssets = [];
    }
    if (count($storyAssets) === 0) {
        $storyAssets = [''];
    }

    $threadPosts = old('thread_posts', $post?->thread_posts ?? []);
    if (! is_array($threadPosts)) {
        $threadPosts = [];
    }
    if (count($threadPosts) === 0) {
        $threadPosts = [''];
    }

    $pollOptions = old('poll_options', $post?->poll_options ?? []);
    if (! is_array($pollOptions)) {
        $pollOptions = [];
    }
    if (count($pollOptions) < 2) {
        $pollOptions = array_pad($pollOptions, 2, '');
    }

    $shortVideoHook = old('short_video_hook', in_array($post?->post_type, ['short_video', 'reel', 'shorts'], true) ? ($post?->hook_line ?? '') : '');
    $staticImageHeadline = old('static_image_headline', in_array($post?->post_type, ['static_image', 'static'], true) ? ($post?->hook_line ?? '') : '');
    $threadHookLine = old('thread_hook_line', ($post?->post_type === 'text_post') ? ($post?->hook_line ?? '') : '');

    $stickerLinkVal = old('sticker_link_url', $post?->sticker_link_url ?? $post?->link_sticker_url ?? '');
    $stickerTypeVal = old('sticker_type', $post?->sticker_type);
    if ($stickerTypeVal === null || $stickerTypeVal === '') {
        $stickerTypeVal = 'none';
    }
@endphp

<style>
    [data-post-type-panel] {
        opacity: 0;
        transition: opacity 200ms ease;
    }
    [data-post-type-panel].pt-visible {
        opacity: 1;
    }
    [data-post-type-panel][hidden] {
        display: none !important;
    }
</style>

<div class="row g-4">
    <div class="col-lg-7">
        {{-- Card 1 — Channel & Distribution --}}
        <div class="card mb-3">
            <div class="card-body">
                <h5 class="card-title mb-3">Channel &amp; distribution</h5>
                <div class="mb-3">
                    <label for="channel_id" class="form-label">Channel <span class="text-danger">*</span></label>
                    <div class="d-flex align-items-center gap-2 flex-wrap">
                        <div id="channel-logo-preview" class="flex-shrink-0" style="width: 40px; height: 40px; border-radius: 10px; overflow: hidden; line-height: 0; background: #f4f4f5;"></div>
                        <select name="channel_id" id="channel_id" class="form-select flex-grow-1 @error('channel_id') is-invalid @enderror" required style="min-width: 200px;">
                            <option value="" disabled {{ old('channel_id', $post?->channel_id) ? '' : 'selected' }}>Select channel</option>
                            @foreach($channels as $ch)
                                <option value="{{ $ch->id }}"
                                        data-platform="{{ $ch->platform }}"
                                        @selected((int) old('channel_id', $post?->channel_id) === (int) $ch->id)>
                                    {{ $platformOptions[$ch->platform] ?? $ch->platform }} — {{ $ch->name }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                    @error('channel_id')
                        <div class="invalid-feedback d-block">{{ $message }}</div>
                    @enderror
                </div>
                <div class="mb-3">
                    <label for="post_type" class="form-label">Post type</label>
                    <select name="post_type" id="post_type" class="form-select @error('post_type') is-invalid @enderror">
                        <option value="static_image" @selected($postType === 'static_image')>📷 Static Image</option>
                        <option value="carousel" @selected($postType === 'carousel')>🎠 Carousel</option>
                        <option value="short_video" @selected($postType === 'short_video')>🎬 Short-Form Video / Reel</option>
                        <option value="long_video" @selected($postType === 'long_video')>🎥 Long-Form Video</option>
                        <option value="story" @selected($postType === 'story')>💫 Story</option>
                        <option value="text_post" @selected($postType === 'text_post')>💬 Text / Thread Post</option>
                        <option value="poll" @selected($postType === 'poll')>📊 Poll / Interactive</option>
                        <option value="ugc" @selected($postType === 'ugc')>🔄 UGC / Repost</option>
                    </select>
                    @error('post_type')
                        <div class="invalid-feedback d-block">{{ $message }}</div>
                    @enderror
                </div>
                <div class="mb-3">
                    <label for="content_pillar" class="form-label">Content pillar</label>
                    <div class="d-flex align-items-center gap-2">
                        <span id="pillar-color-dot" class="rounded-circle flex-shrink-0 border" style="width: 14px; height: 14px; background: #e5e7eb; display: none;"></span>
                        <select name="content_pillar" id="content_pillar" class="form-select @error('content_pillar') is-invalid @enderror">
                            <option value="" @selected(old('content_pillar', $post?->content_pillar) === null || old('content_pillar', $post?->content_pillar) === '')>No pillar selected</option>
                            @foreach($pillars as $pillar)
                                <option value="{{ $pillar->name }}"
                                        data-color="{{ $pillar->color }}"
                                        @selected(old('content_pillar', $post?->content_pillar) === $pillar->name)>
                                    {{ $pillar->name }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                    @error('content_pillar')
                        <div class="invalid-feedback d-block">{{ $message }}</div>
                    @enderror
                </div>
                <div class="mb-3">
                    <label for="primary_goal" class="form-label">Primary goal</label>
                    <select name="primary_goal" id="primary_goal" class="form-select @error('primary_goal') is-invalid @enderror">
                        <option value="" @selected(old('primary_goal', $post?->primary_goal) === null || old('primary_goal', $post?->primary_goal) === '')>—</option>
                        <option value="awareness" @selected(old('primary_goal', $post?->primary_goal) === 'awareness')>🎯 Brand Awareness</option>
                        <option value="lead_gen" @selected(old('primary_goal', $post?->primary_goal) === 'lead_gen')>🎯 Lead Generation</option>
                        <option value="engagement" @selected(old('primary_goal', $post?->primary_goal) === 'engagement')>💬 Engagement</option>
                        <option value="traffic" @selected(old('primary_goal', $post?->primary_goal) === 'traffic')>🌐 Drive Traffic</option>
                    </select>
                    @error('primary_goal')
                        <div class="invalid-feedback d-block">{{ $message }}</div>
                    @enderror
                </div>
                <div class="mb-3">
                    <label for="target_audience" class="form-label">Target audience</label>
                    <input type="text" name="target_audience" id="target_audience" value="{{ old('target_audience', $post?->target_audience) }}" maxlength="500"
                           class="form-control @error('target_audience') is-invalid @enderror" placeholder="e.g. UAE homeowners, 25-45">
                    @error('target_audience')
                        <div class="invalid-feedback d-block">{{ $message }}</div>
                    @enderror
                </div>
                <div class="mb-0">
                    <label for="status" class="form-label">Status</label>
                    <select name="status" id="post-status" class="form-select @error('status') is-invalid @enderror" required>
                        <option value="draft" @selected($statusValue === 'draft')>Draft</option>
                        <option value="in_review" @selected($statusValue === 'in_review')>Submit for review</option>
                        <option value="scheduled" @selected($statusValue === 'scheduled')>Schedule</option>
                    </select>
                    @error('status')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>
            </div>
        </div>

        {{-- Card 2 — Caption & Copy --}}
        <div class="card mb-3">
            <div class="card-body">
                <h5 class="card-title mb-3">Caption &amp; copy</h5>
                <div class="mb-3">
                    <label for="title" class="form-label">Title</label>
                    <input type="text" name="title" id="title" value="{{ old('title', $post?->title) }}" maxlength="255"
                           class="form-control @error('title') is-invalid @enderror" placeholder="Post title or reference">
                    @error('title')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>
                <div class="mb-3">
                    <label for="caption_en" class="form-label">Caption (English)</label>
                    <textarea name="caption_en" id="caption_en" rows="5" maxlength="5000"
                              class="form-control @error('caption_en') is-invalid @enderror">{{ old('caption_en', $post?->caption_en) }}</textarea>
                    <div class="d-flex justify-content-between small text-muted mt-1">
                        <span></span>
                        <span><span id="caption-en-count">0</span> / 5000</span>
                    </div>
                    @error('caption_en')
                        <div class="invalid-feedback d-block">{{ $message }}</div>
                    @enderror
                </div>
                <div class="mb-0">
                    <label for="caption_ar" class="form-label">Caption (Arabic) — RTL</label>
                    <textarea name="caption_ar" id="caption_ar" rows="5" maxlength="5000" dir="rtl"
                              class="form-control @error('caption_ar') is-invalid @enderror">{{ old('caption_ar', $post?->caption_ar) }}</textarea>
                    <div class="d-flex justify-content-between small text-muted mt-1">
                        <span></span>
                        <span><span id="caption-ar-count">0</span> / 5000</span>
                    </div>
                    @error('caption_ar')
                        <div class="invalid-feedback d-block">{{ $message }}</div>
                    @enderror
                </div>
            </div>
        </div>

        {{-- Type-specific sections --}}
        <div id="post-type-panels">
            {{-- A — Short-Form Video --}}
            <div @class(['card', 'mb-3', 'pt-visible' => $postType === 'short_video']) data-post-type-panel="short_video" @if($postType !== 'short_video') hidden @endif>
                <div class="card-body">
                    <h5 class="card-title mb-3">Short-form video / Reel</h5>
                    <div class="mb-3">
                        <label for="sv_video_url" class="form-label">Video URL <span class="text-danger">*</span></label>
                        <input type="url" name="video_url" id="sv_video_url" value="{{ old('video_url', $post?->video_url) }}" maxlength="500"
                               class="form-control @error('video_url') is-invalid @enderror" placeholder="Link to video file">
                        @error('video_url')
                            <div class="invalid-feedback d-block">{{ $message }}</div>
                        @enderror
                    </div>
                    <div class="mb-3">
                        <label for="sv_thumbnail_url" class="form-label">Cover / thumbnail URL</label>
                        <input type="url" name="thumbnail_url" id="sv_thumbnail_url" value="{{ old('thumbnail_url', $post?->thumbnail_url) }}" maxlength="500"
                               class="form-control @error('thumbnail_url') is-invalid @enderror" placeholder="https://...">
                        @error('thumbnail_url')
                            <div class="invalid-feedback d-block">{{ $message }}</div>
                        @enderror
                    </div>
                    <div class="mb-3">
                        <label for="short_video_hook" class="form-label">Hook — first 3 seconds</label>
                        <input type="text" name="short_video_hook" id="short_video_hook" value="{{ $shortVideoHook }}" maxlength="200"
                               class="form-control @error('short_video_hook') is-invalid @enderror" placeholder="What stops the scroll? e.g. 'Stop doing this...'">
                        <div class="text-end small text-muted mt-1"><span id="short-video-hook-count">0</span> / 200</div>
                        @error('short_video_hook')
                            <div class="invalid-feedback d-block">{{ $message }}</div>
                        @enderror
                    </div>
                    <div class="mb-3">
                        <label for="sv_script" class="form-label">Script / voiceover</label>
                        <textarea name="script" id="sv_script" rows="4" class="form-control @error('script') is-invalid @enderror" placeholder="Full script or voiceover notes...">{{ old('script', $post?->script) }}</textarea>
                        @error('script')
                            <div class="invalid-feedback d-block">{{ $message }}</div>
                        @enderror
                    </div>
                    <div class="mb-3">
                        <label for="on_screen_text" class="form-label">On-screen text overlays</label>
                        <textarea name="on_screen_text" id="on_screen_text" rows="3" class="form-control @error('on_screen_text') is-invalid @enderror" placeholder="Text overlays list — check safe zones">{{ old('on_screen_text', $post?->on_screen_text) }}</textarea>
                        @error('on_screen_text')
                            <div class="invalid-feedback d-block">{{ $message }}</div>
                        @enderror
                    </div>
                    <div class="mb-3">
                        <label for="trending_audio_url" class="form-label">Trending audio link</label>
                        <input type="url" name="trending_audio_url" id="trending_audio_url" value="{{ old('trending_audio_url', $post?->trending_audio_url) }}" maxlength="500"
                               class="form-control @error('trending_audio_url') is-invalid @enderror" placeholder="Link to audio/song reference">
                        @error('trending_audio_url')
                            <div class="invalid-feedback d-block">{{ $message }}</div>
                        @enderror
                    </div>
                    <div class="mb-0">
                        <label for="video_instructions" class="form-label">Production notes <span class="text-muted fw-normal">(optional)</span></label>
                        <textarea name="video_instructions" id="video_instructions" rows="2" maxlength="500" class="form-control @error('video_instructions') is-invalid @enderror">{{ old('video_instructions', $post?->video_instructions) }}</textarea>
                        @error('video_instructions')
                            <div class="invalid-feedback d-block">{{ $message }}</div>
                        @enderror
                    </div>
                </div>
            </div>

            {{-- B — Carousel --}}
            <div @class(['card', 'mb-3', 'pt-visible' => $postType === 'carousel']) data-post-type-panel="carousel" @if($postType !== 'carousel') hidden @endif>
                <div class="card-body">
                    <h5 class="card-title mb-3">Carousel</h5>
                    <p class="small text-muted mb-2"><span id="carousel-slide-count">{{ $carouselSlideCount }}</span> slide(s)</p>
                    <div class="mb-3">
                        <label for="slide_headlines_0" class="form-label">Slide 1 headline hook</label>
                        <input type="text" name="slide_headlines[0]" id="slide_headlines_0" value="{{ $carouselHook }}" maxlength="200"
                               class="form-control @error('slide_headlines.0') is-invalid @enderror" placeholder="Strongest 'promise' — what do they get?">
                        <div class="text-end small text-muted mt-1"><span id="carousel-hook-count">0</span> / 200</div>
                        @error('slide_headlines.0')
                            <div class="invalid-feedback d-block">{{ $message }}</div>
                        @enderror
                    </div>
                    <label class="form-label">Slides</label>
                    <div id="carousel-slides-container">
                        @foreach($slideUrls as $i => $url)
                            <div class="carousel-slide-row border rounded p-2 mb-2" data-slide-index="{{ $i }}">
                                <div class="small text-muted mb-1 fw-semibold">Slide {{ $i + 1 }}</div>
                                <div class="mb-2">
                                    <label class="form-label small mb-0">Image / video URL</label>
                                    <input type="url" name="slide_urls[]" value="{{ $url }}" class="form-control carousel-slide-url" maxlength="500" placeholder="Slide {{ $i + 1 }} URL">
                                </div>
                                <div class="mb-2">
                                    <label class="form-label small mb-0">Slide copy</label>
                                    <textarea name="slide_copy[]" rows="2" class="form-control" placeholder="Copy for slide {{ $i + 1 }}...">{{ $slideCopy[$i] ?? '' }}</textarea>
                                </div>
                                <div class="d-flex gap-2 align-items-end">
                                    <div class="flex-grow-1">
                                        <label class="form-label small mb-0">Alt text</label>
                                        <input type="text" name="alt_texts[]" value="{{ $altTexts[$i] ?? '' }}" class="form-control" maxlength="500" placeholder="Describe image for accessibility">
                                    </div>
                                    @if($i > 0)
                                        <button type="button" class="btn btn-sm btn-outline-secondary carousel-remove-slide mb-1" aria-label="Remove slide">×</button>
                                    @else
                                        <span class="carousel-remove-placeholder mb-1" style="width: 38px; flex-shrink: 0;"></span>
                                    @endif
                                </div>
                            </div>
                        @endforeach
                    </div>
                    <button type="button" id="carousel-add-slide" class="btn btn-sm btn-outline-secondary mb-3">Add slide</button>
                    <div class="mb-3">
                        <label for="cta_slide" class="form-label">Final slide CTA</label>
                        <input type="text" name="cta_slide" id="cta_slide" value="{{ old('cta_slide', $post?->cta_slide) }}" class="form-control @error('cta_slide') is-invalid @enderror" placeholder="What's the next step? e.g. 'DM us NOW'">
                        @error('cta_slide')
                            <div class="invalid-feedback d-block">{{ $message }}</div>
                        @enderror
                    </div>
                    <div class="mb-0">
                        <label for="pdf_url" class="form-label">LinkedIn PDF URL</label>
                        <input type="url" name="pdf_url" id="pdf_url" value="{{ old('pdf_url', $post?->pdf_url) }}" maxlength="500" class="form-control @error('pdf_url') is-invalid @enderror" placeholder="For LinkedIn Document posts — PDF link">
                        @error('pdf_url')
                            <div class="invalid-feedback d-block">{{ $message }}</div>
                        @enderror
                    </div>
                    @error('slide_urls')
                        <div class="text-danger small mt-2">{{ $message }}</div>
                    @enderror
                </div>
            </div>

            {{-- C — Long-form video --}}
            <div @class(['card', 'mb-3', 'pt-visible' => $postType === 'long_video']) data-post-type-panel="long_video" @if($postType !== 'long_video') hidden @endif>
                <div class="card-body">
                    <h5 class="card-title mb-3">Long-form video</h5>
                    <div class="mb-3">
                        <label for="lv_video_url" class="form-label">Video URL <span class="text-danger">*</span></label>
                        <input type="url" name="video_url" id="lv_video_url" value="{{ old('video_url', $post?->video_url) }}" maxlength="500"
                               class="form-control @error('video_url') is-invalid @enderror" placeholder="Link to video file">
                        @error('video_url')
                            <div class="invalid-feedback d-block">{{ $message }}</div>
                        @enderror
                    </div>
                    <div class="mb-3">
                        <label for="video_title" class="form-label">Video title — SEO</label>
                        <input type="text" name="video_title" id="video_title" value="{{ old('video_title', $post?->video_title) }}" maxlength="255"
                               class="form-control @error('video_title') is-invalid @enderror" placeholder="Include target keywords">
                        @error('video_title')
                            <div class="invalid-feedback d-block">{{ $message }}</div>
                        @enderror
                    </div>
                    <div class="mb-3">
                        <label for="lv_thumbnail_a" class="form-label">Thumbnail A URL</label>
                        <input type="url" name="thumbnail_url" id="lv_thumbnail_a" value="{{ old('thumbnail_url', $post?->thumbnail_url) }}" maxlength="500" class="form-control @error('thumbnail_url') is-invalid @enderror" placeholder="https://...">
                        @error('thumbnail_url')
                            <div class="invalid-feedback d-block">{{ $message }}</div>
                        @enderror
                    </div>
                    <div class="mb-3">
                        <label for="thumbnail_b_url" class="form-label">Thumbnail B URL</label>
                        <input type="url" name="thumbnail_b_url" id="thumbnail_b_url" value="{{ old('thumbnail_b_url', $post?->thumbnail_b_url) }}" maxlength="500"
                               class="form-control @error('thumbnail_b_url') is-invalid @enderror" placeholder="A/B test second option">
                        @error('thumbnail_b_url')
                            <div class="invalid-feedback d-block">{{ $message }}</div>
                        @enderror
                    </div>
                    <div class="mb-3">
                        <label for="lv_script" class="form-label">Full script / transcript</label>
                        <textarea name="script" id="lv_script" rows="4" class="form-control @error('script') is-invalid @enderror" placeholder="Full video script for compliance review...">{{ old('script', $post?->script) }}</textarea>
                        @error('script')
                            <div class="invalid-feedback d-block">{{ $message }}</div>
                        @enderror
                    </div>
                    <div class="mb-3">
                        <label for="chapters" class="form-label">Timestamps / chapters</label>
                        <textarea name="chapters" id="chapters" rows="3" class="form-control @error('chapters') is-invalid @enderror" placeholder="0:00 Intro&#10;1:30 Main Topic&#10;5:00 Conclusion">{{ old('chapters', $post?->chapters) }}</textarea>
                        @error('chapters')
                            <div class="invalid-feedback d-block">{{ $message }}</div>
                        @enderror
                    </div>
                    <div class="mb-3">
                        <label for="seo_description" class="form-label">SEO description</label>
                        <textarea name="seo_description" id="seo_description" rows="3" class="form-control @error('seo_description') is-invalid @enderror" placeholder="First 2 lines are critical for search...">{{ old('seo_description', $post?->seo_description) }}</textarea>
                        @error('seo_description')
                            <div class="invalid-feedback d-block">{{ $message }}</div>
                        @enderror
                    </div>
                    <div class="mb-3">
                        <label for="end_screen_plan" class="form-label">End screen / cards plan</label>
                        <input type="text" name="end_screen_plan" id="end_screen_plan" value="{{ old('end_screen_plan', $post?->end_screen_plan) }}" class="form-control @error('end_screen_plan') is-invalid @enderror" placeholder="e.g. 'Link to playlist X + Subscribe CTA'">
                        @error('end_screen_plan')
                            <div class="invalid-feedback d-block">{{ $message }}</div>
                        @enderror
                    </div>
                    <div class="mb-0">
                        <label for="duration" class="form-label">Duration <span class="text-muted fw-normal">(optional)</span></label>
                        <input type="text" name="duration" id="duration" value="{{ old('duration', $post?->duration) }}" maxlength="20" class="form-control @error('duration') is-invalid @enderror" placeholder="e.g. 12:34">
                        @error('duration')
                            <div class="invalid-feedback d-block">{{ $message }}</div>
                        @enderror
                    </div>
                </div>
            </div>

            {{-- D — Static image --}}
            <div @class(['card', 'mb-3', 'pt-visible' => $postType === 'static_image']) data-post-type-panel="static_image" @if($postType !== 'static_image') hidden @endif>
                <div class="card-body">
                    <h5 class="card-title mb-3">Static image</h5>
                    <div class="mb-3">
                        <label for="media_url" class="form-label">Image URL <span class="text-danger">*</span></label>
                        <input type="url" name="media_url" id="media_url" value="{{ old('media_url', $post?->media_url) }}" maxlength="500"
                               class="form-control @error('media_url') is-invalid @enderror" placeholder="https://...">
                        @error('media_url')
                            <div class="invalid-feedback d-block">{{ $message }}</div>
                        @enderror
                    </div>
                    <div class="mb-3">
                        <label for="static_image_headline" class="form-label">Headline on image</label>
                        <input type="text" name="static_image_headline" id="static_image_headline" value="{{ $staticImageHeadline }}" maxlength="500"
                               class="form-control @error('static_image_headline') is-invalid @enderror" placeholder="Main text on the graphic">
                        @error('static_image_headline')
                            <div class="invalid-feedback d-block">{{ $message }}</div>
                        @enderror
                    </div>
                    <div class="mb-3">
                        <label for="geo_tag" class="form-label">Geo-tag location</label>
                        <input type="text" name="geo_tag" id="geo_tag" value="{{ old('geo_tag', $post?->geo_tag) }}" maxlength="200" class="form-control @error('geo_tag') is-invalid @enderror" placeholder="e.g. Dubai, UAE">
                        @error('geo_tag')
                            <div class="invalid-feedback d-block">{{ $message }}</div>
                        @enderror
                    </div>
                    <div class="mb-0">
                        <label for="product_tags" class="form-label">Product tags / links</label>
                        <textarea name="product_tags" id="product_tags" rows="2" class="form-control @error('product_tags') is-invalid @enderror" placeholder="Tag product names or paste shopping links">{{ old('product_tags', $post?->product_tags) }}</textarea>
                        @error('product_tags')
                            <div class="invalid-feedback d-block">{{ $message }}</div>
                        @enderror
                    </div>
                </div>
            </div>

            {{-- E — Story --}}
            <div @class(['card', 'mb-3', 'pt-visible' => $postType === 'story']) data-post-type-panel="story" @if($postType !== 'story') hidden @endif>
                <div class="card-body">
                    <h5 class="card-title mb-3">Story</h5>
                    <label class="form-label">Story assets</label>
                    <div id="story-assets-container" class="mb-2">
                        @foreach($storyAssets as $i => $assetUrl)
                            <div class="story-asset-row d-flex gap-2 mb-2 align-items-center">
                                <input type="url" name="story_asset_urls[]" value="{{ $assetUrl }}" class="form-control story-asset-url" maxlength="500" placeholder="Asset URL {{ $i + 1 }}">
                                @if($i > 0)
                                    <button type="button" class="btn btn-sm btn-outline-secondary story-remove-asset" aria-label="Remove">×</button>
                                @else
                                    <span class="story-remove-placeholder" style="width: 38px; flex-shrink: 0;"></span>
                                @endif
                            </div>
                        @endforeach
                    </div>
                    <button type="button" id="story-add-asset" class="btn btn-sm btn-outline-secondary mb-3">Add asset</button>
                    <div class="mb-3">
                        <label for="sticker_type" class="form-label">Interaction sticker type</label>
                        <select name="sticker_type" id="sticker_type" class="form-select @error('sticker_type') is-invalid @enderror">
                            <option value="none" @selected($stickerTypeVal === 'none')>None</option>
                            <option value="poll" @selected($stickerTypeVal === 'poll')>Poll</option>
                            <option value="quiz" @selected($stickerTypeVal === 'quiz')>Quiz</option>
                            <option value="question" @selected($stickerTypeVal === 'question')>Question box</option>
                            <option value="link" @selected($stickerTypeVal === 'link')>Link sticker</option>
                        </select>
                        @error('sticker_type')
                            <div class="invalid-feedback d-block">{{ $message }}</div>
                        @enderror
                    </div>
                    <div class="mb-3" id="sticker-text-wrap">
                        <label for="sticker_text" class="form-label">Sticker text / question</label>
                        <input type="text" name="sticker_text" id="sticker_text" value="{{ old('sticker_text', $post?->sticker_text) }}" class="form-control @error('sticker_text') is-invalid @enderror" placeholder="What question goes on the sticker?">
                        @error('sticker_text')
                            <div class="invalid-feedback d-block">{{ $message }}</div>
                        @enderror
                    </div>
                    <div class="mb-3" id="sticker-link-wrap" hidden>
                        <label for="sticker_link_url" class="form-label">Sticker link URL</label>
                        <input type="url" name="sticker_link_url" id="sticker_link_url" value="{{ $stickerLinkVal }}" maxlength="500" class="form-control @error('sticker_link_url') is-invalid @enderror" placeholder="https://...">
                        @error('sticker_link_url')
                            <div class="invalid-feedback d-block">{{ $message }}</div>
                        @enderror
                    </div>
                    <div class="mb-0">
                        <label for="story_sequence" class="form-label">Sequence order</label>
                        <input type="number" name="story_sequence" id="story_sequence" value="{{ old('story_sequence', $post?->story_sequence) }}" min="0" class="form-control @error('story_sequence') is-invalid @enderror" placeholder="e.g. 3 (if part of a story series)">
                        @error('story_sequence')
                            <div class="invalid-feedback d-block">{{ $message }}</div>
                        @enderror
                    </div>
                </div>
            </div>

            {{-- F — Text / thread --}}
            <div @class(['card', 'mb-3', 'pt-visible' => $postType === 'text_post']) data-post-type-panel="text_post" @if($postType !== 'text_post') hidden @endif>
                <div class="card-body">
                    <h5 class="card-title mb-3">Text / thread</h5>
                    <p class="small text-muted mb-2">Main text uses <strong>Caption (English)</strong> above.</p>
                    <div class="mb-3">
                        <label for="thread_hook_line" class="form-label">Hook line</label>
                        <input type="text" name="thread_hook_line" id="thread_hook_line" value="{{ $threadHookLine }}" maxlength="150"
                               class="form-control @error('thread_hook_line') is-invalid @enderror" placeholder="The opening line that stops the scroll">
                        <div class="text-end small text-muted mt-1"><span id="thread-hook-count">0</span> / 150</div>
                        @error('thread_hook_line')
                            <div class="invalid-feedback d-block">{{ $message }}</div>
                        @enderror
                    </div>
                    <label class="form-label">Thread / continuation posts</label>
                    <div id="thread-posts-container" class="mb-2">
                        @foreach($threadPosts as $i => $tp)
                            <div class="thread-post-row mb-2">
                                <div class="d-flex justify-content-between align-items-center mb-1">
                                    <span class="small text-muted">Post {{ $i + 2 }}</span>
                                    @if($i > 0)
                                        <button type="button" class="btn btn-sm btn-outline-secondary thread-remove-post" aria-label="Remove">×</button>
                                    @endif
                                </div>
                                <textarea name="thread_posts[]" rows="3" maxlength="5000" class="form-control" placeholder="Continuation text...">{{ $tp }}</textarea>
                            </div>
                        @endforeach
                    </div>
                    <button type="button" id="thread-add-post" class="btn btn-sm btn-outline-secondary">Add post</button>
                </div>
            </div>

            {{-- G — Poll --}}
            <div @class(['card', 'mb-3', 'pt-visible' => $postType === 'poll']) data-post-type-panel="poll" @if($postType !== 'poll') hidden @endif>
                <div class="card-body">
                    <h5 class="card-title mb-3">Poll / interactive</h5>
                    <div class="mb-3">
                        <label for="poll_question" class="form-label">Poll question <span class="text-danger">*</span></label>
                        <input type="text" name="poll_question" id="poll_question" value="{{ old('poll_question', $post?->poll_question) }}" maxlength="500"
                               class="form-control @error('poll_question') is-invalid @enderror" placeholder="Your poll question">
                        <div class="text-end small text-muted mt-1"><span id="poll-q-count">0</span> / 500</div>
                        @error('poll_question')
                            <div class="invalid-feedback d-block">{{ $message }}</div>
                        @enderror
                    </div>
                    <label class="form-label">Poll options</label>
                    <div id="poll-options-container" class="mb-2">
                        @foreach($pollOptions as $i => $opt)
                            <div class="poll-option-row mb-2">
                                <label class="form-label small mb-0">Option {{ $i + 1 }}</label>
                                <div class="d-flex gap-2 align-items-center">
                                    <input type="text" name="poll_options[]" value="{{ $opt }}" class="form-control poll-option-input" maxlength="200" placeholder="Option {{ $i + 1 }}">
                                    @if($i >= 2)
                                        <button type="button" class="btn btn-sm btn-outline-secondary poll-remove-option" aria-label="Remove">×</button>
                                    @endif
                                </div>
                            </div>
                        @endforeach
                    </div>
                    <button type="button" id="poll-add-option" class="btn btn-sm btn-outline-secondary mb-3">Add option</button>
                    <p class="small text-muted mb-0" id="poll-platform-note"></p>
                    @error('poll_options')
                        <div class="text-danger small mt-2">{{ $message }}</div>
                    @enderror
                </div>
            </div>

            {{-- H — UGC --}}
            <div @class(['card', 'mb-3', 'pt-visible' => $postType === 'ugc']) data-post-type-panel="ugc" @if($postType !== 'ugc') hidden @endif>
                <div class="card-body">
                    <h5 class="card-title mb-3">UGC / repost</h5>
                    <div class="mb-3">
                        <label for="original_post_url" class="form-label">Original post URL <span class="text-danger">*</span></label>
                        <input type="url" name="original_post_url" id="original_post_url" value="{{ old('original_post_url', $post?->original_post_url) }}" maxlength="500"
                               class="form-control @error('original_post_url') is-invalid @enderror" placeholder="Link to the original post">
                        @error('original_post_url')
                            <div class="invalid-feedback d-block">{{ $message }}</div>
                        @enderror
                    </div>
                    <div class="mb-3">
                        <label for="ugc_creator_handle" class="form-label">Creator handle</label>
                        <input type="text" name="ugc_creator_handle" id="ugc_creator_handle" value="{{ old('ugc_creator_handle', $post?->ugc_creator_handle) }}" maxlength="200"
                               class="form-control @error('ugc_creator_handle') is-invalid @enderror" placeholder="@creatorhandle">
                        @error('ugc_creator_handle')
                            <div class="invalid-feedback d-block">{{ $message }}</div>
                        @enderror
                    </div>
                    <div class="mb-3">
                        <label for="ugc_permission" class="form-label">Permission status</label>
                        <select name="ugc_permission" id="ugc_permission" class="form-select @error('ugc_permission') is-invalid @enderror">
                            <option value="" @selected(old('ugc_permission', $post?->ugc_permission) === null || old('ugc_permission', $post?->ugc_permission) === '')>—</option>
                            <option value="asked" @selected(old('ugc_permission', $post?->ugc_permission) === 'asked')>Asked — awaiting reply</option>
                            <option value="granted" @selected(old('ugc_permission', $post?->ugc_permission) === 'granted')>Permission granted</option>
                            <option value="not_required" @selected(old('ugc_permission', $post?->ugc_permission) === 'not_required')>Not required (public reshare)</option>
                        </select>
                        @error('ugc_permission')
                            <div class="invalid-feedback d-block">{{ $message }}</div>
                        @enderror
                    </div>
                    <p class="small text-muted mb-0">Use the <strong>Caption</strong> fields above for your repost caption (EN + AR).</p>
                </div>
            </div>
        </div>

        {{-- Card 3 — Universal tracking --}}
        <div class="card mb-3">
            <div class="card-body">
                <h5 class="card-title mb-3">Universal tracking</h5>
                <div class="mb-3">
                    <label for="utm_url" class="form-label">UTM tracking URL</label>
                    <input type="url" name="utm_url" id="utm_url" value="{{ old('utm_url', $post?->utm_url) }}" maxlength="500"
                           class="form-control @error('utm_url') is-invalid @enderror" placeholder="https://...">
                    @error('utm_url')
                        <div class="invalid-feedback d-block">{{ $message }}</div>
                    @enderror
                </div>
                <div class="mb-3">
                    <label for="brief" class="form-label">Creative brief <span class="text-muted fw-normal">(optional)</span></label>
                    <textarea name="brief" id="brief" rows="3" maxlength="2000" class="form-control @error('brief') is-invalid @enderror" placeholder="Internal notes for creative team...">{{ old('brief', $post?->brief) }}</textarea>
                    <div class="text-end small text-muted mt-1"><span id="brief-count">0</span> / 2000</div>
                    @error('brief')
                        <div class="invalid-feedback d-block">{{ $message }}</div>
                    @enderror
                </div>
                <div class="mb-0" id="scheduled-at-wrap" style="display: none;">
                    <label for="scheduled_at" class="form-label">Scheduled date &amp; time</label>
                    <input type="datetime-local" name="scheduled_at" id="scheduled_at" value="{{ $scheduledVal }}"
                           class="form-control @error('scheduled_at') is-invalid @enderror">
                    @error('scheduled_at')
                        <div class="invalid-feedback d-block">{{ $message }}</div>
                    @enderror
                </div>
            </div>
        </div>

        @if($post === null || in_array($post->status, ['draft', 'rejected']))
            <div class="d-flex flex-wrap gap-2">
                <button type="submit" name="submit_mode" value="draft" class="btn btn-outline-secondary">Save as draft</button>
                <button type="submit" name="submit_mode" value="review" class="btn text-white" style="background-color: var(--pulsify-accent, #5F63F2);">Submit for review</button>
                <button type="submit" name="submit_mode" value="schedule" id="btn-schedule-post" class="btn btn-outline-primary" style="display: none;">Schedule post</button>
            </div>
        @elseif($post !== null)
            @if($post->status === 'in_review')
                <div class="alert alert-info mb-0" role="alert">
                    This post is under review. Waiting for approval.
                </div>
            @elseif($post->status === 'approved')
                <div class="alert alert-success mb-0" role="alert">
                    ✓ Approved — ready to publish.
                </div>
            @elseif($post->status === 'scheduled')
                <div class="alert alert-primary mb-0" role="alert">
                    ⏰ Scheduled for {{ $post->scheduled_at?->format('M j, Y g:i A') ?? '—' }}
                </div>
            @endif
        @endif
    </div>

    <div class="col-lg-5">
        <div class="card sticky-lg-top" style="top: 88px;">
            <div class="card-body">
                <h5 class="card-title mb-3">Preview</h5>
                <div id="post-preview" class="border rounded-3 p-3 bg-light" style="max-width: 100%;">
                    <div class="d-flex align-items-center gap-2 mb-2">
                        <div id="preview-platform-logo" style="width: 36px; height: 36px; border-radius: 8px; overflow: hidden; line-height: 0;"></div>
                        <div class="min-w-0">
                            <div class="fw-semibold small text-truncate" id="preview-username">{{ auth()->user()->company?->name ?? 'Company' }}</div>
                            <div class="text-muted" style="font-size: 11px;">Just now · <span id="preview-platform-label">—</span></div>
                        </div>
                        <div class="ms-auto" style="flex-shrink: 0;">
                            <span id="preview-avatar-letter" style="width: 36px; height: 36px; min-width: 36px; min-height: 36px; border-radius: 50%; background: var(--brand-primary, #5F63F2); color: white; font-size: 14px; font-weight: 600; display: flex; align-items: center; justify-content: center; overflow: hidden; flex-shrink: 0;">
                                {{ strtoupper(mb_substr((string) (auth()->user()->company?->name ?? 'C'), 0, 1)) }}
                            </span>
                        </div>
                    </div>
                    <div id="preview-caption" class="small mb-2" style="white-space: pre-wrap; word-break: break-word;"></div>
                    <div id="preview-media-wrap" class="rounded-2 overflow-hidden bg-white border" style="display: none; min-height: 120px;">
                        <img id="preview-media-img" src="" alt="" class="w-100 d-block" style="max-height: 220px; object-fit: cover;">
                    </div>
                    <div id="preview-media-placeholder" class="rounded-2 border border-dashed d-flex align-items-center justify-content-center text-muted small" style="min-height: 120px;">
                        Media preview
                    </div>
                </div>
                <div class="mt-3 small">
                    <span class="text-muted">Caption limit (</span><span id="preview-limit-label">—</span><span class="text-muted">):</span>
                    <span id="preview-char-count" class="fw-semibold">0</span>
                    <span class="text-muted"> / </span>
                    <span id="preview-char-max">2200</span>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="d-none" aria-hidden="true">
    @foreach($platformKeys as $pk)
        <template id="tpl-logo-{{ $pk }}">
            @include('posts.partials.platform-logo-svg', ['platform' => $pk, 'uid' => 'ig-grad-'.$pk.'-tpl', 'size' => 40])
        </template>
    @endforeach
</div>

<script>
(function () {
    var limits = @json($platformCaptionLimits);
    var platformLabels = @json($platformOptions);
    var FADE_MS = 200;
    var initialPostType = @json($postType);

    function platformFromChannelSelect() {
        var sel = document.getElementById('channel_id');
        if (!sel || !sel.options[sel.selectedIndex]) return 'custom';
        var opt = sel.options[sel.selectedIndex];
        return opt.getAttribute('data-platform') || 'custom';
    }

    function fixInstagramGradient(root) {
        if (!root) return;
        var grad = root.querySelector('linearGradient[id]');
        if (!grad) return;
        var oldId = grad.id;
        var nid = 'ig-' + Math.random().toString(36).slice(2, 11);
        grad.id = nid;
        var ref = root.querySelector('[fill="url(#' + oldId + ')"]');
        if (ref) ref.setAttribute('fill', 'url(#' + nid + ')');
    }

    function setLogoContainer(container, platform) {
        if (!container) return;
        var tpl = document.getElementById('tpl-logo-' + platform);
        if (!tpl || !tpl.content) {
            tpl = document.getElementById('tpl-logo-custom');
        }
        container.innerHTML = '';
        if (tpl && tpl.content) {
            var frag = tpl.content.cloneNode(true);
            var wrap = document.createElement('div');
            wrap.appendChild(frag);
            fixInstagramGradient(wrap);
            while (wrap.firstChild) {
                container.appendChild(wrap.firstChild);
            }
        }
    }

    function syncChannelLogos() {
        var p = platformFromChannelSelect();
        setLogoContainer(document.getElementById('channel-logo-preview'), p);
        setLogoContainer(document.getElementById('preview-platform-logo'), p);
        var label = platformLabels[p] || p;
        var el = document.getElementById('preview-platform-label');
        if (el) el.textContent = label.replace(/^[^\s]+\s/, '');
        updatePollPlatformNote(p);
    }

    function updatePollPlatformNote(platform) {
        var el = document.getElementById('poll-platform-note');
        if (!el) return;
        var map = {
            twitter: 'Twitter/X polls: 4 options max, 25 characters each.',
            instagram: 'Instagram polls: 2 options only.',
            linkedin: 'LinkedIn polls: 4 options, 30 characters each.',
        };
        el.textContent = map[platform] || 'Check your platform\'s poll limits before publishing.';
    }

    function toggleSchedule() {
        var st = document.getElementById('post-status');
        var wrap = document.getElementById('scheduled-at-wrap');
        var btnSch = document.getElementById('btn-schedule-post');
        if (!st) return;
        if (wrap) wrap.style.display = st.value === 'scheduled' ? 'block' : 'none';
        if (btnSch) btnSch.style.display = st.value === 'scheduled' ? 'inline-block' : 'none';
    }

    function updatePillarDot() {
        var sel = document.getElementById('content_pillar');
        var dot = document.getElementById('pillar-color-dot');
        if (!sel || !dot) return;
        var opt = sel.options[sel.selectedIndex];
        var c = opt && opt.getAttribute('data-color');
        if (c) {
            dot.style.display = 'inline-block';
            dot.style.background = c;
        } else {
            dot.style.display = 'none';
        }
    }

    function countEl(id, counterId) {
        var ta = document.getElementById(id);
        var out = document.getElementById(counterId);
        if (ta && out) out.textContent = String((ta.value || '').length);
    }

    function looksLikeImageUrl(url) {
        if (!url || url.length < 8) return false;
        return /\.(jpg|jpeg|png|gif|webp|svg)(\?|$)/i.test(url);
    }

    function getPrimaryMediaUrl() {
        var ptEl = document.getElementById('post_type');
        var type = ptEl ? ptEl.value : 'static_image';
        if (type === 'carousel') {
            var inputs = document.querySelectorAll('#carousel-slides-container .carousel-slide-url');
            for (var i = 0; i < inputs.length; i++) {
                var v = (inputs[i].value || '').trim();
                if (v) return v;
            }
            return '';
        }
        if (type === 'short_video' || type === 'long_video') {
            var v1 = document.getElementById('sv_video_url');
            var v2 = document.getElementById('lv_video_url');
            if (v1 && (v1.value || '').trim()) return (v1.value || '').trim();
            if (v2 && (v2.value || '').trim()) return (v2.value || '').trim();
            return '';
        }
        if (type === 'story') {
            var a = document.querySelector('.story-asset-url');
            return a ? (a.value || '').trim() : '';
        }
        if (type === 'static_image') {
            var m = document.getElementById('media_url');
            return m ? (m.value || '').trim() : '';
        }
        return '';
    }

    function updatePreview() {
        var cap = document.getElementById('caption_en');
        var prev = document.getElementById('preview-caption');
        var text = cap ? cap.value : '';
        if (prev) prev.textContent = text;

        var p = platformFromChannelSelect();
        var max = limits[p] != null ? limits[p] : 2200;
        var count = text.length;
        var countEl2 = document.getElementById('preview-char-count');
        var maxEl = document.getElementById('preview-char-max');
        var lbl = document.getElementById('preview-limit-label');
        if (countEl2) {
            countEl2.textContent = String(count);
            countEl2.className = 'fw-semibold' + (count > max ? ' text-danger' : '');
        }
        if (maxEl) maxEl.textContent = String(max);
        if (lbl) lbl.textContent = (platformLabels[p] || p).replace(/^[^\s]+\s+/, '');

        var url = getPrimaryMediaUrl();
        var wrap = document.getElementById('preview-media-wrap');
        var ph = document.getElementById('preview-media-placeholder');
        var img = document.getElementById('preview-media-img');
        if (url && looksLikeImageUrl(url) && img && wrap && ph) {
            img.onerror = function () {
                wrap.style.display = 'none';
                ph.style.display = 'flex';
            };
            img.onload = function () {
                wrap.style.display = 'block';
                ph.style.display = 'none';
            };
            img.src = url;
        } else if (wrap && ph) {
            wrap.style.display = 'none';
            ph.style.display = 'flex';
            if (img) img.removeAttribute('src');
        }
    }

    function setPanelDisabled() {
        document.querySelectorAll('[data-post-type-panel]').forEach(function (panel) {
            var on = !panel.hasAttribute('hidden');
            panel.querySelectorAll('input, textarea, select, button').forEach(function (el) {
                el.disabled = !on;
            });
        });
    }

    function showPostTypePanel(type, animate) {
        var panels = document.querySelectorAll('[data-post-type-panel]');
        var target = document.querySelector('[data-post-type-panel="' + type + '"]');

        function reveal(el) {
            if (!el) return;
            el.removeAttribute('hidden');
            el.classList.remove('pt-visible');
            if (!animate) {
                el.classList.add('pt-visible');
                setPanelDisabled();
                updatePreview();
                return;
            }
            requestAnimationFrame(function () {
                requestAnimationFrame(function () {
                    el.classList.add('pt-visible');
                });
            });
            setPanelDisabled();
            updatePreview();
        }

        function hidePanel(el, done) {
            if (!el || el.hasAttribute('hidden')) {
                if (done) done();
                return;
            }
            el.classList.remove('pt-visible');
            setTimeout(function () {
                el.setAttribute('hidden', 'hidden');
                if (done) done();
            }, animate ? FADE_MS : 0);
        }

        var toHide = Array.prototype.filter.call(panels, function (p) {
            return p.getAttribute('data-post-type-panel') !== type;
        });

        if (!animate) {
            toHide.forEach(function (p) {
                p.setAttribute('hidden', 'hidden');
                p.classList.remove('pt-visible');
            });
            reveal(target);
            return;
        }

        var remaining = toHide.length;
        if (remaining === 0) {
            reveal(target);
            return;
        }
        toHide.forEach(function (p) {
            hidePanel(p, function () {
                remaining -= 1;
                if (remaining === 0) {
                    reveal(target);
                }
            });
        });
    }

    function onPostTypeChange() {
        var sel = document.getElementById('post_type');
        var type = sel ? sel.value : 'static_image';
        showPostTypePanel(type, true);
    }

    function renumberCarouselSlides() {
        var rows = document.querySelectorAll('#carousel-slides-container .carousel-slide-row');
        rows.forEach(function (row, idx) {
            row.querySelectorAll('.small.fw-semibold').forEach(function (lab) {
                lab.textContent = 'Slide ' + (idx + 1);
            });
            var urlInp = row.querySelector('.carousel-slide-url');
            if (urlInp) urlInp.placeholder = 'Slide ' + (idx + 1) + ' URL';
        });
        var c = document.getElementById('carousel-slide-count');
        if (c) c.textContent = String(rows.length);
    }

    function bindCarouselRemove(btn) {
        btn.addEventListener('click', function () {
            var row = btn.closest('.carousel-slide-row');
            var container = document.getElementById('carousel-slides-container');
            if (!row || !container || container.querySelectorAll('.carousel-slide-row').length <= 1) return;
            row.remove();
            renumberCarouselSlides();
            refreshCarouselRemoveButtons();
            updatePreview();
        });
    }

    function refreshCarouselRemoveButtons() {
        var rows = document.querySelectorAll('#carousel-slides-container .carousel-slide-row');
        rows.forEach(function (row, idx) {
            var existing = row.querySelector('.carousel-remove-slide');
            var ph = row.querySelector('.carousel-remove-placeholder');
            if (idx === 0) {
                if (existing) {
                    existing.remove();
                    var span = document.createElement('span');
                    span.className = 'carousel-remove-placeholder mb-1';
                    span.style.width = '38px';
                    span.style.flexShrink = '0';
                    var wrap = row.querySelector('.d-flex.gap-2');
                    if (wrap) wrap.appendChild(span);
                }
            } else {
                if (ph) ph.remove();
                if (!row.querySelector('.carousel-remove-slide')) {
                    var b = document.createElement('button');
                    b.type = 'button';
                    b.className = 'btn btn-sm btn-outline-secondary carousel-remove-slide mb-1';
                    b.setAttribute('aria-label', 'Remove slide');
                    b.textContent = '×';
                    bindCarouselRemove(b);
                    var wrap = row.querySelector('.d-flex.gap-2');
                    if (wrap) wrap.appendChild(b);
                }
            }
        });
    }

    function bindStoryRemove(btn) {
        btn.addEventListener('click', function () {
            var row = btn.closest('.story-asset-row');
            var container = document.getElementById('story-assets-container');
            if (!row || !container || container.querySelectorAll('.story-asset-row').length <= 1) return;
            row.remove();
            refreshStoryRemoveButtons();
            updatePreview();
        });
    }

    function refreshStoryRemoveButtons() {
        var rows = document.querySelectorAll('#story-assets-container .story-asset-row');
        rows.forEach(function (row, idx) {
            var existing = row.querySelector('.story-remove-asset');
            var ph = row.querySelector('.story-remove-placeholder');
            if (idx === 0) {
                if (existing) {
                    existing.remove();
                    var span = document.createElement('span');
                    span.className = 'story-remove-placeholder';
                    span.style.width = '38px';
                    span.style.flexShrink = '0';
                    row.appendChild(span);
                }
            } else {
                if (ph) ph.remove();
                if (!row.querySelector('.story-remove-asset')) {
                    var b = document.createElement('button');
                    b.type = 'button';
                    b.className = 'btn btn-sm btn-outline-secondary story-remove-asset';
                    b.setAttribute('aria-label', 'Remove');
                    b.textContent = '×';
                    bindStoryRemove(b);
                    row.appendChild(b);
                }
            }
        });
    }

    function bindThreadRemove(btn) {
        btn.addEventListener('click', function () {
            var row = btn.closest('.thread-post-row');
            var container = document.getElementById('thread-posts-container');
            if (!row || !container || container.querySelectorAll('.thread-post-row').length <= 1) return;
            row.remove();
            refreshThreadRemoveButtons();
        });
    }

    function refreshThreadRemoveButtons() {
        var rows = document.querySelectorAll('#thread-posts-container .thread-post-row');
        rows.forEach(function (row, idx) {
            var hdr = row.querySelector('.d-flex');
            if (!hdr) return;
            var btn = hdr.querySelector('.thread-remove-post');
            if (idx === 0) {
                if (btn) btn.remove();
            } else {
                if (!btn) {
                    var b = document.createElement('button');
                    b.type = 'button';
                    b.className = 'btn btn-sm btn-outline-secondary thread-remove-post';
                    b.setAttribute('aria-label', 'Remove');
                    b.textContent = '×';
                    bindThreadRemove(b);
                    hdr.appendChild(b);
                }
            }
            var label = hdr.querySelector('.small');
            if (label) label.textContent = 'Post ' + (idx + 2);
        });
    }

    function bindPollRemove(btn) {
        btn.addEventListener('click', function () {
            var row = btn.closest('.poll-option-row');
            var container = document.getElementById('poll-options-container');
            if (!row || !container || container.querySelectorAll('.poll-option-row').length <= 2) return;
            row.remove();
            refreshPollOptionButtons();
        });
    }

    function refreshPollOptionButtons() {
        var rows = document.querySelectorAll('#poll-options-container .poll-option-row');
        rows.forEach(function (row, idx) {
            var flex = row.querySelector('.d-flex');
            if (!flex) return;
            var btn = flex.querySelector('.poll-remove-option');
            if (idx < 2) {
                if (btn) btn.remove();
            } else {
                if (!btn) {
                    var b = document.createElement('button');
                    b.type = 'button';
                    b.className = 'btn btn-sm btn-outline-secondary poll-remove-option';
                    b.setAttribute('aria-label', 'Remove');
                    b.textContent = '×';
                    bindPollRemove(b);
                    flex.appendChild(b);
                }
            }
            var lab = row.querySelector('label');
            if (lab) lab.textContent = 'Option ' + (idx + 1);
        });
    }

    function syncStickerFields() {
        var st = document.getElementById('sticker_type');
        var tw = document.getElementById('sticker-text-wrap');
        var lw = document.getElementById('sticker-link-wrap');
        if (!st || !tw || !lw) return;
        var v = st.value || 'none';
        var showText = v !== 'none' && v !== '';
        tw.hidden = !showText;
        lw.hidden = v !== 'link';
        tw.querySelectorAll('input').forEach(function (i) { i.disabled = !showText; });
        lw.querySelectorAll('input').forEach(function (i) { i.disabled = v !== 'link'; });
    }

    document.querySelectorAll('.carousel-remove-slide').forEach(bindCarouselRemove);
    document.querySelectorAll('.story-remove-asset').forEach(bindStoryRemove);
    document.querySelectorAll('.thread-remove-post').forEach(bindThreadRemove);
    document.querySelectorAll('.poll-remove-option').forEach(bindPollRemove);

    var addSlideBtn = document.getElementById('carousel-add-slide');
    if (addSlideBtn) {
        addSlideBtn.addEventListener('click', function () {
            var container = document.getElementById('carousel-slides-container');
            if (!container) return;
            var n = container.querySelectorAll('.carousel-slide-row').length;
            if (n >= 10) return;
            var row = document.createElement('div');
            row.className = 'carousel-slide-row border rounded p-2 mb-2';
            row.innerHTML = '<div class="small text-muted mb-1 fw-semibold">Slide ' + (n + 1) + '</div>' +
                '<div class="mb-2"><label class="form-label small mb-0">Image / video URL</label>' +
                '<input type="url" name="slide_urls[]" value="" class="form-control carousel-slide-url" maxlength="500" placeholder="Slide ' + (n + 1) + ' URL"></div>' +
                '<div class="mb-2"><label class="form-label small mb-0">Slide copy</label>' +
                '<textarea name="slide_copy[]" rows="2" class="form-control" placeholder="Copy for slide ' + (n + 1) + '..."></textarea></div>' +
                '<div class="d-flex gap-2 align-items-end">' +
                '<div class="flex-grow-1"><label class="form-label small mb-0">Alt text</label>' +
                '<input type="text" name="alt_texts[]" value="" class="form-control" maxlength="500" placeholder="Describe image for accessibility"></div>' +
                '<button type="button" class="btn btn-sm btn-outline-secondary carousel-remove-slide mb-1" aria-label="Remove slide">×</button></div>';
            container.appendChild(row);
            bindCarouselRemove(row.querySelector('.carousel-remove-slide'));
            row.querySelector('.carousel-slide-url').addEventListener('input', updatePreview);
            renumberCarouselSlides();
            refreshCarouselRemoveButtons();
        });
    }

    document.getElementById('carousel-slides-container') &&
        document.getElementById('carousel-slides-container').addEventListener('input', function (e) {
            if (e.target && e.target.classList.contains('carousel-slide-url')) updatePreview();
        });

    var addStoryBtn = document.getElementById('story-add-asset');
    if (addStoryBtn) {
        addStoryBtn.addEventListener('click', function () {
            var container = document.getElementById('story-assets-container');
            if (!container) return;
            var n = container.querySelectorAll('.story-asset-row').length;
            if (n >= 10) return;
            var row = document.createElement('div');
            row.className = 'story-asset-row d-flex gap-2 mb-2 align-items-center';
            row.innerHTML = '<input type="url" name="story_asset_urls[]" value="" class="form-control story-asset-url" maxlength="500" placeholder="Asset URL ' + (n + 1) + '">' +
                '<button type="button" class="btn btn-sm btn-outline-secondary story-remove-asset" aria-label="Remove">×</button>';
            container.appendChild(row);
            bindStoryRemove(row.querySelector('.story-remove-asset'));
            row.querySelector('.story-asset-url').addEventListener('input', updatePreview);
            refreshStoryRemoveButtons();
        });
    }

    document.getElementById('story-assets-container') &&
        document.getElementById('story-assets-container').addEventListener('input', function (e) {
            if (e.target && e.target.classList.contains('story-asset-url')) updatePreview();
        });

    var addThreadBtn = document.getElementById('thread-add-post');
    if (addThreadBtn) {
        addThreadBtn.addEventListener('click', function () {
            var container = document.getElementById('thread-posts-container');
            if (!container) return;
            var n = container.querySelectorAll('.thread-post-row').length;
            if (n >= 10) return;
            var row = document.createElement('div');
            row.className = 'thread-post-row mb-2';
            row.innerHTML = '<div class="d-flex justify-content-between align-items-center mb-1">' +
                '<span class="small text-muted">Post ' + (n + 2) + '</span>' +
                '<button type="button" class="btn btn-sm btn-outline-secondary thread-remove-post" aria-label="Remove">×</button></div>' +
                '<textarea name="thread_posts[]" rows="3" maxlength="5000" class="form-control" placeholder="Continuation text..."></textarea>';
            container.appendChild(row);
            bindThreadRemove(row.querySelector('.thread-remove-post'));
            refreshThreadRemoveButtons();
        });
    }

    var addPollOpt = document.getElementById('poll-add-option');
    if (addPollOpt) {
        addPollOpt.addEventListener('click', function () {
            var container = document.getElementById('poll-options-container');
            if (!container) return;
            var n = container.querySelectorAll('.poll-option-row').length;
            if (n >= 4) return;
            var row = document.createElement('div');
            row.className = 'poll-option-row mb-2';
            row.innerHTML = '<label class="form-label small mb-0">Option ' + (n + 1) + '</label>' +
                '<div class="d-flex gap-2 align-items-center">' +
                '<input type="text" name="poll_options[]" value="" class="form-control poll-option-input" maxlength="200" placeholder="Option ' + (n + 1) + '">' +
                '<button type="button" class="btn btn-sm btn-outline-secondary poll-remove-option" aria-label="Remove">×</button></div>';
            container.appendChild(row);
            bindPollRemove(row.querySelector('.poll-remove-option'));
            refreshPollOptionButtons();
        });
    }

    var ch = document.getElementById('channel_id');
    if (ch) ch.addEventListener('change', function () { syncChannelLogos(); updatePreview(); });
    var st = document.getElementById('post-status');
    if (st) st.addEventListener('change', toggleSchedule);
    var pillar = document.getElementById('content_pillar');
    if (pillar) pillar.addEventListener('change', updatePillarDot);
    var pt = document.getElementById('post_type');
    if (pt) pt.addEventListener('change', onPostTypeChange);
    var stickerSel = document.getElementById('sticker_type');
    if (stickerSel) stickerSel.addEventListener('change', syncStickerFields);

    ['caption_en', 'caption_ar', 'brief', 'media_url', 'short_video_hook', 'thread_hook_line', 'poll_question'].forEach(function (id) {
        var el = document.getElementById(id);
        if (el) el.addEventListener('input', function () {
            if (id === 'caption_en' || id === 'media_url') updatePreview();
            if (id === 'caption_en') countEl('caption_en', 'caption-en-count');
            if (id === 'caption_ar') countEl('caption_ar', 'caption-ar-count');
            if (id === 'brief') countEl('brief', 'brief-count');
            if (id === 'short_video_hook') countEl('short_video_hook', 'short-video-hook-count');
            if (id === 'thread_hook_line') countEl('thread_hook_line', 'thread-hook-count');
            if (id === 'poll_question') countEl('poll_question', 'poll-q-count');
        });
    });

    var slideHook = document.getElementById('slide_headlines_0');
    if (slideHook) slideHook.addEventListener('input', function () {
        countEl('slide_headlines_0', 'carousel-hook-count');
    });

    ['sv_video_url', 'lv_video_url'].forEach(function (id) {
        var el = document.getElementById(id);
        if (el) el.addEventListener('input', updatePreview);
    });

    var formEl = document.getElementById('post-form');
    if (formEl) {
        formEl.addEventListener('submit', function () {
            setPanelDisabled();
        });
    }

    syncChannelLogos();
    toggleSchedule();
    updatePillarDot();
    syncStickerFields();
    showPostTypePanel(initialPostType, false);
    renumberCarouselSlides();
    countEl('caption_en', 'caption-en-count');
    countEl('caption_ar', 'caption-ar-count');
    countEl('brief', 'brief-count');
    countEl('short_video_hook', 'short-video-hook-count');
    countEl('thread_hook_line', 'thread-hook-count');
    countEl('slide_headlines_0', 'carousel-hook-count');
    countEl('poll_question', 'poll-q-count');
    updatePreview();
})();
</script>
