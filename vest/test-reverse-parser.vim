scriptencoding utf-8

let s:save_cpo = &cpo
scriptencoding utf-8
set cpo&vim

Context Parser
    It tests reverse parser
        ShouldEqual phpcomplete_extended#parser#reverseParse(
                    \ "$this->"
                    \ ,[]),
                    \ [{'insideBraceText': '', 'methodPropertyText': '$this', 'start': 1}, {'isMethod': 0, 'insideBraceText': '', 'methodPropertyText': '', 'start': 0}]

        ShouldEqual phpcomplete_extended#parser#reverseParse(
                    \ "$var->"
                    \ ,[]),
                    \ [{'insideBraceText': '', 'methodPropertyText': '$var', 'start': 1}, {'isMethod': 0, 'insideBraceText': '', 'methodPropertyText': '', 'start': 0}]

        ShouldEqual phpcomplete_extended#parser#reverseParse(
                    \ "$var"
                    \ ,[]),
                    \ [{'insideBraceText': '', 'methodPropertyText': '$var', 'start': 1}]

        ShouldEqual phpcomplete_extended#parser#reverseParse(
                    \ "method_name"
                    \ ,[]),
                    \ [{'insideBraceText': '', 'methodPropertyText': 'method_name', 'nonClass': 1, 'start': 1}]

        ShouldEqual phpcomplete_extended#parser#reverseParse(
                    \ "new Test"
                    \ ,[]),
                    \ [{'insideBraceText': '', 'isNew': 1, 'methodPropertyText': 'Test', 'start': 1}]

        ShouldEqual phpcomplete_extended#parser#reverseParse(
                    \ "$this->get"
                    \ ,[]),
                    \ [{'insideBraceText': '', 'methodPropertyText': '$this', 'start': 1}, {'isMethod': 0, 'insideBraceText': '', 'methodPropertyText': 'get', 'start': 0}]

        ShouldEqual phpcomplete_extended#parser#reverseParse(
                    \ "$this->get('insidequote"
                    \ ,[]),
                    \ [{'insideBraceText': '', 'methodPropertyText': '$this', 'start': 1}, {'isMethod': 0, 'insideBraceText': '''insidequote', 'insideQuote': 1, 'methodPropertyText': 'get', 'start': 0}]

        ShouldEqual phpcomplete_extended#parser#reverseParse(
                    \ "Class('insidequote"
                    \ ,[]),
                    \ [{'insideBraceText': '''insidequote', 'insideQuote': 1, 'methodPropertyText': 'Class', 'nonClass': 1, 'start': 1}]

        ShouldEqual phpcomplete_extended#parser#reverseParse(
                    \ "Class('insidequote')"
                    \ ,[]),
                    \ [{'insideBraceText': '''insidequote''', 'methodPropertyText': 'Class', 'nonClass': 1, 'start': 1}]

        ShouldEqual phpcomplete_extended#parser#reverseParse(
                    \ "Class('dotted.service.text')"
                    \ ,[]),
                    \ [{'insideBraceText': substitute('''dotted\\.service\\.text''', '\\\\', '\\', 'g'), 'methodPropertyText': 'Class', 'nonClass': 1, 'start': 1}]

        ShouldEqual phpcomplete_extended#parser#reverseParse(
                    \ "$this->method('insidequote')->property"
                    \ ,[]),
                    \ [{'insideBraceText': '', 'methodPropertyText': '$this', 'start': 1}, {'isMethod': 1, 'insideBraceText': '''insidequote''', 'methodPropertyText': 'method', 'start': 0}, {'isMethod': 0, 'insideBraceText': '', 'methodPropertyText': 'property', 'start': 0}]
        "

        ShouldEqual phpcomplete_extended#parser#reverseParse(
                    \ "$this->method('insidequote')->property->method1()"
                    \ ,[]),
                    \ [{'insideBraceText': '', 'methodPropertyText': '$this', 'start': 1}, {'isMethod': 1, 'insideBraceText': '''insidequote''', 'methodPropertyText': 'method', 'start': 0}, {'isMethod': 0, 'insideBraceText': '', 'methodPropertyText': 'property', 'start': 0}, {'isMethod': 1, 'insideBraceText': '', 'methodPropertyText': 'method1', 'start': 0}]


        ShouldEqual phpcomplete_extended#parser#reverseParse(
                    \ "$this->property->method()"
                    \ ,[]),
                    \ [{'insideBraceText': '', 'methodPropertyText': '$this', 'start': 1}, {'isMethod': 0, 'insideBraceText': '', 'methodPropertyText': 'property', 'start': 0}, {'isMethod': 1, 'insideBraceText': '', 'methodPropertyText': 'method', 'start': 0}]

        ShouldEqual phpcomplete_extended#parser#reverseParse(
                    \ 'Class("escaped string\"")'
                    \, []),
                    \ [{'insideBraceText': '"escapedstring\""', 'methodPropertyText': 'Class', 'nonClass': 1, 'start': 1}]

        ShouldEqual phpcomplete_extended#parser#reverseParse(
                    \ '$this->get(''escaped string\'''
                    \ ,[]),
                    \ [{'insideBraceText': '', 'methodPropertyText': '$this', 'start': 1}, {'isMethod': 0, 'insideBraceText': '''escapedstring\''', 'insideQuote': 1, 'methodPropertyText': 'get', 'start': 0}]

        ShouldEqual phpcomplete_extended#parser#reverseParse(
                    \ "Class($skippedTokens, 'in quote')"
                    \, []),
                    \ [{'insideBraceText': '''inquote''', 'methodPropertyText': 'Class', 'nonClass': 1, 'start': 1}]

        ShouldEqual phpcomplete_extended#parser#reverseParse(
                    \ '$this->get($skippedTokens, ''in quote'')'
                    \ ,[]),
                    \ [{'insideBraceText': '', 'methodPropertyText': '$this', 'start': 1}, {'isMethod': 1, 'insideBraceText': '''inquote''', 'methodPropertyText': 'get', 'start': 0}]

        ShouldEqual phpcomplete_extended#parser#reverseParse(
                    \ 'new \Namespace\Expansion("inside quote")'
                    \, []),
                    \ [{'insideBraceText': '"insidequote"', 'isNew': 1, 'methodPropertyText': 'Namespace\Expansion', 'start': 1}]

        ShouldEqual phpcomplete_extended#parser#reverseParse(
                    \ '$this->get($inside->brace("inside quote")'
                    \, []),
                    \ [{'insideBraceText': '', 'methodPropertyText': '$inside', 'start': 1}, {'isMethod': 1, 'insideBraceText': '"insidequote"', 'methodPropertyText': 'brace', 'start': 0}]

        ShouldEqual phpcomplete_extended#parser#reverseParse(
                    \ '$this->get(normal_function("quote'
                    \, []),
                    \ [{'insideBraceText': '"quote', 'insideQuote': 1, 'methodPropertyText': 'normal_function', 'nonClass': 1, 'start': 1}]

        ShouldEqual phpcomplete_extended#parser#reverseParse(
                    \ '$this->get("string_not_alnum:'
                    \,[]),
                    \ [{'insideBraceText': '', 'methodPropertyText': '$this', 'start': 1}, {'isMethod': 0, 'insideBraceText': '"string_not_alnum:', 'insideQuote': 1, 'methodPropertyText': 'get', 'start': 0}]
    End

End

Fin

let &cpo = s:save_cpo
unlet s:save_cpo

" vim: foldmethod=marker:expandtab:ts=4:sts=4:tw=78
