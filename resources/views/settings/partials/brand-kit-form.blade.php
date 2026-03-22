<style>
    .sk-color-row { display: grid; grid-template-columns: 1fr; gap: 14px; }
    @media (min-width: 768px) {
        .sk-color-row { grid-template-columns: 1fr 1fr; gap: 14px; }
    }
    .sk-color-field { border: 1px solid #EAECF0; border-radius: 12px; padding: 14px; }
    .sk-color-inputs { display: grid; grid-template-columns: 44px 1fr 40px; gap: 10px; align-items: center; margin-top: 10px; }
    .sk-hex-input { font-family: ui-monospace, SFMono-Regular, Menlo, Monaco, Consolas, monospace; letter-spacing: 0.2px; }
    .sk-swatch { width: 40px; height: 40px; border-radius: 8px; border: 1px solid #EAECF0; background: #fff; }
    .sk-presets { display: grid; grid-template-columns: 1fr 1fr; gap: 12px; margin-top: 14px; }
    @media (min-width: 768px) { .sk-presets { grid-template-columns: 1fr 1fr 1fr; } }
    .sk-preset { border: 1px solid #EAECF0; border-radius: 10px; padding: 12px; background: #fff; cursor: pointer; user-select: none; transition: transform .15s, box-shadow .15s, border-color .15s; }
    .sk-preset:hover { transform: translateY(-1px); border-color: rgba(95,99,242,0.35); box-shadow: 0 10px 24px rgba(16,24,40,0.08); }
    .sk-preset-swatch { height: 34px; border-radius: 8px; overflow: hidden; border: 1px solid #EAECF0; display: grid; grid-template-columns: 1fr 1fr; }
    .sk-preset-meta { margin-top: 10px; }
    .sk-preset-title { font-weight: 600; font-size: 13px; color: #101828; }
    .sk-preset-codes { font-family: ui-monospace, monospace; font-size: 12px; color: #667085; }
    .sk-preview-wrap { border: 1px solid #EAECF0; border-radius: 14px; overflow: hidden; margin-top: 16px; }
    .sk-mini { display: grid; grid-template-columns: 180px 1fr; min-height: 170px; }
    .sk-mini-sidebar { padding: 14px; background: #272B41; color: #fff; transition: background-color .2s; }
    .sk-mini-brand { font-weight: 900; letter-spacing: .3px; font-size: 14px; opacity: 0.95; }
    .sk-mini-nav { margin-top: 14px; display: grid; gap: 8px; }
    .sk-mini-item { border-radius: 10px; padding: 10px; font-weight: 700; font-size: 12px; background: rgba(255,255,255,0.10); }
    .sk-mini-item.sk-active { background: #5F63F2; color: #fff; transition: background-color .2s; }
    .sk-mini-main { padding: 14px; background: #fff; }
    .sk-mini-card { border: 1px solid #EAECF0; border-radius: 12px; padding: 12px; }
    .sk-mini-badge { display: inline-flex; align-items: center; border-radius: 999px; padding: 6px 10px; font-weight: 800; font-size: 12px; background: rgba(95,99,242,0.12); color: #5F63F2; }
</style>

<form method="POST" action="{{ route('settings.brand') }}" id="brandKitForm" novalidate>
    @csrf
    <div class="sk-color-row">
        <div class="sk-color-field">
            <label class="form-label mb-0">Primary color</label>
            <div class="sk-color-inputs">
                <input class="form-control form-control-color" type="color" id="skPrimaryPicker">
                <input class="form-control sk-hex-input @error('primary_color') is-invalid @enderror" type="text" name="primary_color" id="skPrimaryHex"
                       value="{{ old('primary_color', $company->primary_color ?? $defaultPrimary) }}" placeholder="#5F63F2" required>
                <div class="sk-swatch" id="skPrimarySwatch" aria-hidden="true"></div>
            </div>
            @error('primary_color')<div class="invalid-feedback d-block">{{ $message }}</div>@enderror
        </div>
        <div class="sk-color-field">
            <label class="form-label mb-0">Secondary color</label>
            <div class="sk-color-inputs">
                <input class="form-control form-control-color" type="color" id="skSecondaryPicker">
                <input class="form-control sk-hex-input @error('secondary_color') is-invalid @enderror" type="text" name="secondary_color" id="skSecondaryHex"
                       value="{{ old('secondary_color', $company->secondary_color ?? '#272B41') }}" placeholder="#272B41" required>
                <div class="sk-swatch" id="skSecondarySwatch" aria-hidden="true"></div>
            </div>
            @error('secondary_color')<div class="invalid-feedback d-block">{{ $message }}</div>@enderror
        </div>
    </div>

    <div class="mt-4">
        <div class="fw-semibold" style="font-size: 14px;">Presets</div>
        <div class="text-muted small">Click a combo to auto-fill both colors.</div>
        <div class="sk-presets">
            <div class="sk-preset" data-primary="#5F63F2" data-secondary="#272B41" role="button" tabindex="0">
                <div class="sk-preset-swatch"><div style="background:#5F63F2"></div><div style="background:#272B41"></div></div>
                <div class="sk-preset-meta"><div class="sk-preset-title">Professional Blue</div><div class="sk-preset-codes">#5F63F2 · #272B41</div></div>
            </div>
            <div class="sk-preset" data-primary="#20C997" data-secondary="#1A3C34" role="button" tabindex="0">
                <div class="sk-preset-swatch"><div style="background:#20C997"></div><div style="background:#1A3C34"></div></div>
                <div class="sk-preset-meta"><div class="sk-preset-title">Forest Green</div><div class="sk-preset-codes">#20C997 · #1A3C34</div></div>
            </div>
            <div class="sk-preset" data-primary="#FA8B0C" data-secondary="#2D1F0A" role="button" tabindex="0">
                <div class="sk-preset-swatch"><div style="background:#FA8B0C"></div><div style="background:#2D1F0A"></div></div>
                <div class="sk-preset-meta"><div class="sk-preset-title">Sunset Orange</div><div class="sk-preset-codes">#FA8B0C · #2D1F0A</div></div>
            </div>
            <div class="sk-preset" data-primary="#FF4D4F" data-secondary="#2D0A0A" role="button" tabindex="0">
                <div class="sk-preset-swatch"><div style="background:#FF4D4F"></div><div style="background:#2D0A0A"></div></div>
                <div class="sk-preset-meta"><div class="sk-preset-title">Ruby Red</div><div class="sk-preset-codes">#FF4D4F · #2D0A0A</div></div>
            </div>
            <div class="sk-preset" data-primary="#9B59B6" data-secondary="#1A0A2D" role="button" tabindex="0">
                <div class="sk-preset-swatch"><div style="background:#9B59B6"></div><div style="background:#1A0A2D"></div></div>
                <div class="sk-preset-meta"><div class="sk-preset-title">Royal Purple</div><div class="sk-preset-codes">#9B59B6 · #1A0A2D</div></div>
            </div>
            <div class="sk-preset" data-primary="#64748B" data-secondary="#1E293B" role="button" tabindex="0">
                <div class="sk-preset-swatch"><div style="background:#64748B"></div><div style="background:#1E293B"></div></div>
                <div class="sk-preset-meta"><div class="sk-preset-title">Slate Gray</div><div class="sk-preset-codes">#64748B · #1E293B</div></div>
            </div>
        </div>
    </div>

    <div class="sk-preview-wrap">
        <div class="sk-mini">
            <aside class="sk-mini-sidebar" id="skPreviewSidebar">
                <div class="sk-mini-brand">{{ \Illuminate\Support\Str::limit($company->name ?? 'Workspace', 18) }}</div>
                <div class="sk-mini-nav">
                    <div class="sk-mini-item sk-active" id="skPreviewActive">Dashboard</div>
                    <div class="sk-mini-item">Calendar</div>
                    <div class="sk-mini-item">Posts</div>
                </div>
            </aside>
            <section class="sk-mini-main">
                <div class="sk-mini-card">
                    <div class="d-flex justify-content-between align-items-center">
                        <div class="fw-bold">Preview</div>
                        <div class="sk-mini-badge" id="skPreviewBadge">Primary</div>
                    </div>
                    <div class="text-muted mt-2 small">Sidebar uses secondary. Active item uses primary.</div>
                </div>
            </section>
        </div>
    </div>

    <button type="submit" class="btn text-white mt-4 px-4" style="background: {{ $primaryColor }};">Save Brand Colors</button>
</form>
