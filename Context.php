<?php

/**
 * Created by PhpStorm.
 * User: majko
 * Date: 10.3.17
 * Time: 19:29
 */
class Context
{
    private $class_name = '';
    // variable or method scope - class A { public: ... }
    private $scope = '';
    // DECLARATIONS
    private $prefix = '';
    private $data_type = '';
    private $declaration_id = '';
    private $parameters = array();

    // TODO
    // remember argument that we parse

    // TODO - we dont know that we parsing variable or method declaration
    //


    public function setClassName($class_name){
        $this->class_name = $class_name;
    }
    public function getClassName(){
        return $this->class_name;
    }

    public function setScope($scope){
        $this->scope = $scope;
    }
    public function getScope(){
        return $this->scope;
    }

    public function setDataType($type){
        $this->data_type = $type;
    }
    public function getDataType(){
        return $this->data_type;
    }

    public function setDeclarationId($id){
        $this->declaration_id = $id;
    }
    public function getDeclarationId(){
        return $this->declaration_id;
    }

    public function setPrefix($pref){
        $this->prefix = $pref;
    }

    public function getPrefix(){
        return $this->prefix;
    }

    public function pushParameter($parameter){
        array_push($this->parameters, $parameter);
    }

    public function printContext(){
        echo "--------------------" .PHP_EOL;
        echo "CONTEXT" .PHP_EOL;
        echo "class name      : ". $this->class_name .PHP_EOL;
        echo "scope           : ". $this->scope.PHP_EOL;
        echo "decl prefix     : ". $this->prefix.PHP_EOL;
        echo "decl id         : ". $this->declaration_id.PHP_EOL;
    }
}

class ContextParameter
{
    private $data_type = '';
    private $var_id = '';
    private $is_void = False;

    public function setDataType($type){
        $this->data_type = $type;
    }
    public function setVarId($id){
        $this->var_id = $id;
    }
    public function setVoidTrue(){
        $this->is_void = True;
    }
}