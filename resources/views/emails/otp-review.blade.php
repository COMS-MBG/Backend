<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Kode OTP Verifikasi</title>
</head>
<body style="margin:0; padding:0; background:#f1f5f9; font-family:'Segoe UI',Roboto,sans-serif;">
    <table width="100%" cellpadding="0" cellspacing="0" style="background:#f1f5f9; padding:40px 0;">
        <tr>
            <td align="center">
                <table width="480" cellpadding="0" cellspacing="0" style="background:#ffffff; border-radius:16px; overflow:hidden; box-shadow:0 4px 24px rgba(0,0,0,0.06);">
                    <!-- Header -->
                    <tr>
                        <td style="background:linear-gradient(135deg,#2563eb,#1d4ed8); padding:32px 40px; text-align:center;">
                            <h1 style="margin:0; color:#ffffff; font-size:22px; font-weight:700;">Verifikasi Ulasan Anda</h1>
                            <p style="margin:8px 0 0; color:rgba(255,255,255,0.85); font-size:14px;">Satuan Pelayanan Program Gizi (SPPG)</p>
                        </td>
                    </tr>
                    <!-- Body -->
                    <tr>
                        <td style="padding:40px;">
                            <p style="color:#334155; font-size:15px; line-height:1.6; margin:0 0 8px;">
                                Halo <strong>{{ $recipientName }}</strong>,
                            </p>
                            <p style="color:#64748b; font-size:14px; line-height:1.6; margin:0 0 28px;">
                                Gunakan kode OTP di bawah ini untuk memverifikasi ulasan Anda. Kode ini berlaku selama <strong>10 menit</strong>.
                            </p>
                            <!-- OTP Code -->
                            <div style="text-align:center; margin:0 0 28px;">
                                <div style="display:inline-block; background:#f0f9ff; border:2px dashed #93c5fd; border-radius:12px; padding:20px 40px;">
                                    <span style="font-size:36px; font-weight:800; letter-spacing:12px; color:#1e40af;">{{ $otpCode }}</span>
                                </div>
                            </div>
                            <p style="color:#94a3b8; font-size:13px; line-height:1.6; margin:0; text-align:center;">
                                Jika Anda tidak merasa melakukan permintaan ini, abaikan email ini.
                            </p>
                        </td>
                    </tr>
                    <!-- Footer -->
                    <tr>
                        <td style="background:#f8fafc; padding:20px 40px; text-align:center; border-top:1px solid #e2e8f0;">
                            <p style="color:#94a3b8; font-size:12px; margin:0;">
                                &copy; {{ date('Y') }} COMS MBG - Makan Bergizi Gratis
                            </p>
                        </td>
                    </tr>
                </table>
            </td>
        </tr>
    </table>
</body>
</html>
