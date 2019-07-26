<?php 

require_once("vendor/autoload.php"); //require do composer

$app = new \Slim\Slim(); //aplicacao do slim para as rotas

$app->config('debug', true); //configurado o debug, para apresentar na tela

$app->get('/', function() { //rota principal
    
	//echo "OK";
	$sql = new Hcode\DB\Sql(); //classe SQL dentro do Vendor: DB -->namespace
	$results = $sql->select("SELECT * FROM tb_users"); //consulta SQL simples

	echo json_encode($results); //visualizar os dados no navegador

});

$app->run();

 ?>