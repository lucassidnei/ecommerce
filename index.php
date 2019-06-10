<?php 

session_start();
require_once("vendor/autoload.php");

use \Slim\Slim;
use \Hcode\Page;
use \Hcode\PageAdmin;
Use \Hcode\Model\User;

$app = new Slim();

$app->config('debug', true);

$app->get('/', function() {

	User::verifyLogin();    
	$page = new Page();
	$page->setTpl("index");
});

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

$app->get("/admin/users", function(){

	User::verifyLogin();
	$users = User::listAll();
	$page = new PageAdmin();
	$page->setTpl("users", array(
		"users"=>$users
));
});

// Método que renderiza o html da página users-create
$app->get("/admin/user/create", function(){

	User::verifyLogin();
	$page = new PageAdmin();
	$page->setTpl("user-create");

});

//Método que renderiz o html da pagina delete
$app->get("/admin/user/:iduser/delete", function($iduser){

	User::verifyLogin();
	$user = new User();
	$user->get((int)$iduser);
	$user->delete();
	header("Location: /admin/users");
	exit;
});

// Método para renderizar página de user-update(editar)
$app->get("/admin/user/:iduser", function($iduser){

	User::verifyLogin();
	$user = new User();
	$user->get((int)$iduser);
	$page = new PageAdmin();
	$page->setTpl("user-update",array(
		"user"=>$user->getValues()
	));
});

// Método que salva o usuário do formulário no db
$app->post("/admin/user/create", function () {

 	User::verifyLogin();
	$user = new User();
	$_POST["inadmin"] = (isset($_POST["inadmin"]))? 1:0;
 	$user->setData($_POST); 
	$user->save();
	header("Location: /admin/users");
 	exit;
	});

//Métedo para salvar o update do usuário no db
$app->post("/admin/user/:iduser", function($iduser){

	User::verifyLogin();
	$user = new User();
	$_POST["inadmin"] = (isset($_POST["inadmin"])) ? 1 : 0;
	$user->get((int)$iduser);
	$user->setData($_POST);
	$user->update();
	header("Location: /admin/users");
	exit;
	});



$app->run();

 ?>