<?php

namespace Hcode; //namespace principal

class Model { //classe para fazer os getters e setters

    private $values = [];

    public function __call($name, $args){

        //identificar o tipo de metodo que vem: get ou set
        //uso da funcao "substr" na posição 0, trazer: 3 (três posições)  
        $method = substr($name, 0, 3);
        $fieldName = substr($name, 3, strlen($name)); //pegar a partir da terceira posição

        //iniciar o metodo identificado
        switch ($method) {
            case 'get':
                    return $this->values[$fieldName]; //vai buscar um determinado valor
                break;
            case 'set':
                    $this->values[$fieldName] = $args[0]; //vai atribuir um determinado valor
                break;
        }

    }

    //funcao para organizar os dados que vem do banco, deixa-lo acessivel
    public function setData($data = array())
    {
        foreach ($data as $key => $value) {
            $this->{"set".$key}($value); //realizar o acesso via get e set
        }
    }

    //retorna os atritubos
    public function getValues(){
        return $this->values;
    }
}
?>