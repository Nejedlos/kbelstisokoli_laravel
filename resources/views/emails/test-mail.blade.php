<x-mail::message>
# Zkušební e-mail

Toto je automaticky generovaný e-mail z administrace projektu **{{ config('app.name') }}** pro ověření funkčnosti SMTP spojení.

**Zpráva:**
{{ $messageContent }}

**Technické detaily:**
- Čas odeslání: {{ now()->toDateTimeString() }}
- Prostředí: {{ app()->environment() }}
- Mailer: {{ config('mail.default') }}

Díky,<br>
{{ config('app.name') }}
</x-mail::message>
