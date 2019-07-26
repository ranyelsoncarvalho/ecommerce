<?php 

require_once("vendor/autoload.php");

$app = new \Slim\Slim();

$app->config('debug', true);

$app->get('/', function() {
    
	//echo "OK";
	$sql = new Hcode\DB\Sql(); //classe SQL dentro do Vendor
	$results = $sql->select("SELECT * FROM tb_users"); //consulta SQL

	echo json_encode($results); //visualizar os dados

});

$app->run();

 ?>