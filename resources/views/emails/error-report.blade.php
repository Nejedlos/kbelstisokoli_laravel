<x-mail::message>
# 🚨 Application Error Report

**App:** {{ $report['app']['name'] ?? config('app.name') }}
**Environment:** {{ $report['app']['env'] ?? app()->environment() }}
**Time:** {{ $report['timestamp'] ?? now()->toDateTimeString() }}
**URL:** {{ $report['request']['url'] ?? '-' }}
**Method:** {{ $report['request']['method'] ?? '-' }}
**IP:** {{ $report['request']['ip'] ?? '-' }}

---

## Exception
- **Class:** `{{ $report['exception']['class'] ?? '' }}`
- **Message:** `{{ $report['exception']['message'] ?? '' }}`
- **Code:** `{{ $report['exception']['code'] ?? '' }}`
- **File:** `{{ $report['exception']['file'] ?? '' }}`:{{ $report['exception']['line'] ?? '' }}

### Trace
```
{{ $report['exception']['trace'] ?? '' }}
```

---

## Authenticated User
```
{{ json_encode($report['user'] ?? null, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE) }}
```

---

## Request
```
{{ json_encode($report['request'] ?? null, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE) }}
```

---

## Headers
```
{{ json_encode($report['headers'] ?? null, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE) }}
```

---

## Server
```
{{ json_encode($report['server'] ?? null, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE) }}
```

</x-mail::message>
