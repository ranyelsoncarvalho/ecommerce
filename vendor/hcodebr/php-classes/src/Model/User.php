<?php

namespace Hcode\Model;

use \Hcode\DB\Sql; //buscar a classe SQL criada
use \Hcode\Model;
use \Hcode\Mailer;

class User extends Model {

    //constante para a variavel de sessao
    const SESSION = "User";

    //constante de encriptacao, necessaria para criptografar e descriptografar
    const SECRET = "HcodePhp7_Secret";
    
    //constante para a variavel de erro na sessao
    const ERROR = "UserError";

    //constante para a variavel de erro do usuario no cadastro
    const ERROR_REGISTER = "UserErrorRegister";

    //constante para a variavel de sucesso na edicacao dos dados para usuario
    const SUCCESS = "UserSuccess";

    //metodo para buscar os dados da sessao e verificar se o usuario esta logado
    public static function getFromSession(){
        $user = new User();

        //verificar se a sessao existe
        if(isset($_SESSION[User::SESSION]) && (int)$_SESSION[User::SESSION]['iduser'] > 0){

            $user->setData($_SESSION[User::SESSION]); //os dados estao dentro da sessao
          
        }

        return $user;

    }

    //metodo para verificar se o usuario esta logado
    public static function checkLogin($inadmin = true){

        if(!isset($_SESSION[User::SESSION]) //se ela nao esta definida eh sinal que ele nao esta logado
            || 
            !$_SESSION[User::SESSION] 
            ||!(int)$_SESSION[User::SESSION]["iduser"] > 0 //verificar se carrega algum usuario
        ){
            //nao esta logado
            return false;
        } else {
            if ($inadmin === true && (bool)$_SESSION[User::SESSION]['inadmin'] === true) { //fazendo uma verificacao do usuario da administracao
                return true;
            } //verifica se e uma rota da administracao
            else if ($inadmin === false) { //o usuario pode visualizar a parte de cliente
                return true;
            } else {
                return false; //usuario nao estah logado
            }
        }


    }

    //metodo do login do sistema
    public static function login($login, $password){
        
        //verificar se ele existe no banco
        $sql = new Sql();

        $results = $sql->select("SELECT * FROM tb_users a INNER JOIN tb_persons b ON a.idperson = b.idperson WHERE a.deslogin = :LOGIN", array(
            //fazer o bind dos parâmetros
            ":LOGIN"=>$login
        ));

        //verificar se encontrou algum login
        if (count($results)===0){
            //retornar uma exceção
            throw new \Exception("Usuário inexistente ou senha inválida.", 1);
        }

        //joga os resultados na variavel "data"
        $data = $results[0];

        //verificar a senha do usuario
        if (password_verify($password, $data["despassword"]) === true){
            $user = new User();
            $user->setData($data);

            //e necessario guardar os dados em uma sessão, para verificar se esta logado atribuir a uma seção, senão redireciona para a pagina de login
            $_SESSION[User::SESSION] = $user->getValues();

            return $user;

        }else{
            throw new \Exception("Usuário inexistente ou senha inválida.", 1);
        }

    }

    public static function verifyLogin($inadmin = true){ //verificar se o um usuario logado da administracao
        //if(
            //verificar se foi defindo a sessão ou se o valor esta vazio e tambem o ID do usuario
            //!isset($_SESSION[User::SESSION]) 
            //|| 
            //!$_SESSION[User::SESSION] 
            //||!(int)$_SESSION[User::SESSION]["iduser"] > 0 //verificar se carrega algum usuario
            //||
            //(bool)$_SESSION[User::SESSION]["iduser"] !== $inadmin //verificar se o usuario faz parte do grupo administrativo
            //User::checkLogin($inadmin)
        //){
            //redirecionar para o login caso não tenha os dados validados na credencial
        //    header("Location: /admin/login");
        //    exit;
        if(!User::checkLogin($inadmin)){ //se o usuario estiver logado, ele sera redirecionado para a pagina de login
            if($inadmin){
                header("Location: /admin/login"); //redireciona para o login administrativo
            } else {
                header("Location: /login"); //redireciona par ao login do cliente
            }
            exit;
        }
            
        }

