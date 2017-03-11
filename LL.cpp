<Prog> -> <ClassList><Eof>  // class
<ClassList> -> eps
<ClassList> -> <Class><ClassList>
<Class> -> class id <InheritanceList> { <ClassBody> };
<Class> -> eps
<InheritanceList>  -> : <AccessModifier> id <InheritanceList2>      // : public B, public C, public D
<InheritanceList2> -> , <AccessModifier> id <InheritanceList2>       // , public B, public C, public D
<InheritanceList2> -> eps
<ClassBody> -> <AccessModifier> <Colon> <Declarations>
<ClassBody> -> <Declarations>
<Colon> -> :
<Colon> -> eps
<Declarations> -> <Declaration> <Declarations>
<Declarations> -> eps
<Declaration> -> <Prefix> <DataType> id <Declaration2> ;
<Declaration2> -> ( <ParameterList> ) <DeclarationBody>
<Declaration2> -> ;
<ParameterList> ->  <DataType> id <ParameterList2>                     // int a
<ParameterList> ->  eps
<ParameterList2> -> , <DataType> id <ParameterList2>                   // , int a
<ParameterList2> -> eps
<DeclarationBody> -> {}
<DeclarationBody> -> = 0;
<AccessModifier> -> eps
<AccessModifier> -> public
<AccessModifier> -> protected
<AccessModifier> -> private
<Prefix>  -> static
<Prefix>  -> using
<Prefix>  -> virtual
<Prefix>  -> eps
<DataType> -> signed char
<DataType> -> signed int
<DataType> -> signed short int
<DataType> -> signed long int
<DataType> -> signed long long int
<DataType> -> unsigned char
<DataType> -> unsigned short int
<DataType> -> unsigned int
<DataType> -> unsigned long int
<DataType> -> unsigned long long int
<DataType> -> float
<DataType> -> double
<DataType> -> long double
<DataType> -> bool
<DataType> -> void
<DataType> -> char
<DataType> -> char16_t
<DataType> -> char32_t
<DataType> -> wchar_t

democviko slajdy - pravidla pre parametre

HELP:
AccessModifier = private, public....


// Inheritance
class A: public B, public C, public D {}
// options of sign inheritance
: public A
: A, -> inheritanceList2()

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