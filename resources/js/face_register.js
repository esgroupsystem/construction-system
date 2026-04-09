import { FaceLandmarker, FilesetResolver } from "@mediapipe/tasks-vision";

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
 | INIT MEDIAPIPE
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
    });

    el.cameraStatus.textContent = "Ready";
}

/* ----------------------------------------
 | CAMERA START
-----------------------------------------*/
async function startCamera() {
    if (!faceLandmarker) await initModel();

    stream = await navigator.mediaDevices.getUserMedia({ video: true });

    el.video.srcObject = stream;
    await el.video.play();

    el.overlay.width = el.video.videoWidth;
    el.overlay.height = el.video.videoHeight;

    capturing = true;
    detectLoop();
}

/* ----------------------------------------
 | CAPTURE FRAME
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
 | DETECTION LOOP
-----------------------------------------*/
function detectLoop() {
    if (!faceLandmarker || el.video.readyState < 2) {
        requestAnimationFrame(detectLoop);
        return;
    }

    const result = faceLandmarker.detectForVideo(el.video, performance.now());

    if (!result.faceLandmarks?.length) {
        el.status.textContent = "❌ No face detected";
        return requestAnimationFrame(detectLoop);
    }

    el.status.textContent = "✅ Face detected";

    if (capturing) {
        const now = Date.now();

        if (now - lastCaptureTime > CAPTURE_INTERVAL) {
            lastCaptureTime = now;
            captureFrame(result.faceLandmarks[0]);
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