<?php

use \Hcode\Model\User;

function formatPrice($vlPrice) { //formatar o preco dos produtos que estao carregados no banco

    return number_format($vlPrice, 2, ",", ".");

} 

//essa funcao sera dentro do template


//verificar o login
function checkLogin($inadmin = true){

    return User::checkLogin($inadmin);

}

//funcao para retornar o nome do usuario que esta logado
function getUserName(){
    
    $user = User::getFromSession(); //pegar o usuario da sessao que esta logado

    return $user->getdesperson(); //carregar o nome do usuario que esta logado
}

?>