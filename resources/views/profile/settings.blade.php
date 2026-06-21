@extends('layouts.' . (auth()->user()->isAdmin() ? 'admin' : (auth()->user()->isInstructor() ? 'instructor' : 'student')))

@section('title', 'Account Settings')

@section('page-title', 'Account Settings')
@section('page-actions')
    <span class="badge bg-{{ auth()->user()->isAdmin() ? 'danger' : (auth()->user()->isInstructor() ? 'warning' : 'info') }} fs-6">
        {{ ucfirst(auth()->user()->roles->first()->name ?? 'User') }}
    </span>
@endsection

@section('content')
<div class="row">
    <!-- Settings Sidebar -->
    <div class="col-lg-3 mb-4">
        <div class="card shadow-sm sticky-top" style="top: 80px; z-index: 1;">
            <div class="card-body p-0">
                <div class="list-group list-group-flush settings-nav">
                    <a href="#general" class="list-group-item list-group-item-action active" data-bs-toggle="tab">
                        <i class="bi bi-person-gear me-2"></i>General
                        <span class="badge bg-primary rounded-pill float-end">1</span>
                    </a>
                    <a href="#security" class="list-group-item list-group-item-action" data-bs-toggle="tab">
                        <i class="bi bi-shield-lock me-2"></i>Security
                        <span class="badge bg-primary rounded-pill float-end">2</span>
                    </a>
                    <a href="#notifications" class="list-group-item list-group-item-action" data-bs-toggle="tab">
                        <i class="bi bi-bell me-2"></i>Notifications
                        <span class="badge bg-primary rounded-pill float-end">3</span>
                    </a>
                    <a href="#privacy" class="list-group-item list-group-item-action" data-bs-toggle="tab">
                        <i class="bi bi-eye-slash me-2"></i>Privacy
                        <span class="badge bg-primary rounded-pill float-end">4</span>
                    </a>
                    <a href="#sessions" class="list-group-item list-group-item-action" data-bs-toggle="tab">
                        <i class="bi bi-laptop me-2"></i>Sessions
                        <span class="badge bg-primary rounded-pill float-end">5</span>
                    </a>
                    <a href="#danger" class="list-group-item list-group-item-action text-danger" data-bs-toggle="tab">
                        <i class="bi bi-exclamation-triangle me-2"></i>Danger Zone
                        <span class="badge bg-danger rounded-pill float-end">!</span>
                    </a>
                </div>
            </div>
            <div class="card-footer bg-transparent border-0 pt-0 pb-3">
                <div class="d-grid">
                    <button type="button" class="btn btn-outline-primary btn-sm" onclick="window.location.reload()">
                        <i class="bi bi-arrow-repeat me-1"></i> Refresh
                    </button>
                </div>
            </div>
        </div>
    </div>

    <!-- Settings Content -->
    <div class="col-lg-9">
        <div class="tab-content">
            
            <!-- ==================== GENERAL SETTINGS ==================== -->
            <div class="tab-pane fade show active" id="general">
                <div class="card shadow-sm">
                    <div class="card-header py-3 d-flex justify-content-between align-items-center">
                        <h6 class="m-0 font-weight-bold text-primary">
                            <i class="bi bi-person-gear me-2"></i>General Settings
                        </h6>
                        <span class="badge bg-primary">1</span>
                    </div>
                    <div class="card-body">
                        <div class="row">
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label class="form-label fw-semibold">Display Name</label>
                                    <div class="input-group">
                                        <span class="input-group-text"><i class="bi bi-person"></i></span>
                                        <input type="text" class="form-control" id="display_name" 
                                               value="{{ auth()->user()->name }}" readonly>
                                    </div>
                                    <small class="text-muted">Update your name from your profile page.</small>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label class="form-label fw-semibold">Email Address</label>
                                    <div class="input-group">
                                        <span class="input-group-text"><i class="bi bi-envelope"></i></span>
                                        <input type="email" class="form-control" id="email" 
                                               value="{{ auth()->user()->email }}" readonly>
                                    </div>
                                    <small class="text-muted">Update your email from your profile page.</small>
                                </div>
                            </div>
                        </div>

                        <form action="{{ route('profile.update-settings') }}" method="POST">
                            @csrf
                            @method('PUT')

                            <div class="row">
                                <div class="col-md-6">
                                    <div class="mb-3">
                                        <label class="form-label fw-semibold">Language</label>
                                        <select class="form-select" name="language">
                                            <option value="en" {{ (auth()->user()->settings['language'] ?? 'en') == 'en' ? 'selected' : '' }}>
                                                🇬🇧 English
                                            </option>
                                            <option value="es" {{ (auth()->user()->settings['language'] ?? 'en') == 'es' ? 'selected' : '' }}>
                                                🇪🇸 Español
                                            </option>
                                            <option value="fr" {{ (auth()->user()->settings['language'] ?? 'en') == 'fr' ? 'selected' : '' }}>
                                                🇫🇷 Français
                                            </option>
                                            <option value="de" {{ (auth()->user()->settings['language'] ?? 'en') == 'de' ? 'selected' : '' }}>
                                                🇩🇪 Deutsch
                                            </option>
                                            <option value="hi" {{ (auth()->user()->settings['language'] ?? 'en') == 'hi' ? 'selected' : '' }}>
                                                🇮🇳 हिंदी
                                            </option>
                                        </select>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="mb-3">
                                        <label class="form-label fw-semibold">Timezone</label>
                                        <select class="form-select" name="timezone">
                                            @foreach(timezone_identifiers_list() as $tz)
                                                <option value="{{ $tz }}" 
                                                    {{ (auth()->user()->settings['timezone'] ?? 'UTC') == $tz ? 'selected' : '' }}>
                                                    {{ $tz }}
                                                </option>
                                            @endforeach
                                        </select>
                                    </div>
                                </div>
                            </div>

                            <div class="row">
                                <div class="col-md-6">
                                    <div class="mb-3">
                                        <label class="form-label fw-semibold">Date Format</label>
                                        <select class="form-select" name="date_format">
                                            <option value="d M Y" {{ (auth()->user()->settings['date_format'] ?? 'd M Y') == 'd M Y' ? 'selected' : '' }}>
                                                15 Jan 2024
                                            </option>
                                            <option value="Y-m-d" {{ (auth()->user()->settings['date_format'] ?? 'd M Y') == 'Y-m-d' ? 'selected' : '' }}>
                                                2024-01-15
                                            </option>
                                            <option value="m/d/Y" {{ (auth()->user()->settings['date_format'] ?? 'd M Y') == 'm/d/Y' ? 'selected' : '' }}>
                                                01/15/2024
                                            </option>
                                            <option value="d/m/Y" {{ (auth()->user()->settings['date_format'] ?? 'd M Y') == 'd/m/Y' ? 'selected' : '' }}>
                                                15/01/2024
                                            </option>
                                        </select>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="mb-3">
                                        <label class="form-label fw-semibold">Time Format</label>
                                        <select class="form-select" name="time_format">
                                            <option value="H:i" {{ (auth()->user()->settings['time_format'] ?? 'H:i') == 'H:i' ? 'selected' : '' }}>
                                                24 Hour (14:30)
                                            </option>
                                            <option value="h:i A" {{ (auth()->user()->settings['time_format'] ?? 'H:i') == 'h:i A' ? 'selected' : '' }}>
                                                12 Hour (02:30 PM)
                                            </option>
                                        </select>
                                    </div>
                                </div>
                            </div>

                            <div class="alert alert-info mt-3">
                                <i class="bi bi-info-circle me-2"></i>
                                <strong>Note:</strong> Changes to language and timezone will apply immediately.
                            </div>

                            <div class="d-flex justify-content-end gap-2">
                                <button type="reset" class="btn btn-secondary">Reset</button>
                                <button type="submit" class="btn btn-primary">
                                    <i class="bi bi-save me-1"></i> Save Preferences
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>

            <!-- ==================== SECURITY ==================== -->
            <div class="tab-pane fade" id="security">
                <div class="card shadow-sm">
                    <div class="card-header py-3 d-flex justify-content-between align-items-center">
                        <h6 class="m-0 font-weight-bold text-primary">
                            <i class="bi bi-shield-lock me-2"></i>Security Settings
                        </h6>
                        <span class="badge bg-primary">2</span>
                    </div>
                    <div class="card-body">
                        <h6 class="fw-semibold mb-3">Change Password</h6>
                        <form action="{{ route('profile.password') }}" method="POST">
                            @csrf
                            @method('PUT')

                            <div class="row">
                                <div class="col-md-4">
                                    <div class="mb-3">
                                        <label class="form-label fw-semibold">Current Password</label>
                                        <div class="input-group">
                                            <span class="input-group-text"><i class="bi bi-lock"></i></span>
                                            <input type="password" class="form-control @error('current_password') is-invalid @enderror"
                                                   name="current_password" required>
                                        </div>
                                        @error('current_password')
                                            <div class="text-danger small mt-1">{{ $message }}</div>
                                        @enderror
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <div class="mb-3">
                                        <label class="form-label fw-semibold">New Password</label>
                                        <div class="input-group">
                                            <span class="input-group-text"><i class="bi bi-key"></i></span>
                                            <input type="password" class="form-control @error('new_password') is-invalid @enderror"
                                                   id="new_password" name="new_password" required>
                                        </div>
                                        @error('new_password')
                                            <div class="text-danger small mt-1">{{ $message }}</div>
                                        @enderror
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <div class="mb-3">
                                        <label class="form-label fw-semibold">Confirm Password</label>
                                        <div class="input-group">
                                            <span class="input-group-text"><i class="bi bi-key"></i></span>
                                            <input type="password" class="form-control" 
                                                   name="new_password_confirmation" required>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- Password Strength Indicator -->
                            <div class="password-strength mb-3">
                                <div class="progress" style="height: 6px;">
                                    <div class="progress-bar" id="strength-bar" role="progressbar" style="width: 0%;"></div>
                                </div>
                                <small class="text-muted" id="strength-text">Enter a new password</small>
                            </div>

                            <div class="d-flex justify-content-end gap-2">
                                <button type="reset" class="btn btn-secondary">Reset</button>
                                <button type="submit" class="btn btn-primary">
                                    <i class="bi bi-save me-1"></i> Update Password
                                </button>
                            </div>
                        </form>

                        <hr class="my-4">

                        <h6 class="fw-semibold mb-3">Two-Factor Authentication</h6>
                        <div class="alert alert-info">
                            <div class="d-flex align-items-center">
                                <i class="bi bi-info-circle-fill fs-4 me-3"></i>
                                <div>
                                    <strong>Two-factor authentication</strong> adds an extra layer of security to your account.
                                    When enabled, you'll need to enter a verification code from your authenticator app.
                                </div>
                            </div>
                        </div>

                        @if(auth()->user()->two_factor_enabled ?? false)
                            <div class="alert alert-success">
                                <i class="bi bi-check-circle-fill me-2"></i>
                                Two-factor authentication is <strong>enabled</strong> on your account.
                                <button type="button" class="btn btn-sm btn-warning ms-3">Disable</button>
                            </div>
                        @else
                            <button type="button" class="btn btn-outline-primary" onclick="enable2FA()">
                                <i class="bi bi-shield-lock me-1"></i> Enable Two-Factor Authentication
                            </button>
                        @endif
                    </div>
                </div>
            </div>

            <!-- ==================== NOTIFICATIONS ==================== -->
            <div class="tab-pane fade" id="notifications">
                <div class="card shadow-sm">
                    <div class="card-header py-3 d-flex justify-content-between align-items-center">
                        <h6 class="m-0 font-weight-bold text-primary">
                            <i class="bi bi-bell me-2"></i>Notification Preferences
                        </h6>
                        <span class="badge bg-primary">3</span>
                    </div>
                    <div class="card-body">
                        <form action="{{ route('profile.update-settings') }}" method="POST">
                            @csrf
                            @method('PUT')

                            <div class="notification-options">
                                <div class="form-check form-switch mb-3">
                                    <input class="form-check-input" type="checkbox" name="email_notifications" value="1"
                                           {{ (auth()->user()->settings['email_notifications'] ?? true) ? 'checked' : '' }}
                                           id="email_notifications">
                                    <label class="form-check-label fw-semibold" for="email_notifications">
                                        Email Notifications
                                    </label>
                                    <div class="text-muted small ms-4">Receive exam reminders and result updates via email</div>
                                </div>

                                <div class="form-check form-switch mb-3">
                                    <input class="form-check-input" type="checkbox" name="exam_reminders" value="1"
                                           {{ (auth()->user()->settings['exam_reminders'] ?? true) ? 'checked' : '' }}
                                           id="exam_reminders">
                                    <label class="form-check-label fw-semibold" for="exam_reminders">
                                        Exam Reminders
                                    </label>
                                    <div class="text-muted small ms-4">Get reminders before scheduled exams</div>
                                </div>

                                <div class="form-check form-switch mb-3">
                                    <input class="form-check-input" type="checkbox" name="result_alerts" value="1"
                                           {{ (auth()->user()->settings['result_alerts'] ?? true) ? 'checked' : '' }}
                                           id="result_alerts">
                                    <label class="form-check-label fw-semibold" for="result_alerts">
                                        Result Alerts
                                    </label>
                                    <div class="text-muted small ms-4">Get notified when exam results are published</div>
                                </div>

                                <div class="form-check form-switch mb-3">
                                    <input class="form-check-input" type="checkbox" name="feedback_requests" value="1"
                                           {{ (auth()->user()->settings['feedback_requests'] ?? true) ? 'checked' : '' }}
                                           id="feedback_requests">
                                    <label class="form-check-label fw-semibold" for="feedback_requests">
                                        Feedback Requests
                                    </label>
                                    <div class="text-muted small ms-4">Receive requests for class feedback</div>
                                </div>

                                @if(auth()->user()->isAdmin())
                                <div class="form-check form-switch mb-3">
                                    <input class="form-check-input" type="checkbox" name="system_alerts" value="1"
                                           {{ (auth()->user()->settings['system_alerts'] ?? true) ? 'checked' : '' }}
                                           id="system_alerts">
                                    <label class="form-check-label fw-semibold" for="system_alerts">
                                        System Alerts
                                    </label>
                                    <div class="text-muted small ms-4">Receive system maintenance and update notifications</div>
                                </div>
                                @endif

                                @if(auth()->user()->isInstructor())
                                <div class="form-check form-switch mb-3">
                                    <input class="form-check-input" type="checkbox" name="student_submissions" value="1"
                                           {{ (auth()->user()->settings['student_submissions'] ?? true) ? 'checked' : '' }}
                                           id="student_submissions">
                                    <label class="form-check-label fw-semibold" for="student_submissions">
                                        Student Submissions
                                    </label>
                                    <div class="text-muted small ms-4">Get notified when students submit exams</div>
                                </div>
                                @endif
                            </div>

                            <div class="d-flex justify-content-end gap-2">
                                <button type="reset" class="btn btn-secondary">Reset</button>
                                <button type="submit" class="btn btn-primary">
                                    <i class="bi bi-save me-1"></i> Save Preferences
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>

            <!-- ==================== PRIVACY ==================== -->
            <div class="tab-pane fade" id="privacy">
                <div class="card shadow-sm">
                    <div class="card-header py-3 d-flex justify-content-between align-items-center">
                        <h6 class="m-0 font-weight-bold text-primary">
                            <i class="bi bi-eye-slash me-2"></i>Privacy Settings
                        </h6>
                        <span class="badge bg-primary">4</span>
                    </div>
                    <div class="card-body">
                        <form action="{{ route('profile.update-settings') }}" method="POST">
                            @csrf
                            @method('PUT')

                            <div class="privacy-options">
                                <div class="form-check form-switch mb-3">
                                    <input class="form-check-input" type="checkbox" name="profile_visibility" value="1"
                                           {{ (auth()->user()->settings['profile_visibility'] ?? true) ? 'checked' : '' }}
                                           id="profile_visibility">
                                    <label class="form-check-label fw-semibold" for="profile_visibility">
                                        Profile Visibility
                                    </label>
                                    <div class="text-muted small ms-4">Allow other users to view your profile</div>
                                </div>

                                <div class="form-check form-switch mb-3">
                                    <input class="form-check-input" type="checkbox" name="show_email" value="1"
                                           {{ (auth()->user()->settings['show_email'] ?? true) ? 'checked' : '' }}
                                           id="show_email">
                                    <label class="form-check-label fw-semibold" for="show_email">
                                        Show Email
                                    </label>
                                    <div class="text-muted small ms-4">Display your email address on your profile</div>
                                </div>

                                @if(auth()->user()->isInstructor() || auth()->user()->isAdmin())
                                <div class="form-check form-switch mb-3">
                                    <input class="form-check-input" type="checkbox" name="show_analytics" value="1"
                                           {{ (auth()->user()->settings['show_analytics'] ?? true) ? 'checked' : '' }}
                                           id="show_analytics">
                                    <label class="form-check-label fw-semibold" for="show_analytics">
                                        Show Analytics
                                    </label>
                                    <div class="text-muted small ms-4">Display your performance analytics to others</div>
                                </div>
                                @endif

                                <div class="form-check form-switch mb-3">
                                    <input class="form-check-input" type="checkbox" name="activity_tracking" value="1"
                                           {{ (auth()->user()->settings['activity_tracking'] ?? true) ? 'checked' : '' }}
                                           id="activity_tracking">
                                    <label class="form-check-label fw-semibold" for="activity_tracking">
                                        Activity Tracking
                                    </label>
                                    <div class="text-muted small ms-4">Allow platform to track your activity for personalized recommendations</div>
                                </div>

                                <div class="form-check form-switch mb-3">
                                    <input class="form-check-input" type="checkbox" name="cookie_consent" value="1"
                                           {{ (auth()->user()->settings['cookie_consent'] ?? true) ? 'checked' : '' }}
                                           id="cookie_consent">
                                    <label class="form-check-label fw-semibold" for="cookie_consent">
                                        Cookie Consent
                                    </label>
                                    <div class="text-muted small ms-4">Allow essential and analytical cookies</div>
                                </div>
                            </div>

                            <div class="d-flex justify-content-end gap-2">
                                <button type="reset" class="btn btn-secondary">Reset</button>
                                <button type="submit" class="btn btn-primary">
                                    <i class="bi bi-save me-1"></i> Save Privacy Settings
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>


            
            <!-- ==================== SESSIONS ==================== -->
            <div class="tab-pane fade" id="sessions">
                <div class="card shadow-sm">
                    <div class="card-header py-3 d-flex justify-content-between align-items-center">
                        <h6 class="m-0 font-weight-bold text-primary">
                            <i class="bi bi-laptop me-2"></i>Active Sessions
                        </h6>
                        <span class="badge bg-primary">5</span>
                    </div>
                    <div class="card-body">
                        @php
                            $sessions = session()->get('sessions', []);
                            $currentSessionId = session()->getId();
                        @endphp
            
                        @if(count($sessions) > 0)
                            <div class="table-responsive">
                                <table class="table table-bordered">
                                    <thead>
                                        <tr>
                                            <th>Device</th>
                                            <th>IP Address</th>
                                            <th>Location</th>
                                            <th>Last Active</th>
                                            <th>Action</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach($sessions as $session)
                                            <tr>
                                                <td>
                                                    <div class="d-flex align-items-center">
                                                        <i class="bi bi-{{ $session['device'] == 'mobile' ? 'phone' : 'laptop' }} fs-4 me-2"></i>
                                                        <div>
                                                            <strong>{{ ucfirst($session['device'] ?? 'Desktop') }}</strong>
                                                            <br>
                                                            <small class="text-muted">{{ $session['browser'] ?? 'Unknown' }}</small>
                                                        </div>
                                                    </div>
                                                </td>
                                                <td>{{ $session['ip'] ?? 'Unknown' }}</td>
                                                <td>{{ $session['location'] ?? 'Unknown' }}</td>
                                                <td>{{ \Carbon\Carbon::parse($session['last_activity'])->diffForHumans() }}</td>
                                                <td>
                                                    @if(($session['id'] ?? '') !== $currentSessionId)
                                                        <button type="button" class="btn btn-sm btn-danger" 
                                                                onclick="revokeSession('{{ $session['id'] }}')">
                                                            <i class="bi bi-x-lg me-1"></i> Revoke
                                                        </button>
                                                    @else
                                                        <span class="badge bg-success">
                                                            <i class="bi bi-check-circle me-1"></i> Current Session
                                                        </span>
                                                    @endif
                                                </td>
                                            </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>
            
                            <div class="alert alert-info mt-3">
                                <i class="bi bi-info-circle me-2"></i>
                                If you don't recognize a session, revoke it immediately and change your password.
                            </div>
            
                            <div class="d-flex justify-content-end">
                                <button type="button" class="btn btn-danger" onclick="revokeAllSessions()">
                                    <i class="bi bi-power me-1"></i> Revoke All Other Sessions
                                </button>
                            </div>
                        @else
                            <div class="text-center py-4">
                                <i class="bi bi-laptop fs-1 text-muted"></i>
                                <p class="mt-3 text-muted">No active sessions found.</p>
                            </div>
                        @endif
                    </div>
                </div>
            </div>
            <!-- ==================== DANGER ZONE ==================== -->
            <div class="tab-pane fade" id="danger">
                <div class="card shadow-sm border-danger">
                    <div class="card-header py-3 bg-danger text-white">
                        <h6 class="m-0 font-weight-bold">
                            <i class="bi bi-exclamation-triangle me-2"></i>Danger Zone
                        </h6>
                    </div>
                    <div class="card-body">
                        <div class="alert alert-warning">
                            <div class="d-flex align-items-start">
                                <i class="bi bi-exclamation-triangle-fill fs-4 me-3 text-warning"></i>
                                <div>
                                    <strong>Warning:</strong> The actions below are permanent and cannot be undone.
                                    Please proceed with caution.
                                </div>
                            </div>
                        </div>
            
                        <div class="row">
                            <div class="col-md-6">
                                <div class="card border-warning">
                                    <div class="card-body">
                                        <h6 class="fw-semibold text-warning">
                                            <i class="bi bi-arrow-counterclockwise me-2"></i>Deactivate Account
                                        </h6>
                                        <p class="small text-muted">Temporarily deactivate your account. You can reactivate later.</p>
                                        <button type="button" class="btn btn-warning" onclick="deactivateAccount()">
                                            <i class="bi bi-pause me-1"></i> Deactivate Account
                                        </button>
                                    </div>
                                </div>
                            </div>
                            
                            @if(auth()->user()->isAdmin())
                            <div class="col-md-6">
                                <div class="card border-danger">
                                    <div class="card-body">
                                        <h6 class="fw-semibold text-danger">
                                            <i class="bi bi-trash me-2"></i>Delete Account
                                        </h6>
                                        <p class="small text-muted">Permanently delete this user account and all associated data.</p>
                                        <button type="button" class="btn btn-danger" onclick="deleteAccount()">
                                            <i class="bi bi-trash me-1"></i> Delete Account
                                        </button>
                                    </div>
                                </div>
                            </div>
                            @endif
                        </div>
                        
                        @if(!auth()->user()->isAdmin())
                        <div class="alert alert-info mt-3">
                            <i class="bi bi-info-circle me-2"></i>
                            <strong>Note:</strong> Account deletion is only available to administrators. 
                            If you need to delete your account, please contact an administrator.
                        </div>
                        @endif
                    </div>
                </div>
            </div>

        </div>
    </div>
