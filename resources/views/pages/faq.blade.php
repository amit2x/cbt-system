@extends('layouts.' . ($role !== 'guest' ? $role : 'app'))

@section('title', 'Frequently Asked Questions')

@section('content')
<div class="container py-5">
    <div class="row justify-content-center">
        <div class="col-lg-10">
            <!-- Header -->
            <div class="text-center mb-5">
                <h1 class="display-4 fw-bold">Frequently Asked Questions</h1>
                <p class="lead text-muted">Find answers to common questions about our platform</p>
            </div>

            <!-- Search Bar -->
            <div class="card shadow-sm mb-4">
                <div class="card-body">
                    <div class="input-group">
                        <span class="input-group-text bg-white border-end-0">
                            <i class="bi bi-search"></i>
                        </span>
                        <input type="text" class="form-control border-start-0" id="faqSearch"
                               placeholder="Search FAQs...">
                    </div>
                </div>
            </div>

            <!-- FAQ Categories -->
            <div class="row mb-4">
                <div class="col-12">
                    <div class="btn-group w-100" role="group">
                        <button type="button" class="btn btn-outline-primary active" data-category="all">All</button>
                        <button type="button" class="btn btn-outline-primary" data-category="general">General</button>
                        <button type="button" class="btn btn-outline-primary" data-category="account">Account</button>
                        <button type="button" class="btn btn-outline-primary" data-category="exams">Exams</button>
                        <button type="button" class="btn btn-outline-primary" data-category="technical">Technical</button>
                    </div>
                </div>
            </div>

            <!-- FAQ Accordion -->
            <div class="card shadow-sm">
                <div class="card-body p-4">
                    <div class="accordion" id="faqAccordion">
                        <!-- General FAQs -->
                        <h5 class="mb-3 category-title" data-category="general">General Questions</h5>

                        <div class="accordion-item mb-3 faq-item" data-category="general">
                            <h2 class="accordion-header">
                                <button class="accordion-button" type="button" data-bs-toggle="collapse" data-bs-target="#faq1">
                                    What is {{ setting('app_name', 'CBT System') }}?
                                </button>
                            </h2>
                            <div id="faq1" class="accordion-collapse collapse show" data-bs-parent="#faqAccordion">
                                <div class="accordion-body">
                                    {{ setting('app_name', 'CBT System') }} is a comprehensive computer-based testing platform designed for educational institutions to conduct secure, scalable, and efficient online examinations. We provide tools for creating exams, managing question banks, monitoring live tests, and analyzing results.
                                </div>
                            </div>
                        </div>

                        <div class="accordion-item mb-3 faq-item" data-category="general">
                            <h2 class="accordion-header">
                                <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#faq2">
                                    How does the platform ensure exam security?
                                </button>
                            </h2>
                            <div id="faq2" class="accordion-collapse collapse" data-bs-parent="#faqAccordion">
                                <div class="accordion-body">
                                    Our platform employs multiple security measures including:
                                    <ul class="mb-0 mt-2">
                                        <li>Tab switching detection and logging</li>
                                        <li>Browser focus monitoring</li>
                                        <li>Optional proctoring with screenshot capture</li>
                                        <li>IP address tracking</li>
                                        <li>Question and option randomization</li>
                                        <li>Time-limited exams with auto-submit</li>
                                    </ul>
                                </div>
                            </div>
                        </div>

                        <!-- Account FAQs -->
                        <h5 class="mb-3 mt-4 category-title" data-category="account">Account Management</h5>

                        <div class="accordion-item mb-3 faq-item" data-category="account">
                            <h2 class="accordion-header">
                                <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#faq3">
                                    How do I create an account?
                                </button>
                            </h2>
                            <div id="faq3" class="accordion-collapse collapse" data-bs-parent="#faqAccordion">
                                <div class="accordion-body">
                                    You can create an account by clicking the "Register" button on the login page. Fill in your details including name, email, and password. After registration, you'll receive a verification email to activate your account.
                                </div>
                            </div>
                        </div>

                        <div class="accordion-item mb-3 faq-item" data-category="account">
                            <h2 class="accordion-header">
                                <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#faq4">
                                    What should I do if I forget my password?
                                </button>
                            </h2>
                            <div id="faq4" class="accordion-collapse collapse" data-bs-parent="#faqAccordion">
                                <div class="accordion-body">
                                    Click the "Forgot Password" link on the login page. Enter your email address, and we'll send you a password reset link. Follow the instructions in the email to create a new password.
                                </div>
                            </div>
                        </div>

                        <div class="accordion-item mb-3 faq-item" data-category="account">
                            <h2 class="accordion-header">
                                <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#faq5">
                                    How do I update my profile information?
                                </button>
                            </h2>
                            <div id="faq5" class="accordion-collapse collapse" data-bs-parent="#faqAccordion">
                                <div class="accordion-body">
                                    Log in to your account and navigate to your profile page. You can update your name, email, phone number, and profile picture. To change your password, go to the Settings page.
                                </div>
                            </div>
                        </div>

                        <!-- Exams FAQs -->
                        <h5 class="mb-3 mt-4 category-title" data-category="exams">Exams & Testing</h5>

                        <div class="accordion-item mb-3 faq-item" data-category="exams">
                            <h2 class="accordion-header">
                                <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#faq6">
                                    How do I start an exam?
                                </button>
                            </h2>
                            <div id="faq6" class="accordion-collapse collapse" data-bs-parent="#faqAccordion">
                                <div class="accordion-body">
                                    Navigate to the "My Exams" section from your dashboard. Find the exam you want to take and click "Start Exam". You'll see the exam instructions first, and after confirming, the exam will begin.
                                </div>
                            </div>
                        </div>

                        <div class="accordion-item mb-3 faq-item" data-category="exams">
                            <h2 class="accordion-header">
                                <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#faq7">
                                    What happens if I lose internet connection during an exam?
                                </button>
                            </h2>
                            <div id="faq7" class="accordion-collapse collapse" data-bs-parent="#faqAccordion">
                                <div class="accordion-body">
                                    The platform automatically saves your answers periodically. If you lose connection, you can reconnect and continue from where you left off. However, the timer continues running, so ensure you have a stable connection before starting.
                                </div>
                            </div>
                        </div>

                        <div class="accordion-item mb-3 faq-item" data-category="exams">
                            <h2 class="accordion-header">
                                <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#faq8">
                                    Can I review my answers before submitting?
                                </button>
                            </h2>
                            <div id="faq8" class="accordion-collapse collapse" data-bs-parent="#faqAccordion">
                                <div class="accordion-body">
                                    Yes, you can navigate between questions using the question palette. You can also mark questions for review and come back to them later. The system will show you which questions are answered, marked, or not visited.
                                </div>
                            </div>
                        </div>

                        <div class="accordion-item mb-3 faq-item" data-category="exams">
                            <h2 class="accordion-header">
                                <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#faq9">
                                    How do I view my exam results?
                                </button>
                            </h2>
                            <div id="faq9" class="accordion-collapse collapse" data-bs-parent="#faqAccordion">
                                <div class="accordion-body">
                                    After completing an exam, you can view your results immediately (if enabled by the instructor) or after they're published. Go to the "My Results" section to see detailed performance analytics, including score breakdown and answer review.
                                </div>
                            </div>
                        </div>

                        <!-- Technical FAQs -->
                        <h5 class="mb-3 mt-4 category-title" data-category="technical">Technical Support</h5>

                        <div class="accordion-item mb-3 faq-item" data-category="technical">
                            <h2 class="accordion-header">
                                <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#faq10">
                                    What browsers are supported?
                                </button>
                            </h2>
                            <div id="faq10" class="accordion-collapse collapse" data-bs-parent="#faqAccordion">
                                <div class="accordion-body">
                                    We support the latest versions of:
                                    <ul class="mb-0 mt-2">
                                        <li>Google Chrome (recommended)</li>
                                        <li>Mozilla Firefox</li>
                                        <li>Apple Safari</li>
                                        <li>Microsoft Edge</li>
                                    </ul>
                                    For the best experience, please use the latest version of Google Chrome.
                                </div>
                            </div>
                        </div>

                        <div class="accordion-item mb-3 faq-item" data-category="technical">
                            <h2 class="accordion-header">
                                <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#faq11">
                                    What should I do if the platform is not loading properly?
                                </button>
                            </h2>
                            <div id="faq11" class="accordion-collapse collapse" data-bs-parent="#faqAccordion">
                                <div class="accordion-body">
                                    Try these troubleshooting steps:
                                    <ol class="mb-0 mt-2">
                                        <li>Clear your browser cache and cookies</li>
                                        <li>Update your browser to the latest version</li>
                                        <li>Disable browser extensions temporarily</li>
                                        <li>Try using incognito/private mode</li>
                                        <li>Check your internet connection</li>
                                        <li>Contact support if issues persist</li>
                                    </ol>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Role-Specific FAQs -->
            @if($role !== 'guest')
            <div class="card shadow-sm mt-4 border-{{ $role === 'admin' ? 'danger' : ($role === 'instructor' ? 'warning' : 'info') }}">
                <div class="card-header bg-{{ $role === 'admin' ? 'danger' : ($role === 'instructor' ? 'warning' : 'info') }} text-white">
                    <h5 class="mb-0">
                        <i class="bi bi-question-circle me-2"></i>
                        Frequently Asked Questions for {{ ucfirst($role) }}s
                    </h5>
                </div>
                <div class="card-body">
                    @if($role === 'admin')
                    <div class="accordion" id="roleFaqAccordion">
                        <div class="accordion-item">
                            <h2 class="accordion-header">
                                <button class="accordion-button" type="button" data-bs-toggle="collapse" data-bs-target="#roleFaq1">
                                    How do I create a new course?
                                </button>
                            </h2>
                            <div id="roleFaq1" class="accordion-collapse collapse show">
                                <div class="accordion-body">
                                    Navigate to Courses → Add New Course. Fill in the course details, assign instructors, and set the status. Once created, you can add modules and create batches.
                                </div>
                            </div>
                        </div>
                        <div class="accordion-item">
                            <h2 class="accordion-header">
                                <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#roleFaq2">
                                    How do I manage user roles and permissions?
                                </button>
                            </h2>
                            <div id="roleFaq2" class="accordion-collapse collapse">
                                <div class="accordion-body">
                                    Go to User Management → Users. You can edit user roles, assign permissions, activate/deactivate accounts, and manage user access to different features.
                                </div>
                            </div>
                        </div>
                    </div>
                    @elseif($role === 'instructor')
                    <div class="accordion" id="roleFaqAccordion">
                        <div class="accordion-item">
                            <h2 class="accordion-header">
                                <button class="accordion-button" type="button" data-bs-toggle="collapse" data-bs-target="#roleFaq1">
                                    How do I create an exam?
                                </button>
                            </h2>
                            <div id="roleFaq1" class="accordion-collapse collapse show">
                                <div class="accordion-body">
                                    Navigate to Exams → Create New Exam. Set the exam details, select questions from the question bank, configure settings, and publish when ready.
                                </div>
                            </div>
                        </div>
                        <div class="accordion-item">
                            <h2 class="accordion-header">
                                <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#roleFaq2">
                                    How do I monitor live exams?
                                </button>
                            </h2>
                            <div id="roleFaq2" class="accordion-collapse collapse">
                                <div class="accordion-body">
                                    Use the Live Monitoring feature to see active students, track their progress, view warnings, and intervene if necessary. You can send warnings or terminate exams if rules are violated.
                                </div>
                            </div>
                        </div>
                    </div>
                    @elseif($role === 'student')
                    <div class="accordion" id="roleFaqAccordion">
                        <div class="accordion-item">
                            <h2 class="accordion-header">
                                <button class="accordion-button" type="button" data-bs-toggle="collapse" data-bs-target="#roleFaq1">
                                    How do I access my enrolled courses?
                                </button>
                            </h2>
                            <div id="roleFaq1" class="accordion-collapse collapse show">
                                <div class="accordion-body">
                                    From your dashboard, you can see your enrolled courses and available exams. Click on any exam to view instructions and start when ready.
                                </div>
                            </div>
                        </div>
                        <div class="accordion-item">
                            <h2 class="accordion-header">
                                <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#roleFaq2">
                                    How do I provide feedback on a class?
                                </button>
                            </h2>
                            <div id="roleFaq2" class="accordion-collapse collapse">
                                <div class="accordion-body">
                                    When instructors launch a feedback session, you'll receive a notification. Go to the Feedback section and select the active feedback form to share your thoughts.
                                </div>
                            </div>
                        </div>
                    </div>
                    @endif
                </div>
            </div>
            @endif

            <!-- Still Have Questions -->
            <div class="text-center mt-5">
                <h3 class="h5 mb-3">Still have questions?</h3>
                <p class="text-muted mb-4">Can't find what you're looking for? Reach out to our support team.</p>
                <a href="{{ route('contact') }}" class="btn btn-primary btn-lg px-5">
                    <i class="bi bi-headset me-2"></i>Contact Support
                </a>
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script>
    // FAQ Search functionality
    document.getElementById('faqSearch')?.addEventListener('input', function(e) {
        const searchTerm = e.target.value.toLowerCase();
        const faqItems = document.querySelectorAll('.faq-item');

        faqItems.forEach(item => {
            const question = item.querySelector('.accordion-button').textContent.toLowerCase();
            const answer = item.querySelector('.accordion-body').textContent.toLowerCase();

            if (question.includes(searchTerm) || answer.includes(searchTerm)) {
                item.style.display = 'block';
            } else {
                item.style.display = 'none';
            }
        });
    });

    // Category filtering
    document.querySelectorAll('[data-category]').forEach(button => {
        button.addEventListener('click', function() {
            const category = this.dataset.category;

            // Update active button
            document.querySelectorAll('[data-category]').forEach(btn => {
                btn.classList.remove('active');
            });
            this.classList.add('active');

            // Filter items
            const faqItems = document.querySelectorAll('.faq-item');
            const categoryTitles = document.querySelectorAll('.category-title');

            if (category === 'all') {
                faqItems.forEach(item => item.style.display = 'block');
                categoryTitles.forEach(title => title.style.display = 'block');
            } else {
                faqItems.forEach(item => {
                    item.style.display = item.dataset.category === category ? 'block' : 'none';
                });
                categoryTitles.forEach(title => {
                    title.style.display = title.dataset.category === category ? 'block' : 'none';
                });
            }
        });
    });
</script>
@endpush
@endsection
