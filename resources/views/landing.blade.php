<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<title>FrameQuote by Codevora | Window & Door Quotation Software for uPVC and Aluminium</title>
<meta name="description" content="FrameQuote by Codevora provides cloud-based estimation, CAD elevations, factory BOM, and instant quotation generation for uPVC and Aluminium windows & doors.">
<link rel="icon" type="image/png" href="{{ asset('images/framequote-logo.png') }}">
<meta name="theme-color" content="#0a66c2">

<!-- Google Fonts -->
<link rel="preconnect" href="https://fonts.googleapis.com">
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
<link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
<!-- Bootstrap Icons -->
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">

<style>
  :root {
    --eva-blue: #0a66c2;
    --eva-blue-hover: #08529c;
    --eva-blue-light: #e8f3fc;
    --eva-blue-glow: rgba(10, 102, 194, 0.2);
    --eva-dark: #071529;
    --eva-navy: #0d213f;
    --eva-accent: #00b4d8;
    --eva-green: #10b981;
    --ink-dark: #0f172a;
    --ink-body: #334155;
    --ink-muted: #64748b;
    --bg-light: #f8fafc;
    --bg-white: #ffffff;
    --border-light: #e2e8f0;
    --border-soft: #edf2f7;
    --shadow-sm: 0 2px 8px rgba(7, 21, 41, 0.04);
    --shadow-md: 0 10px 30px -10px rgba(10, 102, 194, 0.15);
    --shadow-lg: 0 24px 50px -15px rgba(7, 21, 41, 0.12);
  }

  * { box-sizing: border-box; margin: 0; padding: 0; }
  html { scroll-behavior: smooth; }
  body {
    font-family: 'Plus Jakarta Sans', 'Inter', -apple-system, BlinkMacSystemFont, sans-serif;
    color: var(--ink-body);
    background-color: var(--bg-white);
    line-height: 1.6;
    -webkit-font-smoothing: antialiased;
  }
  a { text-decoration: none; color: inherit; }
  img { max-width: 100%; display: block; }

  .container {
    width: min(1200px, 92%);
    margin: 0 auto;
  }

  /* Top Alert Banner */
  .top-strip {
    background: linear-gradient(90deg, #071529 0%, #0a66c2 100%);
    color: #ffffff;
    font-size: 13px;
    padding: 8px 0;
    text-align: center;
    font-weight: 500;
  }
  .top-strip a {
    color: #67e8f9;
    font-weight: 700;
    margin-left: 6px;
    text-decoration: underline;
  }

  /* Header & Navigation */
  header.site-header {
    position: sticky;
    top: 0;
    z-index: 100;
    background: rgba(255, 255, 255, 0.95);
    backdrop-filter: blur(12px);
    border-bottom: 1px solid var(--border-light);
    transition: all 0.2s ease;
  }
  .header-inner {
    display: flex;
    align-items: center;
    justify-content: space-between;
    height: 74px;
  }
  .brand-group {
    display: flex;
    align-items: center;
    gap: 12px;
  }
  .brand-logo-img {
    width: 220px;       /* Increase logo size */
    height: auto;       /* Maintain aspect ratio */
    max-width: 100%;
    display: block;
  }
  .parent-badge {
    display: inline-flex;
    align-items: center;
    gap: 4px;
    background: var(--eva-blue-light);
    color: var(--eva-blue);
    font-size: 11px;
    font-weight: 700;
    text-transform: uppercase;
    letter-spacing: 0.5px;
    padding: 3px 8px;
    border-radius: 6px;
    border: 1px solid rgba(10, 102, 194, 0.2);
  }

  .nav-menu {
    display: flex;
    align-items: center;
    gap: 28px;
    list-style: none;
  }
  .nav-menu a {
    font-size: 14.5px;
    font-weight: 600;
    color: var(--ink-dark);
    transition: color 0.15s ease;
  }
  .nav-menu a:hover {
    color: var(--eva-blue);
  }

  .nav-actions {
    display: flex;
    align-items: center;
    gap: 12px;
  }
  .btn {
    display: inline-flex;
    align-items: center;
    justify-content: center;
    gap: 8px;
    padding: 10px 20px;
    font-size: 14.5px;
    font-weight: 600;
    border-radius: 8px;
    transition: all 0.2s ease;
    cursor: pointer;
    border: 1px solid transparent;
  }
  .btn-outline {
    background: transparent;
    border-color: var(--border-light);
    color: var(--ink-dark);
  }
  .btn-outline:hover {
    background: var(--bg-light);
    border-color: #cbd5e1;
  }
  .btn-primary {
    background: var(--eva-blue);
    color: #ffffff;
    box-shadow: 0 4px 14px var(--eva-blue-glow);
  }
  .btn-primary:hover {
    background: var(--eva-blue-hover);
    transform: translateY(-1px);
    box-shadow: 0 6px 20px rgba(10, 102, 194, 0.3);
  }
  .btn-lg {
    padding: 14px 28px;
    font-size: 16px;
    border-radius: 10px;
  }

  /* Hero Section */
  .hero-section {
    position: relative;
    padding: 70px 0 90px;
    background: 
      radial-gradient(800px 400px at 80% 0%, rgba(10, 102, 194, 0.08), transparent 70%),
      radial-gradient(600px 400px at 10% 20%, rgba(0, 180, 216, 0.06), transparent 70%),
      linear-gradient(180deg, #f7faff 0%, #ffffff 100%);
    overflow: hidden;
  }
  .hero-grid {
    display: grid;
    grid-template-columns: 1.15fr 0.85fr;
    gap: 48px;
    align-items: center;
  }
  .hero-eyebrow {
    display: inline-flex;
    align-items: center;
    gap: 8px;
    background: #ffffff;
    border: 1px solid #dbeafe;
    box-shadow: 0 2px 10px rgba(10, 102, 194, 0.06);
    padding: 6px 14px;
    border-radius: 100px;
    font-size: 13px;
    font-weight: 700;
    color: var(--eva-blue);
    margin-bottom: 20px;
  }
  .hero-eyebrow .pulse-dot {
    width: 8px;
    height: 8px;
    border-radius: 50%;
    background: var(--eva-green);
    box-shadow: 0 0 0 4px rgba(16, 185, 129, 0.2);
  }
  .hero-title {
    font-size: clamp(34px, 4.4vw, 52px);
    font-weight: 800;
    line-height: 1.15;
    color: var(--eva-dark);
    letter-spacing: -0.8px;
    margin-bottom: 18px;
  }
  .hero-title .text-blue {
    color: var(--eva-blue);
    background: linear-gradient(135deg, #0a66c2 0%, #00b4d8 100%);
    -webkit-background-clip: text;
    -webkit-text-fill-color: transparent;
  }
  .hero-desc {
    font-size: 17.5px;
    color: var(--ink-body);
    line-height: 1.65;
    margin-bottom: 30px;
    max-width: 580px;
  }
  .hero-badges {
    display: flex;
    flex-wrap: wrap;
    gap: 20px;
    margin-top: 32px;
    padding-top: 24px;
    border-top: 1px solid var(--border-soft);
  }
  .badge-item {
    display: flex;
    align-items: center;
    gap: 8px;
    font-size: 13.5px;
    font-weight: 600;
    color: var(--ink-dark);
  }
  .badge-item i {
    color: var(--eva-green);
    font-size: 16px;
  }

  /* Live Interactive Configurator Preview Card */
  .config-card {
    background: #ffffff;
    border-radius: 18px;
    border: 1px solid var(--border-light);
    box-shadow: var(--shadow-lg);
    overflow: hidden;
    position: relative;
  }
  .config-header {
    background: var(--eva-dark);
    color: #ffffff;
    padding: 14px 18px;
    display: flex;
    justify-content: space-between;
    align-items: center;
  }
  .config-header-title {
    font-size: 13.5px;
    font-weight: 700;
    display: flex;
    align-items: center;
    gap: 8px;
  }
  .config-header-status {
    background: rgba(16, 185, 129, 0.2);
    color: #34d399;
    font-size: 11.5px;
    padding: 3px 8px;
    border-radius: 20px;
    font-weight: 600;
  }
  .config-body {
    padding: 20px;
  }
  .live-window-preview {
    background: #f8fafc;
    border: 1px solid var(--border-light);
    border-radius: 12px;
    height: 220px;
    display: flex;
    align-items: center;
    justify-content: center;
    padding: 10px;
    position: relative;
    margin-bottom: 18px;
  }
  .system-selector {
    display: grid;
    grid-template-columns: repeat(3, 1fr);
    gap: 8px;
    margin-bottom: 16px;
  }
  .system-tab {
    padding: 8px;
    text-align: center;
    font-size: 12px;
    font-weight: 700;
    border-radius: 8px;
    border: 1px solid var(--border-light);
    background: #ffffff;
    cursor: pointer;
    transition: all 0.15s ease;
  }
  .system-tab.active {
    background: var(--eva-blue);
    color: #ffffff;
    border-color: var(--eva-blue);
  }
  .dimension-sliders {
    display: grid;
    grid-template-columns: 1fr 1fr;
    gap: 14px;
    margin-bottom: 16px;
  }
  .slider-group label {
    display: flex;
    justify-content: space-between;
    font-size: 12px;
    font-weight: 600;
    color: var(--ink-dark);
    margin-bottom: 4px;
  }
  .slider-group input[type="range"] {
    width: 100%;
    accent-color: var(--eva-blue);
    cursor: pointer;
  }
  .config-stats-grid {
    display: grid;
    grid-template-columns: 1fr 1fr 1fr;
    gap: 10px;
    background: var(--bg-light);
    padding: 12px;
    border-radius: 10px;
    border: 1px solid var(--border-light);
    text-align: center;
  }
  .stat-label {
    font-size: 11px;
    text-transform: uppercase;
    font-weight: 700;
    color: var(--ink-muted);
  }
  .stat-val {
    font-size: 15px;
    font-weight: 800;
    color: var(--eva-blue);
    margin-top: 2px;
  }

  /* Metric Highlights Strip */
  .metrics-strip {
    background: var(--eva-dark);
    color: #ffffff;
    padding: 40px 0;
    position: relative;
  }
  .metrics-grid {
    display: grid;
    grid-template-columns: repeat(4, 1fr);
    gap: 24px;
    text-align: center;
  }
  .metric-item h3 {
    font-size: 38px;
    font-weight: 800;
    color: #38bdf8;
    margin-bottom: 4px;
    letter-spacing: -0.5px;
  }
  .metric-item p {
    font-size: 14px;
    color: #cbd5e1;
    font-weight: 500;
  }

  /* Section Styling */
  section.page-section {
    padding: 85px 0;
  }
  .section-header {
    text-align: center;
    max-width: 680px;
    margin: 0 auto 52px;
  }
  .section-tag {
    display: inline-block;
    color: var(--eva-blue);
    font-weight: 800;
    font-size: 12.5px;
    text-transform: uppercase;
    letter-spacing: 1px;
    margin-bottom: 10px;
  }
  .section-title {
    font-size: clamp(28px, 3.2vw, 40px);
    font-weight: 800;
    color: var(--eva-dark);
    letter-spacing: -0.6px;
    line-height: 1.2;
    margin-bottom: 14px;
  }
  .section-subtitle {
    font-size: 16.5px;
    color: var(--ink-body);
  }

  /* Feature Cards (EvA Grid) */
  .features-grid {
    display: grid;
    grid-template-columns: repeat(3, 1fr);
    gap: 26px;
  }
  .feature-card {
    background: #ffffff;
    border: 1px solid var(--border-light);
    border-radius: 16px;
    padding: 30px;
    transition: all 0.25s ease;
    box-shadow: var(--shadow-sm);
    position: relative;
  }
  .feature-card:hover {
    transform: translateY(-5px);
    box-shadow: var(--shadow-md);
    border-color: rgba(10, 102, 194, 0.4);
  }
  .feature-icon-box {
    width: 52px;
    height: 52px;
    border-radius: 12px;
    background: var(--eva-blue-light);
    color: var(--eva-blue);
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 24px;
    margin-bottom: 20px;
  }
  .feature-card h4 {
    font-size: 19px;
    font-weight: 700;
    color: var(--eva-dark);
    margin-bottom: 10px;
  }
  .feature-card p {
    font-size: 14.5px;
    color: var(--ink-body);
    line-height: 1.6;
  }

  /* Two Column Solution Showcase */
  .solution-split {
    display: grid;
    grid-template-columns: 1fr 1fr;
    gap: 50px;
    align-items: center;
    margin-bottom: 60px;
  }
  .solution-split.reverse {
    direction: rtl;
  }
  .solution-split.reverse .solution-content {
    direction: ltr;
  }
  .solution-split.reverse .solution-visual {
    direction: ltr;
  }
  .solution-content h3 {
    font-size: 32px;
    font-weight: 800;
    color: var(--eva-dark);
    margin-bottom: 16px;
    line-height: 1.25;
  }
  .solution-content p {
    font-size: 16px;
    color: var(--ink-body);
    margin-bottom: 22px;
  }
  .solution-list {
    list-style: none;
    display: grid;
    gap: 12px;
    margin-bottom: 28px;
  }
  .solution-list li {
    display: flex;
    align-items: center;
    gap: 10px;
    font-size: 15px;
    font-weight: 600;
    color: var(--ink-dark);
  }
  .solution-list li i {
    color: var(--eva-blue);
    font-size: 18px;
  }
  .solution-visual {
    background: var(--bg-light);
    border: 1px solid var(--border-light);
    border-radius: 20px;
    padding: 24px;
    box-shadow: var(--shadow-md);
  }

  /* Comparison Table */
  .compare-table-wrap {
    background: #ffffff;
    border: 1px solid var(--border-light);
    border-radius: 18px;
    overflow: hidden;
    box-shadow: var(--shadow-md);
  }
  table.compare-table {
    width: 100%;
    border-collapse: collapse;
    font-size: 14.5px;
  }
  table.compare-table th {
    padding: 18px 22px;
    text-align: left;
    background: var(--bg-light);
    color: var(--ink-dark);
    font-weight: 700;
    border-bottom: 1px solid var(--border-light);
  }
  table.compare-table td {
    padding: 16px 22px;
    border-bottom: 1px solid var(--border-soft);
    color: var(--ink-body);
  }
  table.compare-table tr:hover td {
    background: #fafcff;
  }
  .highlight-col {
    background: #f0f7ff !important;
    font-weight: 700;
    color: var(--eva-blue) !important;
  }

  /* Call To Action Banner */
  .cta-section {
    background: linear-gradient(135deg, #071529 0%, #0a66c2 100%);
    color: #ffffff;
    padding: 70px 0;
    text-align: center;
    border-radius: 24px;
    margin: 40px auto 70px;
  }
  .cta-section h2 {
    font-size: clamp(28px, 3.5vw, 42px);
    font-weight: 800;
    margin-bottom: 16px;
  }
  .cta-section p {
    font-size: 17px;
    color: #e2e8f0;
    max-width: 600px;
    margin: 0 auto 30px;
  }

  /* Footer */
  footer.site-footer {
    background: var(--eva-dark);
    color: #94a3b8;
    padding: 60px 0 30px;
    font-size: 14px;
    border-top: 1px solid rgba(255, 255, 255, 0.08);
  }
  .footer-grid {
    display: grid;
    grid-template-columns: 1.5fr 1fr 1fr 1fr;
    gap: 40px;
    margin-bottom: 40px;
  }
  .footer-col h5 {
    color: #ffffff;
    font-size: 15px;
    font-weight: 700;
    margin-bottom: 18px;
  }
  .footer-col ul {
    list-style: none;
    display: grid;
    gap: 10px;
  }
  .footer-col a:hover {
    color: #38bdf8;
  }
  .footer-bottom {
    display: flex;
    justify-content: space-between;
    align-items: center;
    padding-top: 24px;
    border-top: 1px solid rgba(255, 255, 255, 0.08);
    font-size: 13px;
  }

  @media (max-width: 991px) {
    .hero-grid { grid-template-columns: 1fr; }
    .features-grid { grid-template-columns: 1fr; }
    .metrics-grid { grid-template-columns: 1fr 1fr; gap: 20px; }
    .solution-split { grid-template-columns: 1fr; }
    .footer-grid { grid-template-columns: 1fr 1fr; }
    .nav-menu { display: none; }
  }
</style>
</head>
<body>

<!-- Top Announcement Banner -->
<div class="top-strip">
  <div class="container">
    <span>✨ Enterprise Release: Full BOM Estimator & FrameQoute-Format CAD Quotation Engine</span>
    <a href="{{ route('login') }}">Access Studio Portal &rarr;</a>
  </div>
</div>

<!-- Header Navigation -->
<header class="site-header">
  <div class="container header-inner">
    <div class="brand-group">
      <a href="/" class="d-flex align-items-center gap-2">
        <img src="{{ asset('images/framequote-logo.png') }}" alt="FrameQuote Logo" class="brand-logo-img">
      </a>
      <!-- <span class="parent-badge"><i class="bi bi-shield-check"></i> By Codevora</span> -->
    </div>

    <ul class="nav-menu">
      <li><a href="#features">Features</a></li>
      <li><a href="#configurator">Live Estimator</a></li>
      <li><a href="#solutions">uPVC &amp; Aluminium</a></li>
      <li><a href="#comparison">Why Us vs FrameQuote</a></li>
      <li><a href="#workflow">Workflow</a></li>
    </ul>

    <div class="nav-actions">
      @auth
        <a href="{{ route('dashboard') }}" class="btn btn-primary">
          <i class="bi bi-speedometer2"></i> Dashboard
        </a>
      @else
        <a href="{{ route('login') }}" class="btn btn-outline">Sign In</a>
        <a href="{{ route('login') }}" class="btn btn-primary">
          <i class="bi bi-rocket-takeoff"></i> Launch Studio
        </a>
      @endauth
    </div>
  </div>
</header>

<!-- Hero Section -->
<section class="hero-section">
  <div class="container hero-grid">
    <div>
      <div class="hero-eyebrow">
        <span class="pulse-dot"></span>
        #1 uPVC &amp; Aluminium Window &amp; Door ERP
      </div>

      <h1 class="hero-title">
        Generate Accurate Window &amp; Door Quotations <span class="text-blue">in Under 5 Minutes.</span>
      </h1>

      <p class="hero-desc">
        Complete commercial estimation, parametric CAD SVG elevations, factory BOM costing, and instant client-ready PDFs. Zero calculation drift tolerance, built specifically for fabricators and dealers.
      </p>

      <div class="d-flex flex-wrap gap-3">
        <a href="{{ route('login') }}" class="btn btn-primary btn-lg">
          <i class="bi bi-play-circle-fill"></i> Start Live Quotation
        </a>
        <a href="#configurator" class="btn btn-outline btn-lg">
          <i class="bi bi-sliders"></i> Try Live Configurator
        </a>
      </div>

      <div class="hero-badges">
        <div class="badge-item"><i class="bi bi-check-circle-fill"></i> 0% Calculation Drift</div>
        <div class="badge-item"><i class="bi bi-check-circle-fill"></i> uPVC &amp; Aluminium</div>
        <div class="badge-item"><i class="bi bi-check-circle-fill"></i> Factory BOM &amp; Weights</div>
        <div class="badge-item"><i class="bi bi-check-circle-fill"></i> 30-Day Client Links</div>
      </div>
    </div>

    <!-- Live Interactive Hero Configurator Widget -->
    <div id="configurator">
      <div class="config-card">
        <div class="config-header">
          <div class="config-header-title">
            <i class="bi bi-cpu-fill text-info"></i> FrameQuote Live Estimator
          </div>
          <span class="config-header-status"><i class="bi bi-circle-fill me-1" style="font-size: 8px;"></i> Instant Engine</span>
        </div>

        <div class="config-body">
          <div class="system-selector">
            <div class="system-tab active" onclick="selectSystem('sliding_2t', this)">2-Track Sliding</div>
            <div class="system-tab" onclick="selectSystem('casement_2s', this)">2-Sash Casement</div>
            <div class="system-tab" onclick="selectSystem('fixed_picture', this)">Fixed Picture</div>
          </div>

          <div class="live-window-preview" id="svgContainer">
            <!-- Dynamic SVG injected here -->
          </div>

          <div class="dimension-sliders">
            <div class="slider-group">
              <label>Width: <span id="widthVal" class="text-primary fw-bold">1500 mm</span></label>
              <input type="range" id="widthRange" min="800" max="3000" step="50" value="1500" oninput="updateConfig()">
            </div>
            <div class="slider-group">
              <label>Height: <span id="heightVal" class="text-primary fw-bold">1800 mm</span></label>
              <input type="range" id="heightRange" min="800" max="2600" step="50" value="1800" oninput="updateConfig()">
            </div>
          </div>

          <div class="config-stats-grid">
            <div>
              <div class="stat-label">Total Area</div>
              <div class="stat-val" id="areaSqFt">29.06 sq.ft</div>
            </div>
            <div>
              <div class="stat-label">BOM Weight</div>
              <div class="stat-val" id="bomWeight">24.8 kg</div>
            </div>
            <div>
              <div class="stat-label">Estimated Cost</div>
              <div class="stat-val" id="estCost">₹ 18,889</div>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>
</section>

<!-- Metrics Highlights Strip -->
<div class="metrics-strip">
  <div class="container metrics-grid">
    <div class="metric-item">
      <h3>&lt; 5 Mins</h3>
      <p>Average Quotation Creation Time</p>
    </div>
    <div class="metric-item">
      <h3>0.0%</h3>
      <p>BOM &amp; Pricing Calculation Tolerance</p>
    </div>
    <div class="metric-item">
      <h3>100%</h3>
      <p>FrameQoute-Standard CAD Elevation Quality</p>
    </div>
    <div class="metric-item">
      <h3>7-Stage</h3>
      <p>Automated Commercial Audit Gate</p>
    </div>
  </div>
</div>

<!-- Features Section (EvA Cloud Feature Set) -->
<section class="page-section" id="features">
  <div class="container">
    <div class="section-header">
      <span class="section-tag">Core Capabilities</span>
      <h2 class="section-title">Built Exclusively for Window &amp; Door Fabricators</h2>
      <p class="section-subtitle">
        Eliminate Excel guesswork, wrong cutting dimensions, and unbranded PDFs. FrameQuote delivers full operational transparency.
      </p>
    </div>

    <div class="features-grid">
      <div class="feature-card">
        <div class="feature-icon-box"><i class="bi bi-calculator"></i></div>
        <h4>Instant On-Site Estimation</h4>
        <p>Sales teams configure dimensions on tablet or laptop with live recalculations of area, freight, installation, discounts, and 18% GST.</p>
      </div>

      <div class="feature-card">
        <div class="feature-icon-box"><i class="bi bi-vector-pen"></i></div>
        <h4>Parametric CAD SVG Elevation</h4>
        <p>Mathematical inside-view elevations rendered on the fly. Sashes, glass panes, mosquito mesh, and dimension callouts matching FrameQoute outputs.</p>
      </div>

      <div class="feature-card">
        <div class="feature-icon-box"><i class="bi bi-boxes"></i></div>
        <h4>Factory BOM &amp; Profile Weight</h4>
        <p>Generates factory BOM cutting sheets with profile lengths, reinforcement steel, hardware counts, waste percentages, and exact profile kilograms.</p>
      </div>

      <div class="feature-card">
        <div class="feature-icon-box"><i class="bi bi-shield-check"></i></div>
        <h4>7-Stage Commercial Validation</h4>
        <p>A rigorous 100% quality gate verifies customer info, technical specs, company GSTIN, bank details, and warranty terms before approval.</p>
      </div>

      <div class="feature-card">
        <div class="feature-icon-box"><i class="bi bi-file-earmark-pdf"></i></div>
        <h4>High-Quality Client PDFs</h4>
        <p>Generates clean, professional proposals with cover letter, elevation drawings, size breakdowns, terms, bank details, and signature blocks.</p>
      </div>

      <div class="feature-card">
        <div class="feature-icon-box"><i class="bi bi-share"></i></div>
        <h4>1-Click WhatsApp &amp; Signed Links</h4>
        <p>Send 30-day tamper-proof cryptographically signed links directly to clients. Works seamlessly with 1-click WhatsApp messaging.</p>
      </div>
    </div>
  </div>
</section>

<!-- Solution Split: uPVC vs Aluminium -->
<section class="page-section bg-light" id="solutions">
  <div class="container">
    <div class="solution-split">
      <div class="solution-content">
        <span class="section-tag">uPVC Systems</span>
        <h3>High-Performance uPVC Windows &amp; Doors</h3>
        <p>Configure multi-chamber uPVC profiles with steel reinforcement calculations, multi-point locks, and concealed water drainage caps.</p>
        <ul class="solution-list">
          <li><i class="bi bi-check-circle-fill"></i> 2-Track, 3-Track, and 4-Track Sliding Windows</li>
          <li><i class="bi bi-check-circle-fill"></i> Casement Openable &amp; Tilt-and-Turn Systems</li>
          <li><i class="bi bi-check-circle-fill"></i> SS304 Mesh Integration &amp; Grill Inclusions</li>
          <li><i class="bi bi-check-circle-fill"></i> Single, DGU (Double Glazed), &amp; Laminated Glass</li>
        </ul>
        <a href="{{ route('login') }}" class="btn btn-primary">Configure uPVC Quote &rarr;</a>
      </div>
      <div class="solution-visual">
        <div class="p-3 bg-white rounded-3 border">
          <div class="d-flex justify-content-between mb-2">
            <span class="badge bg-primary">uPVC Profile Series</span>
            <small class="text-muted fw-bold">60mm Casement / 88mm Sliding</small>
          </div>
          <div class="small text-muted mb-2">Reinforcement: 1.2mm Galvanized Steel Box</div>
          <div class="small text-muted mb-2">Gasket: EPDM Weather Stripping</div>
          <div class="small text-muted">Hardware: Multi-Point Security Transmission</div>
        </div>
      </div>
    </div>

    <div class="solution-split reverse">
      <div class="solution-content">
        <span class="section-tag">Aluminium Systems</span>
        <h3>Architectural Aluminium &amp; Slimline Facades</h3>
        <p>Support for luxury slimline profiles, thermal break sections, heavy duty patio sliders, and folding bi-fold doors.</p>
        <ul class="solution-list">
          <li><i class="bi bi-check-circle-fill"></i> Slim Profile Minimalist Windows</li>
          <li><i class="bi bi-check-circle-fill"></i> Thermal-Break High Insulation Doors</li>
          <li><i class="bi bi-check-circle-fill"></i> Anodized, Powder Coated, &amp; Wooden Finishes</li>
          <li><i class="bi bi-check-circle-fill"></i> Heavy Duty Rollers &amp; Stainless Steel Tracks</li>
        </ul>
        <a href="{{ route('login') }}" class="btn btn-primary">Configure Aluminium Quote &rarr;</a>
      </div>
      <div class="solution-visual">
        <div class="p-3 bg-white rounded-3 border">
          <div class="d-flex justify-content-between mb-2">
            <span class="badge bg-dark">Aluminium Architecture</span>
            <small class="text-muted fw-bold">45mm / 65mm Thermal Break</small>
          </div>
          <div class="small text-muted mb-2">Alloy: 6063-T6 Grade Virgin Aluminium</div>
          <div class="small text-muted mb-2">Glass Pocket: 5mm to 32mm DGU Support</div>
          <div class="small text-muted">Interlock: 19mm Ultra-Slim Sightline</div>
        </div>
      </div>
    </div>
  </div>
</section>

<!-- Comparison Table: FrameQuote vs EvA vs Excel -->
<section class="page-section" id="comparison">
  <div class="container">
    <div class="section-header">
      <span class="section-tag">Industry Benchmark</span>
      <h2 class="section-title">Why Industry Leaders Choose FrameQuote</h2>
      <p class="section-subtitle">A direct comparison of quotation workflows in the fenestration industry.</p>
    </div>

    <div class="compare-table-wrap">
      <table class="compare-table">
        <thead>
          <tr>
            <th>Feature / Capability</th>
            <th>Traditional Excel</th>
            <th>FrameQoute Cloud</th>
            <th class="highlight-col">FrameQuote (by Codevora)</th>
          </tr>
        </thead>
        <tbody>
          <tr>
            <td><strong>Quotation Time</strong></td>
            <td>45 – 90 Minutes</td>
            <td>5 – 10 Minutes</td>
            <td class="highlight-col">&lt; 5 Minutes (Automated)</td>
          </tr>
          <tr>
            <td><strong>Parametric CAD Elevation</strong></td>
            <td>Manual Drawing / None</td>
            <td>Available</td>
            <td class="highlight-col">Authoritative SVG with Dimensions</td>
          </tr>
          <tr>
            <td><strong>Factory BOM &amp; Weight Calculation</strong></td>
            <td>Error-prone formulas</td>
            <td>BOM Engine</td>
            <td class="highlight-col">Full BOM + Kg/M Profile Weight</td>
          </tr>
          <tr>
            <td><strong>Calculation Drift Policy</strong></td>
            <td>High variance</td>
            <td>Standard</td>
            <td class="highlight-col">0% Tolerance Guarantee</td>
          </tr>
          <tr>
            <td><strong>Commercial Audit Gate</strong></td>
            <td>No checks</td>
            <td>Partial</td>
            <td class="highlight-col">7-Stage 100% Pre-Approval Audit</td>
          </tr>
          <tr>
            <td><strong>Multi-Tenant Enterprise Isolation</strong></td>
            <td>None</td>
            <td>Standard</td>
            <td class="highlight-col">Dedicated Tenant Scopes &amp; Platform Console</td>
          </tr>
        </tbody>
      </table>
    </div>
  </div>
</section>

<!-- Call to Action Banner -->
<div class="container">
  <div class="cta-section">
    <h2>Ready to Modernize Your Window &amp; Door Sales?</h2>
    <p>Join fabricators and dealers who create faster, error-free quotations with FrameQuote by Codevora.</p>
    <div class="d-flex justify-content-center gap-3">
      <a href="{{ route('login') }}" class="btn btn-light btn-lg text-primary fw-bold">
        <i class="bi bi-box-arrow-in-right"></i> Launch Studio Portal
      </a>
    </div>
  </div>
</div>

<!-- Footer -->
<footer class="site-footer">
  <div class="container">
    <div class="footer-grid">
      <div class="footer-col">
        <div class="d-flex align-items-center gap-2 mb-3">
          <img src="{{ asset('images/framequote-logo.png') }}" alt="FrameQuote Logo" style="height: 38px; filter: brightness(1.2);">
        </div>
        <p class="small text-slate-400 mb-3">
          The comprehensive quotation ERP, CAD elevation generator, and BOM estimation software for uPVC and Aluminium window &amp; door manufacturers.
        </p>
        <span class="parent-badge"><i class="bi bi-shield-check"></i> Codevora Parent Company</span>
      </div>

      <div class="footer-col">
        <h5>Product</h5>
        <ul>
          <li><a href="#features">Quotation Studio</a></li>
          <li><a href="#configurator">CAD Elevation SVG</a></li>
          <li><a href="#features">Factory BOM Engine</a></li>
          <li><a href="#features">Commercial Audit Gate</a></li>
        </ul>
      </div>

      <div class="footer-col">
        <h5>Solutions</h5>
        <ul>
          <li><a href="#solutions">uPVC Sliding &amp; Casement</a></li>
          <li><a href="#solutions">Aluminium Thermal Break</a></li>
          <li><a href="#solutions">Slimline Facade Systems</a></li>
          <li><a href="#solutions">Glass &amp; Hardware Library</a></li>
        </ul>
      </div>

      <div class="footer-col">
        <h5>Portal</h5>
        <ul>
          <li><a href="{{ route('login') }}">Sign In to ERP</a></li>
          <li><a href="{{ route('password.request') }}">Password Recovery</a></li>
          <li><a href="#comparison">Compare vs EvA</a></li>
          <li><a href="mailto:support@codevoraai.com">Enterprise Support</a></li>
        </ul>
      </div>
    </div>

    <div class="footer-bottom">
      <div>&copy; {{ date('Y') }} FrameQuote. All rights reserved. Built &amp; Powered by <strong>Codevora Technologies</strong>.</div>
      <div class="d-flex gap-3">
        <span class="small"><i class="bi bi-shield-lock-fill text-success me-1"></i> ISO 27001 &amp; Tenancy Compliant</span>
      </div>
    </div>
  </div>
</footer>

<!-- Interactive Configurator JavaScript -->
<script>
  let currentSystem = 'sliding_2t';

  function selectSystem(system, el) {
    currentSystem = system;
    document.querySelectorAll('.system-tab').forEach(t => t.classList.remove('active'));
    el.classList.add('active');
    updateConfig();
  }

  function updateConfig() {
    const w = parseInt(document.getElementById('widthRange').value);
    const h = parseInt(document.getElementById('heightRange').value);
    
    document.getElementById('widthVal').innerText = w + ' mm';
    document.getElementById('heightVal').innerText = h + ' mm';

    // Area in sq.ft: 1 sq.ft = 92903.04 mm²
    const area = (w * h) / 92903.04;
    document.getElementById('areaSqFt').innerText = area.toFixed(2) + ' sq.ft';

    // Estimated Profile Weight in kg
    const perimeterMtr = ((w + h) * 2) / 1000;
    const estWeight = (perimeterMtr * (currentSystem === 'casement_2s' ? 1.6 : 1.35)).toFixed(1);
    document.getElementById('bomWeight').innerText = estWeight + ' kg';

    // Estimated Price
    const baseRatePerSqFt = currentSystem === 'casement_2s' ? 690 : (currentSystem === 'fixed_picture' ? 480 : 620);
    const estPrice = Math.round(area * baseRatePerSqFt * 1.18); // including 18% GST
    document.getElementById('estCost').innerText = '₹ ' + estPrice.toLocaleString('en-IN');

    // Generate Dynamic SVG Elevation
    renderSvg(w, h, currentSystem);
  }

  function renderSvg(w, h, sys) {
    const container = document.getElementById('svgContainer');
    const svgW = 280;
    const svgH = 190;
    const aspect = w / h;
    let boxW, boxH;

    if (aspect > 1) {
      boxW = 200;
      boxH = Math.max(50, Math.min(150, boxW / aspect));
    } else {
      boxH = 140;
      boxW = Math.max(50, Math.min(200, boxH * aspect));
    }

    const x = (svgW - boxW) / 2;
    const y = (svgH - boxH) / 2;

    let innerContent = '';

    if (sys === 'sliding_2t') {
      const halfW = boxW / 2;
      innerContent = `
        <!-- Outer Frame -->
        <rect x="${x}" y="${y}" width="${boxW}" height="${boxH}" fill="#f1f5f9" stroke="#0a66c2" stroke-width="4" rx="2" />
        <!-- Sash 1 (Left) -->
        <rect x="${x + 6}" y="${y + 6}" width="${halfW - 3}" height="${boxH - 12}" fill="#e0f2fe" stroke="#0284c7" stroke-width="2" />
        <!-- Glass 1 -->
        <rect x="${x + 12}" y="${y + 12}" width="${halfW - 15}" height="${boxH - 24}" fill="#bae6fd" opacity="0.6" />
        <!-- Sash 2 (Right) -->
        <rect x="${x + halfW - 3}" y="${y + 6}" width="${halfW - 3}" height="${boxH - 12}" fill="#e0f2fe" stroke="#0284c7" stroke-width="2" />
        <!-- Glass 2 (Mesh pattern) -->
        <rect x="${x + halfW + 3}" y="${y + 12}" width="${halfW - 15}" height="${boxH - 24}" fill="#7dd3fc" opacity="0.7" />
        <!-- Sliding Direction Arrows -->
        <path d="M ${x + 25} ${y + boxH/2} L ${x + 45} ${y + boxH/2} M ${x + 40} ${y + boxH/2 - 5} L ${x + 45} ${y + boxH/2} L ${x + 40} ${y + boxH/2 + 5}" stroke="#0369a1" stroke-width="2" />
        <path d="M ${x + boxW - 25} ${y + boxH/2} L ${x + boxW - 45} ${y + boxH/2} M ${x + boxW - 40} ${y + boxH/2 - 5} L ${x + boxW - 45} ${y + boxH/2} L ${x + boxW - 40} ${y + boxH/2 + 5}" stroke="#0369a1" stroke-width="2" />
      `;
    } else if (sys === 'casement_2s') {
      const halfW = boxW / 2;
      innerContent = `
        <!-- Outer Frame -->
        <rect x="${x}" y="${y}" width="${boxW}" height="${boxH}" fill="#f1f5f9" stroke="#0a66c2" stroke-width="5" rx="2" />
        <!-- Mullion Divider -->
        <line x1="${x + halfW}" y1="${y}" x2="${x + halfW}" y2="${y + boxH}" stroke="#0a66c2" stroke-width="3" />
        <!-- Left Openable Sash -->
        <rect x="${x + 6}" y="${y + 6}" width="${halfW - 9}" height="${boxH - 12}" fill="#e0f2fe" stroke="#0369a1" stroke-width="2" />
        <line x1="${x + 6}" y1="${y + 6}" x2="${x + halfW - 3}" y2="${y + boxH/2}" stroke="#0284c7" stroke-dasharray="3,3" />
        <line x1="${x + 6}" y1="${y + boxH - 6}" x2="${x + halfW - 3}" y2="${y + boxH/2}" stroke="#0284c7" stroke-dasharray="3,3" />
        <!-- Right Openable Sash -->
        <rect x="${x + halfW + 3}" y="${y + 6}" width="${halfW - 9}" height="${boxH - 12}" fill="#e0f2fe" stroke="#0369a1" stroke-width="2" />
        <line x1="${x + boxW - 6}" y1="${y + 6}" x2="${x + halfW + 3}" y2="${y + boxH/2}" stroke="#0284c7" stroke-dasharray="3,3" />
        <line x1="${x + boxW - 6}" y1="${y + boxH - 6}" x2="${x + halfW + 3}" y2="${y + boxH/2}" stroke="#0284c7" stroke-dasharray="3,3" />
      `;
    } else {
      // Fixed Picture Window
      innerContent = `
        <!-- Outer Frame -->
        <rect x="${x}" y="${y}" width="${boxW}" height="${boxH}" fill="#f1f5f9" stroke="#0a66c2" stroke-width="5" rx="2" />
        <!-- Fixed Glass -->
        <rect x="${x + 8}" y="${y + 8}" width="${boxW - 16}" height="${boxH - 16}" fill="#bae6fd" opacity="0.75" />
        <!-- Glass Reflection Highlights -->
        <line x1="${x + 20}" y1="${y + 20}" x2="${x + 60}" y2="${y + 60}" stroke="#ffffff" stroke-width="3" opacity="0.8" />
        <line x1="${x + 30}" y1="${y + 20}" x2="${x + 50}" y2="${y + 40}" stroke="#ffffff" stroke-width="2" opacity="0.8" />
      `;
    }

    // Dimension Callouts
    const dimCallouts = `
      <!-- Top Width Dimension -->
      <line x1="${x}" y1="${y - 8}" x2="${x + boxW}" y2="${y - 8}" stroke="#64748b" stroke-width="1.2" />
      <text x="${x + boxW/2}" y="${y - 12}" fill="#0f172a" font-size="10" font-weight="700" text-anchor="middle">${w} mm</text>
      <!-- Left Height Dimension -->
      <line x1="${x - 8}" y1="${y}" x2="${x - 8}" y2="${y + boxH}" stroke="#64748b" stroke-width="1.2" />
      <text x="${x - 12}" y="${y + boxH/2}" fill="#0f172a" font-size="10" font-weight="700" text-anchor="middle" transform="rotate(-90, ${x - 12}, ${y + boxH/2})">${h} mm</text>
    `;

    container.innerHTML = `
      <svg width="100%" height="100%" viewBox="0 0 ${svgW} ${svgH}">
        ${dimCallouts}
        ${innerContent}
      </svg>
    `;
  }

  // Initialize on load
  document.addEventListener('DOMContentLoaded', () => {
    updateConfig();
  });
</script>
</body>
</html>
