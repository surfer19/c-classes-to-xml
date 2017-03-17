<?php

//require_once('Parser.php');

class Scanner {

    private $i;

    function __construct($input_dir){
        // create instance of Token
        $this->token = new Token();
        // create instance of keyWords
        $this->keyWords = new KeyWords();
        // open file
        $this->input_dir = $input_dir;
        $this->file = "";
        $this->i = 0;
        $this->readFromFile();

        //$this->newParser = new Parser();
        /*do {
            echo "-----------------\n";
            $this->getNextToken();
            echo "TOKEN STATE = ".$this->token->state;
            echo "\n";
            echo "TOKEN DATA = ".$this->token->data;
            echo "\n";
            echo "-----------------\n";
        } while($this->token->state != StatesEnum::S_EOF);*/
        // close file
        //$this->closeFile();
    }
    public function readFromFile(){

        if ($this->input_dir != "") {
            $this->file = file_get_contents($this->input_dir);
        }
        else {
            $this->file = file_get_contents("php://stdin");
        }

    }
    public function editToken($new_character, $new_state){
        $this->token->data .= $new_character;
        $this->token->state = $new_state;
    }
    public function clearToken(){
        $this->token->state = 0;
        $this->token->data = '';
    }
    public function getNextToken(){

        // INITIAL STATE
        $state = StatesEnum::S_START;

        //clear token
        $this->clearToken();


        while(1){
            $char = substr($this->file, $this->i, 1);
            $this->i++;

            switch($state){
                case StatesEnum::S_START:

                    if (ctype_space($char)){
                        //echo "its space\n";
                        $state = StatesEnum::S_START;
                        break;
                    }
                    else if ($char == '0'){
                        $this->editToken('0', StatesEnum::S_ZERO);

                        return $this->token;
                    }
                    else if(ctype_alpha($char) || ctype_alnum($char) || $char == '_') {

                        $state = StatesEnum::S_IDENTIFIER;
                        $this->editToken($char, StatesEnum::S_IDENTIFIER);
                        break;
                    }
                    switch ($char) {
                        case '{':
                            $this->editToken($char, StatesEnum::S_LEFT_VINCULUM);
                            return $this->token;
                        case '}':
                            $this->editToken($char, StatesEnum::S_RIGHT_VINCULUM);
                            return $this->token;
                        case '(':
                            $this->editToken($char, StatesEnum::S_LEFT_BRACKET);
                            return $this->token;
                        case ')':
                            $this->editToken($char, StatesEnum::S_RIGHT_BRACKET);
                            return $this->token;
                        case '=':
                            $this->editToken($char, StatesEnum::S_EQUAL_SIGN);
                            return $this->token;
                        case '*':
                            $this->editToken($char, StatesEnum::S_STAR);
                            return $this->token;
                        case '&':
                            $this->editToken($char, StatesEnum::S_AMPERESAND);
                            return $this->token;
                        case ':':
                            $this->editToken($char, StatesEnum::S_COLON);
                            return $this->token;
                        case ',':
                            $this->editToken($char, StatesEnum::S_COMMA);
                            return $this->token;
                        case '~':
                            $this->editToken($char, StatesEnum::S_WAVE);
                            return $this->token;
                        case ';':
                            $this->editToken($char, StatesEnum::S_SEMICOLON);
                            return $this->token;
                    }
                    break;

                case StatesEnum::S_IDENTIFIER:
                    if (ctype_alpha($char) || ctype_alnum($char) || $char == '_'){
                        $this->editToken($char, StatesEnum::S_IDENTIFIER);
                        $state = StatesEnum::S_IDENTIFIER;
                        // try if is identifier=keyword and change state
                        if(in_array($this->token->data, $this->keyWords->keywords)){
                            $this->token->state = StatesEnum::S_KEYWORD;
                        }
                    }
                    else {
                        $this->i--;
                        $state = StatesEnum::S_END;
                    }
                    break;

                case StatesEnum::S_END:
                    $this->i--;
                    return $this->token;
            }
        }
        return $this->token;
    }
}


class Token {
    function __construct(){
        $this->state = StatesEnum::S_START;
        $this->data = '';
    }
}

class KeyWords {
    public $keywords = array("class", "public", "protected", "private", "using", "virtual", "static",
                             "signed", "unsigned", "short", "long", "char", "int");
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
    const S_ZERO             = 15; // '0'
    const S_EOF              = 16; // EOF
}



