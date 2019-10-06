<?php

//colocar os namespace das classes utilizadas
use \Hcode\Page;

//chamar a classe de produtos, para que eles sejam carregados na home do site
use \Hcode\Model\Product;
use \Hcode\Model\Category;
use \Hcode\Model\Cart;
use \Hcode\Model\Address;
use \Hcode\Model\User;

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
	]); //vai adicionar o arquivo H1 que contÃ©m o "hello"
	
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

//rota para o login do usuario no sistema de compras, deixa apenas se o usuario estiver logado --> parte de checkout
$app->get("/checkout", function(){

	//fazer a validacao do login
	User::verifyLogin(false); //identificar se nao eh uma rota da administracao

	//pegar o endereco
	$address = new Address(); //classe Address
	
	//pegar o carrinho que esta na sessao
	$cart = Cart::getFromSession();

	//verificar o endereco para ser carregado
	if(isset($_GET['zipcode'])){
		$_GET['zipcode'] = $cart->getdeszipcode();
	}


	//verificar se o CEP foi enviado ou nao
	if(isset($_GET['zipcode'])){
		//carregar o objeto endereco
		$address->loadFromCEP($_GET['zipcode']); //metodo para carregar o endereco correto do CEP

		//colocar no novo endereco no carrinho tambem
		$cart->setdeszipcode($_GET['zipcode']);

		//salva o carrinho no BD
		$cart->save();

		//forca atualizar o subtotal
		$cart->getCalculateTotal();

	}


	//verificar o endereo passado no formulario vazio
	if(!$address->getdesaddress()) $address->setdesaddress('');
	if(!$address->getdescomplement()) $address->setdescomplement('');
	if(!$address->getdesdistrict()) $address->setdesdistrict('');
	if(!$address->getdescity()) $address->setdescity('');
	if(!$address->getdesstate()) $address->setdesstate('');
	if(!$address->getdescountry()) $address->setdescountry('');
	if(!$address->getdeszipcode()) $address->setdeszipcode('');



	//criar a pagina do html
	$page = new Page();

	//chamar o template do html
	$page->setTpl("checkout", [
		//passar as variaveis necessarias para renderizar no proprio html
		'cart'=>$cart->getValues(), //a variavel 'cart' vai receber o carrinho, passando os valores dele por meio do 'getValues()'
		'address'=>$address->getValues(),
		'products'=>$cart->getProducts(), //carregar os produtos que estao no carrinho
		'error'=>Address::getMsgError()//recuperar a mensagem de errro para o template

	]); //chamando o template "checkout.html"

});

//rota para salvar os dados do carrinho no banco  (endereco, cidade, etc)
$app->post("/checkout", function(){

	//verificar se o usuario esta logado
	User::verifyLogin(false);

	//validacao dos dados
	if(!isset($_POST['zipcode']) || $_POST['zipcode'] === ''){
		//apresetar a mensagem de erro para o usuario
		Address::setMsgError("CEP.");
		header("Location: /checkout");
		exit;
	}
	if(!isset($_POST['desaddress']) || $_POST['desaddress'] === ''){
		//apresetar a mensagem de erro para o usuario
		Address::setMsgError("Endereço.");
		header("Location: /checkout");
		exit;
	}
	if(!isset($_POST['desdistrict']) || $_POST['desdistrict'] === ''){
		//apresetar a mensagem de erro para o usuario
		Address::setMsgError("Bairro.");
		header("Location: /checkout");
		exit;
	}
	if(!isset($_POST['descity']) || $_POST['descity'] === ''){
		//apresetar a mensagem de erro para o usuario
		Address::setMsgError("Cidade.");
		header("Location: /checkout");
		exit;
	}
	if(!isset($_POST['desstate']) || $_POST['desstate'] === ''){
		//apresetar a mensagem de erro para o usuario
		Cart::setMsgError("Estado.");
		header("Location: /checkout");
		exit;
	}
	if(!isset($_POST['descountry']) || $_POST['descountry'] === ''){
		//apresetar a mensagem de erro para o usuario
		Address::setMsgError("País.");
		header("Location: /checkout");
		exit;
	}


	//pegar o usuario da sessao
	$user = User::getFromSession();

	//criar um novo endereco
	$address = new Address();

	//receber o post do formulario
	$_POST['deszipcode'] = $_POST['zipcode'];
	$_POST['idperson'] = $user->getidperson();
	
	//colocar os dados no POST
	$address->setData($_POST);

	//utiliza o metodo para salvar
	$address->save();

	//redirecionar para a pagina de pagamento
	header("Location: /order");
	exit;

});

//rota para o login do SITE, para o cliente realizar a compra do produto
$app->get("/login", function(){

	//criar a pagina
	$page = new Page();


	//chamar o template do login do cliente
	$page->setTpl("login",[
		//passar a variavel do erro ao template
		'error'=>User::getError(), //para que a funcao possa retornar o erro 
		'errorRegister'=>User::getErrorRegister(), //metodo para trazer o erro quando o cliente esta fazendo o cadastro
		'registerValues'=>(isset($_SESSION['registerValues'])) ? $_SESSION['registerValues'] : ['name'=>'', 'email'=>'', 'phone'=>''] //funcao para guardar os dados na sessao, antes de enviar para o banco de dados
	]);

});

