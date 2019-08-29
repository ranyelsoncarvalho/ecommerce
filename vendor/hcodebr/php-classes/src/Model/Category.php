<?php

namespace Hcode\Model;

use \Hcode\DB\Sql; //buscar a classe SQL criada
use \Hcode\Model;


class Category extends Model {
    
    //metodo para listar todas as categorias cadastradas
    public static function listAll(){ //metodo para listar todas as categorias cadastradas
        $sql = new Sql();
        return $sql->select("SELECT * FROM tb_categories ORDER BY descategory"); //consulta para listar todos os usuarios cadastrados
    }

    //metodo para salvar uma categoria
    public function save(){

        //criar a conexao com o BD
        $sql = new Sql();

        $results = $sql->select("CALL sp_categories_save(:idcategory, :descategory)", array( //chamando a procedure para o cadastro da categoria
            ":idcategory"=>$this->getidcategory(), //bind dos paramentros em conformancia com o PDO
            ":descategory"=>$this->getdescategory()
        ));

        $this->setData($results[0]); //retorna o valor e aplica no SETDATA

        //momento em que ha uma alteracao nas categorias
        Category::updateFile();

    }

    public function get($idcategory){ //metodo para trazer os dados

        //carregar o BD
        $sql = new Sql();

        $results = $sql->select("SELECT * FROM tb_categories WHERE idcategory = :idcategory", [
            ':idcategory'=>$idcategory
        ]);

        $this->setData($results[0]); 

    }

    //metodo para deletar uma categoria
    public function delete(){
        
        $sql = new Sql();

        $sql->query("DELETE FROM tb_categories WHERE idcategory = :idcategory", [
            ':idcategory'=>$this->getidcategory()
        ]);
        $sql->query("ALTER TABLE tb_categories AUTO_INCREMENT = 1"); //limpar a tabela e recomeçar o increment

        //o metodo para atualizar as categorias
        Category::updateFile();

    }

    //metodo para atualizar as categorias do site
    public function updateFile(){
        
        //carregar todas as categorias que estao no banco de dados
        $categories = Category::listAll();

        //montar o HTML da pagina, fazer dinamicamente
        $html = [];

        foreach ($categories as $row) {
            //fazer a concatenacao com as categorias do banco de dados, passando o nome da coluna do banco de dados "descategory"
            //necessario passar a rota tambem
            array_push($html, '<li><a href="/categories/'.$row['idcategory'].'">'.$row['descategory'].'</a></li>');
        }

        //salvar 
        file_put_contents($_SERVER['DOCUMENT_ROOT'].DIRECTORY_SEPARATOR."views".DIRECTORY_SEPARATOR."categories-menu.html", implode('', $html));

    }

    //metodo para trazer todos os produtos
    public function getProducts($related = true){

        $sql = new Sql(); //fazer o acesso ao banco de dados

        if ($related) { //trazer os produtos relacionados

           return $sql->select("SELECT * FROM tb_products WHERE idproduct IN(
                SELECT  a.idproduct 
                FROM tb_products a
                INNER JOIN tb_productscategories b ON a.idproduct = b.idproduct
                WHERE b.idcategory = :idcategory
                );
            ", [
                ':idcategory'=>$this->getidcategory()
            ]);
            
        }else { //trazer os produtos que nao estao relacionados
           return $sql->select("SELECT * FROM tb_products WHERE idproduct NOT IN(
                SELECT  a.idproduct 
                FROM tb_products a
                INNER JOIN tb_productscategories b ON a.idproduct = b.idproduct
                WHERE b.idcategory = :idcategory
                );
            ",[
                ':idcategory'=>$this->getidcategory()
            ]);
        }

    }
}

?>