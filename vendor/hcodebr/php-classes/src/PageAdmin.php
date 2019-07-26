<?php

namespace Hcode;

class PageAdmin extends Page {

    public function __construct($opts=array(), $tpl_dir="/views/admin/"){ //sera necessario criar a pasta admin

        parent::__construct($opts, $tpl_dir); //chamando o método construtor da classe pai: Page
    }

}

?>