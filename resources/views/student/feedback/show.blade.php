@extends('layouts.student')

@section('title', 'Class Feedback')

@section('content')
<div class="container">
    <div class="row justify-content-center">
        <div class="col-md-8">
            <div class="card shadow">
                <div class="card-header bg-primary text-white">
                    <h4 class="mb-0">Class Feedback</h4>
                </div>
                <div class="card-body">
                    <div class="alert alert-info">
                        <h5 class="alert-heading">{{ $feedback->batch->course->title }}</h5>
                        <p class="mb-0">Batch: {{ $feedback->batch->name }}</p>
                        <p class="mb-0">Instructor: {{ $feedback->instructor->name }}</p>
                        <small>Your feedback helps us improve the learning experience.</small>
                    </div>

                    @if($feedback->type === 'quick')
                    <!-- Quick Feedback Form -->
                    <form id="feedbackForm" method="POST"
                        action="{{ route('student.feedback.respond', $feedback->code) }}">
                        @csrf

                        <div class="mb-4">
                            <label class="form-label fw-bold">How would you rate this class?</label>
                            <div class="rating-stars text-center">
                                <div class="d-flex justify-content-center gap-2">
                                    @for($i = 5; $i >= 1; $i--)
                                    <div class="form-check form-check-inline">
                                        <input class="form-check-input rating-input" type="radio" name="rating"
                                            value="{{ $i }}" id="star{{ $i }}" required>
                                        <label class="form-check-label rating-label" for="star{{ $i }}">
                                            <i class="bi bi-star-fill"></i> {{ $i }}
                                        </label>
                                    </div>
                                    @endfor
                                </div>
                                <div class="rating-text mt-2 text-muted" id="ratingText">Select a rating</div>
                            </div>
                        </div>

                        <div class="mb-4">
                            <label for="comment" class="form-label fw-bold">Any additional comments or feedback?</label>
                            <textarea class="form-control" id="comment" name="comment" rows="4"
                                placeholder="Share your thoughts, suggestions, or concerns..."></textarea>
                        </div>

                        <div class="text-end">
                            <button type="submit" class="btn btn-primary" id="submitBtn">
                                <i class="bi bi-send"></i> Submit Feedback
                            </button>
                        </div>
                    </form>
                    @else
                    <!-- Detailed Feedback Form -->
                    <form id="feedbackForm" method="POST"
                        action="{{ route('student.feedback.respond', $feedback->code) }}">
                        @csrf

                        @foreach($feedback->questions as $index => $question)
                        <div class="mb-4">
                            <label class="form-label fw-bold">{{ $loop->iteration }}. {{ $question }}</label>
                            <textarea class="form-control" name="answers[{{ $index }}]" rows="3"
                                placeholder="Your answer..." required></textarea>
                        </div>
                        @endforeach

                        <div class="mb-4">
                            <label class="form-label fw-bold">Overall Rating (Optional)</label>
                            <div class="rating-stars text-center">
                                <div class="d-flex justify-content-center gap-2">
                                    @for($i = 5; $i >= 1; $i--)
                                    <div class="form-check form-check-inline">
                                        <input class="form-check-input rating-input" type="radio" name="rating"
                                            value="{{ $i }}" id="starDetail{{ $i }}">
                                        <label class="form-check-label rating-label" for="starDetail{{ $i }}">
                                            <i class="bi bi-star-fill"></i> {{ $i }}
                                        </label>
                                    </div>
                                    @endfor
                                </div>
                            </div>
                        </div>

                        <div class="text-end">
                            <button type="submit" class="btn btn-primary" id="submitBtn">
                                <i class="bi bi-send"></i> Submit Feedback
                            </button>
                        </div>
                    </form>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Success Modal -->
<div class="modal fade" id="successModal" data-bs-backdrop="static" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header bg-success text-white">
                <h5 class="modal-title">Thank You!</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body text-center">
                <i class="bi bi-check-circle-fill text-success" style="font-size: 4rem;"></i>
                <h4 class="mt-3">Feedback Submitted Successfully!</h4>
                <p>Thank you for sharing your feedback. Your input helps us improve.</p>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-primary" onclick="redirectToDashboard()">Go to Dashboard</button>
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Stay Here</button>
            </div>
        </div>
    </div>
</div>

<!-- Error Modal -->
<div class="modal fade" id="errorModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header bg-danger text-white">
                <h5 class="modal-title">Error</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body text-center">
                <i class="bi bi-exclamation-triangle-fill text-danger" style="font-size: 4rem;"></i>
                <h4 class="mt-3">Submission Failed</h4>
                <p id="errorMessage">Please try again.</p>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
            </div>
        </div>
    </div>
