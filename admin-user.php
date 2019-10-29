<?php

//colocar os namespace das classes utilizadas
use \Hcode\PageAdmin;
use \Hcode\Model\User;


//rota para listar todos os usuarios cadastrados
$app->get('/admin/users', function(){
	
	//verificar se o usuario esta logado no sistema
	User::verifyLogin(); //metodo para verificar se o usuario esta logado ou nao

	//$users = User::listAll(); //metodo para listar todos os usuarios cadastrados

	$search = (isset($_GET['search'])) ? $_GET['search'] : ""; //variavel para fazer a busca de usuarios no portal admin
	$page = (isset($_GET['page'])) ? (int)$_GET['page'] : 1;//verificar se a pagina foi definida
	
	//filtro para retornar os usuarios da busca
	if($search!=''){
		$pagination = User::getPageSearch($search, $page); //metodo para a paginacao de usuarios com a busca
	}else {
		$pagination = User::getPage($page); //metodo para a paginacao de usuarios sem a busca
	}
	

	//montar as paginas
	$pages = [];

	//percorrer e resultar a paginacao
	for ($x=0; $x < $pagination['pages']; $x++){
		array_push($pages, [
			'href'=>'/admin/users?'.http_build_query([
				'page'=>$x+1,
				'search'=>$search
			]), //fazer com que a paginacao continue na proxima pagina
			'text'=>$x+1 //texto a ser carregado na busca	(numero da pagina)
		]);
	}


	//listar todos os usuarios
	$page = new PageAdmin();
	
	//passar os dados que vem do banco para o template
	$page->setTpl('users', array(
		"users"=>$pagination['data'],
		"search"=>$search, //realiza a busca de usuarios
		"pages"=>$pages //variavel para a paginacao
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
 
		"cost"=>12 //documentacao do php para gerar o hash da senha
		
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
	//verificar se o usuario esta logado
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

//rota para alterar a senha do usuario
$app->get("/admin/users/:iduser/password", function($iduser){

	//verificar se o usuario esta logado
	User::verifyLogin();

	//carregar o novo usuario, ao receber o ID dele
	$user = new User();

	$user->get((int)$iduser); //trazer as informacoes do usuario

	//carregar o template
	$page = new PageAdmin();

	$page->setTpl("users-password", [ //chamar o template a ser carregado para alterar a senha
		//passar as variaveis para o template
		"user"=>$user->getValues(), //passar os dados do usuario
		"msgError"=>User::getError(), //variaveis para apresentar o erro
		"msgSuccess"=>User::getSuccess() //variavel para apresentar a mensagem de sucesso
	]); 

});

//rota para a alteracao da senha via POST-->confirmar a alteracao
$app->post("/admin/users/:iduser/password", function($iduser){

	//verificar se o usuario esta logado
	User::verifyLogin();

	//fazer algumas validacoes: verificar se a senha foi definida, se e diferente da atual
	if(!isset($_POST['despassword']) || $_POST['despassword'] === ''){
		User::setError("Preencha a nova senha."); //apresenta a msg de erro
		header("Location: /admin/users/$iduser/password");//realiza o redirect, passando o ID a ser alterado
		exit;
	}

	//verificar se digitou a confirmacao da senha
	if(!isset($_POST['despassword-confirm']) || $_POST['despassword-confirm'] === ''){
		User::setError("Preencha a confirmacao da nova senha."); //apresenta a msg de erro
		header("Location: /admin/users/$iduser/password");//realiza o redirect, passando o ID a ser alterado
		exit;
	}

	//verificar se as senhas digitadas sao iguais
	if($_POST['despassword'] !== $_POST['despassword-confirm']){
		User::setError("Confirme corretamente as senhas."); //apresenta a msg de erro
		header("Location: /admin/users/$iduser/password");//realiza o redirect, passando o ID a ser alterado
		exit;
	}

	//carregar o novo usuario, ao receber o ID dele
	$user = new User();

	$user->get((int)$iduser); //trazer as informacoes do usuario

	//metodo post nao possui tela

	//chama o metodo para alterar a senha
	$user->setPassword(User::getPasswordHash($_POST['despassword'])); //passando a senha ja com o hash para o banco

	//apresentar a mensagem de sucesso e o redirect da pagina
	User::setSuccess("Senha alterada com sucesso!");
	header("Location: /admin/users/$iduser/password");
	exit;

});


?>