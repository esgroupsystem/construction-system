@extends('layouts.app')

@section('title', 'Face Registration')

@section('content')
    <div class="container-fluid">
        <div class="card border-0 shadow-sm mb-4 registration-hero">
            <div class="card-body d-flex flex-column flex-lg-row justify-content-between align-items-lg-center gap-3">
                <div>
                    <div class="text-uppercase small text-muted fw-semibold mb-1">Facial Registration</div>
                    <h3 class="mb-1 fw-bold">{{ $employee->full_name }}</h3>
                    <div class="text-muted">
                        {{ $employee->employee_no ?: 'No Employee No.' }}
                        @if ($employee->department)
                            • {{ $employee->department }}
                        @endif
                        @if ($employee->position)
                            • {{ $employee->position }}
                        @endif
                    </div>
                </div>

                <div class="d-flex flex-wrap gap-2 align-items-center">
                    <span class="badge rounded-pill bg-secondary px-3 py-2" id="cameraStatus">Idle</span>
                    <span class="badge rounded-pill bg-light text-dark border px-3 py-2">
                        Samples: <span id="captureCount">0</span>/10
                    </span>
                </div>
            </div>
        </div>

        <div class="row g-4 align-items-stretch">
            <div class="col-xl-8 d-flex">
                <div class="card border-0 shadow-sm w-100">
                    <div class="card-header bg-white border-0 pb-0">
                        <h5 class="mb-1 fw-semibold">Camera Preview</h5>
                        <p class="text-muted small mb-0">
                            Center the employee’s face inside the guide. Capture is enabled only when a face is detected.
                        </p>
                    </div>

                    <div class="card-body">
                        <div class="camera-shell mx-auto">
                            <div class="camera-wrapper">
                                <video id="video" autoplay playsinline muted></video>
                                <canvas id="overlay"></canvas>
                                <div class="camera-mask"></div>
                                <div id="faceGuide"></div>

                                <div class="camera-top-hint">
                                    <span class="badge rounded-pill bg-dark-subtle text-dark border" id="faceDetectedBadge">
                                        No face detected
                                    </span>
                                </div>
                            </div>
                        </div>

                        <div class="row g-3 mt-1">
                            <div class="col-md-8">
                                <div class="alert alert-light border mb-0 status-panel" id="statusBox">
                                    Start camera to begin registration.
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="guide-box h-100">
                                    <div class="small text-muted mb-2 fw-semibold">Capture Tips</div>
                                    <ul class="small mb-0 ps-3 guide-list">
                                        <li>Only one face in frame</li>
                                        <li>Face centered inside the oval</li>
                                        <li>Good lighting, no heavy shadow</li>
                                        <li>Keep still during auto-capture</li>
                                    </ul>
                                </div>
                            </div>
                        </div>

                        <div class="d-flex justify-content-center gap-2 flex-wrap mt-4">
                            <button class="btn btn-primary px-4" id="startBtn">
                                <i class="fas fa-camera me-2"></i>Start Camera
                            </button>

                            <button class="btn btn-success px-4" id="captureBtn" disabled>
                                <i class="fas fa-circle-check me-2"></i>Capture Samples
                            </button>

                            <button class="btn btn-outline-secondary px-4" id="resetBtn">
                                <i class="fas fa-rotate-left me-2"></i>Reset
                            </button>
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-xl-4 d-flex">
                <div class="card border-0 shadow-sm w-100">
                    <div class="card-header bg-white border-0">
                        <h5 class="mb-0 fw-semibold">Registered Samples</h5>
                    </div>

                    <div class="card-body">
                        <div class="row g-3">
                            @forelse($employee->faceEmbeddings as $sample)
                                <div class="col-sm-6 col-xl-12">
                                    <div class="sample-card border rounded-4 overflow-hidden h-100">
                                        <img src="{{ asset('storage/' . $sample->image_path) }}" class="w-100 sample-image"
                                            alt="Face Sample">

                                        <div class="p-3">
                                            <div class="d-flex justify-content-between align-items-center mb-2">
                                                <div class="fw-semibold">Sample</div>
                                                @if ($sample->is_primary)
                                                    <span class="badge bg-primary">Primary</span>
                                                @endif
                                            </div>

                                            <div class="sample-meta small text-muted">
                                                <div>Quality: {{ $sample->quality_score ?? '-' }}</div>
                                                <div>Detect: {{ $sample->det_score ?? '-' }}</div>
                                                <div>{{ optional($sample->captured_at)->format('M d, Y h:i A') }}</div>
                                            </div>

                                            <div class="d-flex gap-2 mt-3">
                                                @if (!$sample->is_primary)
                                                    <button class="btn btn-sm btn-outline-primary w-100 set-primary-btn"
                                                        data-url="{{ route('face-registration.update', [$employee, $sample]) }}">
                                                        Set Primary
                                                    </button>
                                                @endif

                                                <button class="btn btn-sm btn-outline-danger w-100 delete-sample-btn"
                                                    data-url="{{ route('face-registration.destroy', [$employee, $sample]) }}">
                                                    Delete
                                                </button>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            @empty
                                <div class="col-12">
                                    <div class="empty-samples text-center p-4 rounded-4 border bg-light-subtle">
                                        <i class="fas fa-face-smile text-muted fs-3 mb-2"></i>
                                        <div class="fw-semibold">No registered samples yet</div>
                                        <div class="text-muted small">Capture will appear here after successful
                                            registration.</div>
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
            employeeId: @json($employee->id),
            postUrl: @json(route('face-registration.store', $employee)),
            csrfToken: @json(csrf_token()),
            requiredSamples: 10,
        };
    </script>

    <style>
        .registration-hero {
            border-radius: 20px;
        }

        .camera-shell {
            width: 100%;
            max-width: 560px;
        }

        .camera-wrapper {
            position: relative;
            width: 100%;
            aspect-ratio: 4 / 5;
            border-radius: 24px;
            overflow: hidden;
            background: #0f172a;
            box-shadow: inset 0 0 0 1px rgba(255, 255, 255, .05);
        }

        .camera-wrapper video,
        .camera-wrapper canvas {
            position: absolute;
            inset: 0;
            width: 100%;
            height: 100%;
            object-fit: cover;
        }

        .camera-mask {
            position: absolute;
            inset: 0;
            background:
                radial-gradient(circle at center, transparent 115px, rgba(15, 23, 42, .45) 170px, rgba(15, 23, 42, .78) 270px);
            pointer-events: none;
        }

        #faceGuide {
            position: absolute;
            top: 50%;
            left: 50%;
            width: 220px;
            height: 295px;
            transform: translate(-50%, -50%);
            border-radius: 50% / 58%;
            border: 3px solid rgba(255, 255, 255, .55);
            box-shadow: 0 0 0 9999px rgba(0, 0, 0, 0);
            transition: all .2s ease;
            z-index: 3;
        }

        #faceGuide.active {
            border-color: #22c55e;
            box-shadow: 0 0 0 3px rgba(34, 197, 94, .15), 0 0 24px rgba(34, 197, 94, .45);
        }

        #faceGuide.warning {
            border-color: #f59e0b;
            box-shadow: 0 0 0 3px rgba(245, 158, 11, .12), 0 0 24px rgba(245, 158, 11, .35);
        }

        .camera-top-hint {
            position: absolute;
            top: 14px;
            left: 14px;
            z-index: 4;
        }

        .status-panel,
        .guide-box,
        .sample-card,
        .empty-samples {
            border-radius: 16px;
        }

        .guide-box {
            border: 1px solid var(--bs-border-color);
            background: var(--bs-light-bg-subtle, #f8fafc);
            padding: 1rem;
        }

        .guide-list li+li {
            margin-top: .35rem;
        }

        .sample-image {
            height: 180px;
            object-fit: cover;
            display: block;
        }

        .sample-meta>div+div {
            margin-top: .2rem;
        }

        @media (max-width: 768px) {
            .camera-shell {
                max-width: 100%;
            }

            #faceGuide {
                width: 190px;
                height: 255px;
            }
        }
    </style>
@endsection
