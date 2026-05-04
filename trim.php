<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8"/>
  <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
  <title>Trim Audio</title>
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link href="https://fonts.googleapis.com/css2?family=Syne:wght@400;600;700;800&family=DM+Sans:wght@300;400;500&family=DM+Mono:wght@400;500&display=swap" rel="stylesheet">
  <script src="https://unpkg.com/wavesurfer.js@7/dist/wavesurfer.min.js"></script>
  <style>
    *,*::before,*::after{box-sizing:border-box;margin:0;padding:0}
    :root{
      --bg:#07090f;--bg2:#0d1117;--surface:#111827;--surface2:#1a2235;
      --border:#1e2d45;--border2:#2a3f5f;
      --blue:#2a7ae8;--blue2:#4f9eff;--blue-glow:rgba(42,122,232,.35);
      --cyan:#00c8ff;--text:#e8f0ff;--muted:#5a7a9a;--dim:#2a3f5f;
      --green:#22c55e;--orange:#f59e0b;--radius:14px;
    }
    html,body{min-height:100%;background:var(--bg);color:var(--text);font-family:'DM Sans',sans-serif}
    body::before{
      content:'';position:fixed;inset:0;pointer-events:none;z-index:0;
      background:radial-gradient(ellipse 80% 50% at 50% -5%,rgba(42,122,232,.15) 0%,transparent 60%);
    }

    /* NAV */
    nav{
      position:sticky;top:0;z-index:100;
      background:rgba(7,9,15,.88);backdrop-filter:blur(20px);-webkit-backdrop-filter:blur(20px);
      border-bottom:1px solid var(--border);
      padding:0 2.5rem;height:60px;
      display:flex;align-items:center;justify-content:space-between;
    }
    .logo{font-family:'Syne',sans-serif;font-weight:800;font-size:1.1rem;letter-spacing:-.03em;color:var(--blue2);text-decoration:none}
    .nav-r{display:flex;align-items:center;gap:.8rem}
    .nav-file{font-size:.8rem;color:var(--muted);background:var(--surface);border:1px solid var(--border);border-radius:100px;padding:.3rem .85rem;max-width:200px;overflow:hidden;text-overflow:ellipsis;white-space:nowrap}
    .nav-back{font-size:.82rem;font-weight:600;color:var(--muted);text-decoration:none;border:1px solid var(--border);border-radius:100px;padding:.35rem .85rem;transition:all .15s}
    .nav-back:hover{border-color:var(--blue2);color:var(--blue2)}

    /* PAGE */
    .page{position:relative;z-index:1;max-width:900px;margin:0 auto;padding:2rem 1.5rem 5rem;display:flex;flex-direction:column;gap:1.4rem}

    /* CARD */
    .card{background:var(--surface);border:1px solid var(--border);border-radius:var(--radius);overflow:hidden}
    .card-head{padding:1rem 1.6rem;border-bottom:1px solid var(--border);display:flex;align-items:center;justify-content:space-between}
    .card-title{font-family:'Syne',sans-serif;font-weight:700;font-size:.9rem;display:flex;align-items:center;gap:.5rem}
    .cdot{width:7px;height:7px;border-radius:50%;background:var(--blue)}

    /* TRIM MODE TOGGLE */
    .mode-toggle{display:flex;gap:.5rem;padding:1.2rem 1.6rem;border-bottom:1px solid var(--border)}
    .mtab{
      display:flex;align-items:center;gap:.5rem;
      font-size:.82rem;font-weight:600;padding:.55rem 1.1rem;border-radius:100px;
      border:1.5px solid var(--border);cursor:pointer;background:transparent;color:var(--muted);
      transition:all .2s;
    }
    .mtab:hover{border-color:var(--border2);color:var(--text)}
    .mtab.on{background:var(--blue);border-color:var(--blue);color:#fff}
    .mtab .ico{font-size:.95rem}

    /* MODE DESCRIPTION */
    .mode-desc{
      padding:.7rem 1.6rem;font-size:.8rem;color:var(--muted);
      border-bottom:1px solid var(--border);
      background:rgba(42,122,232,.04);
      display:flex;align-items:center;gap:.5rem;
    }

    /* ── WAVEFORM CONTAINER ── */
    .wave-outer{
      position:relative;
      padding:1.6rem 1.6rem .8rem;
      user-select:none;
    }

    /* The WaveSurfer canvas wrapper */
    #waveform{width:100%;position:relative;z-index:1}

    /* Overlay sits ON TOP of waveform */
    .wave-overlay{
      position:absolute;
      /* will be set by JS to match waveform inner bounds */
      top:0;left:0;right:0;bottom:0;
      pointer-events:none;
      z-index:2;
    }

    /* Shaded region (what gets CUT shown darker) */
    .shade{
      position:absolute;top:0;bottom:0;
      background:rgba(7,9,15,.62);
      pointer-events:none;
    }
    /* Keep region highlight */
    .keep-region{
      position:absolute;top:0;bottom:0;
      background:rgba(42,122,232,.12);
      border-top:2px solid var(--blue);
      border-bottom:2px solid var(--blue);
      pointer-events:none;
    }

    /* Drag handle */
    .handle{
      position:absolute;top:0;bottom:0;width:16px;
      background:var(--blue);
      cursor:ew-resize;
      pointer-events:all;
      display:flex;align-items:center;justify-content:center;
      z-index:4;
      border-radius:3px;
      box-shadow:0 0 12px var(--blue-glow);
      transition:background .15s;
    }
    .handle:hover{background:var(--blue2)}
    .handle::after{
      content:'';width:2px;height:24px;
      background:rgba(255,255,255,.6);border-radius:2px;
    }
    .handle-label{
      position:absolute;top:-26px;
      background:var(--blue);color:#fff;
      font-family:'DM Mono',monospace;font-size:.68rem;font-weight:500;
      padding:.18rem .45rem;border-radius:4px;white-space:nowrap;pointer-events:none;
      left:50%;transform:translateX(-50%);
    }
    .handle.right .handle-label{background:var(--cyan);color:#07090f}

    /* LOADING overlay */
    .wave-load{
      position:absolute;inset:0;z-index:10;
      background:var(--surface);
      display:flex;align-items:center;justify-content:center;gap:.7rem;
      font-size:.88rem;color:var(--muted);transition:opacity .5s;
    }
    .wave-load.gone{opacity:0;pointer-events:none}
    .mini-spin{width:18px;height:18px;border-radius:50%;border:2px solid var(--border2);border-top-color:var(--blue);animation:spin .8s linear infinite}
    @keyframes spin{to{transform:rotate(360deg)}}

    /* Time ruler */
    .ruler{display:flex;justify-content:space-between;padding:.3rem 0 .8rem;font-family:'DM Mono',monospace;font-size:.68rem;color:var(--dim)}

    /* PLAYBACK BAR */
    .play-bar{padding:.9rem 1.6rem;border-top:1px solid var(--border);display:flex;align-items:center;justify-content:space-between;flex-wrap:wrap;gap:.7rem}
    .play-btns{display:flex;align-items:center;gap:.6rem}
    .pbtn{
      width:40px;height:40px;border-radius:50%;
      border:1px solid var(--border);background:var(--surface2);
      display:flex;align-items:center;justify-content:center;
      cursor:pointer;color:var(--text);transition:all .15s;
    }
    .pbtn:hover{border-color:var(--blue2)}
    .pbtn.main{width:48px;height:48px;background:var(--blue);border-color:var(--blue);box-shadow:0 0 20px var(--blue-glow)}
    .pbtn.main:hover{background:var(--blue2)}
    .pbtn svg{width:15px;height:15px}.pbtn.main svg{width:19px;height:19px}
    .time-disp{font-family:'DM Mono',monospace;font-size:.85rem;color:var(--muted)}
    .time-disp strong{color:var(--text)}
    .prev-btn{
      font-size:.8rem;font-weight:600;color:var(--muted);
      background:var(--surface2);border:1px solid var(--border);border-radius:100px;
      padding:.4rem .9rem;cursor:pointer;display:flex;align-items:center;gap:.35rem;
      transition:all .15s;
    }
    .prev-btn:hover{border-color:var(--blue2);color:var(--blue2)}

    /* SELECTION INFO */
    .sel-info{
      display:flex;align-items:center;gap:1.5rem;
      padding:.8rem 1.6rem;border-top:1px solid var(--border);
      font-family:'DM Mono',monospace;font-size:.8rem;flex-wrap:wrap;
    }
    .si{display:flex;flex-direction:column;gap:.18rem}
    .si-label{font-size:.65rem;text-transform:uppercase;letter-spacing:.08em;color:var(--muted);font-weight:700}
    .si-val{color:var(--text);font-weight:500}
    .si-val.start{color:var(--blue2)}
    .si-val.end{color:var(--cyan)}
    .si-val.dur{color:var(--green)}

    /* ACTIONS */
    .actions{padding:1.2rem 1.6rem;border-top:1px solid var(--border);display:flex;gap:.75rem;flex-wrap:wrap}
    .btn{
      flex:1;min-width:140px;
      display:inline-flex;align-items:center;justify-content:center;gap:.5rem;
      font-family:'Syne',sans-serif;font-weight:700;font-size:.9rem;
      padding:.85rem 1.4rem;border-radius:100px;border:none;cursor:pointer;
      transition:transform .18s,box-shadow .18s,opacity .2s;
      text-decoration:none;
    }
    .btn:disabled{opacity:.35;cursor:not-allowed;pointer-events:none}
    .btn:hover:not(:disabled){transform:translateY(-2px)}
    .btn-blue{background:var(--blue);color:#fff;box-shadow:0 3px 20px var(--blue-glow)}
    .btn-blue:hover:not(:disabled){box-shadow:0 4px 30px rgba(42,122,232,.6)}
    .btn-ghost{background:var(--surface2);color:var(--text);border:1px solid var(--border)}
    .btn-ghost:hover:not(:disabled){border-color:var(--border2)}
    .btn-green{background:var(--green);color:#fff;box-shadow:0 3px 18px rgba(34,197,94,.25);animation:popIn .35s cubic-bezier(.34,1.56,.64,1) both}
    @keyframes popIn{from{opacity:0;transform:scale(.8)}to{opacity:1;transform:scale(1)}}

    /* ERR */
    .err{display:none;padding:.8rem 1.2rem;background:rgba(239,68,68,.1);border:1px solid rgba(239,68,68,.3);border-radius:8px;color:#f87171;font-size:.85rem;align-items:center;gap:.5rem}
    .err.on{display:flex}

    /* PROC OVERLAY */
    .proc{display:none;position:fixed;inset:0;background:rgba(7,9,15,.93);backdrop-filter:blur(20px);z-index:500;align-items:center;justify-content:center;flex-direction:column;gap:1.5rem;text-align:center}
    .proc.on{display:flex}
    .proc-ico{font-size:2.5rem;animation:float 2s ease-in-out infinite}
    @keyframes float{0%,100%{transform:translateY(0)}50%{transform:translateY(-8px)}}
    .proc-title{font-family:'Syne',sans-serif;font-size:1.4rem;font-weight:800;letter-spacing:-.03em}
    .proc-steps{display:flex;flex-direction:column;gap:.4rem}
    .pstep{font-size:.8rem;color:var(--dim);display:flex;align-items:center;gap:.4rem;justify-content:center;transition:color .3s}
    .pstep.on{color:var(--blue2)}.pstep.done{color:var(--green)}
    .pstep-dot{width:5px;height:5px;border-radius:50%;background:currentColor;flex-shrink:0}
    .proc-track{width:220px;height:3px;background:var(--border2);border-radius:100px;overflow:hidden}
    .proc-fill{height:100%;width:0%;background:linear-gradient(90deg,var(--blue),var(--cyan));border-radius:100px;transition:width .4s ease}

    .pbtn.skip-btn{width:38px;height:38px;border-radius:10px}
    .si-input{
      background:var(--surface2);border:1px solid var(--border);border-radius:6px;
      color:var(--text);font-family:'DM Mono',monospace;font-size:.82rem;font-weight:500;
      padding:.3rem .5rem;width:72px;outline:none;cursor:text;
      transition:border-color .15s;
    }
    .si-input:focus{border-color:var(--blue2)}
    .si-input.start{color:var(--blue2)}
    .si-input.end{color:var(--cyan)}
  </style>
</head>
<body>
<?php
$filename     = isset($_GET['file'])     ? basename($_GET['file'])     : '';
$originalName = isset($_GET['original']) ? $_GET['original']           : $filename;
$filePath     = __DIR__ . '/uploads/' . $filename;
$audioUrl     = 'uploads/' . rawurlencode($filename);
if (!$filename || !file_exists($filePath)) { header('Location: index.php'); exit; }
$fileSize = filesize($filePath);
function fmtSz(int $b):string{ if($b<1024)return $b.' B'; if($b<1048576)return round($b/1024,1).' KB'; return round($b/1048576,2).' MB'; }
$safe_file=$safeOrig=htmlspecialchars($filename,ENT_QUOTES,'UTF-8');
$safeOrig=htmlspecialchars($originalName,ENT_QUOTES,'UTF-8');
$safeAudio=htmlspecialchars($audioUrl,ENT_QUOTES,'UTF-8');
?>
<nav>
  <a href="index.php" class="logo">✦ Audio Tool</a>
  <div class="nav-r">
    <span class="nav-file">✂️ <?= $safeOrig ?></span>
    <a href="index.php" class="nav-back">← New File</a>
  </div>
</nav>

<div class="page">

  <div class="err" id="err"><svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="10"/><line x1="12" y1="8" x2="12" y2="12"/><line x1="12" y1="16" x2="12.01" y2="16"/></svg><span id="err-msg"></span></div>

  <div class="card">
    <div class="card-head">
      <div class="card-title"><div class="cdot"></div>Waveform Trim</div>
      <div class="time-disp"><strong id="ct">0:00.0</strong> / <span id="tt">—</span></div>
    </div>

    <!-- Trim mode selector -->
    <div class="mode-toggle">
      <button class="mtab on" id="tab-center" onclick="setTrimMode('center')">
        <span class="ico">↔️</span> Keep Center
      </button>
      <button class="mtab" id="tab-sides" onclick="setTrimMode('sides')">
        <span class="ico">⬅️➡️</span> Keep Sides (Remove Middle)
      </button>
    </div>
    <div class="mode-desc" id="mode-desc">
      <span>✂️</span>
      <span id="mode-desc-text">Drag the left/right handles on the waveform. The highlighted region will be <strong>kept</strong>.</span>
    </div>

    <!-- WAVEFORM -->
    <div class="wave-outer" id="wave-outer">
      <div id="waveform"></div>

      <!-- Overlay rendered on top of waveform -->
      <div class="wave-overlay" id="wave-overlay">
        <!-- Shaded regions (removed parts) -->
        <div class="shade" id="shade-left"></div>
        <div class="shade" id="shade-right"></div>
        <!-- For "keep sides" mode: center shade -->
        <div class="shade" id="shade-center" style="display:none"></div>
        <!-- Keep region -->
        <div class="keep-region" id="keep-region"></div>
        <!-- Handles -->
        <div class="handle left" id="h-left">
          <div class="handle-label" id="hl-left">0:00.0</div>
        </div>
        <div class="handle right" id="h-right" style="--hc:var(--cyan)">
          <div class="handle-label" id="hl-right">0:00.0</div>
        </div>
      </div>

      <div class="wave-load" id="wave-load">
        <div class="mini-spin"></div><span>Decoding audio…</span>
      </div>
      <div class="ruler" id="ruler"><span>0:00</span><span></span><span></span><span></span><span></span></div>
    </div>

    <!-- Playback -->
    <div class="play-bar">
      <div class="play-btns">
        <button class="pbtn" onclick="ws.seekTo(0)" title="Rewind to start">
          <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><polygon points="19 20 9 12 19 4 19 20"/><line x1="5" y1="19" x2="5" y2="5"/></svg>
        </button>
        <button class="pbtn skip-btn" onclick="seekBy(-10)" title="-10 seconds">
          <span style="font-size:.72rem;font-weight:700;font-family:'DM Mono',monospace">−10</span>
        </button>
        <button class="pbtn main" id="btn-play">
          <svg id="ico-play" viewBox="0 0 24 24" fill="currentColor"><polygon points="5 3 19 12 5 21 5 3"/></svg>
          <svg id="ico-pause" viewBox="0 0 24 24" fill="currentColor" style="display:none"><rect x="6" y="4" width="4" height="16"/><rect x="14" y="4" width="4" height="16"/></svg>
        </button>
        <button class="pbtn skip-btn" onclick="seekBy(10)" title="+10 seconds">
          <span style="font-size:.72rem;font-weight:700;font-family:'DM Mono',monospace">+10</span>
        </button>
        <button class="pbtn" onclick="ws.seekTo(1)" title="Skip to end">
          <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><polygon points="5 4 15 12 5 20 5 4"/><line x1="19" y1="5" x2="19" y2="19"/></svg>
        </button>
      </div>
      <button class="prev-btn" id="btn-prev">
        <svg width="12" height="12" viewBox="0 0 24 24" fill="currentColor"><polygon points="5 3 19 12 5 21 5 3"/></svg>
        Preview Selection
      </button>
    </div>

    <!-- Selection info -->
    <div class="sel-info" id="sel-info">
      <div class="si">
        <div class="si-label">Start</div>
        <input class="si-input start" id="si-start" value="—" autocomplete="off" spellcheck="false" title="Edit start time (m:ss.s)">
      </div>
      <div class="si">
        <div class="si-label">End</div>
        <input class="si-input end" id="si-end" value="—" autocomplete="off" spellcheck="false" title="Edit end time (m:ss.s)">
      </div>
      <div class="si"><div class="si-label">Duration</div><div class="si-val dur" id="si-dur">—</div></div>
      <div class="si"><div class="si-label">Mode</div><div class="si-val" id="si-mode">Keep Center</div></div>
    </div>

    <!-- Actions -->
    <div class="actions">
      <button class="btn btn-blue" id="btn-cut" disabled>
        ✂️ Cut &amp; Download
      </button>
      <button class="btn btn-ghost" onclick="resetHandles()">↺ Reset</button>
    </div>
  </div>

</div>

<!-- Processing overlay -->
<div class="proc" id="proc">
  <div class="proc-ico">✂️</div>
  <div class="proc-title">Trimming Audio…</div>
  <div class="proc-steps">
    <div class="pstep on" id="ps1"><div class="pstep-dot"></div>Sending trim points</div>
    <div class="pstep" id="ps2"><div class="pstep-dot"></div>Running FFmpeg</div>
    <div class="pstep" id="ps3"><div class="pstep-dot"></div>Preparing download</div>
  </div>
  <div class="proc-track"><div class="proc-fill" id="proc-fill"></div></div>
</div>

<script>
const AUDIO_URL  = <?= json_encode($safeAudio) ?>;
const AUDIO_FILE = <?= json_encode($safe_file) ?>;
const ORIG_NAME  = <?= json_encode($safeOrig) ?>;

let dur       = 0;
let wsReady   = false;
let trimMode  = 'center'; // 'center' = keep middle | 'sides' = keep sides, remove middle
let startSec  = 0;
let endSec    = 0;
let prevTimer = null;
let waveLeft  = 0;  // px offset of waveform inside wave-outer
let waveW     = 0;  // px width of waveform

// ── WaveSurfer ────────────────────────────────────────────────────────────────
const ws = WaveSurfer.create({
  container:'#waveform',
  waveColor:'#1e3a5f',
  progressColor:'#2a7ae8',
  cursorColor:'#00c8ff',
  cursorWidth:2,
  height:100,
  barWidth:2,barGap:1.5,barRadius:3,
  normalize:true,interact:true,
});
ws.load(AUDIO_URL);

ws.on('ready',()=>{
  dur=ws.getDuration(); endSec=dur; wsReady=true;
  document.getElementById('wave-load').classList.add('gone');
  document.getElementById('tt').textContent=fmt(dur);
  document.getElementById('btn-cut').disabled=false;
  buildRuler();
  // Get waveform bounding box AFTER render
  setTimeout(()=>{ calibrate(); updateOverlay(); updateInfo(); },80);
  window.addEventListener('resize',()=>{ calibrate(); updateOverlay(); });
});
ws.on('audioprocess',()=>{
  document.getElementById('ct').textContent=fmt(ws.getCurrentTime());
  if(ws.getCurrentTime()>=endSec) ws.pause();
});
ws.on('play',()=>{ show('ico-play',false);show('ico-pause',true); });
ws.on('pause',()=>{ show('ico-play',true);show('ico-pause',false); });
ws.on('error',e=>showErr('WaveSurfer: '+e.message));

function show(id,v){ document.getElementById(id).style.display=v?'':'none'; }

// ── Calibrate overlay to waveform position ────────────────────────────────────
function calibrate(){
  const outer  = document.getElementById('wave-outer');
  const wvEl   = document.getElementById('waveform');
  const outerR = outer.getBoundingClientRect();
  const wvR    = wvEl.getBoundingClientRect();
  waveLeft = wvR.left - outerR.left;
  waveW    = wvR.width;

  const ovl = document.getElementById('wave-overlay');
  ovl.style.left   = waveLeft+'px';
  ovl.style.top    = (wvR.top - outerR.top)+'px';
  ovl.style.width  = waveW+'px';
  ovl.style.height = wvR.height+'px';
}

// ── Overlay rendering ─────────────────────────────────────────────────────────
function updateOverlay(){
  if(!dur||!waveW) return;
  const sx = (startSec/dur)*waveW;
  const ex = (endSec/dur)*waveW;
  const HW = 8; // half handle width

  if(trimMode==='center'){
    // keep [start..end], shade left and right
    setEl('shade-left',  {left:0,       width:sx,         display:''});
    setEl('shade-right', {left:ex,      width:waveW-ex,   display:''});
    setEl('shade-center',{display:'none'});
    setEl('keep-region', {left:sx,      width:ex-sx,      display:''});
    setEl('h-left',      {left:sx-HW+1, display:''});
    setEl('h-right',     {left:ex-HW,   display:''});
  } else {
    // keep sides, remove [start..end]
    setEl('shade-left',  {display:'none'});
    setEl('shade-right', {display:'none'});
    setEl('shade-center',{left:sx, width:ex-sx, display:''});
    setEl('keep-region', {display:'none'});
    setEl('h-left',      {left:sx-HW+1, display:''});
    setEl('h-right',     {left:ex-HW,   display:''});
  }

  document.getElementById('hl-left').textContent  = fmt(startSec);
  document.getElementById('hl-right').textContent = fmt(endSec);
}

function setEl(id, props){
  const el=document.getElementById(id);
  if(props.display!==undefined) el.style.display=props.display;
  if(props.left!==undefined)    el.style.left=props.left+'px';
  if(props.width!==undefined)   el.style.width=props.width+'px';
  if(props.top!==undefined)     el.style.top=props.top+'px';
  if(props.height!==undefined)  el.style.height=props.height+'px';
}

function updateInfo(){
  document.getElementById('si-start').textContent = fmt(startSec);
  document.getElementById('si-end').textContent   = fmt(endSec);
  document.getElementById('si-dur').textContent   = fmt(endSec-startSec);
  document.getElementById('si-mode').textContent  = trimMode==='center'?'Keep Center':'Keep Sides';
}

// ── Drag handles ───────────────────────────────────────────────────────────────
function makeDraggable(handleId, which){
  const el = document.getElementById(handleId);
  let dragging = false;

  el.addEventListener('mousedown', e=>{ dragging=true; e.preventDefault(); });
  el.addEventListener('touchstart', e=>{ dragging=true; e.preventDefault(); },{passive:false});

  const move = e=>{
    if(!dragging||!waveW) return;
    const ovl   = document.getElementById('wave-overlay');
    const rect  = ovl.getBoundingClientRect();
    const cx    = (e.touches ? e.touches[0].clientX : e.clientX) - rect.left;
    const pct   = Math.max(0, Math.min(1, cx/waveW));
    const newT  = pct*dur;
    const MIN   = 0.5; // min 0.5s selection

    if(which==='left'){
      startSec = Math.min(newT, endSec-MIN);
    } else {
      endSec   = Math.max(newT, startSec+MIN);
    }
    updateOverlay(); updateInfo();
  };

  const up=()=>{ dragging=false; };
  document.addEventListener('mousemove', move);
  document.addEventListener('mouseup',   up);
  document.addEventListener('touchmove', move,{passive:false});
  document.addEventListener('touchend',  up);
}

makeDraggable('h-left',  'left');
makeDraggable('h-right', 'right');

// ── Trim mode ─────────────────────────────────────────────────────────────────
function setTrimMode(m){
  trimMode=m;
  document.getElementById('tab-center').classList.toggle('on', m==='center');
  document.getElementById('tab-sides').classList.toggle('on',  m==='sides');
  const desc = m==='center'
    ? 'Drag the left/right handles on the waveform. The highlighted region will be <strong>kept</strong>.'
    : 'Drag the handles to mark the <strong>region to remove</strong>. Both sides will be kept and joined.';
  document.getElementById('mode-desc-text').innerHTML = desc;
  updateOverlay(); updateInfo();
}

function resetHandles(){
  startSec=0; endSec=dur||0;
  updateOverlay(); updateInfo();
  const dl=document.getElementById('dl-btn'); if(dl) dl.remove();
}

// ── Seek by seconds ───────────────────────────────────────────────────────────
function seekBy(secs){
  if(!wsReady||!dur) return;
  const t = Math.max(0, Math.min(dur, ws.getCurrentTime() + secs));
  ws.seekTo(t / dur);
}

// ── Editable start/end inputs ─────────────────────────────────────────────────
document.getElementById('si-start').addEventListener('change', function(){
  const t = parseT(this.value);
  if(t !== null && t >= 0 && t < endSec - 0.5){
    startSec = t; updateOverlay(); updateInfo();
  } else { this.value = fmt(startSec); }
});
document.getElementById('si-end').addEventListener('change', function(){
  const t = parseT(this.value);
  if(t !== null && t > startSec + 0.5 && t <= dur){
    endSec = t; updateOverlay(); updateInfo();
  } else { this.value = fmt(endSec); }
});

function parseT(s){
  s=s.trim().replace(',','.');
  const p=s.split(':').map(Number);
  if(p.some(isNaN)) return null;
  if(p.length===3) return p[0]*3600+p[1]*60+p[2];
  if(p.length===2) return p[0]*60+p[1];
  return p[0]||0;
}
document.getElementById('btn-play').addEventListener('click',()=> wsReady&&ws.playPause());
document.getElementById('btn-prev').addEventListener('click',()=>{
  if(!wsReady) return;
  clearTimeout(prevTimer);
  ws.seekTo(startSec/dur); ws.play();
  prevTimer=setTimeout(()=>ws.pause(),(endSec-startSec)*1000);
});

// ── Cut & Download ────────────────────────────────────────────────────────────
document.getElementById('btn-cut').addEventListener('click', async ()=>{
  if(!wsReady) return;
  showProc(true);
  const body=new URLSearchParams({
    file:  AUDIO_FILE,
    mode:  trimMode,
    start: startSec.toFixed(3),
    end:   endSec.toFixed(3),
  });
  try{
    await animSteps();
    const res  = await fetch('trim_process.php',{method:'POST',body});
    const data = await res.json();
    showProc(false);
    if(data.success){
      const old=document.getElementById('dl-btn'); if(old) old.remove();
      const a=document.createElement('a');
      a.id='dl-btn'; a.className='btn btn-green';
      a.href=data.download_url; a.download=data.output_name;
      a.innerHTML='⬇ Download &nbsp;<small style="opacity:.7;font-size:.75em">'+data.size+'</small>';
      document.querySelector('.actions').prepend(a);
      a.click();
    } else { showErr(data.error||'Processing failed.'); }
  }catch(e){ showProc(false); showErr('Network error: '+e.message); }
});

// ── Helpers ───────────────────────────────────────────────────────────────────
function fmt(s){
  if(!isFinite(s)||s<0) s=0;
  const m=Math.floor(s/60); const sec=(s%60).toFixed(1);
  return m+':'+String(sec).padStart(4,'0');
}
function buildRuler(){
  const spans=document.querySelectorAll('#ruler span');
  spans.forEach((sp,i)=>{ sp.textContent=fmt((i/(spans.length-1))*dur); });
}
function showErr(msg){
  document.getElementById('err-msg').textContent=msg;
  document.getElementById('err').classList.add('on');
  setTimeout(()=>document.getElementById('err').classList.remove('on'),6000);
}
function showProc(on){
  document.getElementById('proc').classList.toggle('on',on);
  if(on){
    ['ps1','ps2','ps3'].forEach(id=>{ const e=document.getElementById(id); e.classList.remove('on','done'); });
    document.getElementById('ps1').classList.add('on');
    document.getElementById('proc-fill').style.width='0%';
  }
}
async function animSteps(){
  const steps=['ps1','ps2','ps3']; const pcts=[30,65,95];
  for(let i=0;i<steps.length;i++){
    await new Promise(r=>setTimeout(r,500));
    document.getElementById(steps[i]).classList.remove('on');
    document.getElementById(steps[i]).classList.add('done');
    document.getElementById('proc-fill').style.width=pcts[i]+'%';
    if(i+1<steps.length) document.getElementById(steps[i+1]).classList.add('on');
  }
}
</script>
</body>
</html>