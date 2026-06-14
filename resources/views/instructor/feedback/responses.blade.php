@extends('layouts.instructor')

@section('title', 'Feedback Responses')

@section('content')
<div class="container-fluid">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h1 class="h3 mb-0 text-gray-800">Feedback Responses</h1>
        <div>
            <a href="{{ route('instructor.feedback.create') }}" class="btn btn-primary">
                <i class="bi bi-plus-circle"></i> New Feedback
            </a>
            <a href="{{ route('instructor.dashboard') }}" class="btn btn-secondary">
                <i class="bi bi-arrow-left"></i> Back
            </a>
        </div>
    </div>

    <!-- Summary Card -->
    <div class="card shadow mb-4">
        <div class="card-header py-3">
            <h6 class="m-0 font-weight-bold text-primary">Feedback Summary</h6>
        </div>
        <div class="card-body">
            <div class="row">
                <div class="col-md-4 text-center">
                    <h3 class="text-primary">{{ $summary['total_responses'] }}</h3>
                    <p class="text-muted">Total Responses</p>
                </div>
                <div class="col-md-4 text-center">
                    <h3 class="text-success">{{ $summary['average_rating'] ?? 'N/A' }}</h3>
                    <p class="text-muted">Average Rating</p>
                </div>
                <div class="col-md-4 text-center">
                    <h3 class="text-info">{{ $feedback->batch->students->count() }}</h3>
                    <p class="text-muted">Total Students</p>
                </div>
            </div>
        </div>
    </div>

    <!-- Rating Distribution (if applicable) -->
    @if(isset($summary['rating_distribution']))
    <div class="card shadow mb-4">
        <div class="card-header py-3">
            <h6 class="m-0 font-weight-bold text-primary">Rating Distribution</h6>
        </div>
        <div class="card-body">
            <canvas id="ratingChart" height="100"></canvas>
        </div>
    </div>
    @endif

    <!-- Question-wise Responses -->
    <div class="card shadow mb-4">
        <div class="card-header py-3">
            <h6 class="m-0 font-weight-bold text-primary">Question-wise Responses</h6>
        </div>
        <div class="card-body">
            @if($feedback->type === 'quick')
            <div class="mb-4">
                <h6>Rating Summary</h6>
                <div class="row">
                    @for($i = 5; $i >= 1; $i--)
                    @php
                    $count = $responses->where('rating', $i)->count();
                    $percentage = $summary['total_responses'] > 0 ? round(($count / $summary['total_responses']) * 100,
                    1) : 0;
                    @endphp
                    <div class="col-md-2 mb-3 text-center">
                        <div class="bg-light p-3 rounded">
                            <h2 class="text-warning">{{ $i }} <i class="bi bi-star-fill"></i></h2>
                            <p class="mb-0">{{ $count }} responses</p>
                            <small class="text-muted">{{ $percentage }}%</small>
                        </div>
                    </div>
                    @endfor
                </div>
            </div>

            @if(isset($summary['comments']) && count($summary['comments']) > 0)
            <h6>Student Comments</h6>
            <div class="list-group mt-2">
                @foreach($summary['comments'] as $comment)
                <div class="list-group-item">
                    <i class="bi bi-chat-quote me-2 text-primary"></i>
                    "{{ $comment['text'] }}"
                    <br>
                    <small class="text-muted">{{ $comment['rating'] }} ★</small>
                </div>
                @endforeach
            </div>
            @endif
            @else
            @foreach($summary['responses_by_question'] as $index => $questionData)
            <div class="mb-4">
                <h6>Q{{ $index + 1 }}: {{ $questionData['question'] }}</h6>
                <div class="list-group mt-2">
                    @if(count($questionData['responses']) > 0)
                    @foreach($questionData['responses'] as $response)
                    <div class="list-group-item">
                        <i class="bi bi-chat-dots me-2 text-primary"></i>
                        {{ $response }}
                    </div>
                    @endforeach
                    @else
                    <div class="list-group-item text-muted">No responses yet</div>
                    @endif
                </div>
            </div>
            @endforeach
            @endif
        </div>
    </div>

    <!-- Individual Responses -->
    <div class="card shadow mb-4">
        <div class="card-header py-3 d-flex justify-content-between align-items-center">
            <h6 class="m-0 font-weight-bold text-primary">Individual Student Responses</h6>
            <button class="btn btn-sm btn-success" onclick="exportResponses()">
                <i class="bi bi-download"></i> Export
            </button>
        </div>
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-bordered">
                    <thead>
                        <tr>
                            <th>Student</th>
                            <th>Submitted At</th>
                            <th>Rating</th>
                            <th>Response</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($responses as $response)
                        <tr>
                            <td>
                                <div class="d-flex align-items-center">
                                    <img src="{{ $response->student->avatar_url }}" class="rounded-circle me-2"
                                        width="30" height="30">
                                    <div>
                                        {{ $response->student->name }}
                                        <br>
                                        <small class="text-muted">{{ $response->student->email }}</small>
                                    </div>
                                </div>
                            </td>
                            <td>{{ $response->submitted_at->format('d M Y H:i') }}</td>
                            <td>
                                @if($response->rating)
                                @for($i = 1; $i <= 5; $i++) <i
                                    class="bi bi-star{{ $i <= $response->rating ? '-fill' : '' }} text-warning"></i>
                                    @endfor
                                    @else
                                    N/A
                                    @endif
                            </td>
                            <td>
                                @if($response->answers)
                                <ul class="mb-0">
                                    @foreach($response->answers as $answer)
                                    <li>{{ $answer }}</li>
                                    @endforeach
                                </ul>
                                @elseif($response->comment)
                                {{ $response->comment }}
                                @else
                                -
                                @endif
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
    @if(isset($summary['rating_distribution']))
    const ratingCtx = document.getElementById('ratingChart').getContext('2d');
    new Chart(ratingCtx, {
        type: 'bar',
        data: {
            labels: ['5 Stars', '4 Stars', '3 Stars', '2 Stars', '1 Star'],
            datasets: [{
                label: 'Number of Responses',
                data: [
                    {{ $summary['rating_distribution'][5] ?? 0 }},
                    {{ $summary['rating_distribution'][4] ?? 0 }},
                    {{ $summary['rating_distribution'][3] ?? 0 }},
                    {{ $summary['rating_distribution'][2] ?? 0 }},
                    {{ $summary['rating_distribution'][1] ?? 0 }}
                ],
                backgroundColor: ['#ffc107', '#ffc107', '#ffc107', '#ffc107', '#ffc107']
            }]
        }
    });
    @endif

    function exportResponses() {
        window.location.href = '{{ route("instructor.feedback.export", $feedback) }}';
    }
</script>
@endpush
@endsection