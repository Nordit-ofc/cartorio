<!doctype html>
<html lang="pt-BR">
<body style="font-family: Arial, sans-serif; color: #0f2e4d; line-height: 1.5;">
    <h2 style="margin: 0 0 12px;">Nova solicitação de proposta — {{ $proposal->protocol }}</h2>
    <table cellpadding="6" style="border-collapse: collapse;">
        <tr><td><strong>Nome</strong></td><td>{{ $proposal->contact_name }}</td></tr>
        <tr><td><strong>Serventia</strong></td><td>{{ $proposal->organization }}</td></tr>
        <tr><td><strong>E-mail</strong></td><td>{{ $proposal->email }}</td></tr>
        <tr><td><strong>Telefone</strong></td><td>{{ $proposal->formattedPhone() ?? '—' }}</td></tr>
        <tr><td><strong>Áreas</strong></td><td>{{ $proposal->interestAreas->pluck('name')->join(', ') ?: '—' }}</td></tr>
        <tr><td valign="top"><strong>Mensagem</strong></td><td>{!! nl2br(e($proposal->message ?? '—')) !!}</td></tr>
        <tr><td><strong>Enviada em</strong></td><td>{{ $proposal->created_at->format('d/m/Y H:i') }} · pelo {{ mb_strtolower($proposal->source->label()) }}</td></tr>
    </table>
    <p style="margin-top: 16px;">
        <a href="{{ route('admin.proposals.show', $proposal) }}">Abrir no painel</a>
        · Responder a este e-mail escreve direto para o solicitante.
    </p>
</body>
</html>
