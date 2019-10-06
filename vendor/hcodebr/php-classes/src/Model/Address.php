<?php

namespace Hcode\Model;

use \Hcode\DB\Sql; //buscar a classe SQL criada
use \Hcode\Model;

class Address extends Model {

    //variavel de sessao para as mensagens de erro e sucesso
    const SESSION_ERROR = "AddressError";

    //metodo para receber o CEP, atraves de um webservice
    public static function getCEP($nrcep){

        //verificar que o cep tem apenasnumeros
        $nrcep = str_replace("-", "", $nrcep);

        //https://viacep.com.br/ws/01001000/json/

        //utilizando a biblioteca curl, informando que sera consumido uma URL externa
        $ch = curl_init(); //iniciar a lib

        curl_setopt($ch, CURLOPT_URL, "https://viacep.com.br/ws/$nrcep/json/"); //definir a URL que sera chamada

        //verifica se ele tera que devolver alguma informacao
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true); //esperando receber um dado da URL
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false); //se sera exigido algum tipo de autenticacao
        
        //pegar o retorno que esta sendo passado para a variavel $ch. Neste ponto vamos jogar um json decode
        $data = json_decode(curl_exec($ch), true); //passa como true, para vir como array

        //fecha a conexao para não ficar pesado, pois a cada atualização ele iria consultar novamente
        curl_close($ch);

        return $data;

    }

    //carregar o objeto endereco
    public function loadFromCEP($nrcep){

        //carregar os dados do endereco
        $data = Address::getCEP($nrcep);

        //verificar se a variavel $data retornou alguma coisa
        if(isset($data['logradouro']) && $data['logradouro']){

            //carregar os dados no proprio objeto que vem do webservice, via json e passar de acordo com as tabelas cadastradas no BD
            $this->setdesaddress($data['logradouro']);
            $this->setdescomplement($data['complemento']);
            $this->setdesdistrict($data['bairro']);
            $this->setdescity($data['localidade']);
            $this->setdesstate($data['uf']);
            $this->setdescountry('Brasil');
            $this->setdeszipcode($nrcep);
        }

    }

    //metodo para salvar o endereco
    public function save(){

        //cria a instancia SQL
        $sql = new Sql();

        //chama o meotodo select para realizar a operacao de salvar os dados no banco
        $results = $sql->select("CALL sp_addresses_save(:idaddress, :idperson, :desaddress, :descomplement, :descity,
                :descountry, :deszipcode, :desdistrict, :desstate)", [
                //bind dos parametros
                ':idaddress'=>$this->getidaddress(),
                ':idperson'=>$this->getidperson(),
                ':desaddress'=>$this->getdesaddress(),
                ':descomplement'=>$this->getdescomplement(),
                ':descity'=>$this->getdescity(),
                ':descountry'=>$this->getdescountry(),
                ':deszipcode'=>$this->getdeszipcode(),
                ':desdistrict'=>$this->getdesdistrict(),
                ':desstate'=>$this->getdesstate()
                ]);
        
        //verificar se a variavel $results retornou alguma coisa
        if(count($results)>0){
            $this->setData($results[0]);
        }

    }

    //metodos para verificar os erros no template do usuario
    //metodo para passar a mensagem via sessao
    public static function setMsgError($msg){

        $_SESSION[Address::SESSION_ERROR] = $msg; //variavel: SESSION_ERROR --> CONSTANTE QUE CONTEM O ERRO

    }

    //metodo para recuperar o erro da msg
    public static function getMsgError(){

        //verificar se ja foi definido
        $msg = (isset($_SESSION[Address::SESSION_ERROR])) ? $_SESSION[Address::SESSION_ERROR] : "";

        Address::clearMsgError();

        return $msg;

    }

    //metodo para limpar a sessao
    public static function clearMsgError(){

        $_SESSION[Address::SESSION_ERROR] = NULL;

    }
    
}

?>