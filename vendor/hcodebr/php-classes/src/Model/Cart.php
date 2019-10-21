<?php

namespace Hcode\Model;

use \Hcode\DB\Sql; //buscar a classe SQL criada
use \Hcode\Model;
use \Hcode\Model\User;
use \Hcode\Model\Product;


class Cart extends Model {
    

    //variavel de sessao que sera utilizada no carrinho
    const SESSION = "Cart";

    //variavel de sessao que contem o erro
    const SESSION_ERROR = "CartError";

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

        //metodo para atualiza o frete
        $this->getCalculateTotal();

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
                ':idproduct'=>$product->getidproduct() //indica que a da classe produtos
            ]);

        }

         //metodo para atualiza o frete
         $this->getCalculateTotal();

        


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

    //calcular o total dos produtos do carrinho (produto e frete)
    public function getProductsTotal(){

        //criar a conexao com o banco de dados
        $sql = new Sql();

        //criar a instancia para acessar
        $results = $sql->select("SELECT SUM(vlprice) AS vlprice, SUM(vlwidth) AS vlwidth, SUM(vlheight) AS vlheight, SUM(vllength) AS vllength, SUM(vlweight) AS vlweight, COUNT(*) AS nrqtd  
            FROM tb_products a
            INNER JOIN tb_cartsproducts b ON a.idproduct = b.idproduct
            WHERE b.idcart = :idcart AND dtremoved IS NULL", [
                //bind dos parametros
                ':idcart'=>$this->getidcart() //usa o $this pois e na mesma classe
            ]
        );

        //verificar a quantidade de dados que eh retornada
        if(count($results)> 0){
            return $results[0];
        }else {
            return [];
        }


    }

    //metodo para definir o frete do carrinho de compra, passando o CEP como parametro para realizar o calculo
    public function setFreight($nrzipcode){

        //certificar que so retornou numeros, ao digitar o traco ser removido
        $nrzipcode = str_replace('-', '', $nrzipcode);

        //pegar as informacoes totais do carrinho
        $totals = $this->getProductsTotal();


        //verificar se tem algum produto dentro do carrinho
        if($totals['nrqtd']>0){

            //regras para evitar alguns erros
            if($totals['vlheight']<2) $totals['vlheight'] = 2;
            if($totals['vllength']<16) $totals['vllength'] = 16;

            //passar as variaveis
            //dados referencia: file:///C:/xampp/htdocs/e-commerce/Manual-de-Implementacao-do-Calculo-Remoto-de-Precos-e-Prazos-Correios.pdf
            $qs = http_build_query([
                'nCdEmpresa'=>'',
                'sDsSenha'=>'',
                'nCdServico'=>'40010',
                'sCepOrigem'=>'09853120',
                'sCepDestino'=>$nrzipcode,
                'nVlPeso'=>$totals['vlweight'],
                'nCdFormato'=>'1',
                'nVlComprimento'=>$totals['vllength'],
                'nVlAltura'=>$totals['vlheight'],
                'nVlLargura'=>$totals['vlwidth'],
                'nVlDiametro'=>'0',
                'sCdMaoPropria'=>'S',
                'nVlValorDeclarado'=>$totals['vlprice'],
                'sCdAvisoRecebimento'=>'S'
            ]);

            //realiza o calculo do frete, passar os dados via webservice por meio do XML
            $xml = simplexml_load_file("http://ws.correios.com.br/calculador/CalcPrecoPrazo.asmx/CalcPrecoPrazo?".$qs); //a interrogacao eh para passar as variaveis na querystring

            //$xml = (array)simplexml_load_file("http://ws.correios.com.br/calculador/CalcPrecoPrazo.asmx/CalcPrecoPrazo?".$qs); //a interrogacao eh para passar as variaveis na querystring
            //fazer a impressao na tela para verificar se nao tem nenhum erro
            //echo json_encode($xml);
            //exit;

            //acessando os dados do array do XML
            $result = $xml->Servicos->cServico;

            //apresentar alguma mensagem de erro
            if($result->MsgErro != '') {

                //vai passar a msg via sessao dos metodos criados abaixo
                Cart::setMsgError($result->MsgErro);

            } else {
                Cart::clearMsgError();
            }

            //salvar os dados no carrinho
            $this->setnrdays($result->PrazoEntrega);
            $this->setvlfreight(Cart::formatValueToDecimal($result->Valor));
            $this->setdeszipcode($nrzipcode);

            //savlar as informacoes no banco
            $this->save();

            return $result; //caso precise pegar alguma informacao fora do metodo


        }else {

        }

    }

    //formata os valores em decimais, salva no banco com ponto e retorna para a view com virgula
    public static function formatValueToDecimal($value):float {

        $value = str_replace('.', ',', $value); //troca para virgula
        return str_replace(',', '.', $value);

    }

    //metodo para passar a mensagem via sessao
    public static function setMsgError($msg){

        $_SESSION[Cart::SESSION_ERROR] = $msg; //variavel: SESSION_ERROR --> CONSTANTE QUE CONTEM O ERRO

    }

    //metodo para recuperar o erro da msg
    public static function getMsgError(){

        //verificar se ja foi definido
        $msg = (isset($_SESSION[Cart::SESSION_ERROR])) ? $_SESSION[Cart::SESSION_ERROR] : "";

        Cart::clearMsgError();

        return $msg;

    }

    //metodo para limpar a sessao
    public static function clearMsgError(){

        $_SESSION[Cart::SESSION_ERROR] = NULL;

    }

    public function updateFreight()
	{
		if ($this->getdeszipcode() != '') {
			$this->setFreight($this->getdeszipcode());
		}
    }
    
    //adicionar mais informacoes ao carrinho, que eh o total (subtotal + frete) e subtotal
    public function getValues(){

        $this->getCalculateTotal(); //metodo para calcular o valor total

        return parent::getValues(); //parent e utilizado quando deseja descrever o metodo PAI

    }

    //metodo para calcular o valor total
    public function getCalculateTotal(){


        //metodo para atualizar o frete
        $this->updateFreight();

        //necessario pegar as informacoes do carrinho
        
        $totals = $this->getProductsTotal();//metodo que traz todos os valores do carrinho

        $this->setvlsubtotal($totals['vlprice']);
        $this->setvltotal($totals['vlprice'] + (float)$this->getvlfreight()); //calcular o valor do produto com o frete

    }
    
    //metodo para remover a sessao do carrinho do usuario, quando ele efetua o logout
    public static function removeFromSession(){
        $_SESSION[Cart::SESSION] = NULL;
    }

}

?>
