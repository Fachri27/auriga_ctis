<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Update Status Kasus</title>
    <style>
        @import url('https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap');

        * { margin: 0; padding: 0; box-sizing: border-box; }

        body {
            background-color: #F5F7F1;
            font-family: 'Poppins', -apple-system, 'Helvetica Neue', Arial, sans-serif;
            color: #0B1E07;
            padding: 40px 16px;
            -webkit-font-smoothing: antialiased;
        }

        .wrap { max-width: 600px; margin: 0 auto; }

        /* ── Hero ── */
        .hero {
            background-color: #0B1E07;
            background-image:
                repeating-linear-gradient(0deg, transparent, transparent 55px, rgba(255,255,255,0.03) 56px),
                repeating-linear-gradient(90deg, transparent, transparent 55px, rgba(255,255,255,0.03) 56px);
            background-size: 56px 56px;
            color: #ffffff;
            padding: 40px 40px 36px;
            border-radius: 10px 10px 0 0;
        }

        .eyebrow {
            font-size: 10px;
            font-weight: 600;
            letter-spacing: 0.2em;
            text-transform: uppercase;
            color: #9BDB4D;
            margin-bottom: 14px;
            display: block;
        }
        .eyebrow .dot {
            display: inline-block;
            width: 6px; height: 6px;
            border-radius: 50%;
            background: #9BDB4D;
            margin-right: 8px;
            vertical-align: middle;
            position: relative;
            top: -1px;
        }

        .hero h1 {
            font-size: 26px;
            font-weight: 700;
            line-height: 1.2;
            letter-spacing: -0.2px;
            color: #ffffff;
        }

        .hero .lede {
            color: rgba(255,255,255,0.7);
            font-size: 13px;
            font-weight: 300;
            margin-top: 12px;
        }

        .accent { height: 3px; background: #9BDB4D; }

        /* ── Body ── */
        .body {
            background: #ffffff;
            padding: 32px 40px 36px;
            border-left: 1px solid #E2E6DA;
            border-right: 1px solid #E2E6DA;
        }

        .metagrid {
            display: flex;
            border: 1px solid #E2E6DA;
            border-radius: 6px;
            overflow: hidden;
            margin-bottom: 26px;
        }
        .metagrid .cell {
            flex: 1;
            padding: 14px 16px;
            border-right: 1px solid #E2E6DA;
        }
        .metagrid .cell:last-child { border-right: none; }
        .metagrid .k {
            font-size: 9px;
            font-weight: 600;
            letter-spacing: 0.18em;
            text-transform: uppercase;
            color: #6b7268;
            margin-bottom: 5px;
        }
        .metagrid .v {
            font-size: 13px;
            font-weight: 600;
            color: #0B1E07;
        }

        .title {
            font-size: 22px;
            font-weight: 700;
            color: #0B1E07;
            line-height: 1.3;
            letter-spacing: -0.2px;
            margin-bottom: 24px;
        }

        .tlabel {
            font-size: 10px;
            font-weight: 600;
            letter-spacing: 0.18em;
            text-transform: uppercase;
            color: #6b7268;
            margin-bottom: 12px;
        }

        /* ── Status transition ── */
        .transition {
            display: flex;
            align-items: stretch;
            gap: 8px;
            margin-bottom: 30px;
        }
        .pill {
            flex: 1;
            border: 1px solid #E2E6DA;
            border-radius: 6px;
            padding: 16px 12px;
            text-align: center;
            background: #F5F7F1;
        }
        .pill.now {
            border: 1.5px solid #9BDB4D;
        }
        .pill .pk {
            font-size: 9px;
            font-weight: 600;
            letter-spacing: 0.18em;
            text-transform: uppercase;
            color: #6b7268;
            margin-bottom: 7px;
        }
        .pill.now .pk { color: #2F6C14; }
        .pill .pv {
            font-size: 15px;
            font-weight: 600;
            color: #0B1E07;
            line-height: 1.25;
        }
        .chev {
            display: flex;
            align-items: center;
            justify-content: center;
            flex-shrink: 0;
            width: 24px;
        }
        .chev::after {
            content: '';
            display: inline-block;
            width: 8px; height: 8px;
            border-top: 2px solid #9BDB4D;
            border-right: 2px solid #9BDB4D;
            transform: rotate(45deg);
        }

        /* ── CTA ── */
        .cta {
            display: inline-block;
            background: #0B1E07;
            color: #ffffff !important;
            text-decoration: none;
            font-size: 12px;
            font-weight: 600;
            letter-spacing: 0.16em;
            text-transform: uppercase;
            padding: 15px 30px 15px 28px;
            border-radius: 6px;
        }
        .cta::after {
            content: '';
            display: inline-block;
            width: 7px; height: 7px;
            border-top: 2px solid #9BDB4D;
            border-right: 2px solid #9BDB4D;
            transform: rotate(45deg);
            margin-left: 12px;
            vertical-align: middle;
            position: relative;
            top: -1px;
        }

        /* ── Footer ── */
        .footer {
            background-color: #0B1E07;
            color: rgba(255,255,255,0.6);
            border-radius: 0 0 10px 10px;
            padding: 28px 40px 30px;
        }
        .footer .org {
            font-size: 15px;
            font-weight: 600;
            color: #ffffff;
            margin-bottom: 3px;
        }
        .footer .org-sub {
            font-size: 9px;
            letter-spacing: 0.22em;
            text-transform: uppercase;
            color: rgba(255,255,255,0.4);
            margin-bottom: 14px;
        }
        .footer .fr {
            height: 1px;
            background: rgba(255,255,255,0.12);
            margin: 16px 0;
        }
        .footer p {
            font-size: 11px;
            line-height: 1.7;
            color: rgba(255,255,255,0.45);
        }
        .footer a {
            color: #9BDB4D;
            text-decoration: underline;
            text-underline-offset: 2px;
        }

        @media (max-width: 520px) {
            .hero, .body, .footer { padding-left: 24px; padding-right: 24px; }
            .hero h1 { font-size: 23px; }
            .title { font-size: 20px; }
            .metagrid { flex-direction: column; }
            .metagrid .cell { border-right: none; border-bottom: 1px solid #E2E6DA; }
            .metagrid .cell:last-child { border-bottom: none; }
        }
    </style>
</head>

<body>
    <div class="wrap">

        <!-- Hero -->
        <div class="hero">
            <span class="eyebrow"><span class="dot"></span>Pembaruan Status</span>
            <h1>Status Kasus yang Anda Ikuti<br>Telah Diperbarui</h1>
            <p class="lede">{{ now()->translatedFormat('l, d F Y — H:i') }} WIB</p>
        </div>
        <div class="accent"></div>

        <!-- Body -->
        <div class="body">

            <div class="metagrid">
                <div class="cell">
                    <div class="k">No. Kasus</div>
                    <div class="v">{{ $case->case_number ?? '—' }}</div>
                </div>
                <div class="cell">
                    <div class="k">Tanggal</div>
                    <div class="v">{{ now()->format('d M Y') }}</div>
                </div>
            </div>

            <div class="title">{{ strip_tags($case->case_title ?? $case->title ?? $case->case_number ?? 'Tanpa Judul') }}</div>

            <div class="tlabel">Perubahan Status</div>
            <div class="transition">
                <div class="pill">
                    <div class="pk">Sebelumnya</div>
                    <div class="pv">{{ $oldStatus }}</div>
                </div>
                <div class="chev"></div>
                <div class="pill now">
                    <div class="pk">Sekarang</div>
                    <div class="pv">{{ $newStatus }}</div>
                </div>
            </div>

            <a href="{{ route('public.verify.case', ['locale' => app()->getLocale(), 'caseNumber' => $case->case_number]) }}" class="cta">
                Lihat Detail Kasus
            </a>

        </div>

        <!-- Footer -->
        <div class="footer">
            <div class="org">Auriga CTIS</div>
            <div class="org-sub">Case Tracking Information System</div>
            <div class="fr"></div>
            <p>
                Email ini dikirim otomatis karena Anda mengikuti perkembangan kasus ini.<br>
                Mohon jangan membalas email ini secara langsung.<br>
                <a href="{{ url('/') }}">Kunjungi situs</a>
            </p>
        </div>

    </div>
</body>

</html>