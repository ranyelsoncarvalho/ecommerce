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

    //funcao para realizar a paginacao
    public function getProductsPage($page = 1, $itemsPerPage = 3){ //vai receber qual a p?gina e numero de itens por pagina que vamos exibir

        //$itemsPerPage --> indica o numero de items que sera visualizado por p?gina

        //regra para gerar a quantidade de paginas
        $start = ($page-1)*$itemsPerPage; //pegar a primeira pagina
        
        $sql = new Sql(); //fazer o acesso ao banco de dados
        //obter o resultado dos produtos
        $results = $sql->select("SELECT SQL_CALC_FOUND_ROWS * FROM tb_products a
            INNER JOIN tb_productscategories b ON a.idproduct = b.idproduct
            INNER JOIN tb_categories c ON c.idcategory = b.idcategory
            WHERE c.idcategory = :idcategory
            LIMIT $start, $itemsPerPage;",
            [
                ':idcategory'=>$this->getidcategory()]
            );

        //SQL_CALC_FOUND_ROWS --> funcao do MySQL para contar as linhas da tabela, utiliza uma segunda consulta para pegar o numero total de linhas
        //limit --> limitar o numero de resultados

        //resultado do total de produtos (numero), identificar quantos itens tem no banco
        $resultTotal = $sql->select("SELECT FOUND_ROWS() AS nrtotal;");

        //retornar os dados do produto
        return [
            'data'=>Product::checkList($results),
            'total'=>(int)$resultTotal[0]["nrtotal"], //informando a linha e a coluna que ser? trazida da consulta
            'pages'=>ceil($resultTotal[0]["nrtotal"] / $itemsPerPage) //metodo para arredondar para cima
        ];

    }

    //funcao para realizar a adicao de categoria ao produto
    public function addProduct(Product $product){ //forca que seja passado um produto no parametro

        //criar a query para fazer a adicao do produto
        $sql = new Sql();
        $sql->query("INSERT INTO tb_productscategories (idcategory, idproduct) VALUES(:idcategory, :idproduct)", [
            ':idcategory'=>$this->getidcategory(), //faz o bind dos parametros
            ':idproduct'=>$product->getidproduct()    
        ]);//eh utilizado a "query" pois nao sera retornado nada, quando eh para inserir algum dado no bando de dados

    }

    //funcao para realizar remover a categoria ao produto
    public function removeProduct(Product $product){ //forca que seja passado um produto no parametro

        //criar a query para fazer a remocao da categoria do produto
        $sql = new Sql();
        $sql->query("DELETE FROM tb_productscategories WHERE idcategory = :idcategory AND idproduct = :idproduct", [
            ':idcategory'=>$this->getidcategory(), //faz o bind dos parametros
            ':idproduct'=>$product->getidproduct()    
        ]);//eh utulizado a "query" pois nao sera retornado nada, quando eh para inserir algum dado no bando de dados

    }

    //metodo para a paginacao de categorias
    public static function getPage($page = 1, $itemPerPage = 3) { //$page = numero da pagina atual; $itemPerPage = numero de itens a serem carregados na pagina
        
        $start = ($page - 1) * $itemPerPage; //onde sera iniciada a query

        //inicia a instancia SQL
        $sql = new Sql();

        //realiza a query no banco de dados: sql_calc_foun_rows --> calcula quantas linhas foram retornadas
        $results = $sql->select("SELECT SQL_CALC_FOUND_ROWS * 
            FROM tb_categories 
            ORDER BY descategory
            LIMIT $start, $itemPerPage;");

        //numero de linhas retornadas
        $resultTotal = $sql->select("SELECT FOUND_ROWS() AS nrtotal;");
        
        return [
            'data'=>$results, //todas as linhas resultadas da consulta
            'total'=>(int)$resultTotal[0]['nrtotal'], //numero total de registros
            'pages'=>ceil($resultTotal[0]['nrtotal'] / $itemPerPage) //numero total de paginas
        ];


    }

    //metodo para a paginacao de categorias com a variavel de busca
    public static function getPageSearch($search, $page = 1, $itemPerPage = 3) { //$page = numero da pagina atual; $itemPerPage = numero de itens a serem carregados na pagina
        
        $start = ($page - 1) * $itemPerPage; //onde sera iniciada a query

        //inicia a instancia SQL
        $sql = new Sql();

        //realiza a query no banco de dados: sql_calc_foun_rows --> calcula quantas linhas foram retornadas
        $results = $sql->select("SELECT SQL_CALC_FOUND_ROWS * 
            FROM tb_categories  
            WHERE descategory LIKE :search 
            ORDER BY descategory
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