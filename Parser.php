<?php

include("Scanner.php");
include("ClassTable.php");
include("Context.php");

define('DEBUG_SCANNER', false);
define('DEBUG', true);

/**
 * Created by PhpStorm.
 * User: majko
 * Date: 2.3.17
 * Time: 13:57
 */

// TODO co ked chyba pred deklaraciou <AccessModifier> !! osetrit
//class A {
//  virtual int a;
//};
// TODO dorobit parameter list pre viac parametrov
class Parser
{
    // just for print
    public $name_func = "";

    function __construct() {
        // create instance of scanenr
        $this->scanner = new Scanner();
        $this->actual_token = new Token();
        $this->objContext = new Context();
        $this->objTable = new ClassTable();
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

        $this->objContext->printContext();
        $this->objTable->printTable();
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
        $this->printTokenData('startProg   EOF') ;
    }
    /*
     *   <ClassList> -> <Class><ClassList>
     */
    public function parseClassList(){
        if ($this->actual_token->data == 'class') {
            // set actual class name to token
            // read id of class
            $this->getAndSetActualToken();
            $this->printTokenData('Classlist      ');
            // set class name to context
            $this->objContext->setClassName($this->actual_token->data);

            $this->parseClass();
            /*  TODO
             *  after parsing class we can push infos from context
             */
            $this->parseClassList();
        }
    }
    public function parseClass(){

        if ($this->actual_token->state == StatesEnum::S_IDENTIFIER){

            $this->getAndSetActualToken();

            // its non inheritance
            if ($this->actual_token->state == StatesEnum::S_LEFT_VINCULUM){
                $this->printTokenData("Class         {");
                $this->getAndSetActualToken();

                // if is classbody empty dont go inside function
                if ($this->actual_token->state != StatesEnum::S_RIGHT_VINCULUM) {
                    $this->parseClassBody();
                }

                // get }
                // inside classBody read '}' so just print that
                $this->printTokenData("Class         }");
                // get ;
                $this->getAndSetActualToken();
                $this->printTokenData("Class         ;");

                // get 'class' and go up
                $this->getAndSetActualToken();

            }
            // its inheritanceList
            else if ($this->actual_token->state == StatesEnum::S_COLON) {
                $this->printTokenData("Class         :");

                // get AccessModifier
                $this->getAndSetActualToken();
                $this->printTokenData("Class    PPP/id");

                // CALL inherList
                $this->parseInheritanceList();

                // inside inheritanceList read '{' so just print that
                $this->printTokenData("Class         {");

                // get next token and CALL
                $this->getAndSetActualToken();
                $this->printTokenData("Class          ");

                // if is classbody empty dont go inside function
                if ($this->actual_token->state != StatesEnum::S_RIGHT_VINCULUM) {
                    // CALL ClassBody
                    $this->parseClassBody();
                }

                // get }
                //$this->getAndSetActualToken();
                $this->printTokenData("Class         }");

                // get ;
                $this->getAndSetActualToken();
                $this->printTokenData("Class         ;");

                // get 'class' and go up
                $this->getAndSetActualToken();

            }
        }
    }

    public function parseClassBody(){
        // GO TO PPP ?
        // token data == public/protected/private
        if ($this->parseAccessModifier()){

            // read ':'
            $this->getAndSetActualToken();
            $this->printTokenData('ClassBody     :');

            // <Colon>
            if ($this->actual_token->state == StatesEnum::S_COLON){
                $this->getAndSetActualToken();
                $this->parseDeclarations();
            }
        }
        // SUV
        elseif ($this->parsePrefix(0)){
            //$this->printTokenData('Decls       wtf');
            //$this->getAndSetActualToken();
            $this->parseDeclarations();
        }
        else {
            $this->getAndSetActualToken();
            $this->printTokenData('ClassB.       }');
        }
    }

    public function parseInheritanceList(){
        // LONG sign without <AccessModifier>
        // if <AccessModifier>
        // TODO change this nasty if to beautiful if
        if ($this->actual_token->data == 'public' or $this->actual_token->data == 'private'
            or $this->actual_token->data == 'protected'){

            $this->getAndSetActualToken();
            $this->printTokenData("InhList      id");

            if ($this->actual_token->state == StatesEnum::S_IDENTIFIER){

                $this->getAndSetActualToken();
                $this->printTokenData("InhList     ,/{");

                // comma = go to inherList2
                if ($this->actual_token->state == StatesEnum::S_COMMA) {

                    //call inher2
                    $this->parseInheritanceList2();
                }
                // else is read '{' just go up

            }
        }
        // SHORT sign without <AccessModifier>
        else if ($this->actual_token->state == StatesEnum::S_IDENTIFIER){

            $this->getAndSetActualToken();

            // ',' = go to inherList2
            if ($this->actual_token->state == StatesEnum::S_COMMA) {
                $this->printTokenData("InherList     ,");
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
                $this->printTokenData("InhList2     id");


                if ($this->actual_token->state == StatesEnum::S_IDENTIFIER) {

                    // get ',' -> call parseInh2() or  '{' <-
                    $this->getAndSetActualToken();

                    // ',' = go to inherList2
                    if ($this->actual_token->state == StatesEnum::S_COMMA) {
                        $this->printTokenData("InhList2      ,");
                        $this->parseInheritanceList2();
                    }
                    // get '{' just go up
                    else
                        $this->printTokenData("InhList2      {");
                }
            }
            // short sign without <AccessModifier>
            else if ($this->actual_token->state == StatesEnum::S_IDENTIFIER){

                $this->getAndSetActualToken();

                // ',' = go to inherList2
                if ($this->actual_token->state == StatesEnum::S_COMMA) {
                    $this->printTokenData("InhList2      ,");
                    $this->parseInheritanceList2();
                }
                // else is read '{' just go up
                else
                    $this->printTokenData("InhList2      {");
            }
        }
    }

