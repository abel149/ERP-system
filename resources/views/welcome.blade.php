<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Brundo International PLC — Organic Flavors of Ethiopia</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&family=Plus+Jakarta+Sans:wght@600;700;800&display=swap">
    <style>
        :root {
            --ink: #16252e;
            --muted: #637680;
            --bg: #f4f7f8;
            --card: #ffffff;
            --border: #e3eaed;
            --accent: #0b7f78;
            --accent-2: #2dd4bf;
            --accent-dk: #086059;
        }
        * { margin: 0; padding: 0; box-sizing: border-box; }
        html { scroll-behavior: smooth; }
        html, body { overflow-x: hidden; }
        @supports (overflow: clip) { html, body { overflow-x: clip; } }
        body { font-family: 'Inter', system-ui, sans-serif; color: var(--ink); background: var(--bg); }
        h1, h2, h3, .brand-name { font-family: 'Plus Jakarta Sans', sans-serif; }
        a { text-decoration: none; color: inherit; }
        .wrap { max-width: 1180px; margin: 0 auto; padding: 0 28px; }

        /* ---------- scroll reveal ---------- */
        .reveal { opacity: 0; transform: translateY(26px); transition: opacity .65s ease, transform .65s ease; }
        .reveal.in { opacity: 1; transform: none; }
        .reveal[data-delay="1"] { transition-delay: .08s; }
        .reveal[data-delay="2"] { transition-delay: .16s; }
        .reveal[data-delay="3"] { transition-delay: .24s; }

        /* ---------- NAV ---------- */
        .nav {
            position: sticky; top: 0; z-index: 50;
            background: rgba(244, 247, 248, .88); backdrop-filter: blur(12px);
            border-bottom: 1px solid transparent; transition: border-color .25s, box-shadow .25s, background .25s;
        }
        .nav.scrolled { border-bottom-color: var(--border); box-shadow: 0 10px 30px -18px rgba(12, 29, 43, .3); background: rgba(255,255,255,.92); }
        .nav-inner { display: flex; align-items: center; justify-content: space-between; height: 72px; }
        .brand { display: flex; align-items: center; gap: 12px; }
        .brand-icon {
            width: 42px; height: 42px; border-radius: 12px; display: grid; place-items: center;
            font: 800 18px 'Plus Jakarta Sans', sans-serif; color: #fff;
            background: linear-gradient(135deg, #2dd4bf, #0b7f78);
            box-shadow: 0 8px 22px -8px rgba(45, 212, 191, .6);
        }
        .brand-name { font: 750 17px 'Plus Jakarta Sans', sans-serif; letter-spacing: -.02em; }
        .brand-sub { font-size: 11px; color: var(--muted); letter-spacing: .08em; text-transform: uppercase; }
        .nav-links { display: flex; gap: 28px; font-size: 14px; font-weight: 500; color: #3d5059; }
        .nav-links a { position: relative; padding: 4px 0; }
        .nav-links a::after {
            content: ''; position: absolute; left: 0; bottom: -2px; height: 2px; width: 0;
            background: var(--accent-2); border-radius: 2px; transition: width .25s;
        }
        .nav-links a:hover { color: var(--accent); }
        .nav-links a:hover::after { width: 100%; }
        .nav-links .nav-cta { display: none; }
        .nav-actions { display: flex; align-items: center; gap: 10px; }
        .nav-toggle {
            display: none; width: 40px; height: 40px; border: 1px solid var(--border);
            border-radius: 10px; background: #fff; font-size: 18px; cursor: pointer;
            align-items: center; justify-content: center; color: var(--ink);
        }
        .btn {
            display: inline-flex; align-items: center; justify-content: center; gap: 8px;
            padding: 11px 22px; border-radius: 11px; font: 600 14px 'Inter', sans-serif;
            border: 1px solid transparent; cursor: pointer; transition: transform .18s, box-shadow .18s, background .18s, border-color .18s;
        }
        .btn-primary { background: var(--accent); color: #fff; box-shadow: 0 8px 20px -8px rgba(11, 127, 120, .55); }
        .btn-primary:hover { background: var(--accent-dk); transform: translateY(-1px); box-shadow: 0 12px 26px -8px rgba(11, 127, 120, .6); }
        .btn-ghost { border-color: var(--border); color: var(--ink); background: transparent; }
        .btn-ghost:hover { border-color: var(--accent); color: var(--accent); }
        .btn-light { background: #fff; color: var(--accent); }
        .btn-light:hover { transform: translateY(-2px); box-shadow: 0 14px 30px -12px rgba(0,0,0,.4); }
        .btn-outline-light { border-color: rgba(255,255,255,.35); color: #fff; }
        .btn-outline-light:hover { border-color: #fff; background: rgba(255,255,255,.08); }

        /* ---------- HERO ---------- */
        .hero {
            position: relative; overflow: hidden; color: #fff;
            background:
                radial-gradient(70% 60% at 100% 0%, rgba(45, 212, 191, .32), transparent 60%),
                radial-gradient(60% 50% at 0% 100%, rgba(20, 184, 166, .18), transparent 60%),
                linear-gradient(150deg, #0c1d2b 0%, #0f2f3c 60%, #0b5f5a 100%);
        }
        .hero::after {
            content: ''; position: absolute; inset: 0; pointer-events: none; opacity: .45;
            background-image: radial-gradient(rgba(255, 255, 255, .09) 1px, transparent 1px);
            background-size: 24px 24px;
            mask-image: linear-gradient(180deg, transparent, #000 30%, transparent);
        }
        .hero-grid {
            position: relative; z-index: 1; display: grid; grid-template-columns: 1.05fr .95fr;
            gap: 56px; align-items: center; padding: 88px 0 104px;
        }
        .eyebrow {
            display: inline-block; padding: 6px 14px; border-radius: 999px;
            font-size: 12px; font-weight: 600; letter-spacing: .14em; text-transform: uppercase;
            color: #8ef0e0; background: rgba(255,255,255,.08); border: 1px solid rgba(255,255,255,.16);
            margin-bottom: 26px;
        }
        .hero h1 { font-size: clamp(42px, 6vw, 68px); line-height: 1.05; letter-spacing: -.025em; font-weight: 800; }
        .hero h1 span {
            background: linear-gradient(90deg, #5eead4, #a7f3d0);
            -webkit-background-clip: text; background-clip: text; color: transparent;
        }
        .hero p { margin: 24px 0 36px; font-size: 17px; line-height: 1.7; color: rgba(255,255,255,.72); max-width: 540px; }
        .hero-actions { display: flex; gap: 14px; flex-wrap: wrap; }
        .hero-stats { display: flex; gap: 44px; margin-top: 60px; flex-wrap: wrap; }
        .hero-stat b { display: block; font: 800 26px 'Plus Jakarta Sans', sans-serif; color: #5eead4; }
        .hero-stat span { font-size: 12.5px; color: rgba(255,255,255,.6); }

        /* ---------- floating ERP preview ---------- */
        .hero-visual { position: relative; }
        @keyframes floaty { 0%,100% { transform: translateY(0); } 50% { transform: translateY(-10px); } }
        .mock-card {
            position: relative; background: #fff; color: var(--ink); border-radius: 18px;
            padding: 22px 24px; width: 100%; max-width: 400px; margin-left: auto;
            box-shadow: 0 40px 80px -30px rgba(0,0,0,.55);
            animation: floaty 7s ease-in-out infinite;
        }
        .mock-head { display: flex; justify-content: space-between; align-items: center; margin-bottom: 16px; }
        .mock-title { font-size: 11px; font-weight: 700; letter-spacing: .12em; text-transform: uppercase; color: var(--muted); }
        .mock-doc { font-size: 11px; font-weight: 600; padding: 4px 10px; border-radius: 999px; background: #effaf8; color: var(--accent); }
        .mock-prod { display: flex; align-items: center; gap: 12px; padding-bottom: 14px; border-bottom: 1px dashed var(--border); margin-bottom: 12px; }
        .mock-tile {
            width: 42px; height: 42px; border-radius: 11px; display: grid; place-items: center; flex-shrink: 0;
            font: 800 17px 'Plus Jakarta Sans', sans-serif; color: #fff;
            background: linear-gradient(135deg, #2dd4bf, #0b7f78);
        }
        .mock-prod b { display: block; font-size: 14.5px; }
        .mock-prod span { font-size: 12px; color: var(--muted); }
        .mock-row { display: flex; justify-content: space-between; font-size: 13px; padding: 5px 0; color: #415560; }
        .mock-row.total { font-weight: 700; color: var(--ink); font-size: 15px; border-top: 1px solid var(--border); margin-top: 8px; padding-top: 11px; }
        .mock-row .val { font-variant-numeric: tabular-nums; }
        .float-chip {
            position: absolute; display: flex; align-items: center; gap: 9px;
            background: #fff; color: var(--ink); border-radius: 13px; padding: 11px 15px;
            font-size: 12.5px; font-weight: 600; box-shadow: 0 20px 44px -18px rgba(0,0,0,.5);
            animation: floaty 6s ease-in-out infinite;
        }
        .float-chip .dot { width: 9px; height: 9px; border-radius: 50%; }
        .chip-stock { top: -20px; right: -6px; animation-delay: -2s; }
        .chip-stock .dot { background: #2dd4bf; }
        .chip-margin { bottom: -18px; left: -6px; animation-delay: -4s; }
        .chip-margin .dot { background: #34d399; }
        .float-chip small { display: block; font-weight: 500; color: var(--muted); font-size: 11px; }

        /* ---------- marquee ---------- */
        .marquee { background: #fff; border-block: 1px solid var(--border); overflow: hidden; }
        .marquee-track { display: flex; width: max-content; animation: scrollx 46s linear infinite; }
        .marquee:hover .marquee-track { animation-play-state: paused; }
        @keyframes scrollx { to { transform: translateX(-50%); } }
        .mq-item {
            display: flex; align-items: center; gap: 26px; padding: 15px 13px 15px 26px;
            font-size: 13px; font-weight: 600; color: var(--muted); white-space: nowrap;
        }
        .mq-item::after { content: '·'; color: var(--accent-2); font-size: 18px; }

        /* ---------- SECTIONS ---------- */
        .section { padding: 88px 0; }
        .section-head { max-width: 640px; margin: 0 auto 52px; text-align: center; }
        .section-head .kicker { font-size: 12px; font-weight: 700; letter-spacing: .14em; text-transform: uppercase; color: var(--accent); margin-bottom: 10px; }
        .section-head h2 { font-size: clamp(28px, 4vw, 36px); letter-spacing: -.02em; margin-bottom: 12px; }
        .section-head p { color: var(--muted); font-size: 15.5px; line-height: 1.7; }

        .cards { display: grid; grid-template-columns: repeat(3, 1fr); gap: 20px; }
        .card {
            background: var(--card); border: 1px solid var(--border); border-radius: 18px;
            padding: 30px 28px; transition: transform .25s, box-shadow .25s, border-color .25s;
        }
        .card:hover { transform: translateY(-5px); box-shadow: 0 22px 44px -20px rgba(12, 29, 43, .28); border-color: #bfe8e2; }
        .card .ico {
            width: 48px; height: 48px; border-radius: 13px; display: grid; place-items: center; margin-bottom: 18px;
            background: linear-gradient(135deg, #d9f7f2, #aef0e6); color: var(--accent-dk);
        }
        .card h3 { font-size: 17px; margin-bottom: 8px; }
        .card p { font-size: 13.5px; color: var(--muted); line-height: 1.65; }

        .prod-grid { display: grid; grid-template-columns: repeat(4, 1fr); gap: 16px; }
        .prod {
            border-radius: 16px; padding: 20px; border: 1px solid var(--border);
            background: linear-gradient(160deg, #fff, #f0faf9); transition: transform .22s, box-shadow .22s;
        }
        .prod:hover { transform: translateY(-4px); box-shadow: 0 16px 34px -18px rgba(12, 29, 43, .3); }
        .prod .tile {
            width: 38px; height: 38px; border-radius: 10px; display: grid; place-items: center;
            font: 800 15px 'Plus Jakarta Sans', sans-serif; color: #fff; margin-bottom: 12px;
        }
        .prod b { display: block; font: 700 14.5px 'Plus Jakarta Sans', sans-serif; margin-bottom: 3px; }
        .prod span { font-size: 12px; color: var(--muted); }

        .cert-strip {
            background: linear-gradient(140deg, #0c1d2b, #0f2f3c); color: #e6f2f0; border-radius: 20px;
            padding: 38px 44px; display: flex; align-items: center; justify-content: space-between; gap: 24px; flex-wrap: wrap;
            box-shadow: 0 26px 55px -26px rgba(12, 29, 43, .6);
        }
        .cert-strip b { font-size: 13px; letter-spacing: .12em; text-transform: uppercase; color: #5eead4; }
        .cert-items { display: flex; gap: 10px; flex-wrap: wrap; }
        .cert-items span {
            padding: 9px 16px; border-radius: 999px; font-size: 12.5px; font-weight: 600;
            background: rgba(255,255,255,.08); border: 1px solid rgba(255,255,255,.14);
            transition: background .2s, border-color .2s;
        }
        .cert-items span:hover { background: rgba(45,212,191,.18); border-color: rgba(94,234,212,.5); }

        .erp-note {
            display: grid; grid-template-columns: 1.1fr .9fr; gap: 48px; align-items: center;
            background: linear-gradient(150deg, #effbfa, #e4f6f3); border: 1px solid #cdeae4;
            border-radius: 20px; padding: 48px;
        }
        .erp-note .kicker { font-size: 12px; font-weight: 700; letter-spacing: .14em; text-transform: uppercase; color: var(--accent); margin-bottom: 10px; }
        .erp-note h2 { font-size: clamp(24px, 3vw, 30px); margin-bottom: 12px; letter-spacing: -.02em; }
        .erp-note p { color: #44605c; font-size: 14.5px; line-height: 1.7; margin-bottom: 26px; }
        .flow { display: flex; flex-wrap: wrap; gap: 8px; align-items: center; }
        .flow span {
            padding: 9px 15px; border-radius: 999px; font-size: 12.5px; font-weight: 600;
            background: #fff; border: 1px solid #cdeae4; color: #0b5f5a;
            box-shadow: 0 4px 12px -6px rgba(11,95,90,.25);
        }
        .flow i { font-style: normal; color: var(--accent); font-weight: 700; }

        /* ---------- FOOTER ---------- */
        footer { background: #0c1d2b; color: rgba(255,255,255,.65); }
        .foot-grid { display: grid; grid-template-columns: 1.4fr 1fr 1fr; gap: 40px; padding: 60px 0 44px; }
        .foot-grid h4 { color: #fff; font: 700 14px 'Plus Jakarta Sans', sans-serif; margin-bottom: 14px; letter-spacing: .04em; }
        .foot-grid p, .foot-grid a { font-size: 13.5px; line-height: 1.9; display: block; }
        .foot-grid a:hover { color: #5eead4; }
        .foot-bottom { border-top: 1px solid rgba(255,255,255,.1); padding: 20px 0 28px; font-size: 12px; display: flex; justify-content: space-between; flex-wrap: wrap; gap: 10px; }

        @media (max-width: 920px) {
            .nav-toggle { display: flex; }
            .nav-links {
                display: none; position: absolute; top: 72px; left: 0; right: 0;
                flex-direction: column; gap: 0; background: #fff;
                border-bottom: 1px solid var(--border); box-shadow: 0 16px 30px -18px rgba(12, 29, 43, .35);
            }
            .nav-links.open { display: flex; }
            .nav-links a { padding: 13px 28px; }
            .nav-links a:hover { background: var(--bg); }
            .nav-links a::after { display: none; }
            .nav-links .nav-cta { display: block; font-weight: 700; color: var(--accent); border-top: 1px solid var(--border); }
            .hero-grid { grid-template-columns: 1fr; gap: 44px; padding: 72px 0 88px; }
            .mock-card { margin: 0 auto; }
            .cards, .prod-grid { grid-template-columns: 1fr 1fr; }
            .erp-note { grid-template-columns: 1fr; padding: 32px; }
            .foot-grid { grid-template-columns: 1fr; }
        }
        @media (max-width: 560px) {
            .cards, .prod-grid { grid-template-columns: 1fr; }
            .brand-sub { display: none; }
            .brand-name { font-size: 15px; }
            .brand-icon { width: 36px; height: 36px; font-size: 15px; }
            .btn { padding: 10px 16px; font-size: 13px; }
            .nav-actions .btn-ghost { display: none; }
            .hero-stats { gap: 28px; }
            .section { padding: 64px 0; }
            .wrap { padding: 0 18px; }
            .float-chip { padding: 9px 12px; font-size: 11.5px; }
            .erp-note { padding: 26px; }
        }
    </style>
</head>
<body>

<nav class="nav" id="siteNav">
    <div class="wrap nav-inner">
        <a href="{{ route('home') }}" class="brand">
            <div class="brand-icon">B</div>
            <div>
                <div class="brand-name">Brundo International PLC</div>
                <div class="brand-sub">Ethiopian Spices</div>
            </div>
        </a>
        <div class="nav-links" id="navLinks">
            <a href="#unique">Why Us</a>
            <a href="#products">Products</a>
            <a href="#certifications">Certifications</a>
            <a href="#contact">Contact</a>
            @auth
                <a href="{{ route('dashboard') }}" class="nav-cta">Open Dashboard &rarr;</a>
            @else
                <a href="{{ route('login') }}" class="nav-cta">Sign in / Get Started &rarr;</a>
            @endauth
        </div>
        <div class="nav-actions">
            @auth
                <a href="{{ route('dashboard') }}" class="btn btn-primary">Dashboard</a>
            @else
                <a href="{{ route('login') }}" class="btn btn-ghost">Sign in</a>
                <a href="{{ route('login') }}" class="btn btn-primary">Get Started &rarr;</a>
            @endauth
            <button class="nav-toggle" id="navToggle" aria-label="Menu" aria-expanded="false">&#9776;</button>
        </div>
    </div>
</nav>

<header class="hero">
    <div class="wrap hero-grid">
        <div>
            <span class="eyebrow">Brundo Ethiopian Spices · Mojo Kimem</span>
            <h1>ORGANIC FLAVORS<br>OF <span>ETHIOPIA</span></h1>
            <p>Where coffee grows, extraordinary spices grow. A producer and supply partner working
               directly at origin — delivering Ethiopian spices with consistent quality, full
               traceability, and export readiness.</p>
            <div class="hero-actions">
                @auth
                    <a href="{{ route('dashboard') }}" class="btn btn-light">Open Dashboard &rarr;</a>
                @else
                    <a href="{{ route('login') }}" class="btn btn-light">Get Started &rarr;</a>
                @endauth
                <a href="#products" class="btn btn-outline-light">Explore Products</a>
            </div>
            <div class="hero-stats">
                <div class="hero-stat"><b>10+</b><span>Years of export experience</span></div>
                <div class="hero-stat"><b>{{ $productCount ?? '39' }}</b><span>Products in the price list</span></div>
                <div class="hero-stat"><b>6</b><span>Certifications &amp; registrations</span></div>
            </div>
        </div>

      
    </div>
</header>

@if($productNames->isNotEmpty())
<div class="marquee">
    <div class="marquee-track">
        @foreach($productNames as $name)<span class="mq-item">{{ $name }}</span>@endforeach
        @foreach($productNames as $name)<span class="mq-item">{{ $name }}</span>@endforeach
    </div>
</div>
@endif

<section class="section" id="unique">
    <div class="wrap">
        <div class="section-head reveal">
            <div class="kicker">What makes us unique</div>
            <h2>From Ethiopian soil, with traceability</h2>
            <p>Sourced directly from farming communities across Ethiopia and processed at origin —
               pure, traceable spices built on transparency and supply reliability.</p>
        </div>
        <div class="cards">
            <div class="card reveal" data-delay="0">
                <div class="ico"><svg width="22" height="22" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M12 21c-4-4.5-8-7.5-8-12a8 8 0 1116 0c0 4.5-4 7.5-8 12z"/><circle cx="12" cy="9" r="2.5"/></svg></div>
                <h3>Direct sourcing</h3>
                <p>Ingredients come straight from Ethiopia's growing regions, supporting responsible
                   sourcing and long-term supplier relationships.</p>
            </div>
            <div class="card reveal" data-delay="1">
                <div class="ico"><svg width="22" height="22" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M9 5H7a2 2 0 00-2 2v10a2 2 0 002 2h8a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/></svg></div>
                <h3>Processing at origin</h3>
                <p>Cleaning, blending and packing happen where the spice is grown — pure output,
                   every batch measured for purity and conformity.</p>
            </div>
            <div class="card reveal" data-delay="2">
                <div class="ico"><svg width="22" height="22" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M17 20h5v-2a4 4 0 00-5-3.87M9 20H4v-2a4 4 0 015-3.87M12 12a4 4 0 100-8 4 4 0 000 8z"/></svg></div>
                <h3>Dependable partnerships</h3>
                <p>Supermarkets, hotels and exporters rely on consistent grading, honest pricing
                   and supply they can plan around.</p>
            </div>
        </div>
    </div>
</section>

<section class="section" id="products" style="padding-top:0">
    <div class="wrap">
        <div class="section-head reveal">
            <div class="kicker">Our products</div>
            <h2>Bulk, export &amp; private-label programs</h2>
            <p>Ethiopian spices and botanical ingredients, tracked from raw material to finished
               pack in our operations system.</p>
        </div>
        <div class="prod-grid">
            @forelse ($categories as $category)
                @php
                    $tiles = [['#0b7f78','#2dd4bf'],['#0e7490','#22d3ee'],['#065f46','#34d399'],['#1e3a5f','#60a5fa'],['#155e75','#67e8f9'],['#134e4a','#5eead4'],['#1f2937','#94a3b8'],['#0f766e','#99f6e4']];
                    $c = $tiles[$loop->index % count($tiles)];
                @endphp
                <div class="prod reveal" data-delay="{{ $loop->index % 4 }}">
                    <div class="tile" style="background:linear-gradient(135deg, {{ $c[1] }}, {{ $c[0] }})">{{ strtoupper(substr($category->name, 0, 1)) }}</div>
                    <b>{{ $category->name }}</b>
                    <span>{{ $category->products_count }} item{{ $category->products_count === 1 ? '' : 's' }}</span>
                </div>
            @empty
                @foreach (['Aromatic Herbs', 'Whole Spices', 'Spice Blends', 'Chili Peppers', 'Teas & Infusions', 'Seeds & Grains', 'Dried Vegetables & Roots', 'Gifts & Crafts'] as $group)
                    <div class="prod reveal"><div class="tile" style="background:linear-gradient(135deg,#2dd4bf,#0b7f78)">{{ substr($group, 0, 1) }}</div><b>{{ $group }}</b><span>Brundo selection</span></div>
                @endforeach
            @endforelse
        </div>
    </div>
</section>

<section class="section" id="certifications" style="padding-top:0">
    <div class="wrap">
        <div class="cert-strip reveal">
            <b>Certification &amp; compliance</b>
            <div class="cert-items">
                <span>US FDA Registered</span><span>HACCP</span><span>FSPCA</span>
                <span>EFDA</span><span>FSSC 22000</span><span>ISO 22000</span>
            </div>
        </div>
    </div>
</section>

<section class="section" style="padding-top:0">
    <div class="wrap">
        <div class="erp-note reveal">
            <div>
                <div class="kicker">Operations platform</div>
                <h2>Every birr traced, from the first issue to the final sale</h2>
                <p>Our internal ERP follows each batch: store issues raw material, production records
                   purity and rework, finished goods receive their costed stock, and sales orders
                   carry the full cost chain — overheads, margin and VAT included.</p>
                @auth
                    <a href="{{ route('dashboard') }}" class="btn btn-primary">Open Dashboard &rarr;</a>
                @else
                    <a href="{{ route('login') }}" class="btn btn-primary">Get Started &rarr;</a>
                @endauth
            </div>
            <div class="flow">
                <span>Raw materials</span><i>&rarr;</i><span>Issue</span><i>&rarr;</i>
                <span>Production</span><i>&rarr;</i><span>Finished goods</span><i>&rarr;</i>
                <span>Sales orders</span><i>&rarr;</i><span>Invoice</span>
            </div>
        </div>
    </div>
</section>

<footer id="contact">
    <div class="wrap">
        <div class="foot-grid">
            <div>
                <div class="brand" style="margin-bottom:16px">
                    <div class="brand-icon">B</div>
                    <div>
                        <div class="brand-name" style="color:#fff">Brundo International PLC</div>
                        <div class="brand-sub" style="color:rgba(255,255,255,.5)">Brundo Ethiopian Spices · Mojo Kimem</div>
                    </div>
                </div>
                <p>Organic flavors of Ethiopia — produced at origin,<br>exported with full traceability.</p>
            </div>
            <div>
                <h4>Contact</h4>
                <p>106 Nega City Mall, Kazanchis<br>Kirkos Sub City, Addis Ababa<br>Ethiopia, East Africa</p>
            </div>
            <div>
                <h4>Reach us</h4>
                <a href="mailto:info@mojokimem.com">info@mojokimem.com</a>
                <a href="mailto:sales@brundo.com">sales@brundo.com</a>
                <p>WhatsApp: +251 930 069 961<br>WhatsApp / Telegram: +251 973 637 070</p>
            </div>
        </div>
        <div class="foot-bottom">
            <span>&copy; {{ date('Y') }} Brundo International PLC. All rights reserved.</span>
            <span>US FDA Registered · HACCP · FSPCA · EFDA · FSSC 22000 · ISO 22000</span>
        </div>
    </div>
</footer>

<script>
(function () {
    var toggle = document.getElementById('navToggle');
    var links = document.getElementById('navLinks');
    toggle.addEventListener('click', function () {
        var open = links.classList.toggle('open');
        toggle.setAttribute('aria-expanded', open ? 'true' : 'false');
    });
    links.querySelectorAll('a').forEach(function (a) {
        a.addEventListener('click', function () { links.classList.remove('open'); });
    });

    var nav = document.getElementById('siteNav');
    window.addEventListener('scroll', function () {
        nav.classList.toggle('scrolled', window.scrollY > 12);
    }, { passive: true });

    var io = new IntersectionObserver(function (entries) {
        entries.forEach(function (e) {
            if (e.isIntersecting) { e.target.classList.add('in'); io.unobserve(e.target); }
        });
    }, { threshold: .12 });
    document.querySelectorAll('.reveal').forEach(function (el) { io.observe(el); });
})();
</script>

</body>
</html>
