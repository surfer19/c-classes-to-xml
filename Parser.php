<?php

include("Scanner.php");
define('DEBUG_SCANNER', false);
define('DEBUG', true);
//DEBUG ? $debug->writeLine("stuff is ".$str) : null;
/*
 * if (DEBUG):
    $debug->writeLine("stuff");
   endif;
*/
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
        $this->actual_token = 0;

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

        $this->parseClassList();

    }
    public function parseClassList(){

        $this->actual_token = $this->scanner->getNextToken();

        if ($this->actual_token->data == 'class'){
            if(DEBUG) echo "found some class" .PHP_EOL;
        }
        else if ($this->actual_token->state == StatesEnum::S_EOF)
            if(DEBUG) echo "found EOF" .PHP_EOL;
        //else if ()

    }

}


$parser = new Parser();