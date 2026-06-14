@extends('layouts.' . ($role !== 'guest' ? $role : 'app'))

@section('title', 'Contact Us')

@section('content')
<div class="container py-5">
    <div class="row justify-content-center">
        <div class="col-lg-10">
            <!-- Header -->
            <div class="text-center mb-5">
                <h1 class="display-4 fw-bold">Contact Us</h1>
                <p class="lead text-muted">We're here to help! Reach out to us anytime.</p>
            </div>

            <div class="row">
                <!-- Contact Information -->
                <div class="col-md-5 mb-4">
                    <div class="card shadow-sm h-100">
                        <div class="card-body p-4">
                            <h2 class="h4 fw-bold mb-4">Get in Touch</h2>

                            <div class="d-flex align-items-center mb-4">
                                <div class="bg-primary bg-opacity-10 p-3 rounded-circle me-3">
                                    <i class="bi bi-geo-alt-fill text-primary fs-4"></i>
                                </div>
                                <div>
                                    <h6 class="fw-bold mb-1">Visit Us</h6>
                                    <p class="text-muted mb-0">{{ setting('address', '123 Education Street, Learning City, ED 12345') }}</p>
                                </div>
                            </div>

                            <div class="d-flex align-items-center mb-4">
                                <div class="bg-primary bg-opacity-10 p-3 rounded-circle me-3">
                                    <i class="bi bi-telephone-fill text-primary fs-4"></i>
                                </div>
                                <div>
                                    <h6 class="fw-bold mb-1">Call Us</h6>
                                    <p class="text-muted mb-0">{{ setting('contact_phone', '+1 (555) 123-4567') }}</p>
                                    <small class="text-muted">Mon-Fri, 9:00 AM - 6:00 PM</small>
                                </div>
                            </div>

                            <div class="d-flex align-items-center mb-4">
                                <div class="bg-primary bg-opacity-10 p-3 rounded-circle me-3">
                                    <i class="bi bi-envelope-fill text-primary fs-4"></i>
                                </div>
                                <div>
                                    <h6 class="fw-bold mb-1">Email Us</h6>
                                    <p class="text-muted mb-0">{{ setting('contact_email', 'support@cbt.com') }}</p>
                                    <small class="text-muted">We reply within 24 hours</small>
                                </div>
                            </div>

                            <div class="d-flex align-items-center">
                                <div class="bg-primary bg-opacity-10 p-3 rounded-circle me-3">
                                    <i class="bi bi-chat-dots-fill text-primary fs-4"></i>
                                </div>
                                <div>
                                    <h6 class="fw-bold mb-1">Live Chat</h6>
                                    <p class="text-muted mb-0">Available 24/7</p>
                                    <a href="#" class="btn btn-sm btn-primary mt-2">Start Chat</a>
                                </div>
                            </div>

                            <hr class="my-4">

                            <h6 class="fw-bold mb-3">Follow Us</h6>
                            <div class="d-flex gap-3">
                                <a href="#" class="text-decoration-none text-primary fs-4"><i class="bi bi-facebook"></i></a>
                                <a href="#" class="text-decoration-none text-primary fs-4"><i class="bi bi-twitter-x"></i></a>
                                <a href="#" class="text-decoration-none text-primary fs-4"><i class="bi bi-linkedin"></i></a>
                                <a href="#" class="text-decoration-none text-primary fs-4"><i class="bi bi-youtube"></i></a>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Contact Form -->
                <div class="col-md-7 mb-4">
                    <div class="card shadow-sm">
                        <div class="card-body p-4">
                            <h2 class="h4 fw-bold mb-4">Send us a Message</h2>

                            <form action="#" method="POST" id="contactForm">
                                @csrf

                                <div class="row">
                                    <div class="col-md-6 mb-3">
                                        <label for="name" class="form-label">Your Name <span class="text-danger">*</span></label>
                                        <input type="text" class="form-control" id="name" name="name" required
                                               value="{{ auth()->user()->name ?? '' }}">
                                    </div>
                                    <div class="col-md-6 mb-3">
                                        <label for="email" class="form-label">Email Address <span class="text-danger">*</span></label>
                                        <input type="email" class="form-control" id="email" name="email" required
                                               value="{{ auth()->user()->email ?? '' }}">
                                    </div>
                                </div>

                                <div class="mb-3">
                                    <label for="subject" class="form-label">Subject <span class="text-danger">*</span></label>
                                    <select class="form-select" id="subject" name="subject" required>
                                        <option value="">Select a subject</option>
                                        <option value="general">General Inquiry</option>
                                        <option value="technical">Technical Support</option>
                                        <option value="billing">Billing Question</option>
                                        <option value="feature">Feature Request</option>
                                        <option value="other">Other</option>
                                    </select>
                                </div>

                                <div class="mb-3">
                                    <label for="message" class="form-label">Message <span class="text-danger">*</span></label>
                                    <textarea class="form-control" id="message" name="message" rows="5" required
                                              placeholder="How can we help you?"></textarea>
                                </div>

                                <div class="mb-3">
                                    <div class="form-check">
                                        <input class="form-check-input" type="checkbox" id="copy" name="copy">
                                        <label class="form-check-label" for="copy">
                                            Send me a copy of this message
                                        </label>
                                    </div>
                                </div>

                                <button type="submit" class="btn btn-primary w-100 py-3" id="submitBtn">
                                    <i class="bi bi-send me-2"></i>Send Message
                                </button>
                            </form>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Map -->
            <div class="card shadow-sm mt-4">
                <div class="card-body p-0">
                    <iframe
                        src="https://www.google.com/maps?q=Netaji+Subhas+Chandra+Bose+International+Airport+Kolkata&output=embed"
                        width="100%"
                        height="300"
                        style="border:0;"
                        allowfullscreen=""
                        loading="lazy"
                        referrerpolicy="no-referrer-when-downgrade">
                    </iframe>
                </div>
            </div>

            <!-- Role-Specific Contact -->
            @if($role !== 'guest')
            <div class="card shadow-sm mt-4 border-{{ $role === 'admin' ? 'danger' : ($role === 'instructor' ? 'warning' : 'info') }}">
                <div class="card-header bg-{{ $role === 'admin' ? 'danger' : ($role === 'instructor' ? 'warning' : 'info') }} text-white">
                    <h5 class="mb-0">
                        <i class="bi bi-headset me-2"></i>
                        Priority Support for {{ ucfirst($role) }}s
                    </h5>
                </div>
                <div class="card-body">
                    <div class="row align-items-center">
                        <div class="col-md-8">
                            <p class="mb-md-0">As a valued {{ $role }}, you have access to our priority support line for faster assistance.</p>
                        </div>
                        <div class="col-md-4 text-md-end">
                            <a href="#" class="btn btn-{{ $role === 'admin' ? 'danger' : ($role === 'instructor' ? 'warning' : 'info') }}">
                                <i class="bi bi-telephone me-2"></i>Priority Line
                            </a>
                        </div>
                    </div>
                </div>
            </div>
            @endif
        </div>
    </div>
</div>

@push('scripts')
<script>
    document.getElementById('contactForm')?.addEventListener('submit', function(e) {
        e.preventDefault();

        const submitBtn = document.getElementById('submitBtn');
        submitBtn.disabled = true;
        submitBtn.innerHTML = '<span class="spinner-border spinner-border-sm me-2"></span>Sending...';

        // Simulate form submission (replace with actual AJAX)
        setTimeout(() => {
            submitBtn.innerHTML = '<i class="bi bi-check-circle me-2"></i>Sent Successfully!';
            submitBtn.classList.remove('btn-primary');
            submitBtn.classList.add('btn-success');

            // Reset form
            document.getElementById('contactForm').reset();

            // Show success message
            alert('Thank you for your message! We\'ll get back to you soon.');

            // Reset button after 3 seconds
            setTimeout(() => {
                submitBtn.disabled = false;
                submitBtn.innerHTML = '<i class="bi bi-send me-2"></i>Send Message';
                submitBtn.classList.remove('btn-success');
                submitBtn.classList.add('btn-primary');
            }, 3000);
        }, 2000);
    });
</script>
@endpush
@endsection
