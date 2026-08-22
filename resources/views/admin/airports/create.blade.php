@extends('layouts.admin')

@section('title', 'Create New Airport')

@section('content')
<div class="container-fluid">
    <div class="d-sm-flex align-items-center justify-content-between mb-4">
        <h1 class="h3 mb-0 text-gray-800">Create New Airport</h1>
        <a href="{{ route('admin.airports.index') }}" class="btn btn-secondary">
            <i class="fas fa-arrow-left"></i> Back to List
        </a>
    </div>

    <div class="card shadow mb-4">
        <div class="card-body">
            <form action="{{ route('admin.airports.store') }}" method="POST" enctype="multipart/form-data">
                @csrf

                <div class="row">
                    <!-- Basic Information -->
                    <div class="col-md-6">
                        <h5 class="border-bottom pb-2">Basic Information</h5>
                        
                        <div class="form-group mb-3">
                            <label for="name">Airport Name <span class="text-danger">*</span></label>
                            <input type="text" name="name" id="name" 
                                   class="form-control @error('name') is-invalid @enderror"
                                   value="{{ old('name') }}" required>
                            @error('name')
                                <span class="invalid-feedback">{{ $message }}</span>
                            @enderror
                        </div>

                        <div class="form-group mb-3">
                            <label for="code">IATA Code <span class="text-danger">*</span></label>
                            <input type="text" name="code" id="code" 
                                   class="form-control @error('code') is-invalid @enderror"
                                   value="{{ old('code') }}" 
                                   placeholder="e.g., CCU, DEL, BOM" 
                                   maxlength="10" required>
                            @error('code')
                                <span class="invalid-feedback">{{ $message }}</span>
                            @enderror
                            <small class="text-muted">Unique 3-letter IATA airport code</small>
                        </div>

                        <div class="form-group mb-3">
                            <label for="icao_code">ICAO Code</label>
                            <input type="text" name="icao_code" id="icao_code" 
                                   class="form-control @error('icao_code') is-invalid @enderror"
                                   value="{{ old('icao_code') }}" 
                                   placeholder="e.g., VECC, VIDP" 
                                   maxlength="4">
                            @error('icao_code')
                                <span class="invalid-feedback">{{ $message }}</span>
                            @enderror
                        </div>

                        <div class="form-group mb-3">
                            <label for="slug">Slug <span class="text-danger">*</span></label>
                            <input type="text" name="slug" id="slug" 
                                   class="form-control @error('slug') is-invalid @enderror"
                                   value="{{ old('slug') }}" required>
                            @error('slug')
                                <span class="invalid-feedback">{{ $message }}</span>
                            @enderror
                            <small class="text-muted">URL-friendly version of the name (e.g., nscbi-airport-kolkata)</small>
                        </div>

                        <div class="form-group mb-3">
                            <label for="domain">Custom Domain</label>
                            <input type="text" name="domain" id="domain" 
                                   class="form-control @error('domain') is-invalid @enderror"
                                   value="{{ old('domain') }}" 
                                   placeholder="e.g., delhi.cbt-system.com">
                            @error('domain')
                                <span class="invalid-feedback">{{ $message }}</span>
                            @enderror
                        </div>
                    </div>

                    <!-- Location & Contact -->
                    <div class="col-md-6">
                        <h5 class="border-bottom pb-2">Location & Contact</h5>

                        <div class="form-group mb-3">
                            <label for="city">City</label>
                            <input type="text" name="city" id="city" 
                                   class="form-control @error('city') is-invalid @enderror"
                                   value="{{ old('city') }}">
                            @error('city')
                                <span class="invalid-feedback">{{ $message }}</span>
                            @enderror
                        </div>

                        <div class="form-group mb-3">
                            <label for="country">Country</label>
                            <select name="country" id="country" 
                                    class="form-control @error('country') is-invalid @enderror">
                                <option value="">Select Country</option>
                                @foreach($countries as $value => $label)
                                    <option value="{{ $value }}" {{ old('country') == $value ? 'selected' : '' }}>
                                        {{ $label }}
                                    </option>
                                @endforeach
                            </select>
                            @error('country')
                                <span class="invalid-feedback">{{ $message }}</span>
                            @enderror
                        </div>

                        <div class="form-group mb-3">
                            <label for="timezone">Timezone <span class="text-danger">*</span></label>
                            <select name="timezone" id="timezone" 
                                    class="form-control @error('timezone') is-invalid @enderror" required>
                                <option value="">Select Timezone</option>
                                @foreach($timezones as $value => $label)
                                    <option value="{{ $value }}" {{ old('timezone') == $value ? 'selected' : '' }}>
                                        {{ $label }}
                                    </option>
                                @endforeach
                            </select>
                            @error('timezone')
                                <span class="invalid-feedback">{{ $message }}</span>
                            @enderror
                        </div>

                        <div class="form-group mb-3">
                            <label for="address">Address</label>
                            <textarea name="address" id="address" 
                                      class="form-control @error('address') is-invalid @enderror"
                                      rows="2">{{ old('address') }}</textarea>
                            @error('address')
                                <span class="invalid-feedback">{{ $message }}</span>
                            @enderror
                        </div>

                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group mb-3">
                                    <label for="phone">Phone</label>
                                    <input type="text" name="phone" id="phone" 
                                           class="form-control @error('phone') is-invalid @enderror"
                                           value="{{ old('phone') }}">
                                    @error('phone')
                                        <span class="invalid-feedback">{{ $message }}</span>
                                    @enderror
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group mb-3">
                                    <label for="email">Email</label>
                                    <input type="email" name="email" id="email" 
                                           class="form-control @error('email') is-invalid @enderror"
                                           value="{{ old('email') }}">
                                    @error('email')
                                        <span class="invalid-feedback">{{ $message }}</span>
                                    @enderror
                                </div>
                            </div>
                        </div>

                        <div class="form-group mb-3">
                            <label for="website">Website</label>
                            <input type="url" name="website" id="website" 
                                   class="form-control @error('website') is-invalid @enderror"
                                   value="{{ old('website') }}" 
                                   placeholder="https://example.com">
                            @error('website')
                                <span class="invalid-feedback">{{ $message }}</span>
                            @enderror
                        </div>
                    </div>
                </div>

                <hr>

                <!-- Status & Subscription -->
                <div class="row">
                    <div class="col-md-6">
                        <h5 class="border-bottom pb-2">Status & Subscription</h5>

                        <div class="form-group mb-3">
                            <label for="status">Status <span class="text-danger">*</span></label>
                            <select name="status" id="status" 
                                    class="form-control @error('status') is-invalid @enderror" required>
                                <option value="active" {{ old('status') == 'active' ? 'selected' : '' }}>Active</option>
                                <option value="inactive" {{ old('status') == 'inactive' ? 'selected' : '' }}>Inactive</option>
                                <option value="suspended" {{ old('status') == 'suspended' ? 'selected' : '' }}>Suspended</option>
                                <option value="maintenance" {{ old('status') == 'maintenance' ? 'selected' : '' }}>Maintenance</option>
                            </select>
                            @error('status')
                                <span class="invalid-feedback">{{ $message }}</span>
                            @enderror
                        </div>

                        <div class="form-group mb-3">
                            <label for="subscription_plan">Subscription Plan <span class="text-danger">*</span></label>
                            <select name="subscription_plan" id="subscription_plan" 
                                    class="form-control @error('subscription_plan') is-invalid @enderror" required>
                                @foreach($subscriptionPlans as $plan)
                                    <option value="{{ $plan }}" {{ old('subscription_plan') == $plan ? 'selected' : '' }}>
                                        {{ ucfirst($plan) }}
                                    </option>
                                @endforeach
                            </select>
                            @error('subscription_plan')
                                <span class="invalid-feedback">{{ $message }}</span>
                            @enderror
                        </div>

                        <div class="form-group mb-3">
                            <label for="subscription_expires_at">Subscription Expiry Date</label>
                            <input type="datetime-local" name="subscription_expires_at" id="subscription_expires_at" 
                                   class="form-control @error('subscription_expires_at') is-invalid @enderror"
                                   value="{{ old('subscription_expires_at') }}">
                            @error('subscription_expires_at')
                                <span class="invalid-feedback">{{ $message }}</span>
                            @enderror
                        </div>
                    </div>

                    <div class="col-md-6">
                        <h5 class="border-bottom pb-2">Limits</h5>

                        <div class="form-group mb-3">
                            <label for="max_students">Max Students <span class="text-danger">*</span></label>
                            <input type="number" name="max_students" id="max_students" 
                                   class="form-control @error('max_students') is-invalid @enderror"
                                   value="{{ old('max_students', 1000) }}" min="0" required>
                            @error('max_students')
                                <span class="invalid-feedback">{{ $message }}</span>
                            @enderror
                        </div>

                        <div class="form-group mb-3">
                            <label for="max_instructors">Max Instructors <span class="text-danger">*</span></label>
                            <input type="number" name="max_instructors" id="max_instructors" 
                                   class="form-control @error('max_instructors') is-invalid @enderror"
                                   value="{{ old('max_instructors', 100) }}" min="0" required>
                            @error('max_instructors')
                                <span class="invalid-feedback">{{ $message }}</span>
                            @enderror
                        </div>

                        <div class="form-group mb-3">
                            <label for="max_exams">Max Exams <span class="text-danger">*</span></label>
                            <input type="number" name="max_exams" id="max_exams" 
                                   class="form-control @error('max_exams') is-invalid @enderror"
                                   value="{{ old('max_exams', 500) }}" min="0" required>
                            @error('max_exams')
                                <span class="invalid-feedback">{{ $message }}</span>
                            @enderror
                        </div>

                        <div class="form-group mb-3">
                            <label for="storage_limit_gb">Storage Limit (GB) <span class="text-danger">*</span></label>
                            <input type="number" name="storage_limit_gb" id="storage_limit_gb" 
                                   class="form-control @error('storage_limit_gb') is-invalid @enderror"
                                   value="{{ old('storage_limit_gb', 10) }}" min="0" required>
                            @error('storage_limit_gb')
                                <span class="invalid-feedback">{{ $message }}</span>
                            @enderror
                        </div>
                    </div>
                </div>

                <hr>

                <!-- Files -->
                <div class="row">
                    <div class="col-md-6">
                        <h5 class="border-bottom pb-2">Logo</h5>
                        <div class="form-group mb-3">
                            <input type="file" name="logo" id="logo" 
                                   class="form-control @error('logo') is-invalid @enderror"
                                   accept="image/*">
                            @error('logo')
                                <span class="invalid-feedback">{{ $message }}</span>
                            @enderror
                            <small class="text-muted">Recommended: 200x200 pixels, PNG or JPG</small>
                        </div>
                    </div>

                    <div class="col-md-6">
                        <h5 class="border-bottom pb-2">Favicon</h5>
                        <div class="form-group mb-3">
                            <input type="file" name="favicon" id="favicon" 
                                   class="form-control @error('favicon') is-invalid @enderror"
                                   accept="image/*">
                            @error('favicon')
                                <span class="invalid-feedback">{{ $message }}</span>
                            @enderror
                            <small class="text-muted">Recommended: 32x32 pixels, ICO or PNG</small>
                        </div>
                    </div>
                </div>

                <hr>

                <div class="form-group text-center">
                    <button type="submit" class="btn btn-primary btn-lg">
                        <i class="fas fa-save"></i> Create Airport
                    </button>
                    <a href="{{ route('admin.airports.index') }}" class="btn btn-secondary btn-lg">
                        <i class="fas fa-times"></i> Cancel
                    </a>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
// Auto-generate slug from name
document.getElementById('name').addEventListener('input', function() {
    const slug = this.value
        .toLowerCase()
        .replace(/[^a-z0-9]+/g, '-')
        .replace(/^-+|-+$/g, '');
    document.getElementById('slug').value = slug;
});

// Auto-uppercase code
document.getElementById('code').addEventListener('input', function() {
    this.value = this.value.toUpperCase();
});

document.getElementById('icao_code').addEventListener('input', function() {
    this.value = this.value.toUpperCase();
});
</script>
@endpush