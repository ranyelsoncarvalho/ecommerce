<?php

namespace Hcode\Model;

use \Hcode\DB\Sql; //buscar a classe SQL criada
use \Hcode\Model;
use \Hcode\Model\User;


class Cart extends Model {
    

    //variavel de sessao que sera utilizada no carrinho
    const SESSION = "Cart";

    //metodo para verificar se sera necessario inserir um carrinho novo ou se ja tem o carrinho ou se a sessao foi perdida (timeout)
    public static function getFromSession(){
        
        //criar um carrinho vazio
        $cart = new Cart();

        //verificar se o carrinho esta na sessao
        if (isset($_SESSION[Cart::SESSION]) && (int)$_SESSION[Cart::SESSION]['idcart']>0){

            //carregar o carrinho de acordo com a sessao do usuario
            $cart->get((int)$_SESSION[Cart::SESSION]['idcart']); //metodo criado "get"

        } 
        else{
            //recuperar os dados do carrinho
            $cart->getFromSessionID(); //metodo para carregar os dados do carrinho

            if(!(int)$cart->getidcart() > 0){ //nao conseguiu recuperar os dados do carrinho
                //criar um carrinho
                $data = [
                    'dessessionid'=>session_id() //pega o ID  da sessao
                ];

                if(User::checkLogin(false)){ //metodo para verificar se o usuario esta logado, estamos no carrinho de compra e nao no painel administrativo
                    //verificar se o usuario esta logado
                    $user = User::getFromSession(); 

                    //passar o ID do usuario
                    $data['iduser'] = $user->getiduser();

                }

                //colocar a variavel data dentro do objeto cart
                $cart->setData($data);

                //salvar no banco de dados
                $cart->save();

                //colocar na sessao, caso ele acesse novamente
                $cart->setToSession();

            } 
        }

        return $cart;

    }

    //metodo para colocar o carrinho na sessao
    public function setToSession(){ //ele nao e estatico pois faz uso da variavel $this

        $_SESSION[Cart::SESSION] = $this->getValues();


    }

    //recuperar dados do carrinho
    public function getFromSessionID(){
        $sql = new Sql();
        $results = $sql->select("SELECT * FROM tb_carts WHERE dessessionid = :dessessionid", [ //consulta do banco de dados para trazer informacoes do carrinho
            ':dessessionid'=>session_id()
        ]);

        if(count($results)>0){ //caso resulta em dados vazios
            $this->setData($results[0]);
        }
    }

    //metodo para trazer o carrinho ja criado
    public function get(int $idcart){

        $sql = new Sql();
        $results = $sql->select("SELECT * FROM tb_carts WHERE idcart = :idcart",[ //consulta para traazer os dados do carrinho
            ':idcart'=>$idcart
        ]);

        if(count($results)>0){ //caso resulte em dados vazios
            //colocar o resultado da consulta no objeto
            $this->setData($results[0]);
        }

    }

    //metodo para salvar o produto
    public function save(){

        //criar a conexao com o banco
        $sql = new Sql();

        //passando a procedure para salvar os dados no carrinho
        $results = $sql->select("CALL sp_carts_save(:idcart, :dessessionid, :iduser, :deszipcode, :vlfreight, :nrdays)", [
            //bind dos parametros
            ':idcart'=>$this->getidcart(),
            ':dessessionid'=>$this->getdessessionid(),
            ':iduser'=>$this->getiduser(),
            ':deszipcode'=>$this->getdeszipcode(),
            ':vlfreight'=>$this->getvlfreight(),
            ':nrdays'=>$this->getnrdays()
        ]); //passando a procedure com seus parametros

        //colocar os dados retornados no objeto
        $this->setData($results[0]);

        //para alterar dados no carrinho, vamos armazenar o ID do carrio dentro da sessao


    }

}

?>
