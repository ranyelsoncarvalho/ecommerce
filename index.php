<?php 

//verificar se a sessao foi iniciada no servidor WEB
session_start();


require_once("vendor/autoload.php"); //require do composer, para trazer as dependencias

use \Slim\Slim; //namespace -- classes que serao utilizadas
//use \Hcode\Page;
//use \Hcode\PageAdmin;
//use \Hcode\Model\User; //para fazer a validadao de usuario e login
//use \Hcode\Model\Category;
//$app = new \Slim\Slim(); //aplicacao do slim para as rotas

$app = new Slim(); //rotas

$app->config('debug', true); //configurado o debug, para apresentar na tela

//criar um arquivo para cada assunto de rota, trazer o arquivo pelo require_once
require_once("site.php"); //rota do site
require_once("admin.php"); //rota do painel administrativo
require_once("admin-user.php"); //rotas do usuario administrativo
require_once("admin-categories.php");
require_once("admin-products.php");

$app->run(); //rodar a aplicação

 ?>