<?php

/**
 * Created by PhpStorm.
 * User: majko
 * Date: 8.3.17
 * Time: 22:33
 */

// STRUCTURE
// ----------------------------------------------------------
// classArray[] -> ClassObject[] ->
//                               -> class_name
//                               -> methods[] ->
//                                            -> method_name
//                                            -> return_type
//                                            -> arguments[]
//                                                          -> var_name
//                                                          -> data_type

// kontext - typ = int
// kontext - id = f

// kontext - arguments[]
// appen argument 1 to arguments[]
// appen argument 2 to arguments[]
// ';'

//$objMethod = new ClassMethod(context.id, cont, context.ar);


// basic pattern for create some arguments method and class
//$emptyArray = array();
/*$objArgument = new MethodArgument('arg1','int');
$objArgument2 = new MethodArgument('arg2','char');
$objClass = new ClassObject();
$objTable = new ClassTable();

$objMethod = new ClassMethod('meno', 'typ', $emptyArray);
array_push($objMethod->method_arguments, $objArgument);
array_push($objMethod->method_arguments, $objArgument2);


$objClass->push_method($objMethod);
$objClass->setClassName("Moja classa 1");

$objTable->push_class($objClass);*/


//r$objTable->printTable();

class ClassTable
{
    function __construct() {
        $this->classArray = array();
    }

    public function printTable(){
        // print classArray
        $i = 0;
        echo "\nCLASSTABLE\n";
        // print class infos
        foreach ($this->classArray as $key => $obj) {

            echo "\nclass[".$i."]\n";
            echo "Key       : $key \nClass name: ". $obj->class_name  . PHP_EOL;
            echo "Methods\n";
            // print method infos
            foreach ($obj->methods as $key2 => $obj2) {
                echo "Key       : ". $key2 . PHP_EOL;
                echo "name      : " . $obj2->method_name . PHP_EOL;
                echo "ret type  : ". $obj2->return_type .PHP_EOL;
                // print variables
                foreach ($obj2->method_arguments as $key3 => $obj3){
                    echo "variable\n";
                    echo "Key       : ". $key3 . PHP_EOL;
                    echo "name      : " . $obj3->var_name . PHP_EOL;
                    echo "data type : " . $obj3->var_data_type . PHP_EOL;
                }
            }
            $i++;
        }
    }
    public function pushClass($class){
        // push to associative array
        $key = $class->class_name;
        $this->classArray[$key] = $class;
    }
}

// FIXME dirty solution as fk

// get last object in Table Array
// $last_obj = new LastClassObject($this->objTable);
class LastClassObject
{
    function __construct($class_table){
        $this->last_obj = $this->getLastObj($class_table);
    }
    public function getLastObj($table){
        $lastKey = end($table->classArray);
        //print_r((Array) $lastKey);
        return $lastKey;
    }

}

// good pattern
class ClassObject
{
    function __construct(){
        $this->methods = array();
        $this->variables = array();
        // array of strings (names of classes) (ClassInheritanceItem )
        $this->inheritance_from = array();
        $this->class_name = '';
    }
    public function setClassName($class_name){
        $this->class_name = $class_name;
    }
    public function getClassName(){
        return $this->class_name;
    }
    public function pushMethod($method) {
        array_push($this->methods, $method);
    }
    public function pushVariable($variable) {
        array_push($this->variables, $variable);
    }
}

class ClassInheritanceItem {
    function __construct(){
        $this->class_inher_name = '';
        $this->class_inher_scope = '';
    }

    public function setName($name) {
        $this->class_inher_name = $name;
    }

    public function setScope($scope) {
        $this->class_inher_scope = $scope;
    }
}

class ClassMethod
{
    function __construct(){
        $this->method_name = '';
        $this->return_type = '';
        $this->method_scope = '';
        $this->method_prefix = '';
        // arguments == array
        $this->method_arguments = array();
    }
    public function setMethodName($method_name){
        $this->method_name = $method_name;
    }
    public function getMethodName(){
        $this->method_name;
    }
    public function setMethodReturnType($return_type){
        $this->return_type = $return_type;
    }
    public function getMethodReturnType(){
        return $this->return_type;
    }
    public function setMethodScope($scope){
        $this->method_scope = $scope;
    }
    public function getMethodScope(){
        $this->method_scope;
    }
    public function setPrefix($prefix){
        $this->method_prefix = $prefix;
    }
    public function getPrefix(){
        return $this->method_prefix;
    }
    public function pushArgument($argument){
        array_push($this->method_arguments, $argument);
    }
}

/*class MethodArgument {
    function __construct($name, $type){
        $this->var_name = $name;
        $this->var_data_type = $type;
    }
    public function setVarName($var_name){
        $this->var_name = $var_name;
    }
    public function getVarName(){
        return $this->var_name;
    }
    public function setVarDataType($var_data_type){
        $this->var_data_type = $var_data_type;
    }
    public function getVarDataType(){
        return $this->var_data_type;
    }
}*/

class ClassVariable {
    function __construct($name, $type, $scope, $prefix){
        $this->var_name = $name;
        $this->var_data_type = $type;
        $this->scope = $scope;
        $this->prefix = $prefix;
    }
    public function setVarName($var_name){
        $this->var_name = $var_name;
    }
    public function getVarName(){
        return $this->var_name;
    }
    public function setVarDataType($var_data_type){
        $this->var_data_type = $var_data_type;
    }
    public function setScope($scope){
        $this->scope = $scope;
    }
    public function getVarDataType(){
        return $this->var_data_type;
    }
}