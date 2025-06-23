<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ $campaign->title }}</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            line-height: 1.6;
            color: #333;
            max-width: 600px;
            margin: 0 auto;
            padding: 20px;
        }
        .header {
            background-color: #f8f9fa;
            padding: 20px;
            text-align: center;
            border-radius: 8px;
            margin-bottom: 20px;
        }
        .content {
            padding: 20px 0;
        }
        .footer {
            margin-top: 30px;
            padding-top: 20px;
            border-top: 1px solid #eee;
            font-size: 12px;
            color: #666;
            text-align: center;
        }
        .btn {
            display: inline-block;
            background-color: #007bff;
            color: white;
            padding: 12px 24px;
            text-decoration: none;
            border-radius: 4px;
            margin: 10px 0;
        }
        .btn:hover {
            background-color: #0056b3;
        }
    </style>
</head>
<body>
    <div class="header">
        <h1>{{ $campaign->title }}</h1>
        @if($sequence)
            <p><small>Step {{ $sequence->step_order }} - Day {{ $sequence->delay_days }}</small></p>
        @endif
    </div>

    <div class="content">
        <p>Hi {{ $user->name }},</p>
        
        {!! $content !!}
    </div>

    <div class="footer">
        <p>Best regards,<br>Your DJ Platform Team</p>
        <p>
            <small>
                You received this email because you're registered as a DJ on our platform.<br>
                <a href="#">Unsubscribe</a> | <a href="#">Update Preferences</a>
            </small>
        </p>
    </div>
</body>
</html>