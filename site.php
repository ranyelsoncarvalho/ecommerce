<?php

//colocar os namespace das classes utilizadas
use \Hcode\Page;


$app->get('/', function() { //rota principal
    
	//echo "OK";
	//$sql = new Hcode\DB\Sql(); //classe SQL dentro do Vendor: DB -->namespace
	//$results = $sql->select("SELECT * FROM tb_users"); //consulta SQL simples
	//echo json_encode($results); //visualizar os dados no navegador

	$page = new Page(); //construtor vazio

	$page->setTpl("index"); //vai adicionar o arquivo H1 que contém o "hello"
	
});

?>