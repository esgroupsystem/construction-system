const config = window.faceRegisterConfig;

const video = document.getElementById('video');
const overlay = document.getElementById('overlay');
const statusBox = document.getElementById('statusBox');
const cameraStatus = document.getElementById('cameraStatus');
const startBtn = document.getElementById('startBtn');
const captureBtn = document.getElementById('captureBtn');
const resetBtn = document.getElementById('resetBtn');
const captureCount = document.getElementById('captureCount');
const faceGuide = document.getElementById('faceGuide');
const faceDetectedBadge = document.getElementById('faceDetectedBadge');

let stream = null;
let capturedFrames = [];
let faceDetector = null;
let detectLoopId = null;
let isCapturing = false;
let supportsFaceDetection = false;

function setStatus(message, type = 'secondary') {
    if (!statusBox) return;
    statusBox.className = `alert alert-${type} border mb-0 status-panel`;
    statusBox.textContent = message;
}

function updateCount() {
    if (captureCount) {
        captureCount.textContent = capturedFrames.length;
    }
}

function setCameraBadge(text, typeClass) {
    if (!cameraStatus) return;
    cameraStatus.textContent = text;
    cameraStatus.className = `badge rounded-pill px-3 py-2 ${typeClass}`;
}

function setFaceDetectedState(detected, message = '') {
    if (!faceDetectedBadge) return;

    if (detected) {
        faceDetectedBadge.textContent = message || 'Face detected';
        faceDetectedBadge.className = 'badge rounded-pill bg-success-subtle text-success border';
        faceGuide?.classList.add('active');
        faceGuide?.classList.remove('warning');
    } else {
        faceDetectedBadge.textContent = message || 'No face detected';
        faceDetectedBadge.className = 'badge rounded-pill bg-light text-dark border';
        faceGuide?.classList.remove('active');
        faceGuide?.classList.remove('warning');
    }
}

function setWarningState(message = 'Adjust face position') {
    if (!faceDetectedBadge) return;
    faceDetectedBadge.textContent = message;
    faceDetectedBadge.className = 'badge rounded-pill bg-warning-subtle text-warning border';
    faceGuide?.classList.add('warning');
    faceGuide?.classList.remove('active');
}

function clearOverlay() {
    if (!overlay) return;
    const ctx = overlay.getContext('2d');
    ctx.clearRect(0, 0, overlay.width, overlay.height);
}

function syncOverlaySize() {
    if (!overlay || !video || !video.videoWidth || !video.videoHeight) return;
    overlay.width = video.videoWidth;
    overlay.height = video.videoHeight;
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

function captureFrame() {
    const canvas = document.createElement('canvas');

    const maxWidth = 480;
    const scale = Math.min(1, maxWidth / video.videoWidth);

    canvas.width = Math.floor(video.videoWidth * scale);
    canvas.height = Math.floor(video.videoHeight * scale);

    const ctx = canvas.getContext('2d');
    ctx.drawImage(video, 0, 0, canvas.width, canvas.height);

    return canvas.toDataURL('image/jpeg', 0.7);
}

function sleep(ms) {
    return new Promise(resolve => setTimeout(resolve, ms));
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
        } catch (error) {
            console.warn('FaceDetector init failed:', error);
        }
    }

    supportsFaceDetection = false;
    return false;
}

function isFaceCentered(face) {
    if (!face?.boundingBox || !video.videoWidth || !video.videoHeight) {
        return false;
    }

    const { x, y, width, height } = face.boundingBox;

    const centerX = x + width / 2;
    const centerY = y + height / 2;

    const videoCenterX = video.videoWidth / 2;
    const videoCenterY = video.videoHeight / 2;

    const offsetX = Math.abs(centerX - videoCenterX);
    const offsetY = Math.abs(centerY - videoCenterY);

    const faceLargeEnough = width > video.videoWidth * 0.18 && height > video.videoHeight * 0.18;
    const centeredEnough = offsetX < video.videoWidth * 0.16 && offsetY < video.videoHeight * 0.18;

    return faceLargeEnough && centeredEnough;
}

