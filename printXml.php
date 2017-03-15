<?php

/**
 * Created by PhpStorm.
 * User: majko
 * Date: 14.3.17
 * Time: 16:45
 */
class PrintXml
{
    function __construct($table)
    {
        echo "print\n";
        $this->table = $table;
        //print_r($this->table);

        list($first_key) = array_keys($this->table->table->classArray);
        //$first_class_key = $this->table->table->classArray[$firstKey]->class_name;

        //echo "first key = " . $first_class_key . PHP_EOL;

        //if ($this->table->table->classArray[$first_key]->inheritance_from != null){
        echo "first class = " . $first_key . PHP_EOL;
        // $this->printXml($first_key);

        /*foreach ($this->table->table->classArray as $key => $value){
            if ($value->inheritance_from != null){
                $this->printXml($key);
            }
            else {
                echo "class inheritance is empty = ". $value->class_name .PHP_EOL;
            }
        }*/
        $this->printXml($key = $first_key);

    }

    public function printXml()
    {
        // echo "this class doesnt have inheritance\n";
        foreach ($this->table->table->classArray as $key => $obj) {
//            if ($k < 1) continue;
            if(empty($obj->inheritance_from)){
                // read next obj
                echo "\n";
                echo "Root item = ". $key .PHP_EOL;
                //print_r($obj);
            }
            else {
                //$this->inheritClass();
                echo "have inher list =". $key .PHP_EOL;
            }
        }
    }
}