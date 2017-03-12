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

// TODO what is correct?  int f(void) {} or int f(void) {};

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

        //$this->objContext->printContext();
        //$this->objTable->printTable();
        print_r($this->objContext);
        //var_dump($this->objContext);

        // invented print_r() and var_dump() in 2017
        print_r((array) $this->objTable);

        //$objLast = new LastClassObject($this->objTable);
        $this->end();
    }
    /*
     *   <Prog> -> <ClassList><Eof>
     */
    public function startProg(){

        if($this->actual_token->data == 'class'){
            echo "         EXPECT | COME\n";
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

            /*
             *
             *  CONTEXT class name + push object to TABLE
             */
            // set class name to context
            $this->objContext->setClassName($this->actual_token->data);

            $objClass = new ClassObject();
            $objClass->setClassName($this->objContext->getClassName());
            $this->objTable->pushClass($objClass);



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
            // CONTEXT set actual scope
            $this->objContext->setScope($this->actual_token->data);

            // read ':'
            $this->getAndSetActualToken();
            $this->printTokenData('ClassBody     :');

            // <Colon>
            if ($this->actual_token->state == StatesEnum::S_COLON){
                $this->getAndSetActualToken();
                $this->parseDeclarations();
            }

            $this->parseClassBody();
        }
        // SUV
        elseif ($this->parsePrefix(0)){

            $this->printTokenData('ClassBody   suv');

            $this->objContext->setPrefix($this->actual_token->data);

            $this->parseDeclarations();
            $this->parseClassBody();

        }
        elseif ($this->actual_token->data == $this->parseDataType()){

            $this->objContext->setDataType($this->actual_token->data);

            $this->parseDeclarations();
            $this->parseClassBody();
        }

        // REMOVED TODO maybe wrong?
        //else {
            //$this->getAndSetActualToken();
            //$this->printTokenData('ClassB.       }');
        //}

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

        // SPECIAL expression! using B::var;
        // TODO push using expression to CONTEXT
        if ($this->actual_token->data == 'using'){
            $this->printTokenData('Decls     using');

            $this->parseDeclaration();
            $this->parseDeclarations();
        }
        // get SUV(<Prefix>)
        else if ($this->parsePrefix(0)){
            $this->printTokenData('Decls       suv');
            /*
             * CONTEXT set prefix
             */
            $this->objContext->setPrefix($this->actual_token->data);

            // get <DataType>
            $this->getAndSetActualToken();
            $this->printTokenData('Decls        DT');
            /*
             * CONTEXT set dataType
             */
            $this->objContext->setDataType($this->actual_token->data);
            $this->objContext->setReturnType($this->actual_token->data);

            if ($this->actual_token->data == $this->parseDataType()) {
                // get id
                $this->getAndSetActualToken();
                $this->printTokenData('Decls        id');
                /*
                 * CONTEXT set id
                 */
                $this->objContext->setDeclarationId($this->actual_token->data);

                $this->parseDeclaration();
                $this->parseDeclarations();
                // CONTEXT
                $this->objContext->clearScope();
                $this->objContext->clearPrefix();
            }
        }
        // get empty <Prefix> so read DataType
        else if ($this->actual_token->data == $this->parseDataType()){
            $this->printTokenData('Decls        DT');
            /*
             * CONTEXT set dataType
             */
            $this->objContext->setDataType($this->actual_token->data);
            $this->objContext->setReturnType($this->actual_token->data);

            $this->getAndSetActualToken();
            $this->printTokenData('Decls        id');
            /*
             * CONTEXT set id
             */
            $this->objContext->setDeclarationId($this->actual_token->data);

            $this->parseDeclaration();
            $this->parseDeclarations();

            // CONTEXT clear it after end declaration
            $this->objContext->clearScope();
            $this->objContext->clearPrefix();
        }
    }

    public function parseDeclaration(){

        //echo "DECLARATION". PHP_EOL;
        if ($this->actual_token->state == StatesEnum::S_IDENTIFIER) {

            $this->objContext->setMethodDeclId($this->actual_token->data);

            $this->getAndSetActualToken();

            if ($this->actual_token->state == StatesEnum::S_SEMICOLON){
                /*
                 *  Definitely end of simple declaration at ex.- int variable;
                 */
                // TODO how get actual objClass ?

                // get last object in Table Array
                $last_obj = new LastClassObject($this->objTable);
                // create TABLE variable from CONTEXT values
                $obj_var  = new ClassVariable($this->objContext->getDeclarationId(),
                                              $this->objContext->getDataType(),
                                              $this->objContext->getScope(),
                                              $this->objContext->getPrefix()
                );

                //var_dump($last_obj);
                // push it to last object
                if ($last_obj->last_obj != null) {
                    array_push($last_obj->last_obj->variables, $obj_var);
                    echo "pushed variable\n";
                    //var_dump($last_obj);
                }
                //print_r((array)$last_obj);

                $this->printTokenData('Decl          ;');
                $this->getAndSetActualToken();
                $this->printTokenData('Decl   }/suv/DT');
            }
            // go inside pararameter list
            elseif ($this->actual_token->state == StatesEnum::S_LEFT_BRACKET) {
                $this->printTokenData('Decl          (');

                $this->getAndSetActualToken();
                $this->printTokenData('Decl         DT');

                // ITS FUNCTION START PARSE PARAMETERS
                $this->parseParameterList();

                // parameter list push from context to TABLE
                // get last object from Table Array
                $last_obj = new LastClassObject($this->objTable);
                // new method obj
                $newMethod = new ClassMethod();

                //$arr = array_merge($this->objContext->parameters, $newMethod->method_arguments);
                //var_dump($arr);
                // deep copy of this arr
                //$newArray = $this->array_copy($arr);

                // cant push one argument but all from $arr
                $newMethod->method_arguments = $this->objContext->parameters;
                $newMethod->setMethodName($this->objContext->getMethodDeclId());
                $newMethod->setMethodReturnType($this->objContext->getReturnType());

                array_push($last_obj->last_obj->methods, $newMethod);
                //var_dump($last_obj->last_obj);
                //array_push($last_obj->last_obj->methods, )

                /*
                 *
                 *  Clear CONTEXT after doing with parameters
                 *
                 */
                $this->objContext->declaration_id ='';
                $this->objContext->data_type ='';
                $this->objContext->is_void= '';
                $this->objContext->prefix = '';
                $this->objContext->method_return_type = '';
                //clear array of parameters
                unset($this->objContext->parameters); // break references
                $this->objContext->parameters = array(); // re-initialize to empty array

                $this->printTokenData('Decl          )');

                $this->getAndSetActualToken();

                $this->parseDeclarationBody();

                //  get }
                $this->getAndSetActualToken();
                $this->printTokenData('Decl           ');
            }
        }
        // using expression
        elseif ($this->actual_token->data == 'using'){
            $this->printTokenData('Decl      using');

            $this->getAndSetActualToken();
            $this->printTokenData('Decl         id');

            if ($this->actual_token->state == StatesEnum::S_IDENTIFIER){
                $this->getAndSetActualToken();
                $this->printTokenData('Decl          :');

                $this->getAndSetActualToken();
                $this->printTokenData('Decl          :');

                $this->getAndSetActualToken();
                $this->printTokenData('Decl         id');

                $this->getAndSetActualToken();
                $this->printTokenData('Decl          ;');

                //  get } or next declaration
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

                $this->getAndSetActualToken();

                if ($this->actual_token->state == StatesEnum::S_SEMICOLON) {
                    $this->printTokenData('DeclBody      ;');

                }
            }
        }
        // it can be standard decl body ' {} '
        elseif ($this->actual_token->state == StatesEnum::S_LEFT_VINCULUM){
            $this->printTokenData('DeclBody      {');

            $this->getAndSetActualToken();

            if ($this->actual_token->state == StatesEnum:: S_RIGHT_VINCULUM){
                $this->printTokenData('DeclBody      }');
            }
            // FIXME int f(void) {};
            //$this->getAndSetActualToken();

            //$this->printTokenData('DeclBody      ;');

        }
    }

    public function parseParameterList(){
        /*
         * note -  get ')' and jump from function
         */
        //get dataType
        if ($this->parseDataType()){

            // CONTEXT set dataType
            // FIXME write it to variables for definition variables maybe bad idea
            $this->objContext->setDataType($this->actual_token->data);
            //echo "set cont data type =". $this->objContext->getDataType() . PHP_EOL;

            // <DataType> is void
            if ($this->actual_token->data == 'void') {
                $this->printTokenData('ParList    void');

                // TODO TABLE finally its just void parameter
                $this->objContext->setVoid();

                $objParam = new ContextParameter();

                //if ($this->objContext->getIsVoid() == True) {
                $objParam->setVoidTrue();

                $this->objContext->pushParameter($objParam);

                // get ')'
                $this->getAndSetActualToken();
                return;
            }

            $this->getAndSetActualToken();

            // get id
            if ($this->actual_token->state == StatesEnum::S_IDENTIFIER){
                $this->printTokenData('ParList      id');

                // CONTEXT set id of var
                $this->objContext->setDeclarationId($this->actual_token->data);
                //echo "set cont id =". $this->objContext->getDeclarationId() . PHP_EOL;

                // CONTEXT - push first parameter to context array
                $objParam = new ContextParameter();
                $objParam->setDataType($this->objContext->getDataType());
                $objParam->setVarId($this->objContext->getDeclarationId());

                $this->objContext->pushParameter($objParam);
                //print_r($this->objContext->parameters);

                $this->getAndSetActualToken();

                if ($this->actual_token->state == StatesEnum::S_COMMA) {
                    $this->printTokenData('ParList       ,');
                    $this->parseParameterList2();
                }
                // its end of params cause ')'
                elseif ($this->actual_token->state == StatesEnum::S_RIGHT_BRACKET) {
                    //else it is only one parameter in func
                    $this->printTokenData('ParList       )');
                }
            }
            // CONTEXT end of param
        }
        // elseif TODO can be parameter <Prefix> (suv)??
        // parameter list is empty
        elseif ($this->actual_token->state == StatesEnum::S_RIGHT_BRACKET){
            $this->printTokenData('ParList      )');
            // FIXME & TESTME
            ///$this->getAndSetActualToken();
        }

    }

    public function ParseParameterList2(){

        if ($this->actual_token->state == StatesEnum::S_COMMA){
            $this->printTokenData('ParList2      ,');

            $this->getAndSetActualToken();

            if ($this->parseDataType() == $this->actual_token->data){

                // CONTEXT set dataType
                $this->objContext->setDataType($this->actual_token->data);

                $this->printTokenData('ParList2     DT');
                $this->getAndSetActualToken();

                if ($this->actual_token->state == StatesEnum::S_IDENTIFIER){

                    // CONTEXT set id of var
                    $this->objContext->setDeclarationId($this->actual_token->data);

                    $this->printTokenData('ParList2     id');

                    $this->getAndSetActualToken();
                    $this->printTokenData('ParList2    ,/)');

                    // CONTEXT - push first parameter to context array
                    $objParam = new ContextParameter();
                    $objParam->setDataType($this->objContext->getDataType());
                    $objParam->setVarId($this->objContext->getDeclarationId());

                    if ($this->objContext->getIsVoid() == True) {
                        $objParam->setVoidTrue();
                    }
                    $this->objContext->pushParameter($objParam);
//                    print_r($this->objContext->parameters);
                    // recursive call
                    $this->parseParameterList2();
                }
            }
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

    function array_copy($arr) {
        $newArray = array();
        foreach($arr as $key => $value) {
            if(is_array($value)) $newArray[$key] = $this->array_copy($value);
            else if(is_object($value)) $newArray[$key] = clone $value;
            else $newArray[$key] = $value;
        }
        return $newArray;
    }
    public function end(){
        //echo $this->objTable->classArray['B']->methods[0]->method_arguments[0]->var_id;
    }

}

$parser = new Parser();