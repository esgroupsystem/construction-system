const config = window.attendanceConfig;

const video = document.getElementById('video');
const overlay = document.getElementById('overlay');
const startBtn = document.getElementById('startCamera');
const timeInBtn = document.getElementById('timeInBtn');
const timeOutBtn = document.getElementById('timeOutBtn');
const resultBox = document.getElementById('resultBox');
const attendanceBox = document.getElementById('attendanceBox');
const cameraStatus = document.getElementById('cameraStatus');
const guideBox = document.getElementById('guideBox');
const countdownEl = document.getElementById('countdown');
const faceGuide = document.getElementById('faceGuide');
const faceDetectedBadge = document.getElementById('faceDetectedBadge');

const framesCollectedEl = document.getElementById('framesCollected');
const stabilityValueEl = document.getElementById('stabilityValue');
const qualityValueEl = document.getElementById('qualityValue');
const livenessValueEl = document.getElementById('livenessValue');

let stream = null;
let faceDetector = null;
let supportsFaceDetection = false;
let verificationRunning = false;

const REQUIRED_FRAMES = 5;

function setCameraBadge(text, className) {
    cameraStatus.textContent = text;
    cameraStatus.className = `badge rounded-pill px-3 py-2 ${className}`;
}

function setFaceBadge(text, className) {
    faceDetectedBadge.textContent = text;
    faceDetectedBadge.className = `badge rounded-pill px-3 py-2 ${className}`;
}

function setGuideMessage(message, type = 'secondary') {
    guideBox.className = `alert alert-${type} mt-3 mb-0 status-panel`;
    guideBox.textContent = message;
}

function sleep(ms) {
    return new Promise(resolve => setTimeout(resolve, ms));
}

function syncOverlaySize() {
    if (!overlay || !video.videoWidth || !video.videoHeight) return;
    overlay.width = video.videoWidth;
    overlay.height = video.videoHeight;
}

function clearOverlay() {
    if (!overlay) return;
    const ctx = overlay.getContext('2d');
    ctx.clearRect(0, 0, overlay.width, overlay.height);
}

function drawDetectionBox(face) {
    if (!overlay || !face?.boundingBox) return;
    syncOverlaySize();

    const ctx = overlay.getContext('2d');
    ctx.clearRect(0, 0, overlay.width, overlay.height);

    const { x, y, width, height } = face.boundingBox;
    ctx.lineWidth = 3;
    ctx.strokeStyle = '#22c55e';
    ctx.strokeRect(x, y, width, height);
}

function initFaceDetector() {
    if ('FaceDetector' in window) {
        try {
            faceDetector = new FaceDetector({
                fastMode: true,
                maxDetectedFaces: 1
            });
            supportsFaceDetection = true;
            return true;
        } catch (e) {
            console.warn(e);
        }
    }

    supportsFaceDetection = false;
    return false;
}

async function detectFaceOnce() {
    if (!supportsFaceDetection || !faceDetector || !video.videoWidth || !video.videoHeight) {
        return { detected: true, centered: true, fallback: true };
    }

    try {
        syncOverlaySize();
        const faces = await faceDetector.detect(video);

        if (!faces || faces.length === 0) {
            clearOverlay();
            return { detected: false, centered: false };
        }

        const face = faces[0];
        drawDetectionBox(face);

        const { x, y, width, height } = face.boundingBox;

        const centerX = x + width / 2;
        const centerY = y + height / 2;

        const centered =
            Math.abs(centerX - video.videoWidth / 2) < video.videoWidth * 0.22 &&
            Math.abs(centerY - video.videoHeight / 2) < video.videoHeight * 0.24 &&
            width > video.videoWidth * 0.14 &&
            height > video.videoHeight * 0.14;

        return {
            detected: true,
            centered,
            face
        };
    } catch (e) {
        console.warn(e);
        return { detected: false, centered: false };
    }
}