async function detectFaceOnce() {
    if (!supportsFaceDetection || !faceDetector || !video.videoWidth || !video.videoHeight) {
        return { detected: true, centered: true, face: null, fallback: true };
    }

    try {
        syncOverlaySize();
        const faces = await faceDetector.detect(video);

        if (!faces || faces.length === 0) {
            clearOverlay();
            return { detected: false, centered: false, face: null };
        }

        const face = faces[0];
        drawDetectionBox(face);

        return {
            detected: true,
            centered: isFaceCentered(face),
            face
        };
    } catch (error) {
        console.warn('Face detection failed:', error);
        return { detected: false, centered: false, face: null };
    }
}

async function startDetectionLoop() {
    stopDetectionLoop();

    const loop = async () => {
        if (!stream || video.readyState < 2) {
            detectLoopId = requestAnimationFrame(loop);
            return;
        }

        const result = await detectFaceOnce();

        if (result.fallback) {
            setFaceDetectedState(true, 'Face check unavailable');
            if (captureBtn) captureBtn.disabled = false;
        } else if (!result.detected) {
            setFaceDetectedState(false, 'No face detected');
            if (captureBtn) captureBtn.disabled = true;
            setCameraBadge('Waiting Face', 'bg-warning text-dark');
        } else if (!result.centered) {
            setWarningState('Center face in guide');
            if (captureBtn) captureBtn.disabled = true;
            setCameraBadge('Align Face', 'bg-warning text-dark');
        } else {
            setFaceDetectedState(true, 'Face ready');
            if (captureBtn) captureBtn.disabled = false;
            setCameraBadge('Ready', 'bg-success');
        }

        detectLoopId = requestAnimationFrame(loop);
    };

    detectLoopId = requestAnimationFrame(loop);
}

function stopDetectionLoop() {
    if (detectLoopId) {
        cancelAnimationFrame(detectLoopId);
        detectLoopId = null;
    }
}

async function startCamera() {
    try {
        stream = await navigator.mediaDevices.getUserMedia({
            video: {
                facingMode: 'user',
                width: { ideal: 640 },
                height: { ideal: 800 }
            },
            audio: false
        });

        video.srcObject = stream;
        await video.play();

        initFaceDetector();
        startDetectionLoop();

        setCameraBadge('Camera On', 'bg-info text-dark');
        setStatus(
            supportsFaceDetection
                ? 'Camera started. Place one face inside the guide until status changes to Ready.'
                : 'Camera started. Face detection is not supported in this browser, so capture stays manual.',
            supportsFaceDetection ? 'info' : 'warning'
        );
    } catch (error) {
        console.error(error);
        setCameraBadge('Camera Error', 'bg-danger');
        setStatus('Unable to access camera.', 'danger');
    }
}

function stopCamera() {
    stopDetectionLoop();
    clearOverlay();

    if (stream) {
        stream.getTracks().forEach(track => track.stop());
        stream = null;
    }

    video.srcObject = null;
    if (captureBtn) captureBtn.disabled = true;
    setCameraBadge('Idle', 'bg-secondary');
    setFaceDetectedState(false, 'No face detected');
    faceGuide?.classList.remove('active', 'warning');
}

