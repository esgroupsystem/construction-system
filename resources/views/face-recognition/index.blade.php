@extends('layouts.app')

@section('title', 'Face Recognition')

@section('content')
    <div class="container-fluid">
        <div class="card shadow-sm border-0">
            <div class="card-header">
                <h4 class="mb-0">Face Recognition</h4>
            </div>
            <div class="card-body">
                <video id="video" autoplay playsinline class="w-100 rounded border" style="max-height: 420px;"></video>
                <canvas id="canvas" class="d-none"></canvas>

                <div class="mt-3 d-flex gap-2">
                    <button type="button" class="btn btn-secondary" id="startCamera">Start Camera</button>
                    <button type="button" class="btn btn-primary" id="identifyFace">Identify Face</button>
                </div>

                <div id="resultBox" class="mt-4"></div>
            </div>
        </div>
    </div>

    <script type="module">
        import {
            FaceLandmarker,
            FilesetResolver
        } from "https://cdn.jsdelivr.net/npm/@mediapipe/tasks-vision@latest";

        const video = document.getElementById('video');
        const identifyBtn = document.getElementById('identifyFace');
        const startBtn = document.getElementById('startCamera');
        const resultBox = document.getElementById('resultBox');

        let faceLandmarker;

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

        startBtn.addEventListener('click', async () => {
            const stream = await navigator.mediaDevices.getUserMedia({
                video: true
            });
            video.srcObject = stream;
            await video.play();

            await init();

            resultBox.innerHTML = "Camera ready";
        });

        identifyBtn.addEventListener('click', async () => {

            if (!faceLandmarker) {
                alert("Start camera first");
                return;
            }

            const result = faceLandmarker.detectForVideo(video, performance.now());

            if (!result.faceLandmarks?.length) {
                resultBox.innerHTML = "No face detected";
                return;
            }

            const landmarks = result.faceLandmarks[0];

            const embedding = landmarks.flatMap(p => [p.x, p.y, p.z]);

            resultBox.innerHTML = "Recognizing...";

            const response = await fetch("{{ route('face-recognition.identify') }}", {
                method: "POST",
                headers: {
                    "Content-Type": "application/json",
                    "X-CSRF-TOKEN": "{{ csrf_token() }}"
                },
                body: JSON.stringify({
                    embedding: embedding
                })
            });

            const data = await response.json();

            if (data.success && data.matched) {
                resultBox.innerHTML = `
            <div class="alert alert-success">
                <b>${data.employee.full_name}</b><br>
                ${data.employee.employee_no}<br>
                Confidence: ${data.confidence}
            </div>
        `;
            } else {
                resultBox.innerHTML = `
            <div class="alert alert-warning">
                No match found
            </div>
        `;
            }
        });
    </script>
@endsection
