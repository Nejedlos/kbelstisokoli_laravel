<!DOCTYPE html>
<html>
<head>
    <title>Email Preview | {{ config('app.name') }}</title>
    <style>
        body { font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, Helvetica, Arial, sans-serif; padding: 50px; background: #F8FAFC; color: #0F172A; }
        .container { max-width: 600px; margin: 0 auto; background: white; padding: 40px; border-radius: 12px; box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1), 0 2px 4px -1px rgba(0, 0, 0, 0.06); }
        h1 { border-bottom: 1px solid #E2E8F0; padding-bottom: 20px; margin-bottom: 30px; font-size: 24px; text-align: center; }
        ul { list-style: none; padding: 0; margin: 0; }
        li { margin-bottom: 12px; }
        a { text-decoration: none; color: #0F172A; font-weight: 500; display: block; padding: 15px 20px; background: #F1F5F9; border-radius: 8px; transition: all 0.2s; border: 1px solid transparent; }
        a:hover { background: #E2E8F0; border-color: #CBD5E1; transform: translateY(-1px); }
        .badge { display: inline-block; padding: 2px 8px; font-size: 11px; font-weight: bold; text-transform: uppercase; background: #E11D48; color: white; border-radius: 4px; margin-left: 10px; vertical-align: middle; }
    </style>
</head>
<body>
    <div class="container">
        <h1>Email Design System <span class="badge">v2.0</span></h1>
        <ul>
            @foreach($mailables as $name => $slug)
                <li>
                    <a href="{{ route('dev.mail-preview.show', $slug) }}" target="_blank">
                        {{ $name }}
                    </a>
                </li>
            @endforeach
        </ul>
        <p style="text-align: center; margin-top: 30px; font-size: 13px; color: #64748B;">
            Prostředí: <strong>{{ app()->environment() }}</strong> | Uživatel: <strong>{{ auth()->user()?->name ?? 'Host' }}</strong>
        </p>
    </div>
</body>
</html>
