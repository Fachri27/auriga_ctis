<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Kasus Baru Dipublikasikan</title>
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

        /* ── Hero (match public header) ── */
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
            font-size: 28px;
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
        .metagrid .v .dot {
            display: inline-block;
            width: 8px; height: 8px;
            border-radius: 50%;
            background: #9BDB4D;
            margin-right: 6px;
            vertical-align: middle;
            position: relative;
            top: -1px;
        }

        .title {
            font-size: 22px;
            font-weight: 700;
            color: #0B1E07;
            line-height: 1.3;
            letter-spacing: -0.2px;
            margin-bottom: 18px;
        }

        .text {
            font-size: 14px;
            line-height: 1.7;
            color: #374151;
            margin-bottom: 28px;
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

        /* ── Note ── */
        .note {
            background: #F5F7F1;
            border-left: 3px solid #9BDB4D;
            padding: 14px 18px;
            border-radius: 0 6px 6px 0;
            margin-top: 28px;
        }
        .note .nk {
            font-size: 10px;
            font-weight: 600;
            letter-spacing: 0.18em;
            text-transform: uppercase;
            color: #2F6C14;
            margin-bottom: 5px;
        }
        .note .nt {
            font-size: 13px;
            line-height: 1.55;
            color: #374151;
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
            .hero h1 { font-size: 24px; }
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
            <span class="eyebrow"><span class="dot"></span>Notifikasi Kasus Baru</span>
            <h1>Kasus Baru Telah Dipublikasikan</h1>
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
                <div class="cell">
                    <div class="k">Status</div>
                    <div class="v"><span class="dot"></span>Publik</div>
                </div>
            </div>

            <div class="title">{{ strip_tags($case->title ?? 'Tanpa Judul') }}</div>

            @if(!empty($case->description))
            <p class="text">{{ \Illuminate\Support\Str::limit(strip_tags($case->description), 280) }}</p>
            @endif

            <a href="{{ route('public.verify.case', ['locale' => app()->getLocale(), 'caseNumber' => $case->case_number]) }}" class="cta">
                Lihat Detail Kasus
            </a>

            <div class="note">
                <div class="nk">Tindak lanjuti segera</div>
                <div class="nt">Kasus ini kini dapat diakses oleh publik. Pastikan seluruh data dan dokumen pendukung telah lengkap serta akurat sebelum ditinjau lebih lanjut.</div>
            </div>

        </div>

        <!-- Footer -->
        <div class="footer">
            <div class="org">greendefender</div>
            <div class="org-sub">Case Tracking Information System</div>
            <div class="fr"></div>
            <p>
                Email ini dikirim otomatis oleh sistem karena Anda berlangganan notifikasi kasus baru.<br>
                Mohon jangan membalas email ini secara langsung.<br>
                <a href="{{ url('/') }}">Kunjungi situs</a>
            </p>
        </div>

    </div>
</body>

</html>