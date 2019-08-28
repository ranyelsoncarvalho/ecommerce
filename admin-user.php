<?php

//colocar os namespace das classes utilizadas
use \Hcode\PageAdmin;
use \Hcode\Model\User;


//rota para listar todos os usuarios cadastrados
$app->get('/admin/users', function(){
	
	//verificar se o usuario esta logado no sistema
	User::verifyLogin(); //metodo para verificar se o usuario esta logado ou nao

	$users = User::listAll(); //metodo para listar todos os usuarios cadastrados


	//listar todos os usuarios
	$page = new PageAdmin();
	
	//passar os dados que vem do banco para o template
	$page->setTpl('users', array(
		"users"=>$users
	)); //nome do template a ser utilizado
});

//rota para o create
$app->get('/admin/users/create', function(){
	User::verifyLogin();
	$page = new PageAdmin();
	$page->setTpl("users-create"); //template html: users-create

	//envio de um "post" que será salvo por outra rota
});

//excluir um usuario
$app->get('/admin/users/:iduser/delete', function($iduser){
	User::verifyLogin();

	//carregar e verificar o usuario
	$user = new User();
	$user->get((int)$iduser);

	//metodo para deletar na pasta Users
	$user->delete();

	//redirecionamento para a lista de usuarios
	header("Location: /admin/users");
	exit;


});

//rota para o update(editar) - passando o ID do funcionario na URL
$app->get('/admin/users/:iduser', function($iduser)
{
	User::verifyLogin();
	$user = new User();
	$user->get((int)$iduser); //passando o ID do usuario para carregar os dados -->metodo($iduser)
	$page = new PageAdmin();
	$page->setTpl("users-update", array( 
		"user"=>$user->getValues() //metodo GETvalues --> pega todos os dados
	));
});


//envio ao BD - criar novo usuario
$app->post('/admin/users/create', function(){
	User::verifyLogin();
	
	//passando os dados via post
	//var_dump($_POST);

	//passar o dado para o objeto usuarios
	$user = new User();

	//verificar se o cadastro eh admin ou usuario normal
	$_POST["inadmin"] = (isset($_POST["inadmin"]))?1:0;//verifica se o valor foi definido como admin (1) ou nao (0)

	$_POST['despassword'] = password_hash($_POST["despassword"], PASSWORD_DEFAULT, [
 
		"cost"=>12
		
		]);


	$user->setData($_POST); //metodo que cria as varias para o DAO

	//chamar o metodo para salvar
	$user->save();

	//redireciona para a lista de usuarios
	header("Location: /admin/users");
	exit;

});

//salvar a edição, quando o usuario dar o clique em "salvar"
$app->post('/admin/users/:iduser', function($iduser){
	User::verifyLogin();

	$user = new User();

	//verificar se o cadastro eh admin ou usuario normal
	$_POST["inadmin"] = (isset($_POST["inadmin"]))?1:0;//verifica se o valor foi definido como admin (1) ou nao (0)
	
	//carregar os dados atuais do usuario do banco para depois alterar
	$user->get((int)$iduser);

	$user->setData($_POST); //metodo utilizado

	//metodo de atualização, localizado no arquivo User.php
	$user->update();

	header("Location: /admin/users"); //redirecionamento para a lista de usuarios
	exit;
	
});


?>