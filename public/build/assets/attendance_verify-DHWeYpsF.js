const B=window.attendanceConfig,n=document.getElementById("video"),c=document.getElementById("overlay"),D=document.getElementById("startCamera"),f=document.getElementById("timeInBtn"),g=document.getElementById("timeOutBtn"),l=document.getElementById("resultBox"),N=document.getElementById("attendanceBox"),p=document.getElementById("cameraStatus"),C=document.getElementById("guideBox"),v=document.getElementById("countdown"),o=document.getElementById("faceGuide"),E=document.getElementById("faceDetectedBadge"),I=document.getElementById("framesCollected"),$=document.getElementById("stabilityValue"),M=document.getElementById("qualityValue"),L=document.getElementById("livenessValue");let y=null,b=null,w=!1,h=!1;const x=5;function F(e,t){p.textContent=e,p.className=`badge rounded-pill px-3 py-2 ${t}`}function m(e,t){E.textContent=e,E.className=`badge rounded-pill px-3 py-2 ${t}`}function r(e,t="secondary"){C.className=`alert alert-${t} mt-3 mb-0 status-panel`,C.textContent=e}function u(e){return new Promise(t=>setTimeout(t,e))}function T(){!c||!n.videoWidth||!n.videoHeight||(c.width=n.videoWidth,c.height=n.videoHeight)}function S(){if(!c)return;c.getContext("2d").clearRect(0,0,c.width,c.height)}function k(e){if(!c||!e?.boundingBox)return;T();const t=c.getContext("2d");t.clearRect(0,0,c.width,c.height);const{x:i,y:s,width:d,height:a}=e.boundingBox;t.lineWidth=3,t.strokeStyle="#22c55e",t.strokeRect(i,s,d,a)}function q(){if("FaceDetector"in window)try{return b=new FaceDetector({fastMode:!0,maxDetectedFaces:1}),w=!0,!0}catch(e){console.warn(e)}return w=!1,!1}async function R(){if(!w||!b||!n.videoWidth||!n.videoHeight)return{detected:!0,centered:!0,fallback:!0};try{T();const e=await b.detect(n);if(!e||e.length===0)return S(),{detected:!1,centered:!1};const t=e[0];k(t);const{x:i,y:s,width:d,height:a}=t.boundingBox,O=i+d/2,_=s+a/2;return{detected:!0,centered:Math.abs(O-n.videoWidth/2)<n.videoWidth*.22&&Math.abs(_-n.videoHeight/2)<n.videoHeight*.24&&d>n.videoWidth*.14&&a>n.videoHeight*.14,face:t}}catch(e){return console.warn(e),{detected:!1,centered:!1}}}function W(){const e=document.createElement("canvas");return e.width=n.videoWidth,e.height=n.videoHeight,e.getContext("2d").drawImage(n,0,0,e.width,e.height),e.toDataURL("image/jpeg",.9)}async function A(){try{y||(y=await navigator.mediaDevices.getUserMedia({video:{facingMode:"user",width:{ideal:1280},height:{ideal:720}},audio:!1})),n.srcObject=y,await n.play(),q(),F("Camera On","bg-info text-dark"),r("Camera ready. Align your face inside the guide.","info"),m("Waiting face","bg-light text-dark border"),f.disabled=!1,g.disabled=!1}catch(e){console.error(e),F("Camera Error","bg-danger"),r("Unable to access camera.","danger")}}async function V(){v.style.display="flex";for(let e=3;e>=1;e--)v.textContent=e,await u(500);v.style.display="none"}async function P(){const e=[];let t=0,i=0,s=0;for(I.textContent="0",$.textContent="0",M.textContent="0.00",L.textContent="Passed";e.length<x&&s<80;){s++;const d=await R();if(!d.detected){m("No face detected","bg-light text-dark border"),r("No face detected.","warning"),o?.classList.remove("active"),o?.classList.add("warning"),t=0,await u(120);continue}if(!d.centered){m("Align face","bg-warning-subtle text-warning border"),r("Center your face inside the guide.","warning"),o?.classList.remove("active"),o?.classList.add("warning"),t=0,await u(120);continue}m("Face ready","bg-success-subtle text-success border"),r("Good, keep still...","success"),o?.classList.add("active"),o?.classList.remove("warning"),t++;const a=Math.min(1,.6+t/10);if(i=Math.max(i,a),$.textContent=String(t),M.textContent=a.toFixed(2),t>=2){e.push(W()),I.textContent=String(e.length),r(`Good capture ${e.length}/${x}`,"success"),t=0,await u(180);continue}await u(120)}return{frames:e,quality_score:Number(i.toFixed(4))}}async function H(e,t){if(!n.srcObject){alert("Start camera first.");return}if(!h){h=!0,f.disabled=!0,g.disabled=!0,l.innerHTML=`
        <div class="alert alert-primary mb-0">
            Preparing ${t} verification...
        </div>
    `;try{await V();const i=!0;L.textContent="Passed";const s=await P();if(s.frames.length<x){l.innerHTML=`
                <div class="alert alert-warning mb-0">
                    Not enough stable face frames were collected.
                </div>
            `;return}l.innerHTML=`
            <div class="alert alert-primary mb-0">
                Verifying face for ${t}...
            </div>
        `;const a=await(await fetch(e,{method:"POST",headers:{"Content-Type":"application/json","X-CSRF-TOKEN":B.csrfToken,Accept:"application/json"},body:JSON.stringify({frames:s.frames,liveness_passed:i,quality_score:s.quality_score})})).json();a.success?(l.innerHTML=`
                <div class="alert alert-success mb-0">
                    <div class="fw-semibold mb-1">${a.message}</div>
                    <div class="small">Confidence: ${a.confidence??"N/A"}</div>
                    <div class="small">Matched Frames: ${a.matched_frames??"N/A"}</div>
                    <div class="small">Quality Score: ${a.quality_score??s.quality_score}</div>
                </div>
            `,j(a)):l.innerHTML=`
                <div class="alert alert-danger mb-0">
                    <div class="fw-semibold mb-1">${a.message??"Verification failed."}</div>
                    ${a.confidence?`<div class="small">Confidence: ${a.confidence}</div>`:""}
                    ${a.matched_frames?`<div class="small">Matched Frames: ${a.matched_frames}</div>`:""}
                    ${a.quality_score?`<div class="small">Quality Score: ${a.quality_score}</div>`:""}
                </div>
            `}catch(i){console.error(i),l.innerHTML=`
            <div class="alert alert-danger mb-0">
                Request failed. Please try again.
            </div>
        `}finally{h=!1,f.disabled=!1,g.disabled=!1}}}function j(e){const t=e.attendance??{};N.innerHTML=`
        <div class="mb-3">
            <label class="form-label text-muted small">Date</label>
            <div class="fw-semibold">${t.date??"-"}</div>
        </div>

        <div class="mb-3">
            <label class="form-label text-muted small">Time In</label>
            <div class="fw-semibold">${t.time_in??"Not yet"}</div>
        </div>

        <div class="mb-0">
            <label class="form-label text-muted small">Time Out</label>
            <div class="fw-semibold">${t.time_out??"Not yet"}</div>
        </div>
    `}D?.addEventListener("click",A);f?.addEventListener("click",()=>H(B.timeInUrl,"Time In"));g?.addEventListener("click",()=>H(B.timeOutUrl,"Time Out"));