    public static function logout(){
        //exlcui a sessão
        $_SESSION[User::SESSION] = NULL;

    }

    public static function listAll(){ //metodo para listar todos os usuarios cadastrados
        $sql = new Sql();
        return $sql->select("SELECT * FROM tb_users a INNER JOIN tb_persons b USING(idperson) ORDER BY b.desperson"); //consulta para listar todos os usuarios cadastrados
    }

    //metodo para retornar a quantidade de usuarios cadastrados
    public function getTotalUsers(){
        $sql = new Sql();
        $results = $sql->select("SELECT COUNT(*) as numberusers FROM tb_users");
        //verificar a quantidade de dados que eh retornada
        if(count($results)> 0){
            return $results[0];
        }else {
            return [];
        }

    }

    //metodo para salvar os dados no banco de dados
    public function save(){
        $sql = new Sql();
       
        //os dados precisam ser colocados na ordem em que esta na procedure
        $results = $sql->select("CALL sp_users_save(:desperson, :deslogin, :despassword, :desemail, :nrphone, :inadmin)", array(
            ":desperson"=>$this->getdesperson(), //associacao com as chaves
            ":deslogin"=>$this->getdeslogin(),
            ":despassword"=>User::getPasswordHash($this->getdespassword()),
            ":desemail"=>$this->getdesemail(),
            ":nrphone"=>$this->getnrphone(),
            ":inadmin"=>$this->getinadmin()
        )); //a insercao sera por meio de um procedure, deixa a aplicação mais rapida, pois executa apenas uma vez
        
        //so interessa o primeiro dado do resultado
        $this->setData($results[0]);

    }

    public function get($iduser){ //recuperar os dados para fazer a edicao do cadastro
        $sql = new Sql();
        $results = $sql->select("SELECT * FROM tb_users a INNER JOIN tb_persons b USING(idperson) WHERE a.iduser = :iduser", array(
            ":iduser"=>$iduser
        ));

        $data = $results[0];
        $this->setData($data);
    }

    public function update(){ //atualizar qualquer registro no banco de dados
        $sql = new Sql();
       
        //os dados precisam ser colocados na ordem em que esta na procedure: sp_usersupdate_save
        $results = $sql->select("CALL sp_usersupdate_save(:iduser, :desperson, :deslogin, :despassword, :desemail, :nrphone, :inadmin)", array(
            ":iduser"=>$this->getiduser(), //para atualizar o registro no BD
            ":desperson"=>$this->getdesperson(), //associacao com as chaves
            ":deslogin"=>$this->getdeslogin(),
            ":despassword"=>$this->getdespassword(),
            ":desemail"=>$this->getdesemail(),
            ":nrphone"=>$this->getnrphone(),
            ":inadmin"=>$this->getinadmin()
        )); //a insercao sera por meio de um procedure, deixa a aplicação mais rapida, pois executa apenas uma vez
        
        //so interessa o primeiro dado do resultado, ele pega o resultado e joga no objeto
        $this->setData($results[0]);
    }

    public function delete(){ //metodo para deletar usuario

        $sql = new Sql();

        $sql->query("CALL sp_users_delete(:iduser)", array( //realiza a exclusao por meio de uma procedure
            ":iduser"=>$this->getiduser() //em um objeto dentro da propria classe
        ));
       

    }

