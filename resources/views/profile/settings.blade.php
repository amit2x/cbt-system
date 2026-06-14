@extends('layouts.' . (auth()->user()->isAdmin() ? 'admin' : (auth()->user()->isInstructor() ? 'instructor' : 'student')))

@section('title', 'Account Settings')

@section('content')
<div class="container-fluid">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h1 class="h3 mb-0 text-gray-800">Account Settings</h1>
        <a href="{{ route('profile.show') }}" class="btn btn-secondary">
            <i class="bi bi-arrow-left"></i> Back to Profile
        </a>
    </div>

    <div class="row">
        <div class="col-md-3">
            <div class="card shadow mb-4">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-primary">Settings Menu</h6>
                </div>
                <div class="list-group list-group-flush">
                    <a href="#general" class="list-group-item list-group-item-action active" data-bs-toggle="tab">
                        <i class="bi bi-gear me-2"></i>General Settings
                    </a>
                    <a href="#password" class="list-group-item list-group-item-action" data-bs-toggle="tab">
                        <i class="bi bi-lock me-2"></i>Change Password
                    </a>
                    <a href="#notifications" class="list-group-item list-group-item-action" data-bs-toggle="tab">
                        <i class="bi bi-bell me-2"></i>Notifications
                    </a>
                    <a href="#privacy" class="list-group-item list-group-item-action" data-bs-toggle="tab">
                        <i class="bi bi-shield-lock me-2"></i>Privacy & Security
                    </a>
                </div>
            </div>
        </div>

        <div class="col-md-9">
            <div class="tab-content">
                <!-- General Settings Tab -->
                <div class="tab-pane fade show active" id="general">
                    <div class="card shadow mb-4">
                        <div class="card-header py-3">
                            <h6 class="m-0 font-weight-bold text-primary">General Settings</h6>
                        </div>
                        <div class="card-body">
                            <form action="{{ route('profile.update-settings') }}" method="POST">
                                @csrf
                                @method('PUT')

                                <div class="mb-3">
                                    <label for="language" class="form-label">Language</label>
                                    <select class="form-control" id="language" name="language">
                                        <option value="en" {{ (auth()->user()->settings['language'] ?? 'en') == 'en' ? 'selected' : '' }}>English</option>
                                        <option value="es" {{ (auth()->user()->settings['language'] ?? 'en') == 'es' ? 'selected' : '' }}>Español</option>
                                        <option value="fr" {{ (auth()->user()->settings['language'] ?? 'en') == 'fr' ? 'selected' : '' }}>Français</option>
                                        <option value="de" {{ (auth()->user()->settings['language'] ?? 'en') == 'de' ? 'selected' : '' }}>Deutsch</option>
                                    </select>
                                </div>

                                <div class="mb-3">
                                    <label for="timezone" class="form-label">Timezone</label>
                                    <select class="form-control" id="timezone" name="timezone">
                                        @foreach(timezone_identifiers_list() as $tz)
                                            <option value="{{ $tz }}" {{ (auth()->user()->settings['timezone'] ?? 'UTC') == $tz ? 'selected' : '' }}>
                                                {{ $tz }}
                                            </option>
                                        @endforeach
                                    </select>
                                </div>

                                <div class="mb-3">
                                    <label for="date_format" class="form-label">Date Format</label>
                                    <select class="form-control" id="date_format" name="date_format">
                                        <option value="d M Y" {{ (auth()->user()->settings['date_format'] ?? 'd M Y') == 'd M Y' ? 'selected' : '' }}>15 Jan 2024</option>
                                        <option value="Y-m-d" {{ (auth()->user()->settings['date_format'] ?? 'd M Y') == 'Y-m-d' ? 'selected' : '' }}>2024-01-15</option>
                                        <option value="m/d/Y" {{ (auth()->user()->settings['date_format'] ?? 'd M Y') == 'm/d/Y' ? 'selected' : '' }}>01/15/2024</option>
                                        <option value="d/m/Y" {{ (auth()->user()->settings['date_format'] ?? 'd M Y') == 'd/m/Y' ? 'selected' : '' }}>15/01/2024</option>
                                    </select>
                                </div>

                                <div class="mb-3">
                                    <label for="time_format" class="form-label">Time Format</label>
                                    <select class="form-control" id="time_format" name="time_format">
                                        <option value="H:i" {{ (auth()->user()->settings['time_format'] ?? 'H:i') == 'H:i' ? 'selected' : '' }}>24 Hour (14:30)</option>
                                        <option value="h:i A" {{ (auth()->user()->settings['time_format'] ?? 'H:i') == 'h:i A' ? 'selected' : '' }}>12 Hour (02:30 PM)</option>
                                    </select>
                                </div>

                                <button type="submit" class="btn btn-primary">
                                    <i class="bi bi-save"></i> Save Settings
                                </button>
                            </form>
                        </div>
                    </div>
                </div>

                <!-- Change Password Tab -->
                <div class="tab-pane fade" id="password">
                    <div class="card shadow mb-4">
                        <div class="card-header py-3">
                            <h6 class="m-0 font-weight-bold text-primary">Change Password</h6>
                        </div>
                        <div class="card-body">
                            <form action="{{ route('profile.password') }}" method="POST">
                                @csrf
                                @method('PUT')

                                <div class="mb-3">
                                    <label for="current_password" class="form-label">Current Password</label>
                                    <input type="password" class="form-control @error('current_password') is-invalid @enderror"
                                           id="current_password" name="current_password" required>
                                    @error('current_password')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>

                                <div class="mb-3">
                                    <label for="new_password" class="form-label">New Password</label>
                                    <input type="password" class="form-control @error('new_password') is-invalid @enderror"
                                           id="new_password" name="new_password" required>
                                    @error('new_password')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>

                                <div class="mb-3">
                                    <label for="new_password_confirmation" class="form-label">Confirm New Password</label>
                                    <input type="password" class="form-control" id="new_password_confirmation"
                                           name="new_password_confirmation" required>
                                </div>

                                <!-- Password Strength Indicator -->
                                <div class="password-strength mb-3">
                                    <div class="progress" style="height: 5px;">
                                        <div class="progress-bar" id="strength-bar" role="progressbar" style="width: 0%;"></div>
                                    </div>
                                    <small class="text-muted" id="strength-text">Enter a new password</small>
                                </div>

                                <button type="submit" class="btn btn-primary">
                                    <i class="bi bi-save"></i> Update Password
                                </button>
                            </form>
                        </div>
                    </div>
                </div>

                <!-- Notifications Tab -->
                <div class="tab-pane fade" id="notifications">
                    <div class="card shadow mb-4">
                        <div class="card-header py-3">
                            <h6 class="m-0 font-weight-bold text-primary">Notification Preferences</h6>
                        </div>
                        <div class="card-body">
                            <form action="{{ route('profile.update-settings') }}" method="POST">
                                @csrf
                                @method('PUT')

                                <div class="form-check mb-3">
                                    <input type="checkbox" class="form-check-input" id="email_notifications"
                                           name="email_notifications" value="1"
                                           {{ (auth()->user()->settings['email_notifications'] ?? true) ? 'checked' : '' }}>
                                    <label class="form-check-label" for="email_notifications">
                                        Email Notifications
                                    </label>
                                    <small class="d-block text-muted">Receive exam reminders and result updates via email</small>
                                </div>

                                <div class="form-check mb-3">
                                    <input type="checkbox" class="form-check-input" id="exam_reminders"
                                           name="exam_reminders" value="1"
                                           {{ (auth()->user()->settings['exam_reminders'] ?? true) ? 'checked' : '' }}>
                                    <label class="form-check-label" for="exam_reminders">
                                        Exam Reminders
                                    </label>
                                    <small class="d-block text-muted">Get reminders before scheduled exams</small>
                                </div>

                                <div class="form-check mb-3">
                                    <input type="checkbox" class="form-check-input" id="result_alerts"
                                           name="result_alerts" value="1"
                                           {{ (auth()->user()->settings['result_alerts'] ?? true) ? 'checked' : '' }}>
                                    <label class="form-check-label" for="result_alerts">
                                        Result Alerts
                                    </label>
                                    <small class="d-block text-muted">Get notified when exam results are published</small>
                                </div>

                                <div class="form-check mb-3">
                                    <input type="checkbox" class="form-check-input" id="feedback_requests"
                                           name="feedback_requests" value="1"
                                           {{ (auth()->user()->settings['feedback_requests'] ?? true) ? 'checked' : '' }}>
                                    <label class="form-check-label" for="feedback_requests">
                                        Feedback Requests
                                    </label>
                                    <small class="d-block text-muted">Receive requests for class feedback</small>
                                </div>

                                <button type="submit" class="btn btn-primary">
                                    <i class="bi bi-save"></i> Save Preferences
                                </button>
                            </form>
                        </div>
                    </div>
                </div>

                <!-- Privacy & Security Tab -->
                <div class="tab-pane fade" id="privacy">
                    <div class="card shadow mb-4">
                        <div class="card-header py-3">
                            <h6 class="m-0 font-weight-bold text-primary">Privacy & Security</h6>
                        </div>
                        <div class="card-body">
                            <h6 class="mb-3">Active Sessions</h6>
                            <div class="table-responsive mb-4">
                                <table class="table table-bordered">
                                    <thead>
                                        <tr>
                                            <th>Device</th>
                                            <th>IP Address</th>
                                            <th>Last Activity</th>
                                            <th>Action</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach(session()->get('sessions', []) as $session)
                                            <tr>
                                                <td>
                                                    <i class="bi bi-{{ $session['device'] == 'mobile' ? 'phone' : 'laptop' }} me-2"></i>
                                                    {{ ucfirst($session['device']) }}
                                                </td>
                                                <td>{{ $session['ip'] }}</td>
                                                <td>{{ \Carbon\Carbon::parse($session['last_activity'])->diffForHumans() }}</td>
                                                <td>
                                                    @if(!$session['current'])
                                                        <button type="button" class="btn btn-sm btn-danger" onclick="revokeSession('{{ $session['id'] }}')">
                                                            Revoke
                                                        </button>
                                                    @else
                                                        <span class="badge bg-success">Current Session</span>
                                                    @endif
                                                </td>
                                            </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>

                            <h6 class="mb-3">Two-Factor Authentication</h6>
                            <div class="alert alert-info">
                                <i class="bi bi-info-circle me-2"></i>
                                Two-factor authentication adds an extra layer of security to your account.
                            </div>

                            <button type="button" class="btn btn-outline-primary" onclick="enable2FA()">
                                <i class="bi bi-shield-lock"></i> Enable 2FA
                            </button>

                            <hr>

                            <h6 class="mb-3 text-danger">Danger Zone</h6>
                            <div class="alert alert-warning">
                                <i class="bi bi-exclamation-triangle me-2"></i>
                                Once you delete your account, there is no going back. Please be certain.
                            </div>

                            <button type="button" class="btn btn-danger" onclick="deleteAccount()">
                                <i class="bi bi-trash"></i> Delete Account
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Delete Account Modal -->
<div class="modal fade" id="deleteAccountModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header bg-danger text-white">
                <h5 class="modal-title">Delete Account</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <p>Are you absolutely sure you want to delete your account?</p>
                <p class="text-danger">This action cannot be undone. All your data will be permanently removed.</p>

                <form id="deleteAccountForm" method="POST">
                    @csrf
                    @method('DELETE')
                    <div class="mb-3">
                        <label for="confirm_password" class="form-label">Enter your password to confirm:</label>
                        <input type="password" class="form-control" id="confirm_password" name="password" required>
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                <button type="submit" form="deleteAccountForm" class="btn btn-danger">Delete Account</button>
            </div>
        </div>
    </div>
