<?php

/**
 * Created by PhpStorm.
 * User: majko
 * Date: 14.3.17
 * Time: 16:45
 */
class PrintXml
{
    function __construct($table, $out_dir)
    {

        $this->table = $table;
        $this->out_dir = $out_dir;
        //todo get it to nice function

        $this->dom = new DOMDocument('1.0');
        $this->root = $this->dom->createElement('model');
        $this->dom->appendChild($this->root);

        $this->dom->formatOutput = true;
        $this->dom->preserveWhiteSpace = true;

        $this->printTree();
        
        if ($this->out_dir != "") {
            file_put_contents($this->out_dir, $this->dom->saveXML());
        }
        else {
            echo $this->dom->saveXML();
        }

    }

    public function printTree()
    {

        foreach ($this->table->table->classArray as $key => $obj) {
            // is root ?
            // what if is root
            if (empty($obj->inheritance_from)) {
                // create element
                $class_tag = $this->dom->createElement('class');

                $this->root->appendChild($class_tag);

                $class_tag->setAttribute('name',$key);
                if ($obj->is_abstract) {
                    $class_tag->setAttribute('abstract',$key);
                }
                else {
                    $class_tag->setAttribute('concrete',$key);
                }
                //var_dump($class_tag);
                // just for first iteration
                $root_helper = 1;
                $this->printChild($key, $class_tag, $root_helper);

            }

        }

    }

    public function printChild($class_name, $class_tag, $root_helper)
    {
        if ($root_helper == 0) {
            //$this->writer->startElement("class");
            //$this->writer->writeAttribute('name', $child_name);
            $inner_class_tag = $this->dom->createElement('class');

            // create attribute
            $class_attribute_name = $this->dom->createAttribute('name');
            $class_attribute_name->value = $class_name;
            $class_attribute_kind = $this->dom->createAttribute('kind');

            if ($this->table->table->classArray[$class_name]->is_abstract) {
                $class_attribute_kind->value = 'abstract';
            }
            else {
                $class_attribute_kind->value = 'concrete';
            }

            $inner_class_tag->appendChild($class_attribute_name);
            $inner_class_tag->appendChild($class_attribute_kind);

            $class_tag->appendChild($inner_class_tag);


            //print_r($inner_class_tag);
            //echo "dalsia iter\n";
        }
        else {
            $inner_class_tag = $class_tag;
        }
        //  is some inheritance child
        if (!empty($this->table->table->classArray[$class_name]->inheritance_child)){

            foreach ($this->table->table->classArray[$class_name]->inheritance_child as $key => $obj){
                $this->printChild($obj->child_name, $inner_class_tag, $root_helper = 0);
            }
        }
        else {
            return;
        }
    }
}