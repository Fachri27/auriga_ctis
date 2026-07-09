<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Test Email</title>
    <style>
        body { font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif; line-height: 1.6; color: #333; max-width: 600px; margin: 0 auto; padding: 20px; }
        .card { background: #fff; border: 1px solid #e5e7eb; border-radius: 12px; overflow: hidden; box-shadow: 0 1px 3px rgba(0,0,0,0.1); }
        .header { background: #264c16; color: #fff; padding: 32px 24px; text-align: center; }
        .header h1 { margin: 0; font-size: 24px; }
        .body { padding: 32px 24px; }
        .body p { color: #4b5563; margin-bottom: 16px; }
        .badge { display: inline-block; background: #e8ff47; color: #111; font-size: 10px; font-weight: 700; letter-spacing: 2px; text-transform: uppercase; padding: 4px 12px; border-radius: 100px; margin-bottom: 16px; }
        .footer { background: #f9fafb; padding: 24px; text-align: center; border-top: 1px solid #e5e7eb; font-size: 12px; color: #6b7280; }
    </style>
</head>
<body>
    <div class="card">
        <div class="header">
            <div class="badge">Test Email</div>
            <h1>Email Berhasil Dikirim!</h1>
        </div>
        <div class="body">
            <p>Halo <strong>{{ $name }}</strong>,</p>
            <p>Email ini adalah email percobaan dari sistem Auriga CTIS. Jika Anda menerima email ini, berarti konfigurasi email sudah berfungsi dengan baik.</p>
            <p style="text-align: center; padding: 20px; background: #f0fdf4; border-radius: 8px; font-size: 24px;">✅</p>
            <p>Terima kasih.</p>
        </div>
        <div class="footer">
            <p>&copy; {{ date('Y') }} Auriga CTIS. All rights reserved.</p>
        </div>
    </div>
</body>
</html>
