<?php

namespace Hcode\Model;

use \Hcode\DB\Sql; //buscar a classe SQL criada
use \Hcode\Model;


class Product extends Model {
    
    //metodo para listar todas os produtos cadastradas
    public static function listAll(){ //metodo para listar todos os produtos cadastrados
        $sql = new Sql();
        return $sql->select("SELECT * FROM tb_products ORDER BY desproduct"); //consulta para listar todos os produtos cadastrados
    }

    //como a foto nao tem o campo no banco de dados, vamos criar um metodo para guardar esses dados
    public static function checkList($list){

        foreach ($list as &$row){ //cada item da lista serah chamado de row, uso do & para manipular a mesma variavel
            $p = new Product();
            $p->setData($row);
            $row = $p->getValues(); //assim e possivel carregar as imagens do produto no banco
        }

        return $list;

    }

    //metodo para salvar um produto
    public function save(){

        //criar a conexao com o BD
        $sql = new Sql();

        $results = $sql->select("CALL sp_products_save(:idproduct, :desproduct, :vlprice, :vlwidth, :vlheight, :vllength, :vlweight, :desurl)", array( //chamando a procedure para o cadastro do produto, colocar na ordem que esta na procedure
            ":idproduct"=>$this->getidproduct(), //bind dos paramentros em conformancia com o PDO
            ":desproduct"=>$this->getdesproduct(),
            ":vlprice"=>$this->getvlprice(),
            ":vlwidth"=>$this->getvlwidth(),
            ":vlheight"=>$this->getvlheight(),
            ":vllength"=>$this->getvllength(),
            ":vlweight"=>$this->getvlweight(),
            ":desurl"=>$this->getdesurl()
        ));

        $this->setData($results[0]); //retorna o valor e aplica no SETDATA

    }

    public function get($idproduct){ //metodo para trazer os dados cadastrados 

        //carregar o BD
        $sql = new Sql();

        $results = $sql->select("SELECT * FROM tb_products WHERE idproduct = :idproduct", [
            ':idproduct'=>$idproduct
        ]);

        $this->setData($results[0]); 

    }

    //metodo para deletar um produto
    public function delete(){
        
        $sql = new Sql();

        $sql->query("DELETE FROM tb_products WHERE idproduct = :idproduct", [
            ':idproduct'=>$this->getidproduct()
        ]);
        $sql->query("ALTER TABLE tb_products AUTO_INCREMENT = 1"); //limpar a tabela e recomeçar o increment

    }


    //metodo para verificar se tem ou n?o foto do produto
    public function checkPhoto(){
        //verifica na pasta onde a imagem sera salva -- especificando o caminho de pasta
        if (\file_exists($_SERVER['DOCUMENT_ROOT'] . DIRECTORY_SEPARATOR . "res" . DIRECTORY_SEPARATOR . "site" . DIRECTORY_SEPARATOR . "img" . DIRECTORY_SEPARATOR . "products" . DIRECTORY_SEPARATOR . $this->getidproduct() . ".jpg")) //o nome da foto ser? o nome do arquivo
        {
            $url =  "/res/site/img/products/" . $this->getidproduct() . ".jpg"; //passando a URL
        }
        else { //retornar uma foto padrao
            $url =  "/res/site/img/product.jpg";
        }
        
        //coloca a foto dentro do objeto
        return $this->setdesphoto($url);
    }   

    //metodo para carregar a foto do produto
    public function getValues(){

        //metodo para verificar sem tem ou n?o foto do produto, caso n?o tenha foto adicionamos uma foto padr?o
        $this->checkPhoto();

        $values = parent::getValues();

        return $values;

    }

