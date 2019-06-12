<?php 

session_start();
require_once("vendor/autoload.php");

use \Slim\Slim;
use \Hcode\Page;
use \Hcode\PageAdmin;
Use \Hcode\Model\User;
Use \Hcode\Model\Category;


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

//Métod para listar os usuários
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
//////////////////////////////////////////////////////// categoria

$app->get("/admin/categories", function(){

	User::verifyLogin();
	$categories = Category::listAll();

	$page = new PageAdmin();

	$page->setTpl("categories",[
		'categories'=>$categories
	]);

});

$app->get("/admin/categories/create", function(){

	User::verifyLogin();
	$page = new PageAdmin();

	$page->setTpl("categories-create");

});

$app->post("/admin/categories/create", function(){

	User::verifyLogin();
	$category = new Category();

	$category->setData($_POST);
	$category->save();
	header('Location: /admin/categories');
	exit;

});


// Exclui o registro no banco a partir de um $id
$app->get("/admin/categories/:idcategory/delete", function($idcategory){

	User::verifyLogin();
    $category = new Category();
    $category->get((int)$idcategory);
    $category->delete();
    header('Location: /admin/categories');
    exit;
});


// Envia a tupla de dados para um formulário
$app->get("/admin/categories/:idcategory", function($idcategory){

	User::verifyLogin();
    $category = new Category();
    $category->get((int)$idcategory);
    $page = new PageAdmin();
    $page->setTpl("categories-update", [
        "category"=>$category->getValues()
    ]);
});

$app->post("/admin/categories/:idcategory", function($idcategory){
  
	User::verifyLogin();
    $category = new Category();
    $category->get((int)$idcategory);
    $category->setData($_POST);
    $category->save();
    header('Location: /admin/categories');
    exit;
});

$app->get("/categories/:idcategory", function($idcategory){

	$category = new Category();
	$category->get((int)$idcategory);
	$page = new Page();
	$page->setTpl("category", [
		'category'=>$category->getValues(),
		'products'=>[] 

	]);
});


$app->run();

 ?>
 