</div>
@endsection

@push('styles')
<style>
    .rating-stars .form-check {
        padding: 0;
    }

    .rating-stars .rating-input {
        display: none;
    }

    .rating-stars .rating-label {
        cursor: pointer;
        padding: 10px 15px;
        border-radius: 8px;
        background: #f8f9fa;
        border: 1px solid #dee2e6;
        transition: all 0.3s;
    }

    .rating-stars .rating-input:checked+.rating-label {
        background: #ffc107;
        color: white;
        border-color: #ffc107;
    }

    .rating-stars .rating-label:hover {
        background: #ffc107;
        color: white;
        border-color: #ffc107;
        transform: scale(1.05);
    }

    .rating-stars .rating-label i {
        margin-right: 5px;
    }

    .btn-loading {
        pointer-events: none;
        opacity: 0.7;
    }
</style>
@endpush

@push('scripts')
<script>
    // Update rating text for quick feedback
    document.querySelectorAll('.rating-input').forEach(input => {
        input.addEventListener('change', function() {
            const rating = this.value;
            const ratingText = document.getElementById('ratingText');
            let text = '';
            switch(rating) {
                case '5': text = 'Excellent! ⭐⭐⭐⭐⭐'; break;
                case '4': text = 'Very Good ⭐⭐⭐⭐'; break;
                case '3': text = 'Good ⭐⭐⭐'; break;
                case '2': text = 'Fair ⭐⭐'; break;
                case '1': text = 'Poor ⭐'; break;
                default: text = 'Select a rating';
            }
            if (ratingText) ratingText.textContent = text;
        });
    });

    function showSuccessModal() {
        const modalElement = document.getElementById('successModal');
        if (modalElement) {
            // Check if Bootstrap is available
            if (typeof bootstrap !== 'undefined') {
                const modal = new bootstrap.Modal(modalElement);
                modal.show();
            } else {
                // Fallback - show alert
                alert('Feedback submitted successfully! Thank you for your feedback.');
                window.location.href = '{{ route("student.dashboard") }}';
            }
        } else {
            alert('Feedback submitted successfully! Thank you for your feedback.');
            window.location.href = '{{ route("student.dashboard") }}';
        }
    }

    function showErrorModal(message) {
        const errorMsg = document.getElementById('errorMessage');
        if (errorMsg) {
            errorMsg.textContent = message;
        }

        const modalElement = document.getElementById('errorModal');
        if (modalElement && typeof bootstrap !== 'undefined') {
            const modal = new bootstrap.Modal(modalElement);
            modal.show();
        } else {
            alert('Error: ' + message);
        }
    }

    function redirectToDashboard() {
        window.location.href = '{{ route("student.dashboard") }}';
    }

    // Handle form submission with AJAX
    const form = document.getElementById('feedbackForm');
    if (form) {
        form.addEventListener('submit', function(e) {
            e.preventDefault();

            const submitBtn = document.getElementById('submitBtn');
            const originalText = submitBtn.innerHTML;

            // Show loading state
            submitBtn.disabled = true;
            submitBtn.innerHTML = '<span class="spinner-border spinner-border-sm me-2"></span>Submitting...';

            const formData = new FormData(this);

            fetch(this.action, {
                method: 'POST',
                headers: {
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                    'Accept': 'application/json'
                },
                body: formData
            })
            .then(response => response.json())
            .then(data => {
                console.log('Response:', data); // Debug log

                if (data.status === 'success') {
                    // Reset form
                    form.reset();

                    // Reset rating stars display
                    document.querySelectorAll('.rating-input').forEach(input => {
                        input.checked = false;
                    });
                    const ratingText = document.getElementById('ratingText');
                    if (ratingText) ratingText.textContent = 'Select a rating';

                    // Show success modal
                    showSuccessModal();
                } else {
                    // Show error modal
                    showErrorModal(data.message || 'Submission failed. Please try again.');
                }
            })
            .catch(error => {
                console.error('Fetch Error:', error);
                showErrorModal('An error occurred. Please try again.');
            })
            .finally(() => {
                // Reset button state
                submitBtn.disabled = false;
                submitBtn.innerHTML = originalText;
            });
        });
    }

    // Ensure redirect button works
    document.getElementById('redirectBtn')?.addEventListener('click', redirectToDashboard);
</script>
@endpush