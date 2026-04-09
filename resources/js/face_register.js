import { FaceLandmarker, FilesetResolver } from "@mediapipe/tasks-vision";

/* ----------------------------------------
 | 🔕 SILENCE MEDIAPIPE LOGS (FIX YOUR ISSUE)
-----------------------------------------*/
/* ----------------------------------------
 🔕 FORCE REMOVE ALL MEDIAPIPE LOGS (FINAL FIX)
-----------------------------------------*/
const ignorePatterns = [
    "vision_wasm",
    "FaceLandmarker",
    "tensorflow",
    "Graph",
    "OpenGL",
    "feedback_manager",
    "TensorFlow Lite",
    "gl_context"
];

function shouldIgnore(msg) {
    return ignorePatterns.some(p => msg?.toString().includes(p));
}

['log', 'warn', 'error', 'info', 'debug'].forEach(method => {
    const original = console[method];
    console[method] = (...args) => {
        if (shouldIgnore(args[0])) return;
        original.apply(console, args);
    };
});

/* ----------------------------------------
 | DOM
-----------------------------------------*/
const el = {
    video: document.getElementById("video"),
    overlay: document.getElementById("overlay"),
    startBtn: document.getElementById("startBtn"),
    saveBtn: document.getElementById("saveBtn"),
    resetBtn: document.getElementById("resetBtn"),
    status: document.getElementById("statusBox"),
    count: document.getElementById("captureCount"),
    cameraStatus: document.getElementById("cameraStatus"),
};

/* ----------------------------------------
 | CONFIG
-----------------------------------------*/
const config = window.faceRegisterConfig || {};
const REQUIRED_SAMPLES = config.requiredSamples || 3;
const CAPTURE_INTERVAL = 1200;

/* ----------------------------------------
 | STATE
-----------------------------------------*/
let faceLandmarker = null;
let stream = null;
let capturing = false;
let samples = [];
let lastCaptureTime = 0;

/* ----------------------------------------
 | INIT MEDIAPIPE (CLEAN VERSION)
-----------------------------------------*/
async function initModel() {
    const vision = await FilesetResolver.forVisionTasks(
        "https://cdn.jsdelivr.net/npm/@mediapipe/tasks-vision@latest/wasm"
    );

    faceLandmarker = await FaceLandmarker.createFromOptions(vision, {
        baseOptions: {
            modelAssetPath:
                "https://storage.googleapis.com/mediapipe-models/face_landmarker/face_landmarker/float16/latest/face_landmarker.task",
        },
        runningMode: "VIDEO",
        numFaces: 1,
        outputFaceBlendshapes: false,
        outputFacialTransformationMatrixes: false,
    });

    el.cameraStatus.textContent = "Ready";
}

/* ----------------------------------------
 | CAMERA START
-----------------------------------------*/
async function startCamera() {
    if (!faceLandmarker) await initModel();

    stream = await navigator.mediaDevices.getUserMedia({
        video: {
            facingMode: "user",
            width: { ideal: 640 },
            height: { ideal: 480 },
        },
    });

    el.video.srcObject = stream;
    await el.video.play();

    el.overlay.width = el.video.videoWidth;
    el.overlay.height = el.video.videoHeight;

    capturing = true;
    el.status.textContent = "Starting...";
    detectLoop();
}

/* ----------------------------------------
 | CAPTURE FRAME (IMPROVED)
-----------------------------------------*/
function captureFrame(landmarks) {
    if (!landmarks || landmarks.length < 50) return;

    const embedding = landmarks.flatMap(p => [
        Number(p.x || 0),
        Number(p.y || 0),
        Number(p.z || 0),
    ]);

    if (embedding.length < 300) return;

    const canvas = document.createElement("canvas");
    canvas.width = el.video.videoWidth;
    canvas.height = el.video.videoHeight;

    canvas.getContext("2d").drawImage(el.video, 0, 0);

    samples.push({
        image: canvas.toDataURL("image/jpeg", 0.9),
        embedding,
    });

    el.count.textContent = samples.length;

    if (samples.length >= REQUIRED_SAMPLES) {
        capturing = false;
        el.status.textContent = "✅ Ready to save";
        el.saveBtn.disabled = false;
    }
}

/* ----------------------------------------
 | DETECTION LOOP (OPTIMIZED)
-----------------------------------------*/
function isFaceCentered(landmarks) {
    const nose = landmarks[1]; // nose tip

    if (!nose) return false;

    return (
        nose.x > 0.35 && nose.x < 0.65 &&
        nose.y > 0.30 && nose.y < 0.70
    );
}

function detectLoop() {
    if (!faceLandmarker || el.video.readyState < 2) {
        requestAnimationFrame(detectLoop);
        return;
    }

    const nowPerf = performance.now();

    // 🔥 throttle (fix lag)
    if (!window.lastDetect) window.lastDetect = 0;
    if (nowPerf - window.lastDetect < 100) {
        requestAnimationFrame(detectLoop);
        return;
    }
    window.lastDetect = nowPerf;

    const result = faceLandmarker.detectForVideo(el.video, nowPerf);

    if (!result.faceLandmarks?.length) {
        el.status.textContent = "❌ No face";
        return requestAnimationFrame(detectLoop);
    }

    const landmarks = result.faceLandmarks[0];

    // 🎯 CENTER CHECK
    if (!isFaceCentered(landmarks)) {
        el.status.textContent = "📍 Center your face";
        return requestAnimationFrame(detectLoop);
    }

    el.status.textContent = "✅ Hold still...";

    if (capturing) {
        const now = Date.now();

        if (now - lastCaptureTime > CAPTURE_INTERVAL) {
            lastCaptureTime = now;
            captureFrame(landmarks);
        }
    }

    requestAnimationFrame(detectLoop);
}

/* ----------------------------------------
 | SAVE
-----------------------------------------*/
async function saveSamples() {
    if (samples.length < REQUIRED_SAMPLES) {
        return alert("Capture more samples first.");
    }

    try {
        const res = await fetch(config.postUrl, {
            method: "POST",
            headers: {
                "Content-Type": "application/json",
                "X-CSRF-TOKEN": config.csrfToken,
                Accept: "application/json",
            },
            body: JSON.stringify({ samples }),
        });

        const data = await res.json();

        if (!res.ok) throw new Error(data.message);

        alert("Saved successfully");
        location.reload();
    } catch (err) {
        console.error(err);
        alert(err.message || "Save failed");
    }
}

/* ----------------------------------------
 | EVENTS
-----------------------------------------*/
el.startBtn?.addEventListener("click", startCamera);
el.saveBtn?.addEventListener("click", saveSamples);
el.resetBtn?.addEventListener("click", () => {
    samples = [];
    el.count.textContent = 0;
    el.saveBtn.disabled = true;
    capturing = true;
    el.status.textContent = "Reset complete";
});