<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8"/>
  <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
  <title>Compress Audio</title>
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link href="https://fonts.googleapis.com/css2?family=Syne:wght@400;600;700;800&family=DM+Sans:wght@300;400;500&family=DM+Mono:wght@400;500&display=swap" rel="stylesheet">
  <style>
    *,*::before,*::after{box-sizing:border-box;margin:0;padding:0}
    :root{
      --bg:#07090f;--bg2:#0d1117;--surface:#111827;--surface2:#1a2235;
      --border:#1e2d45;--border2:#2a3f5f;
      --blue:#2a7ae8;--blue2:#4f9eff;--blue-glow:rgba(42,122,232,.35);
      --cyan:#00c8ff;--text:#e8f0ff;--muted:#5a7a9a;--dim:#2a3f5f;
      --green:#22c55e;--radius:14px;
    }
    html,body{min-height:100%;background:var(--bg);color:var(--text);font-family:'DM Sans',sans-serif}
    body::before{
      content:'';position:fixed;inset:0;pointer-events:none;z-index:0;
      background:radial-gradient(ellipse 80% 50% at 50% -5%,rgba(42,122,232,.15) 0%,transparent 60%);
    }

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

    .page{position:relative;z-index:1;max-width:720px;margin:0 auto;padding:2.5rem 1.5rem 5rem;display:flex;flex-direction:column;gap:1.4rem}

    .card{background:var(--surface);border:1px solid var(--border);border-radius:var(--radius);overflow:hidden}
    .card-head{padding:1rem 1.6rem;border-bottom:1px solid var(--border);display:flex;align-items:center;justify-content:space-between}
    .card-title{font-family:'Syne',sans-serif;font-weight:700;font-size:.9rem;display:flex;align-items:center;gap:.5rem}
    .cdot{width:7px;height:7px;border-radius:50%;background:var(--cyan)}

    /* File info */
    .file-info{
      display:flex;align-items:center;gap:1.2rem;
      padding:1.4rem 1.6rem;border-bottom:1px solid var(--border);
    }
    .file-icon{
      width:48px;height:48px;border-radius:12px;
      background:rgba(42,122,232,.12);border:1px solid var(--border2);
      display:flex;align-items:center;justify-content:center;font-size:1.3rem;flex-shrink:0;
    }
    .file-details{flex:1;min-width:0}
    .file-name{font-weight:600;font-size:.92rem;overflow:hidden;text-overflow:ellipsis;white-space:nowrap;margin-bottom:.2rem}
    .file-meta{font-size:.78rem;color:var(--muted)}

    /* Audio player */
    .player-section{padding:1.4rem 1.6rem;border-bottom:1px solid var(--border)}
    .player-label{font-size:.72rem;font-weight:700;letter-spacing:.08em;text-transform:uppercase;color:var(--muted);margin-bottom:.8rem}
    audio{
      width:100%;height:40px;border-radius:8px;
      accent-color:var(--blue);background:var(--surface2);
    }
    audio::-webkit-media-controls-panel{background:var(--surface2)}

    /* Quality section */
    .quality-section{padding:1.4rem 1.6rem;border-bottom:1px solid var(--border)}
    .q-label{font-size:.72rem;font-weight:700;letter-spacing:.08em;text-transform:uppercase;color:var(--muted);margin-bottom:1rem;display:block}

    .qcards{display:flex;gap:.75rem;flex-wrap:wrap}
    .qcard{
      flex:1;min-width:140px;cursor:pointer;
      background:var(--surface2);border:2px solid var(--border);border-radius:var(--radius);
      padding:1.1rem;transition:all .2s;
    }
    .qcard:hover{border-color:var(--border2)}
    .qcard.sel{border-color:var(--blue);background:rgba(42,122,232,.07)}

    .qcard-top{display:flex;align-items:center;justify-content:space-between;margin-bottom:.6rem}
    .qcard-name{font-family:'Syne',sans-serif;font-weight:700;font-size:.88rem}
    .qcard-check{
      width:16px;height:16px;border-radius:50%;
      border:2px solid var(--border2);flex-shrink:0;
      position:relative;transition:all .15s;
    }
    .qcard.sel .qcard-check{border-color:var(--blue);background:var(--blue)}
    .qcard.sel .qcard-check::after{
      content:'';position:absolute;top:50%;left:50%;
      transform:translate(-50%,-52%) rotate(45deg);
      width:4px;height:6px;
      border-right:2px solid #fff;border-bottom:2px solid #fff;
    }
    .qcard-bitrate{font-family:'DM Mono',monospace;font-size:.82rem;color:var(--blue2);margin-bottom:.25rem;font-weight:500}
    .qcard-desc{font-size:.72rem;color:var(--muted);line-height:1.4}

    /* Size comparison */
    .size-compare{
      margin:1.2rem 0 0;
      display:flex;align-items:center;gap:.8rem;
      background:var(--surface2);border:1px solid var(--border);border-radius:10px;
      padding:.9rem 1.1rem;flex-wrap:wrap;
    }
    .sz-block{display:flex;flex-direction:column;gap:.2rem}
    .sz-lbl{font-size:.65rem;font-weight:700;text-transform:uppercase;letter-spacing:.07em;color:var(--muted)}
    .sz-val{font-family:'DM Mono',monospace;font-size:.95rem;font-weight:500}
    .sz-arrow{font-size:1.1rem;color:var(--dim)}
    .sz-saving{font-size:.78rem;font-weight:700;color:var(--green);margin-left:.3rem}

    /* Actions */
    .actions{padding:1.2rem 1.6rem;display:flex;gap:.75rem;flex-wrap:wrap}
    .btn{
      flex:1;min-width:140px;
      display:inline-flex;align-items:center;justify-content:center;gap:.5rem;
      font-family:'Syne',sans-serif;font-weight:700;font-size:.9rem;
      padding:.85rem 1.4rem;border-radius:100px;border:none;cursor:pointer;
      transition:transform .18s,box-shadow .18s,opacity .2s;text-decoration:none;
    }
    .btn:disabled{opacity:.35;cursor:not-allowed;pointer-events:none}
    .btn:hover:not(:disabled){transform:translateY(-2px)}
    .btn-blue{background:var(--blue);color:#fff;box-shadow:0 3px 20px var(--blue-glow)}
    .btn-blue:hover:not(:disabled){box-shadow:0 4px 30px rgba(42,122,232,.6)}
    .btn-ghost{background:var(--surface2);color:var(--text);border:1px solid var(--border)}
    .btn-green{background:var(--green);color:#fff;box-shadow:0 3px 18px rgba(34,197,94,.25);animation:popIn .35s cubic-bezier(.34,1.56,.64,1) both}
    @keyframes popIn{from{opacity:0;transform:scale(.8)}to{opacity:1;transform:scale(1)}}

    .err{display:none;padding:.8rem 1.2rem;background:rgba(239,68,68,.1);border:1px solid rgba(239,68,68,.3);border-radius:8px;color:#f87171;font-size:.85rem;align-items:center;gap:.5rem}
    .err.on{display:flex}

    /* Processing overlay */
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

    @media(max-width:600px){nav{padding:0 1rem}.page{padding:1.2rem .8rem 4rem}.nav-file{display:none}.qcards{flex-direction:column}}
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
$safeFile  = htmlspecialchars($filename,ENT_QUOTES,'UTF-8');
$safeOrig  = htmlspecialchars($originalName,ENT_QUOTES,'UTF-8');
$safeAudio = htmlspecialchars($audioUrl,ENT_QUOTES,'UTF-8');
?>
<nav>
  <a href="index.php" class="logo">✦ Audio Tool</a>
  <div class="nav-r">
    <span class="nav-file">📦 <?= $safeOrig ?></span>
    <a href="index.php" class="nav-back">← New File</a>
  </div>
</nav>

<div class="page">

  <div class="err" id="err"><svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="10"/><line x1="12" y1="8" x2="12" y2="12"/><line x1="12" y1="16" x2="12.01" y2="16"/></svg><span id="err-msg"></span></div>

  <div class="card">
    <div class="card-head">
      <div class="card-title"><div class="cdot"></div>Audio Compressor</div>
      <span style="font-size:.78rem;color:var(--muted)">Select quality → Compress</span>
    </div>

    <!-- File info -->
    <div class="file-info">
      <div class="file-icon">🎵</div>
      <div class="file-details">
        <div class="file-name"><?= $safeOrig ?></div>
        <div class="file-meta">Original size: <strong style="color:var(--text)"><?= fmtSz($fileSize) ?></strong></div>
      </div>
    </div>

    <!-- Preview player -->
    <div class="player-section">
      <div class="player-label">Preview</div>
      <audio controls src="<?= $safeAudio ?>"></audio>
    </div>

    <!-- Quality -->
    <div class="quality-section">
      <span class="q-label">Select Compression Quality</span>
      <div class="qcards">
        <div class="qcard" data-bitrate="192k">
          <div class="qcard-top">
            <span class="qcard-name">High</span>
            <div class="qcard-check"></div>
          </div>
          <div class="qcard-bitrate">192 kbps</div>
          <div class="qcard-desc">Near-lossless quality. Best for music.</div>
        </div>
        <div class="qcard sel" data-bitrate="128k">
          <div class="qcard-top">
            <span class="qcard-name">Medium</span>
            <div class="qcard-check"></div>
          </div>
          <div class="qcard-bitrate">128 kbps</div>
          <div class="qcard-desc">Balanced quality &amp; size. Recommended.</div>
        </div>
        <div class="qcard" data-bitrate="64k">
          <div class="qcard-top">
            <span class="qcard-name">Low</span>
            <div class="qcard-check"></div>
          </div>
          <div class="qcard-bitrate">64 kbps</div>
          <div class="qcard-desc">Smallest file. Good for voice/speech.</div>
        </div>
      </div>

      <!-- Size estimate -->
      <div class="size-compare" id="size-compare">
        <div class="sz-block">
          <div class="sz-lbl">Original</div>
          <div class="sz-val"><?= fmtSz($fileSize) ?></div>
        </div>
        <div class="sz-arrow">→</div>
        <div class="sz-block">
          <div class="sz-lbl">Estimated</div>
          <div class="sz-val" id="est-size">—</div>
        </div>
        <div class="sz-saving" id="saving-pct"></div>
      </div>
    </div>

    <!-- Actions -->
    <div class="actions">
      <button class="btn btn-blue" id="btn-compress">
        📦 Compress &amp; Download
      </button>
      <a href="trim.php?file=<?= urlencode($filename) ?>&original=<?= urlencode($originalName) ?>" class="btn btn-ghost">
        ✂️ Switch to Trim
      </a>
    </div>
  </div>

</div>

<!-- Processing overlay -->
<div class="proc" id="proc">
  <div class="proc-ico">📦</div>
  <div class="proc-title">Compressing Audio…</div>
  <div class="proc-steps">
    <div class="pstep on" id="ps1"><div class="pstep-dot"></div>Sending parameters</div>
    <div class="pstep" id="ps2"><div class="pstep-dot"></div>Running FFmpeg</div>
    <div class="pstep" id="ps3"><div class="pstep-dot"></div>Preparing download</div>
  </div>
  <div class="proc-track"><div class="proc-fill" id="proc-fill"></div></div>
</div>

<script>
const AUDIO_FILE  = <?= json_encode($safeFile) ?>;
const ORIG_NAME   = <?= json_encode($safeOrig) ?>;
const ORIG_BYTES  = <?= (int)$fileSize ?>;

let curBitrate = '128k';

// Quality card selection
document.querySelectorAll('.qcard').forEach(c=>{
  c.addEventListener('click',()=>{
    document.querySelectorAll('.qcard').forEach(x=>x.classList.remove('sel'));
    c.classList.add('sel');
    curBitrate = c.dataset.bitrate;
    updateEstimate();
  });
});

function updateEstimate(){
  const bpsMap={'192k':192000,'128k':128000,'64k':64000};
  const bps  = bpsMap[curBitrate]||128000;
  // rough estimate: bitrate * duration; we don't know duration here so estimate from original size
  // For a rough estimate we use: (bitrate_new / bitrate_original) * original_size
  // bitrate_original approx from filesize — assume ~1 min avg if unknown, just ratio
  const ratio = bps / 192000; // relative to 192k (high quality ref)
  const est   = Math.round(ORIG_BYTES * ratio * 0.85); // 0.85 overhead factor
  const saving= Math.max(0,Math.round((1-est/ORIG_BYTES)*100));
  document.getElementById('est-size').textContent  = fmtBytes(est);
  document.getElementById('saving-pct').textContent= saving>0?'~'+saving+'% smaller':'';
}

function fmtBytes(b){
  if(b<1024)    return b+' B';
  if(b<1048576) return Math.round(b/1024)+' KB';
  return (b/1048576).toFixed(2)+' MB';
}

updateEstimate();

// Compress
document.getElementById('btn-compress').addEventListener('click', async ()=>{
  showProc(true);
  const body=new URLSearchParams({ file:AUDIO_FILE, bitrate:curBitrate });
  try{
    await animSteps();
    const res  = await fetch('compress_process.php',{method:'POST',body});
    const data = await res.json();
    showProc(false);
    if(data.success){
      const old=document.getElementById('dl-btn'); if(old) old.remove();
      const a=document.createElement('a');
      a.id='dl-btn'; a.className='btn btn-green';
      a.href=data.download_url; a.download=data.output_name;
      const saved = data.output_size_bytes ? Math.max(0,Math.round((1-data.output_size_bytes/ORIG_BYTES)*100)) : 0;
      const label = saved>0 ? data.output_size+' (−'+saved+'%)' : data.output_size;
      a.innerHTML='⬇ Download &nbsp;<small style="opacity:.7;font-size:.75em">'+label+'</small>';
      document.querySelector('.actions').prepend(a);
      a.click();
    } else { showErr(data.error||'Compression failed.'); }
  }catch(e){ showProc(false); showErr('Network error: '+e.message); }
});

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
