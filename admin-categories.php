<?php

//colocar os namespace das classes utilizadas
use \Hcode\PageAdmin;
use \Hcode\Page;
use \Hcode\Model\User;
use \Hcode\Model\Category;
use \Hcode\Model\Product;


//rota para o template de categoria
$app->get("/admin/categories", function(){

	//verificar se o usuario esta logado
	User::verifyLogin();

	//$categories = Category::listAll(); //classe de categoria e o metodo de categorias (arquivo: Category.php)

	$search = (isset($_GET['search'])) ? $_GET['search'] : ""; //variavel para fazer a busca de categoria no portal admin
	$page = (isset($_GET['page'])) ? (int)$_GET['page'] : 1;//verificar se a pagina foi definida
	
	//filtro para retornar as categorias da busca
	if($search!=''){
		$pagination = Category::getPageSearch($search, $page); //metodo para a paginacao de categoria com a busca
	}else {
		$pagination = Category::getPage($page); //metodo para a paginacao de categoria sem a busca
	}
	

	//montar as paginas
	$pages = [];

	//percorrer e resultar a paginacao
	for ($x=0; $x < $pagination['pages']; $x++){
		array_push($pages, [
			'href'=>'/admin/categories?'.http_build_query([
				'page'=>$x+1,
				'search'=>$search
			]), //fazer com que a paginacao continue na proxima pagina
			'text'=>$x+1 //texto a ser carregado na busca	(numero da pagina)
		]);
	}

	$page = new PageAdmin();

	//passar as variaveis para o template
	$page->setTpl("categories", [
		"categories"=>$pagination['data'], //carregar os dados das categorias
		"search"=>$search, //realiza a busca de categorias
		"pages"=>$pages //variavel para a paginacao
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



//rota para acessar as (categorias e linkar com os produtos)
$app->get("/admin/categories/:idcategory/products", function($idcategory){

	//verificar se o usuario esta logado
	User::verifyLogin();

	$category = new Category();

	//recuperar a categoria que foi passada no GET, carrega os dados da categoria (id)
	$category->get((int)$idcategory);

	//chamar o template do site onde esta a categoria
	$page = new PageAdmin();

	//template que sera chamado
	$page->setTpl("categories-products", [
		'category'=>$category->getValues(),
		'productsRelated'=>$category->getProducts(true), //passar os produtos relacionados, por padrao o metodo vai chamar os produtos relacionados
		'productsNotRelated'=>$category->getProducts(false)
	]);

});

//rota para adicionar categoria do produto
$app->get("/admin/categories/:idcategory/products/:idproduct/add", function($idcategory, $idproduct){

	//verificar se o usuario esta logado
	User::verifyLogin();

	//classe categoria e carrega a categoria que foi passada via GET
	$category = new Category();
	$category->get((int)$idcategory);

	//carregar o produto que foi passado via GET
	$product = new Product();
	$product->get((int)$idproduct);

	//chama o metodo para cadastrar categoria do produto
	$category->addProduct($product);

	//redirecionar para a lista da relacao de categoria e produto
	header("Location: /admin/categories/".$idcategory."/products");
	exit;
});

//rota para remover categoria do produto
$app->get("/admin/categories/:idcategory/products/:idproduct/remove", function($idcategory, $idproduct){

	//verificar se o usuario esta logado
	User::verifyLogin();

	//classe categoria e carrega a categoria que foi passada via GET
	$category = new Category();
	$category->get((int)$idcategory);

	//carregar o produto que foi passado via GET
	$product = new Product();
	$product->get((int)$idproduct);

	//chama o metodo para remover categoria do produto
	$category->removeProduct($product);

	//redirecionar para a lista da relacao de categoria e produto
	header("Location: /admin/categories/".$idcategory."/products");
	exit;
});

//metodo de paginacao


?>