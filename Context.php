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

    public function setClassName($class_name){
        $this->class_name = $class_name;
    }
    public function printContext(){
        echo "--------------------" .PHP_EOL;
        echo "CONTEXT" .PHP_EOL;
        echo "class name      : ". $this->class_name .PHP_EOL;
    }
}