<?php

//colocar os namespace das classes utilizadas
use \Hcode\PageAdmin;
use \Hcode\Model\User;
use \Hcode\Model\Product;

$app->get("/admin/products", function(){ //rota para listar todos os produtos

    //verificar se o usuario esta logado
    User::verifyLogin();

    //variavel para listar todos os produtos
    $products = Product::listAll(); //metodo para listar todo os produtos cadastrados

    $page = new PageAdmin(); //classe do painel administrativo
    
    //template a ser chamado
    $page->setTpl("products", [
        //lista de produtos
        "products"=>$products
    ]);

});

//rota para criar os produtos
$app->get("/admin/products/create", function(){

    //verificar se o usuario esta logado
    User::verifyLogin();

    $page = new PageAdmin(); //classe do painel administrativo

    //template a ser carregado prodcuts-create/html
    $page->setTpl("products-create");
});

//rota para salvar os produtos
$app->post("/admin/products/create", function(){

     //verificar se o usuario esta logado
     User::verifyLogin();

     //criar um novo produto
     $product = new Product();

     //carregar o dado que vem do formulario
     $product->setData($_POST);

     //metodo para salvar o produto no BD
     $product->save();

     //$product->setPhoto($_FILES["file"]);

     //redirecionar para a pagina de produtos
     header("Location: /admin/products");
     exit;
});


//rota para editar o produto, lembrando de passar o ID do item do banco
$app->get("/admin/products/:idproduct", function($idproduct){

     //verificar se o usuario esta logado
     User::verifyLogin();

     //carrega a lista de produtos
     $product = new Product();
     
     //pegar o produto que foi passado na url
     $product->get((int)$idproduct);

     

     //carrega o HTML para a edicao da categoria
     $page = new PageAdmin();
     $page->setTpl("products-update", [
         'product'=>$product->getValues()
     ]);

});

//rota para salvar a edicao do produto
$app->post("/admin/products/:idproduct", function($idproduct){

    //verificar se o usuario esta logado
    User::verifyLogin();

     //carrega a lista de produtos
     $product = new Product();
     
     //pegar o produto que foi passado na url
     $product->get((int)$idproduct);

     //carrega os dados atuais
     $product->setData($_POST);

     //salvar a edicao do produto
     $product->save();

     //realizar o upload do arquivo
     $product->setPhoto($_FILES["file"]); //metodo para salvar a foto

     //redirecionar para a lista de produtos
     header('Location: /admin/products');
     exit;
});

//rota para excluir o produto
$app->get("/admin/products/:idproduct/delete", function($idproduct){

    //verificar se o usuario esta logado
    User::verifyLogin();

    //carrega a lista de produtos
    $product = new Product();
    
    //pegar o produto que foi passado na url
    $product->get((int)$idproduct);

    $product->delete();

    //redirecionar para a lista de produtos
    header('Location: /admin/products');
    exit;
});




?>