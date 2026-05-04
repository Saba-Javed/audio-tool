<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8"/>
  <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
  <title>Audio Tool</title>
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link href="https://fonts.googleapis.com/css2?family=Syne:wght@400;600;700;800&family=DM+Sans:wght@300;400;500&display=swap" rel="stylesheet">
  <style>
    *,*::before,*::after{box-sizing:border-box;margin:0;padding:0}
    :root{
      --bg:#07090f;
      --bg2:#0d1117;
      --surface:#111827;
      --surface2:#1a2235;
      --border:#1e2d45;
      --border2:#2a3f5f;
      --blue:#2a7ae8;
      --blue2:#4f9eff;
      --blue-glow:rgba(42,122,232,0.35);
      --cyan:#00c8ff;
      --text:#e8f0ff;
      --muted:#5a7a9a;
      --dim:#2a3f5f;
      --green:#22c55e;
      --radius:14px;
    }
    html,body{min-height:100%;background:var(--bg);color:var(--text);font-family:'DM Sans',sans-serif}

    body::before{
      content:'';position:fixed;inset:0;pointer-events:none;z-index:0;
      background:
        radial-gradient(ellipse 80% 60% at 50% -10%, rgba(42,122,232,0.18) 0%, transparent 60%),
        radial-gradient(ellipse 40% 40% at 80% 80%, rgba(0,200,255,0.06) 0%, transparent 50%);
    }

    /* NAV */
    nav{
      position:sticky;top:0;z-index:100;
      background:rgba(7,9,15,0.85);backdrop-filter:blur(20px);-webkit-backdrop-filter:blur(20px);
      border-bottom:1px solid var(--border);
      padding:0 3rem;height:62px;
      display:flex;align-items:center;justify-content:space-between;
    }
    .logo{
      font-family:'Syne',sans-serif;font-weight:800;font-size:1.2rem;letter-spacing:-0.03em;
      color:var(--blue2);text-decoration:none;display:flex;align-items:center;gap:.5rem;
    }
    .logo-dot{width:8px;height:8px;border-radius:50%;background:var(--cyan);animation:pulse 2s ease-in-out infinite}
    @keyframes pulse{0%,100%{box-shadow:0 0 0 0 rgba(0,200,255,.5)}50%{box-shadow:0 0 0 6px rgba(0,200,255,0)}}

    .nav-links{display:flex;gap:.25rem}
    .nav-link{
      font-size:.85rem;font-weight:500;color:var(--muted);text-decoration:none;
      padding:.45rem .9rem;border-radius:100px;transition:all .18s;
    }
    .nav-link:hover{background:var(--surface);color:var(--text)}
    .nav-link.active{background:var(--blue);color:#fff}

    /* HERO */
    .hero{
      position:relative;z-index:1;
      display:flex;flex-direction:column;align-items:center;justify-content:center;text-align:center;
      min-height:calc(100vh - 62px);padding:4rem 2rem 6rem;
    }
    .badge{
      display:inline-flex;align-items:center;gap:.5rem;
      border:1px solid var(--border2);border-radius:100px;padding:.35rem 1rem;
      font-size:.75rem;font-weight:600;letter-spacing:.08em;text-transform:uppercase;
      color:var(--blue2);margin-bottom:2rem;
      background:rgba(42,122,232,.07);
      animation:fadeUp .5s ease both;
    }
    .badge-dot{width:6px;height:6px;border-radius:50%;background:var(--cyan);animation:pulse 2s infinite}

    h1{
      font-family:'Syne',sans-serif;font-size:clamp(3rem,8vw,6rem);
      font-weight:800;line-height:.93;letter-spacing:-.05em;margin-bottom:1.4rem;
      animation:fadeUp .5s ease .08s both;
    }
    h1 .hl{
      background:linear-gradient(135deg,var(--blue2),var(--cyan));
      -webkit-background-clip:text;-webkit-text-fill-color:transparent;background-clip:text;
    }
    .sub{
      font-size:1.05rem;font-weight:300;color:var(--muted);line-height:1.7;
      max-width:480px;margin-bottom:3.5rem;
      animation:fadeUp .5s ease .15s both;
    }

    /* Upload */
    .upload-zone{animation:fadeUp .5s ease .22s both}
    #file-input{display:none}
    .choose-btn{
      display:inline-flex;align-items:center;gap:.75rem;
      background:var(--blue);color:#fff;
      font-family:'Syne',sans-serif;font-weight:700;font-size:1rem;
      padding:1rem 2.5rem;border-radius:100px;border:none;cursor:pointer;
      box-shadow:0 0 40px var(--blue-glow);
      transition:transform .2s,box-shadow .2s;
    }
    .choose-btn:hover{transform:translateY(-2px);box-shadow:0 0 60px rgba(42,122,232,.55)}
    .choose-btn svg{width:20px;height:20px;flex-shrink:0}
    .upload-hint{margin-top:.8rem;font-size:.78rem;color:var(--muted)}

    /* Feature cards */
    .feat-row{
      display:grid;grid-template-columns:1fr 1fr;gap:1rem;
      max-width:520px;width:100%;margin-top:4.5rem;
      animation:fadeUp .5s ease .32s both;
    }
    .feat{
      background:var(--surface);border:1px solid var(--border);border-radius:var(--radius);
      padding:1.4rem;text-align:left;cursor:pointer;text-decoration:none;
      transition:border-color .2s,transform .2s;
    }
    .feat:hover{border-color:var(--blue);transform:translateY(-2px)}
    .feat-ico{font-size:1.4rem;margin-bottom:.7rem}
    .feat-name{font-family:'Syne',sans-serif;font-weight:700;font-size:.92rem;margin-bottom:.3rem;color:var(--text)}
    .feat-desc{font-size:.78rem;color:var(--muted);line-height:1.5}

    /* Upload overlay */
    .ovl{
      display:none;position:fixed;inset:0;
      background:rgba(7,9,15,.93);backdrop-filter:blur(20px);
      z-index:500;flex-direction:column;align-items:center;justify-content:center;gap:1.8rem;text-align:center;
    }
    .ovl.on{display:flex}
    .spinner{width:56px;height:56px;border-radius:50%;border:3px solid var(--border2);border-top-color:var(--blue);animation:spin .9s linear infinite}
    @keyframes spin{to{transform:rotate(360deg)}}
    .ovl-title{font-family:'Syne',sans-serif;font-size:1.4rem;font-weight:800;letter-spacing:-.03em}
    .ovl-file{font-size:.83rem;color:var(--muted);max-width:280px;overflow:hidden;text-overflow:ellipsis;white-space:nowrap}
    .prog-track{width:260px;height:3px;background:var(--border2);border-radius:100px;overflow:hidden}
    .prog-fill{height:100%;width:0%;background:linear-gradient(90deg,var(--blue),var(--cyan));border-radius:100px;transition:width .25s ease}

    @keyframes fadeUp{from{opacity:0;transform:translateY(16px)}to{opacity:1;transform:translateY(0)}}
    @media(max-width:600px){nav{padding:0 1rem}.feat-row{grid-template-columns:1fr}h1{font-size:2.8rem}}
  </style>
</head>
<body>

<nav>
  <a href="index.php" class="logo"><span class="logo-dot"></span>Audio Tool</a>
  <div class="nav-links">
    <a href="index.php" class="nav-link active">Home</a>
    <a href="index.php?goto=trim" class="nav-link">✂️ Trim Audio</a>
    <a href="index.php?goto=compress" class="nav-link">📦 Compress Audio</a>
  </div>
</nav>

<section class="hero">
  <div class="badge"><span class="badge-dot"></span>Free · No Signup · Local</div>
  <h1>Audio Tool<br><span class="hl">Trim & Compress</span></h1>
  <p class="sub">Upload an audio file to trim it precisely or compress it to a smaller size.</p>

  <div class="upload-zone">
    <input type="file" id="file-input" accept="audio/*">
    <button class="choose-btn" onclick="document.getElementById('file-input').click()">
      <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
        <path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"/>
        <polyline points="17 8 12 3 7 8"/><line x1="12" y1="3" x2="12" y2="15"/>
      </svg>
      Choose File
    </button>
    <p class="upload-hint">MP3, WAV, OGG, M4A, FLAC — up to 100MB</p>
  </div>

  <div class="feat-row">
    <a class="feat" href="index.php?goto=trim">
      <div class="feat-ico">✂️</div>
      <div class="feat-name">Trim Audio</div>
      <div class="feat-desc">Drag handles directly on the waveform. Keep center or keep sides.</div>
    </a>
    <a class="feat" href="index.php?goto=compress">
      <div class="feat-ico">📦</div>
      <div class="feat-name">Compress Audio</div>
      <div class="feat-desc">Reduce file size with High / Medium / Low quality presets.</div>
    </a>
  </div>
</section>

<div class="ovl" id="ovl">
  <div class="spinner"></div>
  <div>
    <div class="ovl-title">Uploading…</div>
    <div class="ovl-file" id="ovl-file"></div>
  </div>
  <div class="prog-track"><div class="prog-fill" id="prog-fill"></div></div>
</div>

<script>
  const goto = new URLSearchParams(location.search).get('goto') || 'trim';
  const inp  = document.getElementById('file-input');

  inp.addEventListener('change', function(){
    const f = this.files[0]; if(!f) return;
    document.getElementById('ovl-file').textContent = f.name;
    document.getElementById('ovl').classList.add('on');
    const fd  = new FormData(); fd.append('audio', f);
    const xhr = new XMLHttpRequest();
    xhr.upload.addEventListener('progress', e=>{
      if(e.lengthComputable) document.getElementById('prog-fill').style.width = Math.round(e.loaded/e.total*80)+'%';
    });
    xhr.addEventListener('load', ()=>{
      document.getElementById('prog-fill').style.width='100%';
      if(xhr.status===200){
        try{
          const r=JSON.parse(xhr.responseText);
          if(r.success){
            setTimeout(()=>{ location.href=goto+'.php?file='+encodeURIComponent(r.filename)+'&original='+encodeURIComponent(r.original_name); },350);
          } else { alert('Upload failed: '+(r.error||'Unknown')); document.getElementById('ovl').classList.remove('on'); }
        }catch{ alert('Server error.'); document.getElementById('ovl').classList.remove('on'); }
      }
    });
    xhr.addEventListener('error',()=>{ alert('Network error.'); document.getElementById('ovl').classList.remove('on'); });
    xhr.open('POST','upload.php'); xhr.send(fd);
  });

  // If user clicked a feature card or nav link, store destination
  document.querySelectorAll('.feat, .nav-link').forEach(el=>{
    el.addEventListener('click', e=>{
      const href = el.getAttribute('href')||'';
      const g = new URLSearchParams(href.split('?')[1]||'').get('goto');
      if(g){ e.preventDefault(); location.href='index.php?goto='+g; }
    });
  });
</script>
</body>
</html>