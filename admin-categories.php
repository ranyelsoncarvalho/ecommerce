<?php

//colocar os namespace das classes utilizadas
use \Hcode\PageAdmin;
use \Hcode\Page;
use \Hcode\Model\User;
use \Hcode\Model\Category;


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

?>