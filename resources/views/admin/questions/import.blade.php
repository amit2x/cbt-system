@extends('layouts.admin')

@section('title', 'Import Questions')

@section('content')
<div class="container-fluid">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h1 class="h3 mb-0 text-gray-800">Import Questions</h1>
        <a href="{{ route('admin.questions.index') }}" class="btn btn-secondary">
            <i class="bi bi-arrow-left"></i> Back to List
        </a>
    </div>

    <div class="row">
        <div class="col-md-8">
            <div class="card shadow mb-4">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-primary">Upload File</h6>
                </div>
                <div class="card-body">
                    <form action="{{ route('admin.questions.import') }}" method="POST" enctype="multipart/form-data">
                        @csrf

                        <div class="mb-3">
                            <label for="file" class="form-label">Excel File <span class="text-danger">*</span></label>
                            <input type="file" class="form-control @error('file') is-invalid @enderror"
                                   id="file" name="file" accept=".xlsx,.xls,.csv" required>
                            @error('file')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                            <small class="text-muted">
                                Supported formats: .xlsx, .xls, .csv (Max: 10MB)
                            </small>
                        </div>

                        <div class="mb-3">
                            <label for="type" class="form-label">Question Type (Optional)</label>
                            <select class="form-control @error('type') is-invalid @enderror" id="type" name="type">
                                <option value="">Auto-detect from file</option>
                                <option value="single_choice">Single Choice</option>
                                <option value="multiple_choice">Multiple Choice</option>
                                <option value="numerical">Numerical</option>
                            </select>
                            @error('type')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                            <small class="text-muted">Select if all questions in file are of same type</small>
                        </div>

                        <button type="submit" class="btn btn-primary">
                            <i class="bi bi-upload"></i> Import Questions
                        </button>
                    </form>
                </div>
            </div>
        </div>

        <div class="col-md-4">
            <!-- Instructions -->
            <div class="card shadow mb-4">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-primary">Instructions</h6>
                </div>
                <div class="card-body">
                    <div class="alert alert-info">
                        <h6>File Format Requirements:</h6>
                        <ul class="mb-0">
                            <li>First row must contain column headers</li>
                            <li>Required columns: question_text, type, difficulty, marks</li>
                            <li>For MCQ: option_1, option_1_correct, option_2, option_2_correct, etc.</li>
                            <li>For numerical: correct_numerical, tolerance (optional)</li>
                            <li>Max 4 options per question</li>
                        </ul>
                    </div>

                    <hr>

                    <h6>Sample Data:</h6>
                    <pre class="bg-light p-2 rounded" style="font-size: 12px;">
question_text,type,difficulty,marks,negative_marks,option_1,option_1_correct,option_2,option_2_correct
"What is 2+2?",single_choice,easy,1,0,"3",0,"4",1
"Select primes:",multiple_choice,medium,2,0.5,"2",1,"3",1,"4",0,"5",1
                    </pre>

                    <a href="{{ route('admin.questions.template') }}" class="btn btn-success w-100 mt-3">
                        <i class="bi bi-download"></i> Download Template
                    </a>
                </div>
            </div>

            <!-- Notes -->
            <div class="card shadow mb-4">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-primary">Important Notes</h6>
                </div>
                <div class="card-body">
                    <ul class="mb-0">
                        <li>Use 1 for correct, 0 for incorrect</li>
                        <li>Difficulty: easy, medium, hard</li>
                        <li>Type: single_choice, multiple_choice, numerical</li>
                        <li>Max file size: 10MB</li>
                        <li>All questions will be set to active status</li>
                    </ul>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
