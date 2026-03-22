@php
    use App\Http\Controllers\ChannelController;
    $platformOptions = $platformOptions ?? ChannelController::platformOptions();
@endphp
<select id="{{ $selectId }}" class="form-select {{ $selectClass ?? 'form-select-sm' }}"
        @if($includeName ?? true) name="{{ $name ?? 'post_id' }}" @endif>
    <option value="">— None —</option>
    @foreach($postsForTaskLink->groupBy('status') as $grpStatus => $grpPosts)
        <optgroup label="{{ ucwords(str_replace('_', ' ', $grpStatus)) }}">
            @foreach($grpPosts as $p)
                @php
                    $plat = $p->channel?->platform ?? 'custom';
                    $platLabel = $platformOptions[$plat] ?? $plat;
                    $emoji = preg_match('/^(\X)/u', $platLabel, $m) ? $m[1] : '📄';
                    $line = $p->title ?: (\Illuminate\Support\Str::limit((string) ($p->caption_en ?? ''), 50) ?: 'Untitled');
                @endphp
                <option value="{{ $p->id }}">{{ $emoji }} {{ $line }}</option>
            @endforeach
        </optgroup>
    @endforeach
</select>
