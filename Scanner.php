<?php

/**
 * Created by PhpStorm.
 * User: majko
 * Date: 18.2.17
 * Time: 13:51
 */
class Scanner
{
    function __construct(){
        echo "start scanner \n";

        #$token = new Token();
        #$token->getNextToken();
        $this->token = new Token();
        $this->file = $this->readFromFile();
        $this->getNextToken();
    }
    public function readFromFile(){
        $file = fopen("input/main.cpp", "r");

        /*while(!feof($file)){
            $char = fgetc($file);
            $this->getNextToken($char);
        }*/
        return $file;
        //fclose($file);
    }
    public function getNextToken(){
        $state = StatesEnum::S_START;
        //echo "start state = ".$state;
        while(1){
            $char = fgetc($this->file);
            //echo "precital som znak\n". $char;

            switch($state){
                case StatesEnum::S_START:

                    if (ctype_space($char)){
                        //echo "its space\n";
                        $state = StatesEnum::S_START;
                    }
                    else if(feof($this->file)){
                        return 1;
                    }
                    else if(ctype_alpha($char)) {
                        echo "FOUND char\n";
                    }
                    switch ($char) {
                        case '{':
                        case '}':
                        case '(':
                        case ')':
                        case '=':
                        case '*':
                        case '&':
                        case ':':
                        case ',':
                        case '~':
                        case ';':
                            echo "char $char \n";
                            $this->editToken($char);
                            return $this->token;
                            break;
                        default:
                            echo "CHARACTER NOT FOUND LEL :(\n";
                            break;
                    }
                break;
            }
        }
        return $this->token;
    }
    public function editToken($data){
        $this->token->data = $data;
    }
}

/* keywords = class, public, protected, private, using, virtual a static
   data_types  =  bool, char, char16_t, char32_t, wchar_t, , int
                  float, double, long double, void
signed char
short int
long int
long long int
unsigned char
unsigned short int
unsigned int
unsigned long int
unsigned long long int

*/

class Token {
    function __construct(){
        $this->state = StatesEnum::S_START;
        $this->data = '';
    }

}

abstract class StatesEnum {
    const S_START            = 0;
    const S_END              = 1;
    const S_KEYWORD          = 2;  // class, int etc..
    const S_EQUAL_SIGN       = 3;  // '='
    const S_STAR             = 4;  // '*'
    const S_AMPERESAND       = 5;  // '&'
    const S_IDENTIFIER       = 6;  // variable1, var2...
    const S_COLON            = 7;  // ':'
    const S_SEMICOLON        = 8;  // ';'
    const S_COMMA            = 9;  // ','
    const S_WAVE             = 10; // '~'
    const S_LEFT_VINCULUM    = 11; // '{'
    const S_RIGHT_VINCULUM   = 12; // '}'
    const S_LEFT_BRACKET     = 13; // '('
    const S_RIGHT_BRACKET    = 14; // ')'
    const S_EOF              = 15; // EOF
}



$scanner = new Scanner();
//$obj->doSomething();