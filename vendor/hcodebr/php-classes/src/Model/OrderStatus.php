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

}


?>