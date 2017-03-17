<?php

require_once("Scanner.php");
require_once("ClassTable.php");
require_once("Context.php");
require_once("InheritanceParser.php");
require_once("PrintXml.php");

define('DEBUG_SCANNER', false);
define('DEBUG', false);

class Parser
{
    // just for print
    public $name_func = "";

    function __construct() {
        $this->input_dir = "";
        $this->out_dir = "";

        $this->parseArgs();
        // create instance of scanenr
        $this->scanner      = new Scanner($this->input_dir);
        $this->actual_token = new Token();
        $this->objContext   = new Context();
        $this->objTable     = new ClassTable();


        // get first token ge
        $this->getAndSetActualToken();
        // start parse LL grammar
        $this->startProg();

        if ($this->actual_token->state == StatesEnum::S_EOF) {
            //echo "\n-- SUCCESSFULLY PARSED --  " . PHP_EOL;
        }
        else
            //echo "\n-- PARSE ERROR !!!--  " . PHP_EOL;

        //print_r($this->objContext);
        // invented print_r() and var_dump() in 2017
        //print_r((array) $this->objTable);

        $this->inheritanceTable = new InheritanceParser($this->objTable);
        $this->printThat        = new PrintXml($this->inheritanceTable, $this->out_dir);

        
    }
    /*
     *   <Prog> -> <ClassList><Eof>
     */
    public function startProg(){

        if($this->actual_token->data == 'class'){
            //echo "         EXPECT | COME\n";
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


            ////echo "parseCLass\n";
            //print_r($this->objContext->inheritance_declarations);
            $this->parseClass();
            /*
             *
             *  TABLE set abstract
             */
            $objClass->is_abstract = $this->objContext->is_abstract;
            $this->objContext->is_abstract = False;

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

                /*
                 * TABLE  read all inheritances so push
                 *
                 */
                // MAYBE push here list of inheritances
                $last_obj = new LastClassObject($this->objTable);
                // array_clone = deep copy
                $copy_array =  $this->array_clone($this->objContext->inheritance_declarations);
                $last_obj->last_obj->inheritance_from = $copy_array;
                //echo "push inheritance \n";
                //print_r($last_obj->last_obj->inheritance_from);
                //print_r($this->objTable);

                /*
                 *  CONTEXT clear
                 */
                unset($this->objContext->inheritance_declarations); // break references
                $this->objContext->inheritance_declarations = array(); // re-initialize to empty array


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
            /*
             *  CONTEXT set actual scope
             */
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

            /*
             *  CONTEXT set actual scope
             */
            if ($this->objContext->getScope() == ''){
                //default
                $this->objContext->setScope('private');
                //echo "set to private \n";
            }

            $this->objContext->setPrefix($this->actual_token->data);

            $this->parseDeclarations();
            $this->parseClassBody();

        }
        elseif ($this->actual_token->data == $this->parseDataType()){

            /*
             *  CONTEXT set actual scope
             */
            if ($this->objContext->getScope() == ''){
                //default
                $this->objContext->setScope('private');
                //echo "set to private \n";
            }

            $this->objContext->setDataType($this->actual_token->data);

            $this->parseDeclarations();
            $this->parseClassBody();
        }


    }

