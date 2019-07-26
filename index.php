<?php 

require_once("vendor/autoload.php"); //require do composer, para trazer as dependencias

use \Slim\Slim; //namespace -- classes que serao utilizadas
use \Hcode\Page;
use \Hcode\PageAdmin;
//$app = new \Slim\Slim(); //aplicacao do slim para as rotas

$app = new Slim(); //rotas

$app->config('debug', true); //configurado o debug, para apresentar na tela

$app->get('/', function() { //rota principal
    
	//echo "OK";
	//$sql = new Hcode\DB\Sql(); //classe SQL dentro do Vendor: DB -->namespace
	//$results = $sql->select("SELECT * FROM tb_users"); //consulta SQL simples
	//echo json_encode($results); //visualizar os dados no navegador

	$page = new Page(); //construtor vazio

	$page->setTpl("index"); //vai adicionar o arquivo H1 que contém o "hello"
	
});

//criar a rota para o painel administrativo
$app->get('/admin', function() { //rota principal
    
	$page = new PageAdmin(); //construtor vazio

	$page->setTpl("index"); //vai adicionar o arquivo H1 que contém o "hello"
	
});

$app->run(); //rodar a aplicação

 ?>