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

	//echo 'teste 1';
	//exit;

	$page = new PageAdmin();

	$page->setTpl("index");

});

$app->get('/admin/login',function(){

	$page = new PageAdmin([
		"header"=>false,
		"footer"=>false
	]);

	$page->setTpl("login");


});

$app->post('/admin/login', function(){

	User::login($POST["login"], $_POST["password"]);

	header("Location: /admin");
	exit;

});

$app->get('/admin/logout', function(){

	User::logout();
	header("Location: /admin/login");
	exit;

});

$app->get("/admin/user", function(){

User::verifyLogin();

User::listAll();

$page = new PageAdmin();

$page->setTpl("users", array(

	"user"=>$user

));

});

$app->get("/admin/user/create", function(){

User::verifyLogin();

$page = new PageAdmin();

$page->setTpl("users-create");

});

$app->get("/admin/user/:iduser/delete", function($iduser){

User::verifyLogin();

});


$app->get("/admin/user/:iduser", function($iduser){

User::verifyLogin();

$user = new User();

$user->get((int)$iduser);

$page = new PageAdmin();

$page->setTpl("users-update",array(

	"user"=>$user->getValues()

	));

});


$app->post("/admin/users/create", function () {

 	User::verifyLogin();

	$user = new User();

 	$_POST["inadmin"] = (isset($_POST["inadmin"])) ? 1 : 0;

 	$_POST['despassword'] = password_hash($_POST["despassword"], PASSWORD_DEFAULT, [

 		"cost"=>12

 	]);

 	$user->setData($_POST);

	$user->save();

	header("Location: /admin/users");
 	exit;

	});

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