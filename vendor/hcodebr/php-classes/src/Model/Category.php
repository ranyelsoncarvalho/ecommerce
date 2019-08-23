<?php

namespace Hcode\Model;

use \Hcode\DB\Sql; //buscar a classe SQL criada
use \Hcode\Model;


class Category extends Model {
    
    public static function listAll(){ //metodo para listar todas as categorias cadastradas
        $sql = new Sql();
        return $sql->select("SELECT * FROM tb_categories ORDER BY descategory"); //consulta para listar todos os usuarios cadastrados
    }

    public function save(){

        //criar a conexao com o BD
        $sql = new Sql();

        $results = $sql->select("CALL sp_categories_save(:idcategory, :descategory)", array( //chamando a procedure para o cadastro da categoria
            ":idcategory"=>$this->getidcategory(), //bind dos paramentros em conformancia com o PDO
            ":descategory"=>$this->getdescategory()
        ));

        $this->setData($results[0]); //retorna o valor e aplica no SETDATA

    }

    public function get($idcategory){ //metodo para trazer os dados

        //carregar o BD
        $sql = new Sql();

        $results = $sql->select("SELECT * FROM tb_categories WHERE idcategory = :idcategory", [
            ':idcategory'=>$idcategory
        ]);

        $this->setData($results[0]); 

    }

    public function delete(){
        
        $sql = new Sql();

        $sql->query("DELETE FROM tb_categories WHERE idcategory = :idcategory", [
            ':idcategory'=>$this->getidcategory()
        ]);
        $sql->query("ALTER TABLE tb_categories AUTO_INCREMENT = 1"); //limpar a tabela e recomeçar o increment

    }
}

?>