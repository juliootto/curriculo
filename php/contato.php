<?php
include "config.php";
require './PHPMailer/src/Exception.php';
require './PHPMailer/src/PHPMailer.php';
require './PHPMailer/src/SMTP.php';

use PHPMailer\PHPMailer\Exception;
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\SMTP;


/***VALIDAÇÃO DOS DADOS RECEBIDOS DO FORMULÁRIO ***/

if($_REQUEST['nome'] == ""){
	echo "O campo Nome não pode ficar vazio.";
	exit;
}

if(!stripos($_REQUEST['email'], "@") || !stripos($_REQUEST['email'],".")){
	echo "O campo E-mail não é válido.";
	exit;
}

if(strlen($_REQUEST['phone']) == ""){
	echo "O campo Telefone não pode ficar vazio.";
	exit;
}

if($_REQUEST['message'] == ""){
	echo "O campo Data de Nascimento não pode ficar vazio.";
	exit;
}
/***FIM DA VALIDAÇÃO DOS DADOS RECEBIDOS DO FORMULÁRIO ***/

try {
	$dsn = new PDO("mysql:host=". HOST . ";port=".PORT.";dbname=" . DBNAME .";user=" . USER . ";password=" . PASSWORD);
} catch (PDOException $e) {
	echo 'A conexão falhou e retorno a seguinte mensagem de erro: ' .$e->getMessage();
}

/***PREPARAÇÃO E INSERÇÃO NO BANCO DE DADOS ***/
$stmt = $dsn->prepare("INSERT INTO 
							Contato(Nome, Email, Telefone, Mensagem)
							VALUES (?, ?, ?, ?)
						");

$resultSet = $stmt->execute([$_REQUEST['nome'], $_REQUEST['email'], $_REQUEST['phone'], $_REQUEST['message']]);

if($resultSet){
	echo "Dados salvos no banco com sucesso";
}else{
	echo "Erro ao enviar os Dadosno banco!";
}

$mail = new PHPMailer(true);
$mensagem_email = "<p>Dados do contato:</p> <p>Nome: ".$_REQUEST['nome']."</p><p>Email: ".$_REQUEST['email']."</p><p>Telefone: ".$_REQUEST['phone']."</p><p>Mensagem:</p><p>".$_REQUEST['message']."</p>";

try {

	$mail->isSMTP();
	$mail->Host       = HOST_EMAIL;
	$mail->SMTPSecure = PHPMailer::ENCRYPTION_SMTPS;
	$mail->Port       = PORT_EMAIL;

	$mail->SMTPAuth   = true;
	$mail->Username   = USER_EMAIL;
	$mail->Password   = PASS_EMAIL;

	$mail->setFrom($_REQUEST['email'], $_REQUEST['nome']);
	$mail->addAddress(USER_EMAIL);

	$mail->isHTML(true);
	$mail->Subject = 'Contato pelo site curriculo';
	$mail->Body    = $mensagem_email;

	$mail->send();
	echo 'Email enviado com sucesso';
} catch (Exception $e) {

	echo "A mensagem não pôde ser enviada. Erro do PHPMailer: {$mail->ErrorInfo}";

}


//Destruindo o objecto statement e fechando a conexão
$stmt = null;
$dsn = null;
