<!doctype html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Your Pulsify verification code</title>
</head>
<body style="margin:0; padding:0; background:#ffffff; font-family: Arial, Helvetica, sans-serif; color:#272B41;">
    <div style="width:100%; padding:32px 16px;">
        <div style="max-width:600px; margin:0 auto; border:1px solid #EEF0F6; border-radius:12px; overflow:hidden;">
            <div style="padding:24px 24px 0 24px;">
                <div style="font-size:22px; font-weight:700; color:#5F63F2; letter-spacing:.2px;">
                    Pulsify
                </div>
            </div>

            <div style="padding:20px 24px 28px 24px;">
                <div style="font-size:16px; line-height:24px; margin-bottom:14px;">
                    Hi {{ $userName }},
                </div>

                <div style="font-size:14px; line-height:22px; margin-bottom:14px;">
                    Your verification code is:
                </div>

                <div style="background:#F4F5F7; border-radius:10px; padding:18px; text-align:center; margin:12px 0 10px 0;">
                    <div style="font-size:32px; font-weight:800; letter-spacing:6px; color:#272B41;">
                        {{ $otp }}
                    </div>
                </div>

                <div style="font-size:13px; line-height:20px; color:#5b627a; margin-top:8px;">
                    This code expires in 15 minutes
                </div>
            </div>

            <div style="padding:16px 24px; background:#FAFBFD; border-top:1px solid #EEF0F6; font-size:12px; line-height:18px; color:#7b82a0;">
                If you didn't create a Pulsify account, ignore this email.
            </div>
        </div>
    </div>
</body>
</html>

