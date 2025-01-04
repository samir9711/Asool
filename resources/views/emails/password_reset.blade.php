<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Password Reset Request</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            margin: 0;
            padding: 0;
            background-color: #f4f4f4;
            color: #333;
        }
        .container {
            width: 100%;
            max-width: 600px;
            margin: 0 auto;
            background-color: #ffffff;
            padding: 20px;
            border-radius: 8px;
            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
        }
        .header {
            background-color: #2b7cf2;
            padding: 10px;
            border-radius: 8px 8px 0 0;
            text-align: center;
            color: #ffffff;
        }
        .header h1 {
            margin: 0;
            font-size: 24px;
        }
        .content {
            margin: 20px 0;
        }
        .content p {
            font-size: 16px;
        }
        .content a {
            display: inline-block;
            padding: 10px 20px;
            margin-top: 20px;
            background-color: #2b7cf2;
            color: white;
            text-decoration: none;
            font-size: 16px;
            border-radius: 5px;
        }
        .content a:hover {
            background-color: #1f66c0;
        }
        .footer {
            font-size: 12px;
            color: #777;
            text-align: center;
            margin-top: 20px;
        }
        .footer a {
            color: #2b7cf2;
            text-decoration: none;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h1>Password Reset Request</h1>
        </div>

        <div class="content">
            <p>Hello, {{ $user->name }}!</p>
            <p>We received a request to reset your password. If you did not request a password reset, please ignore this email.</p>
            <p>To reset your password, click the button below:</p>

            <!-- Reset password link -->
            <a href="{{ url('reset-password/' . $resetToken) }}" target="_blank">Reset My Password</a>
        </div>

        <div class="footer">
            <p>If you need help, visit our <a href="{{ url('/') }}">support page</a>.</p>
            <p>Thank you for using our service.</p>
        </div>
    </div>
</body>
</html>
