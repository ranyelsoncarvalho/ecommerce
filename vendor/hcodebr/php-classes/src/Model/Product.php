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
        if (\file_exists($_SERVER['DOCUMENT_ROOT'] . DIRECTORY_SEPARATOR . "res" . DIRECTORY_SEPARATOR . "site" . DIRECTORY_SEPARATOR . "img" . DIRECTORY_SEPARATOR . "products" . DIRECTORY_SEPARATOR . $this->getidproduct() . "jpg")) //o nome da foto ser? o nome do arquivo
        {
            $url =  "/res/site/img/products/" . $this->getidproduct() . "jpg"; //passando a URL
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
}

?>