</div>

<!-- Delete Account Modal (Admin Only) -->
@if(auth()->user()->isAdmin())
<div class="modal fade" id="deleteAccountModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header bg-danger text-white">
                <h5 class="modal-title">
                    <i class="bi bi-exclamation-triangle me-2"></i>Delete Account
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <p><strong>Are you absolutely sure you want to delete this account?</strong></p>
                <div class="alert alert-danger">
                    <i class="bi bi-x-circle me-2"></i>
                    This action <strong>cannot be undone</strong>. All data including:
                    <ul class="mt-2 mb-0">
                        <li>Personal information</li>
                        <li>Exam results and history</li>
                        <li>Course enrollments</li>
                        <li>Feedback and responses</li>
                    </ul>
                    will be permanently removed.
                </div>

                <form id="deleteAccountForm" action="{{ route('admin.users.destroy', auth()->user()) }}" method="POST">
                    @csrf
                    @method('DELETE')
                    <div class="mb-3">
                        <label for="confirm_password" class="form-label fw-semibold">Enter your password to confirm:</label>
                        <input type="password" class="form-control" id="confirm_password" name="password" placeholder="Type your password..." required>
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                <button type="submit" form="deleteAccountForm" class="btn btn-danger">
                    <i class="bi bi-trash me-1"></i> Permanently Delete
                </button>
            </div>
        </div>
    </div>
