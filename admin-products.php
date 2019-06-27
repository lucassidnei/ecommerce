<?php 

    use \Hcode\PageAdmin;
    use \Hcode\Model\User;
    use \Hcode\Model\Product;


    // Carrega página e lista todos os itens
    $app->get("/admin/products", function(){

        User::verifyLogin();
        $products = Product::listAll();
        $page = new PageAdmin();
        $page->setTpl("products", [
            "products"=>$products
        ]);
    });

    // Carrega página para criar novo registro
    $app->get("/admin/products/create", function(){

        User::verifyLogin();
        $page = new PageAdmin();
        $page->setTpl("products-create");
    });

    // Cria novo registro no banco
    $app->post("/admin/products/create", function(){

        User::verifyLogin();
        $product = new Product();
        $product->setData($_POST);
        $product->save();
        header('Location: /admin/products');
        exit;
    });

     // Carrega página com id do registro para ação
     $app->get("/admin/products/:idproduct", function($idproduct){

        User::verifyLogin();
        $product = new Product();
        $product->get((int)$idproduct);
        $page = new PageAdmin();
        $page->setTpl("products-update", [
            'product'=>$product->getValues()
        ]);
    });

     // Edita item no banco a partir do id do registro
     $app->post("/admin/products/:idproduct", function($idproduct){
          
        User::verifyLogin();
        $product = new Product();
        $product->get((int)$idproduct);
        $product->setData($_POST);
        $product->save();
        $product->setPhoto($_FILES['file']);
        header('Location: /admin/products');
        exit;
    });

    // Deleta um item a partir do id de um registro
    $app->get("/admin/products/:idproduct/delete", function($idproduct){

        User::verifyLogin();
        
        $product = new Product();
        $product->get((int)$idproduct);
        $product->delete();
        
        header('Location: /admin/products');
        exit;
    });






?>