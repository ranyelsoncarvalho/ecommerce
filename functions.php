<?php

use \Hcode\Model\User; //chamando a classe de usuarios
use \Hcode\Model\Cart; //chamando a classe do carrinho de compras
use \Hcode\Model\Category; //chamando a classe de categorias
use \Hcode\Model\Product; //chamando a classe de produtos
use \Hcode\Model\Order; //chamando a classe de pedidos

function formatPrice($vlPrice) { //formatar o preco dos produtos que estao carregados no banco

    return number_format($vlPrice, 2, ",", ".");

} 

//essa funcao sera dentro do template

function formatDate($date){ //funcao para formatar a data do pedido
    return date('d/m/Y', strtotime($date));
} 

//verificar o login
function checkLogin($inadmin = true){

    return User::checkLogin($inadmin);

}

//funcao para retornar o nome do usuario que esta logado
function getUserName(){
    
    $user = User::getFromSession(); //pegar o usuario da sessao que esta logado

    return $user->getdesperson(); //carregar o nome do usuario que esta logado
}

//funcao para retornar a data de cadastro do usuario logado
function getUserNameDateRegister(){
    
    $user = User::getFromSession(); //pegar o usuario da sessao que esta logado

    return $user->getdtregister(); //carregar a data de cadastro do usuario
}

//funcao para apresentar a quantidade de itens do carrinho em todas as telas do site
function getCartNrQtd(){

    $cart = Cart::getFromSession(); //pegar o carrinho que esta na sessao

    $totals = $cart->getProductsTotals(); //soma todos os valores do carrinho

    return $totals['nrqtd'];

}

//funcao para apresentar o valor total do carrinho em todas as telas do site
function getCartVlSubTotal(){

    $cart = Cart::getFromSession(); //pegar o carrinho que esta na sessao

    $totals = $cart->getProductsTotals(); //soma todos os valores do carrinho

    return formatPrice($totals['vlprice']); //retornar o valor ja formatado
} 

//funcao para retornar o numero de usuarios cadastrados
function getTotalUsers(){
    $user = new User(); //cria um novo objeto para instanciar
    $total = $user->getTotalUsers(); //chama o metodo para retornar a quantidade de usuarios cadastrados   
    return $total['numberusers'];
}

//funcao para retornar o numero de categorias cadastradas
function getTotalCategories(){
    $category = new Category();
    $total = $category->getTotalCategory();
    return $total['numbercategories'];
}

//funcao para retornar o numero de produtos cadastrados
function getTotalProducts(){
    $product = new Product();
    $total = $product->getTotalProducts();
    return $total['numberproducts'];
}

//funcao para retornar o numero de pedidos cadastrados
function getTotalOrders(){
    $order = new Order();
    $total = $order->getTotalOrders();
    return $total['numberorders'];
}

?>