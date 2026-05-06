{{ $appName }}

@if(! empty($userName))
Olá, {{ $userName }},
@else
Olá,
@endif

Recebemos um pedido para redefinir a senha da sua conta.

Abra este link no navegador para escolher uma nova senha:
{{ $resetUrl }}

O link expira em {{ $expiresIn }} minutos.

Se você não pediu esta redefinição, ignore este email.
