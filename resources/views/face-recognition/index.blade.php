@extends('layouts.app')

@section('title', 'Face Recognition')

@section('content')
    <div class="container-fluid">

        {{-- HEADER --}}
        <div class="card border-0 shadow-sm mb-4">
            <div class="card-body d-flex justify-content-between align-items-center">
                <div>
                    <h3 class="mb-1">Face Recognition</h3>
                    <p class="text-muted mb-0">
                        Identify employee using facial recognition system.
                    </p>
                </div>

                <span class="badge bg-info-subtle text-info px-3 py-2" id="cameraStatus">
                    Idle
                </span>
            </div>
        </div>

        <div class="row g-4">

            {{-- LEFT: CAMERA --}}
            <div class="col-xl-7">

                <div class="card border-0 shadow-sm h-100">

                    <div class="card-header bg-white border-0">
                        <h5 class="mb-0">Camera Feed</h5>
                    </div>

                    <div class="card-body">

                        <div class="position-relative rounded overflow-hidden bg-dark border">
                            <video id="video" autoplay playsinline style="width:100%; height:420px; object-fit:cover;">
                            </video>
                        </div>

                        {{-- CONTROLS --}}
                        <div class="mt-3 d-flex flex-wrap gap-2">

                            <button type="button" class="btn btn-primary" id="startCamera">
                                <i class="fas fa-video me-1"></i> Start Camera
                            </button>

                            <button type="button" class="btn btn-success" id="identifyFace">
                                <i class="fas fa-user-check me-1"></i> Identify
                            </button>

                        </div>

                        <div class="small text-muted mt-2">
                            Make sure your face is clearly visible and centered.
                        </div>

                    </div>
                </div>

            </div>

            {{-- RIGHT: RESULT --}}
            <div class="col-xl-5">

                <div class="card border-0 shadow-sm h-100">

                    <div class="card-header bg-white border-0">
                        <h5 class="mb-0">Recognition Result</h5>
                    </div>

                    <div class="card-body">

                        <div id="resultBox" class="text-center py-5 text-muted">
                            <i class="fas fa-user-circle fa-2x mb-2"></i>
                            <p class="mb-0">No result yet</p>
                        </div>

                    </div>
                </div>

            </div>

        </div>

    </div>

    <script type="module">
        import {
            FaceLandmarker,
            FilesetResolver
        }
        from "https://cdn.jsdelivr.net/npm/@mediapipe/tasks-vision@latest";

        const video = document.getElementById('video');
        const identifyBtn = document.getElementById('identifyFace');
        const startBtn = document.getElementById('startCamera');
        const resultBox = document.getElementById('resultBox');
        const cameraStatus = document.getElementById('cameraStatus');

        let faceLandmarker;

        /* INIT */
        async function init() {
            const vision = await FilesetResolver.forVisionTasks(
                "https://cdn.jsdelivr.net/npm/@mediapipe/tasks-vision@latest/wasm"
            );

            faceLandmarker = await FaceLandmarker.createFromOptions(vision, {
                baseOptions: {
                    modelAssetPath: "https://storage.googleapis.com/mediapipe-models/face_landmarker/face_landmarker/float16/latest/face_landmarker.task",
                },
                runningMode: "VIDEO",
                numFaces: 1,
            });
        }

        /* START CAMERA */
        startBtn.addEventListener('click', async () => {
            const stream = await navigator.mediaDevices.getUserMedia({
                video: true
            });
            video.srcObject = stream;
            await video.play();

            await init();

            cameraStatus.textContent = "Ready";
            resultBox.innerHTML = `
        <div class="alert alert-soft-info border-0">
            Camera ready. Click identify to scan.
        </div>
    `;
        });

        /* IDENTIFY */
        identifyBtn.addEventListener('click', async () => {

            if (!faceLandmarker) {
                alert("Start camera first");
                return;
            }

            const result = faceLandmarker.detectForVideo(video, performance.now());

            if (!result.faceLandmarks?.length) {
                resultBox.innerHTML = `
            <div class="alert alert-soft-warning border-0">
                No face detected
            </div>
        `;
                return;
            }

            const landmarks = result.faceLandmarks[0];
            const embedding = landmarks.flatMap(p => [p.x, p.y, p.z]);

            resultBox.innerHTML = `
        <div class="alert alert-soft-primary border-0">
            Recognizing...
        </div>
    `;

            const response = await fetch("{{ route('face-recognition.identify') }}", {
                method: "POST",
                headers: {
                    "Content-Type": "application/json",
                    "X-CSRF-TOKEN": "{{ csrf_token() }}"
                },
                body: JSON.stringify({
                    embedding
                })
            });

            const data = await response.json();

            if (data.success && data.matched) {

                resultBox.innerHTML = `
            <div class="text-center">
                <img src="${data.employee.photo_url ?? '/assets/img/team/avatar.png'}"
                     class="rounded-circle mb-3 border"
                     width="90" height="90"
                     style="object-fit: cover;">

                <h5 class="mb-1">${data.employee.full_name}</h5>
                <div class="text-muted small mb-2">${data.employee.employee_no}</div>

                <span class="badge bg-success-subtle text-success px-3 py-2">
                    Confidence: ${data.confidence}
                </span>
            </div>
        `;

            } else {

                resultBox.innerHTML = `
            <div class="alert alert-soft-danger border-0">
                No match found
            </div>
        `;
            }
        });
    </script>
@endsection
