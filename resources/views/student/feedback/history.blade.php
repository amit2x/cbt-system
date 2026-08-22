@extends('layouts.student')

@section('title', 'My Feedback History')

@section('content')
<div class="container-fluid">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h1 class="h3 mb-0 text-gray-800">My Feedback History</h1>
    </div>

    <!-- Stats Cards -->
<div class="row mb-4">
    <!-- Total Feedback -->
    <div class="col-6 col-md-4 mb-4">
        <div class="card border-left-primary shadow h-100 py-2">
            <div class="card-body">
                <div class="row g-0 align-items-center">
                    <div class="col mr-2">
                        <div class="text-xs font-weight-bold text-primary text-uppercase mb-1">
                            Total Feedback</div>
                        <div class="h5 mb-0 font-weight-bold text-gray-800">{{ $stats['total'] }}</div>
                    </div>
                    <div class="col-auto">
                        <i class="bi bi-chat-dots-fill fs-2 text-primary"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Average Rating -->
    <div class="col-6 col-md-4 mb-4">
        <div class="card border-left-success shadow h-100 py-2">
            <div class="card-body">
                <div class="row g-0 align-items-center">
                    <div class="col mr-2">
                        <div class="text-xs font-weight-bold text-success text-uppercase mb-1">
                            Average Rating</div>
                        <div class="h5 mb-0 font-weight-bold text-gray-800">
                            {{ $stats['average_rating'] ? number_format($stats['average_rating'], 1) . ' / 5' : 'N/A' }}
                        </div>
                    </div>
                    <div class="col-auto">
                        <i class="bi bi-star-fill fs-2 text-success"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Last 30 Days -->
    <div class="col-6 col-md-4 mb-4">
        <div class="card border-left-info shadow h-100 py-2">
            <div class="card-body">
                <div class="row g-0 align-items-center">
                    <div class="col mr-2">
                        <div class="text-xs font-weight-bold text-info text-uppercase mb-1">
                            Last 30 Days</div>
                        <div class="h5 mb-0 font-weight-bold text-gray-800">{{ $stats['recent'] }}</div>
                    </div>
                    <div class="col-auto">
                        <i class="bi bi-calendar-check fs-2 text-info"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

    <!-- Feedback List -->
    <div class="card shadow mb-4">
        <div class="card-header py-3">
            <h6 class="m-0 font-weight-bold text-primary">Feedback Responses</h6>
        </div>
        <div class="card-body">
            @if($responses->count() > 0)
            <div class="table-responsive">
                <table class="table table-bordered">
                    <thead>
                        <tr>
                            <th>Date</th>
                            <th>Course</th>
                            <th>Batch</th>
                            <th>Instructor</th>
                            <th>Rating</th>
                            <th>Feedback</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($responses as $response)
                        <tr>
                            <td>{{ $response->submitted_at->format('d M Y') }}</td>
                            <td>{{ $response->feedback->batch->course->title ?? 'N/A' }}</td>
                            <td>{{ $response->feedback->batch->name ?? 'N/A' }}</td>
                            <td>{{ $response->feedback->instructor->name ?? 'N/A' }}</td>
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
                                @if($response->comment)
                                <span>{{ Str::limit($response->comment, 100) }}</span>
                                @elseif($response->answers)
                                <span class="badge bg-info">Answered {{ count($response->answers) }} questions</span>
                                @else
                                -
                                @endif
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

            <div class="d-flex justify-content-center mt-4">
                {{ $responses->links() }}
            </div>
            @else
            <div class="text-center py-4">
                <i class="bi bi-chat-dots fs-1 text-muted"></i>
                <p class="mt-2">No feedback history found.</p>
            </div>
            @endif
        </div>
    </div>
</div>
@endsection