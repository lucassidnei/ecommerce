<?php

use \Hcode\PageAdmin;
use \Hcode\Model\User;

//Página inicial do admin
$app->get('/admin', function() {

	$page = new PageAdmin();
	$page->setTpl("index");
});

//Métod q rederiza pagina de login
$app->get('/admin/login',function(){

	$page = new PageAdmin([
		"header"=>false,
		"footer"=>false
	]);
	$page->setTpl("login");


});

//Metodo de login
$app->post('/admin/login', function(){

	User::login($_POST["login"], $_POST["password"]);
	header("Location: /admin");
	exit;

});

//Método para o logout
$app->get('/admin/logout', function(){

	User::logout();
	header("Location: /admin/login");
	exit;

});

//Método para rederizar pagina de recuperação de senha
$app->get("/admin/forgot", function(){

	$page = new PageAdmin([
		"header"=>false,
		"footer"=>false
	]);
	$page->setTpl("forgot");

});

// Método para enviar o e-mail para a função que envia o e-mail para o endereço de recuperação
$app->post("/admin/forgot", function(){

	$user = User::getForgot($_POST["email"]);
	header("Location: /admin/forgot/sent");
	exit;
});

// Método que renderiza a página com a mensagem de e-mail enviado
$app->get("/admin/forgot/sent", function(){

	$page = new PageAdmin([
		"header"=>false,
		"footer"=>false
	]);
	$page->setTpl("forgot-sent");
});

 // Método que válida e encripta o código do e-mail
 $app->get("/admin/forgot/reset", function(){
    
	$user = User::validForgotDecryt($_GET["code"]);

	$page = new PageAdmin([
		"header"=>false,
		"footer"=>false
	]);

	$page->setTpl("forgot-reset", array(

		"name"=>$user["desperson"],
		"code"=>$_GET["code"]

	));
});

$app->post("/admin/forgot/reset", function(){
    
	$Forgot = User::validForgotDecryt($_POST["code"]);

	User::setForgotUsed($Forgot["idrecovery"]);

	$user = new User();

	$user->get((int)$Forgot["iduser"]);

	$password = password_hash($_POST["password"], PASSWORD_DEFAULT, [
		"cost"=>12
	]);

	$user->setPassword($password);

	$page = new PageAdmin([

		"header"=>false,
		"footer"=>false

	]);

	$page->setTpl("forgot-reset-success");

});


?>