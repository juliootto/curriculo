<?php
include "config.php";
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

/*if($resultSet){
	echo "<script>alert('Dados enviados com Sucesso!');";
}else{
	echo "<script>alert('Erro ao enviar os Dados!');</script>";
}*/

//Destruindo o objecto statement e fechando a conexão
$stmt = null;
$dsn = null;
