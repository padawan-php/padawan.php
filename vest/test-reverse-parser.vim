scriptencoding utf-8

let s:save_cpo = &cpo
scriptencoding utf-8
set cpo&vim

Context reverse_parser
    It tests reverse parser
        ShouldEqual phpcomplete_extended#parser#reverseParse(
                    \ "$this->"
                    \ ,[]),
                    \ [{'insideBraceText': '', 'isMethod': 0, 'methodPropertyText': '$this', 'start': 1}, {'insideBraceText': '', 'isMethod': 0, 'methodPropertyText': '', 'start': 0}]

        ShouldEqual phpcomplete_extended#parser#reverseParse(
                    \ "$var->"
                    \ ,[]),
                    \ [{'insideBraceText': '', 'isMethod': 0, 'methodPropertyText': '$var', 'start': 1}, {'insideBraceText': '', 'isMethod': 0, 'methodPropertyText': '', 'start': 0}]
        ShouldEqual phpcomplete_extended#parser#reverseParse(
                    \ "$foo = $var->"
                    \ ,[]),
                    \ [{'insideBraceText': '', 'isMethod': 0, 'methodPropertyText': '$var', 'start': 1}, {'insideBraceText': '', 'isMethod': 0, 'methodPropertyText': '', 'start': 0}]


        ShouldEqual phpcomplete_extended#parser#reverseParse(
                    \ "$var"
                    \ ,[]),
                    \ [{'insideBraceText': '', 'isMethod': 0, 'methodPropertyText': '$var', 'start': 1}]

        ShouldEqual phpcomplete_extended#parser#reverseParse(
                    \ "method_name"
                    \ ,[]),
                    \ [{'insideBraceText': '', 'isMethod': 0, 'methodPropertyText': 'method_name', 'nonClass': 1, 'start': 1}]

        ShouldEqual phpcomplete_extended#parser#reverseParse(
                    \ "new Test"
                    \ ,[]),
                    \ [{'insideBraceText': '', 'isMethod': 0, 'isNew': 1, 'methodPropertyText': 'Test', 'start': 1}]

        ShouldEqual phpcomplete_extended#parser#reverseParse(
                    \ "$this->get"
                    \ ,[]),
                    \ [{'insideBraceText': '', 'isMethod': 0, 'methodPropertyText': '$this', 'start': 1}, {'insideBraceText': '', 'isMethod': 0, 'methodPropertyText': 'get', 'start': 0}]

        ShouldEqual phpcomplete_extended#parser#reverseParse(
                    \ "$this->get('insidequote"
                    \ ,[]),
                    \ [{'insideBraceText': '', 'isMethod': 0, 'methodPropertyText': '$this', 'start': 1}, {'insideBraceText': "'insidequote", 'insideQuote': 1, 'isMethod': 0, 'methodPropertyText': 'get', 'start': 0}]

        ShouldEqual phpcomplete_extended#parser#reverseParse(
                    \ "Class('insidequote"
                    \ ,[]),
                    \ [{'insideBraceText': "'insidequote", 'insideQuote': 1, 'isMethod': 0, 'methodPropertyText': 'Class', 'nonClass': 1, 'start': 1}]

        ShouldEqual phpcomplete_extended#parser#reverseParse(
                    \ "Class('insidequote')"
                    \ ,[]),
                    \ [{'insideBraceText': "'insidequote'", 'isMethod': 0, 'methodPropertyText': 'Class', 'nonClass': 1, 'start': 1}]

        ShouldEqual phpcomplete_extended#parser#reverseParse(
                    \ "Class('dotted.service.text')"
                    \ ,[]),
                    \ [{'isMethod': 0, 'insideBraceText': '''dotted\.service\.text''', 'methodPropertyText': 'Class', 'nonClass': 1, 'start': 1}]

        ShouldEqual phpcomplete_extended#parser#reverseParse(
                    \ "$this->method('insidequote')->property"
                    \ ,[]),
                    \ [{'isMethod': 0, 'insideBraceText': '', 'methodPropertyText': '$this', 'start': 1}, {'isMethod': 1, 'insideBraceText': '''insidequote''', 'methodPropertyText': 'method', 'start': 0}, {'isMethod': 0, 'insideBraceText': '', 'methodPropertyText': 'property', 'start': 0}]

        ShouldEqual phpcomplete_extended#parser#reverseParse(
                    \ "$this->method('insidequote')->property->method1()"
                    \ ,[]),
                    \ [{'isMethod': 0, 'insideBraceText': '', 'methodPropertyText': '$this', 'start': 1}, {'isMethod': 1, 'insideBraceText': '''insidequote''', 'methodPropertyText': 'method', 'start': 0}, {'isMethod': 0, 'insideBraceText': '', 'methodPropertyText': 'property', 'start': 0}, {'isMethod': 1, 'insideBraceText': '', 'methodPropertyText': 'method1', 'start': 0}]


        ShouldEqual phpcomplete_extended#parser#reverseParse(
                    \ "$this->property->method()"
                    \ ,[]),
                    \ [{'isMethod': 0, 'insideBraceText': '', 'methodPropertyText': '$this', 'start': 1}, {'isMethod': 0, 'insideBraceText': '', 'methodPropertyText': 'property', 'start': 0}, {'isMethod': 1, 'insideBraceText': '', 'methodPropertyText': 'method', 'start': 0}]

        ShouldEqual phpcomplete_extended#parser#reverseParse(
                    \ 'Class("escaped string\"")'
                    \, []),
                    \ [{'isMethod': 0, 'insideBraceText': '"escapedstring\"', 'methodPropertyText': 'Class', 'nonClass': 1, 'start': 1}]

        "ShouldEqual phpcomplete_extended#parser#reverseParse(
                    "\ '$this->get(''escaped string\'''
                    "\ ,[]),
                    "\ [{'isMethod': 0, 'insideBraceText': '', 'methodPropertyText': '$this', 'start': 1}, {'isMethod': 0, 'insideBraceText': '''escapedstring\', 'insideQuote': 1, 'methodPropertyText': 'get', 'start': 0}]

        "ShouldEqual phpcomplete_extended#parser#reverseParse(
                    "\ "Class($skippedTokens, 'in quote')"
                    "\, []),
                    "\ [{'insideBraceText': "'inquote'", 'isMethod': 0, 'methodPropertyText': 'Class', 'nonClass': 1, 'start': 1}]

        "ShouldEqual phpcomplete_extended#parser#reverseParse(
                    "\ '$this->get($skippedTokens, ''in quote'')'
                    "\ ,[]),
                    "\ [{'insideBraceText': '', 'isMethod': 0, 'methodPropertyText': '$this', 'start': 1}, {'insideBraceText': "'inquote'", 'isMethod': 1, 'methodPropertyText': 'get', 'start': 0}]

        ShouldEqual phpcomplete_extended#parser#reverseParse(
                    \ 'new \Namespace\Expansion("inside quote")'
                    \, []),
                    \ [{'isMethod': 0, 'insideBraceText': '"insidequote"', 'isNew': 1, 'methodPropertyText': 'Namespace\Expansion', 'start': 1}]

        ShouldEqual phpcomplete_extended#parser#reverseParse(
                    \ '$this->get($inside->brace("inside quote")'
                    \, []),
                    \ [{'isMethod': 0, 'insideBraceText': '', 'methodPropertyText': '$inside', 'start': 1}, {'isMethod': 1, 'insideBraceText': '"insidequote"', 'methodPropertyText': 'brace', 'start': 0}]

        ShouldEqual phpcomplete_extended#parser#reverseParse(
                    \ '$this->get(normal_function("quote'
                    \, []),
                    \ [{'isMethod': 0, 'insideBraceText': '"quote', 'insideQuote': 1, 'methodPropertyText': 'normal_function', 'nonClass': 1, 'start': 1}]

        ShouldEqual phpcomplete_extended#parser#reverseParse(
                    \ '$this->get("string_not_alnum:'
                    \,[]),
                    \ [{'isMethod': 0, 'insideBraceText': '', 'methodPropertyText': '$this', 'start': 1}, {'isMethod': 0, 'insideBraceText': '"string_not_alnum:', 'insideQuote': 1, 'methodPropertyText': 'get', 'start': 0}]

        ShouldEqual phpcomplete_extended#parser#reverseParse(
                    \ "$foo['bar']"
                    \,[]),
                    \ [{'insideBraceText': '', 'isMethod': 0, 'methodPropertyText': '$foo', 'start': 1}, {'insideBraceText': "'bar'", 'isArrayElement': 1, 'isMethod': 0, 'methodPropertyText': '', 'start': 0}]

        ShouldEqual phpcomplete_extended#parser#reverseParse(
                    \ "$foo['bar"
                    \,[]),
                    \ [{'insideBraceText': '', 'isMethod': 0, 'methodPropertyText': '$foo', 'start': 1}, {'insideBraceText': "'bar", 'insideQuote': 1, 'isArrayElement': 1, 'isMethod': 0, 'methodPropertyText': '', 'start': 0}]

        ShouldEqual phpcomplete_extended#parser#reverseParse(
                    \ "$foo['bar']->"
                    \,[]),
                    \ [{'insideBraceText': '', 'isMethod': 0, 'methodPropertyText': '$foo', 'start': 1}, {'insideBraceText': "'bar'", 'isArrayElement': 1, 'isMethod': 0, 'methodPropertyText': '', 'start': 0}, {'insideBraceText': '', 'isMethod': 0, 'methodPropertyText': '', 'start': 0}]

        ShouldEqual phpcomplete_extended#parser#reverseParse(
                    \ '$this->foo()["bar"]->'
                    \,[]),
                    \ [{'insideBraceText': '', 'isMethod': 0, 'methodPropertyText': '$this', 'start': 1}, {'insideBraceText': '', 'isMethod': 1, 'methodPropertyText': 'foo', 'start': 0}, {'insideBraceText': '"bar"', 'isArrayElement': 1, 'isMethod': 0, 'methodPropertyText': '', 'start': 0}, {'insideBraceText': '', 'isMethod': 0, 'methodPropertyText': '', 'start': 0}]

        ShouldEqual phpcomplete_extended#parser#reverseParse(
                    \ '$foo->bar["baz"]->bzz'
                    \,[]),
                    \ [{'insideBraceText': '', 'isMethod': 0, 'methodPropertyText': '$foo', 'start': 1}, {'insideBraceText': '', 'isMethod': 0, 'methodPropertyText': 'bar', 'start': 0}, {'insideBraceText': '"baz"', 'isArrayElement': 1, 'isMethod': 0, 'methodPropertyText': '', 'start': 0}, {'insideBraceText': '', 'isMethod': 0, 'methodPropertyText': 'bzz', 'start': 0}]

        ShouldEqual phpcomplete_extended#parser#reverseParse(
                    \ "$this->foo['bar"
                    \,[]),
                    \ [{'insideBraceText': '', 'isMethod': 0, 'methodPropertyText': '$this', 'start': 1}, {'insideBraceText': '', 'isMethod': 0, 'methodPropertyText': 'foo', 'start': 0}, {'insideBraceText': "'bar", 'insideQuote': 1, 'isArrayElement': 1, 'isMethod': 0, 'methodPropertyText': '', 'start': 0}]


        "ShouldEqual phpcomplete_extended#parser#reverseParse(
                    "\ '(new Foo)->bar()'
                    "\,[]),
                    "\ [{'isMethod': 0, 'insideBraceText': '', 'isNew': 1, 'methodPropertyText': 'Foo', 'start': 1}, {'isMethod': 1, 'insideBraceText': '', 'methodPropertyText': 'bar', 'start': 0}]

        "ShouldEqual phpcomplete_extended#parser#reverseParse(
                    "\ "(new Foo('bar'))->baz('bzzz')"
                    "\,[]),
                    "\ [{'isMethod': 0, 'insideBraceText': '''bar''', 'isNew': 1, 'methodPropertyText': 'Foo', 'start': 1}, {'isMethod': 1, 'insideBraceText': '''bzzz''', 'methodPropertyText': 'baz', 'start': 0}]

        ShouldEqual phpcomplete_extended#parser#reverseParse(
                    \ '$this->foo($bar->baz())->'
                    \,[]),
                    \ [{'insideBraceText': '', 'isMethod': 0, 'methodPropertyText': '$this', 'start': 1},
                    \ {'insideBraceText': '$bar->baz', 'isMethod': 1, 'methodPropertyText': 'foo', 'start': 0},
                    \ {'insideBraceText': '', 'isMethod': 0, 'methodPropertyText': '', 'start': 0}]

        ShouldEqual phpcomplete_extended#parser#reverseParse(
                    \ '$this->foo($bar->baz("bzz"))->'
                    \,[]),
                    \ [{'insideBraceText': '', 'isMethod': 0, 'methodPropertyText': '$this', 'start': 1},
                    \ {'insideBraceText': '$bar->baz"bzz"', 'isMethod': 1, 'methodPropertyText': 'foo', 'start': 0},
                    \ {'insideBraceText': '', 'isMethod': 0, 'methodPropertyText': '', 'start': 0}]

        ShouldEqual phpcomplete_extended#parser#reverseParse(
                    \  '$this->foo($bar->baz[])->'
                    \,[]),
                    \ [{'insideBraceText': '', 'isMethod': 0, 'methodPropertyText': '$this', 'start': 1},
                    \ {'insideBraceText': '$bar->baz', 'isMethod': 1, 'methodPropertyText': 'foo', 'start': 0},
                    \ {'insideBraceText': '', 'isMethod': 0, 'methodPropertyText': '', 'start': 0}]

        ShouldEqual phpcomplete_extended#parser#reverseParse(
                    \ '$this->foo($bar->baz["dfdfa"])->'
                    \,[]),
                    \ [{'insideBraceText': '', 'isMethod': 0, 'methodPropertyText': '$this', 'start': 1},
                    \ {'insideBraceText': '$bar->baz"dfdfa"', 'isMethod': 1, 'methodPropertyText': 'foo', 'start': 0},
                    \ {'insideBraceText': '', 'isMethod': 0, 'methodPropertyText': '', 'start': 0}]


        ShouldEqual phpcomplete_extended#parser#reverseParse(
                    \ 'return new Foo'
                    \,[]),
                    \ [{'insideBraceText': '', 'isMethod': 0, 'isNew': 1, 'methodPropertyText': 'Foo', 'start': 1}]

        ShouldEqual phpcomplete_extended#parser#reverseParse(
                    \ "$this->foo('session')->bar()->baz($bzz, $zzzz)"
                    \,[]),
                    \ [{'insideBraceText': '', 'isMethod': 0, 'methodPropertyText': '$this', 'start': 1},
                    \ {'insideBraceText': "'session'", 'isMethod': 1, 'methodPropertyText': 'foo', 'start': 0},
                    \ {'insideBraceText': '', 'isMethod': 1, 'methodPropertyText': 'bar', 'start': 0},
                    \ {'insideBraceText': '$bzz,$zzzz', 'isMethod': 1, 'methodPropertyText': 'baz', 'start': 0}]

    End

End

Fin

let &cpo = s:save_cpo
unlet s:save_cpo

" vim: foldmethod=marker:expandtab:ts=4:sts=4:tw=78
