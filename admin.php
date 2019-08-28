<?php

//colocar os namespace das classes utilizadas
use \Hcode\PageAdmin;
use \Hcode\Model\User;



//criar a rota para o painel administrativo
$app->get('/admin', function() { //rota principal
	
	//fazer a validacao do login
	User::verifyLogin(); //criar o metodo dentro da classe usuario para fazer a validadao do usuario e so redirecionar para a pagina de login

	$page = new PageAdmin(); //construtor vazio

	$page->setTpl("index"); //vai adicionar o arquivo H1 que contém o "hello"
	
});

//rota para o login
$app->get('/admin/login', function(){
	$page = new PageAdmin([
		//desabilitar a construção do header e footer da página, pois são diferentes na página do login
		"header"=>false,
		"footer"=>false
	]);
	$page->setTpl("login"); //chamando a página criada (template)
});

//rota para o envio dos dados do formulário de login
$app->post('/admin/login', function(){

	//validacao do login
	User::login($_POST["login"], $_POST["password"]);

	//redireciona para a homepage do usuario ao fazer o login
	header("Location: /admin");
	exit; //para a execução

});

//rota para fazer o logout
$app->get('/admin/logout', function(){
	User::logout();

	//redireciona para a tela de login
	header("Location: /admin/login");
	exit;

	//"/admin/logout" joga este endereco para o template de logout, arquivo: header.html --> no href=
});

//rota para a funcionalidade: resetar senha
$app->get('/admin/forgot', function(){

	//vai ser similar a tela de login
	$page = new PageAdmin([
		//desabilitar a construção do header e footer da página, pois são diferentes na página do login
		"header"=>false,
		"footer"=>false
	]);
	$page->setTpl("forgot"); //chamando a página criada (template)
});

//criar a rota para a solicitacao do email para troca de senha
$app->post('/admin/forgot', function(){

	//necessario buscar o email que o usuario cadastro, lembrando que isto e feito via post
	$user = User::getForgot($_POST["email"]); //metodo na classe User.php para recuperar a senha

	//redirect para confirmar para o usuario que o email foi enviado com sucesso
	header("Location: /admin/forgot/sent");
	exit;

});

//criar a rota SENT
$app->get('/admin/forgot/sent', function(){
	//rendenrizar o template do SENT
	$page = new PageAdmin([
		//desabilitar a construção do header e footer da página, pois são diferentes na página do login
		"header"=>false,
		"footer"=>false
	]);
	$page->setTpl("forgot-sent"); //chamando a página criada (template)
});



?>