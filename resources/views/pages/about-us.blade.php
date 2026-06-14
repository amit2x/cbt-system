@extends('layouts.' . ($role !== 'guest' ? $role : 'app'))

@section('title', 'About Us')

@section('content')
<div class="container py-5">
    <div class="row justify-content-center">
        <div class="col-lg-10">
            <!-- Header -->
            <div class="text-center mb-5">
                <h1 class="display-4 fw-bold">About Us</h1>
                <p class="lead text-muted">Empowering education through innovative technology</p>
            </div>

            <!-- Our Mission -->
            <div class="card shadow-sm mb-4 border-0 bg-primary text-white">
                <div class="card-body p-5 text-center">
                    <i class="bi bi-bullseye display-1 mb-3"></i>
                    <h2 class="h3 mb-3">Our Mission</h2>
                    <p class="lead mb-0">To revolutionize the assessment experience by providing a secure, reliable, and user-friendly computer-based testing platform that empowers educational institutions to conduct exams efficiently and effectively.</p>
                </div>
            </div>

            <!-- Our Story -->
            <div class="card shadow-sm mb-4">
                <div class="card-body p-4">
                    <h2 class="h4 fw-bold mb-3">Our Story</h2>
                    <div class="row">
                        <div class="col-md-6">
                            <p>Founded in 2024, {{ setting('app_name', 'CBT System') }} was born from a simple idea: make testing accessible, secure, and efficient for everyone. What started as a small project has grown into a comprehensive computer-based testing platform used by educational institutions worldwide.</p>
                            <p>Our team of educators, technologists, and assessment experts came together with a shared vision of transforming traditional testing methods. We recognized the challenges faced by institutions in conducting fair, scalable, and secure assessments, and set out to build a solution that addresses these needs.</p>
                        </div>
                        <div class="col-md-6">
                            <div class="bg-light p-4 rounded">
                                <h5 class="fw-bold mb-3">Quick Facts</h5>
                                <ul class="list-unstyled">
                                    <li class="mb-2"><i class="bi bi-calendar-check text-primary me-2"></i> Founded: 2024</li>
                                    <li class="mb-2"><i class="bi bi-people text-primary me-2"></i> Team Size: 50+</li>
                                    <li class="mb-2"><i class="bi bi-building text-primary me-2"></i> Institutions: 100+</li>
                                    <li class="mb-2"><i class="bi bi-globe text-primary me-2"></i> Countries: 15+</li>
                                    <li class="mb-2"><i class="bi bi-journal-check text-primary me-2"></i> Exams Conducted: 50,000+</li>
                                </ul>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Our Values -->
            <div class="card shadow-sm mb-4">
                <div class="card-body p-4">
                    <h2 class="h4 fw-bold mb-4 text-center">Our Core Values</h2>
                    <div class="row g-4">
                        <div class="col-md-4">
                            <div class="text-center p-3">
                                <div class="rounded-circle bg-primary bg-opacity-10 p-3 d-inline-block mb-3">
                                    <i class="bi bi-shield-check text-primary fs-1"></i>
                                </div>
                                <h5 class="fw-bold">Integrity</h5>
                                <p class="text-muted">We maintain the highest standards of academic integrity and data security.</p>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="text-center p-3">
                                <div class="rounded-circle bg-primary bg-opacity-10 p-3 d-inline-block mb-3">
                                    <i class="bi bi-lightbulb text-primary fs-1"></i>
                                </div>
                                <h5 class="fw-bold">Innovation</h5>
                                <p class="text-muted">We continuously evolve our platform to meet changing educational needs.</p>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="text-center p-3">
                                <div class="rounded-circle bg-primary bg-opacity-10 p-3 d-inline-block mb-3">
                                    <i class="bi bi-people text-primary fs-1"></i>
                                </div>
                                <h5 class="fw-bold">Accessibility</h5>
                                <p class="text-muted">We believe quality assessment should be accessible to all learners.</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Our Team -->
            <div class="card shadow-sm mb-4">
                <div class="card-body p-4">
                    <h2 class="h4 fw-bold mb-4 text-center">Leadership Team</h2>
                    <div class="row">
                        <div class="col-md-4 mb-4">
                            <div class="text-center">
                                <img src="https://ui-avatars.com/api/?name=John+Doe&size=150&background=4e73df&color=fff" class="rounded-circle mb-3" width="150" height="150">
                                <h5 class="fw-bold mb-1">Dr. John Doe</h5>
                                <p class="text-muted">CEO & Founder</p>
                                <p class="small">20+ years in educational technology</p>
                            </div>
                        </div>
                        <div class="col-md-4 mb-4">
                            <div class="text-center">
                                <img src="https://ui-avatars.com/api/?name=Jane+Smith&size=150&background=1cc88a&color=fff" class="rounded-circle mb-3" width="150" height="150">
                                <h5 class="fw-bold mb-1">Dr. Jane Smith</h5>
                                <p class="text-muted">Chief Academic Officer</p>
                                <p class="small">Former Dean of Education</p>
                            </div>
                        </div>
                        <div class="col-md-4 mb-4">
                            <div class="text-center">
                                <img src="https://ui-avatars.com/api/?name=Mike+Johnson&size=150&background=36b9cc&color=fff" class="rounded-circle mb-3" width="150" height="150">
                                <h5 class="fw-bold mb-1">Mike Johnson</h5>
                                <p class="text-muted">CTO</p>
                                <p class="small">Tech innovation leader</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Role-Specific Content -->
            @if($role !== 'guest')
            <div class="card shadow-sm mb-4 border-{{ $role === 'admin' ? 'danger' : ($role === 'instructor' ? 'warning' : 'info') }}">
                <div class="card-header bg-{{ $role === 'admin' ? 'danger' : ($role === 'instructor' ? 'warning' : 'info') }} text-white">
                    <h5 class="mb-0">
                        <i class="bi bi-megaphone me-2"></i>
                        For Our {{ ucfirst($role) }}s
                    </h5>
                </div>
                <div class="card-body">
                    @if($role === 'admin')
                    <p>As an administrator, you have access to our dedicated support team and priority assistance. We're committed to helping you make the most of our platform for your institution.</p>
                    <a href="#" class="btn btn-outline-danger">Contact Admin Support</a>
                    @elseif($role === 'instructor')
                    <p>We provide instructors with comprehensive tools to create engaging assessments. Join our instructor community to share best practices and get exclusive resources.</p>
                    <a href="#" class="btn btn-outline-warning">Join Instructor Community</a>
                    @elseif($role === 'student')
                    <p>Your success is our priority. Access study tips, practice materials, and connect with peers in our student community.</p>
                    <a href="#" class="btn btn-outline-info">Explore Student Resources</a>
                    @endif
                </div>
            </div>
            @endif

            <!-- Call to Action -->
            <div class="text-center mt-5">
                <h3 class="h5 mb-3">Ready to get started?</h3>
                @guest
                    <a href="{{ route('register') }}" class="btn btn-primary btn-lg px-5">Join Us Today</a>
                @else
                    <a href="{{ route('dashboard') }}" class="btn btn-primary btn-lg px-5">Go to Dashboard</a>
                @endguest
            </div>
        </div>
    </div>
</div>
@endsection