</div>
@endif

<!-- Deactivate Account Modal -->
<div class="modal fade" id="deactivateModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header bg-warning">
                <h5 class="modal-title">
                    <i class="bi bi-pause-circle me-2"></i>Deactivate Account
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <p><strong>Are you sure you want to deactivate your account?</strong></p>
                <p>Your account will be hidden and you won't be able to access the platform until you reactivate.</p>
                <ul class="text-muted small">
                    <li>Your data will be preserved</li>
                    <li>You can reactivate anytime by logging in</li>
                    <li>Instructors and admins won't see your profile</li>
                </ul>
                <form id="deactivateForm" action="{{ route('profile.deactivate') }}" method="POST">
                    @csrf
                    <div class="mb-3">
                        <label for="deactivate_password" class="form-label fw-semibold">Enter your password to confirm:</label>
                        <input type="password" class="form-control" id="deactivate_password" name="password" placeholder="Type your password..." required>
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                <button type="submit" form="deactivateForm" class="btn btn-warning">
                    <i class="bi bi-pause-circle me-1"></i> Deactivate Account
                </button>
            </div>
        </div>
    </div>
</div>

<!-- 2FA Modal -->
<div class="modal fade" id="twoFactorModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header bg-primary text-white">
                <h5 class="modal-title">
                    <i class="bi bi-shield-lock me-2"></i>Enable Two-Factor Authentication
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body text-center">
                <div class="mb-3">
                    <div class="bg-light p-4 rounded">
                        {!! $qrCode ?? '<i class="bi bi-qr-code fs-1 text-muted"></i><p class="mt-2">QR Code will be generated</p>' !!}
                    </div>
                </div>
                <p>Scan this QR code with your authenticator app</p>
                <p class="text-muted small">(Google Authenticator, Authy, or Microsoft Authenticator)</p>
                <p class="text-muted small">Or enter this code manually: <strong>ABCD EFGH IJKL MNOP</strong></p>
                <hr>
                <form id="verify2FAForm">
                    <div class="mb-3">
                        <label for="verification_code" class="form-label fw-semibold">Verification Code</label>
                        <input type="text" class="form-control text-center" id="verification_code"
                               placeholder="Enter 6-digit code" maxlength="6" required>
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                <button type="button" class="btn btn-primary" onclick="verify2FA()">
                    <i class="bi bi-shield-check me-1"></i> Verify & Enable
                </button>
            </div>
        </div>
    </div>
