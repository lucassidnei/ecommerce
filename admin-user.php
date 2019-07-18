<?php

use \Hcode\PageAdmin;
use \Hcode\Model\User;

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

?>