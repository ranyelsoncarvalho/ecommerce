<?php

namespace Hcode\Model;

use \Hcode\DB\Sql; //buscar a classe SQL criada
use \Hcode\Model;
use \Hcode\Model\User;
use \Hcode\Model\Product;


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

    //metodo para adicionar um produto ao carrinho
    public function addProduct(Product $product){

        //criar a conexao com o banco
        $sql = new Sql();

        //fazer o insert na tabela cartsproducts
        $sql->query("INSERT INTO tb_cartsproducts (idcart, idproduct) VALUES(:idcart, :idproduct)", [
            //bind dos parametros
           ':idcart'=>$this->getidcart(), //variavel que vem da classe
           ':idproduct'=>$product->getidproduct() //vem da variavel do parametro, instancia que vem da classe produto
        ]);

    }
    
    //metodo para remover um produto do carrinho
    public function removeProduct(Product $product, $all = false){

        //criar a conexao com o banco
        $sql = new Sql();
     
        //$all --> permitir que o usuario possa remover todos os produtos de uma soh vez (quantidade)
        if($all) {
            $sql->query("UPDATE tb_cartsproducts SET dtremoved = NOW() WHERE idcart = :idcart AND idproduct = :idproduct AND dtremoved IS NULL", [
                //bind dos parametros
                ':idcart'=>$this->getidcart(),
                ':idproduct'=>$product->getidproduct()
            ]);
        } else {
            //funcao para remover apenas 1 produto, ou seja, diminuir
            $sql->query("UPDATE tb_cartsproducts SET dtremoved = NOW() WHERE idcart = :idcart AND idproduct = :idproduct AND dtremoved IS NULL LIMIT 1", [
                //bind dos parametros
                ':idcart'=>$this->getidcart(),
                ':idproduct'=>$product->getidproduct()
            ]);

        }


    }

    //listar todos os produtos do carrinho
    public function getProducts(){

        //criar a coenxao com o banco de dados
        $sql = new Sql();

        $rows = $sql->select("SELECT b.idproduct, b.desproduct, b.vlprice, b.vlwidth, b.vlheight, b.vllength, b.vlweight, b.desurl, COUNT(*) AS nrqtd, SUM(b.vlprice) AS vltotal 
        FROM tb_cartsproducts a 
        INNER JOIN tb_products b ON a.idproduct = b.idproduct 
        WHERE a.idcart = :idcart AND a.dtremoved IS NULL
        GROUP BY b.idproduct, b.desproduct, b.vlprice, b.vlwidth, b.vlheight, b.vllength, b.vlweight, b.desurl
        ORDER BY b.desproduct", [
        //bind dos parametros
        ':idcart'=>$this->getidcart()
        ]);

        return Product::checkList($rows);

    }

}

?>
