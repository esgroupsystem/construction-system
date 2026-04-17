@extends('layouts.app')

@section('title', 'Face Registration')

@section('content')
    <div class="container-fluid">

        <div class="row g-4">

            {{-- LEFT: CAMERA --}}
            <div class="col-xl-7 d-flex">

                <div class="card border-0 shadow-sm w-100 d-flex flex-column">

                    {{-- HEADER --}}
                    <div class="card-header bg-white border-0 d-flex justify-content-between align-items-center">
                        <div>
                            <h5 class="mb-1 fw-semibold">Face Registration</h5>
                            <small class="text-muted">
                                {{ $employee->full_name }}
                                @if ($employee->employee_no)
                                    • {{ $employee->employee_no }}
                                @endif
                            </small>
                        </div>

                        <span class="badge bg-success-subtle text-success px-3 py-2" id="cameraStatus">
                            Ready
                        </span>
                    </div>

                    {{-- BODY --}}
                    <div class="card-body d-flex flex-column align-items-center">

                        {{-- CAMERA CENTERED --}}
                        <div class="camera-container">

                            <div class="camera-wrapper">

                                <video id="video" autoplay playsinline></video>
                                <canvas id="overlay"></canvas>

                                <div class="camera-mask"></div>
                                <div id="faceGuide"></div>
                                <div id="countdown"></div>
                                <div id="flash"></div>

                            </div>

                        </div>

                        {{-- STATUS --}}
                        <div class="text-center mt-3 w-100">
                            <div class="alert alert-light border mb-2 py-2" id="statusBox">
                                Align your face inside the oval
                            </div>

                            {{-- BUTTONS --}}
                            <div class="d-flex justify-content-center gap-2 flex-wrap">

                                <button class="btn btn-primary px-4" id="startBtn">
                                    <i class="fas fa-video me-1"></i> Start
                                </button>

                                <button class="btn btn-success px-4" id="saveBtn" disabled>
                                    Save
                                </button>

                                <button class="btn btn-outline-secondary px-4" id="resetBtn">
                                    Reset
                                </button>

                            </div>

                            {{-- COUNTER --}}
                            <div class="mt-2 text-success small fw-semibold">
                                Captured: <span id="captureCount">0</span>/3
                            </div>
                        </div>

                    </div>
                </div>
            </div>

            {{-- RIGHT: REGISTERED --}}
            <div class="col-xl-5 d-flex">
                <div class="card border-0 shadow-sm w-100">

                    <div class="card-header bg-white border-0">
                        <h5 class="mb-0">Registered Samples</h5>
                    </div>

                    <div class="card-body">

                        <div class="row g-3">

                            @forelse($employee->faceSamples as $sample)
                                <div class="col-md-6">

                                    <div class="card border-0 shadow-sm h-100">

                                        <img src="{{ asset('storage/' . $sample->image_path) }}"
                                            class="card-img-top rounded-top" style="height: 180px; object-fit: cover;">

                                        <div class="card-body">

                                            {{-- STATUS --}}
                                            <div class="mb-2">
                                                @if ($sample->is_primary)
                                                    <span class="badge bg-primary-subtle text-primary">
                                                        Primary
                                                    </span>
                                                @endif
                                            </div>

                                            {{-- DATE --}}
                                            <div class="small text-muted mb-3">
                                                {{ optional($sample->captured_at)->format('M d, Y h:i A') }}
                                            </div>

                                            {{-- ACTION --}}
                                            <div class="d-flex gap-2">

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
                                    <div class="alert alert-light border text-center mb-0">
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

        /* ----------------------------------------
         | DELETE SAMPLE
        -----------------------------------------*/
        document.addEventListener("click", async (e) => {
            if (e.target.closest(".delete-sample-btn")) {
                const btn = e.target.closest(".delete-sample-btn");
                const url = btn.dataset.url;

                if (!confirm("Delete this sample?")) return;

                try {
                    const res = await fetch(url, {
                        method: "DELETE",
                        headers: {
                            "X-CSRF-TOKEN": window.faceRegisterConfig.csrfToken,
                            "Accept": "application/json"
                        }
                    });

                    const data = await res.json();

                    if (!res.ok) throw new Error(data.message);

                    location.reload();

                } catch (err) {
                    alert(err.message || "Delete failed");
                }
            }
        });


        /* ----------------------------------------
         | SET PRIMARY
        -----------------------------------------*/
        document.addEventListener("click", async (e) => {
            if (e.target.closest(".set-primary-btn")) {
                const btn = e.target.closest(".set-primary-btn");
                const url = btn.dataset.url;

                try {
                    const res = await fetch(url, {
                        method: "PUT",
                        headers: {
                            "X-CSRF-TOKEN": window.faceRegisterConfig.csrfToken,
                            "Accept": "application/json"
                        }
                    });

                    const data = await res.json();

                    if (!res.ok) throw new Error(data.message);

                    location.reload();

                } catch (err) {
                    alert(err.message || "Update failed");
                }
            }
        });
    </script>

    <style>
        /* CAMERA CONTAINER (CENTER + SIZE CONTROL) */
        .camera-container {
            width: 100%;
            max-width: 380px;
            /* slightly smaller = better UI */
            margin: auto;
        }

        /* CAMERA WRAPPER (MAIN FIX) */
        .camera-wrapper {
            position: relative;
            width: 100%;
            aspect-ratio: 3 / 4;
            /* 🔥 KEY FIX: prevents stretching */
            border-radius: 20px;
            overflow: hidden;
            background: black;
        }

        /* VIDEO + CANVAS */
        .camera-wrapper video,
        .camera-wrapper canvas {
            position: absolute;
            inset: 0;
            width: 100%;
            height: 100%;
            object-fit: contain;
            object-fit: scale-down;
        }

        /* DARK MASK (FOCUS EFFECT) */
        .camera-mask {
            position: absolute;
            inset: 0;
            background: radial-gradient(circle at center,
                    transparent 120px,
                    rgba(0, 0, 0, 0.75) 260px);
        }

        /* FACE OVAL */
        #faceGuide {
            position: absolute;
            top: 50%;
            left: 50%;
            width: 220px;
            height: 300px;
            transform: translate(-50%, -50%);
            border-radius: 50% / 60%;
            border: 3px solid rgba(255, 255, 255, 0.5);
            transition: all 0.3s ease;
        }

        /* ACTIVE (GREEN) */
        #faceGuide.active {
            border-color: #00ff88;
            box-shadow: 0 0 25px #00ff88;
        }

        /* COUNTDOWN */
        #countdown {
            position: absolute;
            top: 50%;
            left: 50%;
            transform: translate(-50%, -50%);
            font-size: 80px;
            font-weight: bold;
            color: white;
            display: none;
            text-shadow: 0 0 20px rgba(0, 0, 0, 0.8);
            animation: pop 0.4s ease;
        }

        @keyframes pop {
            0% {
                transform: scale(0.5) translate(-50%, -50%);
            }

            100% {
                transform: scale(1) translate(-50%, -50%);
            }
        }

        /* FLASH EFFECT */
        #flash {
            position: absolute;
            inset: 0;
            background: white;
            opacity: 0;
            pointer-events: none;
        }

        #flash.active {
            animation: flash 0.3s ease;
        }

        @keyframes flash {
            0% {
                opacity: 1;
            }

            100% {
                opacity: 0;
            }
        }
    </style>
@endsection
