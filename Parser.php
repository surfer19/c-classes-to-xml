<?php

include("Scanner.php");

define('DEBUG_SCANNER', false);
define('DEBUG', true);

/**
 * Created by PhpStorm.
 * User: majko
 * Date: 2.3.17
 * Time: 13:57
 */
class Parser
{
    function __construct() {

        // create instance of scanenr
        $this->scanner = new Scanner();
        $this->actual_token = new Token();

        $file = fopen("output.cpp","w");
        fwrite($file,"");
        fclose($file);
        /*
         *
         * JUST FOR DEBUG SCANNER
         *
         */
        if(DEBUG_SCANNER):
            echo "\n---  SCANNER  ---\n";
            do {
                $this->scanner->getNextToken();
                echo "TOKEN STATE = ".$this->scanner->token->state;
                echo "\n";
                echo "TOKEN DATA  = ".$this->scanner->token->data;
                echo "\n";
                echo "-------------\n";
            } while($this->scanner->token->state != StatesEnum::S_EOF);
        endif;
        // get first token
        $this->getAndSetActualToken();
        // start parse LL grammar
        $this->startProg();

    }
    public function startProg(){
        if(DEBUG) echo "---------- startProg ----------" .PHP_EOL;

        if($this->actual_token->data == 'class')
            $this->parseClassList();
        if($this->actual_token->state == StatesEnum::S_EOF){
            if (DEBUG) echo "found EOF" . PHP_EOL;
            die(0);
        }
    }
    public function parseClassList(){
        if(DEBUG) echo "---------- parseClassList ----------" .PHP_EOL;
        if(DEBUG) echo "come =". $this->actual_token->data .PHP_EOL;

        if ($this->actual_token->data == 'class'){
            if(DEBUG) echo "---------- found class" .PHP_EOL;

            $this->getAndSetActualToken();
            $this->parseClass();
            $this->parseClassList();
        }
        else
            $this->getAndSetActualToken();
    }

    /**
     * @void
     */
    public function parseClass(){

        if(DEBUG) echo "---------- parseClass" .PHP_EOL;

        //if(DEBUG) echo "state before identifier=".$this->actual_token->state .PHP_EOL;
        if($this->actual_token->state == StatesEnum::S_IDENTIFIER){
            if(DEBUG) echo "---------- found identifier" .PHP_EOL;

            $this->getAndSetActualToken();

            if($this->actual_token->state == StatesEnum::S_LEFT_VINCULUM) {

                $this->getAndSetActualToken();

                if($this->actual_token->state == StatesEnum::S_RIGHT_VINCULUM) {
                    $this->getAndSetActualToken();
                    if($this->actual_token->state == StatesEnum::S_SEMICOLON){
                        $this->getAndSetActualToken();
                    }
                }
                else
                    $this->parseClassBody();
            }
            // read ':' read next char
            else if ($this->actual_token->state == StatesEnum::S_COLON) {
                $this->getAndSetActualToken();
                if(DEBUG) echo "expected: 6/2 got ". $this->actual_token->state .PHP_EOL;
                $this->parseInheritanceList();
                $this->getAndSetActualToken();
            }
        }
    }
    public function parseAccessModifier(){
        if ($this->actual_token->state == StatesEnum::S_KEYWORD){
            $this->getAndSetActualToken();
        }
        //else eps
    }
    //
    public function parseInheritanceList(){
        if(DEBUG) echo "---------- parseInheritanceList" .PHP_EOL;
        /*
         *  class A : B {} or class A : private B {}
         *
         */
        if($this->actual_token->state  == StatesEnum::S_KEYWORD) { // public..

            $this->getAndSetActualToken();

            if($this->actual_token->state == StatesEnum::S_IDENTIFIER){ // id

                $this->getAndSetActualToken();

                if(DEBUG) echo "---------- 9ku=".  $this->actual_token->state .PHP_EOL;

                $this->parseInheritanceList2();
            }
        }
        else if ($this->actual_token->state == StatesEnum::S_IDENTIFIER){
            $this->getAndSetActualToken();
            $this->parseInheritanceList2();
        }

    }
    public function parseInheritanceList2(){
        if(DEBUG) echo "---------- parseInheritanceList2" .PHP_EOL;

        if(DEBUG) echo "expected 9 got ". $this->actual_token->state . "(11 = '{')" .PHP_EOL;
        if ($this->actual_token->state == StatesEnum::S_COMMA) {

            $this->getAndSetActualToken();

            if (DEBUG) echo "---------- 2ku=" . $this->actual_token->state . PHP_EOL;

            if ($this->actual_token->state == StatesEnum::S_KEYWORD) {

                $this->getAndSetActualToken();

                if (DEBUG) echo "---------- 6ku=" . $this->actual_token->state . PHP_EOL;
                if ($this->actual_token->state == StatesEnum::S_IDENTIFIER) {
                    $this->getAndSetActualToken();
                    $this->parseInheritanceList2();
                }
            }
            else if ($this->actual_token->state == StatesEnum::S_IDENTIFIER) {
                $this->getAndSetActualToken();
                $this->parseInheritanceList2();
            }
        }
        else // eps
            $this->getAndSetActualToken();

        $this->getAndSetActualToken();

    }
    public function parseClassBody(){
        if(DEBUG) echo "---------- parseClassBody" .PHP_EOL;

        $this->getAndSetActualToken();
    }

    public function getAndSetActualToken(){

        $this->actual_token = $this->scanner->getNextToken();

        if (DEBUG) echo "--- ". $this->actual_token->data . "" . PHP_EOL;

        //$file = fopen("output.cpp","w");
        $current = file_get_contents("output.cpp");
        // Append a new person to the file
        $current .= $this->actual_token->data;

        if ($this->actual_token->state == StatesEnum::S_KEYWORD  or
            $this->actual_token->state == StatesEnum::S_IDENTIFIER or
            $this->actual_token->state == StatesEnum::S_COLON
        )
            $current .= " ";
        // Write the contents back to the file
        if ($this->actual_token->data == ';')
        { $current .= PHP_EOL; }
        file_put_contents("output.cpp", $current);

        return $this->actual_token;
    }

}

$parser = new Parser();