//rota para acessar a pagina de login via post, vindo do formulario HTML
$app->post("/login", function(){

	//apresentar o erro na tela para o usuario, caso o login falhe
	try{

		//apenas verificar o login do usuario
		User::login($_POST['login'], $_POST['password']); //o metodo eh estatico, por isso nao eh feito o "new", passar o login do usuario via post
	
	} catch (Exception $e) {
		User::setError($e->getMessage());
	}

	
	//redirecionar para a tela do checkout;
	header("Location: /checkout");
	exit;


});

//rota para efetuar o logout do cliente na loja
$app->get("/logout", function(){

	//chamar o metodo que realizar o logout
	User::logout();

	//redireciona para a tela de login
	header("Location: /login");
	exit;

});

//rota para fazer o cadastro do cliente no site da loja
$app->post("/register", function(){


	//para nao perder os dados preenchido caso falte algum dado na hora do cadastro
	//solucao: colocao o dado da sessao
	$_SESSION['registerValues'] = $_POST;

	//antes de enviar para o banco eh necessario fazer algumas validações
	//verificar se o nome foi enviado
	if (!isset($_POST['name']) || $_POST['name'] == ''){

		User::setErrorRegister("Preencha o seu nome."); //metodo para apresentar uma msg ao usuario
		header("Location: /login");
		exit;

	}

	//verificar se o email foi digitado
	if (!isset($_POST['email']) || $_POST['email'] == ''){

		User::setErrorRegister("Preencha o seu email."); //metodo para apresentar uma msg ao usuario
		header("Location: /login");
		exit;

	}

	//verificar se a senha foi digitada
	if (!isset($_POST['password']) || $_POST['password'] == ''){

		User::setErrorRegister("Preencha a senha."); //metodo para apresentar uma msg ao usuario
		header("Location: /login");
		exit;

	}

	//fazer a verificacao para permitir que o login seja unico
	if (User::checkLoginExist($_POST['email']) === true) {
		User::setErrorRegister("Este e-mail ja esta cadastrado."); //metodo para apresentar uma msg ao usuario
		header("Location: /login");
		exit;
	}

	$_SESSION['registerValues'] = NULL; // Zerando a sessão para limpar os dados do formulário.
	
	
	//receber os dados do formulario para criar o usuario
	$user = new User();

	$user->setData([ //array para receber os dados e fazer o save no banco
		'inadmin'=>0,//forca o inadmin ser 0 para não ser administrador
		'deslogin'=>$_POST['email'], //campos que estao vindo do formulario HTML
		'desperson'=>$_POST['name'],
		'desemail'=>$_POST['email'],
		'despassword'=>$_POST['password'],
		'nrphone'=>$_POST['phone']
	]);

	$user->save(); //metodo para salvar o usuario no bd


	//para fazer a autenticacao do usuario apos ele realizar o cadastrdo
	User::login($_POST['email'], $_POST['password']);

	//redireciona para a tela do checkout
	header("Location: /checkout");
	exit;

});


//rota para acessar Minha Conta - profile do usuario
$app->get("/profile", function(){

	//eh necessario o usuario estar logado --> passa como "false" para indicar que nao e administrativo
	User::verifyLogin(false);


	//recuperar dados do usuario que esta na sessao
	$user = User::getFromSession();

	//cria o template
	$page = new Page();

	//chama o novo template: profile.html
	$page->setTpl("profile", [
		//passar as informacoes que serao carregadas no template: usuario, msg de erro e demais informacoes
		'user'=>$user->getValues(),
		'profileMsg'=>User::getSuccess(), //passar a mensagem de sucesso
		'profileError'=>User::getError() //passando o erro para o template
	]);
	

});

//rota para realizar as alteracoes nos dados cadastrados do usuario logado (edicao)
$app->post("/profile", function(){

	//eh necessario o usuario estar logado --> passa como "false" para indicar que nao e administrativo
	User::verifyLogin(false);

	//fazer algumas validacoes para nao deixar os campos em branco
	if(!isset($_POST['desperson']) || $_POST['desperson'] === ''){
		//apresentar as mensagens de erro
		User::setError("Preencha o seu nome."); //agora e necessario passar o erro para o template
		header('Location: /profile'); //redireciona para a pagina do perfil
		exit;
	}

	if(!isset($_POST['desemail']) || $_POST['desemail'] === ''){
		//apresentar as mensagens de erro
		User::setError("Preencha o seu email."); //agora e necessario passar o erro para o template
		header('Location: /profile'); //redireciona para a pagina do perfil
		exit;
	}

	//recuperar dados do usuario que esta na sessao
	$user = User::getFromSession();

	//verificar se o novo login ja existe na base de dados
	if($_POST['desemail'] !== $user->getdesemail()){
		//significa que o usuario alterou o email, lembrando que o email deve ser unico
		if (User::checkLoginExist($_POST['desemail']) === true){
			User::setError("Este endereço já é cadastrado"); //agora e necessario passar o erro para o template
			header('Location: /profile'); //redireciona para a pagina do perfil
			exit;
		}
	}


	//nao permitir que o usuario possa alterar o INADMIN para se tornar ADM
	$_POST['inadmin'] = $user->getinadmin();
	$_POST['despassword'] = $user->getdespassword();
	$_POST['deslogin'] = $_POST['desemail'];

	//instanciar os dados para fazer a alteracao
	$user->setData($_POST);

	//metodo para atualizar os dados alterados
	$user->update();

	//mostrar os dados alterados no template
	$_SESSION[User::SESSION] = $user->getValues();

	//passar a mensagem de sucesso para a alteracao do cadastrado
	User::setSuccess("Dados alterados com sucesso!");

	//retornar para a pagina do formulario
	header('Location: /profile');
	exit;

});




?>