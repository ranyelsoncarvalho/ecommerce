<?php

namespace Hcode\Model;

use \Hcode\DB\Sql; //buscar a classe SQL criada
use \Hcode\Model;

class User extends Model {

    //constante para a variavel de sessao
    const SESSION = "User";

    //metodo do login
    public static function login($login, $password){
        
        //verificar se ele existe no banco
        $sql = new Sql();

        $results = $sql->select("SELECT * FROM tb_users WHERE deslogin = :LOGIN", array(
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

            //e necessario guardar os dados em uma seção, para verificar se esta logado atribuir a uma seção, senão redireciona para a pagina de login
            $_SESSION[User::SESSION] = $user->getValues();

            return $user;

        }else{
            throw new \Exception("Usuário inexistente ou senha inválida.", 1);
        }

    }

    public static function verifyLogin($inadmin = true){ //verificar se o um usuario logado da administracao
        if(
            //verificar se foi defindo a sessão ou se o valor esta vazio e tambem o ID do usuario
            !isset($_SESSION[User::SESSION]) 
            || 
            !$_SESSION[User::SESSION] 
            ||!(int)$_SESSION[User::SESSION]["iduser"] > 0 //verificar se carrega algum usuario
            ||
            (bool)$_SESSION[User::SESSION]["iduser"] !== $inadmin //verificar se o usuario faz parte do grupo administrativo
        ){
            //redirecionar para o login caso não tenha os dados validados na credencial
            header("Location: /admin/login");
            exit;
        }
    }

    public static function logout(){
        //exlcui a sessão
        $_SESSION[User::SESSION] == NULL;
    }

    public static function listAll(){ //metodo para listar todos os usuarios cadastrados
        $sql = new Sql();
        return $sql->select("SELECT * FROM tb_users a INNER JOIN tb_persons b USING(idperson) ORDER BY b.desperson"); //consulta para listar todos os usuarios cadastrados
    }

    //metodo para salvar os dados no banco de dados
    public function save(){
        $sql = new Sql();
       
        //os dados precisam ser colocados na ordem em que esta na procedure
        $results = $sql->select("CALL sp_users_save(:desperson, :deslogin, :despassword, :desemail, :nrphone, :inadmin)", array(
            ":desperson"=>$this->getdesperson(), //associacao com as chaves
            ":deslogin"=>$this->getdeslogin(),
            ":despassword"=>$this->getdespassword(),
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
            ":iduser"=>$this->getiduser()
        ));

    }

    //metodo para recuperar a senha
    public static function getForgot($email){
        
        //verificar se o email esta na base de dados
        $sql = new Sql();
        $results = $sql->select("SELECT * FROM tb_persons a INNER JOIN tb_users b USING(idperson) WHERE a.desemail = :=email", array(
            //bind parametros
            ":email"=>$email
        ));

        //validar o email
        if(count($results) === 0) //significa que nao retornou nada
        {
            throw new \Exception("Não foi possível recuperar a senha", 1);
        }
        else{

            //pegar os dados que retornaram na posicao 0
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


                //agora e necessario gerar um codigo criptografrado para o usuario
                base64_encode((mcrypt_encrypt(MCRYPT_RIJNDAEL_128)));

            }

        }

    }

}

?>