@extends('layouts.app')

@section('title', 'Register Face')

@section('content')
    <div class="container-fluid">
        <div class="row g-3">
            <div class="col-lg-7">
                <div class="card shadow-sm border-0">
                    <div class="card-header">
                        <h4 class="mb-0">Register Face - {{ $employee->full_name }}</h4>
                    </div>
                    <div class="card-body">
                        <video id="video" autoplay playsinline style="width:100%; height:420px; object-fit:cover;">
                        </video>
                        <canvas id="canvas" class="d-none"></canvas>

                        <div class="mt-3 d-flex gap-2 flex-wrap">
                            <button type="button" class="btn btn-secondary" id="startCamera">Start Camera</button>
                            <button type="button" class="btn btn-primary" id="captureFace">Capture & Save</button>
                        </div>

                        <div class="form-check mt-3">
                            <input class="form-check-input" type="checkbox" id="is_primary" checked>
                            <label class="form-check-label" for="is_primary">
                                Set as primary face sample
                            </label>
                        </div>

                        <div id="resultBox" class="mt-3"></div>
                    </div>
                </div>
            </div>

            <div class="col-lg-5">
                <div class="card shadow-sm border-0">
                    <div class="card-header">
                        <h5 class="mb-0">Employee Info</h5>
                    </div>
                    <div class="card-body">
                        <p><strong>Employee No:</strong> {{ $employee->employee_no }}</p>
                        <p><strong>Name:</strong> {{ $employee->full_name }}</p>
                        <p><strong>Department:</strong> {{ $employee->department ?: '-' }}</p>
                        <p><strong>Position:</strong> {{ $employee->position ?: '-' }}</p>
                        <p><strong>Registered At:</strong>
                            {{ $employee->face_registered_at ? $employee->face_registered_at->format('M d, Y h:i A') : 'Not yet' }}
                        </p>
                    </div>
                </div>

                <div class="card shadow-sm border-0 mt-3">
                    <div class="card-header">
                        <h5 class="mb-0">Saved Samples</h5>
                    </div>
                    <div class="card-body">
                        <div class="row g-2">
                            @forelse($employee->faceSamples as $sample)
                                <div class="col-6">
                                    <div class="border rounded p-2 text-center">
                                        <img src="{{ asset('storage/' . $sample->image_path) }}"
                                            class="img-fluid rounded mb-2" style="height: 120px; object-fit: cover;">
                                        <div>
                                            @if ($sample->is_primary)
                                                <span class="badge bg-success">Primary</span>
                                            @endif
                                        </div>
                                    </div>
                                </div>
                            @empty
                                <div class="col-12 text-muted">No face samples yet.</div>
                            @endforelse
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const video = document.getElementById('video');
            const canvas = document.getElementById('canvas');
            const startCameraBtn = document.getElementById('startCamera');
            const captureFaceBtn = document.getElementById('captureFace');
            const resultBox = document.getElementById('resultBox');
            const isPrimary = document.getElementById('is_primary');

            let stream = null;

            startCameraBtn.addEventListener('click', async function() {
                try {
                    stream = await navigator.mediaDevices.getUserMedia({
                        video: true,
                        audio: false
                    });
                    video.srcObject = stream;
                    resultBox.innerHTML = '<div class="alert alert-success">Camera started.</div>';
                } catch (error) {
                    resultBox.innerHTML =
                        '<div class="alert alert-danger">Unable to access camera.</div>';
                }
            });

            captureFaceBtn.addEventListener('click', async function() {
                if (!video.srcObject) {
                    resultBox.innerHTML =
                        '<div class="alert alert-warning">Please start the camera first.</div>';
                    return;
                }

                const context = canvas.getContext('2d');
                canvas.width = video.videoWidth;
                canvas.height = video.videoHeight;
                context.drawImage(video, 0, 0, canvas.width, canvas.height);

                const photo = canvas.toDataURL('image/jpeg');

                resultBox.innerHTML = '<div class="alert alert-info">Saving face sample...</div>';

                try {
                    const response = await fetch('{{ route('face-registration.store', $employee) }}', {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': '{{ csrf_token() }}',
                            'Accept': 'application/json',
                        },
                        body: JSON.stringify({
                            photo: photo,
                            is_primary: isPrimary.checked ? 1 : 0
                        })
                    });

                    const data = await response.json();

                    if (data.success) {
                        resultBox.innerHTML = `<div class="alert alert-success">${data.message}</div>`;
                        setTimeout(() => window.location.reload(), 1200);
                    } else {
                        resultBox.innerHTML =
                            `<div class="alert alert-danger">${data.message ?? 'Save failed.'}</div>`;
                    }
                } catch (error) {
                    resultBox.innerHTML = '<div class="alert alert-danger">Request failed.</div>';
                }
            });
        });
    </script>
@endsection
