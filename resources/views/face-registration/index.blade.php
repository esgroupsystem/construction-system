@extends('layouts.app')

@section('title', 'Face Registration')

@section('content')
    <div class="container-fluid">

        <div class="row g-4">

            {{-- LEFT: CAMERA --}}
            <div class="col-xl-7">

                <div class="card border-0 shadow-sm h-100">

                    {{-- HEADER --}}
                    <div class="card-header bg-white border-0 d-flex justify-content-between align-items-center">
                        <div>
                            <h4 class="mb-1">Face Registration</h4>
                            <p class="text-muted mb-0">
                                <strong>{{ $employee->full_name }}</strong>
                                @if ($employee->employee_no)
                                    • {{ $employee->employee_no }}
                                @endif
                            </p>
                        </div>

                        <span class="badge bg-info-subtle text-info px-3 py-2" id="cameraStatus">
                            Initializing...
                        </span>
                    </div>

                    {{-- BODY --}}
                    <div class="card-body">

                        {{-- CAMERA --}}
                        <div class="position-relative rounded overflow-hidden bg-dark border">
                            <video id="video" autoplay playsinline style="width:100%; height:420px; object-fit:cover;">
                            </video>

                            <canvas id="overlay" class="position-absolute top-0 start-0 w-100 h-100">
                            </canvas>
                        </div>

                        {{-- STATUS --}}
                        <div class="mt-3">

                            <div class="alert alert-soft-primary border-0 mb-3" id="statusBox">
                                Position your face in front of the camera.
                            </div>

                            <div class="d-flex flex-wrap align-items-center gap-2">

                                <button type="button" class="btn btn-primary" id="startBtn">
                                    <i class="fas fa-video me-1"></i> Start
                                </button>

                                <button type="button" class="btn btn-success" id="saveBtn" disabled>
                                    <i class="fas fa-save me-1"></i> Save
                                </button>

                                <button type="button" class="btn btn-outline-secondary" id="resetBtn">
                                    Reset
                                </button>

                                <span class="badge bg-success-subtle text-success px-3 py-2">
                                    Captured: <span id="captureCount">0</span>/3
                                </span>

                            </div>

                            <div class="small text-muted mt-2">
                                Auto capture works when your face is centered and stable.
                            </div>
                        </div>

                        {{-- PREVIEW --}}
                        <div class="mt-4">
                            <h6 class="mb-3">Captured Samples</h6>
                            <div class="row g-3" id="previewGrid"></div>
                        </div>

                    </div>
                </div>
            </div>

            {{-- RIGHT: REGISTERED --}}
            <div class="col-xl-5">

                <div class="card border-0 shadow-sm h-100">

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
@endsection
