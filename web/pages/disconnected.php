<!DOCTYPE html>
<html lang="az">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Bağlantı kəsildi | alt404</title>
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
  <link href="https://fonts.googleapis.com/css2?family=Tomorrow:wght@400;500;600;700&display=swap" rel="stylesheet">
  <style>
    :root {
      --bg-dark: #08080d;
      --bg-card: rgba(15, 15, 25, 0.65);
      --border-glow: rgba(0, 240, 255, 0.15);
      --neon-cyan: #00f0ff;
      --neon-pink: #ff007f;
      --text-main: #f0f4f8;
      --text-muted: #8a9cae;
      --font-family: 'Tomorrow', sans-serif;
    }

    * {
      box-sizing: border-box;
      margin: 0;
      padding: 0;
    }

    body {
      background-color: var(--bg-dark);
      color: var(--text-main);
      font-family: var(--font-family);
      min-height: 100vh;
      display: flex;
      align-items: center;
      justify-content: center;
      overflow: hidden;
      position: relative;
    }

    /* Grid overlay */
    body::before {
      content: '';
      position: absolute;
      top: 0;
      left: 0;
      right: 0;
      bottom: 0;
      background-image: 
        linear-gradient(rgba(0, 240, 255, 0.03) 1px, transparent 1px),
        linear-gradient(90deg, rgba(0, 240, 255, 0.03) 1px, transparent 1px);
      background-size: 40px 40px;
      z-index: 1;
      pointer-events: none;
    }

    /* Subtle ambient glows */
    .glow-blob {
      position: absolute;
      width: 400px;
      height: 400px;
      border-radius: 50%;
      background: radial-gradient(circle, rgba(0, 240, 255, 0.08) 0%, transparent 70%);
      z-index: 0;
      filter: blur(40px);
      pointer-events: none;
    }

    .glow-left {
      top: 10%;
      left: -10%;
      background: radial-gradient(circle, rgba(255, 0, 127, 0.06) 0%, transparent 70%);
    }

    .glow-right {
      bottom: 10%;
      right: -10%;
    }

    /* Main Card Container */
    .card-container {
      position: relative;
      z-index: 2;
      width: 90%;
      max-width: 540px;
      background: var(--bg-card);
      backdrop-filter: blur(20px);
      -webkit-backdrop-filter: blur(20px);
      border: 1px solid var(--border-glow);
      border-radius: 20px;
      padding: 48px 32px;
      text-align: center;
      box-shadow: 
        0 20px 50px rgba(0, 0, 0, 0.5),
        inset 0 1px 0 rgba(255, 255, 255, 0.05),
        0 0 40px rgba(0, 240, 255, 0.03);
      animation: cardEntrance 0.8s cubic-bezier(0.16, 1, 0.3, 1) forwards;
    }

    @keyframes cardEntrance {
      from {
        opacity: 0;
        transform: translateY(20px) scale(0.98);
      }
      to {
        opacity: 1;
        transform: translateY(0) scale(1);
      }
    }

    /* Connection visualization */
    .visualizer {
      display: flex;
      justify-content: center;
      align-items: center;
      gap: 16px;
      margin-bottom: 32px;
    }

    .node {
      width: 48px;
      height: 48px;
      border-radius: 12px;
      display: flex;
      align-items: center;
      justify-content: center;
      background: rgba(255, 255, 255, 0.03);
      border: 1px solid rgba(255, 255, 255, 0.08);
      color: var(--text-muted);
      position: relative;
      transition: all 0.3s ease;
    }

    .node-server {
      color: var(--neon-cyan);
      border-color: rgba(0, 240, 255, 0.3);
      box-shadow: 0 0 15px rgba(0, 240, 255, 0.1);
    }

    .node-db {
      color: var(--neon-pink);
      border-color: rgba(255, 0, 127, 0.2);
      animation: pulseDbError 2s infinite ease-in-out;
    }

    @keyframes pulseDbError {
      0%, 100% {
        box-shadow: 0 0 5px rgba(255, 0, 127, 0.1);
        border-color: rgba(255, 0, 127, 0.2);
      }
      50% {
        box-shadow: 0 0 25px rgba(255, 0, 127, 0.35);
        border-color: rgba(255, 0, 127, 0.6);
      }
    }

    /* Link line between nodes */
    .link-line {
      width: 60px;
      height: 2px;
      background: rgba(255, 255, 255, 0.08);
      position: relative;
      overflow: hidden;
    }

    .link-line::after {
      content: '';
      position: absolute;
      top: 0;
      left: 0;
      height: 100%;
      width: 15px;
      background: linear-gradient(90deg, transparent, var(--neon-pink), transparent);
      animation: signalFail 2s infinite linear;
    }

    @keyframes signalFail {
      0% { left: 0%; opacity: 1; }
      40% { left: 45%; opacity: 0.3; }
      50% { left: 50%; opacity: 0; }
      100% { left: 100%; opacity: 0; }
    }

    /* Typography */
    .error-code {
      font-size: 0.75rem;
      text-transform: uppercase;
      letter-spacing: 4px;
      color: var(--neon-pink);
      margin-bottom: 12px;
      font-weight: 700;
      text-shadow: 0 0 8px rgba(255, 0, 127, 0.4);
    }

    h1 {
      font-size: 1.8rem;
      font-weight: 700;
      line-height: 1.3;
      margin-bottom: 16px;
      background: linear-gradient(135deg, #fff 0%, #a5b4fc 100%);
      -webkit-background-clip: text;
      -webkit-text-fill-color: transparent;
    }

    p {
      color: var(--text-muted);
      font-size: 0.95rem;
      line-height: 1.6;
      margin-bottom: 32px;
      padding: 0 10px;
    }

    /* Interactive Button */
    .btn-retry {
      display: inline-flex;
      align-items: center;
      gap: 10px;
      background: transparent;
      border: 1px solid var(--neon-cyan);
      color: var(--neon-cyan);
      font-family: var(--font-family);
      font-size: 0.9rem;
      font-weight: 600;
      padding: 14px 28px;
      border-radius: 12px;
      cursor: pointer;
      text-decoration: none;
      transition: all 0.3s cubic-bezier(0.16, 1, 0.3, 1);
      box-shadow: 0 0 15px rgba(0, 240, 255, 0.05);
      position: relative;
      overflow: hidden;
    }

    .btn-retry::before {
      content: '';
      position: absolute;
      top: 0;
      left: -100%;
      width: 100%;
      height: 100%;
      background: linear-gradient(90deg, transparent, rgba(0, 240, 255, 0.15), transparent);
      transition: 0.5s;
    }

    .btn-retry:hover::before {
      left: 100%;
    }

    .btn-retry:hover {
      background: rgba(0, 240, 255, 0.08);
      box-shadow: 0 0 25px rgba(0, 240, 255, 0.2);
      transform: translateY(-2px);
    }

    .btn-retry:active {
      transform: translateY(0);
    }

    /* Reload SVG animation */
    .icon-spin {
      animation: none;
    }
    
    .loading .icon-spin {
      animation: spin 1s infinite linear;
      transform-origin: center;
    }

    @keyframes spin {
      100% { transform: rotate(360deg); }
    }

    /* Timer indicator */
    .auto-retry {
      margin-top: 24px;
      font-size: 0.8rem;
      color: var(--text-muted);
      opacity: 0.8;
    }

    .countdown {
      color: var(--neon-cyan);
      font-weight: 600;
    }
  </style>
</head>
<body>

  <div class="glow-blob glow-left"></div>
  <div class="glow-blob glow-right"></div>

  <div class="card-container">
    <div class="error-code">Alt404 // Sistem Xətası</div>
    
    <div class="visualizer">
      <!-- Web Server -->
      <div class="node node-server" title="Veb Server">
        <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
          <rect x="2" y="2" width="20" height="8" rx="2" ry="2"/>
          <rect x="2" y="14" width="20" height="8" rx="2" ry="2"/>
          <line x1="6" y1="6" x2="6.01" y2="6"/>
          <line x1="6" y1="18" x2="6.01" y2="18"/>
        </svg>
      </div>
      
      <!-- Broken connection -->
      <div class="link-line"></div>
      
      <!-- Database -->
      <div class="node node-db" title="Məlumat Bazası">
        <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
          <ellipse cx="12" cy="5" rx="9" ry="3"/>
          <path d="M3 5V19A9 3 0 0 0 21 19V5"/>
          <path d="M3 12A9 3 0 0 0 21 12"/>
        </svg>
      </div>
    </div>

    <h1>Bağlantı kəsildi</h1>
    <p>Hazırda serverə bağlanmaq mümkün deyil. Bu texniki bir nasazlıq ola bilər. Komandamız problemi həll etmək üzərində işləyir.</p>
    
    <button class="btn-retry" id="retry-btn">
      <svg class="icon-spin" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
        <path d="M21.5 2v6h-6M21.34 15.57a10 10 0 1 1-.57-8.38l5.67-5.67"/>
      </svg>
      <span>Yenidən yoxla</span>
    </button>

    <div class="auto-retry">
      Səhifə <span class="countdown" id="timer">30</span> saniyə sonra avtomatik yenilənəcək.
    </div>
  </div>

  <script>
    const retryBtn = document.getElementById('retry-btn');
    const timerSpan = document.getElementById('timer');
    let timeLeft = 30;

    function triggerRetry() {
      retryBtn.classList.add('loading');
      retryBtn.disabled = true;
      setTimeout(() => {
        window.location.reload();
      }, 800);
    }

    retryBtn.addEventListener('click', triggerRetry);

    const countdown = setInterval(() => {
      timeLeft--;
      timerSpan.textContent = timeLeft;
      if (timeLeft <= 0) {
        clearInterval(countdown);
        triggerRetry();
      }
    }, 1000);
  </script>
</body>
</html>
