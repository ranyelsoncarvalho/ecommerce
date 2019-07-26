<?php

//eh necessario indicar onde a classe esta localizada
namespace Hcode;

use Rain\Tpl; //framework para o template

class Page{

    
    private $tpl; //para ser acessivel
    private $options = [];
    //opcoes default
    private $defaults = [
        "data"=>[]
    ];

    //metodo construtor (metodo magico)
    public function __construct($opts = array()){

    //fazer o merge com a variavel options -- mesclar
    $this->options = array_merge($this->defaults, $opts); //um array irá sobrescrever o outro
    
    
    //configurar o template
    $config = array(
        "tpl_dir"       => $_SERVER["DOCUMENT_ROOT"]."/views/", //pasta para pegar os arquivos HTML, criar essas pastas no diretorio do projeto
        "cache_dir"     => $_SERVER["DOCUMENT_ROOT"]."/views-cache/", //pasta criada no diretorio do projeto
        "debug"         => false // set to false to improve the speed
       );

    Tpl::configure($config);
    
    //cria a variavel, para ser acessivel para as outras classes
    $this->tpl = new Tpl;

    $this->setData($this->options["data"]);

    //percorrer os dados
    //foreach ($this-options["data"] as $key => $value) {
    //    $this->tpl->assign($key, $value); //atribuição de variaveis que irão aparecer no template
    //}
    //as variaveis serão carregadas dependendo da rota
    
    //desenhar o template na tela, ele espera o arquivo a ser chamado
    $this->tpl->draw("header"); //o arquivo "header" irá repetir para todos, ele sera criado dentro da pasta views

    }


    private function setData($data = array()){
        
        foreach ($data as $key => $value) {
            $this->tpl->assign($key, $value); //atribuição de variaveis que irão aparecer no template
        }

    }

    //corpo da pagina -- conteudo
    public function setTpl($name, $data = array(), $returnHTML = false){

        $this->setData($data); //pegar os dados que estão na variavel data e fazer o assign
        return $this->tpl->draw($name, $returnHTML);//desenhar o template na tela
    
    }

    //ultimo a ser executado
    public function __destruct(){
        //desenhar o rodape, que sera repetido em todas as paginas
        $this->tpl->draw("footer"); //arquivo html que sera criado
    }

}

?>