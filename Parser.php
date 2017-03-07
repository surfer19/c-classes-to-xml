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
    // just for print
    public $name_func = "";

    function __construct() {

        // create instance of scanenr
        $this->scanner = new Scanner();
        $this->actual_token = new Token();
        $this->classArr = new classArr;
        /*$file = fopen("output.cpp","w");
        fwrite($file,"");
        fclose($file);*/
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
        echo "\n-- START PARSING --  \n".PHP_EOL;

        // get first token
        $this->getAndSetActualToken();
        // start parse LL grammar
        $this->startProg();

        if ($this->actual_token->state == StatesEnum::S_EOF) {
            echo "\n-- SUCCESSFULLY PARSED --  " . PHP_EOL;
        }
        else
            echo "\n-- PARSE ERROR !!!--  " . PHP_EOL;

    }
    /*
     *   <Prog> -> <ClassList><Eof>
     */
    public function startProg(){
        if($this->actual_token->data == 'class'){
            $this->printTokenData('startProg      ');
            $this->parseClassList();
        }
        // comes token EOF so print them
        $this->printTokenData('startProg      ') ;

    }
    /*
     *   <ClassList> -> <Class><ClassList>
     */
    public function parseClassList(){
        if ($this->actual_token->data == 'class') {
            //$this->printTokenData('Classlist      ');
            $this->getAndSetActualToken();
            $this->printTokenData('Classlist      ');
            $this->parseClass();
            $this->parseClassList();
        }
    }
    public function parseClass(){

        if ($this->actual_token->state == StatesEnum::S_IDENTIFIER){

            $this->getAndSetActualToken();

            // its non inheritance
            if ($this->actual_token->state == StatesEnum::S_LEFT_VINCULUM){
                $this->printTokenData("Class exp     {");
                $this->getAndSetActualToken();
                $this->parseClassBody();

                // get }
                // inside classBody read '}' so just print that
                $this->printTokenData("Class exp     }");
                // get ;
                $this->getAndSetActualToken();
                $this->printTokenData("Class exp     ;");

                // get 'class' and go up
                $this->getAndSetActualToken();

            }
            // its inheritanceList
            else if ($this->actual_token->state == StatesEnum::S_COLON) {
                $this->printTokenData("Class     exp :");

                // get AccessModifier
                $this->getAndSetActualToken();
                $this->printTokenData("Class    PPP/id");

                // CALL inherList
                $this->parseInheritanceList();

                // inside inheritanceList read '{' so just print that
                $this->printTokenData("Class     exp {");

                // get next token and CALL
                $this->getAndSetActualToken();
                $this->printTokenData("Class          ");
                // CALL ClassBody
                $this->parseClassBody();

                // get }
                $this->getAndSetActualToken();
                $this->printTokenData("Class     exp }");

                // get ;
                $this->getAndSetActualToken();
                $this->printTokenData("Class     exp ;");

                // get 'class' and go up
                $this->getAndSetActualToken();

            }
        }
    }

    public function parseInheritanceList(){
        // LONG sign without <AccessModifier>
        // if <AccessModifier>
        // TODO change this nasty if to beautiful if
        if ($this->actual_token->data == 'public' or $this->actual_token->data == 'private'
            or $this->actual_token->data == 'protected'){

            $this->getAndSetActualToken();
            $this->printTokenData("InhList  exp id");

            if ($this->actual_token->state == StatesEnum::S_IDENTIFIER){

                $this->getAndSetActualToken();
                $this->printTokenData("InhList        ");

                // comma = go to inherList2
                if ($this->actual_token->state == StatesEnum::S_COMMA) {
                    $this->getAndSetActualToken();
                    $this->printTokenData("InherList   exp ,");

                }
                // else is read '{' just go up

            }
        }
        // SHORT sign without <AccessModifier>
        else if ($this->actual_token->state == StatesEnum::S_IDENTIFIER){

            $this->getAndSetActualToken();

            // ',' = go to inherList2
            if ($this->actual_token->state == StatesEnum::S_COMMA) {
                $this->printTokenData("InherList exp ,");
                $this->parseInheritanceList2();
            }
            // else is read '{' just go up
        }
    }
    public function parseInheritanceList2(){
        // ',' parse next expression
        if ($this->actual_token->state == StatesEnum::S_COMMA) {

            // get <AccessModifier>
            $this->getAndSetActualToken();
            $this->printTokenData("InhList2       ");

            // if <AccessModifier>
            // TODO change this nasty if to beautiful if
            if ($this->actual_token->data == 'public' or $this->actual_token->data == 'private'
                or $this->actual_token->data == 'protected'
            ) {

                $this->getAndSetActualToken();
                $this->printTokenData("InhList2 exp id");


                if ($this->actual_token->state == StatesEnum::S_IDENTIFIER) {

                    // get ',' -> call parseInh2() or  '{' <-
                    $this->getAndSetActualToken();

                    // ',' = go to inherList2
                    if ($this->actual_token->state == StatesEnum::S_COMMA) {
                        $this->printTokenData("InhList2  exp ,");
                        $this->parseInheritanceList2();
                    }
                    // get '{' just go up
                    else
                        $this->printTokenData("InhList2  exp {");
                }
            }
            // short sign without <AccessModifier>
            else if ($this->actual_token->state == StatesEnum::S_IDENTIFIER){

                $this->getAndSetActualToken();

                // ',' = go to inherList2
                if ($this->actual_token->state == StatesEnum::S_COMMA) {
                    $this->printTokenData("InhList2  exp ,");
                    $this->parseInheritanceList2();
                }
                // else is read '{' just go up
                else
                    $this->printTokenData("InhList2  exp {");
            }
        }
    }

    public function parseClassBody(){
        // GO TO PPP ?
        // token data == public/protected/private
        if ($this->parseAccessModifier()){

            // read ':'
            $this->getAndSetActualToken();
            $this->printTokenData('ClassBody exp :');

            // <Colon>
            if ($this->actual_token->state == StatesEnum::S_COLON){
                //  $this->getAndSetActualToken();
                //  expect <Prefix> or <DataType>
                //  $this->printTokenData('ClassB.  SUV|DT');
                $this->getAndSetActualToken();
                $this->printTokenData('ClassBody      ');
                $this->parseDeclarations();
            }
        }
    }

    public function parseDeclarations(){

        // call getNextTOKEN
        // is token parsePrefix() or parseDataType() == true??
        // yes get NExt token ....
        // recursive();
        // TODO is public, protected, virtual, static, int IN KEYWORD ARRAY???
        if ($this->actual_token->state == StatesEnum::S_KEYWORD) {

            if ($this->parsePrefix(0) or ($this->actual_token->data == $this->parseDataType())) {
                //echo "CALL RECURSIVE". PHP_EOL;
                $this->parseDeclaration();
                $this->parseDeclarations();
            }
        }
    }

    public function parseDeclaration(){
        //echo "DECLARATION". PHP_EOL;
        if ($this->actual_token->data == $this->parsePrefix(1)) {
            $this->getAndSetActualToken();
            $this->printTokenData('Declaration');
            $this->getAndSetActualToken();
            $this->printTokenData('Declaration');
            $this->getAndSetActualToken();
            $this->printTokenData('Declaration');

        }

        // get one token more and read them up
        $this->getAndSetActualToken();
        $this->printTokenData('Declaration');

    }

    public function parseAccessModifier(){
       switch ($this->actual_token->data){
           case 'public':
           case 'protected':
           case 'private':
               echo "----------------------AccessMod=TRUE". PHP_EOL;
               return true;

           default :
               echo "----------------------AccessMod=FALSE". PHP_EOL;
               return false;

       }
    }

    public function parseDataType(){
        if ($this->actual_token->data == 'signed'){
            //TODO
        }
        else if ($this->actual_token->data == 'unsigned'){
            //TODO
        }
        else if ($this->actual_token->data == 'long'){
            //TODO
        }
        else {

            $string = $this->actual_token->data;

            switch ($string) {
                case 'double':
                case 'float':
                case 'bool':
                case 'void':
                case 'char':
                case 'char16_t':
                case 'char32_t':
                case 'wchar_t':
                    return $string;
                    break;

                default:
                    break;
            }
        }
    }

    public function parsePrefix($return_value){

        $string = $this->actual_token->data;

        switch ($string){
            case 'static':
            case 'using':
            case 'virtual':
                echo "----------------------Prefix-". $this->actual_token->data ."=TRUE". PHP_EOL;
                return ($return_value ? $string : true);

            default :
                echo "----------------------Prefix-". $this->actual_token->data ."=FALSE". PHP_EOL;
                return false;

        }
    }

    public function printTokenData($name_func){
        echo  $name_func ." | ". $this->scanner->token->data .PHP_EOL;
    }

    public function getAndSetActualToken(){
        $this->actual_token = $this->scanner->getNextToken();
        return $this->actual_token;
    }

}

class ClassArr
{
    function __construct(){
       $this-> array_of_classes = array();
    }
}

$parser = new Parser();