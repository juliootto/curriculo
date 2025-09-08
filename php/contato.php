<?php
/**
 * =================================================================
 * SCRIPT DE PROCESSAMENTO DE FORMULÁRIO DE CONTATO
 * =================================================================
 * Responsabilidades:
 * 1. Validar os dados recebidos do formulário.
 * 2. Inserir os dados de forma segura no banco de dados.
 * 3. Enviar um e-mail de notificação formatado.
 * 4. Fornecer feedback claro ao utilizador.
 * =================================================================
 */

// --- CONFIGURAÇÃO INICIAL E INCLUDES ---

// É uma boa prática iniciar sessões caso queira redirecionar o utilizador
// de volta ao formulário com uma mensagem de sucesso ou erro.
// session_start(); 

// Exibe todos os erros (ótimo para desenvolvimento, desative em produção)
ini_set('display_errors', 1);
error_reporting(E_ALL);

// Inclui o arquivo com as constantes de configuração (banco de dados, e-mail)
include "config.php";

// Inclui os arquivos necessários da biblioteca PHPMailer
require './PHPMailer/src/Exception.php';
require './PHPMailer/src/PHPMailer.php';
require './PHPMailer/src/SMTP.php';

// Usa as classes do PHPMailer para evitar ter que escrever o caminho completo
use PHPMailer\PHPMailer\Exception;
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\SMTP;

// --- ETAPA 1: VALIDAÇÃO DOS DADOS DO FORMULÁRIO ---

// Array para armazenar mensagens de erro de validação
$erros = [];

// 1.1. Verifica se o método da requisição é POST
// Usar $_POST é mais seguro do que $_REQUEST, pois aceita dados apenas do corpo da requisição.
if ($_SERVER["REQUEST_METHOD"] == "POST") {

    // 1.2. Validação do campo Nome
    // trim() remove espaços em branco no início e no fim da string
    $nome = trim($_POST['nome']);
    if (empty($nome)) {
        $erros[] = "O campo Nome não pode ficar vazio.";
    }

    // 1.3. Validação do campo E-mail
    $email = trim($_POST['email']);
    if (empty($email)) {
        $erros[] = "O campo E-mail não pode ficar vazio.";
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        // filter_var é a forma mais correta e segura de validar um e-mail em PHP
        $erros[] = "O formato do E-mail é inválido.";
    }
    
    // 1.4. Validação do campo Telefone
    $telefone = trim($_POST['phone']);
    if (empty($telefone)) {
        $erros[] = "O campo Telefone não pode ficar vazio.";
    }
    
    // 1.5. Validação do campo Mensagem
    // Corrigi a mensagem de erro que mencionava "Data de Nascimento"
    $mensagem_usuario = trim($_POST['message']);
    if (empty($mensagem_usuario)) {
        $erros[] = "O campo Mensagem não pode ficar vazio.";
    }

    // 1.6. Se existirem erros, exibe todos e termina o script
    if (!empty($erros)) {
        echo "<strong>Por favor, corrija os seguintes erros:</strong><br>";
        foreach ($erros as $erro) {
            echo "- " . $erro . "<br>";
        }
        exit; // Para a execução do script
    }

} else {
    // Se o script for acedido diretamente via GET, não faz nada.
    echo "Acesso inválido.";
    exit;
}

// --- ETAPA 2: PROCESSAMENTO E ENVIO (BANCO DE DADOS E E-MAIL) ---

