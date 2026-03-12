<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <style>
        body {
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, 'Helvetica Neue', Arial, sans-serif;
            line-height: 1.6;
            color: #333;
            max-width: 600px;
            margin: 0 auto;
        }

        .container {
            background: linear-gradient(to bottom, #f9fafb, #ffffff);
            padding: 40px 20px;
        }

        .email-wrapper {
            background: #ffffff;
            border: 1px solid #e5e7eb;
            border-radius: 8px;
            box-shadow: 0 1px 3px rgba(0, 0, 0, 0.1);
            overflow: hidden;
        }

        .header {
            background: linear-gradient(135deg, #4f46e5, #7c3aed);
            color: white;
            padding: 40px 20px;
            text-align: center;
        }

        .header h1 {
            margin: 0;
            font-size: 28px;
            font-weight: 700;
        }

        .content {
            padding: 40px 30px;
        }

        .greeting {
            font-size: 18px;
            margin-bottom: 20px;
            color: #1f2937;
        }

        .message {
            color: #4b5563;
            margin-bottom: 30px;
            line-height: 1.8;
        }

        .cta-button {
            display: inline-block;
            background: linear-gradient(135deg, #4f46e5, #7c3aed);
            color: white;
            padding: 14px 40px;
            border-radius: 6px;
            text-decoration: none;
            font-weight: 600;
            margin: 30px 0;
            transition: transform 0.2s;
        }

        .cta-button:hover {
            transform: translateY(-2px);
        }

        .verification-link {
            background: #f3f4f6;
            border-left: 4px solid #4f46e5;
            padding: 15px;
            margin: 30px 0;
            border-radius: 4px;
            word-break: break-all;
            font-size: 12px;
            color: #666;
        }

        .footer {
            background: #f9fafb;
            padding: 30px;
            text-align: center;
            border-top: 1px solid #e5e7eb;
            font-size: 12px;
            color: #6b7280;
        }

        .important {
            background: #fef3c7;
            border-left: 4px solid #f59e0b;
            padding: 15px;
            margin: 20px 0;
            border-radius: 4px;
            font-size: 14px;
            color: #92400e;
        }

        .info-box {
            background: #dbeafe;
            border-left: 4px solid #3b82f6;
            padding: 15px;
            margin: 20px 0;
            border-radius: 4px;
            font-size: 14px;
            color: #1e40af;
        }

        .signature {
            margin-top: 30px;
            padding-top: 20px;
            border-top: 1px solid #e5e7eb;
        }
    </style>
</head>

<body>
    <div class="container">
        <div class="email-wrapper">
            <!-- Header -->
            <div class="header">
                <h1>📧 Verifikasi Email Anda</h1>
            </div>

            <!-- Content -->
            <div class="content">
                <div class="greeting">
                    Halo {{ $user->name }},
                </div>

                <div class="message">
                    Terima kasih telah mendaftar di aplikasi kami! Kami senang menyambut Anda.
                    <br><br>
                    Untuk menyelesaikan pendaftaran dan mengaktifkan akun Anda, silakan verifikasi alamat email Anda
                    dengan mengklik tombol di bawah:
                </div>

                <!-- CTA Button -->
                <div style="text-align: center;">
                    <a href="{{ $verificationUrl }}" class="cta-button">
                        ✓ Verifikasi Email Saya
                    </a>
                </div>

                <!-- Alternative Link -->
                <div class="verification-link">
                    <strong>Atau salin dan tempel link di bawah ke browser Anda:</strong>
                    <br><br>
                    {{ $verificationUrl }}
                </div>

                <!-- Info Box -->
                <div class="info-box">
                    <strong>💡 Informasi:</strong>
                    <br>
                    Link verifikasi ini akan berlaku selama 24 jam. Setelah itu, Anda perlu meminta link verifikasi
                    baru.
                </div>

                <!-- Important Notice -->
                <div class="important">
                    <strong>⚠️ Penting:</strong>
                    <br>
                    Jika Anda tidak mendaftar akun ini, abaikan email ini. Akun Anda tidak akan aktif sampai email
                    diverifikasi.
                </div>

                <!-- Additional Help -->
                <div class="message" style="margin-top: 30px; font-size: 14px; color: #6b7280;">
                    <strong>Butuh bantuan?</strong>
                    <br>
                    Jika Anda mengalami masalah saat memverifikasi email, silakan hubungi tim dukungan kami melalui
                    email: support@example.com
                </div>

                <!-- Signature -->
                <div class="signature">
                    <p style="margin: 10px 0; color: #4b5563;">
                        Salam hormat,<br>
                        <strong>Tim Aplikasi</strong>
                    </p>
                </div>
            </div>

            <!-- Footer -->
            <div class="footer">
                <p style="margin: 0 0 10px 0;">
                    © {{ date('Y') }} {{ config('app.name', 'Aplikasi Kami') }}. Hak cipta dilindungi undang-undang.
                </p>
                <p style="margin: 0;">
                    Anda menerima email ini karena mendaftar di platform kami.
                </p>
            </div>
        </div>
    </div>
</body>

</html>