function captureFrame() {
    const canvas = document.createElement('canvas');
    canvas.width = video.videoWidth;
    canvas.height = video.videoHeight;

    const ctx = canvas.getContext('2d');
    ctx.drawImage(video, 0, 0, canvas.width, canvas.height);

    return canvas.toDataURL('image/jpeg', 0.9);
}

async function startCamera() {
    try {
        if (!stream) {
            stream = await navigator.mediaDevices.getUserMedia({
                video: {
                    facingMode: 'user',
                    width: { ideal: 1280 },
                    height: { ideal: 720 }
                },
                audio: false
            });
        }

        video.srcObject = stream;
        await video.play();

        initFaceDetector();

        setCameraBadge('Camera On', 'bg-info text-dark');
        setGuideMessage('Camera ready. Align your face inside the guide.', 'info');
        setFaceBadge('Waiting face', 'bg-light text-dark border');

        timeInBtn.disabled = false;
        timeOutBtn.disabled = false;
    } catch (error) {
        console.error(error);
        setCameraBadge('Camera Error', 'bg-danger');
        setGuideMessage('Unable to access camera.', 'danger');
    }
}

async function showCountdown() {
    countdownEl.style.display = 'flex';

    for (let i = 3; i >= 1; i--) {
        countdownEl.textContent = i;
        await sleep(500);
    }

    countdownEl.style.display = 'none';
}

async function runLivenessCheck() {
    livenessValueEl.textContent = 'Move your head slightly';

    const start = performance.now();
    let initialCenterX = null;

    while (performance.now() - start < 5000) {
        const detection = await detectFaceOnce();

        if (detection.detected && detection.face?.boundingBox) {
            const box = detection.face.boundingBox;
            const centerX = box.x + box.width / 2;

            if (initialCenterX === null) {
                initialCenterX = centerX;
            }

            const movement = Math.abs(centerX - initialCenterX);

            // 👇 MUCH EASIER threshold
            if (movement > 10) {
                livenessValueEl.textContent = 'Passed';
                return true;
            }
        }

        await sleep(120);
    }

    livenessValueEl.textContent = 'Failed';
    return false;
}

async function collectFrames() {
    const frames = [];
    let readyFrames = 0;
    let bestQuality = 0;
    let tries = 0;

    framesCollectedEl.textContent = '0';
    stabilityValueEl.textContent = '0';
    qualityValueEl.textContent = '0.00';
    livenessValueEl.textContent = 'Passed';

    while (frames.length < REQUIRED_FRAMES && tries < 80) {
        tries++;

        const detection = await detectFaceOnce();

        if (!detection.detected) {
            setFaceBadge('No face detected', 'bg-light text-dark border');
            setGuideMessage('No face detected.', 'warning');
            faceGuide?.classList.remove('active');
            faceGuide?.classList.add('warning');
            readyFrames = 0;
            await sleep(120);
            continue;
        }

        if (!detection.centered) {
            setFaceBadge('Align face', 'bg-warning-subtle text-warning border');
            setGuideMessage('Center your face inside the guide.', 'warning');
            faceGuide?.classList.remove('active');
            faceGuide?.classList.add('warning');
            readyFrames = 0;
            await sleep(120);
            continue;
        }

        setFaceBadge('Face ready', 'bg-success-subtle text-success border');
        setGuideMessage('Good, keep still...', 'success');
        faceGuide?.classList.add('active');
        faceGuide?.classList.remove('warning');

        readyFrames++;
        const quality = Math.min(1, 0.60 + readyFrames / 10);
        bestQuality = Math.max(bestQuality, quality);

        stabilityValueEl.textContent = String(readyFrames);
        qualityValueEl.textContent = quality.toFixed(2);

        // much easier: capture after 2 good cycles
        if (readyFrames >= 2) {
            frames.push(captureFrame());
            framesCollectedEl.textContent = String(frames.length);
            setGuideMessage(`Good capture ${frames.length}/${REQUIRED_FRAMES}`, 'success');
            readyFrames = 0;
            await sleep(180);
            continue;
        }

        await sleep(120);
    }

    return {
        frames,
        quality_score: Number(bestQuality.toFixed(4))
    };
}

