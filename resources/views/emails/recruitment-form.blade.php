<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
</head>
<body style="font-family: sans-serif; line-height: 1.6; color: #333;">
    <h2 style="color: #c41e3a;">{{ __('recruitment.email.title', ['team' => $teamName]) }}</h2>

    <p><strong>{{ __('recruitment.email.from') }}:</strong> {{ $senderName }} (<a href="mailto:{{ $senderEmail }}">{{ $senderEmail }}</a>)</p>
    <p><strong>{{ __('recruitment.email.team') }}:</strong> {{ $teamName }}</p>

    <h3 style="color: #333; margin-top: 20px;">{{ __('recruitment.email.basic_info') }}:</h3>
    <ul>
        <li><strong>{{ __('recruitment.email.age') }}:</strong> {{ $extraData['age'] ?? __('recruitment.email.not_provided') }}</li>
        <li><strong>{{ __('recruitment.email.height') }}:</strong> {{ $extraData['height'] ? $extraData['height'] . ' cm' : __('recruitment.email.not_provided') }}</li>
        <li><strong>{{ __('recruitment.email.position') }}:</strong> {{ $extraData['position'] ?? __('recruitment.email.not_provided') }}</li>
        <li><strong>{{ __('recruitment.email.level') }}:</strong> {{ $extraData['level'] ?? __('recruitment.email.not_provided') }}</li>
    </ul>

    <hr style="border: 0; border-top: 1px solid #eee; margin: 20px 0;">

    <div style="white-space: pre-wrap;">{{ $messageBody }}</div>

    <hr style="border: 0; border-top: 1px solid #eee; margin: 20px 0;">
    <p style="font-size: 0.85em; color: #777;">{{ __('recruitment.email.footer') }}</p>
</body>
</html>
