scriptencoding utf-8

let s:save_cpo = &cpo
scriptencoding utf-8
set cpo&vim

Context forward_parser
    It tests reverse parser
        ShouldEqual phpcomplete_extended#parser#forwardParse(
            \ '$var;'
            \ ,[]),
            \ [{'insideBraceText': '', 'isMethod': 0, 'methodPropertyText': '$var', 'pEnd': 1, 'start': 0}]

        ShouldEqual phpcomplete_extended#parser#forwardParse(
                    \ "$this->get('fdsa')->get2();"
                    \ ,[]),
                    \ [{'insideBraceText': '', 'isMethod': 0, 'methodPropertyText': '$this', 'start': 1},
                    \  {'insideBraceText': "'fdsa'", 'isMethod': 1, 'methodPropertyText': 'get', 'start': 0},
                    \  {'insideBraceText': '', 'isMethod': 1, 'methodPropertyText': 'get2', 'pEnd': 1, 'start': 0}]

        ShouldEqual phpcomplete_extended#parser#forwardParse(
                    \ 'Foo::Bar()->Baz();'
                    \ ,[]),
                    \ [{'insideBraceText': '', 'isMethod': 0, 'isStatic': 1, 'methodPropertyText': 'Foo', 'nonClass': 1, 'start': 1},
                    \  {'insideBraceText': '', 'isMethod': 1, 'methodPropertyText': '', 'start': 0},
                    \  {'insideBraceText': '', 'isMethod': 1, 'methodPropertyText': 'Baz', 'pEnd': 1, 'start': 0}]

        ShouldEqual phpcomplete_extended#parser#forwardParse(
                    \ '(new Foo)->bar();'
                    \ ,[]),
                    \ [{'insideBraceText': '', 'isMethod': 0, 'isNew': 1, 'methodPropertyText': 'Foo', 'start': 0},
                    \  {'insideBraceText': '', 'isMethod': 1, 'methodPropertyText': 'bar', 'pEnd': 1, 'start': 0}]

        ShouldEqual phpcomplete_extended#parser#forwardParse(
                    \ '(new Foo())->bar();'
                    \ ,[]),
                    \ [{'insideBraceText': '', 'isMethod': 1, 'isNew': 1, 'methodPropertyText': 'Foo', 'start': 0},
                    \  {'insideBraceText': '', 'isMethod': 1, 'methodPropertyText': 'bar', 'pEnd': 1, 'start': 0}]

        ShouldEqual phpcomplete_extended#parser#forwardParse(
                    \ "$foo['bar']->get();"
                    \ ,[]),
                    \ [{'insideBraceText': "'bar'", 'isArrayElement': 1, 'isMethod': 0, 'methodPropertyText': '$foo', 'start': 1},
                    \  {'insideBraceText': '', 'isMethod': 1, 'methodPropertyText': 'get', 'pEnd': 1, 'start': 0}]

        ShouldEqual phpcomplete_extended#parser#forwardParse(
                    \ "$foo->bar()['baz'];"
                    \ ,[]),
                    \ [{'insideBraceText': '', 'isMethod': 0, 'methodPropertyText': '$foo', 'start': 1},
                    \  {'insideBraceText': '', 'isArrayElement': 1, 'isMethod': 1, 'methodPropertyText': 'bar', 'start': 0},
                    \  {'insideBraceText': "'baz'", 'isArrayElement': 1, 'isMethod': 0, 'methodPropertyText': '', 'pEnd': 1, 'start': 0}]

        ShouldEqual phpcomplete_extended#parser#forwardParse(
                    \ '$foo->bar->baz();'
                    \ ,[]),
                    \ [{'insideBraceText': '', 'isMethod': 0, 'methodPropertyText': '$foo', 'start': 1},
                    \  {'insideBraceText': '', 'isMethod': 0, 'methodPropertyText': 'bar', 'start': 0},
                    \  {'insideBraceText': '', 'isMethod': 1, 'methodPropertyText': 'baz', 'pEnd': 1, 'start': 0}]

        ShouldEqual phpcomplete_extended#parser#forwardParse(
                    \ "$foo->bar()['baz']->bzz;"
                    \ ,[]),
                    \  [{'insideBraceText': '', 'isMethod': 0, 'methodPropertyText': '$foo', 'start': 1},
                    \   {'insideBraceText': '', 'isArrayElement': 1, 'isMethod': 1, 'methodPropertyText': 'bar', 'start': 0},
                    \   {'insideBraceText': "'baz'", 'isArrayElement': 1, 'isMethod': 1, 'methodPropertyText': '', 'start': 0},
                    \   {'insideBraceText': '', 'isMethod': 0, 'methodPropertyText': 'bzz', 'pEnd': 1, 'start': 0}]

        ShouldEqual phpcomplete_extended#parser#forwardParse(
                    \ '$this->foo($bar->baz());'
                    \ ,[]),
                    \ [{'insideBraceText': '', 'isMethod': 0, 'methodPropertyText': '$this', 'start': 1},
                    \ {'insideBraceText': '$bar->baz', 'isMethod': 1, 'methodPropertyText': 'foo', 'pEnd': 1, 'start': 0}]

     End

 End

 Fin

let &cpo = s:save_cpo
unlet s:save_cpo

" vim: foldmethod=marker:expandtab:ts=4:sts=4:tw=78
