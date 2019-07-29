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

}

?>