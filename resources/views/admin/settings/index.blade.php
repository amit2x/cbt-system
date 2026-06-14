@extends('layouts.admin')

@section('title', 'System Settings')

@section('page-title', 'System Settings')

@section('content')
<div class="row">
    <div class="col-md-3">
        <div class="card shadow mb-4">
            <div class="card-header py-3">
                <h6 class="m-0 font-weight-bold text-primary">Settings Menu</h6>
            </div>
            <div class="list-group list-group-flush">
                <a href="#general" class="list-group-item list-group-item-action" data-bs-toggle="tab">
                    <i class="bi bi-gear me-2"></i>General
                </a>
                <a href="#theme" class="list-group-item list-group-item-action" data-bs-toggle="tab">
                    <i class="bi bi-palette me-2"></i>Theme
                </a>
                <a href="#system" class="list-group-item list-group-item-action" data-bs-toggle="tab">
                    <i class="bi bi-cpu me-2"></i>System
                </a>
                <a href="#exam" class="list-group-item list-group-item-action" data-bs-toggle="tab">
                    <i class="bi bi-pencil-square me-2"></i>Exam Settings
                </a>
                <a href="#seo" class="list-group-item list-group-item-action" data-bs-toggle="tab">
                    <i class="bi bi-search me-2"></i>SEO
                </a>
                <a href="#performance" class="list-group-item list-group-item-action" data-bs-toggle="tab">
                    <i class="bi bi-speedometer2 me-2"></i>Performance
                </a>
            </div>
        </div>
    </div>

    <div class="col-md-9">
        <div class="tab-content">
            <!-- General Settings -->
            <div class="tab-pane fade show active" id="general">
                <div class="card shadow mb-4">
                    <div class="card-header py-3">
                        <h6 class="m-0 font-weight-bold text-primary">General Settings</h6>
                    </div>
                    <div class="card-body">
                        <form action="{{ route('admin.settings.update') }}" method="POST" enctype="multipart/form-data">
                            @csrf

                            <div class="mb-3">
                                <label class="form-label">Application Name</label>
                                <input type="text" class="form-control" name="settings[app_name]"
                                       value="{{ setting('app_name') }}">
                            </div>

                            <div class="mb-3">
                                <label class="form-label">Application Logo</label>
                                <div class="d-flex align-items-center">
                                    @if(setting('app_logo'))
                                        <img src="{{ setting('app_logo') }}" alt="Logo" height="50" class="me-3">
                                    @endif
                                    <input type="file" class="form-control" id="logo" accept="image/*">
                                </div>
                            </div>

                            <div class="mb-3">
                                <label class="form-label">Favicon</label>
                                <div class="d-flex align-items-center">
                                    @if(setting('app_favicon'))
                                        <img src="{{ setting('app_favicon') }}" alt="Favicon" height="32" class="me-3">
                                    @endif
                                    <input type="file" class="form-control" id="favicon" accept="image/*">
                                </div>
                            </div>

                            <div class="mb-3">
                                <label class="form-label">Application Description</label>
                                <textarea class="form-control" name="settings[app_description]" rows="3">{{ setting('app_description') }}</textarea>
                            </div>

                            <div class="row">
                                <div class="col-md-6">
                                    <div class="mb-3">
                                        <label class="form-label">Contact Email</label>
                                        <input type="email" class="form-control" name="settings[contact_email]"
                                               value="{{ setting('contact_email') }}">
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="mb-3">
                                        <label class="form-label">Contact Phone</label>
                                        <input type="text" class="form-control" name="settings[contact_phone]"
                                               value="{{ setting('contact_phone') }}">
                                    </div>
                                </div>
                            </div>

                            <div class="mb-3">
                                <label class="form-label">Address</label>
                                <textarea class="form-control" name="settings[address]" rows="2">{{ setting('address') }}</textarea>
                            </div>

                            <button type="submit" class="btn btn-primary">Save General Settings</button>
                        </form>
                    </div>
                </div>
            </div>

            <!-- Theme Settings -->
            <div class="tab-pane fade" id="theme">
                <div class="card shadow mb-4">
                    <div class="card-header py-3">
                        <h6 class="m-0 font-weight-bold text-primary">Theme Settings</h6>
                    </div>
                    <div class="card-body">
                        <form action="{{ route('admin.settings.update') }}" method="POST">
                            @csrf

                            <div class="mb-3">
                                <label class="form-label">Theme Mode</label>
                                <select class="form-control" name="settings[theme_mode]">
                                    <option value="light" {{ setting('theme_mode') == 'light' ? 'selected' : '' }}>Light</option>
                                    <option value="dark" {{ setting('theme_mode') == 'dark' ? 'selected' : '' }}>Dark</option>
                                    <option value="auto" {{ setting('theme_mode') == 'auto' ? 'selected' : '' }}>Auto (System)</option>
                                </select>
                            </div>

                            <div class="row">
                                <div class="col-md-4">
                                    <div class="mb-3">
                                        <label class="form-label">Primary Color</label>
                                        <input type="color" class="form-control form-control-color"
                                               name="settings[primary_color]" value="{{ setting('primary_color') }}">
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <div class="mb-3">
                                        <label class="form-label">Secondary Color</label>
                                        <input type="color" class="form-control form-control-color"
                                               name="settings[secondary_color]" value="{{ setting('secondary_color') }}">
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <div class="mb-3">
                                        <label class="form-label">Success Color</label>
                                        <input type="color" class="form-control form-control-color"
                                               name="settings[success_color]" value="{{ setting('success_color') }}">
                                    </div>
                                </div>
                            </div>

                            <div class="row">
                                <div class="col-md-4">
                                    <div class="mb-3">
                                        <label class="form-label">Info Color</label>
                                        <input type="color" class="form-control form-control-color"
                                               name="settings[info_color]" value="{{ setting('info_color') }}">
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <div class="mb-3">
                                        <label class="form-label">Warning Color</label>
                                        <input type="color" class="form-control form-control-color"
                                               name="settings[warning_color]" value="{{ setting('warning_color') }}">
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <div class="mb-3">
                                        <label class="form-label">Danger Color</label>
                                        <input type="color" class="form-control form-control-color"
                                               name="settings[danger_color]" value="{{ setting('danger_color') }}">
                                    </div>
                                </div>
                            </div>

                            <div class="row">
                                <div class="col-md-6">
                                    <div class="mb-3">
                                        <label class="form-label">Sidebar Background</label>
                                        <input type="color" class="form-control form-control-color"
                                               name="settings[sidebar_bg]" value="{{ setting('sidebar_bg') }}">
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="mb-3">
                                        <label class="form-label">Sidebar Text Color</label>
                                        <input type="color" class="form-control form-control-color"
                                               name="settings[sidebar_text]" value="{{ setting('sidebar_text') }}">
                                    </div>
                                </div>
                            </div>

                            <button type="submit" class="btn btn-primary">Save Theme Settings</button>
                        </form>
                    </div>
                </div>
            </div>

            <!-- System Settings -->
            <div class="tab-pane fade" id="system">
                <div class="card shadow mb-4">
                    <div class="card-header py-3">
                        <h6 class="m-0 font-weight-bold text-primary">System Settings</h6>
                    </div>
                    <div class="card-body">
                        <form action="{{ route('admin.settings.update') }}" method="POST">
                            @csrf

                            <div class="row">
                                <div class="col-md-6">
                                    <div class="mb-3">
                                        <label class="form-label">Timezone</label>
                                        <select class="form-control" name="settings[timezone]">
                                            @foreach(timezone_identifiers_list() as $tz)
                                                <option value="{{ $tz }}" {{ setting('timezone') == $tz ? 'selected' : '' }}>
                                                    {{ $tz }}
                                                </option>
                                            @endforeach
                                        </select>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="mb-3">
                                        <label class="form-label">Date Format</label>
                                        <input type="text" class="form-control" name="settings[date_format]"
                                               value="{{ setting('date_format') }}">
                                    </div>
                                </div>
                            </div>

                            <div class="row">
                                <div class="col-md-6">
                                    <div class="mb-3">
                                        <label class="form-label">Time Format</label>
                                        <input type="text" class="form-control" name="settings[time_format]"
                                               value="{{ setting('time_format') }}">
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="mb-3">
                                        <label class="form-label">Items Per Page</label>
                                        <input type="number" class="form-control" name="settings[items_per_page]"
                                               value="{{ setting('items_per_page') }}">
                                    </div>
                                </div>
                            </div>

                            <div class="form-check mb-3">
                                <input type="checkbox" class="form-check-input" name="settings[enable_registration]"
                                       value="1" {{ setting('enable_registration') ? 'checked' : '' }}>
                                <label class="form-check-label">Enable User Registration</label>
                            </div>

                            <div class="form-check mb-3">
                                <input type="checkbox" class="form-check-input" name="settings[enable_notifications]"
                                       value="1" {{ setting('enable_notifications') ? 'checked' : '' }}>
                                <label class="form-check-label">Enable Notifications</label>
                            </div>

                            <div class="form-check mb-3">
                                <input type="checkbox" class="form-check-input" name="settings[maintenance_mode]"
                                       value="1" {{ setting('maintenance_mode') ? 'checked' : '' }}>
                                <label class="form-check-label">Maintenance Mode</label>
                            </div>

                            <button type="submit" class="btn btn-primary">Save System Settings</button>
                        </form>
                    </div>
                </div>
            </div>

            <!-- Exam Settings -->
            <div class="tab-pane fade" id="exam">
                <div class="card shadow mb-4">
                    <div class="card-header py-3">
                        <h6 class="m-0 font-weight-bold text-primary">Exam Settings</h6>
                    </div>
                    <div class="card-body">
                        <form action="{{ route('admin.settings.update') }}" method="POST">
                            @csrf

                            <div class="row">
                                <div class="col-md-6">
                                    <div class="mb-3">
                                        <label class="form-label">Default Exam Duration (minutes)</label>
                                        <input type="number" class="form-control" name="settings[default_exam_duration]"
                                               value="{{ setting('default_exam_duration') }}">
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="mb-3">
                                        <label class="form-label">Default Passing Percentage</label>
                                        <input type="number" class="form-control" name="settings[default_passing_percentage]"
                                               value="{{ setting('default_passing_percentage') }}">
                                    </div>
                                </div>
                            </div>

                            <div class="row">
                                <div class="col-md-6">
                                    <div class="mb-3">
                                        <label class="form-label">Default Negative Marking</label>
                                        <input type="number" step="0.25" class="form-control" name="settings[default_negative_marking]"
                                               value="{{ setting('default_negative_marking') }}">
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="mb-3">
                                        <label class="form-label">Max Tab Switches Allowed</label>
                                        <input type="number" class="form-control" name="settings[max_tab_switches]"
                                               value="{{ setting('max_tab_switches') }}">
                                    </div>
                                </div>
                            </div>

                            <div class="form-check mb-3">
                                <input type="checkbox" class="form-check-input" name="settings[enable_proctoring]"
                                       value="1" {{ setting('enable_proctoring') ? 'checked' : '' }}>
                                <label class="form-check-label">Enable Proctoring</label>
                            </div>

                            <div class="form-check mb-3">
                                <input type="checkbox" class="form-check-input" name="settings[auto_submit_on_timeout]"
                                       value="1" {{ setting('auto_submit_on_timeout') ? 'checked' : '' }}>
                                <label class="form-check-label">Auto Submit on Timeout</label>
                            </div>

                            <button type="submit" class="btn btn-primary">Save Exam Settings</button>
                        </form>
                    </div>
                </div>
            </div>

            <!-- SEO Settings -->
            <div class="tab-pane fade" id="seo">
                <div class="card shadow mb-4">
                    <div class="card-header py-3">
                        <h6 class="m-0 font-weight-bold text-primary">SEO Settings</h6>
                    </div>
                    <div class="card-body">
                        <form action="{{ route('admin.settings.update') }}" method="POST">
                            @csrf

                            <div class="mb-3">
                                <label class="form-label">Meta Title</label>
                                <input type="text" class="form-control" name="settings[meta_title]"
                                       value="{{ setting('meta_title') }}">
                            </div>

                            <div class="mb-3">
                                <label class="form-label">Meta Description</label>
                                <textarea class="form-control" name="settings[meta_description]" rows="3">{{ setting('meta_description') }}</textarea>
                            </div>

                            <div class="mb-3">
                                <label class="form-label">Meta Keywords</label>
                                <input type="text" class="form-control" name="settings[meta_keywords]"
                                       value="{{ setting('meta_keywords') }}">
                                <small class="text-muted">Comma separated keywords</small>
                            </div>

                            <div class="mb-3">
                                <label class="form-label">Google Analytics ID</label>
                                <input type="text" class="form-control" name="settings[google_analytics_id]"
                                       value="{{ setting('google_analytics_id') }}">
                            </div>

                            <button type="submit" class="btn btn-primary">Save SEO Settings</button>
                        </form>
                    </div>
                </div>
            </div>

            <!-- Performance Settings -->
            <div class="tab-pane fade" id="performance">
                <div class="card shadow mb-4">
                    <div class="card-header py-3">
                        <h6 class="m-0 font-weight-bold text-primary">Performance Settings</h6>
                    </div>
                    <div class="card-body">
                        <form action="{{ route('admin.settings.update') }}" method="POST">
                            @csrf

                            <div class="form-check mb-3">
                                <input type="checkbox" class="form-check-input" name="settings[cache_enabled]"
                                       value="1" {{ setting('cache_enabled') ? 'checked' : '' }}>
                                <label class="form-check-label">Enable Cache</label>
                            </div>

                            <div class="mb-3">
                                <label class="form-label">Cache TTL (seconds)</label>
                                <input type="number" class="form-control" name="settings[cache_ttl]"
                                       value="{{ setting('cache_ttl') }}">
                            </div>

                            <button type="submit" class="btn btn-primary">Save Performance Settings</button>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
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

    // Logo upload
    document.getElementById('logo')?.addEventListener('change', function(e) {
        const formData = new FormData();
        formData.append('logo', this.files[0]);

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
                location.reload();
            }
        });
    });

    // Favicon upload
    document.getElementById('favicon')?.addEventListener('change', function(e) {
        const formData = new FormData();
        formData.append('favicon', this.files[0]);

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
                location.reload();
            }
        });
    });
</script>
@endpush
