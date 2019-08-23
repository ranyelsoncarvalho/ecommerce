<?php 

//verificar se a sessao foi iniciada no servidor WEB
session_start();


require_once("vendor/autoload.php"); //require do composer, para trazer as dependencias

use \Slim\Slim; //namespace -- classes que serao utilizadas
use \Hcode\Page;
use \Hcode\PageAdmin;
use \Hcode\Model\User; //para fazer a validadao de usuario e login
use \Hcode\Model\Category;
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

//rotas para o CRUD do projeto

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
	$page->setTpl("users-create");

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

//rota para o template de categoria
$app->get("/admin/categories", function(){

	//verificar se o usuario esta logado
	User::verifyLogin();

	$categories = Category::listAll(); //classe de categoria e o metodo de categorias (arquivo: Category.php)

	$page = new PageAdmin();

	$page->setTpl("categories", [
		'categories'=>$categories
	]);
});

//rota para visualizar as categorias de produto
$app->get("/admin/categories/create", function(){

	//verificar se o usuario esta logado
	User::verifyLogin();

	$page = new PageAdmin();
	$page->setTpl("categories-create"); //retorna a view para cadastrar uma categoria

});

//rota para cadastrar a categoria
$app->post("/admin/categories/create", function(){

	//verificar se o usuario esta logado
	User::verifyLogin();

	$category = new Category();

	//carregar o dado que vem do formulario
	$category->setData($_POST);

	//salvar o metodo
	$category->save();

	header('Location: /admin/categories'); //redirecionamento apos o cadastro da categoria
	exit;

});

//rota para a exclusao da categoria
$app->get("/admin/categories/:idcategory/delete", function($idcategory){

	//verificar se o usuario esta logado
	User::verifyLogin();
	
	//cria o objeto category
	$category = new Category();

	//carregar o objeto para ver se ele existe no BD
	$category->get((int)$idcategory);

	//metodo para a exclusao
	$category->delete();

	//redirecionar para a lista de categorias
	header('Location: /admin/categories'); //redirecionamento apos o cadastro da categoria
	exit;

});

//rota para editar uma categoria de produto
$app->get("/admin/categories/:idcategory", function($idcategory){

	//verificar se o usuario esta logado
	User::verifyLogin();

	$category = new Category();

	//carregar a categoria que foi passada na url
	$category->get((int)$idcategory);

	//mostra um HTML para a edicao da categoria
	$page = new PageAdmin();
	$page->setTpl("categories-update", [
		'category'=>$category->getValues()
	]); //passa o nome da views

});

//rota para efetuar a edicao na categoria do produto
$app->post("/admin/categories/:idcategory", function($idcategory){

	//verificar se o usuario esta logado
	User::verifyLogin();

	$category = new Category();

	//carregar a categoria que foi passada
	$category->get((int)$idcategory);

	//carrega os dados atuais
	$category->setData($_POST);

	//salvar a edicao da categoria
	$category->save();

	//fazer o redirect para as categorias
	header('Location: /admin/categories'); //redirecionamento apos o cadastro da categoria
	exit;

});

//rota para as categorias
$app->get("/categories/:idcategory", function($idcategory){

	$category = new Category();

	//recuperar a categoria que foi passada no GET
	$category->get((int)$idcategory);

	//chamar o template do site onde esta a categoria
	$page = new Page();

	//template que sera chamado
	$page->setTpl("category", [
		'category'=>$category->getValues(),
		'products'=>[] //colocar os produtos que vem do banco de dados
	]);

});

$app->run(); //rodar a aplicação

 ?>