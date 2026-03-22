@extends('layouts.dashboard')

@php
    $pageTitle = 'Channels';
    $user = auth()->user();
    $canArchive = in_array($user->role, ['owner', 'admin'], true);
@endphp

@section('content')
    @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            {{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    @endif
    @error('followers_count')
        <div class="alert alert-danger">{{ $message }}</div>
    @enderror

    <div class="card mb-4">
        <div class="card-body">
            <h5 class="card-title mb-3">Connect a Channel</h5>
            <form method="post" action="{{ route('channels.store') }}" class="row g-3 align-items-end">
                @csrf
                <div class="col-12 col-lg-3">
                    <label for="platform" class="form-label">Platform <span class="text-danger">*</span></label>
                    <select name="platform" id="platform" class="form-select @error('platform') is-invalid @enderror" required>
                        <option value="" disabled {{ old('platform') ? '' : 'selected' }}>Select platform</option>
                        @foreach($platformOptions as $value => $label)
                            <option value="{{ $value }}" @selected(old('platform') === $value)>{{ $label }}</option>
                        @endforeach
                    </select>
                    @error('platform')
                        <div class="invalid-feedback d-block">{{ $message }}</div>
                    @enderror
                </div>
                <div class="col-12 col-lg-2">
                    <label for="name" class="form-label">Channel name <span class="text-danger">*</span></label>
                    <input type="text" name="name" id="name" value="{{ old('name') }}" maxlength="100"
                           class="form-control @error('name') is-invalid @enderror" required>
                    @error('name')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>
                <div class="col-12 col-lg-2">
                    <label for="handle" class="form-label">Handle</label>
                    <input type="text" name="handle" id="handle" value="{{ old('handle') }}" maxlength="100"
                           class="form-control @error('handle') is-invalid @enderror" placeholder="@yourhandle">
                    @error('handle')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>
                <div class="col-6 col-lg-1">
                    <label for="color" class="form-label">Color</label>
                    <input type="color" name="color" id="color" value="{{ old('color', '#5F63F2') }}"
                           class="form-control form-control-color w-100 @error('color') is-invalid @enderror" title="Channel color">
                    @error('color')
                        <div class="invalid-feedback d-block">{{ $message }}</div>
                    @enderror
                </div>
                <div class="col-12 col-lg-2">
                    <label for="notes" class="form-label">Notes</label>
                    <input type="text" name="notes" id="notes" value="{{ old('notes') }}"
                           class="form-control @error('notes') is-invalid @enderror" placeholder="Optional">
                    @error('notes')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>
                <div class="col-12 col-lg-2 d-grid">
                    <button type="submit" class="btn text-white" style="background-color: var(--pulsify-accent, #5F63F2);">
                        Add Channel
                    </button>
                </div>
            </form>
        </div>
    </div>

    <div class="row g-3">
        @forelse($channels as $channel)
            @php
                $platformLabel = $platformOptions[$channel->platform] ?? $channel->platform;
                $platformParts = explode(' ', $platformLabel, 2);
                $platformName = count($platformParts) === 2 ? $platformParts[1] : $platformLabel;
                $lastSnap = $channel->latestFollowerSnapshot;
                $lastUpdated = $lastSnap?->recorded_date ?? $channel->updated_at;
                $igGradId = 'ig-grad-' . $channel->id;
            @endphp
            <div class="col-xl-3 col-md-4 col-sm-6">
                <div class="card h-100 overflow-hidden">
                    <div style="height: 6px; background-color: {{ $channel->color }};"></div>
                    <div class="card-body d-flex flex-column" style="padding: 16px;">
                        <div class="d-flex align-items-start gap-2 mb-2">
                            <div class="flex-shrink-0" style="width: 40px; height: 40px; border-radius: 10px; overflow: hidden; line-height: 0;" aria-hidden="true">
                                @switch($channel->platform)
                                    @case('instagram')
                                        <svg width="40" height="40" viewBox="0 0 40 40" xmlns="http://www.w3.org/2000/svg">
                                            <defs>
                                                <linearGradient id="{{ $igGradId }}" x1="0%" y1="100%" x2="100%" y2="0%">
                                                    <stop offset="0%" stop-color="#f09433"/>
                                                    <stop offset="25%" stop-color="#e6683c"/>
                                                    <stop offset="50%" stop-color="#dc2743"/>
                                                    <stop offset="75%" stop-color="#cc2366"/>
                                                    <stop offset="100%" stop-color="#bc1888"/>
                                                </linearGradient>
                                            </defs>
                                            <rect width="40" height="40" rx="10" fill="url(#{{ $igGradId }})"/>
                                            <rect x="11" y="14" width="18" height="14" rx="3.5" fill="none" stroke="#fff" stroke-width="1.4"/>
                                            <circle cx="20" cy="21" r="3.8" fill="none" stroke="#fff" stroke-width="1.4"/>
                                            <circle cx="24.5" cy="16.5" r="1.2" fill="#fff"/>
                                        </svg>
                                        @break
                                    @case('youtube')
                                        <svg width="40" height="40" viewBox="0 0 40 40" xmlns="http://www.w3.org/2000/svg">
                                            <rect width="40" height="40" rx="10" fill="#FF0000"/>
                                            <polygon points="17,12 17,28 29,20" fill="#fff"/>
                                        </svg>
                                        @break
                                    @case('linkedin')
                                        <svg width="40" height="40" viewBox="0 0 40 40" xmlns="http://www.w3.org/2000/svg">
                                            <rect width="40" height="40" rx="10" fill="#0077B5"/>
                                            <text x="20" y="25" text-anchor="middle" fill="#fff" font-size="15" font-weight="700" font-family="system-ui, -apple-system, BlinkMacSystemFont, 'Segoe UI', sans-serif">in</text>
                                        </svg>
                                        @break
                                    @case('tiktok')
                                        <svg width="40" height="40" viewBox="0 0 40 40" xmlns="http://www.w3.org/2000/svg">
                                            <rect width="40" height="40" rx="10" fill="#010101"/>
                                            <ellipse cx="16" cy="26" rx="4" ry="3.2" fill="#fff"/>
                                            <rect x="20.5" y="9" width="3.2" height="17" rx="0.5" fill="#fff"/>
                                            <path d="M23.7 9.2h2.8v0.2c0 3.2 2.4 5.6 5.6 5.8v3.2c-2.4-0.2-4.5-1.4-5.6-3.4V26h-3V9.2z" fill="#fff"/>
                                        </svg>
                                        @break
                                    @case('facebook')
                                        <svg width="40" height="40" viewBox="0 0 40 40" xmlns="http://www.w3.org/2000/svg">
                                            <rect width="40" height="40" rx="10" fill="#1877F2"/>
                                            <text x="21" y="27" text-anchor="middle" fill="#fff" font-size="22" font-weight="700" font-family="system-ui, -apple-system, BlinkMacSystemFont, 'Segoe UI', Arial, sans-serif">f</text>
                                        </svg>
                                        @break
                                    @case('twitter')
                                        <svg width="40" height="40" viewBox="0 0 40 40" xmlns="http://www.w3.org/2000/svg">
                                            <rect width="40" height="40" rx="10" fill="#000000"/>
                                            <path d="M12 12 L28 28 M28 12 L12 28" stroke="#fff" stroke-width="2.4" stroke-linecap="round"/>
                                        </svg>
                                        @break
                                    @case('pinterest')
                                        <svg width="40" height="40" viewBox="0 0 40 40" xmlns="http://www.w3.org/2000/svg">
                                            <rect width="40" height="40" rx="10" fill="#E60023"/>
                                            <text x="20" y="26" text-anchor="middle" fill="#fff" font-size="18" font-weight="700" font-family="system-ui, -apple-system, BlinkMacSystemFont, 'Segoe UI', Arial, sans-serif">P</text>
                                        </svg>
                                        @break
                                    @case('snapchat')
                                        <svg width="40" height="40" viewBox="0 0 40 40" xmlns="http://www.w3.org/2000/svg">
                                            <rect width="40" height="40" rx="10" fill="#FFFC00"/>
                                            <path fill="#fff" d="M20 10c-3.3 0-5.8 2.4-5.8 5.6v4.2c0 0.9 0.4 1.7 1.1 2.2 0.2 0.15 0.15 0.45-0.05 0.6-0.55 0.35-1.15 0.55-1.75 0.75-0.35 0.12-0.5 0.5-0.35 0.85 0.15 0.4 0.55 0.55 0.95 0.45 0.95-0.25 1.85-0.65 2.65-1.15 0.35-0.2 0.8-0.2 1.15 0 0.85 0.55 1.85 0.95 2.9 1.15 0.4 0.1 0.8-0.05 0.95-0.45 0.15-0.35 0-0.73-0.35-0.85-0.6-0.2-1.2-0.4-1.75-0.75-0.2-0.15-0.25-0.45-0.05-0.6 0.7-0.55 1.1-1.35 1.1-2.2v-4.2C25.8 12.4 23.3 10 20 10z"/>
                                        </svg>
                                        @break
                                    @case('whatsapp')
                                        <svg width="40" height="40" viewBox="0 0 40 40" xmlns="http://www.w3.org/2000/svg">
                                            <rect width="40" height="40" rx="10" fill="#25D366"/>
                                            <g transform="translate(8,8)" fill="#fff">
                                                <path d="M6.62 10.79c1.44 2.83 3.76 5.14 6.59 6.59l2.2-2.2c.27-.27.67-.36 1.02-.24 1.12.37 2.33.57 3.57.57.55 0 1 .45 1 1V21c0 9.39-7.61 17-17 17-9.39 0-17-7.61-17-17 0-1.25.2-2.45.57-3.57.12-.35.03-.75-.24-1.02l-2.2-2.2z"/>
                                            </g>
                                        </svg>
                                        @break
                                    @case('custom')
                                    @default
                                        <svg width="40" height="40" viewBox="0 0 40 40" xmlns="http://www.w3.org/2000/svg">
                                            <rect width="40" height="40" rx="10" fill="#6B7280"/>
                                            <text x="20" y="25" text-anchor="middle" fill="#fff" font-size="19" font-family="system-ui, -apple-system, 'Segoe UI', sans-serif">⚙</text>
                                        </svg>
                                        @break
                                @endswitch
                            </div>
                            <span class="text-muted small pt-1">{{ $platformName }}</span>
                        </div>
                        <div style="font-size: 15px; font-weight: 600;">{{ $channel->name }}</div>
                        @if($channel->handle)
                            <div class="text-muted small">{{ $channel->handle }}</div>
                        @endif

                        <div class="mt-2">
                            <div class="small text-muted">Followers</div>
                            <div class="fw-semibold">{{ number_format((int) $channel->followers_count) }}</div>
                            @if($channel->status === 'active')
                                <form method="post" action="{{ route('channels.followers.update', $channel) }}" class="d-flex flex-wrap gap-1 align-items-center mt-1">
                                    @csrf
                                    <input type="number" name="followers_count" value="{{ (int) $channel->followers_count }}" min="0"
                                           class="form-control form-control-sm" style="max-width: 110px;">
                                    <button type="submit" class="btn btn-sm btn-outline-secondary">Update</button>
                                </form>
                            @endif
                            <div class="text-muted small mt-1">
                                Last updated
                                @if($lastUpdated)
                                    {{ \Illuminate\Support\Carbon::parse($lastUpdated)->format('M j, Y') }}
                                @else
                                    —
                                @endif
                            </div>
                        </div>

                        <div class="d-flex flex-wrap gap-1 mt-2">
                            @if($channel->status === 'active')
                                <span class="badge bg-success">Active</span>
                            @else
                                <span class="badge bg-secondary">Archived</span>
                            @endif
                            <span class="badge" style="background: #fd7e14; color: #fff;">Manual</span>
                        </div>

                        @if($channel->notes)
                            <p class="text-muted small mt-2 mb-0 flex-grow-1">
                                {{ \Illuminate\Support\Str::limit($channel->notes, 60) }}
                            </p>
                        @endif

                        <div class="d-flex gap-2 mt-3 pt-2 border-top">
                            <button type="button"
                                    class="btn btn-sm btn-light border btn-edit-channel"
                                    data-bs-toggle="modal"
                                    data-bs-target="#editChannelModal"
                                    data-id="{{ $channel->id }}"
                                    data-name="{{ $channel->name }}"
                                    data-platform="{{ $channel->platform }}"
                                    data-handle="{{ $channel->handle }}"
                                    data-color="{{ $channel->color }}"
                                    data-notes="{{ $channel->notes }}">
                                <i class="ri-pencil-line"></i>
                            </button>
                            @if($canArchive && $channel->status === 'active')
                                <form method="post" action="{{ route('channels.destroy', $channel) }}" class="d-inline"
                                      onsubmit="return confirm('Archive this channel?');">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="btn btn-sm btn-light border text-danger">
                                        <i class="ri-delete-bin-line"></i>
                                    </button>
                                </form>
                            @endif
                        </div>
                    </div>
                </div>
            </div>
        @empty
            <div class="col-12">
                <div class="card">
                    <div class="card-body text-center py-5">
                        <div style="font-size: 64px; line-height: 1;" class="mb-3" aria-hidden="true">📡</div>
                        <p class="text-muted mb-0">No channels yet. Add your first social media channel above.</p>
                    </div>
                </div>
            </div>
        @endforelse
    </div>

    <div class="modal fade" id="editChannelModal" tabindex="-1" aria-labelledby="editChannelModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="editChannelModalLabel">Edit channel</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <form id="edit-channel-form" method="post" action="">
                    @csrf
                    @method('PUT')
                    <div class="modal-body row g-3">
                        <div class="col-md-6">
                            <label for="edit_platform" class="form-label">Platform <span class="text-danger">*</span></label>
                            <select name="platform" id="edit_platform" class="form-select" required>
                                @foreach($platformOptions as $value => $label)
                                    <option value="{{ $value }}">{{ $label }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-6">
                            <label for="edit_name" class="form-label">Channel name <span class="text-danger">*</span></label>
                            <input type="text" name="name" id="edit_name" class="form-control" maxlength="100" required>
                        </div>
                        <div class="col-md-6">
                            <label for="edit_handle" class="form-label">Handle</label>
                            <input type="text" name="handle" id="edit_handle" class="form-control" maxlength="100" placeholder="@yourhandle">
                        </div>
                        <div class="col-md-6">
                            <label for="edit_color" class="form-label">Color</label>
                            <input type="color" name="color" id="edit_color" class="form-control form-control-color w-100">
                        </div>
                        <div class="col-12">
                            <label for="edit_notes" class="form-label">Notes</label>
                            <input type="text" name="notes" id="edit_notes" class="form-control" placeholder="Optional">
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-light" data-bs-dismiss="modal">Cancel</button>
                        <button type="submit" class="btn text-white" style="background-color: var(--pulsify-accent, #5F63F2);">Save Changes</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
@endsection

@section('scripts')
    <script>
        document.querySelectorAll('.btn-edit-channel').forEach(function (btn) {
            btn.addEventListener('click', function () {
                var id = btn.getAttribute('data-id');
                var form = document.getElementById('edit-channel-form');
                form.action = '{{ url('/channels') }}/' + id;

                document.getElementById('edit_name').value = btn.getAttribute('data-name') || '';
                document.getElementById('edit_platform').value = btn.getAttribute('data-platform') || '';
                document.getElementById('edit_handle').value = btn.getAttribute('data-handle') || '';
                document.getElementById('edit_color').value = btn.getAttribute('data-color') || '#5F63F2';
                document.getElementById('edit_notes').value = btn.getAttribute('data-notes') || '';
            });
        });
    </script>
@endsection
