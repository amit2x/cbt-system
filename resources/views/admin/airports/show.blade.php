@extends('layouts.admin')

@section('title', $airport->name)

@section('content')
<div class="container-fluid">
    <div class="d-sm-flex align-items-center justify-content-between mb-4">
        <h1 class="h3 mb-0 text-gray-800">{{ $airport->full_name }}</h1>
        <div>
            <a href="{{ route('admin.airports.edit', $airport) }}" class="btn btn-warning">
                <i class="fas fa-edit"></i> Edit
            </a>
            <a href="{{ route('admin.airports.index') }}" class="btn btn-secondary">
                <i class="fas fa-arrow-left"></i> Back
            </a>
        </div>
    </div>

    <div class="row">
        <!-- Airport Info -->
        <div class="col-xl-4 col-lg-5">
            <div class="card shadow mb-4">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-primary">Airport Information</h6>
                </div>
                <div class="card-body text-center">
                    @if($airport->logo)
                        <img src="{{ asset('storage/' . $airport->logo) }}" 
                             alt="{{ $airport->name }}" 
                             class="img-fluid rounded mb-3" 
                             style="max-height: 150px;">
                    @else
                        <div class="bg-light rounded p-4 mb-3">
                            <i class="fas fa-plane fa-5x text-gray-300"></i>
                        </div>
                    @endif

                    <h4>{{ $airport->name }}</h4>
                    <p>
                        <span class="badge bg-primary">{{ $airport->code }}</span>
                        @if($airport->icao_code)
                            <span class="badge bg-secondary">{{ $airport->icao_code }}</span>
                        @endif
                        {!! $airport->status_badge !!}
                    </p>

                    <hr>

                    <div class="text-left">
                        <p><strong>City:</strong> {{ $airport->city ?? '-' }}</p>
                        <p><strong>Country:</strong> {{ $airport->country ?? '-' }}</p>
                        <p><strong>Timezone:</strong> {{ $airport->timezone }}</p>
                        <p><strong>Slug:</strong> <code>{{ $airport->slug }}</code></p>
                        @if($airport->domain)
                            <p><strong>Domain:</strong> <code>{{ $airport->domain }}</code></p>
                        @endif
                        @if($airport->phone)
                            <p><strong>Phone:</strong> {{ $airport->phone }}</p>
                        @endif
                        @if($airport->email)
                            <p><strong>Email:</strong> {{ $airport->email }}</p>
                        @endif
                        @if($airport->website)
                            <p><strong>Website:</strong> <a href="{{ $airport->website }}" target="_blank">{{ $airport->website }}</a></p>
                        @endif
                        @if($airport->address)
                            <p><strong>Address:</strong> {{ $airport->address }}</p>
                        @endif
                    </div>
                </div>
            </div>

            <!-- Subscription Info -->
            <div class="card shadow mb-4">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-primary">Subscription</h6>
                </div>
                <div class="card-body">
                    <p><strong>Plan:</strong> <span class="badge bg-info">{{ ucfirst($airport->subscription_plan) }}</span></p>
                    @if($airport->subscription_expires_at)
                        <p><strong>Expires:</strong> {{ $airport->subscription_expires_at->format('d M Y H:i') }}</p>
                        <p>
                            <strong>Status:</strong>
                            @if($airport->subscription_expires_at->isFuture())
                                <span class="badge bg-success">Active</span>
                            @else
                                <span class="badge bg-danger">Expired</span>
                            @endif
                        </p>
                    @endif

                    <div class="mt-3">
                        <p><strong>Usage:</strong></p>
                        <div class="progress" style="height: 20px;">
                            <div class="progress-bar" role="progressbar" 
                                 style="width: {{ $stats['usage_percentage'] }}%;" 
                                 aria-valuenow="{{ $stats['usage_percentage'] }}" 
                                 aria-valuemin="0" 
                                 aria-valuemax="100">
                                {{ $stats['usage_percentage'] }}%
                            </div>
                        </div>
                        <small class="text-muted">
                            {{ $airport->max_students - $stats['students'] }} slots available
                        </small>
                    </div>
                </div>
            </div>
        </div>

        <!-- Statistics -->
        <div class="col-xl-8 col-lg-7">
            <div class="row">
                <div class="col-md-6 mb-4">
                    <div class="card border-left-primary shadow h-100 py-2">
                        <div class="card-body">
                            <div class="row no-gutters align-items-center">
                                <div class="col mr-2">
                                    <div class="text-xs font-weight-bold text-primary text-uppercase mb-1">
                                        Total Users</div>
                                    <div class="h5 mb-0 font-weight-bold text-gray-800">{{ $stats['total_users'] }}</div>
                                </div>
                                <div class="col-auto">
                                    <i class="fas fa-users fa-2x text-gray-300"></i>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="col-md-6 mb-4">
                    <div class="card border-left-success shadow h-100 py-2">
                        <div class="card-body">
                            <div class="row no-gutters align-items-center">
                                <div class="col mr-2">
                                    <div class="text-xs font-weight-bold text-success text-uppercase mb-1">
                                        Students</div>
                                    <div class="h5 mb-0 font-weight-bold text-gray-800">{{ $stats['students'] }}</div>
                                </div>
                                <div class="col-auto">
                                    <i class="fas fa-user-graduate fa-2x text-gray-300"></i>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="col-md-6 mb-4">
                    <div class="card border-left-info shadow h-100 py-2">
                        <div class="card-body">
                            <div class="row no-gutters align-items-center">
                                <div class="col mr-2">
                                    <div class="text-xs font-weight-bold text-info text-uppercase mb-1">
                                        Instructors</div>
                                    <div class="h5 mb-0 font-weight-bold text-gray-800">{{ $stats['instructors'] }}</div>
                                </div>
                                <div class="col-auto">
                                    <i class="fas fa-chalkboard-teacher fa-2x text-gray-300"></i>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="col-md-6 mb-4">
                    <div class="card border-left-warning shadow h-100 py-2">
                        <div class="card-body">
                            <div class="row no-gutters align-items-center">
                                <div class="col mr-2">
                                    <div class="text-xs font-weight-bold text-warning text-uppercase mb-1">
                                        Courses</div>
                                    <div class="h5 mb-0 font-weight-bold text-gray-800">{{ $stats['courses'] }}</div>
                                </div>
                                <div class="col-auto">
                                    <i class="fas fa-book fa-2x text-gray-300"></i>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="col-md-6 mb-4">
                    <div class="card border-left-danger shadow h-100 py-2">
                        <div class="card-body">
                            <div class="row no-gutters align-items-center">
                                <div class="col mr-2">
                                    <div class="text-xs font-weight-bold text-danger text-uppercase mb-1">
                                        Exams</div>
                                    <div class="h5 mb-0 font-weight-bold text-gray-800">{{ $stats['exams'] }}</div>
                                    <small>Active: {{ $stats['active_exams'] }} | Completed: {{ $stats['completed_exams'] }}</small>
                                </div>
                                <div class="col-auto">
                                    <i class="fas fa-file-alt fa-2x text-gray-300"></i>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="col-md-6 mb-4">
                    <div class="card border-left-secondary shadow h-100 py-2">
                        <div class="card-body">
                            <div class="row no-gutters align-items-center">
                                <div class="col mr-2">
                                    <div class="text-xs font-weight-bold text-secondary text-uppercase mb-1">
                                        Questions</div>
                                    <div class="h5 mb-0 font-weight-bold text-gray-800">{{ $stats['questions'] }}</div>
                                </div>
                                <div class="col-auto">
                                    <i class="fas fa-question-circle fa-2x text-gray-300"></i>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Recent Activity -->
            <div class="card shadow mb-4">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-primary">Recent Activity</h6>
                </div>
                <div class="card-body">
                    <ul class="nav nav-tabs" id="activityTabs" role="tablist">
                        <li class="nav-item">
                            <a class="nav-link active" id="users-tab" data-toggle="tab" href="#users" role="tab">
                                Recent Users
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" id="exams-tab" data-toggle="tab" href="#exams" role="tab">
                                Recent Exams
                            </a>
                        </li>
                    </ul>

                    <div class="tab-content mt-3">
                        <div class="tab-pane fade show active" id="users" role="tabpanel">
                            @if($recentUsers->count())
                                <ul class="list-group">
                                    @foreach($recentUsers as $user)
                                        <li class="list-group-item d-flex justify-content-between align-items-center">
                                            <div>
                                                <strong>{{ $user->name }}</strong>
                                                <small class="text-muted">{{ $user->email }}</small>
                                            </div>
                                            <span class="badge bg-info">{{ $user->created_at->diffForHumans() }}</span>
                                        </li>
                                    @endforeach
                                </ul>
                            @else
                                <p class="text-muted">No recent users</p>
                            @endif
                        </div>

                        <div class="tab-pane fade" id="exams" role="tabpanel">
                            @if($recentExams->count())
                                <ul class="list-group">
                                    @foreach($recentExams as $exam)
                                        <li class="list-group-item d-flex justify-content-between align-items-center">
                                            <div>
                                                <strong>{{ $exam->title }}</strong>
                                                <small class="text-muted">Status: {{ $exam->status }}</small>
                                            </div>
                                            <span class="badge bg-info">{{ $exam->created_at->diffForHumans() }}</span>
                                        </li>
                                    @endforeach
                                </ul>
                            @else
                                <p class="text-muted">No recent exams</p>
                            @endif
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection