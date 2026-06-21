@extends('layouts.admin')

@section('title', 'System Settings')

@section('page-title', 'System Settings')
@section('page-actions')
    <button type="button" class="btn btn-danger" onclick="resetSettings()">
        <i class="bi bi-arrow-counterclockwise"></i> Reset to Default
    </button>
@endsection

@section('content')
<div class="row">
    <!-- Settings Sidebar -->
    <div class="col-lg-3 mb-4">
        <div class="card shadow-sm sticky-top" style="top: 80px; z-index: 1;">
            <div class="card-header py-3">
                <h6 class="m-0 font-weight-bold text-primary">
                    <i class="bi bi-gear me-2"></i>Settings Menu
                </h6>
            </div>
            <div class="list-group list-group-flush settings-nav">
                <a href="#general" class="list-group-item list-group-item-action active" data-bs-toggle="tab">
                    <i class="bi bi-sliders2 me-2"></i>General
                    <span class="badge bg-primary rounded-pill float-end">1</span>
                </a>
                <a href="#theme" class="list-group-item list-group-item-action" data-bs-toggle="tab">
                    <i class="bi bi-palette me-2"></i>Theme
                    <span class="badge bg-primary rounded-pill float-end">2</span>
                </a>
                <a href="#system" class="list-group-item list-group-item-action" data-bs-toggle="tab">
                    <i class="bi bi-cpu me-2"></i>System
                    <span class="badge bg-primary rounded-pill float-end">3</span>
                </a>
                <a href="#exam" class="list-group-item list-group-item-action" data-bs-toggle="tab">
                    <i class="bi bi-pencil-square me-2"></i>Exam Settings
                    <span class="badge bg-primary rounded-pill float-end">4</span>
                </a>
                <a href="#seo" class="list-group-item list-group-item-action" data-bs-toggle="tab">
                    <i class="bi bi-search me-2"></i>SEO
                    <span class="badge bg-primary rounded-pill float-end">5</span>
                </a>
                <a href="#performance" class="list-group-item list-group-item-action" data-bs-toggle="tab">
                    <i class="bi bi-speedometer2 me-2"></i>Performance
                    <span class="badge bg-primary rounded-pill float-end">6</span>
                </a>
            </div>
            <div class="card-footer bg-transparent border-0 pt-3">
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
                            <i class="bi bi-sliders2 me-2"></i>General Settings
                        </h6>
                        <span class="badge bg-primary">1</span>
                    </div>
                    <div class="card-body">
                        <form action="{{ route('admin.settings.update') }}" method="POST" enctype="multipart/form-data" class="settings-form">
                            @csrf
                            <input type="hidden" name="settings[group]" value="general">

                            <div class="row">
                                <div class="col-md-6">
                                    <div class="mb-3">
                                        <label class="form-label fw-semibold">Application Name</label>
                                        <div class="input-group">
                                            <span class="input-group-text"><i class="bi bi-tag"></i></span>
                                            <input type="text" class="form-control" name="settings[app_name]"
                                                   value="{{ setting('app_name') }}" placeholder="Enter application name">
                                        </div>
                                        <small class="text-muted">This name appears throughout the application.</small>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="mb-3">
                                        <label class="form-label fw-semibold">Application Description</label>
                                        <div class="input-group">
                                            <span class="input-group-text"><i class="bi bi-align-start"></i></span>
                                            <input type="text" class="form-control" name="settings[app_description]"
                                                   value="{{ setting('app_description') }}" placeholder="Brief description">
                                        </div>
                                        <small class="text-muted">Used for meta description and branding.</small>
                                    </div>
                                </div>
                            </div>

                            <div class="row">
                                <div class="col-md-6">
                                    <div class="mb-3">
                                        <label class="form-label fw-semibold">Application Logo</label>
                                        <div class="d-flex align-items-center gap-3 flex-wrap">
                                            @if(setting('app_logo'))
                                                <img src="{{ setting('app_logo') }}" alt="Logo" height="60" class="rounded border p-1">
                                            @else
                                                <div class="bg-light rounded p-3 text-center" style="width: 100px;">
                                                    <i class="bi bi-image text-muted fs-1"></i>
                                                </div>
                                            @endif
                                            <div class="flex-grow-1">
                                                <input type="file" class="form-control" id="logo" accept="image/*">
                                                <small class="text-muted">Recommended: 200x200px PNG or JPG</small>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="mb-3">
                                        <label class="form-label fw-semibold">Favicon</label>
                                        <div class="d-flex align-items-center gap-3 flex-wrap">
                                            @if(setting('app_favicon'))
                                                <img src="{{ setting('app_favicon') }}" alt="Favicon" height="32" class="border rounded p-1">
                                            @else
                                                <div class="bg-light rounded p-2 text-center" style="width: 48px;">
                                                    <i class="bi bi-file-text text-muted"></i>
                                                </div>
                                            @endif
                                            <div class="flex-grow-1">
                                                <input type="file" class="form-control" id="favicon" accept="image/*">
                                                <small class="text-muted">Recommended: 32x32px ICO or PNG</small>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <div class="row">
                                <div class="col-md-6">
                                    <div class="mb-3">
                                        <label class="form-label fw-semibold">Contact Email</label>
                                        <div class="input-group">
                                            <span class="input-group-text"><i class="bi bi-envelope"></i></span>
                                            <input type="email" class="form-control" name="settings[contact_email]"
                                                   value="{{ setting('contact_email') }}" placeholder="admin@example.com">
                                        </div>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="mb-3">
                                        <label class="form-label fw-semibold">Contact Phone</label>
                                        <div class="input-group">
                                            <span class="input-group-text"><i class="bi bi-telephone"></i></span>
                                            <input type="text" class="form-control" name="settings[contact_phone]"
                                                   value="{{ setting('contact_phone') }}" placeholder="+1 234 567 8900">
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <div class="mb-3">
                                <label class="form-label fw-semibold">Address</label>
                                <div class="input-group">
                                    <span class="input-group-text"><i class="bi bi-geo-alt"></i></span>
                                    <textarea class="form-control" name="settings[address]" rows="2" placeholder="123 Main Street, City, Country">{{ setting('address') }}</textarea>
                                </div>
                            </div>

                            <div class="d-flex justify-content-end gap-2">
                                <button type="reset" class="btn btn-secondary">Reset</button>
                                <button type="submit" class="btn btn-primary">
                                    <i class="bi bi-save me-1"></i> Save General Settings
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>

            <!-- ==================== THEME SETTINGS ==================== -->
            <div class="tab-pane fade" id="theme">
                <div class="card shadow-sm">
                    <div class="card-header py-3 d-flex justify-content-between align-items-center">
                        <h6 class="m-0 font-weight-bold text-primary">
                            <i class="bi bi-palette me-2"></i>Theme Settings
                        </h6>
                        <span class="badge bg-primary">2</span>
                    </div>
                    <div class="card-body">
                        <form action="{{ route('admin.settings.update') }}" method="POST" class="settings-form">
                            @csrf
                            <input type="hidden" name="settings[group]" value="theme">

                            <!-- Theme Mode -->
                            <div class="mb-4">
                                <label class="form-label fw-semibold">Theme Mode</label>
                                <div class="theme-mode-selector d-flex gap-3">
                                    <label class="theme-option card p-3 text-center cursor-pointer" style="flex:1; cursor:pointer;">
                                        <input type="radio" name="settings[theme_mode]" value="light" 
                                               {{ setting('theme_mode') == 'light' ? 'checked' : '' }} class="d-none">
                                        <i class="bi bi-sun fs-2 d-block mb-1"></i>
                                        <span class="d-block">Light</span>
                                    </label>
                                    <label class="theme-option card p-3 text-center cursor-pointer" style="flex:1; cursor:pointer;">
                                        <input type="radio" name="settings[theme_mode]" value="dark" 
                                               {{ setting('theme_mode') == 'dark' ? 'checked' : '' }} class="d-none">
                                        <i class="bi bi-moon fs-2 d-block mb-1"></i>
                                        <span class="d-block">Dark</span>
                                    </label>
                                    <label class="theme-option card p-3 text-center cursor-pointer" style="flex:1; cursor:pointer;">
                                        <input type="radio" name="settings[theme_mode]" value="auto" 
                                               {{ setting('theme_mode') == 'auto' ? 'checked' : '' }} class="d-none">
                                        <i class="bi bi-laptop fs-2 d-block mb-1"></i>
                                        <span class="d-block">Auto</span>
                                    </label>
                                </div>
                            </div>

                            <!-- Color Palette -->
                            <h6 class="fw-semibold mb-3">Color Palette</h6>
                            <div class="row g-3">
                                <div class="col-md-3">
                                    <div class="mb-3">
                                        <label class="form-label small">Primary</label>
                                        <input type="color" class="form-control form-control-color" style="height: 50px;"
                                               name="settings[primary_color]" value="{{ setting('primary_color', '#4e73df') }}">
                                    </div>
                                </div>
                                <div class="col-md-3">
                                    <div class="mb-3">
                                        <label class="form-label small">Secondary</label>
                                        <input type="color" class="form-control form-control-color" style="height: 50px;"
                                               name="settings[secondary_color]" value="{{ setting('secondary_color', '#858796') }}">
                                    </div>
                                </div>
                                <div class="col-md-3">
                                    <div class="mb-3">
                                        <label class="form-label small">Success</label>
                                        <input type="color" class="form-control form-control-color" style="height: 50px;"
                                               name="settings[success_color]" value="{{ setting('success_color', '#1cc88a') }}">
                                    </div>
                                </div>
                                <div class="col-md-3">
                                    <div class="mb-3">
                                        <label class="form-label small">Info</label>
                                        <input type="color" class="form-control form-control-color" style="height: 50px;"
                                               name="settings[info_color]" value="{{ setting('info_color', '#36b9cc') }}">
                                    </div>
                                </div>
                                <div class="col-md-3">
                                    <div class="mb-3">
                                        <label class="form-label small">Warning</label>
                                        <input type="color" class="form-control form-control-color" style="height: 50px;"
                                               name="settings[warning_color]" value="{{ setting('warning_color', '#f6c23e') }}">
                                    </div>
                                </div>
                                <div class="col-md-3">
                                    <div class="mb-3">
                                        <label class="form-label small">Danger</label>
                                        <input type="color" class="form-control form-control-color" style="height: 50px;"
                                               name="settings[danger_color]" value="{{ setting('danger_color', '#e74a3b') }}">
                                    </div>
                                </div>
                            </div>

                            <!-- Sidebar Colors -->
                            <h6 class="fw-semibold mb-3 mt-4">Sidebar Colors</h6>
                            <div class="row">
                                <div class="col-md-6">
                                    <div class="mb-3">
                                        <label class="form-label small">Sidebar Background</label>
                                        <input type="color" class="form-control form-control-color" style="height: 50px;"
                                               name="settings[sidebar_bg]" value="{{ setting('sidebar_bg', '#1a1e2b') }}">
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="mb-3">
                                        <label class="form-label small">Sidebar Text</label>
                                        <input type="color" class="form-control form-control-color" style="height: 50px;"
                                               name="settings[sidebar_text]" value="{{ setting('sidebar_text', '#ffffff') }}">
                                    </div>
                                </div>
                            </div>

                            <!-- Live Preview -->
                            <div class="theme-preview mt-4 p-4 rounded border">
                                <h6 class="fw-semibold mb-3">Live Preview</h6>
                                <div class="d-flex flex-wrap gap-3">
                                    <button class="btn" style="background: var(--primary-color); color: white;">Primary</button>
                                    <button class="btn" style="background: var(--success-color); color: white;">Success</button>
                                    <button class="btn" style="background: var(--info-color); color: white;">Info</button>
                                    <button class="btn" style="background: var(--warning-color); color: black;">Warning</button>
                                    <button class="btn" style="background: var(--danger-color); color: white;">Danger</button>
                                </div>
                                <div class="mt-3 d-flex gap-2">
                                    <span class="badge" style="background: var(--primary-color);">Primary Badge</span>
                                    <span class="badge" style="background: var(--success-color);">Success Badge</span>
                                    <span class="badge" style="background: var(--info-color);">Info Badge</span>
                                </div>
                            </div>

                            <div class="d-flex justify-content-end gap-2 mt-4">
                                <button type="reset" class="btn btn-secondary">Reset</button>
                                <button type="submit" class="btn btn-primary">
                                    <i class="bi bi-save me-1"></i> Save Theme Settings
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>

            <!-- ==================== SYSTEM SETTINGS ==================== -->
            <div class="tab-pane fade" id="system">
                <div class="card shadow-sm">
                    <div class="card-header py-3 d-flex justify-content-between align-items-center">
                        <h6 class="m-0 font-weight-bold text-primary">
                            <i class="bi bi-cpu me-2"></i>System Settings
                        </h6>
                        <span class="badge bg-primary">3</span>
                    </div>
                    <div class="card-body">
                        <form action="{{ route('admin.settings.update') }}" method="POST" class="settings-form">
                            @csrf
                            <input type="hidden" name="settings[group]" value="system">

                            <div class="row">
                                <div class="col-md-6">
                                    <div class="mb-3">
                                        <label class="form-label fw-semibold">Timezone</label>
                                        <select class="form-select" name="settings[timezone]">
                                            @foreach(timezone_identifiers_list() as $tz)
                                                <option value="{{ $tz }}" {{ setting('timezone', 'UTC') == $tz ? 'selected' : '' }}>
                                                    {{ $tz }}
                                                </option>
                                            @endforeach
                                        </select>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="mb-3">
                                        <label class="form-label fw-semibold">Date Format</label>
                                        <input type="text" class="form-control" name="settings[date_format]"
                                               value="{{ setting('date_format', 'd M Y') }}" placeholder="d M Y">
                                        <small class="text-muted">PHP date format. e.g., d M Y, Y-m-d</small>
                                    </div>
                                </div>
                            </div>

                            <div class="row">
                                <div class="col-md-6">
                                    <div class="mb-3">
                                        <label class="form-label fw-semibold">Time Format</label>
                                        <input type="text" class="form-control" name="settings[time_format]"
                                               value="{{ setting('time_format', 'H:i') }}" placeholder="H:i">
                                        <small class="text-muted">PHP time format. e.g., H:i, h:i A</small>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="mb-3">
                                        <label class="form-label fw-semibold">Items Per Page</label>
                                        <input type="number" class="form-control" name="settings[items_per_page]"
                                               value="{{ setting('items_per_page', 15) }}" min="5" max="100">
                                        <small class="text-muted">Number of items shown per page in lists</small>
                                    </div>
                                </div>
                            </div>

                            <div class="row">
                                <div class="col-md-6">
                                    <div class="form-check form-switch mb-3">
                                        <input class="form-check-input" type="checkbox" name="settings[enable_registration]"
                                               value="1" {{ setting('enable_registration', true) ? 'checked' : '' }}>
                                        <label class="form-check-label">Enable User Registration</label>
                                        <div class="text-muted small">Allow new users to register</div>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="form-check form-switch mb-3">
                                        <input class="form-check-input" type="checkbox" name="settings[enable_notifications]"
                                               value="1" {{ setting('enable_notifications', true) ? 'checked' : '' }}>
                                        <label class="form-check-label">Enable Notifications</label>
                                        <div class="text-muted small">Show notifications to users</div>
                                    </div>
                                </div>
                            </div>

                            <div class="form-check form-switch mb-3">
                                <input class="form-check-input" type="checkbox" name="settings[maintenance_mode]"
                                       value="1" {{ setting('maintenance_mode', false) ? 'checked' : '' }}>
                                <label class="form-check-label text-danger fw-semibold">Maintenance Mode</label>
                                <div class="text-muted small">Put the application in maintenance mode</div>
                            </div>

                            <div class="d-flex justify-content-end gap-2">
                                <button type="reset" class="btn btn-secondary">Reset</button>
                                <button type="submit" class="btn btn-primary">
                                    <i class="bi bi-save me-1"></i> Save System Settings
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>

            <!-- ==================== EXAM SETTINGS ==================== -->
            <div class="tab-pane fade" id="exam">
                <div class="card shadow-sm">
                    <div class="card-header py-3 d-flex justify-content-between align-items-center">
                        <h6 class="m-0 font-weight-bold text-primary">
                            <i class="bi bi-pencil-square me-2"></i>Exam Settings
                        </h6>
                        <span class="badge bg-primary">4</span>
                    </div>
                    <div class="card-body">
                        <form action="{{ route('admin.settings.update') }}" method="POST" class="settings-form">
                            @csrf
                            <input type="hidden" name="settings[group]" value="exam">

                            <div class="row">
                                <div class="col-md-6">
                                    <div class="mb-3">
                                        <label class="form-label fw-semibold">Default Exam Duration</label>
                                        <div class="input-group">
                                            <input type="number" class="form-control" name="settings[default_exam_duration]"
                                                   value="{{ setting('default_exam_duration', 60) }}" min="1" max="1440">
                                            <span class="input-group-text">minutes</span>
                                        </div>
                                        <small class="text-muted">Default duration for new exams</small>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="mb-3">
                                        <label class="form-label fw-semibold">Default Passing Percentage</label>
                                        <div class="input-group">
                                            <input type="number" class="form-control" name="settings[default_passing_percentage]"
                                                   value="{{ setting('default_passing_percentage', 40) }}" min="0" max="100">
                                            <span class="input-group-text">%</span>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <div class="row">
                                <div class="col-md-6">
                                    <div class="mb-3">
                                        <label class="form-label fw-semibold">Default Negative Marking</label>
                                        <div class="input-group">
                                            <input type="number" step="0.25" class="form-control" name="settings[default_negative_marking]"
                                                   value="{{ setting('default_negative_marking', 0) }}" min="0" max="100">
                                            <span class="input-group-text">marks</span>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="mb-3">
                                        <label class="form-label fw-semibold">Max Tab Switches Allowed</label>
                                        <input type="number" class="form-control" name="settings[max_tab_switches]"
                                               value="{{ setting('max_tab_switches', 3) }}" min="0" max="10">
                                        <small class="text-muted">0 = unlimited</small>
                                    </div>
                                </div>
                            </div>

                            <div class="row">
                                <div class="col-md-6">
                                    <div class="form-check form-switch mb-3">
                                        <input class="form-check-input" type="checkbox" name="settings[enable_proctoring]"
                                               value="1" {{ setting('enable_proctoring', false) ? 'checked' : '' }}>
                                        <label class="form-check-label">Enable Proctoring</label>
                                        <div class="text-muted small">Monitor students during exams</div>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="form-check form-switch mb-3">
                                        <input class="form-check-input" type="checkbox" name="settings[auto_submit_on_timeout]"
                                               value="1" {{ setting('auto_submit_on_timeout', true) ? 'checked' : '' }}>
                                        <label class="form-check-label">Auto Submit on Timeout</label>
                                        <div class="text-muted small">Auto-submit exam when time expires</div>
                                    </div>
                                </div>
                            </div>

                            <div class="d-flex justify-content-end gap-2">
                                <button type="reset" class="btn btn-secondary">Reset</button>
                                <button type="submit" class="btn btn-primary">
                                    <i class="bi bi-save me-1"></i> Save Exam Settings
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>

            <!-- ==================== SEO SETTINGS ==================== -->
            <div class="tab-pane fade" id="seo">
                <div class="card shadow-sm">
                    <div class="card-header py-3 d-flex justify-content-between align-items-center">
                        <h6 class="m-0 font-weight-bold text-primary">
                            <i class="bi bi-search me-2"></i>SEO Settings
                        </h6>
                        <span class="badge bg-primary">5</span>
                    </div>
                    <div class="card-body">
                        <form action="{{ route('admin.settings.update') }}" method="POST" class="settings-form">
                            @csrf
                            <input type="hidden" name="settings[group]" value="seo">

                            <div class="mb-3">
                                <label class="form-label fw-semibold">Meta Title</label>
                                <input type="text" class="form-control" name="settings[meta_title]"
                                       value="{{ setting('meta_title', setting('app_name', 'CBT System')) }}" 
                                       placeholder="CBT System - Online Exam Platform">
                                <small class="text-muted">Recommended: 50-60 characters</small>
                            </div>

                            <div class="mb-3">
                                <label class="form-label fw-semibold">Meta Description</label>
                                <textarea class="form-control" name="settings[meta_description]" rows="3" 
                                          placeholder="Advanced Computer Based Testing System">{{ setting('meta_description') }}</textarea>
                                <small class="text-muted">Recommended: 150-160 characters</small>
                            </div>

                            <div class="mb-3">
                                <label class="form-label fw-semibold">Meta Keywords</label>
                                <input type="text" class="form-control" name="settings[meta_keywords]"
                                       value="{{ setting('meta_keywords', 'cbt, exam, online test') }}" 
                                       placeholder="cbt, exam, online test, assessment">
                                <small class="text-muted">Comma separated keywords</small>
                            </div>

                            <div class="mb-3">
                                <label class="form-label fw-semibold">Google Analytics ID</label>
                                <input type="text" class="form-control" name="settings[google_analytics_id]"
                                       value="{{ setting('google_analytics_id') }}" 
                                       placeholder="UA-XXXXXXXXX-X or G-XXXXXXXXXX">
                                <small class="text-muted">Leave empty to disable Google Analytics</small>
                            </div>

                            <div class="d-flex justify-content-end gap-2">
                                <button type="reset" class="btn btn-secondary">Reset</button>
                                <button type="submit" class="btn btn-primary">
                                    <i class="bi bi-save me-1"></i> Save SEO Settings
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>

            <!-- ==================== PERFORMANCE SETTINGS ==================== -->
            <div class="tab-pane fade" id="performance">
                <div class="card shadow-sm">
                    <div class="card-header py-3 d-flex justify-content-between align-items-center">
                        <h6 class="m-0 font-weight-bold text-primary">
                            <i class="bi bi-speedometer2 me-2"></i>Performance Settings
                        </h6>
                        <span class="badge bg-primary">6</span>
                    </div>
                    <div class="card-body">
                        <form action="{{ route('admin.settings.update') }}" method="POST" class="settings-form">
                            @csrf
                            <input type="hidden" name="settings[group]" value="performance">

                            <div class="form-check form-switch mb-3">
                                <input class="form-check-input" type="checkbox" name="settings[cache_enabled]"
                                       value="1" {{ setting('cache_enabled', true) ? 'checked' : '' }}>
                                <label class="form-check-label fw-semibold">Enable Cache</label>
                                <div class="text-muted small">Cache database queries and views for better performance</div>
                            </div>

                            <div class="mb-3">
                                <label class="form-label fw-semibold">Cache TTL</label>
                                <div class="input-group">
                                    <input type="number" class="form-control" name="settings[cache_ttl]"
                                           value="{{ setting('cache_ttl', 3600) }}" min="60" max="86400">
                                    <span class="input-group-text">seconds</span>
                                </div>
                                <small class="text-muted">Time to live for cached items (1 hour = 3600 seconds)</small>
                            </div>

                            <div class="alert alert-info mt-3">
                                <i class="bi bi-info-circle me-2"></i>
                                <strong>Performance Tips:</strong>
                                <ul class="mb-0 mt-1">
                                    <li>Enable cache for better performance</li>
                                    <li>Use Redis or Memcached for production</li>
                                    <li>Optimize database queries with indexes</li>
                                </ul>
                            </div>

                            <div class="d-flex justify-content-end gap-2">
                                <button type="reset" class="btn btn-secondary">Reset</button>
                                <button type="submit" class="btn btn-primary">
                                    <i class="bi bi-save me-1"></i> Save Performance Settings
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>

        </div>
    </div>
