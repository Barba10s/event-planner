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
            background: #10B981;
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
            background: #10B981;
            color: white;
            text-decoration: none;
            border-radius: 6px;
            font-weight: bold;
        }

        .button:hover {
            background: #059669;
        }

        .options {
            background: white;
            padding: 15px;
            border-radius: 4px;
            margin: 20px 0;
        }

        .option {
            padding: 10px 0;
            border-bottom: 1px solid #e0e0e0;
        }

        .option:last-child {
            border-bottom: none;
        }

        .meta {
            color: #666;
            font-size: 14px;
            margin-top: 15px;
        }
    </style>
</head>
<body>
<div class="container">
    <div class="header">
        <h1>Новый опрос!</h1>
    </div>
    <div class="content">
        <p>Здравствуйте, <strong>{{ $recipient->name }}</strong>!</p>

        <p><strong>{{ $poll->creator->name }}</strong> создал новый опрос в канале
            <strong>{{ $poll->channel->name }}</strong>:</p>

        <h3 style="margin: 20px 0;">{{ $poll->question }}</h3>

        @if($poll->description)
            <p><em>{{ $poll->description }}</em></p>
        @endif

        <div class="options">
            <strong>Варианты ответов:</strong>
            @foreach($poll->options as $option)
                <div class="option">• {{ $option->text }}</div>
            @endforeach
        </div>

        @if($poll->ends_at)
            <p class="meta">Голосование завершится: <strong>{{ $poll->ends_at->format('d.m.Y H:i') }}</strong></p>
        @else
            <p class="meta">Голосование бессрочное</p>
        @endif

        <p style="text-align: center; margin: 25px 0;">
            <a href="{{ config('app.url') }}/channels/{{ $poll->channel_id }}/polls/{{ $poll->id }}" class="button">
                Проголосовать
            </a>
        </p>

    </div>
</div>
</body>
</html>