</div>

<!-- 2FA Modal -->
<div class="modal fade" id="twoFactorModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header bg-primary text-white">
                <h5 class="modal-title">Enable Two-Factor Authentication</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body text-center">
                <div class="mb-3">
                    {!! $qrCode ?? 'QR Code would appear here' !!}
                </div>
                <p>Scan this QR code with your authenticator app (Google Authenticator, Authy, etc.)</p>
                <p class="text-muted small">Or enter this code manually: <strong>ABCD EFGH IJKL MNOP</strong></p>

                <form id="verify2FAForm">
                    <div class="mb-3">
                        <label for="verification_code" class="form-label">Verification Code</label>
                        <input type="text" class="form-control text-center" id="verification_code"
                               placeholder="Enter 6-digit code" maxlength="6">
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                <button type="button" class="btn btn-primary" onclick="verify2FA()">Verify & Enable</button>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
    // Password strength checker
    document.getElementById('new_password')?.addEventListener('input', function(e) {
        const password = e.target.value;
        const strengthBar = document.getElementById('strength-bar');
        const strengthText = document.getElementById('strength-text');

        let strength = 0;
        let feedback = '';

        if (password.length >= 8) strength += 25;
        if (password.match(/[a-z]+/)) strength += 25;
        if (password.match(/[A-Z]+/)) strength += 25;
        if (password.match(/[0-9]+/)) strength += 15;
        if (password.match(/[$@#&!]+/)) strength += 10;

        strengthBar.style.width = strength + '%';

        if (strength < 30) {
            strengthBar.className = 'progress-bar bg-danger';
            strengthText.textContent = 'Weak password';
        } else if (strength < 60) {
            strengthBar.className = 'progress-bar bg-warning';
            strengthText.textContent = 'Medium password';
        } else if (strength < 80) {
            strengthBar.className = 'progress-bar bg-info';
            strengthText.textContent = 'Good password';
        } else {
            strengthBar.className = 'progress-bar bg-success';
            strengthText.textContent = 'Strong password';
        }
    });

    // Tab switching
    document.querySelectorAll('.list-group-item').forEach(item => {
        item.addEventListener('click', function(e) {
            e.preventDefault();

            document.querySelectorAll('.list-group-item').forEach(i => i.classList.remove('active'));
            this.classList.add('active');

            const target = this.getAttribute('href').substring(1);
            document.querySelectorAll('.tab-pane').forEach(tab => tab.classList.remove('show', 'active'));
            document.getElementById(target).classList.add('show', 'active');
        });
    });

    function revokeSession(sessionId) {
        if (confirm('Are you sure you want to revoke this session?')) {
            $.ajax({
                url: '{{ route("profile.revoke-session") }}',
                method: 'POST',
                data: {
                    _token: '{{ csrf_token() }}',
                    session_id: sessionId
                },
                success: function(response) {
                    toastr.success('Session revoked successfully');
                    setTimeout(() => {
                        location.reload();
                    }, 1500);
                },
                error: function() {
                    toastr.error('Error revoking session');
                }
            });
        }
    }

    function enable2FA() {
        $('#twoFactorModal').modal('show');
    }

    function verify2FA() {
        const code = document.getElementById('verification_code').value;
        if (code.length !== 6) {
            toastr.warning('Please enter a valid 6-digit code');
            return;
        }

        $.ajax({
            url: '{{ route("profile.enable-2fa") }}',
            method: 'POST',
            data: {
                _token: '{{ csrf_token() }}',
                code: code
            },
            success: function(response) {
                if (response.status === 'success') {
                    toastr.success('Two-factor authentication enabled');
                    $('#twoFactorModal').modal('hide');
                    setTimeout(() => {
                        location.reload();
                    }, 1500);
                }
            },
            error: function() {
                toastr.error('Invalid verification code');
            }
        });
    }

    function deleteAccount() {
        $('#deleteAccountModal').modal('show');
    }
</script>
@endpush
