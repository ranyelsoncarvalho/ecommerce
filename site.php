<?php

//colocar os namespace das classes utilizadas
use \Hcode\Page;

//chamar a classe de produtos, para que eles sejam carregados na home do site
use \Hcode\Model\Product;
use \Hcode\Model\Category;
use \Hcode\Model\Cart;

$app->get('/', function() { //rota principal (home do site)
    
	//echo "OK";
	//$sql = new Hcode\DB\Sql(); //classe SQL dentro do Vendor: DB -->namespace
	//$results = $sql->select("SELECT * FROM tb_users"); //consulta SQL simples
	//echo json_encode($results); //visualizar os dados no navegador


	//carregar os produtos que estao cadastrados no banco
	$products = Product::listAll();

	$page = new Page(); //construtor vazio

	$page->setTpl("index", [
		'products'=>Product::checkList($products) //passando a variavel para listar todos os produtos cadastrados
	]); //vai adicionar o arquivo H1 que contém o "hello"
	
});

//rota para as categorias dos produtos
$app->get("/categories/:idcategory", function($idcategory){

	//verificar se o usuario esta logado
	//User::verifyLogin();

	//receber a pagina
	$page = (isset($_GET['page'])) ? (int)$_GET['page'] : 1;//verificar se foi definido no GET da pagina

	$category = new Category();

	//recuperar a categoria que foi passada no GET
	$category->get((int)$idcategory);

	//informacoes da pagina, preciso passar a pagina atual
	$pagination = $category->getProductsPage($page);

	//array para passar as paginas
	$pages = [];
	for($i = 1; $i<=$pagination['pages']; $i++){
		array_push($pages, [
			'link'=>'/categories/'.$category->getidcategory().'?page='. $i,
			'page'=>$i //numero da pagina que sera visualizado
		]); //adicionar item ao array
	}

	//chamar o template do site onde esta a categoria
	$page = new Page();

	//template que sera chamado para carregar os produtos de determinada categoria
	$page->setTpl("category", [
		'category'=>$category->getValues(),
		//'products'=>Product::checkList($category->getProducts()) //colocar os produtos que vem do banco de dados de acordo com a categoria, passar o metodo "checklist" para carregar a foto
		'products'=>$pagination["data"],
		'pages'=>$pages
	]);

});

//rota para o produto
$app->get("/products/:desurl", function($desurl){

	$product = new Product();

	//metodo para retornar a URL do produto - metodo criado na classe de produto
	$product->getFromURL($desurl);




	//chamar o template do site
	$page = new Page();


	//template que sera chamado para carregar os detalhes do produto
	$page->setTpl("product-detail", [
		//dados que serao carregados do produto
		'product'=>$product->getValues(), //variavel 'product' vem do template
		'categories'=>$product->getCategories() 	//metodo para trazer as categorias do produto
	]);


});

//rota para acessar o carrinho de compras
$app->get("/cart", function(){
	
	$cart = Cart::getFromSession();

	$page = new Page(); //chamar o template do site

	$page->setTpl("cart", [
		'cart'=>$cart->getValues(), //dados que vem da variavel cart, que podem ser apresentados no template
		'products'=>$cart->getProducts(), //metodo para retornar todos os produtos do carrinho
		'error'=>Cart::getMsgError()
	]); //passar as informacoes do carrinho

});

//rota para adicionar produto no carrinho
$app->get("/cart/:idproduct/add", function($idproduct){

	$product = new Product();

	//carregar o produto
	$product->get((int)$idproduct);

	//recuperar o carrinho da sessao
	$cart = Cart::getFromSession();

	//para adicionar junto ao carrinho a quantidade definida pelo usuario
	$qtd = (isset($_GET['qtd']))? (int)$_GET['qtd'] : 1;

	//chamar o metodo a quantidade de vezes necessarias
	for ($i = 0; $i < $qtd; $i++) {
		
		//metodo para adicionar o produto no carrinho
		$cart->addProduct($product);

	}

	//redirecionar para visualizar o carrinho
	header("Location: /cart");
	exit;

});


//rota para remover um produto do carrinho
$app->get("/cart/:idproduct/minus", function($idproduct){

	$product = new Product();

	//carregar o produto
	$product->get((int)$idproduct);

	//recuperar o carrinho da sessao
	$cart = Cart::getFromSession();

	//metodo para remover o produto no carrinho
	$cart->removeProduct($product);

	//redirecionar para visualizar o carrinho
	header("Location: /cart");
	exit;

});

//rota para remover todos os produtos do carrinho
$app->get("/cart/:idproduct/remove", function($idproduct){

	$product = new Product();

	//carregar o produto
	$product->get((int)$idproduct);

	//recuperar o carrinho da sessao
	$cart = Cart::getFromSession();

	//metodo para remover todos os produtos do carrinho
	$cart->removeProduct($product, true);

	//redirecionar para visualizar o carrinho
	header("Location: /cart");
	exit;

});

//rota para receber os dados do formulario do carrinho para realizar o calculo do frete
$app->post("/cart/freight", function(){
	
	//pegar o carrinho que esta na sessao
	$cart = Cart::getFromSession();

	//metodo para passar o CEP
	$cart->setFreight($_POST['zipcode']); //dados que vem do formulario

	//redireciona para a tela do carrinho
	header("Location: /cart");
	exit;


});




?>