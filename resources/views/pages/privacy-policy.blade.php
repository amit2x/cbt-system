@extends('layouts.' . ($role !== 'guest' ? $role : 'app'))

@section('title', 'Privacy Policy')

@section('content')
<div class="container py-5">
    <div class="row justify-content-center">
        <div class="col-lg-10">
            <!-- Header -->
            <div class="text-center mb-5">
                <h1 class="display-4 fw-bold">Privacy Policy</h1>
                <p class="lead text-muted">Last updated: {{ date('F d, Y') }}</p>
                <div class="mt-3">
                    <span class="badge bg-primary">Version 1.0</span>
                </div>
            </div>

            <!-- Introduction -->
            <div class="card shadow-sm mb-4">
                <div class="card-body p-4">
                    <h2 class="h4 fw-bold mb-3">Introduction</h2>
                    <p>At {{ setting('app_name', 'CBT System') }}, we are committed to protecting your privacy and ensuring you have a positive experience on our platform. This Privacy Policy explains how we collect, use, disclose, and safeguard your information when you use our computer-based testing platform.</p>
                    <p>Please read this privacy policy carefully. If you do not agree with the terms of this privacy policy, please do not access the site.</p>
                </div>
            </div>

            <!-- Information We Collect -->
            <div class="card shadow-sm mb-4">
                <div class="card-body p-4">
                    <h2 class="h4 fw-bold mb-3">Information We Collect</h2>

                    <h5 class="h6 fw-bold mt-3">Personal Data</h5>
                    <p>We may collect personal identification information in various ways, including, but not limited to:</p>
                    <ul class="list-group list-group-flush mb-3">
                        <li class="list-group-item bg-light"><i class="bi bi-check-circle-fill text-success me-2"></i>Name and email address</li>
                        <li class="list-group-item bg-light"><i class="bi bi-check-circle-fill text-success me-2"></i>Phone number</li>
                        <li class="list-group-item bg-light"><i class="bi bi-check-circle-fill text-success me-2"></i>Educational background</li>
                        <li class="list-group-item bg-light"><i class="bi bi-check-circle-fill text-success me-2"></i>Exam results and performance data</li>
                        <li class="list-group-item bg-light"><i class="bi bi-check-circle-fill text-success me-2"></i>Profile picture</li>
                    </ul>

                    <h5 class="h6 fw-bold mt-3">Usage Data</h5>
                    <p>We may also collect information on how you access and use the platform:</p>
                    <ul class="list-group list-group-flush">
                        <li class="list-group-item bg-light"><i class="bi bi-check-circle-fill text-success me-2"></i>IP address and browser type</li>
                        <li class="list-group-item bg-light"><i class="bi bi-check-circle-fill text-success me-2"></i>Device information</li>
                        <li class="list-group-item bg-light"><i class="bi bi-check-circle-fill text-success me-2"></i>Pages visited and time spent</li>
                        <li class="list-group-item bg-light"><i class="bi bi-check-circle-fill text-success me-2"></i>Exam navigation patterns</li>
                    </ul>
                </div>
            </div>

            <!-- How We Use Your Information -->
            <div class="card shadow-sm mb-4">
                <div class="card-body p-4">
                    <h2 class="h4 fw-bold mb-3">How We Use Your Information</h2>

                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <div class="p-3 bg-light rounded h-100">
                                <i class="bi bi-person-badge fs-1 text-primary mb-3"></i>
                                <h5 class="h6 fw-bold">Account Management</h5>
                                <p class="small text-muted mb-0">To create and manage your account, provide user support, and communicate with you.</p>
                            </div>
                        </div>
                        <div class="col-md-6 mb-3">
                            <div class="p-3 bg-light rounded h-100">
                                <i class="bi bi-pencil-square fs-1 text-primary mb-3"></i>
                                <h5 class="h6 fw-bold">Exam Administration</h5>
                                <p class="small text-muted mb-0">To conduct exams, track your progress, and generate results and analytics.</p>
                            </div>
                        </div>
                        <div class="col-md-6 mb-3">
                            <div class="p-3 bg-light rounded h-100">
                                <i class="bi bi-shield-check fs-1 text-primary mb-3"></i>
                                <h5 class="h6 fw-bold">Security</h5>
                                <p class="small text-muted mb-0">To monitor for suspicious activity, prevent cheating, and ensure platform integrity.</p>
                            </div>
                        </div>
                        <div class="col-md-6 mb-3">
                            <div class="p-3 bg-light rounded h-100">
                                <i class="bi bi-graph-up fs-1 text-primary mb-3"></i>
                                <h5 class="h6 fw-bold">Analytics</h5>
                                <p class="small text-muted mb-0">To analyze platform usage and improve our services.</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Data Retention -->
            <div class="card shadow-sm mb-4">
                <div class="card-body p-4">
                    <h2 class="h4 fw-bold mb-3">Data Retention</h2>
                    <p>We will retain your personal information only for as long as is necessary for the purposes set out in this privacy policy. We will retain and use your information to the extent necessary to comply with our legal obligations, resolve disputes, and enforce our policies.</p>

                    <div class="alert alert-info mt-3">
                        <i class="bi bi-clock-history me-2"></i>
                        <strong>Exam Data:</strong> Your exam results and performance data are retained for educational records as required by your institution.
                    </div>
                </div>
            </div>

            <!-- Your Rights -->
            <div class="card shadow-sm mb-4">
                <div class="card-body p-4">
                    <h2 class="h4 fw-bold mb-3">Your Rights</h2>

                    <div class="accordion" id="rightsAccordion">
                        <div class="accordion-item">
                            <h2 class="accordion-header">
                                <button class="accordion-button" type="button" data-bs-toggle="collapse" data-bs-target="#rights1">
                                    <i class="bi bi-eye me-2"></i> Right to Access
                                </button>
                            </h2>
                            <div id="rights1" class="accordion-collapse collapse show" data-bs-parent="#rightsAccordion">
                                <div class="accordion-body">
                                    You have the right to request copies of your personal data. We may charge a small fee for this service.
                                </div>
                            </div>
                        </div>
                        <div class="accordion-item">
                            <h2 class="accordion-header">
                                <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#rights2">
                                    <i class="bi bi-pencil me-2"></i> Right to Rectification
                                </button>
                            </h2>
                            <div id="rights2" class="accordion-collapse collapse" data-bs-parent="#rightsAccordion">
                                <div class="accordion-body">
                                    You have the right to request that we correct any information you believe is inaccurate or complete information you believe is incomplete.
                                </div>
                            </div>
                        </div>
                        <div class="accordion-item">
                            <h2 class="accordion-header">
                                <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#rights3">
                                    <i class="bi bi-trash me-2"></i> Right to Erasure
                                </button>
                            </h2>
                            <div id="rights3" class="accordion-collapse collapse" data-bs-parent="#rightsAccordion">
                                <div class="accordion-body">
                                    You have the right to request that we erase your personal data, under certain conditions.
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Role-Specific Information -->
            @if($role !== 'guest')
            <div class="card shadow-sm mb-4 border-{{ $role === 'admin' ? 'danger' : ($role === 'instructor' ? 'warning' : 'info') }}">
                <div class="card-header bg-{{ $role === 'admin' ? 'danger' : ($role === 'instructor' ? 'warning' : 'info') }} text-white">
                    <h5 class="mb-0">
                        <i class="bi bi-shield-lock me-2"></i>
                        Additional Information for {{ ucfirst($role) }}s
                    </h5>
                </div>
                <div class="card-body">
                    @if($role === 'admin')
                    <h6 class="fw-bold">Administrator Responsibilities:</h6>
                    <ul>
                        <li>You have access to all user data for platform management</li>
                        <li>You are responsible for maintaining data confidentiality</li>
                        <li>All administrative actions are logged and monitored</li>
                        <li>You must comply with data protection regulations when handling user information</li>
                    </ul>
                    @elseif($role === 'instructor')
                    <h6 class="fw-bold">Instructor Responsibilities:</h6>
                    <ul>
                        <li>You can view student performance data for your assigned courses</li>
                        <li>You may create and manage exams using our question bank</li>
                        <li>Student data should only be used for educational purposes</li>
                        <li>Exam content you create remains the property of the institution</li>
                    </ul>
                    @elseif($role === 'student')
                    <h6 class="fw-bold">Student Rights:</h6>
                    <ul>
                        <li>Your exam attempts and results are private to you and your instructors</li>
                        <li>You can view your own performance analytics</li>
                        <li>Your personal information is protected and not shared with third parties</li>
                        <li>You can request deletion of your account at any time</li>
                    </ul>
                    @endif
                </div>
            </div>
            @endif

            <!-- Contact Information -->
            <div class="card shadow-sm mb-4">
                <div class="card-body p-4">
                    <h2 class="h4 fw-bold mb-3">Contact Us</h2>

                    <p>If you have questions or concerns about this Privacy Policy, please contact us:</p>

                    <div class="row">
                        <div class="col-md-6">
                            <div class="d-flex align-items-center mb-3">
                                <i class="bi bi-envelope-fill fs-4 text-primary me-3"></i>
                                <div>
                                    <small class="text-muted">Email</small>
                                    <div class="fw-bold">{{ setting('contact_email', 'privacy@cbt.com') }}</div>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="d-flex align-items-center mb-3">
                                <i class="bi bi-telephone-fill fs-4 text-primary me-3"></i>
                                <div>
                                    <small class="text-muted">Phone</small>
                                    <div class="fw-bold">{{ setting('contact_phone', '+1 (555) 123-4567') }}</div>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="d-flex align-items-center">
                                <i class="bi bi-building fs-4 text-primary me-3"></i>
                                <div>
                                    <small class="text-muted">Address</small>
                                    <div class="fw-bold">{{ setting('address', '123 Education St, Learning City, ED 12345') }}</div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Last Updated -->
            <div class="text-center text-muted mt-4">
                <p><i class="bi bi-clock me-1"></i> This privacy policy was last updated on {{ date('F d, Y') }}</p>
            </div>
        </div>
    </div>
</div>
@endsection