</div>
@endsection

@push('styles')
<style>
    .settings-nav .list-group-item {
        border-left: 3px solid transparent;
        padding: 0.75rem 1.25rem;
        transition: all 0.3s;
    }

    .settings-nav .list-group-item.active {
        border-left-color: var(--primary-color);
        background-color: rgba(78, 115, 223, 0.08);
        color: var(--primary-color);
    }

    .settings-nav .list-group-item:hover:not(.active) {
        background-color: rgba(0, 0, 0, 0.02);
    }

    .form-switch .form-check-input {
        width: 3rem;
        height: 1.5rem;
    }

    .form-switch .form-check-input:checked {
        background-color: var(--primary-color);
        border-color: var(--primary-color);
    }
</style>
@endpush

@push('scripts')
<script>
    // Password strength checker
    document.getElementById('new_password')?.addEventListener('input', function(e) {
        const password = e.target.value;
        const strengthBar = document.getElementById('strength-bar');
        const strengthText = document.getElementById('strength-text');

        let strength = 0;

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
    document.querySelectorAll('.settings-nav .list-group-item').forEach(item => {
        item.addEventListener('click', function(e) {
            e.preventDefault();

            document.querySelectorAll('.settings-nav .list-group-item').forEach(i => i.classList.remove('active'));
            this.classList.add('active');

            const target = this.getAttribute('href').substring(1);
            document.querySelectorAll('.tab-pane').forEach(tab => tab.classList.remove('show', 'active'));
            document.getElementById(target).classList.add('show', 'active');

            history.pushState(null, '', this.getAttribute('href'));
        });
    });

    // Handle hash on load
    if (window.location.hash) {
        const hash = window.location.hash.substring(1);
        const targetLink = document.querySelector(`.settings-nav .list-group-item[href="#${hash}"]`);
        if (targetLink) {
            targetLink.click();
        }
    }



    
    
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
                    if (response.status === 'success') {
                        toastr.success('Session revoked successfully');
                        setTimeout(() => location.reload(), 1500);
                    }
                },
                error: function() {
                    toastr.error('Error revoking session');
                }
            });
        }
    }

    function revokeAllSessions() {
        if (confirm('Are you sure you want to revoke all sessions except this one?')) {
            $.ajax({
                url: '{{ route("profile.revoke-all-sessions") }}',
                method: 'POST',
                data: {
                    _token: '{{ csrf_token() }}'
                },
                success: function(response) {
                    if (response.status === 'success') {
                        toastr.success('All other sessions revoked');
                        setTimeout(() => location.reload(), 1500);
                    }
                },
                error: function() {
                    toastr.error('Error revoking sessions');
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
                    setTimeout(() => location.reload(), 1500);
                }
            },
            error: function() {
                toastr.error('Invalid verification code');
            }
        });
    }

    function disable2FA() {
        if (confirm('Are you sure you want to disable two-factor authentication?')) {
            $.ajax({
                url: '{{ route("profile.disable-2fa") }}',
                method: 'POST',
                data: {
                    _token: '{{ csrf_token() }}'
                },
                success: function(response) {
                    if (response.status === 'success') {
                        toastr.success('Two-factor authentication disabled');
                        setTimeout(() => location.reload(), 1500);
                    }
                },
                error: function() {
                    toastr.error('Error disabling two-factor authentication');
                }
            });
        }
    }

    @if(auth()->user()->isAdmin())
    function deleteAccount() {
        $('#deleteAccountModal').modal('show');
    }
    @endif

    function deactivateAccount() {
        $('#deactivateModal').modal('show');
    }
</script>
@endpush