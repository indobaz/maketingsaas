<!doctype html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Reset your Pulsify password</title>
</head>
<body style="margin:0; padding:0; background:#ffffff; font-family: Arial, Helvetica, sans-serif; color:#272B41;">
    @php
        $resetUrl = url('/reset-password') . '?token=' . urlencode($token) . '&email=' . urlencode($email);
    @endphp

    <div style="width:100%; padding:32px 16px;">
        <div style="max-width:600px; margin:0 auto; border:1px solid #EEF0F6; border-radius:12px; overflow:hidden;">
            <div style="padding:24px 24px 0 24px;">
                <div style="font-size:22px; font-weight:700; color:#5F63F2; letter-spacing:.2px;">
                    Pulsify
                </div>
            </div>

            <div style="padding:20px 24px 28px 24px;">
                <div style="font-size:16px; line-height:24px; margin-bottom:14px;">
                    Hi there!
                </div>

                <div style="font-size:14px; line-height:22px; margin-bottom:18px;">
                    We received a request to reset your Pulsify password.
                </div>

                <div style="margin: 18px 0 18px 0; text-align:center;">
                    <a href="{{ $resetUrl }}"
                       style="display:inline-block; background:#5F63F2; color:#ffffff; text-decoration:none; padding:12px 18px; border-radius:10px; font-weight:700;">
                        Reset Password
                    </a>
                </div>

                <div style="font-size:13px; line-height:20px; color:#5b627a; margin-top:8px;">
                    This link expires in 60 minutes.
                </div>

                <div style="font-size:13px; line-height:20px; color:#5b627a; margin-top:10px;">
                    If you didn't request this, ignore this email.
                </div>
            </div>

            <div style="padding:16px 24px; background:#FAFBFD; border-top:1px solid #EEF0F6; font-size:12px; line-height:18px; color:#7b82a0;">
                If you didn't request this, ignore this email.
            </div>
        </div>
    </div>
</body>
</html>