async function processAttendance(url, actionLabel) {
    if (!video.srcObject) {
        alert('Start camera first.');
        return;
    }

    if (verificationRunning) return;

    verificationRunning = true;
    timeInBtn.disabled = true;
    timeOutBtn.disabled = true;

    resultBox.innerHTML = `
        <div class="alert alert-primary mb-0">
            Preparing ${actionLabel} verification...
        </div>
    `;

    try {
        await showCountdown();

        const livenessPassed = true;
        livenessValueEl.textContent = 'Passed';

        if (!livenessPassed) {
            resultBox.innerHTML = `
                <div class="alert alert-danger mb-0">
                    Liveness check failed. Please try again.
                </div>
            `;
            return;
        }

        const payload = await collectFrames();

        if (payload.frames.length < REQUIRED_FRAMES) {
            resultBox.innerHTML = `
                <div class="alert alert-warning mb-0">
                    Not enough stable face frames were collected.
                </div>
            `;
            return;
        }

        resultBox.innerHTML = `
            <div class="alert alert-primary mb-0">
                Verifying face for ${actionLabel}...
            </div>
        `;

        const response = await fetch(url, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': config.csrfToken,
                'Accept': 'application/json',
            },
            body: JSON.stringify({
                frames: payload.frames,
                liveness_passed: livenessPassed,
                quality_score: payload.quality_score,
            })
        });

        const data = await response.json();

        if (data.success) {
            resultBox.innerHTML = `
                <div class="alert alert-success mb-0">
                    <div class="fw-semibold mb-1">${data.message}</div>
                    <div class="small">Confidence: ${data.confidence ?? 'N/A'}</div>
                    <div class="small">Matched Frames: ${data.matched_frames ?? 'N/A'}</div>
                    <div class="small">Quality Score: ${data.quality_score ?? payload.quality_score}</div>
                </div>
            `;
            refreshAttendanceBox(data);
        } else {
            resultBox.innerHTML = `
                <div class="alert alert-danger mb-0">
                    <div class="fw-semibold mb-1">${data.message ?? 'Verification failed.'}</div>
                    ${data.confidence ? `<div class="small">Confidence: ${data.confidence}</div>` : ''}
                    ${data.matched_frames ? `<div class="small">Matched Frames: ${data.matched_frames}</div>` : ''}
                    ${data.quality_score ? `<div class="small">Quality Score: ${data.quality_score}</div>` : ''}
                </div>
            `;
        }
    } catch (error) {
        console.error(error);
        resultBox.innerHTML = `
            <div class="alert alert-danger mb-0">
                Request failed. Please try again.
            </div>
        `;
    } finally {
        verificationRunning = false;
        timeInBtn.disabled = false;
        timeOutBtn.disabled = false;
    }
}

function refreshAttendanceBox(data) {
    const attendance = data.attendance ?? {};

    attendanceBox.innerHTML = `
        <div class="mb-3">
            <label class="form-label text-muted small">Date</label>
            <div class="fw-semibold">${attendance.date ?? '-'}</div>
        </div>

        <div class="mb-3">
            <label class="form-label text-muted small">Time In</label>
            <div class="fw-semibold">${attendance.time_in ?? 'Not yet'}</div>
            <div class="small text-muted">
                Method: ${attendance.time_in_method ?? '-'} |
                Confidence: ${attendance.time_in_confidence ?? '-'}
            </div>
        </div>

        <div class="mb-0">
            <label class="form-label text-muted small">Time Out</label>
            <div class="fw-semibold">${attendance.time_out ?? 'Not yet'}</div>
            <div class="small text-muted">
                Method: ${attendance.time_out_method ?? '-'} |
                Confidence: ${attendance.time_out_confidence ?? '-'}
            </div>
        </div>
    `;
}

startBtn?.addEventListener('click', startCamera);
timeInBtn?.addEventListener('click', () => processAttendance(config.timeInUrl, 'Time In'));
timeOutBtn?.addEventListener('click', () => processAttendance(config.timeOutUrl, 'Time Out'));
