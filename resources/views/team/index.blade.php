@extends('layouts.dashboard')

@php
    $pageTitle = 'Team';
@endphp

@section('content')
    <div class="row">
        <div class="col-12 col-xl-5">
            <div class="card">
                <div class="card-header">
                    <div class="fw-semibold">Invite Team Member</div>
                </div>
                <div class="card-body">
                    @if (session('success'))
                        <div class="alert alert-success">{{ session('success') }}</div>
                    @endif
                    @if (session('warning'))
                        <div class="alert alert-warning">{{ session('warning') }}</div>
                    @endif
                    @if (session('error'))
                        <div class="alert alert-danger">{{ session('error') }}</div>
                    @endif

                    <form method="POST" action="{{ url('/team/invite') }}" class="row g-3">
                        @csrf

                        <div class="col-12">
                            <label class="form-label" for="email">Email</label>
                            <input id="email" name="email" type="email"
                                   class="form-control @error('email') is-invalid @enderror"
                                   value="{{ old('email') }}" required>
                            @error('email')
                            <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="col-12">
                            <label class="form-label" for="role">Role</label>
                            <select id="role" name="role"
                                    class="form-select @error('role') is-invalid @enderror" required>
                                <option value="" disabled {{ old('role') ? '' : 'selected' }}>Select role</option>
                                <option value="admin" {{ old('role') === 'admin' ? 'selected' : '' }}>Admin</option>
                                <option value="editor" {{ old('role') === 'editor' ? 'selected' : '' }}>Editor</option>
                                <option value="viewer" {{ old('role') === 'viewer' ? 'selected' : '' }}>Viewer</option>
                            </select>
                            @error('role')
                            <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="col-12">
                            <button type="submit" class="btn bg-pulsify-accent text-white w-100">
                                Send Invite
                            </button>
                        </div>
                    </form>

                    <div class="text-muted small mt-3">
                        Invites are valid for 7 days.
                    </div>
                </div>
            </div>
        </div>

        <div class="col-12 col-xl-7">
            <div class="d-flex align-items-center justify-content-between mb-2">
                <div class="fw-semibold">Team Members</div>
                <div class="text-muted small">{{ $pendingCount }} pending invites</div>
            </div>

            <div class="card">
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table table-hover align-middle mb-0">
                            <thead class="table-light">
                            <tr>
                                <th>Name</th>
                                <th>Email</th>
                                <th>Role</th>
                                <th>Status</th>
                                <th>Joined Date</th>
                                <th class="text-end">Actions</th>
                            </tr>
                            </thead>
                            <tbody>
                            @forelse($members as $member)
                                @php
                                    $role = strtolower((string) $member->role);
                                    $status = strtolower((string) $member->status);

                                    $roleClasses = [
                                        'owner' => 'bg-warning-subtle text-warning',
                                        'admin' => 'bg-purple-subtle text-purple',
                                        'editor' => 'bg-primary-subtle text-primary',
                                        'viewer' => 'bg-secondary-subtle text-secondary',
                                    ];
                                    $statusClasses = [
                                        'active' => 'bg-success-subtle text-success',
                                        'invited' => 'bg-info-subtle text-info',
                                        'inactive' => 'bg-danger-subtle text-danger',
                                    ];
                                @endphp
                                <tr>
                                    <td>
                                        {{ $member->name ?? '—' }}
                                    </td>
                                    <td>{{ $member->email }}</td>
                                    <td>
                                        <span class="badge {{ $roleClasses[$role] ?? 'bg-secondary-subtle text-secondary' }}">
                                            {{ ucfirst($role) }}
                                        </span>
                                    </td>
                                    <td>
                                        <span class="badge {{ $statusClasses[$status] ?? 'bg-secondary-subtle text-secondary' }}">
                                            {{ ucfirst($status) }}
                                        </span>
                                    </td>
                                    <td>
                                        {{ $member->created_at?->format('Y-m-d') ?? '—' }}
                                    </td>
                                    <td class="text-end">
                                        <div class="d-inline-flex gap-2">
                                            @if($member->status === 'invited')
                                                <form method="POST" action="{{ url('/team/'.$member->id.'/resend') }}">
                                                    @csrf
                                                    <button type="submit" class="btn btn-sm btn-outline-primary">
                                                        Resend
                                                    </button>
                                                </form>
                                            @endif

                                            @if(auth()->id() !== $member->id)
                                                <form method="POST" action="{{ url('/team/'.$member->id.'/remove') }}"
                                                      onsubmit="return confirm('Remove this user?');">
                                                    @csrf
                                                    @method('DELETE')
                                                    <button type="submit" class="btn btn-sm btn-outline-danger">
                                                        Remove
                                                    </button>
                                                </form>
                                            @endif
                                        </div>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="6" class="text-center text-muted py-4">
                                        No team members found.
                                    </td>
                                </tr>
                            @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection

