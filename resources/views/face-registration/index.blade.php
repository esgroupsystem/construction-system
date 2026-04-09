@extends('layouts.app')

@section('title', 'Face Registration')

@section('content')
    <div class="container-fluid">
        <div class="row g-4">
            <div class="col-xl-7">
                <div class="card shadow-sm">
                    <div class="card-header d-flex justify-content-between align-items-center">
                        <div>
                            <h4 class="mb-1">Automatic Face Registration</h4>
                            <p class="text-muted mb-0">
                                Employee: <strong>{{ $employee->full_name }}</strong>
                                @if ($employee->employee_no)
                                    • {{ $employee->employee_no }}
                                @endif
                            </p>
                        </div>
                        <span class="badge bg-info text-dark" id="cameraStatus">Initializing...</span>
                    </div>

                    <div class="card-body">
                        <div class="position-relative rounded overflow-hidden bg-dark">
                            <video id="video" class="w-100" autoplay playsinline muted
                                style="max-height: 520px; object-fit: cover;"></video>
                            <canvas id="overlay" class="position-absolute top-0 start-0 w-100 h-100"></canvas>
                        </div>

                        <div class="mt-3">
                            <div class="alert alert-primary mb-2" id="statusBox">
                                Position your face in front of the camera.
                            </div>

                            <div class="d-flex flex-wrap gap-2 align-items-center">
                                <button type="button" class="btn btn-primary" id="startBtn">Start Camera</button>
                                <button type="button" class="btn btn-success" id="saveBtn" disabled>Save Samples</button>
                                <button type="button" class="btn btn-outline-secondary" id="resetBtn">Reset</button>
                                <span class="badge bg-success fs-6">Captured: <span id="captureCount">0</span>/3</span>
                            </div>

                            <div class="small text-muted mt-2">
                                Auto-capture only happens when the face is centered, large enough, and stable.
                            </div>
                        </div>

                        <div class="mt-4">
                            <h6 class="mb-3">Captured Samples</h6>
                            <div class="row g-3" id="previewGrid"></div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-xl-5">
                <div class="card shadow-sm">
                    <div class="card-header">
                        <h5 class="mb-0">Registered Face Samples</h5>
                    </div>
                    <div class="card-body">
                        <div class="row g-3">
                            @forelse($employee->faceSamples as $sample)
                                <div class="col-md-6">
                                    <div class="card border">
                                        <img src="{{ asset('storage/' . $sample->image_path) }}" class="card-img-top"
                                            style="height: 220px; object-fit: cover;">
                                        <div class="card-body">
                                            <div class="mb-2">
                                                @if ($sample->is_primary)
                                                    <span class="badge bg-primary">Primary</span>
                                                @endif
                                            </div>

                                            <div class="small text-muted mb-3">
                                                {{ optional($sample->captured_at)->format('M d, Y h:i A') }}
                                            </div>

                                            <div class="d-flex gap-2">
                                                @if (!$sample->is_primary)
                                                    <button class="btn btn-sm btn-outline-primary set-primary-btn"
                                                        data-url="{{ route('face-registration.update', [$employee, $sample]) }}">
                                                        Set Primary
                                                    </button>
                                                @endif

                                                <button class="btn btn-sm btn-outline-danger delete-sample-btn"
                                                    data-url="{{ route('face-registration.destroy', [$employee, $sample]) }}">
                                                    Delete
                                                </button>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            @empty
                                <div class="col-12">
                                    <div class="alert alert-light border mb-0">
                                        No registered face samples yet.
                                    </div>
                                </div>
                            @endforelse
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    @vite('resources/js/face_register.js')

    <script>
        window.faceRegisterConfig = {
            postUrl: @json(route('face-registration.store', ['employee' => $employee->id])),
            csrfToken: @json(csrf_token()),
            employeeId: @json($employee->id),
            requiredSamples: 3,
        };
    </script>
@endsection