    //metodo para recuperar a senha
    public static function getForgot($email){
        
        //verificar se o email esta na base de dados
        $sql = new Sql();
        $results = $sql->select("SELECT * FROM tb_persons a INNER JOIN tb_users b USING(idperson) WHERE a.desemail = :email", array(
            //bind parametros
            ":email"=>$email
        ));

        //validar o email
        if(count($results) === 0) //significa que nao retornou nada
        {
            throw new \Exception("Não foi possível recuperar a senha", 1);
        }
        else{

            //pegar os dados que retornaram na posicao 0, dados que vem do banco
            $data = $results[0];

            //vamos criar um novo registro na tabela de recuperacao de senha, utilizando uma procedure
            $results2 = $sql->select("CALL sp_userspasswordsrecoveries_create(:iduser, :desip)", array(
                ":iduser"=>$data["iduser"],
                ":desip"=>$_SERVER["REMOTE_ADDR"] //pegar o IP do usuario
            ));


            //verificar se criou a variavel $results2
            if(count($results2)===0)
            {
                throw new \Exception("Não foi possível recuperar a senha", 1);
            }
            else{
                
                //vai conter os dados recuperados
                $dataRecovery = $results2[0]; //jogando os dados para o objeto


                //criptografia do dado
                //$code = openssl_encrypt($dataRecovery['idrecovery'], 'AES-128-CBC', pack("a16", User::SECRET), 0, pack("a16", User::SECRET_IV));
                $code = base64_encode(openssl_encrypt($dataRecovery["idrecovery"],"AES-128-ECB",User::SECRET));
                $code = base64_encode($code);

                //montar o link no qual sera recebido o código, que sera encaminhado pelo email
                $link = "http://www.hcodecommerce.com.br/admin/forgot/reset?code=$code"; //necessario criar a rota "reset", "?" significa que sera passado via POST

                //enviar por e-mail o código para resetar a senha
                $mailer = new Mailer($data["desemail"], $data["desperson"], "Redefinir senha da HCode Store", "forgot", array(
                    //dados a serem renderizados no template email
                    "name"=>$data["desperson"], //nome do usuario
                    "link"=>$link
                ));

                //enviar o email
                $mailer->send();

                return $link;

                //retornar os dados do usuario que foi recuperado


            }

        }

    }

    //metodo para atualizar a senha do usuario
    public function setPassword($password) //recebe a senha em texto puro e necessario passar o hash
	{
		$sql = new Sql();
		$sql->query("UPDATE tb_users SET despassword = :password WHERE iduser = :iduser", array(
			":password"=>$password,
			":iduser"=>$this->getiduser()
		));
	}


    //metodos para as variaveis de sessao de erro
    public static function setError($msg)
	{
		$_SESSION[User::ERROR] = $msg;
    }
    
	public static function getError()
	{
		$msg = (isset($_SESSION[User::ERROR]) && $_SESSION[User::ERROR]) ? $_SESSION[User::ERROR] : '';
		User::clearError();
		return $msg;
    }
    
	public static function clearError()
	{
		$_SESSION[User::ERROR] = NULL;
    }
    
    //metodos para as variaveis de sessao de erro: SUCCESS
    public static function setSuccess($msg)
	{
		$_SESSION[User::SUCCESS] = $msg;
    }
    
	public static function getSuccess()
	{
		$msg = (isset($_SESSION[User::SUCCESS]) && $_SESSION[User::SUCCESS]) ? $_SESSION[User::SUCCESS] : '';
		User::clearSuccess();
		return $msg;
    }
    
	public static function clearSuccess()
	{
		$_SESSION[User::SUCCESS] = NULL;
    }


    //metodo para variaveis de erro
    public static function setErrorRegister($msg) {

        $_SESSION[User::ERROR_REGISTER] = $msg;

    }

    //pegar o erro da sessao
    public static function getErrorRegister()
	{
		$msg = (isset($_SESSION[User::ERROR_REGISTER]) && $_SESSION[User::ERROR_REGISTER]) ? $_SESSION[User::ERROR_REGISTER] : '';
		User::clearErrorRegister(); //limpar o erro da sessao
		return $msg;
    }
    
    public static function clearErrorRegister() //metodo para limpar o erro da sessao
	{
		$_SESSION[User::ERROR_REGISTER] = NULL;
    }
    
