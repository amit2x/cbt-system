{{-- resources/views/admin/results/moderation/show.blade.php --}}

@extends('layouts.admin')

@section('title', 'Review Result - ' . $result->student->name)

@section('content')
<div class="container-fluid">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h1 class="h3 mb-0">Review Answer Sheet</h1>
            <p class="text-muted small mb-0">
                Student: <strong>{{ $result->student->name }}</strong> |
                Exam: <strong>{{ $result->exam->title }}</strong>
            </p>
        </div>
        <div>
            <button type="button" class="btn btn-success" onclick="publishResult()">
                <i class="bi bi-check-circle"></i> Publish Result
            </button>
            <a href="{{ route('admin.results.moderation.index', $result->exam_id) }}" class="btn btn-secondary">
                <i class="bi bi-arrow-left"></i> Back
            </a>
        </div>
    </div>

    <!-- Summary -->
    <div class="card shadow mb-4">
        <div class="card-body">
            <div class="row text-center">
                <div class="col-md-3">
                    <h5 class="text-success" id="correctCount">{{ $result->correct_answers }}</h5><small>Correct</small>
                </div>
                <div class="col-md-3">
                    <h5 class="text-danger" id="incorrectCount">{{ $result->incorrect_answers }}</h5>
                    <small>Incorrect</small>
                </div>
                <div class="col-md-3">
                    <h5 class="text-secondary">{{ $result->unanswered }}</h5><small>Unanswered</small>
                </div>
                <div class="col-md-3">
                    <h5 class="text-primary" id="percentageVal">{{ round($result->percentage, 1) }}%</h5>
                    <small>Percentage</small>
                </div>
            </div>
            <hr>
            <div class="text-center">
                <span class="badge bg-{{ $result->is_published ? 'success' : 'warning' }} fs-6">
                    {{ $result->is_published ? 'Published' : 'Draft' }}
                </span>
            </div>
        </div>
    </div>

    <!-- Answer Sheet -->
    <div class="card shadow">
        <div class="card-header">
            <h6 class="mb-0">Answers (Click to edit)</h6>
        </div>
        <div class="card-body">
            @foreach($result->studentExam->answers as $i => $answer)
            <div class="card mb-3 answer-block" data-answer-id="{{ $answer->id }}">
                <div class="card-header bg-light d-flex justify-content-between">
                    <strong>Q{{ $i + 1 }}: {{ Str::limit($answer->question->question_text, 100) }}</strong>
                    <span
                        class="badge bg-{{ $answer->is_correct ? 'success' : ($answer->selected_options || $answer->answer_text ? 'danger' : 'secondary') }} status-badge">
                        {{ $answer->is_correct ? 'Correct' : ($answer->selected_options || $answer->answer_text ?
                        'Incorrect' : 'Unanswered') }}
                    </span>
                </div>
                <div class="card-body">
                    @if(in_array($answer->question->type, ['single_choice', 'multiple_choice']))
                    <div class="row">
                        @foreach($answer->question->options as $option)
                        @php $isSelected = in_array($option->id, $answer->selected_options ?? []); @endphp
                        <div class="col-md-6 mb-2">
                            <label
                                class="d-block p-2 rounded border
                                            {{ $option->is_correct ? 'border-success bg-success bg-opacity-10' : '' }}">
                                <input type="{{ $answer->question->type === 'multiple_choice' ? 'checkbox' : 'radio' }}"
                                    name="answer_{{ $answer->id }}" value="{{ $option->id }}" class="option-input"
                                    data-answer-id="{{ $answer->id }}" {{ $isSelected ? 'checked' : '' }}>
                                {{ $option->option_text }}
                                @if($option->is_correct)
                                <i class="bi bi-check-circle-fill text-success float-end"></i>
                                @endif
                            </label>
                        </div>
                        @endforeach
                    </div>
                    @elseif($answer->question->type === 'numerical')
                    <div class="row align-items-center">
                        <div class="col-md-6">
                            <label>Your Answer</label>
                            <input type="number" class="form-control numerical-input" data-answer-id="{{ $answer->id }}"
                                value="{{ $answer->answer_text ?? '' }}" step="any">
                        </div>
                        <div class="col-md-6">
                            <label>Correct Answer</label>
                            <input type="text" class="form-control" disabled
                                value="{{ $answer->question->options->first()->option_text ?? 'N/A' }}">
                        </div>
                    </div>
                    @endif
                    <small class="text-muted d-block mt-2">
                        Marks: <span class="marks-val">{{ $answer->marks_obtained ?? 0 }}</span>
                    </small>
                </div>
            </div>
            @endforeach
        </div>
    </div>
</div>

@push('scripts')
<script>
    document.querySelectorAll('.option-input, .numerical-input').forEach(input => {
    input.addEventListener('change', async function () {
        const answerId = this.dataset.answerId;
        const block = document.querySelector(`.answer-block[data-answer-id="${answerId}"]`);

        let payload = { _method: 'PATCH' };

        if (this.classList.contains('numerical-input')) {
            payload.answer_text = this.value;
        } else {
            // Collect all checked options for this answer
            const answerName = this.getAttribute('name');
            const checked = Array.from(document.querySelectorAll(`input[name="${answerName}"]:checked`))
                                 .map(el => parseInt(el.value));
            payload.selected_options = checked;
        }

        const res = await fetch(`/admin/answers/${answerId}`, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': '{{ csrf_token() }}'
            },
            body: JSON.stringify(payload)
        });

        const data = await res.json();

        if (data.status === 'success') {
            // Update UI
            block.querySelector('.status-badge').textContent = data.data.is_correct ? 'Correct' : 'Incorrect';
            block.querySelector('.status-badge').className = 'badge status-badge bg-' + (data.data.is_correct ? 'success' : 'danger');
            block.querySelector('.marks-val').textContent = data.data.marks_obtained;
            document.getElementById('percentageVal').textContent = data.data.percentage + '%';
            toastr.success('Answer updated');
        } else {
            toastr.error(data.message || 'Error updating answer');
        }
    });
});

async function publishResult() {
    if (!confirm('Publish this result? The student will be able to see it.')) return;

    const res = await fetch(`/admin/results-moderation/{{ $result->id }}/publish`, {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': '{{ csrf_token() }}'
        }
    });
    const data = await res.json();

    if (data.status === 'success') {
        toastr.success(data.message);
        setTimeout(() => location.reload(), 1000);
    } else {
        toastr.error(data.message);
    }
}
</script>
@endpush
@endsection