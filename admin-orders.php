<?php

//colocar os namespace das classes utilizadas
use \Hcode\PageAdmin;
use \Hcode\Model\User;
use \Hcode\Model\Order;
use \Hcode\Model\OrderStatus;

//arquivo que sera utilizado apra passar as rotas dos pedidos

//rota para alterar o status do pedido
$app->get("/admin/orders/:idorder/status", function($idorder){

    //verificar se o usuario esta logado
    User::verifyLogin();

    //carregar qual o status atual do pedido
    $order = new Order();
    $order->get((int)$idorder);

    //definir o template
    $page = new PageAdmin();

    //template a ser chamado
    $page->setTpl("order-status", [
        "order"=>$order->getValues(),
        "status"=>OrderStatus::listAll(), //chamando a classe OrderStatus e passando o metodo para listar todos os status cadastrados
        "msgSuccess"=>Order::getSuccess(),
        "msgError"=>Order::getError()
    ]);

});

//rota para salvar o novo status do pedido


//rota para excluir o pedido
$app->get("/admin/orders/:idorder/delete", function($idorder){ //e preciso passar o id do pedido para fazer a exclusao

     //verificar se o usuario esta logado
     User::verifyLogin(); //nao utiliza o false, pois e uma rota da administracao

     $order = new Order(); //carregar a classe, para carregar o pedido

     //carregar o pedido
     $order->get((int)$idorder);

     $order->delete(); //chama o metodo para realizar o delete do objeto

     //redireciona para a lista de pedidos
     header("Location: /admin/orders");
     exit;
});

//rota para exibir os detalhes do pedido
$app->get("/admin/orders/:idorder", function($idorder){

    //verificar se o usuario esta logado
    User::verifyLogin(); //nao utiliza o false, pois e uma rota da administracao

    $order = new Order(); //carregar a classe, para carregar o pedido

    //carregar os dados do pedido de acordo com o que veio da rota
    $order->get((int)$idorder);

    //carregar os dados do carrinho que estao vinculados ao pedido
    $cart = $order->getCart(); //metodo para trazer os dados do carrinho

    //criar a pagina e carregar o template
    $page = new PageAdmin();

    //chamando o template a ser exibido
    $page->setTpl("order", [
        "order"=>$order->getValues(), //carregar todos os valores que estao dentro do objeto Order (pedidos)
        "cart"=>$cart->getValues(),
        "products"=>$cart->getProducts() //carregar todos os produtos que esta no carrinho
    ]);

});




//rota para visualiza os pedidos no portal admnistrativo
$app->get("/admin/orders", function(){

    //verificar se o usuario esta logado
    User::verifyLogin();

    //criar o objeto PageAdmin
    $page = new PageAdmin();

    //carregar o template
    $page->setTpl("orders", [
        //passar a lista de pedidos
        "orders"=>Order::listAll() //metodo para carregar todos os pedidos: variavel orders - carregar os dados para o template
    ]);


});

?>