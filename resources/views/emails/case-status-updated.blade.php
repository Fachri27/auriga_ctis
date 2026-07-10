<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Update Status Kasus</title>
    <style>
        @import url('https://fonts.googleapis.com/css2?family=Playfair+Display:wght@700&family=DM+Sans:wght@400;500;600&display=swap');

        * { margin: 0; padding: 0; box-sizing: border-box; }

        body {
            background-color: #f0ede8;
            font-family: 'DM Sans', sans-serif;
            color: #1a1a1a;
            padding: 40px 16px;
        }

        .wrapper { max-width: 580px; margin: 0 auto; }

        .header {
            background-color: #111;
            border-radius: 16px 16px 0 0;
            padding: 36px 40px 28px;
            position: relative;
            overflow: hidden;
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
            font-size: 26px;
            color: #fff;
            line-height: 1.2;
        }

        .header p {
            color: rgba(255, 255, 255, 0.5);
            font-size: 13px;
            margin-top: 8px;
        }

        .accent-line {
            height: 4px;
            background: linear-gradient(90deg, #e8ff47 0%, #111 60%);
        }

        .body { background: #fff; padding: 36px 40px; }

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
            margin-bottom: 24px;
            padding-bottom: 20px;
            border-bottom: 1px solid #f0f0f0;
        }

        .status-row {
            display: flex;
            align-items: center;
            gap: 16px;
            margin-bottom: 32px;
        }

        .status-pill {
            flex: 1;
            background: #f7f5f0;
            border-radius: 10px;
            padding: 16px 20px;
            text-align: center;
        }

        .status-pill .pill-label {
            font-size: 10px;
            font-weight: 700;
            letter-spacing: 1.5px;
            text-transform: uppercase;
            color: #aaa;
            margin-bottom: 6px;
        }

        .status-pill .pill-value {
            font-size: 15px;
            font-weight: 600;
            color: #222;
        }

        .arrow { color: #999; font-size: 20px; flex-shrink: 0; }

        .cta-wrap { text-align: center; margin-bottom: 8px; }

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

        .footer {
            background: #f7f5f0;
            border-radius: 0 0 16px 16px;
            padding: 24px 40px;
            text-align: center;
            border-top: 1px solid #ede9e2;
        }

        .footer p { font-size: 12px; color: #aaa; line-height: 1.6; }
        .footer a { color: #888; text-decoration: underline; }
        .footer .org { font-weight: 700; color: #555; font-size: 13px; margin-bottom: 6px; }
    </style>
</head>

<body>
    <div class="wrapper">

        <div class="header">
            <div class="badge">🔄 Notifikasi Sistem</div>
            <h1>Update Status pada Kasus<br>yang Anda Ikuti</h1>
            <p>{{ now()->translatedFormat('l, d F Y — H:i') }} WIB</p>
        </div>
        <div class="accent-line"></div>

        <div class="body">

            <p class="label">Judul Kasus</p>
            <div class="case-title">{{ strip_tags($case->title ?? $case->case_number ?? 'Tanpa Judul') }}</div>

            <p class="label">Perubahan Status</p>
            <div class="status-row">
                <div class="status-pill">
                    <div class="pill-label">Sebelumnya</div>
                    <div class="pill-value">{{ $oldStatus }}</div>
                </div>
                <div class="arrow">→</div>
                <div class="status-pill">
                    <div class="pill-label">Sekarang</div>
                    <div class="pill-value">{{ $newStatus }}</div>
                </div>
            </div>

            <div class="cta-wrap">
                <a href="{{ route('public.verify.case', ['locale' => app()->getLocale(), 'caseNumber' => $case->case_number]) }}" class="cta-btn">
                    Lihat Detail Kasus →
                </a>
            </div>

        </div>

        <div class="footer">
            <p class="org">CTIS Auriga</p>
            <p>
                Email ini dikirim otomatis karena Anda mengikuti kasus ini.<br>
                Jangan balas email ini langsung.<br>
                <a href="{{ url('/') }}">Kunjungi Website</a>
            </p>
        </div>

    </div>
</body>

</html>