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

        //todo get it to nice function
        $this->writer = new XMLWriter();
        $this->writer->openURI('out/test.out');
        $this->writer->startDocument('1.0','UTF-8');
        $this->writer->setIndent(4);
        $this->printTree();

        $this->writer->endDocument();
        $this->writer->flush();


    }

    public function printTree()
    {
        $this->writer->startElement("model");
        //$this->writer->writeElement("price_per_quantity", 110);
        // echo "this class doesnt have inheritance\n";
        foreach ($this->table->table->classArray as $key => $obj) {

            // print root item
            //$this->writer->startElement("class");
            //$this->writer->endElement();

            if (empty($obj->inheritance_from)) {
                $this->writer->startElement("class");
                $this->writer->writeAttribute('name', $key);
                $this->writer->endElement();
                // read next obj
                //echo "\n";
                //echo "Root item = " . $key . PHP_EOL;
                //echo "vetva ma aspon jeden child\n";
                if (!empty($obj->inheritance_child)) {

                  //  echo "child && ma roota\n";
                    $this->printChild($obj->inheritance_child[0]->child_name);
                }
            }
            else if (!empty($obj->inheritance_child)) {
                //echo "vetva ma aspon jeden child\n";
                $this->printChild($obj->inheritance_child[0]->child_name);
            }
            // else do nothink
            else {
                echo "else";
            }
        }

        $this->writer->endElement();


    }

    public function printChild($child_name)
    {
        echo "child name = ". $child_name . PHP_EOL;
    }

}