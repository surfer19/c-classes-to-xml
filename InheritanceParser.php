<?php

/**
 * Created by PhpStorm.
 * User: majko
 * Date: 13.3.17
 * Time: 10:37
 */
class InheritanceParser
{
    /**
     * InheritanceParser constructor.
     * @param $table
     */
    function __construct($table){
        $this->table = $table;

        $this->copyInheritanceData();
    }
    /**
     *  TODO
     */
    public function copyInheritanceData(){
        // iterate over all class Object
        foreach ($this->table->classArray as $key_obj => $class_obj){

            //echo "-------------------------------------\n";
            //echo "pasing class = ".$class_obj->class_name .PHP_EOL;
            //echo "read inher class...". PHP_EOL.PHP_EOL;

            // iterate over all class object inheritance items
            foreach ($class_obj->inheritance_from as $key_inher => $inherit_obj) {

                //echo "name             : " . $inherit_obj->class_inher_name . PHP_EOL;
                //echo "scope            : " . $inherit_obj->class_inher_scope . PHP_EOL . PHP_EOL;

                //echo "-------------------------------------\n";
                //echo "FOUND CLASS  \n\n";
                /*
                 *
                 *  Find one element from inheritance list
                 *
                 */
                $helper_key = $inherit_obj->class_inher_name;
                $found_object = $this->table->classArray[$helper_key];

                //echo "find keyyyyyy= " . $helper_key . PHP_EOL;

                $this->copyClassVariables($found_object->variables, $class_obj->variables);
                $this->copyClassMethods($found_object->methods, $class_obj->methods);

                $this->copyClassAbstraction($found_object, $class_obj);
            }
        }

        //print_r($this->table->classArray);

        return $this->table->classArray;

    }
    /**
     * Method do deep copy all variables from found array of variables to actual actual object that we parse.
     *
     * @access public
     * @param array $found_var_arr {
     *      @type ClassVariable
     * }
     * @param array &$actual_class_var_arr {   Its just reference!
     *      @type ClassVariable
     * }
     * @return void
     *
     */
    // TODO one of the variable found is same as variable in actual class == ERROR!!!
    public function copyClassVariables($found_var_arr, &$actual_class_var_arr){
        // deep copy array of variables
        $deep_var_arr = Parser::array_clone($found_var_arr);

        foreach ($deep_var_arr as $key_var => $obj_var){
            array_push($actual_class_var_arr, $obj_var);
        }

        //print_r($actual_class_var_arr);
    }
    /**
     * Method do deep copy all methods from found array of methods to actual actual object that we parse.
     *
     * @access public
     * @param array $found_meth_arr {
     *      @type ClassVariable
     * }
     * @param array &$actual_class_meth_arr {   Its just reference!
     *      @type ClassVariable
     * }
     * @return void
     *
     */
    public function copyClassMethods($found_meth_arr, &$actual_class_meth_arr){
        // deep copy array of methods
        $deep_meth_arr = Parser::array_clone($found_meth_arr);

        foreach ($deep_meth_arr as $key_var => $obj_meth){
            array_push($actual_class_meth_arr, $obj_meth);
        }
    }
    // par 1 - inheritance item
    // par 2 - class obj that we parse
    public function copyClassAbstraction($found_object, &$actual_obj_abstr){
        //var_dump($actual_obj_abstr);
        //echo " neni true\n".PHP_EOL;
        //foreach ($class_obj as $key => $obj)
        if ($found_object->is_abstract == True){
            // set True
            //echo " NASTAVIL SOM NA TRUE\n".PHP_EOL;
            $actual_obj_abstr->is_abstract = True;

            //print_r($found_object);
        }
        //print_r($this->table->classArray);
    }
}