async function captureSamples() {
    if (isCapturing) return;

    if (!video.srcObject) {
        setStatus('Please start the camera first.', 'warning');
        return;
    }

    if (supportsFaceDetection) {
        const currentCheck = await detectFaceOnce();

        if (!currentCheck.detected) {
            setStatus('No face detected. Please face the camera first.', 'warning');
            return;
        }

        if (!currentCheck.centered) {
            setStatus('Face detected, but not aligned well. Center it inside the guide.', 'warning');
            return;
        }
    }

    isCapturing = true;
    if (captureBtn) captureBtn.disabled = true;
    capturedFrames = [];
    updateCount();

    setCameraBadge('Capturing', 'bg-primary');
    setStatus('Capturing samples. Keep your face steady and look at the camera.', 'primary');

    try {
        const totalSamples = config.requiredSamples || 4;

        for (let i = 0; i < totalSamples; i++) {
            if (supportsFaceDetection) {
                const check = await detectFaceOnce();

                if (!check.detected || !check.centered) {
                    isCapturing = false;
                    setStatus('Capture stopped because the face moved out of position. Please try again.', 'warning');
                    setCameraBadge('Retry Needed', 'bg-warning text-dark');
                    return;
                }
            }

            capturedFrames.push(captureFrame());
            updateCount();
            await sleep(300);
        }

        setCameraBadge('Uploading', 'bg-info text-dark');
        setStatus('Uploading samples...', 'info');

        const response = await fetch(config.postUrl, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': config.csrfToken,
                'Accept': 'application/json',
                'X-Requested-With': 'XMLHttpRequest'
            },
            body: JSON.stringify({
                frames: capturedFrames
            })
        });

        let result = {};
        try {
            result = await response.json();
        } catch (e) {
            throw new Error('Invalid server response.');
        }

        if (!response.ok) {
            console.error('Request failed:', result);
            throw new Error(result.message || 'Registration failed.');
        }

        if (!result.success) {
            throw new Error(result.message || 'Registration failed.');
        }

        setCameraBadge('Saved', 'bg-success');
        setStatus(result.message || 'Face registration completed.', 'success');

        setTimeout(() => {
            window.location.href = result.redirect || window.location.href;
        }, 1200);
    } catch (error) {
        console.error(error);
        setCameraBadge('Error', 'bg-danger');
        setStatus(error.message || 'Registration failed.', 'danger');
    } finally {
        isCapturing = false;
        if (stream) {
            startDetectionLoop();
        }
    }
}

function resetCapture() {
    capturedFrames = [];
    updateCount();
    setStatus('Capture reset. Re-align the face and capture again.', 'secondary');

    if (stream) {
        setCameraBadge('Camera On', 'bg-info text-dark');
    } else {
        setCameraBadge('Idle', 'bg-secondary');
    }
}

async function sendSampleAction(url, method, defaultErrorMessage) {
    const response = await fetch(url, {
        method,
        headers: {
            'X-CSRF-TOKEN': config.csrfToken,
            'Accept': 'application/json',
            'Content-Type': 'application/json',
            'X-Requested-With': 'XMLHttpRequest'
        },
        body: JSON.stringify({})
    });

    let result = {};
    try {
        result = await response.json();
    } catch (e) {
        throw new Error('Invalid server response.');
    }

    if (!response.ok || !result.success) {
        throw new Error(result.message || defaultErrorMessage);
    }

    return result;
}

document.addEventListener('click', async function (e) {
    const setPrimaryBtn = e.target.closest('.set-primary-btn');
    const deleteBtn = e.target.closest('.delete-sample-btn');

    if (setPrimaryBtn) {
        e.preventDefault();

        const url = setPrimaryBtn.dataset.url;
        if (!url) return;

        const originalText = setPrimaryBtn.innerHTML;
        setPrimaryBtn.disabled = true;
        setPrimaryBtn.innerHTML = 'Updating...';

        try {
            const result = await sendSampleAction(url, 'PUT', 'Failed to set primary sample.');
            setStatus(result.message || 'Primary sample updated.', 'success');
            setTimeout(() => window.location.reload(), 500);
        } catch (error) {
            alert(error.message || 'Failed to set primary sample.');
            setPrimaryBtn.disabled = false;
            setPrimaryBtn.innerHTML = originalText;
        }
    }

    if (deleteBtn) {
        e.preventDefault();

        if (!confirm('Are you sure you want to delete this sample?')) {
            return;
        }

        const url = deleteBtn.dataset.url;
        if (!url) return;

        const originalText = deleteBtn.innerHTML;
        deleteBtn.disabled = true;
        deleteBtn.innerHTML = 'Deleting...';

        try {
            const result = await sendSampleAction(url, 'DELETE', 'Failed to delete sample.');
            setStatus(result.message || 'Sample deleted successfully.', 'success');
            setTimeout(() => window.location.reload(), 500);
        } catch (error) {
            alert(error.message || 'Failed to delete sample.');
            deleteBtn.disabled = false;
            deleteBtn.innerHTML = originalText;
        }
    }
});

startBtn?.addEventListener('click', startCamera);
captureBtn?.addEventListener('click', captureSamples);
resetBtn?.addEventListener('click', resetCapture);

window.addEventListener('beforeunload', stopCamera);
window.addEventListener('resize', syncOverlaySize);
