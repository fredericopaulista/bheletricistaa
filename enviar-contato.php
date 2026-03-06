<?php
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Collect post data
    $nome = strip_tags(trim($_POST["nome"]));
    $telefone = strip_tags(trim($_POST["telefone"]));
    $bairro = strip_tags(trim($_POST["bairro"]));
    $servico = strip_tags(trim($_POST["servico"]));
    $mensagem = strip_tags(trim($_POST["mensagem"]));

    // Validations
    if (empty($nome) || empty($telefone)) {
        http_response_code(400);
        echo "Por favor, preencha o nome e o telefone.";
        exit;
    }

require 'libs/PHPMailer/src/Exception.php';
    require 'libs/PHPMailer/src/PHPMailer.php';
    require 'libs/PHPMailer/src/SMTP.php';

    $mail = new PHPMailer\PHPMailer\PHPMailer(true);

    try {
        // ==========================================
        // CONFIGURAÇÕES DO SERVIDOR SMTP
        // ==========================================
        $mail->isSMTP();
        $mail->Host       = 'mail.bheletricista.com.br'; // Ex: smtp.hostgator.com.br, smtp.gmail.com
        $mail->SMTPAuth   = true;
        $mail->Username   = 'noreply@bheletricista.com.br'; // Email de envio (SMTP Username)
        $mail->Password   = 'z_Qbtjyk6YnzjjH!'; // Senha do email
        
        // Criptografia (Geralmente STARTTLS na porta 587 ou SMTPS na porta 465)
        $mail->SMTPSecure = PHPMailer\PHPMailer\PHPMailer::ENCRYPTION_SMTPS; // ou ENCRYPTION_STARTTLS
        $mail->Port       = 465; // ou 587
        
        // Charset para evitar problemas com acentos PT-BR
        $mail->CharSet = 'UTF-8';

        // ==========================================
        // CONFIGURAÇÕES DE REMETENTE E DESTINATÁRIO
        // ==========================================
        // Quem está enviando (Geralmente TEM QUE SER o mesmo email do Username do SMTP)
        $mail->setFrom('noreply@bheletricista.com.br', 'Site - BH Eletricista');
        
        // Para quem vai a mensagem (Email que recebe os contatos)
        $mail->addAddress('contato@bheletricista.com.br', 'Contato BH Eletricista'); 
        
        // Cópia Oculta (BCC)
        $mail->addBCC('fredericopaulista@gmail.com', 'Frederico Paulista');
        
        // Responder para o email ou whatsapp do cliente (opcional)
        // $mail->addReplyTo('cliente@email.com', $nome);

        // ==========================================
        // CONTEÚDO DO E-MAIL
        // ==========================================
        $mail->isHTML(true);
        $mail->Subject = "Novo Contato pelo Site: $nome";
        
        // Corpo do Email em HTML
        $mail->Body    = "
            <h2>Novo Pedido de Atendimento</h2>
            <p><strong>Nome:</strong> {$nome}</p>
            <p><strong>Telefone / WhatsApp:</strong> {$telefone}</p>
            <p><strong>Bairro:</strong> {$bairro}</p>
            <p><strong>Serviço Solicitado:</strong> {$servico}</p>
            <br>
            <p><strong>Descrição do Problema:</strong><br>{$mensagem}</p>
        ";
        
        // Corpo do Email em Texto (para clientes de email que não suportam HTML)
        $mail->AltBody = "Nome: {$nome}\nTelefone: {$telefone}\nBairro: {$bairro}\nServiço: {$servico}\nMensagem: {$mensagem}";

        $mail->send();
        
        http_response_code(200);
        echo "Obrigado! Sua mensagem foi enviada com sucesso.";

    } catch (Exception $e) {
        http_response_code(500);
        // Em produção, você pode não querer exibir $mail->ErrorInfo pro usuário final, mas é útil para testar agora:
        echo "Oops! Erro ao enviar a mensagem. Erro do Mailer: {$mail->ErrorInfo}";
    }

} else {
    http_response_code(403);
    echo "Houve um problema com a sua solicitação, tente novamente.";
}
?>
