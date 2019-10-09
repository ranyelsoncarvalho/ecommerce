<?php

//colocar os namespaces
namespace Hcode\Model;

use \Hcode\DB\Sql; //buscar a classe SQL criada
use \Hcode\Model; //classe base
use \Hcode\Model\Cart;

class Order extends Model {

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

}


?>