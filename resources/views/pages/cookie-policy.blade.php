@extends('layouts.' . ($role !== 'guest' ? $role : 'app'))

@section('title', 'Cookie Policy')

@section('content')
<div class="container py-5">
    <div class="row justify-content-center">
        <div class="col-lg-10">
            <!-- Header -->
            <div class="text-center mb-5">
                <h1 class="display-4 fw-bold">Cookie Policy</h1>
                <p class="lead text-muted">Last updated: {{ date('F d, Y') }}</p>
                <div class="mt-3">
                    <span class="badge bg-primary">Version 1.0</span>
                </div>
            </div>

            <!-- Introduction -->
            <div class="card shadow-sm mb-4">
                <div class="card-body p-4">
                    <h2 class="h4 fw-bold mb-3">What Are Cookies?</h2>
                    <p>Cookies are small text files that are stored on your computer or mobile device when you visit a website. They are widely used to make websites work more efficiently and provide useful information to website owners.</p>
                    <p>This Cookie Policy explains how {{ setting('app_name', 'CBT System') }} uses cookies and similar technologies to recognize you when you visit our platform. It explains what these technologies are and why we use them, as well as your rights to control our use of them.</p>
                </div>
            </div>

            <!-- Cookie Usage Table -->
            <div class="card shadow-sm mb-4">
                <div class="card-body p-4">
                    <h2 class="h4 fw-bold mb-3">How We Use Cookies</h2>

                    <div class="table-responsive">
                        <table class="table table-bordered">
                            <thead class="table-light">
                                <tr>
                                    <th>Cookie Type</th>
                                    <th>Purpose</th>
                                    <th>Duration</th>
                                    <th>Examples</th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr>
                                    <td><strong>Essential Cookies</strong></td>
                                    <td>Required for basic platform functionality, authentication, and security</td>
                                    <td>Session / Persistent</td>
                                    <td>session_token, XSRF-TOKEN</td>
                                </tr>
                                <tr>
                                    <td><strong>Preference Cookies</strong></td>
                                    <td>Remember your settings and preferences (language, theme, etc.)</td>
                                    <td>1 year</td>
                                    <td>dark_mode, language</td>
                                </tr>
                                <tr>
                                    <td><strong>Analytics Cookies</strong></td>
                                    <td>Help us understand how visitors interact with our platform</td>
                                    <td>2 years</td>
                                    <td>_ga, _gid, _gat</td>
                                </tr>
                                <tr>
                                    <td><strong>Exam Session Cookies</strong></td>
                                    <td>Track exam progress, prevent cheating, ensure exam integrity</td>
                                    <td>Session</td>
                                    <td>exam_token, question_order</td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>

            <!-- Cookie Categories -->
            <div class="card shadow-sm mb-4">
                <div class="card-body p-4">
                    <h2 class="h4 fw-bold mb-3">Cookie Categories</h2>

                    <div class="accordion" id="cookieAccordion">
                        <div class="accordion-item">
                            <h2 class="accordion-header">
                                <button class="accordion-button" type="button" data-bs-toggle="collapse" data-bs-target="#cookie1">
                                    <i class="bi bi-shield-check text-success me-2"></i>
                                    Essential Cookies
                                </button>
                            </h2>
                            <div id="cookie1" class="accordion-collapse collapse show">
                                <div class="accordion-body">
                                    <p>These cookies are necessary for the platform to function properly. They enable core functionality such as security, network management, and account access. You cannot opt out of these cookies as the platform would not work without them.</p>
                                    <p class="mb-0"><strong>Examples:</strong> Authentication tokens, CSRF protection, session identifiers.</p>
                                </div>
                            </div>
                        </div>

                        <div class="accordion-item">
                            <h2 class="accordion-header">
                                <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#cookie2">
                                    <i class="bi bi-palette text-primary me-2"></i>
                                    Preference Cookies
                                </button>
                            </h2>
                            <div id="cookie2" class="accordion-collapse collapse">
                                <div class="accordion-body">
                                    <p>These cookies remember your choices and preferences to enhance your experience. They may remember your theme preference (light/dark mode), language selection, and dashboard layout choices.</p>
                                    <p class="mb-0"><strong>Duration:</strong> Up to 1 year</p>
                                </div>
                            </div>
                        </div>

                        <div class="accordion-item">
                            <h2 class="accordion-header">
                                <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#cookie3">
                                    <i class="bi bi-bar-chart text-info me-2"></i>
                                    Analytics Cookies
                                </button>
                            </h2>
                            <div id="cookie3" class="accordion-collapse collapse">
                                <div class="accordion-body">
                                    <p>We use analytics cookies to understand how visitors use our platform, which pages are most popular, and what features users engage with. This helps us improve the platform experience.</p>
                                    <p>We currently use:</p>
                                    <ul>
                                        <li><strong>Google Analytics:</strong> Tracks page views, user behavior, and platform performance</li>
                                        <li><strong>Custom Analytics:</strong> Our internal tracking for feature usage</li>
                                    </ul>
                                </div>
                            </div>
                        </div>

                        <div class="accordion-item">
                            <h2 class="accordion-header">
                                <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#cookie4">
                                    <i class="bi bi-pencil-square text-warning me-2"></i>
                                    Exam Session Cookies
                                </button>
                            </h2>
                            <div id="cookie4" class="accordion-collapse collapse">
                                <div class="accordion-body">
                                    <p>These cookies are essential for conducting secure online examinations. They track your exam progress, save answers, monitor for potential cheating, and ensure exam integrity. These cookies are temporary and are deleted when you submit or complete an exam.</p>
                                    <p class="mb-0"><strong>Note:</strong> These cookies are required for taking exams and cannot be disabled during an active exam session.</p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Cookie Consent Banner Preview -->
            <div class="card shadow-sm mb-4">
                <div class="card-body p-4">
                    <h2 class="h4 fw-bold mb-3">Cookie Consent Banner</h2>
                    <p>When you first visit our platform, you'll see a cookie consent banner that looks like this:</p>

                    <div class="border rounded p-3 bg-light">
                        <div class="d-flex flex-wrap align-items-center justify-content-between">
                            <div class="d-flex align-items-center mb-2 mb-md-0">
                                <i class="bi bi-cookie text-warning fs-1 me-3"></i>
                                <div>
                                    <h6 class="mb-1">We use cookies 🍪</h6>
                                    <p class="small text-muted mb-0">We use cookies to enhance your experience and analyze platform usage.</p>
                                </div>
                            </div>
                            <div class="d-flex gap-2">
                                <button class="btn btn-sm btn-outline-secondary">Cookie Settings</button>
                                <button class="btn btn-sm btn-primary">Accept All</button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Cookie Settings -->
            <div class="card shadow-sm mb-4">
                <div class="card-body p-4">
                    <h2 class="h4 fw-bold mb-3">Manage Cookie Preferences</h2>
                    <p>You can control and manage your cookie preferences at any time. Use the toggles below to enable or disable non-essential cookies.</p>

                    <div class="cookie-settings mt-4">
                        <div class="d-flex justify-content-between align-items-center p-3 bg-light rounded mb-2">
                            <div>
                                <h6 class="mb-1">Essential Cookies</h6>
                                <small class="text-muted">Required for basic platform functionality</small>
                            </div>
                            <span class="badge bg-secondary">Always Active</span>
                        </div>

                        <div class="d-flex justify-content-between align-items-center p-3 bg-light rounded mb-2">
                            <div>
                                <h6 class="mb-1">Preference Cookies</h6>
                                <small class="text-muted">Remember your settings and choices</small>
                            </div>
                            <div class="form-check form-switch">
                                <input class="form-check-input" type="checkbox" id="preferenceCookies" checked>
                            </div>
                        </div>

                        <div class="d-flex justify-content-between align-items-center p-3 bg-light rounded mb-2">
                            <div>
                                <h6 class="mb-1">Analytics Cookies</h6>
                                <small class="text-muted">Help us improve our platform</small>
                            </div>
                            <div class="form-check form-switch">
                                <input class="form-check-input" type="checkbox" id="analyticsCookies" checked>
                            </div>
                        </div>

                        <div class="d-flex justify-content-between align-items-center p-3 bg-light rounded">
                            <div>
                                <h6 class="mb-1">Marketing Cookies</h6>
                                <small class="text-muted">Used for targeted advertising</small>
                            </div>
                            <div class="form-check form-switch">
                                <input class="form-check-input" type="checkbox" id="marketingCookies">
                            </div>
                        </div>
                    </div>

                    <div class="text-end mt-4">
                        <button class="btn btn-primary" onclick="saveCookiePreferences()">
                            Save Preferences
                        </button>
                    </div>
                </div>
            </div>

            <!-- Third-Party Cookies -->
            <div class="card shadow-sm mb-4">
                <div class="card-body p-4">
                    <h2 class="h4 fw-bold mb-3">Third-Party Cookies</h2>
                    <p>In addition to our own cookies, we may also use various third-party cookies to report usage statistics and improve the platform. These include:</p>

                    <ul>
                        <li><strong>Google Analytics:</strong> Used to analyze platform traffic and user behavior</li>
                        <li><strong>Cloudflare:</strong> Security and performance optimization</li>
                        <li><strong>Stripe/Payment Processors:</strong> If you make payments on our platform</li>
                    </ul>

                    <p class="mb-0">These third parties have their own privacy policies and cookie policies. We recommend reviewing them for more information.</p>
                </div>
            </div>

            <!-- How to Control Cookies -->
            <div class="card shadow-sm mb-4">
                <div class="card-body p-4">
                    <h2 class="h4 fw-bold mb-3">How to Control Cookies</h2>

                    <p>You can control and manage cookies in various ways:</p>

                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <div class="p-3 bg-light rounded h-100">
                                <i class="bi bi-browser-chrome fs-2 text-primary mb-2"></i>
                                <h6 class="fw-bold">Browser Settings</h6>
                                <p class="small text-muted">Most browsers allow you to view, delete, and block cookies through their settings.</p>
                                <ul class="small">
                                    <li>Chrome: Settings → Privacy and Security</li>
                                    <li>Firefox: Options → Privacy & Security</li>
                                    <li>Safari: Preferences → Privacy</li>
                                    <li>Edge: Settings → Cookies and site permissions</li>
                                </ul>
                            </div>
                        </div>
                        <div class="col-md-6 mb-3">
                            <div class="p-3 bg-light rounded h-100">
                                <i class="bi bi-sliders2 fs-2 text-primary mb-2"></i>
                                <h6 class="fw-bold">Platform Controls</h6>
                                <p class="small text-muted">Use our cookie settings panel above to manage non-essential cookies.</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Updates to This Policy -->
            <div class="card shadow-sm mb-4">
                <div class="card-body p-4">
                    <h2 class="h4 fw-bold mb-3">Updates to This Policy</h2>
                    <p>We may update this Cookie Policy from time to time to reflect changes in technology, regulation, or our business practices. Any changes will be posted on this page with an updated revision date.</p>
                    <p class="mb-0">We encourage you to review this Cookie Policy periodically to stay informed about how we use cookies.</p>
                </div>
            </div>

            <!-- Contact -->
            <div class="card shadow-sm mb-4">
                <div class="card-body p-4">
                    <h2 class="h4 fw-bold mb-3">Contact Us</h2>
                    <p>If you have questions about our use of cookies, please contact us:</p>

                    <div class="d-flex align-items-center mb-3">
                        <i class="bi bi-envelope-fill fs-4 text-primary me-3"></i>
                        <span>{{ setting('contact_email', 'privacy@cbt.com') }}</span>
                    </div>
                    <div class="d-flex align-items-center">
                        <i class="bi bi-building fs-4 text-primary me-3"></i>
                        <span>{{ setting('address', '123 Education St, Learning City, ED 12345') }}</span>
                    </div>
                </div>
            </div>

            <!-- Last Updated -->
            <div class="text-center text-muted mt-4">
                <p><i class="bi bi-clock me-1"></i> This cookie policy was last updated on {{ date('F d, Y') }}</p>
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script>
    function saveCookiePreferences() {
        const preferences = {
            essential: true, // Always true
            preference: document.getElementById('preferenceCookies').checked,
            analytics: document.getElementById('analyticsCookies').checked,
            marketing: document.getElementById('marketingCookies').checked
        };

        // Here you would send these preferences to your server
        console.log('Saving cookie preferences:', preferences);

        // Show success message
        alert('Your cookie preferences have been saved!');
    }

    // Simulate loading saved preferences
    document.addEventListener('DOMContentLoaded', function() {
        // You would load saved preferences from server here
        console.log('Loading cookie preferences...');
    });
</script>
@endpush
@endsection
