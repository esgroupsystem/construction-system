import { FaceLandmarker, FilesetResolver } from "@mediapipe/tasks-vision";

/* ----------------------------------------
 | 🔕 SILENCE MEDIAPIPE LOGS
-----------------------------------------*/
const ignorePatterns = [
    "vision_wasm", "FaceLandmarker", "tensorflow",
    "Graph", "OpenGL", "feedback_manager",
    "TensorFlow Lite", "gl_context"
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

const faceGuide = document.getElementById("faceGuide");
const countdownEl = document.getElementById("countdown");
const flash = document.getElementById("flash");

/* ----------------------------------------
 | CONFIG
-----------------------------------------*/
const config = window.faceRegisterConfig || {};
const REQUIRED_SAMPLES = config.requiredSamples || 3;

/* ----------------------------------------
 | STATE
-----------------------------------------*/
let faceLandmarker = null;
let stream = null;
let capturing = false;
let samples = [];

let isCountingDown = false;
let lastFaceCenter = null;
let stableFrames = 0;

/* ----------------------------------------
 | INIT MODEL
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

    stream = await navigator.mediaDevices.getUserMedia({
        video: { facingMode: "user", width: 640, height: 480 },
    });

    el.video.srcObject = stream;
    await el.video.play();

    el.overlay.width = el.video.videoWidth;
    el.overlay.height = el.video.videoHeight;

    capturing = true;
    el.status.textContent = "📷 Align your face inside the oval";
    detectLoop();
}

/* ----------------------------------------
 | FACE METRICS
-----------------------------------------*/
function getFaceMetrics(landmarks) {
    let minX = 1, maxX = 0, minY = 1, maxY = 0;

    landmarks.forEach(p => {
        if (p.x < minX) minX = p.x;
        if (p.x > maxX) maxX = p.x;
        if (p.y < minY) minY = p.y;
        if (p.y > maxY) maxY = p.y;
    });

    return {
        centerX: (minX + maxX) / 2,
        centerY: (minY + maxY) / 2,
        width: maxX - minX
    };
}

/* ----------------------------------------
 | CAPTURE FRAME
-----------------------------------------*/
function captureFrame(landmarks) {
    const canvas = document.createElement("canvas");
    canvas.width = el.video.videoWidth;
    canvas.height = el.video.videoHeight;

    canvas.getContext("2d").drawImage(el.video, 0, 0);

    samples.push({
        image: canvas.toDataURL("image/jpeg", 0.9),
        embedding: landmarks.flatMap(p => [p.x, p.y, p.z]),
    });

    el.count.textContent = samples.length;

    if (samples.length >= REQUIRED_SAMPLES) {
        capturing = false;
        el.status.textContent = "✅ Ready to save";
        el.saveBtn.disabled = false;
    }
}

/* ----------------------------------------
 | COUNTDOWN + CAPTURE
-----------------------------------------*/
async function startCountdownAndCapture(landmarks) {
    if (isCountingDown) return;

    isCountingDown = true;
    countdownEl.style.display = "block";

    for (let i = 3; i >= 1; i--) {
        countdownEl.textContent = i;
        await new Promise(r => setTimeout(r, 600));
    }

    countdownEl.style.display = "none";

    // FLASH
    flash.classList.add("active");
    setTimeout(() => flash.classList.remove("active"), 300);

    captureFrame(landmarks);

    isCountingDown = false;
}

/* ----------------------------------------
 | DETECTION LOOP
-----------------------------------------*/
function detectLoop() {
    if (!faceLandmarker || el.video.readyState < 2) {
        requestAnimationFrame(detectLoop);
        return;
    }

    const now = performance.now();
    const result = faceLandmarker.detectForVideo(el.video, now);

    if (!result.faceLandmarks?.length) {
        el.status.textContent = "❌ No face detected";
        faceGuide.classList.remove("active");
        return requestAnimationFrame(detectLoop);
    }

    const landmarks = result.faceLandmarks[0];
    const { centerX, centerY, width } = getFaceMetrics(landmarks);

    // POSITION
    if (centerX < 0.3) return update("➡️ Move right");
    if (centerX > 0.7) return update("⬅️ Move left");
    if (centerY < 0.3) return update("⬇️ Move down");
    if (centerY > 0.7) return update("⬆️ Move up");

    // DISTANCE
    if (width < 0.15) return update("🔍 Move closer");
    if (width > 0.45) return update("📏 Move back");

    // STABILITY
    const current = [centerX, centerY];

    if (lastFaceCenter &&
        Math.abs(current[0] - lastFaceCenter[0]) < 0.01 &&
        Math.abs(current[1] - lastFaceCenter[1]) < 0.01) {
        stableFrames++;
    } else {
        stableFrames = 0;
    }

    lastFaceCenter = current;

    if (stableFrames < 8) return update("🧍 Hold still...");

    // PERFECT
    faceGuide.classList.add("active");
    el.status.textContent = "✅ Perfect! Capturing...";

    if (capturing && samples.length < REQUIRED_SAMPLES) {
        startCountdownAndCapture(landmarks);
    }

    requestAnimationFrame(detectLoop);
}

function update(msg) {
    el.status.textContent = msg;
    faceGuide.classList.remove("active");
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
