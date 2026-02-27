<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
</head>
<body style="font-family: sans-serif; line-height: 1.6; color: #333;">
    <h2 style="color: #c41e3a;">Žádost o nábor do týmu {{ $teamName }}</h2>

    <p><strong>Od:</strong> {{ $senderName }} (<a href="mailto:{{ $senderEmail }}">{{ $senderEmail }}</a>)</p>
    <p><strong>Tým:</strong> {{ $teamName }}</p>

    <h3 style="color: #333; margin-top: 20px;">Základní údaje:</h3>
    <ul>
        <li><strong>Věk:</strong> {{ $extraData['age'] ?? 'neuvedeno' }}</li>
        <li><strong>Výška:</strong> {{ $extraData['height'] ? $extraData['height'] . ' cm' : 'neuvedeno' }}</li>
        <li><strong>Pozice:</strong> {{ $extraData['position'] ?? 'neuvedeno' }}</li>
        <li><strong>Zkušenosti:</strong> {{ $extraData['level'] ?? 'neuvedeno' }}</li>
    </ul>

    <hr style="border: 0; border-top: 1px solid #eee; margin: 20px 0;">

    <div style="white-space: pre-wrap;">{{ $messageBody }}</div>

    <hr style="border: 0; border-top: 1px solid #eee; margin: 20px 0;">
    <p style="font-size: 0.85em; color: #777;">Tato zpráva byla odeslána z náborového formuláře webu Kbelští sokoli.</p>
</body>
</html>