    public function parseDeclarations(){
        // TODO looks like a bug

        // get SUV(<Prefix>)
        if ($this->parsePrefix(0)){
            $this->printTokenData('Decls       suv');
            // get <DataType>
            $this->getAndSetActualToken();
            $this->printTokenData('Decls        DT');

            if ($this->actual_token->data == $this->parseDataType()) {
                // get id
                $this->getAndSetActualToken();
                $this->printTokenData('Decls        id');

                $this->parseDeclaration();
                $this->parseDeclarations();
            }
        }
        // get empty <Prefix> so read DataType
        else if ($this->actual_token->data == $this->parseDataType()){

            $this->printTokenData('Decls        DT');

            $this->getAndSetActualToken();
            $this->printTokenData('Decls        id');

            $this->parseDeclaration();
            $this->parseDeclarations();
        }
        // TODO wtf what it mean?
        //else {
        //    $this->printTokenData('Decls         }');
        //}
    }

    public function parseDeclaration(){
        //echo "DECLARATION". PHP_EOL;
        if ($this->actual_token->state == StatesEnum::S_IDENTIFIER) {
            $this->getAndSetActualToken();

            if ($this->actual_token->state == StatesEnum::S_SEMICOLON){
                $this->printTokenData('Decl          ;');
                $this->getAndSetActualToken();
                $this->printTokenData('Decl   }/suv/DT');
            }
            // go insede pararameter list
            elseif ($this->actual_token->state == StatesEnum::S_LEFT_BRACKET) {
                $this->printTokenData('Decl          (');

                $this->getAndSetActualToken();
                $this->printTokenData('Decl         DT');

                $this->parseParameterList();

                $this->getAndSetActualToken();
                $this->printTokenData('Decl          )');

                $this->getAndSetActualToken();

                $this->parseDeclarationBody();

                $this->getAndSetActualToken();
                $this->printTokenData('Decl          ;');

                //  get }
                // TODO cant get token on this place I think but maybe iam wrong
                $this->getAndSetActualToken();
                $this->printTokenData('Decl           ');
            }
        }
    }

    public function parseDeclarationBody(){

        if ($this->actual_token->state == StatesEnum::S_EQUAL_SIGN){
            $this->printTokenData('DeclBody      =');

            $this->getAndSetActualToken();

            if ($this->actual_token->state == StatesEnum::S_ZERO){
                $this->printTokenData('DeclBody      0');
            }

        }
        // TODO elseif - it can be standard decl body ' {} '
    }

    public function parseParameterList(){

        if ($this->parseDataType()){

            // <DataType> is void
            if ($this->actual_token->data == 'void') {
                $this->printTokenData('ParList    void');
                return;
            }
            // FIXME maybe bad place
            elseif ($this->actual_token->state == StatesEnum::S_IDENTIFIER){
                $this->printTokenData('ParList      id');
            }
        }
        // parameter list is empty
        elseif ($this->actual_token->state == StatesEnum::S_RIGHT_BRACKET){
            $this->printTokenData('ParList      )');
            // FIXME & TESTME
            ///$this->getAndSetActualToken();
        }

    }

    public function parseAccessModifier(){
       switch ($this->actual_token->data){
           case 'public':
           case 'protected':
           case 'private':
               //echo "-------------------------AccessMod=TRUE". PHP_EOL;
               return true;

           default :
               //echo "-------------------------AccessMod=FALSE". PHP_EOL;
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
                case 'int':
                case 'char16_t':
                case 'char32_t':
                case 'wchar_t':
                    //echo "-------------------------DataType=".$string. PHP_EOL;
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
                //echo "-------------------------Prefix-". $this->actual_token->data ."=TRUE". PHP_EOL;
                return ($return_value ? $string : true);

            default :
                //echo "-------------------------Prefix-". $this->actual_token->data ."=FALSE". PHP_EOL;
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

/*class ClassArr
{
    function __construct(){
       $this-> array_of_classes = array();
    }
}*/

$parser = new Parser();