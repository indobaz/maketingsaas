<select id="{{ $selectId }}" class="form-select {{ $selectClass ?? 'form-select-sm' }}"
        @if($includeName ?? true) name="{{ $name ?? 'campaign_id' }}" @endif>
    <option value="">— None —</option>
    @foreach($campaignsForTaskLink as $c)
        <option value="{{ $c->id }}">{{ $c->name }} — {{ ucwords(str_replace('_', ' ', (string) $c->status)) }}</option>
    @endforeach
</select>
