<?php

//colocar os namespaces
namespace Hcode\Model;

use \Hcode\DB\Sql; //buscar a classe SQL criada
use \Hcode\Model; //classe base

class OrderStatus extends Model { //classe para trazer o status dos pedidos gerados

    const EM_ABERTO = 1;
    const AGUARDANDO_PAGAMENTO = 2;
    const PAGO = 3;
    const ENTREGUE = 4;   


    //metodo para trazer todos os status que ja estao cadastrados no BD
    public static function listAll(){

        //informacoes do banco de dados
        $sql = new Sql();

        return $sql->select("SELECT * FROM tb_ordersstatus ORDER BY desstatus"); //consulta para retornar os status cadastrados no BD

    }

}


?>