    public function parseInheritanceList(){

        // LONG sign without <AccessModifier>
        // if <AccessModifier>
        // TODO change this nasty if to beautiful if
        if ($this->actual_token->data == 'public' or $this->actual_token->data == 'private'
            or $this->actual_token->data == 'protected'){

            /*
             *  CONTEXT push PPP
             */
            $objItem = new ClassInheritanceItem();
            $objItem->setScope($this->actual_token->data);

            $this->getAndSetActualToken();
            $this->printTokenData("InhList      id");

            if ($this->actual_token->state == StatesEnum::S_IDENTIFIER){

                /*
                 * find child of class object
                 */

                //echo "Actual class = ". $this->objContext->class_name . PHP_EOL;
                //echo "Actual inher par = ". $this->actual_token->data. PHP_EOL;

                $find_key = $this->actual_token->data;
                $set_child = $this->objContext->class_name;
                // find parent object and push here his child
                if ($find_key != null) {
                    $this->findParentObj($find_key, $set_child);
                }
                else {
                    //echo "NENASIEL SA PARENT !!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!";
                }
                /*
                 *  CONTEXT push id
                 */
                $objItem->setName($this->actual_token->data);
                // push one inherit item
                array_push($this->objContext->inheritance_declarations, $objItem);

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
            /*
             *  CONTEXT push id
             */
            $objItem = new ClassInheritanceItem();
            $objItem->setName($this->actual_token->data);
            $objItem->setScope('private');                // DEFAULT!!!
            // push one inherit item
            array_push($this->objContext->inheritance_declarations, $objItem);

            /*
             * find child of class object
             */

            //echo "AKKKTTT class = ". $this->objContext->class_name . PHP_EOL;
            //echo "AKKKTTT inher par = ". $this->actual_token->data. PHP_EOL;

            $find_key = $this->actual_token->data;
            $set_child = $this->objContext->class_name;
            // find parent object and push here his child
            $this->findParentObj($find_key, $set_child);

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

            /*
             *  CONTEXT push PPP
             */
            $objItem = new ClassInheritanceItem();

            // if <AccessModifier>
            // TODO change this nasty if to beautiful if
            if ($this->actual_token->data == 'public' or $this->actual_token->data == 'private'
                or $this->actual_token->data == 'protected'
            ) {
                $objItem->setScope($this->actual_token->data);

                $this->getAndSetActualToken();
                $this->printTokenData("InhList2     id");

                /*
                 *  CONTEXT get name
                 */
                $objItem->setName($this->actual_token->data);
                // push one inherit item
                array_push($this->objContext->inheritance_declarations, $objItem);

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
                /*
                 *  CONTEXT get name
                 */
                $objItem->setName($this->actual_token->data);
                $objItem->setScope('private');

                // push one inherit item
                array_push($this->objContext->inheritance_declarations, $objItem);

                /*
                 * find child of class object
                 */
                // TODO same thing copy to parseInheritanceList2()
                //echo "AKKKTTT class = ". $this->objContext->class_name . PHP_EOL;
                //echo "AKKKTTT inher par = ". $this->actual_token->data. PHP_EOL;
                $find_key = $this->actual_token->data;
                $set_child = $this->objContext->class_name;
                // find parent object and push here his child
                $this->findParentObj($find_key, $set_child);

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

        ////echo "DECLARATION". PHP_EOL;
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
                /*$obj_var  = new ClassVariable($this->objContext->getDeclarationId(),
                                              $this->objContext->getDataType(),
                                              $this->objContext->getScope(),
                                              $this->objContext->getPrefix()*/
                $obj_var  = new ClassVariable();
                $obj_var->setVarName($this->objContext->getDeclarationId());
                $obj_var->setPrefix($this->objContext->getPrefix());
                $obj_var->setScope($this->objContext->getScope());
                $obj_var->setVarDataType($this->objContext->getDataType());

                // DFAULT
                if ($this->objContext->getScope() == ''){
                    $obj_var->setScope('private');
                }

                //var_dump($last_obj);
                // push it to last object
                if ($last_obj->last_obj != null) {
                    array_push($last_obj->last_obj->variables, $obj_var);
                    //echo "pushed variable\n";
                    //var_dump($last_obj);
                }

                 /*
                 * CONTEXT clear
                 *
                 */
                //unset($this->objContext->inheritance_declarations); // break references
                //$this->objContext->inheritance_declarations = array(); // re-initialize to empty array

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

                // cant push one argument but all from $arr
                $newMethod->method_arguments = $this->objContext->parameters;
                $newMethod->setMethodName($this->objContext->getMethodDeclId());
                $newMethod->setMethodReturnType($this->objContext->getReturnType());
                $newMethod->setMethodScope($this->objContext->getScope());
                $newMethod->setPrefix($this->objContext->getPrefix());

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

                //unset($this->objContext->inheritance_declarations); // break references
                //$this->objContext->inheritance_declarations = array(); // re-initialize to empty array

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

                $this->objContext->is_abstract = True;

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
            ////echo "set cont data type =". $this->objContext->getDataType() . PHP_EOL;

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
                ////echo "set cont id =". $this->objContext->getDeclarationId() . PHP_EOL;

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
               ////echo "-------------------------AccessMod=TRUE". PHP_EOL;
               return true;

           default :
               ////echo "-------------------------AccessMod=FALSE". PHP_EOL;
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
                    ////echo "-------------------------DataType=".$string. PHP_EOL;
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
                ////echo "-------------------------Prefix-". $this->actual_token->data ."=TRUE". PHP_EOL;
                return ($return_value ? $string : true);

            default :
                ////echo "-------------------------Prefix-". $this->actual_token->data ."=FALSE". PHP_EOL;
                return false;
        }
    }

    public function printTokenData($name_func){
        //echo  $name_func ." | ". $this->scanner->token->data .PHP_EOL;
    }

    public function getAndSetActualToken(){
        $this->actual_token = $this->scanner->getNextToken();
        return $this->actual_token;
    }

    static public function array_clone($array) {
        return array_map(function($element) {
            return ((is_array($element))
                ? call_user_func(__FUNCTION__, $element)
                : ((is_object($element))
                    ? clone $element
                    : $element
                )
            );
        }, $array);
    }
    public function findParentObj($find_key, $set_child){
        //foreach ($this->objTable->classArray[$find_key] as $key => $obj){
            $newItem = new ClassInheritanceChild();
            $newItem->child_name = $set_child;

            array_push($this->objTable->classArray[$find_key]->inheritance_child, $newItem);
            // TODO maybe child_scope
        //}
    }

    public function parseArgs(){
        // Script example.php
        $shortopts  = "";

        $longopts  = array(
              "help",
              "output:",
              "pretty-xml::",
              "input:",
              "details::"
        );
        $options = getopt($shortopts, $longopts);
        //var_dump($options);
        foreach( $options as $key => $value ){
            switch ($key){
                case 'input':
                    $this->input_dir = $value;
                    break;

                case 'output':
                    $this->out_dir = $value;
                    //echo "tuuu: ". $value;
                    break;
            }
        }
    }
}

$parser = new Parser();