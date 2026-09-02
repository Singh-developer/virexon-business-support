<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Agent Business Support</title>
    
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">

    <style>
        /* =========================================
           ROOT VARIABLES - EASY COLOR MANAGEMENT
        ========================================= */
        :root {
            /* User Requested Colors */
            --primary: #6A0DAD;       /* Main Purple */
            --primary-light: #f3ebf8; /* Light Purple */
            --secondary: #e62e04;     /* Red/Orange */
            
            /* Logo & Footer Gradient Colors */
            --logo-grad-start: #2e75e7;
            --logo-grad-end: #03245f;
            
            /* Base Colors */
            --text-dark: #1a1a24;
            --text-muted: #6b7280;
            --bg-white: #ffffff;
            --bg-page: #fafafa;
            --border-color: #e5e7eb;
            --hero-bg: linear-gradient(135deg, #f5f0fa 0%, #e9f0fa 100%);
            
            /* Shadows & Border Radius */
            --shadow-sm: 0 1px 3px rgba(0,0,0,0.1);
            --shadow-md: 0 4px 6px rgba(106, 13, 173, 0.05);
            --shadow-lg: 0 10px 15px -3px rgba(0,0,0,0.1);
            --radius-md: 8px;
            --radius-lg: 16px;
            --radius-xl: 24px;
        }

        /* =========================================
           GLOBAL RESET & TYPOGRAPHY
        ========================================= */
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
            font-family: 'Inter', sans-serif;
        }
        
        body {
            background-color: var(--bg-white);
            color: var(--text-dark);
            line-height: 1.6;
            overflow-x: hidden;
        }

        .container {
            max-width: 1280px;
            margin: 0 auto;
            padding: 0 20px;
        }

        a {
            text-decoration: none;
            color: inherit;
        }

        ul {
            list-style: none;
        }

        /* =========================================
           BUTTONS
        ========================================= */
        .btn {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            padding: 10px 24px;
            font-weight: 500;
            border-radius: var(--radius-md);
            cursor: pointer;
            transition: all 0.3s ease;
            font-size: 15px;
            gap: 8px;
        }

        .btn-primary {
            background-color: var(--primary);
            color: var(--bg-white);
            border: 1px solid var(--primary);
        }

        .btn-primary:hover {
            background-color: var(--text-dark);
            border-color: var(--text-dark);
        }

        .btn-outline {
            background-color: transparent;
            color: var(--text-dark);
            border: 1px solid var(--border-color);
        }

        .btn-outline:hover {
            border-color: var(--primary);
            color: var(--primary);
        }

        /* =========================================
           HEADER / NAVBAR
        ========================================= */
        header {
            background: var(--bg-white);
            padding: 15px 0;
            position: sticky;
            top: 0;
            z-index: 1000;
            box-shadow: var(--shadow-sm);
        }

        .navbar {
            display: flex;
            justify-content: space-between;
            align-items: center;
            position: relative;
        }

        .logo {
            display: flex;
            align-items: center;
            gap: 10px;
            font-size: 20px;
            font-weight: 700;
            line-height: 1.2;
            z-index: 1001;
        }

        .logo-icon {
            font-size: 28px;
            background: -webkit-linear-gradient(var(--logo-grad-start), var(--logo-grad-end));
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
        }

        .logo-text span {
            display: block;
            font-size: 10px;
            color: var(--secondary);
            font-weight: 600;
            letter-spacing: 1px;
            text-transform: uppercase;
        }

        .nav-links {
            display: flex;
            gap: 25px;
            align-items: center;
        }

        .nav-links a {
            font-size: 15px;
            font-weight: 500;
            color: var(--text-dark);
            transition: color 0.3s;
            padding: 5px 0;
        }

        .nav-links a:hover, .nav-links a.active {
            color: var(--primary);
            border-bottom: 2px solid var(--primary);
        }

        .nav-actions {
            display: flex;
            gap: 12px;
        }

        .menu-toggle {
            display: none;
            font-size: 24px;
            color: var(--text-dark);
            cursor: pointer;
            z-index: 1001;
        }

        /* =========================================
           HERO SECTION
        ========================================= */
        .hero-wrapper {
            padding: 20px;
        }

        .hero {
            background: var(--hero-bg);
            border-radius: var(--radius-xl);
            display: flex;
            align-items: center;
            justify-content: space-between;
            padding: 60px 50px;
            position: relative;
            overflow: hidden;
        }

        .hero-content {
            flex: 1;
            max-width: 55%;
            position: relative;
            z-index: 2;
        }

        .badge {
            display: inline-block;
            background-color: var(--bg-white);
            color: var(--secondary);
            padding: 6px 12px;
            border-radius: 20px;
            font-size: 12px;
            font-weight: 600;
            letter-spacing: 0.5px;
            border: 1px solid rgba(230, 46, 4, 0.2);
            margin-bottom: 20px;
        }

        .hero-title {
            font-size: 48px;
            font-weight: 700;
            line-height: 1.1;
            margin-bottom: 20px;
            color: var(--text-dark);
        }

        .hero-title span {
            color: var(--primary);
        }

        .hero-subtitle {
            font-size: 16px;
            color: var(--text-muted);
            margin-bottom: 40px;
            max-width: 90%;
        }

        .hero-stats {
            display: flex;
            background: var(--bg-white);
            padding: 20px;
            border-radius: var(--radius-lg);
            box-shadow: var(--shadow-md);
            margin-bottom: 30px;
            gap: 20px;
        }

        .stat-item {
            flex: 1;
            border-right: 1px solid var(--border-color);
            display: flex;
            flex-direction: column;
            justify-content: center;
        }
        
        .stat-item:last-child {
            border-right: none;
            flex-direction: row;
            align-items: center;
            gap: 10px;
        }

        .stat-item h3 {
            font-size: 20px;
            color: var(--primary);
            font-weight: 700;
        }

        .stat-item p {
            font-size: 12px;
            color: var(--text-muted);
        }

        .stat-item .stat-icon {
            color: var(--primary);
            font-size: 24px;
        }

        .hero-actions {
            display: flex;
            gap: 15px;
            flex-wrap: wrap;
        }

        /* Hero Right - Image & Floating Cards */
        .hero-visual {
            flex: 1;
            position: relative;
            height: 500px;
            display: flex;
            justify-content: flex-end;
            align-items: center;
        }

        .hero-image-bg {
            position: absolute;
            right: 50px;
            bottom: 0;
            width: 400px;
            height: 450px;
            background: url('https://images.unsplash.com/photo-1556761175-5973dc0f32d7?auto=format&fit=crop&q=80') center/cover no-repeat;
            border-radius: var(--radius-lg);
            box-shadow: var(--shadow-lg);
            z-index: 1;
        }
        
        .hero-image-bg::after {
            content: '';
            position: absolute;
            inset: 0;
            background: linear-gradient(to top, rgba(106, 13, 173, 0.4), transparent);
            border-radius: var(--radius-lg);
        }

        .floating-cards {
            position: relative;
            z-index: 3;
            display: flex;
            flex-direction: column;
            gap: 15px;
            margin-right: -20px;
        }

        .float-card {
            background: var(--bg-white);
            padding: 15px 20px;
            border-radius: var(--radius-md);
            box-shadow: var(--shadow-lg);
            display: flex;
            align-items: center;
            gap: 15px;
            width: 260px;
            animation: float 4s ease-in-out infinite;
        }
        
        .float-card:nth-child(2) { animation-delay: 1s; }
        .float-card:nth-child(3) { animation-delay: 2s; }
        .float-card:nth-child(4) { animation-delay: 3s; }

        @keyframes float {
            0% { transform: translateY(0px); }
            50% { transform: translateY(-8px); }
            100% { transform: translateY(0px); }
        }

        .fc-icon {
            width: 40px;
            height: 40px;
            border-radius: 50%;
            background: var(--primary-light);
            color: var(--primary);
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 16px;
        }
        
        .fc-icon.secondary {
            background: rgba(230, 46, 4, 0.1);
            color: var(--secondary);
        }

        .fc-text h4 { font-size: 14px; margin-bottom: 2px; }
        .fc-text p { font-size: 11px; color: var(--text-muted); }

        /* =========================================
           FEATURES SECTION
        ========================================= */
        .features {
            padding: 60px 0 30px 0;
            display: grid;
            grid-template-columns: repeat(4, 1fr);
            gap: 20px;
            border-bottom: 1px solid var(--border-color);
        }

        .feature-box {
            display: flex;
            gap: 15px;
            align-items: flex-start;
        }

        .fb-icon {
            font-size: 24px;
            color: var(--primary);
            background: var(--primary-light);
            padding: 15px;
            border-radius: 50%;
            flex-shrink: 0;
        }

        .fb-text h4 { font-size: 16px; margin-bottom: 5px; }
        .fb-text p { font-size: 13px; color: var(--text-muted); }

        /* =========================================
           HOW IT WORKS SECTION
        ========================================= */
        .how-it-works {
            padding: 50px 0;
            text-align: center;
            border-bottom: 1px solid var(--border-color);
        }

        .section-title {
            font-size: 28px;
            font-weight: 700;
            margin-bottom: 40px;
            color: var(--text-dark);
        }

        .steps-container {
            display: flex;
            justify-content: space-between;
            align-items: flex-start;
        }

        .step {
            flex: 1;
            display: flex;
            flex-direction: column;
            align-items: center;
            padding: 0 10px;
        }

        .step-icon {
            width: 70px;
            height: 70px;
            border-radius: 50%;
            background: var(--bg-white);
            border: 2px solid var(--border-color);
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 24px;
            color: var(--primary);
            margin-bottom: 15px;
            position: relative;
            transition: all 0.3s;
        }

        .step:hover .step-icon {
            border-color: var(--primary);
            box-shadow: 0 0 0 6px var(--primary-light);
        }

        .step-badge {
            position: absolute;
            top: -5px;
            left: -5px;
            background: var(--primary);
            color: white;
            width: 24px;
            height: 24px;
            border-radius: 50%;
            font-size: 12px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-weight: bold;
        }

        .step h4 { font-size: 16px; margin-bottom: 5px; }
        .step p { font-size: 13px; color: var(--text-muted); }

        .step-arrow {
            color: var(--border-color);
            font-size: 20px;
            margin-top: 25px;
        }

        /* =========================================
           BOTTOM SPLIT SECTION
        ========================================= */
        .bottom-section {
            padding: 60px 0;
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 40px;
        }

        .who-can-benefit .section-title { text-align: left; margin-bottom: 30px; }

        .benefit-grid {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 20px;
        }

        .benefit-card {
            border: 1px solid var(--border-color);
            border-radius: var(--radius-md);
            padding: 25px 20px;
            text-align: center;
            transition: all 0.3s;
            background: var(--bg-white);
        }

        .benefit-card:hover {
            border-color: var(--primary);
            box-shadow: var(--shadow-md);
            transform: translateY(-3px);
        }

        .benefit-card i {
            font-size: 32px;
            color: var(--primary);
            margin-bottom: 15px;
        }
        
        .benefit-card:nth-child(2) i { color: var(--secondary); }

        .benefit-card h4 { font-size: 15px; margin-bottom: 8px; }
        .benefit-card p { font-size: 13px; color: var(--text-muted); }

        /* CTA Box */
        .cta-box {
            background: linear-gradient(135deg, var(--logo-grad-start), var(--logo-grad-end));
            border-radius: var(--radius-lg);
            padding: 40px;
            color: var(--bg-white);
            display: flex;
            flex-direction: column;
            justify-content: center;
            position: relative;
            overflow: hidden;
            box-shadow: var(--shadow-lg);
        }

        .cta-box::after {
            content: '';
            position: absolute;
            top: 0;
            right: 0;
            width: 100%;
            height: 100%;
            background: url('https://images.unsplash.com/photo-1556761175-5973dc0f32d7?auto=format&fit=crop&q=80') right/cover;
            opacity: 0.1;
            z-index: 1;
        }

        .cta-content { position: relative; z-index: 2; }
        .cta-box h3 { font-size: 28px; margin-bottom: 15px; }
        .cta-box p { font-size: 15px; color: rgba(255,255,255,0.8); margin-bottom: 30px; line-height: 1.6; }

        .cta-stats {
            display: flex;
            justify-content: space-between;
            border-top: 1px solid rgba(255,255,255,0.2);
            padding-top: 25px;
            flex-wrap: wrap;
            gap: 15px;
        }

        .cta-stat-item {
            display: flex;
            align-items: center;
            gap: 10px;
        }

        .cta-stat-item i { font-size: 24px; color: #4ade80; }
        .cta-stat-item i.text-primary { color: #fff; }
        .cta-stat-item h4 { font-size: 16px; font-weight: 700; margin-bottom: 2px;}
        .cta-stat-item span { font-size: 12px; color: rgba(255,255,255,0.7); display: block; }

        /* =========================================
           STUNNING FOOTER
        ========================================= */
        footer {
            background-color: var(--logo-grad-end); /* Deep dark blue */
            color: #fff;
            padding: 70px 0 30px;
            position: relative;
            margin-top: 60px;
            font-size: 14px;
        }
        
        /* Wavy Top Border Effect */
        .footer-wave {
            position: absolute;
            top: -1px;
            left: 0;
            width: 100%;
            overflow: hidden;
            line-height: 0;
        }

        .footer-wave svg {
            display: block;
            width: calc(100% + 1.3px);
            height: 40px;
        }

        .footer-wave .shape-fill { fill: var(--bg-white); }

        .footer-grid {
            display: grid;
            grid-template-columns: 2fr 1fr 1fr 1.5fr;
            gap: 40px;
            margin-bottom: 40px;
        }

        .footer-logo {
            display: flex;
            align-items: center;
            gap: 10px;
            font-size: 24px;
            font-weight: 700;
            margin-bottom: 15px;
        }

        .footer-logo i { color: var(--primary-light); }
        .footer-logo span { color: var(--primary-light); display: block; font-size: 11px; font-weight: 500; letter-spacing: 2px;}

        .footer-about p {
            color: rgba(255,255,255,0.7);
            margin-bottom: 20px;
            line-height: 1.6;
        }

        .social-icons {
            display: flex;
            gap: 12px;
        }

        .social-icons a {
            width: 36px;
            height: 36px;
            border-radius: 50%;
            background: rgba(255,255,255,0.1);
            display: flex;
            align-items: center;
            justify-content: center;
            color: #fff;
            transition: all 0.3s;
        }

        .social-icons a:hover {
            background: var(--primary);
            transform: translateY(-3px);
        }

        .footer-heading {
            font-size: 18px;
            font-weight: 600;
            margin-bottom: 20px;
            color: #fff;
            position: relative;
            padding-bottom: 10px;
        }

        .footer-heading::after {
            content: '';
            position: absolute;
            left: 0;
            bottom: 0;
            width: 30px;
            height: 2px;
            background: var(--secondary);
        }

        .footer-links li { margin-bottom: 12px; }
        .footer-links a {
            color: rgba(255,255,255,0.7);
            transition: color 0.3s, padding-left 0.3s;
            display: inline-block;
        }

        .footer-links a:hover {
            color: var(--primary-light);
            padding-left: 5px;
        }

        .contact-info li {
            display: flex;
            gap: 15px;
            margin-bottom: 15px;
            color: rgba(255,255,255,0.7);
            align-items: flex-start;
        }
        .contact-info i { color: var(--primary-light); font-size: 18px; margin-top: 3px; }

        .footer-bottom {
            text-align: center;
            padding-top: 25px;
            border-top: 1px solid rgba(255,255,255,0.1);
            color: rgba(255,255,255,0.5);
            font-size: 13px;
        }

        /* =========================================
           RESPONSIVE DESIGN (MOBILE & TABLET)
        ========================================= */
        @media (max-width: 1024px) {
            .hero-title { font-size: 38px; }
            .hero { padding: 40px 30px; }
            .hero-visual { height: 400px; }
            .hero-image-bg { width: 300px; height: 350px; }
            .features { grid-template-columns: repeat(2, 1fr); gap: 30px; }
            .cta-stats { grid-template-columns: 1fr 1fr; }
            .footer-grid { grid-template-columns: 1fr 1fr; }
        }

        @media (max-width: 768px) {
            /* Navbar Mobile */
            .menu-toggle { display: block; }
            .nav-links {
                display: none;
                flex-direction: column;
                position: absolute;
                top: 100%;
                left: 0;
                width: 100%;
                background: var(--bg-white);
                padding: 20px;
                box-shadow: var(--shadow-md);
                gap: 15px;
            }
            .nav-links.active { display: flex; }
            .nav-actions { display: none; }
            .nav-links .nav-actions-mobile {
                display: flex;
                flex-direction: column;
                gap: 10px;
                margin-top: 10px;
                border-top: 1px solid var(--border-color);
                padding-top: 15px;
            }
            
            /* Hide desktop actions in header on mobile */
            header > .container > .nav-actions { display: none; }

            /* Hero Mobile */
            .hero { flex-direction: column; padding: 40px 20px; }
            .hero-content { max-width: 100%; text-align: center; }
            .hero-subtitle { margin: 0 auto 30px auto; }
            .hero-stats {
                flex-direction: column;
                gap: 15px;
                padding: 15px;
            }
            .stat-item { border-right: none; border-bottom: 1px solid var(--border-color); padding-bottom: 10px; align-items: center; }
            .stat-item:last-child { border-bottom: none; padding-bottom: 0; justify-content: center; }
            .hero-actions { justify-content: center; }
            .hero-visual { display: none; } /* Hide heavy visuals on mobile */

            /* Features Mobile */
            .features { grid-template-columns: 1fr; padding: 40px 0; }

            /* How it Works Mobile */
            .steps-container { flex-direction: column; align-items: center; gap: 30px; }
            .step-arrow { transform: rotate(90deg); margin: 0; }

            /* Bottom Section Mobile */
            .bottom-section { grid-template-columns: 1fr; padding: 40px 0; }
            .who-can-benefit .section-title { text-align: center; }
            .benefit-grid { grid-template-columns: 1fr; }
            .cta-stats { flex-direction: column; align-items: flex-start; }

            /* Footer Mobile */
            .footer-grid { grid-template-columns: 1fr; gap: 30px; }
        }
    </style>
</head>
<body>

    <header>
        <div class="container navbar">
            <div class="logo">
                <i class="fa-solid fa-handshake logo-icon"></i>
                <div class="logo-text">
                    AGENT
                    <span>Business Support</span>
                </div>
            </div>
            
            <ul class="nav-links" id="navLinks">
                <li><a href="#" class="active">Home</a></li>
                <li><a href="#">About Us</a></li>
                <!-- <li><a href="#">Benefits</a></li>
                <li><a href="#">How It Works</a></li>
                <li><a href="#">Documents</a></li>
                <li><a href="#">FAQs</a></li> -->
                <li><a href="#">Contact Us</a></li>
                
                <div class="nav-actions-mobile" style="display: none;">
                    <a href="{{ route('login') }}" class="btn btn-outline" style="width: 100%;"><i class="fa-regular fa-user"></i> Login</a>
                    <a href="{{ route('register.agent') }}" class="btn btn-primary" style="width: 100%;"><i class="fa-solid fa-lock"></i> Agent Portal</a>
                </div>
            </ul>

            <div class="nav-actions">
                <a href="{{ route('login') }}" class="btn btn-outline"><i class="fa-regular fa-user"></i> Login</a>
                <a href="{{ route('register.agent') }}" class="btn btn-primary"><i class="fa-solid fa-lock"></i> Agent Register</a>
            </div>

            <i class="fa-solid fa-bars menu-toggle" id="menuToggle"></i>
        </div>
    </header>

    <div class="hero-wrapper container">
        <section class="hero">
            <div class="hero-content">
                <div class="badge">
                    <i class="fa-regular fa-star"></i> SUPPORTING AGENTS. BUILDING SUCCESS.
                </div>
                
                <h1 class="hero-title">
                    Business Support Advance <br>
                    <span>For Your Growth</span>
                </h1>
                
                <p class="hero-subtitle">
                    We empower our selected working agents with interest-free business support advances up to ₹5,00,000 to help you grow your business, achieve more, and earn better.
                </p>

                <div class="hero-stats">
                    <div class="stat-item">
                        <h3>₹5,00,000</h3>
                        <p>Maximum Advance</p>
                    </div>
                    <!-- <div class="stat-item">
                        <h3>0%</h3>
                        <p>Interest</p>
                    </div> -->
                    <div class="stat-item">
                        <h3>60</h3>
                        <p>Months Tenure</p>
                    </div>
                    <div class="stat-item">
                        <i class="fa-solid fa-building-columns stat-icon"></i>
                        <div>
                            <h4 style="font-size: 14px; color: var(--text-dark); margin:0;">Bank Transfer</h4>
                            <p>Disbursement</p>
                        </div>
                    </div>
                </div>

                <div class="hero-actions">
                    <a href="#" class="btn btn-primary">Apply for Advance <i class="fa-solid fa-arrow-right"></i></a>
                    <a href="#" class="btn btn-outline">Know More <i class="fa-solid fa-chevron-down"></i></a>
                </div>
            </div>

            <div class="hero-visual">
                <div class="hero-image-bg"></div>
                <div class="floating-cards">
                    <div class="float-card">
                        <div class="fc-icon secondary"><i class="fa-solid fa-percent"></i></div>
                        <div class="fc-text">
                            <h4>0% Interest</h4>
                            <p>No hidden charges</p>
                        </div>
                    </div>
                    <div class="float-card">
                        <div class="fc-icon"><i class="fa-solid fa-users"></i></div>
                        <div class="fc-text">
                            <h4>Selected Agents Only</h4>
                            <p>Up to 10% of active agents</p>
                        </div>
                    </div>
                    <div class="float-card">
                        <div class="fc-icon"><i class="fa-regular fa-calendar-check"></i></div>
                        <div class="fc-text">
                            <h4>Flexible Tenure</h4>
                            <p>Up to 60 Months</p>
                        </div>
                    </div>
                    <div class="float-card">
                        <div class="fc-icon secondary"><i class="fa-solid fa-building-columns"></i></div>
                        <div class="fc-text">
                            <h4>Direct Bank Transfer</h4>
                            <p>Secure & transparent</p>
                        </div>
                    </div>
                </div>
            </div>
        </section>
    </div>

    <div class="container">
        <section class="features">
            <div class="feature-box">
                <div class="fb-icon"><i class="fa-solid fa-handshake-angle"></i></div>
                <div class="fb-text">
                    <h4>Grow Your Business</h4>
                    <p>Get financial support for marketing, promotion, inventory, and expansion.</p>
                </div>
            </div>
            
            <div class="feature-box">
                <div class="fb-icon"><i class="fa-solid fa-chart-line"></i></div>
                <div class="fb-text">
                    <h4>Increase Your Earnings</h4>
                    <p>Use the advance to achieve higher sales and better commissions.</p>
                </div>
            </div>

            <div class="feature-box">
                <div class="fb-icon"><i class="fa-solid fa-shield-halved"></i></div>
                <div class="fb-text">
                    <h4>Trust & Transparency</h4>
                    <p>Clear terms, no hidden charges, 100% transparent process.</p>
                </div>
            </div>

            <div class="feature-box">
                <div class="fb-icon"><i class="fa-solid fa-user-tie"></i></div>
                <div class="fb-text">
                    <h4>We Are With You</h4>
                    <p>Dedicated support to help you at every step of your journey.</p>
                </div>
            </div>
        </section>
    </div>

    <div class="container">
        <section class="how-it-works">
            <h2 class="section-title">How It Works</h2>
            
            <div class="steps-container">
                <div class="step">
                    <div class="step-icon">
                        <div class="step-badge">1</div>
                        <i class="fa-solid fa-user-plus"></i>
                    </div>
                    <h4>Apply Online</h4>
                    <p>Submit your advance request with basic details</p>
                </div>
                
                <i class="fa-solid fa-arrow-right step-arrow"></i>

                <div class="step">
                    <div class="step-icon">
                        <div class="step-badge">2</div>
                        <i class="fa-solid fa-clipboard-check"></i>
                    </div>
                    <h4>Verification</h4>
                    <p>Our team verifies your documents and eligibility</p>
                </div>

                <i class="fa-solid fa-arrow-right step-arrow"></i>

                <div class="step">
                    <div class="step-icon">
                        <div class="step-badge">3</div>
                        <i class="fa-solid fa-file-signature"></i>
                    </div>
                    <h4>Approval</h4>
                    <p>Advance amount approved as per policy</p>
                </div>

                <i class="fa-solid fa-arrow-right step-arrow"></i>

                <div class="step">
                    <div class="step-icon">
                        <div class="step-badge">4</div>
                        <i class="fa-solid fa-building-columns"></i>
                    </div>
                    <h4>Disbursement</h4>
                    <p>Amount transferred directly to your bank account</p>
                </div>

                <i class="fa-solid fa-arrow-right step-arrow"></i>

                <div class="step">
                    <div class="step-icon">
                        <div class="step-badge">5</div>
                        <i class="fa-solid fa-arrows-rotate"></i>
                    </div>
                    <h4>Adjustment</h4>
                    <p>Repaid automatically through your future commissions</p>
                </div>
            </div>
        </section>
    </div>

    <div class="container">
        <section class="bottom-section">
            
            <div class="who-can-benefit">
                <h2 class="section-title">Who Can Benefit?</h2>
                <div class="benefit-grid">
                    <div class="benefit-card">
                        <i class="fa-solid fa-store"></i>
                        <h4>E-commerce Agents</h4>
                        <p>Expand your online business operations</p>
                    </div>
                    <div class="benefit-card">
                        <i class="fa-solid fa-bullhorn"></i>
                        <h4>Marketing Agents</h4>
                        <p>Increase reach and generate more leads</p>
                    </div>
                    <div class="benefit-card">
                        <i class="fa-solid fa-bag-shopping"></i>
                        <h4>Sales Agents</h4>
                        <p>Boost sales with better resources & support</p>
                    </div>
                    <div class="benefit-card">
                        <i class="fa-solid fa-handshake"></i>
                        <h4>Franchise Partners</h4>
                        <p>Grow your franchise and build your team</p>
                    </div>
                </div>
            </div>

            <div class="cta-box">
                <div class="cta-content">
                    <h3>Your Growth, Our Commitment</h3>
                    <p>We believe in your potential. Our Business Support Advance is designed to give you the financial strength you need to achieve bigger goals.</p>
                    
                    <div class="cta-stats">
                        <div class="cta-stat-item">
                            <i class="fa-solid fa-hand-holding-dollar text-primary"></i>
                            <div>
                                <h4>₹5,00,000</h4>
                                <span>Max Advance</span>
                            </div>
                        </div>
                        <!-- <div class="cta-stat-item">
                            <i class="fa-solid fa-percent text-primary"></i>
                            <div>
                                <h4>0%</h4>
                                <span>Interest</span>
                            </div>
                        </div> -->
                        <div class="cta-stat-item">
                            <i class="fa-regular fa-clock text-primary"></i>
                            <div>
                                <h4>60 Months</h4>
                                <span>Tenure</span>
                            </div>
                        </div>
                        <div class="cta-stat-item">
                            <i class="fa-solid fa-building-columns text-primary"></i>
                            <div>
                                <h4>Bank Transfer</h4>
                                <span>Disbursement</span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

        </section>
    </div>

    <footer>
        <div class="footer-wave">
            <svg data-name="Layer 1" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 1200 120" preserveAspectRatio="none">
                <path d="M321.39,56.44c58-10.79,114.16-30.13,172-41.86,82.39-16.72,168.19-17.73,250.45-.39C823.78,31,906.67,72,985.66,92.83c70.05,18.48,146.53,26.09,214.34,3V0H0V27.35A600.21,600.21,0,0,0,321.39,56.44Z" class="shape-fill"></path>
            </svg>
        </div>

        <div class="container">
            <div class="footer-grid">
                
                <div class="footer-about">
                    <div class="footer-logo">
                        <i class="fa-solid fa-handshake"></i>
                        <div style="line-height: 1.1;">
                            AGENT
                            <span>Support</span>
                        </div>
                    </div>
                    <p>Empowering agents nationwide with zero-interest financial backing. Achieve your sales targets, expand your reach, and secure your financial future with our seamless support network.</p>
                    <div class="social-icons">
                        <a href="#"><i class="fa-brands fa-facebook-f"></i></a>
                        <a href="#"><i class="fa-brands fa-twitter"></i></a>
                        <a href="#"><i class="fa-brands fa-instagram"></i></a>
                        <a href="#"><i class="fa-brands fa-linkedin-in"></i></a>
                    </div>
                </div>

                <div>
                    <h4 class="footer-heading">Quick Links</h4>
                    <ul class="footer-links">
                        <li><a href="#">About Us</a></li>
                        <li><a href="#">How it Works</a></li>
                        <li><a href="#">Eligibility Criteria</a></li>
                        <li><a href="#">Apply for Advance</a></li>
                        <li><a href="#">Login</a></li>
                    </ul>
                </div>

                <div>
                    <h4 class="footer-heading">Support</h4>
                    <ul class="footer-links">
                        <li><a href="#">Help Center / FAQs</a></li>
                        <li><a href="#">Required Documents</a></li>
                        <li><a href="#">Terms & Conditions</a></li>
                        <li><a href="#">Privacy Policy</a></li>
                        <li><a href="#">Report an Issue</a></li>
                    </ul>
                </div>

                <!-- <div>
                    <h4 class="footer-heading">Contact Us</h4>
                    <ul class="contact-info">
                        <li>
                            <i class="fa-solid fa-location-dot"></i>
                            <span>123 Business Support Tower, Financial District, New York, NY 10004</span>
                        </li>
                        <li>
                            <i class="fa-solid fa-phone"></i>
                            <span>+1 (800) 123-4567<br>Mon-Fri 9am - 6pm</span>
                        </li>
                        <li>
                            <i class="fa-solid fa-envelope"></i>
                            <span>support@agentadvance.com</span>
                        </li>
                    </ul>
                </div> -->

            </div>

            <div class="footer-bottom">
                <p>&copy; 2026 Agent Business Support. All Rights Reserved. Designed for growth.</p>
            </div>
        </div>
    </footer>

    <script>
        const menuToggle = document.getElementById('menuToggle');
        const navLinks = document.getElementById('navLinks');
        const mobileActions = document.querySelector('.nav-actions-mobile');

        menuToggle.addEventListener('click', () => {
            navLinks.classList.toggle('active');
            
            // Show mobile actions only when menu is active
            if(navLinks.classList.contains('active')) {
                mobileActions.style.display = 'flex';
                menuToggle.classList.replace('fa-bars', 'fa-xmark');
            } else {
                mobileActions.style.display = 'none';
                menuToggle.classList.replace('fa-xmark', 'fa-bars');
            }
        });
    </script>

</body>
</html>