</div>

<!-- Reset Confirmation Modal -->
<div class="modal fade" id="resetModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title text-danger">
                    <i class="bi bi-exclamation-triangle me-2"></i>Reset Settings
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <p>Are you sure you want to reset all settings to default values?</p>
                <p class="text-danger mb-0">This action cannot be undone!</p>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                <form action="{{ route('admin.settings.reset') }}" method="POST">
                    @csrf
                    <button type="submit" class="btn btn-danger">
                        <i class="bi bi-arrow-counterclockwise me-1"></i> Reset All
                    </button>
                </form>
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
    
    .theme-option {
        transition: all 0.3s;
        border: 2px solid transparent;
    }
    
    .theme-option:hover {
        border-color: var(--primary-color);
        transform: translateY(-2px);
    }
    
    .theme-option:has(input:checked) {
        border-color: var(--primary-color);
        background-color: rgba(78, 115, 223, 0.08);
    }
    
    .theme-preview {
        background: var(--bg-color, #f8f9fc);
        border-color: var(--border-color, #e9ecef) !important;
    }
    
    [data-theme="dark"] .theme-preview,
    .dark-mode .theme-preview {
        background: #2d323f;
        border-color: #404654 !important;
    }
    
    .settings-form .input-group-text {
        background: transparent;
        border-right: none;
    }
    
    .settings-form .form-control {
        border-left: none;
    }
    
    .settings-form .form-control:focus {
        box-shadow: none;
        border-color: var(--primary-color);
    }
    
    .settings-form .input-group:focus-within .input-group-text {
        border-color: var(--primary-color);
    }
    
    .form-control-color {
        padding: 0.25rem;
        cursor: pointer;
    }
</style>
@endpush

@push('scripts')
<script>
    // Tab switching with active state
    document.querySelectorAll('.settings-nav .list-group-item').forEach(item => {
        item.addEventListener('click', function(e) {
            e.preventDefault();
            
            document.querySelectorAll('.settings-nav .list-group-item').forEach(i => i.classList.remove('active'));
            this.classList.add('active');
            
            const target = this.getAttribute('href').substring(1);
            document.querySelectorAll('.tab-pane').forEach(tab => {
                tab.classList.remove('show', 'active');
            });
            const targetTab = document.getElementById(target);
            if (targetTab) {
                targetTab.classList.add('show', 'active');
            }
            
            // Update URL hash without scrolling
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

    // Logo upload with preview
    document.getElementById('logo')?.addEventListener('change', function(e) {
        const file = this.files[0];
        if (!file) return;
        
        const formData = new FormData();
        formData.append('logo', file);
        
        // Show loading
        const btn = this.closest('.d-flex').querySelector('.btn');
        
        fetch('{{ route("admin.settings.upload-logo") }}', {
            method: 'POST',
            headers: {
                'X-CSRF-TOKEN': '{{ csrf_token() }}'
            },
            body: formData
        })
        .then(response => response.json())
        .then(data => {
            if (data.status === 'success') {
                toastr.success('Logo uploaded successfully');
                setTimeout(() => location.reload(), 1500);
            } else {
                toastr.error(data.message || 'Error uploading logo');
            }
        })
        .catch(() => {
            toastr.error('Error uploading logo');
        });
    });

    // Favicon upload
    document.getElementById('favicon')?.addEventListener('change', function(e) {
        const file = this.files[0];
        if (!file) return;
        
        const formData = new FormData();
        formData.append('favicon', file);
        
        fetch('{{ route("admin.settings.upload-favicon") }}', {
            method: 'POST',
            headers: {
                'X-CSRF-TOKEN': '{{ csrf_token() }}'
            },
            body: formData
        })
        .then(response => response.json())
        .then(data => {
            if (data.status === 'success') {
                toastr.success('Favicon uploaded successfully');
                setTimeout(() => location.reload(), 1500);
            } else {
                toastr.error(data.message || 'Error uploading favicon');
            }
        })
        .catch(() => {
            toastr.error('Error uploading favicon');
        });
    });

    // Reset settings
    function resetSettings() {
        if (confirm('Are you sure you want to reset all settings to default values?')) {
            document.getElementById('resetModal').querySelector('form').submit();
        }
    }

    // Theme option click handler
    document.querySelectorAll('.theme-option').forEach(option => {
        option.addEventListener('click', function() {
            const radio = this.querySelector('input[type="radio"]');
            if (radio) {
                radio.checked = true;
                // Trigger change event
                const event = new Event('change', { bubbles: true });
                radio.dispatchEvent(event);
            }
        });
    });

    // Auto-save theme preview on color change
    document.querySelectorAll('#theme input[type="color"]').forEach(input => {
        input.addEventListener('input', function() {
            const color = this.value;
            const name = this.name.replace('settings[', '').replace(']', '');
            
            // Update CSS variable preview
            const root = document.documentElement;
            const varName = '--' + name.replace('_color', '');
            root.style.setProperty(varName, color);
            
            // Update preview buttons
            const previewBtn = document.querySelector(`.theme-preview .btn-${name.replace('_color', '')}`);
            if (previewBtn) {
                previewBtn.style.backgroundColor = color;
                previewBtn.style.borderColor = color;
            }
        });
    });
</script>
@endpush