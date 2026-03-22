@extends('layouts.dashboard')

@php
    $pageTitle = 'Content Pillars';
    $canManage = in_array(auth()->user()->role, ['owner', 'admin'], true);
    $remaining = 100 - $totalPercentage;
@endphp

@section('content')
    @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            {{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    @endif
    @if(session('error'))
        <div class="alert alert-danger alert-dismissible fade show" role="alert">
            {{ session('error') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    @endif
    @if($errors->any())
        <div class="alert alert-danger" role="alert">
            <ul class="mb-0 ps-3">
                @foreach($errors->all() as $err)
                    <li>{{ $err }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    @if($canManage)
        <div class="card mb-4">
            <div class="card-body">
                <h5 class="card-title mb-3">Add New Pillar</h5>
                <form method="post" action="{{ route('pillars.store') }}" class="row g-3 align-items-end">
                    @csrf
                    <div class="col-12 col-lg-4">
                        <label for="name" class="form-label">Pillar name <span class="text-danger">*</span></label>
                        <input type="text" name="name" id="name" value="{{ old('name') }}" maxlength="100"
                               class="form-control @error('name') is-invalid @enderror"
                               placeholder="e.g. Product Showcase" required>
                        @error('name')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                    <div class="col-6 col-lg-2">
                        <label for="color" class="form-label">Color <span class="text-danger">*</span></label>
                        <input type="color" name="color" id="color" value="{{ old('color', '#5F63F2') }}"
                               class="form-control form-control-color w-100 @error('color') is-invalid @enderror" required>
                        @error('color')
                            <div class="invalid-feedback d-block">{{ $message }}</div>
                        @enderror
                    </div>
                    <div class="col-6 col-lg-2">
                        <label for="target_percentage" class="form-label">Target % <span class="text-danger">*</span></label>
                        <input type="number" name="target_percentage" id="target_percentage" min="0" max="100"
                               value="{{ old('target_percentage') }}"
                               class="form-control @error('target_percentage') is-invalid @enderror"
                               placeholder="e.g. 25" required>
                        @error('target_percentage')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                    <div class="col-12 col-lg-2 d-grid">
                        <button type="submit" class="btn text-white" style="background-color: var(--pulsify-accent, #5F63F2);">
                            Add Pillar
                        </button>
                    </div>
                </form>
                <div class="mt-3 small">
                    @if($totalPercentage > 100)
                        <span class="text-danger fw-semibold">Warning: total allocation is {{ $totalPercentage }}% (over 100%). Adjust pillars to fix.</span>
                    @elseif($totalPercentage === 100)
                        <span class="text-success fw-semibold">✓ 100% allocated</span>
                    @else
                        <span class="text-muted">{{ max(0, $remaining) }}% remaining to allocate</span>
                    @endif
                </div>
            </div>
        </div>

        @if($pillars->isEmpty())
            <div class="card mb-4">
                <div class="card-body">
                    <form method="post" action="{{ route('pillars.defaults') }}" class="d-inline">
                        @csrf
                        <button type="submit" class="btn btn-outline-secondary">Load Default Pillars</button>
                    </form>
                    <p class="text-muted small mb-0 mt-2">Adds Product Showcase, Educational, Behind the Scenes, Promotional, and User Generated Content (100% total).</p>
                </div>
            </div>
        @endif
    @endif

    @if($pillars->isEmpty())
        <div class="card">
            <div class="card-body text-center py-5">
                <p class="text-muted mb-0">
                    @if($canManage)
                        No content pillars yet. Add your first pillar above.
                    @else
                        No content pillars yet.
                    @endif
                </p>
            </div>
        </div>
    @else
        <div class="card mb-4">
            <div class="card-body">
                <h5 class="card-title mb-3">Your pillars</h5>
                <div class="table-responsive">
                    <table class="table table-hover table-nowrap align-middle mb-0">
                        <thead class="table-light">
                            <tr>
                                <th style="width: 48px;" class="border-0"></th>
                                <th class="border-0">Pillar name</th>
                                <th class="border-0" style="width: 100px;">Target %</th>
                                <th class="border-0" style="min-width: 160px;">Progress</th>
                                <th class="border-0" style="width: 110px;">Posts</th>
                                @if($canManage)
                                    <th class="border-0 text-end" style="width: 100px;">Actions</th>
                                @endif
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($pillars as $pillar)
                                <tr>
                                    <td>
                                        <span class="d-inline-block rounded-circle border"
                                              style="width: 20px; height: 20px; background-color: {{ $pillar->color }};"
                                              title="{{ $pillar->color }}"></span>
                                    </td>
                                    <td class="fw-medium">{{ $pillar->name }}</td>
                                    <td>{{ (int) $pillar->target_percentage }}%</td>
                                    <td>
                                        <div class="progress" style="height: 8px;">
                                            <div class="progress-bar" role="progressbar"
                                                 style="width: {{ min(100, max(0, (int) $pillar->target_percentage)) }}%; background-color: {{ $pillar->color }};"
                                                 aria-valuenow="{{ (int) $pillar->target_percentage }}" aria-valuemin="0" aria-valuemax="100"></div>
                                        </div>
                                    </td>
                                    <td>{{ number_format((int) $pillar->posts_count) }}</td>
                                    @if($canManage)
                                        <td class="text-end">
                                            <button type="button"
                                                    class="btn btn-sm btn-light border btn-edit-pillar"
                                                    data-bs-toggle="modal"
                                                    data-bs-target="#editPillarModal"
                                                    data-id="{{ $pillar->id }}"
                                                    data-name="{{ $pillar->name }}"
                                                    data-color="{{ $pillar->color }}"
                                                    data-target="{{ (int) $pillar->target_percentage }}">
                                                <i class="ri-pencil-line"></i>
                                            </button>
                                            <form method="post" action="{{ route('pillars.destroy', $pillar) }}" class="d-inline"
                                                  onsubmit="return confirm('Delete this pillar?');">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" class="btn btn-sm btn-light border text-danger">
                                                    <i class="ri-delete-bin-line"></i>
                                                </button>
                                            </form>
                                        </td>
                                    @endif
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    @endif

    <div class="card">
        <div class="card-body">
            <h5 class="card-title mb-3">Allocation summary</h5>
            @if($pillars->isEmpty())
                <p class="text-muted small mb-0">Add pillars to see allocation.</p>
            @else
                <div class="d-flex rounded overflow-hidden border bg-light" style="min-height: 40px;">
                    @if($totalPercentage > 0)
                        <div class="d-flex flex-grow-0 flex-shrink-0 overflow-hidden" style="width: {{ min(100, $totalPercentage) }}%; min-height: 40px;">
                            @foreach($pillars as $pillar)
                                @if((int) $pillar->target_percentage > 0)
                                    <div class="d-flex align-items-center justify-content-center px-1 text-center small text-white text-truncate"
                                         style="flex: {{ (int) $pillar->target_percentage }} 1 0%; min-width: 0; background-color: {{ $pillar->color }};"
                                         title="{{ $pillar->name }} ({{ (int) $pillar->target_percentage }}%)">
                                        <span class="text-truncate" style="max-width: 100%; font-size: 11px; font-weight: 600; text-shadow: 0 0 2px rgba(0,0,0,0.35);">{{ $pillar->name }}</span>
                                    </div>
                                @endif
                            @endforeach
                        </div>
                        @if($totalPercentage < 100)
                            <div class="flex-grow-1 d-flex align-items-center justify-content-center text-muted small px-2">
                                Unallocated
                            </div>
                        @endif
                    @else
                        <div class="flex-grow-1 d-flex align-items-center justify-content-center text-muted small">No allocation</div>
                    @endif
                </div>
                <p class="mt-3 mb-0 small">
                    <span class="fw-semibold">{{ $totalPercentage }}%</span> allocated ·
                    <span class="text-muted">{{ max(0, 100 - $totalPercentage) }}% unallocated</span>
                </p>
                @if($totalPercentage === 100)
                    <p class="text-success small fw-semibold mb-0 mt-2">✓ 100% allocated</p>
                @endif
                @if($totalPercentage > 100)
                    <p class="text-danger small fw-semibold mb-0 mt-2">Total exceeds 100% — rebalance your pillars.</p>
                @endif
            @endif
        </div>
    </div>

    @if($canManage)
        <div class="modal fade" id="editPillarModal" tabindex="-1" aria-labelledby="editPillarModalLabel" aria-hidden="true">
            <div class="modal-dialog modal-dialog-centered">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title" id="editPillarModalLabel">Edit pillar</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <form id="edit-pillar-form" method="post" action="">
                        @csrf
                        @method('PUT')
                        <div class="modal-body">
                            <div class="mb-3">
                                <label for="edit_name" class="form-label">Pillar name <span class="text-danger">*</span></label>
                                <input type="text" name="name" id="edit_name" class="form-control" maxlength="100" required>
                            </div>
                            <div class="mb-3">
                                <label for="edit_color" class="form-label">Color <span class="text-danger">*</span></label>
                                <input type="color" name="color" id="edit_color" class="form-control form-control-color w-100" required>
                            </div>
                            <div class="mb-0">
                                <label for="edit_target_percentage" class="form-label">Target % <span class="text-danger">*</span></label>
                                <input type="number" name="target_percentage" id="edit_target_percentage" class="form-control" min="0" max="100" required>
                            </div>
                        </div>
                        <div class="modal-footer">
                            <button type="button" class="btn btn-light" data-bs-dismiss="modal">Cancel</button>
                            <button type="submit" class="btn text-white" style="background-color: var(--pulsify-accent, #5F63F2);">Save</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    @endif
@endsection

@section('scripts')
    @if($canManage)
        <script>
            document.querySelectorAll('.btn-edit-pillar').forEach(function (btn) {
                btn.addEventListener('click', function () {
                    var id = btn.getAttribute('data-id');
                    document.getElementById('edit-pillar-form').action = '{{ url('/pillars') }}/' + id;
                    document.getElementById('edit_name').value = btn.getAttribute('data-name') || '';
                    document.getElementById('edit_color').value = btn.getAttribute('data-color') || '#5F63F2';
                    document.getElementById('edit_target_percentage').value = btn.getAttribute('data-target') || '0';
                });
            });
        </script>
    @endif
@endsection
