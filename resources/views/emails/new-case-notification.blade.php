<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Kasus Baru Dipublikasikan</title>
    <style>
        @import url('https://fonts.googleapis.com/css2?family=Playfair+Display:wght@700&family=DM+Sans:wght@400;500;600&display=swap');

        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            background-color: #f0ede8;
            font-family: 'DM Sans', sans-serif;
            color: #1a1a1a;
            padding: 40px 16px;
        }

        .wrapper {
            max-width: 580px;
            margin: 0 auto;
        }

        /* ── Header ── */
        .header {
            background-color: #111;
            border-radius: 16px 16px 0 0;
            padding: 36px 40px 28px;
            position: relative;
            overflow: hidden;
        }

        .header::before {
            content: '';
            position: absolute;
            top: -40px;
            right: -40px;
            width: 180px;
            height: 180px;
            border-radius: 50%;
            background: rgba(255, 255, 255, 0.04);
        }

        .header::after {
            content: '';
            position: absolute;
            bottom: -20px;
            left: 30px;
            width: 100px;
            height: 100px;
            border-radius: 50%;
            background: rgba(255, 255, 255, 0.03);
        }

        .badge {
            display: inline-block;
            background: #e8ff47;
            color: #111;
            font-size: 10px;
            font-weight: 700;
            letter-spacing: 2px;
            text-transform: uppercase;
            padding: 4px 12px;
            border-radius: 100px;
            margin-bottom: 16px;
        }

        .header h1 {
            font-family: 'Playfair Display', serif;
            font-size: 28px;
            color: #fff;
            line-height: 1.2;
            position: relative;
            z-index: 1;
        }

        .header p {
            color: rgba(255, 255, 255, 0.5);
            font-size: 13px;
            margin-top: 8px;
            position: relative;
            z-index: 1;
        }

        /* ── Body ── */
        .body {
            background: #fff;
            padding: 36px 40px;
        }

        .label {
            font-size: 10px;
            font-weight: 700;
            letter-spacing: 2px;
            text-transform: uppercase;
            color: #999;
            margin-bottom: 6px;
        }

        .case-title {
            font-family: 'Playfair Display', serif;
            font-size: 22px;
            color: #111;
            line-height: 1.3;
            margin-bottom: 20px;
            padding-bottom: 20px;
            border-bottom: 1px solid #f0f0f0;
        }

        .description {
            font-size: 14px;
            line-height: 1.7;
            color: #555;
            margin-bottom: 28px;
        }

        /* ── Info strip ── */
        .info-strip {
            background: #f7f5f0;
            border-radius: 10px;
            padding: 16px 20px;
            display: flex;
            gap: 40px;
            margin-bottom: 32px;
        }

        .info-item {
            flex: 1;
            gap: 4px;
        }

        .info-item .info-label {
            font-size: 10px;
            font-weight: 700;
            letter-spacing: 1.5px;
            text-transform: uppercase;
            color: #aaa;
            margin-bottom: 3px;
        }

        .info-item .info-value {
            font-size: 13px;
            font-weight: 600;
            color: #222;
        }

        /* ── CTA Button ── */
        .cta-wrap {
            text-align: center;
            margin-bottom: 32px;
        }

        .cta-btn {
            display: inline-block;
            background: #111;
            color: #e8ff47 !important;
            text-decoration: none;
            font-size: 13px;
            font-weight: 700;
            letter-spacing: 1px;
            text-transform: uppercase;
            padding: 14px 36px;
            border-radius: 100px;
        }

        /* ── Divider ── */
        .divider {
            height: 1px;
            background: #f0f0f0;
            margin: 0 0 24px;
        }

        /* ── Urgency note ── */
        .urgency {
            display: flex;
            align-items: flex-start;
            gap: 12px;
            background: #fff8e1;
            border-left: 3px solid #f5a623;
            border-radius: 0 8px 8px 0;
            padding: 14px 16px;
            margin-bottom: 0;
        }

        .urgency-icon {
            font-size: 18px;
            flex-shrink: 0;
            line-height: 1;
        }

        .urgency-text {
            font-size: 13px;
            color: #7a5c00;
            line-height: 1.5;
        }

        .urgency-text strong {
            display: block;
            font-weight: 600;
            margin-bottom: 2px;
            color: #5c4200;
        }

        /* ── Footer ── */
        .footer {
            background: #f7f5f0;
            border-radius: 0 0 16px 16px;
            padding: 24px 40px;
            text-align: center;
            border-top: 1px solid #ede9e2;
        }

        .footer p {
            font-size: 12px;
            color: #aaa;
            line-height: 1.6;
        }

        .footer a {
            color: #888;
            text-decoration: underline;
        }

        .footer .org {
            font-weight: 700;
            color: #555;
            font-size: 13px;
            margin-bottom: 6px;
        }

        /* ── Accent line ── */
        .accent-line {
            height: 4px;
            background: linear-gradient(90deg, #e8ff47 0%, #111 60%);
            border-radius: 0 0 4px 4px;
            margin-bottom: 0;
        }
    </style>
</head>

<body>
    <div class="wrapper">

        <!-- Header -->
        <div class="header">
            <div class="badge">🔔 Notifikasi Sistem</div>
            <h1>Ada Kasus Baru<br>yang Dipublikasikan</h1>
            <p>{{ now()->translatedFormat('l, d F Y — H:i') }} WIB</p>
        </div>
        <div class="accent-line"></div>

        <!-- Body -->
        <div class="body">

            <p class="label">Judul Kasus</p>
            <div class="case-title">{{ strip_tags($case->title ?? 'Tanpa Judul') }}</div>

            @if(!empty($case->description))
            <p class="label">Ringkasan</p>
            <p class="description">
                {{ \Illuminate\Support\Str::limit(strip_tags($case->description), 280) }}
            </p>
            @endif

            <!-- Info strip -->
            <div class="info-strip">
                <div class="info-item">
                    <div class="info-label">Status</div>
                    <div class="info-value">🟢 Publik</div>
                </div>
                <div class="info-item">
                    <div class="info-label">Tanggal</div>
                    <div class="info-value">{{ now()->format('d M Y') }}</div>
                </div>
            </div>

            <!-- CTA -->
            <div class="cta-wrap">
                <a href="{{ route('public.verify.case', ['locale' => app()->getLocale(), 'caseNumber' => $case->case_number]) }}" class="cta-btn">
                    Lihat Detail Kasus →
                </a>
            </div>

            <div class="divider"></div>

            <!-- Urgency note -->
            <div class="urgency">
                <div class="urgency-icon">⚡</div>
                <div class="urgency-text">
                    <strong>Tindak lanjuti segera</strong>
                    Kasus ini sudah dapat dilihat oleh publik. Pastikan semua data sudah lengkap dan akurat.
                </div>
            </div>

        </div>

        <!-- Footer -->
        <div class="footer">
            <p class="org">CTIS Auriga</p>
            <p>
                Email ini dikirim otomatis oleh sistem.<br>
                Jangan balas email ini langsung.<br>
                <a href="{{ url('/') }}">Kunjungi Website</a>
            </p>
        </div>

    </div>
</body>

</html>