    public function setPhoto($file){


        //converter a imagem para um formato especifico
        $extension = explode('.', $file['name']);
        $extension = end($extension);

        switch($extension){
            case "jpg":
            case "jpeg":
            $image = imagecreatefromjpeg($file["tmp_name"]);
            break;

            case "png";
            $image = imagecreatefrompng($file["tmp_name"]);
            break;

            case "gif";
            $image = imagecreatefromgif($file["tmp_name"]);
            break;
        }

        $dst = $_SERVER['DOCUMENT_ROOT'] . DIRECTORY_SEPARATOR . "res" . DIRECTORY_SEPARATOR . "site" . DIRECTORY_SEPARATOR . "img" . DIRECTORY_SEPARATOR . "products" . DIRECTORY_SEPARATOR . $this->getidproduct() . ".jpg";

        imagejpeg($image, $dst);

        imagedestroy($image);

        $this->checkPhoto();

    }

    //metodo para carregar os detalhes do produto de acordo com a URL passada
    public function getFromURL($desurl){

        //conexao com o BD
        $sql = new Sql();

        //fazer a consulta para retornar os dados
        $rows =  $sql->select("SELECT * FROM tb_products WHERE desurl = :desurl LIMIT 1", [
            //fazer o bind dos parametros
            ':desurl'=>$desurl
        ]);

        //colocar as informacoes dentro do proprio objeto
        $this->setData($rows[0]);

    }

    //metodo para trazer as categorias do produto relacionado
    public function getCategories(){

        //instancia da classe SQL
        $sql = new Sql();

        return $sql->select("SELECT * FROM tb_categories a INNER JOIN tb_productscategories b ON a.idcategory = b.idcategory WHERE b.idproduct = :idproduct", [
            //fazer o bind do parametro
            ':idproduct'=>$this->getidproduct()
        ]);

    }

     //metodo para a paginacao de produtos
     public static function getPage($page = 1, $itemPerPage = 3) { //$page = numero da pagina atual; $itemPerPage = numero de itens a serem carregados na pagina
        
        $start = ($page - 1) * $itemPerPage; //onde sera iniciada a query

        //inicia a instancia SQL
        $sql = new Sql();

        //realiza a query no banco de dados: sql_calc_foun_rows --> calcula quantas linhas foram retornadas
        $results = $sql->select("SELECT SQL_CALC_FOUND_ROWS * 
            FROM tb_products 
            ORDER BY desproduct
            LIMIT $start, $itemPerPage;");

        //numero de linhas retornadas
        $resultTotal = $sql->select("SELECT FOUND_ROWS() AS nrtotal;");
        
        return [
            'data'=>$results, //todas as linhas resultadas da consulta
            'total'=>(int)$resultTotal[0]['nrtotal'], //numero total de registros
            'pages'=>ceil($resultTotal[0]['nrtotal'] / $itemPerPage) //numero total de paginas
        ];


    }

    //metodo para a paginacao de produtos com a variavel de busca
    public static function getPageSearch($search, $page = 1, $itemPerPage = 3) { //$page = numero da pagina atual; $itemPerPage = numero de itens a serem carregados na pagina
        
        $start = ($page - 1) * $itemPerPage; //onde sera iniciada a query

        //inicia a instancia SQL
        $sql = new Sql();

        //realiza a query no banco de dados: sql_calc_foun_rows --> calcula quantas linhas foram retornadas
        $results = $sql->select("SELECT SQL_CALC_FOUND_ROWS * 
            FROM tb_products  
            WHERE desproduct LIKE :search 
            ORDER BY desproduct
            LIMIT $start, $itemPerPage;", [
                //bind do parametro de busca
                ':search'=>'%'.$search.'%'
            ]);

        //numero de linhas retornadas
        $resultTotal = $sql->select("SELECT FOUND_ROWS() AS nrtotal;");
        
        return [
            'data'=>$results, //todas as linhas resultadas da consulta
            'total'=>(int)$resultTotal[0]['nrtotal'], //numero total de registros
            'pages'=>ceil($resultTotal[0]['nrtotal'] / $itemPerPage) //numero total de paginas
        ];


    }
    
}

?>