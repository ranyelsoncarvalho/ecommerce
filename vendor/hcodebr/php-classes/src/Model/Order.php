<?php

//colocar os namespaces
namespace Hcode\Model;

use \Hcode\DB\Sql; //buscar a classe SQL criada
use \Hcode\Model; //classe base
use \Hcode\Model\Cart; //classe do carrinho

class Order extends Model {


    //variaveis de sessao: ERROR e SUCCESS
    const SUCCESS = "Order-Success";
    const ERROR = "Order-Error";

    //salvar a ordem do pedido
    public function save(){

        //criar a instancia sql
        $sql = new Sql();
        
        //funcao para executar a procedure no banco de dados
        $results = $sql->select("CALL sp_orders_save(:idorder, :idcart, :iduser, :idstatus, :idaddress, :vltotal)", [
            //bind dos parametros
            ':idorder'=>$this->getidorder(),
            ':idcart'=>$this->getidcart(),
            ':iduser'=>$this->getiduser(),
            ':idstatus'=>$this->getidstatus(),
            ':idaddress'=>$this->getidaddress(),
            ':vltotal'=>$this->getvltotal()
        ]);

        //verificar se trouxe algum resultado
        if(count($results)>0){
            $this->setData($results[0]);
        }

    }


    //metodo para recuperar as informacoes do pedido
    public function get($idorder){

        //criar a instancia sql
        $sql = new Sql();

        //jogar os dados em um $results, realiza uma consulta
        $results = $sql->select("SELECT * 
			FROM tb_orders a 
			INNER JOIN tb_ordersstatus b USING(idstatus) 
			INNER JOIN tb_carts c USING(idcart)
			INNER JOIN tb_users d ON d.iduser = a.iduser
			INNER JOIN tb_addresses e USING(idaddress)
			INNER JOIN tb_persons f ON f.idperson = d.idperson
			WHERE a.idorder = :idorder
		", [
			':idorder'=>$idorder
		]);

        //fazer a verificação para saber se trouxe algum resultado
        if(count($results)>0){
            $this->setData($results[0]);
        }

    }

    //metodo para listar todos os pedidos
    public static function listAll(){ //metodo estatico, pois ele nao muda 

        //criar o objeto do banco de dados para trazer os dados do BD
        $sql = new Sql();

        //consulta para trazer todos os pedidos
        return $sql->select("SELECT * 
        FROM tb_orders a 
        INNER JOIN tb_ordersstatus b USING(idstatus) 
        INNER JOIN tb_carts c USING(idcart)
        INNER JOIN tb_users d ON d.iduser = a.iduser
        INNER JOIN tb_addresses e USING(idaddress)
        INNER JOIN tb_persons f ON f.idperson = d.idperson
        ORDER BY a.dtregister DESC
        ");

    }


    //metodo para deletar o pedido
    public function delete(){

        //fazer a conexao com o BD
        $sql = new Sql();
        
        //realiza a operacao de deletar
        $sql->query("DELETE FROM tb_orders WHERE idorder = :idorder", [
            //faz o bind dos parametros
            ':idorder'=>$this->getidorder()//pega o objeto dentro da propria classe
        ]);

    }

    //metodo para trazer todas as informacoes do carrinho referente ao pedido
    public function getCart():Cart { //o metodo vai retornar uma instancia da classe Cart

        //criar a instancia da classe
        $cart = new Cart();

        $cart->get((int)$this->getidcart()); //apontar o objeto para retornar os dados do carrinho, o this, indica dado da propria classe

        return $cart;

    }

    //metodos para as variaveis de sessao de erro
    public static function setError($msg)
	{
		$_SESSION[Order::ERROR] = $msg;
    }
    
	public static function getError()
	{
		$msg = (isset($_SESSION[Order::ERROR]) && $_SESSION[Order::ERROR]) ? $_SESSION[Order::ERROR] : '';
		Order::clearError();
		return $msg;
    }
    
	public static function clearError()
	{
		$_SESSION[Order::ERROR] = NULL;
    }
    
    //metodos para as variaveis de sessao de erro: SUCCESS
    public static function setSuccess($msg)
	{
		$_SESSION[Order::SUCCESS] = $msg;
    }
    
	public static function getSuccess()
	{
		$msg = (isset($_SESSION[Order::SUCCESS]) && $_SESSION[Order::SUCCESS]) ? $_SESSION[Order::SUCCESS] : '';
		Order::clearSuccess();
		return $msg;
    }
    
	public static function clearSuccess()
	{
		$_SESSION[Order::SUCCESS] = NULL;
    }

}


?>