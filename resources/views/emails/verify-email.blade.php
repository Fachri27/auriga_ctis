<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Verifikasi Email Anda</title>
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

        .greeting {
            font-size: 18px;
            font-weight: 600;
            color: #0B1E07;
            margin-bottom: 18px;
        }

        .text {
            font-size: 14px;
            line-height: 1.7;
            color: #374151;
            margin-bottom: 26px;
        }

        /* ── CTA ── */
        .ctawrap { margin-bottom: 24px; }
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

        /* ── Alt link ── */
        .altlink {
            background: #F5F7F1;
            border: 1px solid #E2E6DA;
            border-radius: 6px;
            padding: 14px 16px;
            margin-bottom: 24px;
        }
        .altlink .k {
            font-size: 10px;
            font-weight: 600;
            letter-spacing: 0.12em;
            text-transform: uppercase;
            color: #6b7268;
            margin-bottom: 6px;
        }
        .altlink .v {
            font-size: 12px;
            color: #0B1E07;
            word-break: break-all;
        }

        /* ── Notes ── */
        .note {
            background: #F5F7F1;
            border-left: 3px solid #9BDB4D;
            padding: 14px 18px;
            border-radius: 0 6px 6px 0;
            margin-bottom: 14px;
        }
        .note.warn { border-left-color: #6b7268; }
        .note .nk {
            font-size: 10px;
            font-weight: 600;
            letter-spacing: 0.18em;
            text-transform: uppercase;
            color: #2F6C14;
            margin-bottom: 5px;
        }
        .note.warn .nk { color: #6b7268; }
        .note .nt {
            font-size: 13px;
            line-height: 1.55;
            color: #374151;
        }

        .help {
            font-size: 13px;
            color: #6b7268;
            line-height: 1.6;
            margin: 24px 0 6px;
        }
        .help a { color: #0B1E07; text-decoration: underline; text-underline-offset: 2px; }

        .sig {
            margin-top: 26px;
            padding-top: 20px;
            border-top: 1px solid #E2E6DA;
            font-size: 14px;
            color: #374151;
            line-height: 1.6;
        }
        .sig strong { color: #0B1E07; font-weight: 600; }

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
        }
    </style>
</head>

<body>
    <div class="wrap">

        <!-- Hero -->
        <div class="hero">
            <span class="eyebrow"><span class="dot"></span>Verifikasi Email</span>
            <h1>Verifikasi Alamat Email Anda</h1>
            <p class="lede">Satu langkah lagi untuk mengaktifkan akun Anda.</p>
        </div>
        <div class="accent"></div>

        <!-- Body -->
        <div class="body">

            <p class="greeting">Halo {{ $user->name }},</p>

            <p class="text">
                Terima kasih telah mendaftar di greendefender. Untuk menyelesaikan pendaftaran dan mengaktifkan akun Anda, silakan verifikasi alamat email dengan menekan tombol di bawah.
            </p>

            <div class="ctawrap">
                <a href="{{ $verificationUrl }}" class="cta">Verifikasi Email Saya</a>
            </div>

            <div class="altlink">
                <div class="k">Atau salin dan tempel tautan ini ke browser Anda</div>
                <div class="v">{{ $verificationUrl }}</div>
            </div>

            <div class="note">
                <div class="nk">Informasi</div>
                <div class="nt">Tautan verifikasi ini berlaku selama 1 jam. Jika sudah kedaluwarsa, Anda dapat meminta tautan verifikasi baru dari halaman login.</div>
            </div>

            <div class="note warn">
                <div class="nk">Keamanan</div>
                <div class="nt">Jika Anda tidak merasa mendaftar akun ini, abaikan email ini. Akun tidak akan aktif sampai email diverifikasi.</div>
            </div>

            <p class="help">
                Butuh bantuan? Hubungi tim dukungan kami di <a href="mailto:support@auriga.or.id">support@auriga.or.id</a>.
            </p>

            <div class="sig">
                Salam hangat,<br>
                <strong>Tim greendefender</strong>
            </div>

        </div>

        <!-- Footer -->
        <div class="footer">
            <div class="org">greendefender</div>
            <div class="org-sub">Case Tracking Information System</div>
            <div class="fr"></div>
            <p>
                Anda menerima email ini karena mendaftar di platform greendefender.<br>
                Mohon jangan membalas email ini secara langsung.<br>
                <a href="{{ url('/') }}">Kunjungi situs</a>
            </p>
        </div>

    </div>
</body>

</html>