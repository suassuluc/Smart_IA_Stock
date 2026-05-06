<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Redefinição de senha</title>
</head>
<body style="margin:0;padding:0;background-color:#f4f4f5;font-family:system-ui,-apple-system,BlinkMacSystemFont,'Segoe UI',Roboto,sans-serif;line-height:1.5;color:#27272a;">
<table role="presentation" width="100%" cellspacing="0" cellpadding="0" style="padding:24px 12px;">
    <tr>
        <td align="center">
            <table role="presentation" width="100%" cellspacing="0" cellpadding="0" style="max-width:480px;background:#ffffff;border-radius:12px;border:1px solid #e4e4e7;overflow:hidden;">
                <tr>
                    <td style="padding:28px 24px 12px;text-align:center;border-bottom:1px solid #f4f4f5;">
                        <p style="margin:0;font-size:17px;font-weight:600;color:#18181b;">{{ $appName }}</p>
                    </td>
                </tr>
                <tr>
                    <td style="padding:24px;">
                        @if(! empty($userName))
                            <p style="margin:0 0 12px;font-size:15px;">Olá, {{ $userName }},</p>
                        @else
                            <p style="margin:0 0 12px;font-size:15px;">Olá,</p>
                        @endif
                        <p style="margin:0 0 16px;font-size:15px;">
                            Recebemos um pedido para redefinir a senha da sua conta. Se foi você, use o botão abaixo para escolher uma nova senha.
                        </p>
                        <table role="presentation" cellspacing="0" cellpadding="0" style="margin:24px 0;">
                            <tr>
                                <td style="border-radius:8px;background:#2563eb;">
                                    <a href="{{ $resetUrl }}" style="display:inline-block;padding:12px 24px;font-size:15px;font-weight:600;color:#ffffff;text-decoration:none;">
                                        Redefinir senha
                                    </a>
                                </td>
                            </tr>
                        </table>
                        <p style="margin:16px 0 8px;font-size:13px;color:#71717a;">
                            Este link expira em <strong>{{ $expiresIn }} minutos</strong>. Se não pediu a redefinição, pode ignorar este email — sua senha continuará igual.
                        </p>
                        <p style="margin:16px 0 0;font-size:12px;color:#a1a1aa;word-break:break-all;">
                            Se o botão não funcionar, copie e cole este endereço no navegador:<br>
                            <span style="color:#52525b;">{{ $resetUrl }}</span>
                        </p>
                    </td>
                </tr>
                <tr>
                    <td style="padding:16px 24px;background:#fafafa;font-size:12px;color:#a1a1aa;text-align:center;">
                        Mensagem enviada automaticamente — não responda.
                    </td>
                </tr>
            </table>
        </td>
    </tr>
</table>
</body>
</html>
