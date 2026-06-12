<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Akun COMS MBG Anda</title>
</head>
<body style="font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif; background-color: #f4f6f9; padding: 20px; color: #333;">
    <div style="max-width: 600px; margin: 0 auto; background: #ffffff; border-radius: 8px; overflow: hidden; box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);">
        <div style="background: linear-gradient(135deg, #4f46e5, #6366f1); padding: 30px; text-align: center; color: white;">
            <h1 style="margin: 0; font-size: 24px; font-weight: 600;">COMS MBG</h1>
            <p style="margin: 5px 0 0 0; font-size: 14px; opacity: 0.9;">Catering Operations Management System</p>
        </div>
        <div style="padding: 30px; line-height: 1.6;">
            <p style="font-size: 16px; margin-top: 0;">Halo <strong>{{ $name }}</strong>,</p>
            <p>Akun Anda telah berhasil dibuat untuk beroperasi di SPPG berikut:</p>
            
            <div style="background-color: #f3f4f6; border-left: 4px solid #4f46e5; padding: 15px; margin: 20px 0; border-radius: 4px;">
                <p style="margin: 0 0 5px 0;"><strong>SPPG:</strong> {{ $sppgName }}</p>
                <p style="margin: 0 0 5px 0;"><strong>Email Login:</strong> {{ $email }}</p>
                <p style="margin: 0;"><strong>Password Sementara:</strong> <code style="background: #e5e7eb; padding: 2px 6px; border-radius: 4px; font-family: monospace; font-size: 14px;">{{ $password }}</code></p>
            </div>

            <div style="text-align: center; margin: 30px 0;">
                <a href="{{ $loginLink }}" style="background-color: #4f46e5; color: white; padding: 12px 24px; text-decoration: none; border-radius: 5px; font-weight: bold; display: inline-block;">Login ke Sistem</a>
            </div>

            <div style="background-color: #fef3c7; border: 1px solid #fde68a; padding: 15px; border-radius: 4px; margin-top: 20px;">
                <p style="color: #92400e; margin: 0; font-size: 14px;">
                    <strong>⚠️ PENTING:</strong> Segera ganti password Anda setelah login pertama kali untuk keamanan akun Anda.
                </p>
            </div>
        </div>
        <div style="background-color: #f9fafb; padding: 20px; text-align: center; font-size: 12px; color: #6b7280; border-top: 1px solid #e5e7eb;">
            <p style="margin: 0;">Email ini dikirim secara otomatis oleh sistem COMS MBG. Mohon tidak membalas email ini.</p>
        </div>
    </div>
</body>
</html>