// Usamos um bloco try...catch para gerir qualquer exceção que possa ocorrer
// tanto na conexão com o banco de dados quanto no envio do e-mail.
try {
    
    // 2.1. Conexão com o Banco de Dados usando PDO
    // As constantes (HOST, DBNAME, etc.) vêm do seu arquivo config.php
    $dsn = new PDO("mysql:host=". HOST . ";port=".PORT.";dbname=" . DBNAME, USER, PASSWORD);
    // Configura o PDO para lançar exceções em caso de erro, o que permite que o catch as capture.
    $dsn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    // 2.2. Preparação e Inserção no Banco de Dados
    $stmt = $dsn->prepare("INSERT INTO Contato(Nome, Email, Telefone, Mensagem) VALUES (?, ?, ?, ?)");
    
    // Executa a query passando os dados validados. O PDO cuida da segurança contra SQL Injection.
    $stmt->execute([$nome, $email, $telefone, $mensagem_usuario]);


    // --- ETAPA 3: PREPARAÇÃO E ENVIO DO E-MAIL ---
    
    // Sanitiza os dados para exibição segura em HTML (prevenção contra XSS)
    $nome_seguro = htmlspecialchars($nome, ENT_QUOTES, 'UTF-8');
    $email_seguro = htmlspecialchars($email, ENT_QUOTES, 'UTF-8');
    $telefone_seguro = htmlspecialchars($telefone, ENT_QUOTES, 'UTF-8');
    $mensagem_usuario_segura = nl2br(htmlspecialchars($mensagem_usuario, ENT_QUOTES, 'UTF-8')); // nl2br converte quebras de linha em <br>

    // 3.1. Criação do corpo do e-mail em HTML (Heredoc)
    $mensagem_email_html = <<<HTML
    <!DOCTYPE html>
    <html lang="pt-br">
    <body style="margin: 0; padding: 0; font-family: Arial, sans-serif; background-color: #f4f4f4;">
        <table width="100%" border="0" cellpadding="0" cellspacing="0" bgcolor="#f4f4f4">
            <tr>
                <td>
                    <table align="center" border="0" cellpadding="0" cellspacing="0" width="600" style="border-collapse: collapse; margin-top: 20px; margin-bottom: 20px; border: 1px solid #cccccc; border-radius: 8px; background-color: #ffffff;">
                        <tr>
                            <td align="center" bgcolor="#007BFF" style="padding: 20px 0; color: #ffffff; font-size: 24px; font-weight: bold; border-top-left-radius: 8px; border-top-right-radius: 8px;">
                                Novo Contato Recebido
                            </td>
                        </tr>
                        <tr>
                            <td style="padding: 40px 30px;">
                                <p style="margin: 0 0 20px 0; font-size: 16px; color: #555555; line-height: 1.5;">Você recebeu uma nova mensagem através do formulário de contato do site de currículo juliootto:</p>
                                <table width="100%" border="0" cellpadding="5" cellspacing="0" style="border: 1px solid #dddddd;">
                                    <tr style="background-color: #f9f9f9;">
                                        <td width="100" style="padding: 12px; font-weight: bold; color: #333333;">Nome:</td>
                                        <td style="padding: 12px; color: #555555;">$nome_seguro</td>
                                    </tr>
                                    <tr>
                                        <td style="padding: 12px; font-weight: bold; color: #333333;">Email:</td>
                                        <td style="padding: 12px; color: #555555;">$email_seguro</td>
                                    </tr>
                                    <tr style="background-color: #f9f9f9;">
                                        <td style="padding: 12px; font-weight: bold; color: #333333;">Telefone:</td>
                                        <td style="padding: 12px; color: #555555;">$telefone_seguro</td>
                                    </tr>
                                </table>
                                <div style="margin-top: 20px; padding: 15px; border: 1px solid #dddddd; border-radius: 5px; background-color: #fdfdfd;">
                                    <p style="margin: 0 0 10px 0; font-weight: bold; color: #333333;">Mensagem:</p>
                                    <p style="margin: 0; color: #555555; line-height: 1.5;">$mensagem_usuario_segura</p>
                                </div>
                            </td>
                        </tr>
                        <tr>
                            <td bgcolor="#eeeeee" style="padding: 15px 30px; text-align: center; color: #888888; font-size: 12px; border-bottom-left-radius: 8px; border-bottom-right-radius: 8px;">
                                E-mail enviado automaticamente pelo site de currículo juliootto.
                            </td>
                        </tr>
                    </table>
                </td>
            </tr>
        </table>
    </body>
    </html>
HTML;


    // 3.2. Configuração do PHPMailer
    $mail = new PHPMailer(true);
    $mail->CharSet = 'UTF-8'; // Garante a codificação correta de caracteres especiais

    // Configurações do servidor SMTP
    $mail->isSMTP();
    $mail->Host       = HOST_EMAIL;
    $mail->SMTPAuth   = true;
    $mail->Username   = USER_EMAIL;
    $mail->Password   = PASS_EMAIL;
    $mail->SMTPSecure = PHPMailer::ENCRYPTION_SMTPS;
    $mail->Port       = PORT_EMAIL;

    // Configurações dos destinatários e remetente
    // **BOA PRÁTICA**: Envie o e-mail a partir de um endereço seu e use o e-mail do utilizador como "ReplyTo".
    // Isso evita que seu e-mail seja marcado como spam.
    $mail->setFrom(USER_EMAIL, 'Contato do site de currículo juliootto'); // Remetente: seu e-mail
    $mail->addAddress(USER_EMAIL);                 // Destinatário: seu e-mail
    $mail->addReplyTo($email, $nome);              // Endereço de resposta: e-mail do utilizador

    // Conteúdo do e-mail
    $mail->isHTML(true);
    $mail->Subject = 'Nova mensagem do site de currículo juliootto, enviada por: ' . $nome;
    $mail->Body    = $mensagem_email_html;

    // 3.3. Envio do e-mail
    $mail->send();

    // --- ETAPA 4: FEEDBACK DE SUCESSO ---
    echo "Mensagem enviada com sucesso! Obrigado pelo seu contato.";


} catch (PDOException $e) {
    // Captura erros específicos do banco de dados
    // Em um ambiente de produção, grave o erro num arquivo de log em vez de exibi-lo.
    // error_log('Erro de Banco de Dados: ' . $e->getMessage());
    echo "Ocorreu um erro ao salvar os seus dados. Por favor, tente novamente mais tarde.";

} catch (Exception $e) {
    // Captura erros do PHPMailer ou outros erros gerais
    // Em produção, também grave em log.
    // error_log("Erro no envio de e-mail: {$mail->ErrorInfo}");
    echo "Ocorreu um erro ao enviar a sua mensagem. Por favor, tente novamente mais tarde.";

} finally {
    // --- ETAPA 5: LIMPEZA ---
    // Este bloco é sempre executado, tenha ocorrido erro ou não.
    // Garante que a conexão com o banco de dados seja fechada.
    $stmt = null;
    $dsn = null;
}

?>