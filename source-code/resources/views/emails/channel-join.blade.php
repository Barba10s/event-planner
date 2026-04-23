<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <style>
        body {
            font-family: Arial, sans-serif;
            line-height: 1.6;
            color: #333;
        }

        .container {
            max-width: 600px;
            margin: 0 auto;
            padding: 20px;
        }

        .header {
            background: #4F46E5;
            color: white;
            padding: 25px;
            border-radius: 8px 8px 0 0;
            text-align: center;
        }

        .content {
            background: #f9f9f9;
            padding: 25px;
            border: 1px solid #e0e0e0;
        }

        .button {
            display: inline-block;
            padding: 14px 28px;
            background: #4F46E5;
            color: white;
            text-decoration: none;
            border-radius: 6px;
            font-weight: bold;
        }

        .button:hover {
            background: #4338CA;
        }

    </style>
</head>
<body>
<div class="container">
    <div class="header">
        <h1>Добро пожаловать!</h1>
    </div>
    <div class="content">
        <p>Здравствуйте, <strong>{{ $member->name }}</strong>!</p>

        <p>Вы успешно присоединились к каналу <strong>{{ $channel->name }}</strong>.</p>

        @if($channel->description)
            <p><em>{{ $channel->description }}</em></p>
        @endif

        <p>Теперь вы можете:</p>
        <ul>
            <li>Участвовать в опросах и голосованиях</li>
            <li>Создавать свои опросы</li>
            <li>Приглашать других участников</li>
        </ul>

        <p style="text-align: center; margin: 25px 0;">
            <a href="{{ config('app.url') }}/channels/{{ $channel->id }}" class="button">
                Перейти в канал
            </a>
        </p>

        <p>Приятного планирования встреч и вечиринок!</p>
    </div>
</div>
</body>
</html>
