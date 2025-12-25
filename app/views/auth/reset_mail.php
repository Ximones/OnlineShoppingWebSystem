<!DOCTYPE html>
<html>

<head>
    <meta charset="UTF-8">
    <style>
        body {
            font-family: Arial, Helvetica, sans-serif;
            color: #333;
            line-height: 1.6;
            background-color: #f5f5f5;
        }

        .container {
            max-width: 600px;
            margin: 0 auto;
            background-color: #ffffff;
            padding: 40px;
            border-radius: 8px;
            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
        }

        .header {
            text-align: center;
            margin-bottom: 30px;
            border-bottom: 2px solid #0e3d73;
            padding-bottom: 20px;
        }

        .logo {
            font-size: 24px;
            font-weight: bold;
            color: #0e3d73;
        }

        h2 {
            color: #0e3d73;
            margin-top: 20px;
        }

        .content {
            margin: 20px 0;
        }

        .reset-link {
            background-color: #f9f9f9;
            padding: 15px;
            border-left: 4px solid #0e3d73;
            margin: 20px 0;
            word-break: break-all;
            font-size: 12px;
        }

        .warning {
            background-color: #fff3cd;
            border: 1px solid #ffc107;
            padding: 15px;
            border-radius: 5px;
            margin: 20px 0;
            color: #856404;
        }

        .footer {
            text-align: center;
            margin-top: 30px;
            padding-top: 20px;
            border-top: 1px solid #ddd;
            font-size: 12px;
            color: #666;
        }

        .footer-link {
            color: #0e3d73;
            text-decoration: none;
        }
    </style>
</head>

<body>

    <div class="container">

        <div class="header">
            <div class="logo">🚽 Daily Bowls</div>
        </div>

        <h2>Password Reset Request</h2>

        <div class="content">
            <p>Hi <?= htmlspecialchars($user['name']) ?>,</p>

            <p>We received a request to reset the password for your Daily Bowls account. If you did not make this request, you can safely ignore this email.</p>

            <p style="text-align: center;">
                <a href="<?= htmlspecialchars($resetLink) ?>" style="display: inline-block; background-color: #0e3d73; color: #ffffff; padding: 12px 30px; text-decoration: none; border-radius: 5px; margin: 20px 0; font-weight: bold;">Reset Your Password</a>
            </p>

            <p>Or copy and paste this link in your browser:</p>
            <div class="reset-link">
                <?= htmlspecialchars($resetLink) ?>
            </div>

            <div class="warning">
                <strong>⚠️ Security Notice:</strong>
                <ul style="margin: 10px 0; padding-left: 20px;">
                    <li>This link expires in <strong>1 hour</strong></li>
                    <li>Only use this link if you requested the password reset</li>
                    <li>Never share this link with anyone</li>
                    <li>If you didn't request this, ignore this email and your account remains secure</li>
                </ul>
            </div>

            <p>If you have any questions, please contact our support team.</p>

            <p>
                Best regards,<br>
                <strong>The Daily Bowls Team</strong>
            </p>
        </div>

        <div class="footer">
            <p>© <?= date('Y') ?> Daily Bowls. All rights reserved.</p>
            <p>This is an automated email, please do not reply directly to this message.</p>
        </div>

    </div>

</body>

</html>