<?php

use \Hcode\Page;
use \Hcode\Model\Product;
use \Hcode\Model\Category;
use \Hcode\Model\User;

$app->get('/', function() {
 

	$products = Product::listAll();
	$page = new Page();
	$page->setTpl("index", [
		'products'=>$products
	]);
});

$app->get("/categories/:idcategory", function($idcategory){

    User::verifyLogin();
    $category = new Category();
    $category->get((int)$idcategory);
    $page = new Page();
    $page->setTpl("category", [
        "category"=>$category->getValues(),
        "products"=>$category->getProducts() 
    ]);
});



?>