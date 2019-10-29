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

//rota para salvar o novo status do pedido recebido
$app->post("/admin/orders/:idorder/status", function($idorder){

    //verificar se o usuario esta logado
    User::verifyLogin();

    //caso o IDSTATUS nao seja enviado
    if(!isset($_POST['idstatus']) || !(int)$_POST['idstatus'] > 0){
        Order::setError("Informe o status do pedido");
        header("Location: /admin/orders/" .$idorder."/status");//redirecionar para o status do pedido
        exit;
    }

     //carregar qual o status atual do pedido
    $order = new Order();
    $order->get((int)$idorder);

    $order->setidstatus((int)$_POST['idstatus']); //metodo para alterar o status do pedido, com o dado que vem do formulario

    //salvar o alteração
    $order->save();

    //carregar a mensagem de sucesso do status do pedido alterado
    Order::setSuccess("Status atualizado");
    header("Location: /admin/orders/" .$idorder."/status");//redirecionar para o status do pedido
    exit;

});

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

    $search = (isset($_GET['search'])) ? $_GET['search'] : ""; //variavel para fazer a busca dos pedidos no portal admin
	$page = (isset($_GET['page'])) ? (int)$_GET['page'] : 1;//verificar se a pagina foi definida
	
	//filtro para retornar os pedidos da busca
	if($search!=''){
		$pagination = Order::getPageSearch($search, $page); //metodo para a paginacao do pedido com a busca
	}else {
		$pagination = Order::getPage($page); //metodo para a paginacao do pedido sem a busca
	}
	

	//montar as paginas
	$pages = [];

	//percorrer e resultar a paginacao
	for ($x=0; $x < $pagination['pages']; $x++){
		array_push($pages, [
			'href'=>'/admin/orders?'.http_build_query([
				'page'=>$x+1,
				'search'=>$search
			]), //fazer com que a paginacao continue na proxima pagina
			'text'=>$x+1 //texto a ser carregado na busca	(numero da pagina)
		]);
	}

    //criar o objeto PageAdmin
    $page = new PageAdmin();

    //carregar o template
    $page->setTpl("orders", [
        //passar a lista de pedidos
        //"orders"=>Order::listAll() //metodo para carregar todos os pedidos: variavel orders - carregar os dados para o template
        "orders"=>$pagination['data'], //carregar os dados dos pedidos
		"search"=>$search, //realiza a busca de pedidos
		"pages"=>$pages //variavel para a paginacao
    ]);


});

?>