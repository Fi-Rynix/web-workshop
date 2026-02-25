<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Verifikasi OTP - Purple Admin</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        body {
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, 'Helvetica Neue', Arial, sans-serif;
            line-height: 1.6;
            color: #333;
            background-color: #f5f5f5;
        }
        .email-wrapper {
            max-width: 600px;
            margin: 0 auto;
            background-color: #ffffff;
        }
        .email-header {
            background: linear-gradient(135deg, #b66dff 0%, #8b5cf6 100%);
            padding: 40px 30px;
            text-align: center;
            color: #ffffff;
        }
        .email-header h1 {
            font-size: 28px;
            font-weight: 600;
            margin: 0;
            letter-spacing: -0.5px;
        }
        .email-content {
            padding: 40px 30px;
        }
        .greeting {
            font-size: 16px;
            font-weight: 500;
            color: #333;
            margin-bottom: 20px;
        }
        .message {
            font-size: 15px;
            color: #555;
            margin-bottom: 30px;
            line-height: 1.8;
        }
        .otp-box {
            background-color: #f8f9fa;
            border: 1px solid #e0e0e0;
            border-left: 4px solid #b66dff;
            border-radius: 4px;
            padding: 30px;
            margin: 30px 0;
            text-align: center;
        }
        .otp-label {
            font-size: 12px;
            color: #999;
            text-transform: uppercase;
            letter-spacing: 1.5px;
            font-weight: 600;
            margin-bottom: 15px;
        }
        .otp-code {
            font-size: 42px;
            font-weight: bold;
            color: #b66dff;
            font-family: 'Courier New', monospace;
            letter-spacing: 8px;
            margin: 15px 0;
            word-spacing: 10px;
        }
        .otp-validity {
            font-size: 13px;
            color: #999;
            margin-top: 15px;
            font-style: italic;
        }
        .warning-box {
            background-color: #fffbeb;
            border: 1px solid #fcd34d;
            border-radius: 4px;
            padding: 15px 20px;
            margin: 25px 0;
            font-size: 14px;
            color: #92400e;
        }
        .warning-box strong {
            display: block;
            margin-bottom: 8px;
            color: #b45309;
        }
        .divider {
            height: 1px;
            background-color: #e0e0e0;
            margin: 30px 0;
        }
        .info-list {
            font-size: 13px;
            color: #666;
            line-height: 2;
            margin: 20px 0;
        }
        .info-list li {
            list-style: none;
            padding-left: 0;
        }
        .info-list li:before {
            content: "✓ ";
            color: #b66dff;
            font-weight: bold;
            margin-right: 8px;
        }
        .email-footer {
            background-color: #f8f9fa;
            padding: 25px 30px;
            border-top: 1px solid #e0e0e0;
            text-align: center;
            font-size: 12px;
            color: #666;
        }
        .footer-text {
            margin-bottom: 15px;
        }
        .footer-links a {
            color: #b66dff;
            text-decoration: none;
            margin: 0 8px;
        }
        .footer-links a:hover {
            text-decoration: underline;
        }
        .copyright {
            font-size: 11px;
            color: #999;
            margin-top: 15px;
        }
    </style>
</head>
<body>
    <div class="email-wrapper">
        <!-- Header -->
        <div class="email-header">
            <h1>Verifikasi Akun Anda</h1>
        </div>

        <!-- Content -->
        <div class="email-content">
            <div class="greeting">
                Halo {{ $userName ?? 'Pengguna' }},
            </div>

            <div class="message">
                {{ $mailMessage ?? 'Terima kasih telah mendaftar. Untuk menyelesaikan proses verifikasi akun Anda, silakan gunakan kode OTP di bawah ini.' }}
            </div>

            <!-- OTP Section -->
            <div class="otp-box">
                <div class="otp-label">Kode Verifikasi OTP</div>
                <div class="otp-code">{{ $otp ?? '------' }}</div>
                <div class="otp-validity">Kode berlaku selama 5 menit</div>
            </div>

            <!-- Warning -->
            <div class="warning-box">
                <strong>⚠️ Keamanan Penting</strong>
                Jangan bagikan kode OTP ini kepada siapapun. Tim kami tidak akan pernah meminta kode OTP.
            </div>

            <div class="message">
                Jika Anda tidak melakukan permintaan ini, silakan abaikan email ini atau hubungi tim dukungan kami.
            </div>

            <div class="divider"></div>

            <div class="info-list">
                <strong style="color: #333;">Informasi Penting:</strong>
                <ul>
                    <li>Jangan bagikan OTP dengan siapapun</li>
                    <li>OTP hanya berlaku untuk 5 menit</li>
                    <li>Hubungi support jika ada kendala</li>
                </ul>
            </div>
        </div>

        <!-- Footer -->
        <div class="email-footer">
            <div class="footer-text">
                © {{ date('Y') }} Purple Admin. Semua hak dilindungi.
            </div>
            <div class="footer-links">
                <a href="#">Kebijakan Privasi</a> | 
                <a href="#">Syarat & Ketentuan</a> | 
                <a href="#">Hubungi Kami</a>
            </div>
            <div class="copyright">
                Anda menerima email ini karena memiliki akun di platform kami.
            </div>
        </div>
    </div>
</body>
</html>