    //verificar se jah existe determinado login, para impedir de dois usuarios com o mesmo login
    public static function checkLoginExist($login)
	{
		$sql = new Sql();
		$results = $sql->select("SELECT * FROM tb_users WHERE deslogin = :deslogin", [
			':deslogin'=>$login
		]);
		return (count($results) > 0); //se retornar maior que 0, quer dizer que tem ja tem um usuario com esse login
    }
    
    //metodo para adicionar o hash na senha do usuario ao criar a conta
    public static function getPasswordHash($password) 
    {   
        return password_hash($password, PASSWORD_DEFAULT, [
            'cost'=>12
        ]);
    }

    //metodo para trazer o pedido do usuario
    public function getOrders()
    {
        //conexao com banco
        $sql = new Sql();

        //joga os dados da consulta em uma variavel chamada "results"
        $results = $sql->select("SELECT * 
			FROM tb_orders a 
			INNER JOIN tb_ordersstatus b USING(idstatus) 
			INNER JOIN tb_carts c USING(idcart)
			INNER JOIN tb_users d ON d.iduser = a.iduser
			INNER JOIN tb_addresses e USING(idaddress)
			INNER JOIN tb_persons f ON f.idperson = d.idperson
			WHERE a.iduser = :iduser
		", [
			':iduser'=>$this->getiduser() //uso do this, pois esta na mesma classe
        ]);
        
        return $results;
    }

    //metodo para a paginacao de usuarios
    public static function getPage($page = 1, $itemPerPage = 3) { //$page = numero da pagina atual; $itemPerPage = numero de itens a serem carregados na pagina
        
        $start = ($page - 1) * $itemPerPage; //onde sera iniciada a query

        //inicia a instancia SQL
        $sql = new Sql();

        //realiza a query no banco de dados: sql_calc_foun_rows --> calcula quantas linhas foram retornadas
        $results = $sql->select("SELECT SQL_CALC_FOUND_ROWS * 
            FROM tb_users a 
            INNER JOIN tb_persons b 
            USING (idperson) 
            ORDER BY b.desperson
            LIMIT $start, $itemPerPage;");

        //numero de linhas retornadas
        $resultTotal = $sql->select("SELECT FOUND_ROWS() AS nrtotal;");
        
        return [
            'data'=>$results, //todas as linhas resultadas da consulta
            'total'=>(int)$resultTotal[0]['nrtotal'], //numero total de registros
            'pages'=>ceil($resultTotal[0]['nrtotal'] / $itemPerPage) //numero total de paginas
        ];


    }

    //metodo para a paginacao de usuarios com a variavel de busca
    public static function getPageSearch($search, $page = 1, $itemPerPage = 3) { //$page = numero da pagina atual; $itemPerPage = numero de itens a serem carregados na pagina
        
        $start = ($page - 1) * $itemPerPage; //onde sera iniciada a query

        //inicia a instancia SQL
        $sql = new Sql();

        //realiza a query no banco de dados: sql_calc_foun_rows --> calcula quantas linhas foram retornadas
        $results = $sql->select("SELECT SQL_CALC_FOUND_ROWS * 
            FROM tb_users a 
            INNER JOIN tb_persons b USING (idperson) 
            WHERE b.desperson LIKE :search OR b.desemail = :search OR a.deslogin LIKE :search 
            ORDER BY b.desperson
            LIMIT $start, $itemPerPage;", [
                //bind do parametro de busca
                ':search'=>'%'.$search.'%'
            ]);

        //numero de linhas retornadas
        $resultTotal = $sql->select("SELECT FOUND_ROWS() AS nrtotal;");
        
        return [
            'data'=>$results, //todas as linhas resultadas da consulta
            'total'=>(int)$resultTotal[0]['nrtotal'], //numero total de registros
            'pages'=>ceil($resultTotal[0]['nrtotal'] / $itemPerPage) //numero total de paginas
        ];


    }


}

?>