@extends('layouts.' . ($role !== 'guest' ? $role : 'app'))

@section('title', 'Terms of Service')

@section('content')
<div class="container py-5">
    <div class="row justify-content-center">
        <div class="col-lg-10">
            <!-- Header -->
            <div class="text-center mb-5">
                <h1 class="display-4 fw-bold">Terms of Service</h1>
                <p class="lead text-muted">Last updated: {{ date('F d, Y') }}</p>
                <div class="mt-3">
                    <span class="badge bg-primary">Version 1.0</span>
                </div>
            </div>

            <!-- Introduction -->
            <div class="card shadow-sm mb-4">
                <div class="card-body p-4">
                    <h2 class="h4 fw-bold mb-3">1. Agreement to Terms</h2>
                    <p>By accessing or using {{ setting('app_name', 'CBT System') }}, you agree to be bound by these Terms of Service and all applicable laws and regulations. If you do not agree with any part of these terms, you may not access or use our services.</p>
                </div>
            </div>

            <!-- User Accounts -->
            <div class="card shadow-sm mb-4">
                <div class="card-body p-4">
                    <h2 class="h4 fw-bold mb-3">2. User Accounts</h2>

                    <div class="mb-3">
                        <h5 class="h6 fw-bold">2.1 Account Creation</h5>
                        <p>To use our platform, you must create an account with accurate and complete information. You are responsible for maintaining the security of your account credentials.</p>
                    </div>

                    <div class="mb-3">
                        <h5 class="h6 fw-bold">2.2 Account Responsibilities</h5>
                        <ul>
                            <li>You are responsible for all activities that occur under your account</li>
                            <li>You must notify us immediately of any unauthorized access</li>
                            <li>We reserve the right to suspend or terminate accounts that violate our terms</li>
                        </ul>
                    </div>

                    <div class="alert alert-warning">
                        <i class="bi bi-exclamation-triangle me-2"></i>
                        <strong>Note:</strong> Sharing account credentials is strictly prohibited and may result in immediate account termination.
                    </div>
                </div>
            </div>

            <!-- Acceptable Use -->
            <div class="card shadow-sm mb-4">
                <div class="card-body p-4">
                    <h2 class="h4 fw-bold mb-3">3. Acceptable Use Policy</h2>

                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <div class="p-3 bg-light rounded h-100">
                                <i class="bi bi-check-circle-fill text-success fs-3 mb-2"></i>
                                <h5 class="h6 fw-bold">You May:</h5>
                                <ul class="small">
                                    <li>Take exams and quizzes as assigned</li>
                                    <li>View your results and analytics</li>
                                    <li>Provide feedback on courses</li>
                                    <li>Access study materials</li>
                                </ul>
                            </div>
                        </div>
                        <div class="col-md-6 mb-3">
                            <div class="p-3 bg-light rounded h-100">
                                <i class="bi bi-x-circle-fill text-danger fs-3 mb-2"></i>
                                <h5 class="h6 fw-bold">You May Not:</h5>
                                <ul class="small">
                                    <li>Share exam content with others</li>
                                    <li>Use automated tools to take exams</li>
                                    <li>Attempt to cheat or circumvent security</li>
                                    <li>Harass other users or staff</li>
                                </ul>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Intellectual Property -->
            <div class="card shadow-sm mb-4">
                <div class="card-body p-4">
                    <h2 class="h4 fw-bold mb-3">4. Intellectual Property Rights</h2>

                    <p>All content on this platform, including but not limited to exam questions, course materials, software, and design, is the property of {{ setting('app_name', 'CBT System') }} or its licensors and is protected by copyright and other intellectual property laws.</p>

                    <div class="alert alert-info mt-3">
                        <i class="bi bi-info-circle me-2"></i>
                        <strong>Note:</strong> You retain ownership of any content you submit, but grant us a license to use it for platform operations.
                    </div>
                </div>
            </div>

            <!-- Proctoring and Monitoring -->
            <div class="card shadow-sm mb-4">
                <div class="card-body p-4">
                    <h2 class="h4 fw-bold mb-3">5. Exam Proctoring and Monitoring</h2>

                    <p>During exams, we may use proctoring technologies to ensure academic integrity. This may include:</p>

                    <ul>
                        <li>Recording screen activity</li>
                        <li>Monitoring tab switching and window focus</li>
                        <li>Capturing webcam images (with consent)</li>
                        <li>Logging mouse movements and keystrokes</li>
                    </ul>

                    <p>By taking exams on our platform, you consent to such monitoring as necessary to maintain exam integrity.</p>
                </div>
            </div>

            <!-- Role-Specific Terms -->
            @if($role !== 'guest')
            <div class="card shadow-sm mb-4 border-{{ $role === 'admin' ? 'danger' : ($role === 'instructor' ? 'warning' : 'info') }}">
                <div class="card-header bg-{{ $role === 'admin' ? 'danger' : ($role === 'instructor' ? 'warning' : 'info') }} text-white">
                    <h5 class="mb-0">
                        <i class="bi bi-shield-lock me-2"></i>
                        Additional Terms for {{ ucfirst($role) }}s
                    </h5>
                </div>
                <div class="card-body">
                    @if($role === 'admin')
                    <h6 class="fw-bold">Administrator Terms:</h6>
                    <ul>
                        <li>You agree to maintain confidentiality of all user data</li>
                        <li>You will use administrative privileges only for legitimate purposes</li>
                        <li>You are responsible for proper configuration of exams and courses</li>
                        <li>All administrative actions are subject to audit</li>
                    </ul>
                    @elseif($role === 'instructor')
                    <h6 class="fw-bold">Instructor Terms:</h6>
                    <ul>
                        <li>You agree to create fair and appropriate exam content</li>
                        <li>You will respect student privacy and data rights</li>
                        <li>Exam content should comply with educational standards</li>
                        <li>You are responsible for proper exam scheduling and grading</li>
                    </ul>
                    @elseif($role === 'student')
                    <h6 class="fw-bold">Student Terms:</h6>
                    <ul>
                        <li>You agree to complete exams honestly and without assistance</li>
                        <li>You will not share exam content with others</li>
                        <li>You accept the proctoring and monitoring during exams</li>
                        <li>You understand that cheating may result in disciplinary action</li>
                    </ul>
                    @endif
                </div>
            </div>
            @endif

            <!-- Limitation of Liability -->
            <div class="card shadow-sm mb-4">
                <div class="card-body p-4">
                    <h2 class="h4 fw-bold mb-3">6. Limitation of Liability</h2>

                    <p>To the fullest extent permitted by law, {{ setting('app_name', 'CBT System') }} shall not be liable for any indirect, incidental, special, consequential, or punitive damages, including without limitation, loss of profits, data, use, goodwill, or other intangible losses, resulting from:</p>

                    <ul>
                        <li>Your use or inability to use the platform</li>
                        <li>Any conduct or content of any third party</li>
                        <li>Unauthorized access to your data</li>
                        <li>Technical issues or service interruptions</li>
                    </ul>
                </div>
            </div>

            <!-- Termination -->
            <div class="card shadow-sm mb-4">
                <div class="card-body p-4">
                    <h2 class="h4 fw-bold mb-3">7. Termination</h2>

                    <p>We may terminate or suspend your account and bar access to the platform immediately, without prior notice or liability, under our sole discretion, for any reason whatsoever, including without limitation if you breach the Terms.</p>

                    <p>Upon termination, your right to use the platform will immediately cease. All provisions of the Terms which by their nature should survive termination shall survive termination.</p>
                </div>
            </div>

            <!-- Governing Law -->
            <div class="card shadow-sm mb-4">
                <div class="card-body p-4">
                    <h2 class="h4 fw-bold mb-3">8. Governing Law</h2>

                    <p>These Terms shall be governed and construed in accordance with the laws of [Your Jurisdiction], without regard to its conflict of law provisions.</p>

                    <p>Our failure to enforce any right or provision of these Terms will not be considered a waiver of those rights.</p>
                </div>
            </div>

            <!-- Changes to Terms -->
            <div class="card shadow-sm mb-4">
                <div class="card-body p-4">
                    <h2 class="h4 fw-bold mb-3">9. Changes to Terms</h2>

                    <p>We reserve the right to modify or replace these Terms at any time. If a revision is material, we will try to provide at least 30 days' notice prior to any new terms taking effect.</p>

                    <p>By continuing to access or use our platform after those revisions become effective, you agree to be bound by the revised terms.</p>
                </div>
            </div>

            <!-- Contact -->
            <div class="card shadow-sm mb-4">
                <div class="card-body p-4">
                    <h2 class="h4 fw-bold mb-3">10. Contact Us</h2>

                    <p>If you have any questions about these Terms, please contact us:</p>

                    <div class="row">
                        <div class="col-md-6">
                            <div class="d-flex align-items-center mb-3">
                                <i class="bi bi-envelope-fill fs-4 text-primary me-3"></i>
                                <div>
                                    <small class="text-muted">Email</small>
                                    <div class="fw-bold">{{ setting('contact_email', 'legal@cbt.com') }}</div>
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

            <!-- Acknowledgement -->
            <div class="text-center mt-4">
                <div class="form-check d-inline-block">
                    <input class="form-check-input" type="checkbox" id="acknowledge">
                    <label class="form-check-label" for="acknowledge">
                        I have read and agree to the Terms of Service
                    </label>
                </div>
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script>
    document.getElementById('acknowledge')?.addEventListener('change', function(e) {
        // You can add functionality here, like enabling a continue button
        console.log('Terms acknowledged:', e.target.checked);
    });
</script>
@endpush
@endsection
