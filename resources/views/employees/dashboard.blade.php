@extends('layouts.app')

@section('title', 'Employee Attendance')

@section('content')
    <div class="container-fluid">
        <div class="card border-0 shadow-sm mb-4 attendance-hero">
            <div class="card-body d-flex flex-column flex-lg-row justify-content-between align-items-lg-center gap-3">
                <div>
                    <div class="text-uppercase small text-muted fw-semibold mb-1">Attendance Verification</div>
                    <h3 class="mb-1 fw-bold">Employee Attendance</h3>
                    <div class="text-muted">
                        Welcome, {{ $employee->full_name }} • {{ $employee->position ?: 'Employee' }}
                    </div>
                </div>

                <div class="d-flex align-items-center gap-2 flex-wrap">
                    <span class="badge rounded-pill bg-secondary px-3 py-2" id="cameraStatus">Idle</span>
                    <span class="badge rounded-pill bg-light text-dark border px-3 py-2" id="faceDetectedBadge">
                        No face detected
                    </span>
                </div>
            </div>
        </div>

        <div class="row g-4">
            <div class="col-xl-8 d-flex">
                <div class="card border-0 shadow-sm w-100">
                    <div class="card-header bg-white border-0 pb-0">
                        <h5 class="mb-1 fw-semibold">Face Verification Camera</h5>
                        <p class="text-muted small mb-0">
                            Only the registered face of the logged-in employee can record attendance.
                        </p>
                    </div>

                    <div class="card-body">
                        <div class="attendance-camera-shell mx-auto">
                            <div class="attendance-camera-wrapper">
                                <video id="video" autoplay playsinline muted></video>
                                <canvas id="overlay"></canvas>
                                <div class="camera-mask"></div>
                                <div id="faceGuide"></div>

                                <div id="countdown" class="countdown-box"></div>
                            </div>
                        </div>

                        <div class="row g-3 mt-1">
                            <div class="col-md-8">
                                <div class="alert alert-light border mb-0 status-panel" id="guideBox">
                                    Start camera first, then align your face inside the guide.
                                </div>
                            </div>

                            <div class="col-md-4">
                                <div class="guide-box h-100">
                                    <div class="small text-muted fw-semibold mb-2">Verification Rules</div>
                                    <ul class="small mb-0 ps-3 guide-list">
                                        <li>One face only</li>
                                        <li>Face centered in the guide</li>
                                        <li>Good lighting required</li>
                                        <li>Head movement required for liveness</li>
                                    </ul>
                                </div>
                            </div>
                        </div>

                        <div class="d-flex flex-wrap gap-2 mt-4">
                            <button type="button" class="btn btn-primary px-4" id="startCamera">
                                <i class="fas fa-video me-2"></i>Start Camera
                            </button>

                            <button type="button" class="btn btn-success px-4" id="timeInBtn" disabled>
                                <i class="fas fa-right-to-bracket me-2"></i>Time In
                            </button>

                            <button type="button" class="btn btn-danger px-4" id="timeOutBtn" disabled>
                                <i class="fas fa-right-from-bracket me-2"></i>Time Out
                            </button>
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-xl-4">
                <div class="card border-0 shadow-sm mb-4">
                    <div class="card-header bg-white border-0">
                        <h5 class="mb-0 fw-semibold">Employee Information</h5>
                    </div>

                    <div class="card-body text-center">
                        <img
                            src="{{ $employee->photo_path ? asset('storage/' . $employee->photo_path) : asset('assets/img/team/avatar.png') }}"
                            class="rounded-circle border mb-3"
                            width="100"
                            height="100"
                            style="object-fit: cover;"
                        >

                        <h5 class="mb-1">{{ $employee->full_name }}</h5>
                        <div class="text-muted small mb-1">{{ $employee->employee_no }}</div>
                        <div class="text-muted small mb-1">{{ $employee->department ?: '-' }}</div>
                        <div class="text-muted small">{{ $employee->position ?: '-' }}</div>
                    </div>
                </div>

                <div class="card border-0 shadow-sm mb-4">
                    <div class="card-header bg-white border-0">
                        <h5 class="mb-0 fw-semibold">Today's Attendance</h5>
                    </div>

                    <div class="card-body" id="attendanceBox">
                        @if ($todayAttendance)
                            <div class="mb-3">
                                <label class="form-label text-muted small">Date</label>
                                <div class="fw-semibold">{{ $todayAttendance->attendance_date->format('F d, Y') }}</div>
                            </div>

                            <div class="mb-3">
                                <label class="form-label text-muted small">Time In</label>
                                <div class="fw-semibold">
                                    {{ $todayAttendance->time_in ? $todayAttendance->time_in->format('h:i:s A') : 'Not yet' }}
                                </div>
                            </div>

                            <div class="mb-0">
                                <label class="form-label text-muted small">Time Out</label>
                                <div class="fw-semibold">
                                    {{ $todayAttendance->time_out ? $todayAttendance->time_out->format('h:i:s A') : 'Not yet' }}
                                </div>
                            </div>
                        @else
                            <div class="text-muted text-center py-4">
                                No attendance record yet for today.
                            </div>
                        @endif
                    </div>
                </div>

                <div class="card border-0 shadow-sm mb-4">
                    <div class="card-header bg-white border-0">
                        <h5 class="mb-0 fw-semibold">Verification Result</h5>
                    </div>

                    <div class="card-body">
                        <div id="resultBox" class="text-center py-4 text-muted">
                            <i class="fas fa-user-circle fa-2x mb-2"></i>
                            <p class="mb-0">No verification yet</p>
                        </div>
                    </div>
                </div>

                <div class="card border-0 shadow-sm">
                    <div class="card-header bg-white border-0">
                        <h5 class="mb-0 fw-semibold">Live Status</h5>
                    </div>

                    <div class="card-body small">
                        <div class="mb-2">Frames Collected: <strong id="framesCollected">0</strong></div>
                        <div class="mb-2">Stability: <strong id="stabilityValue">0</strong></div>
                        <div class="mb-2">Quality Score: <strong id="qualityValue">0.00</strong></div>
                        <div class="mb-0">Liveness: <strong id="livenessValue">Waiting</strong></div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script>
        window.attendanceConfig = {
            csrfToken: @json(csrf_token()),
            timeInUrl: @json(route('attendance.time-in')),
            timeOutUrl: @json(route('attendance.time-out')),
        };
    </script>

    @vite('resources/js/attendance_verify.js')

    <style>
        .attendance-hero { border-radius: 20px; }
        .attendance-camera-shell { width: 100%; max-width: 560px; }
        .attendance-camera-wrapper {
            position: relative;
            width: 100%;
            aspect-ratio: 4 / 5;
            border-radius: 24px;
            overflow: hidden;
            background: #0f172a;
        }
        .attendance-camera-wrapper video,
        .attendance-camera-wrapper canvas {
            position: absolute;
            inset: 0;
            width: 100%;
            height: 100%;
            object-fit: cover;
        }
        .camera-mask {
            position: absolute;
            inset: 0;
            background: radial-gradient(circle at center, transparent 115px, rgba(15,23,42,.45) 170px, rgba(15,23,42,.78) 270px);
        }
        #faceGuide {
            position: absolute;
            top: 50%;
            left: 50%;
            width: 220px;
            height: 295px;
            transform: translate(-50%, -50%);
            border-radius: 50% / 58%;
            border: 3px solid rgba(255,255,255,.55);
            transition: all .2s ease;
            z-index: 3;
        }
        #faceGuide.active {
            border-color: #22c55e;
            box-shadow: 0 0 0 3px rgba(34,197,94,.15), 0 0 24px rgba(34,197,94,.45);
        }
        #faceGuide.warning {
            border-color: #f59e0b;
            box-shadow: 0 0 0 3px rgba(245,158,11,.12), 0 0 24px rgba(245,158,11,.35);
        }
        .countdown-box {
            display: none;
            position: absolute;
            inset: 0;
            align-items: center;
            justify-content: center;
            font-size: 72px;
            font-weight: 700;
            color: #fff;
            background: rgba(0,0,0,.18);
            z-index: 5;
        }
        .status-panel, .guide-box { border-radius: 16px; }
        .guide-box {
            border: 1px solid var(--bs-border-color);
            background: var(--bs-light-bg-subtle, #f8fafc);
            padding: 1rem;
        }
        .guide-list li + li { margin-top: .35rem; }
    </style>
@endsection
