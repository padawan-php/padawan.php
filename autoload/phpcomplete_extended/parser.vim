let s:save_cpo = &cpo
set cpo&vim

let s:lexer_symbols = [
    \ ['open_brace'        , '(']             , ['close_brace'       , ')']      ,
    \ ['curly_brace_open'  , '{']             , ['curly_brace_close' , '}']      ,
    \ ['square_brace_open' , '[']             , ['square_brace_open' , ']']      ,
    \ ['escaped_single_quote'      , '\(\\''\)\+']             , ['escaped_double_quote'      , '\(\\"\)\+']      ,
    \ ['single_quote'      , "'"]             , ['double_quote'      , '"']      ,
    \ ["object_resolutor"  , "->"]            , ['static_resolutor'  , "::"]     ,
    \ ["self"              , "self"]          , ['parent'            , "parent"] ,
    \ ['colon'             , ':']             , ['question'          , '?']      ,
    \ ['new'               , 'new']           , ['tab'               , '\t']     ,
    \ ['right_arrow'       , '<']             , ['left_arrow'        , '>']      ,
    \ ['function'          , 'function']      , ['use'               , 'use'],
    \ ['ampersand'         , '&']             , ['front_slash'       , '/']      ,
    \ ['ns_seperator'      , "\\"]            , ['underscore'        , '_']      ,
    \ ['equal'             , '=']             , ['negate'            , '!']      ,
    \ ['dollar'            , '\$']            , ['dash'              , '-']      ,
    \ ['semicolon'         , ';']             , ['comma'             , ','] ,
    \ ['plus'              , '+']             , ['star', '*'], ['dot', '\.'],
    \ ['tild'              , '^']             ,
    \ ['alnum'             , '[[:alnum:]]\+']        , ['whitespace'        , '\s\+']   ,
    \ ['xothers'            , '[^[:alnum:]]\+']
    \]

function! phpcomplete_extended#parser#reverseParse(line, parsedTokens) "{{{
    let line = a:line
    if line == ""
        return [{'insideBraceText': '', 'methodPropertyText': '', 'nonClass': 1, 'start': 1}]
    endif
    let parsedTokens = a:parsedTokens
    let braceStack = []
    let quoteStack = []
    let L = phpcomplete_extended#util#getLexer()
    let P = phpcomplete_extended#util#getParser()
    let List = phpcomplete_extended#util#getDataList()
    let lexerTokens = reverse(L.lexer(s:lexer_symbols).exec(line))
    let parser = P.parser().exec(lexerTokens)
    let next = parser.next()
    let insideBraceText = ""
    let expectResolutor = 1
    let methodPropertyText  = ""
    let isMethod = 0
    let isStatic = 0
    let insideQuote = 0
    let startSkipping = 0
    let wordAfterSpace = ""
    let wordAfterBrace = ""
    while !parser.end()
        "PrettyPrint parser.next()
        "PrettyPrint braceStack
        "PrettyPrint parsedTokens
        "call parser.consume()
        "continue
        if startSkipping && !parser.next_is('open_brace')
            call parser.consume()
            continue
        end
        if parser.next_is("whitespace")
            let whitespace =  parser.consume()['matched_text']
            if parser.next_is('new') && wordAfterSpace != ""
                let methodPropertyText = wordAfterSpace . methodPropertyText
            else
                let insideBraceText = wordAfterSpace . insideBraceText
            endif

            if insideQuote && parser.next_is('open_brace')
                if !empty(quoteStack)
                    call List.pop(quoteStack)
                endif
            endif
        elseif parser.next_is('close_brace')

            let close_brace = parser.consume()['matched_text']
            if empty(braceStack)
                call List.push(braceStack, ")")
                let expectResolutor = 0
            else
                let insideBraceText = close_brace . insideBraceText
            endif
            continue
        elseif parser.next_is("open_brace")
            if startSkipping
                let startSkipping = 0
            endif
            let open_brace = parser.consume()['matched_text']
            if !empty(braceStack)
                call List.pop(braceStack)
                let expectResolutor = 1
                let isMethod = 1
            elseif len(insideBraceText) &&  insideBraceText[0] !~ '''\|"'
                let insideBraceText = open_brace . insideBraceText
            endif
        elseif parser.next_is("alnum")
            let charNumbers = parser.consume()
            if parser.end() || isStatic || (empty(braceStack) && parser.next_is('open_brace'))
                let parsedObject = {}
                let parsedObject.start = 1
                let parsedObject.methodPropertyText = charNumbers['matched_text'] . methodPropertyText
                let parsedObject.insideBraceText = insideBraceText

                let parsedObject.nonClass = 1
                if insideQuote
                    let parsedObject.insideQuote = 1
                    "let parsedObject.isMethod = 1
                    let insideQuote = 0
                endif

                if expectResolutor && len(parsedTokens)
                    let parsedObject.nonClass = 0
                    let expectResolutor = 0
                endif

                let expectResolutor = 0
                let insideBraceText = ""
                call insert(parsedTokens, parsedObject)
                break
            elseif empty(braceStack) && parser.next_is('whitespace')
                let wordAfterSpace = charNumbers['matched_text']


            elseif empty(braceStack) && (parser.next()['matched_text'] == "$" || parser.next_is("object_resolutor") || parser.next_is("static_resolutor") || parser.next_is("underscore") || parser.next_is("ns_seperator")) 
                let methodPropertyText = charNumbers['matched_text'] .methodPropertyText
            else 
                let insideBraceText = charNumbers['matched_text'] .insideBraceText
            endif


        elseif parser.next_is('single_quote') || parser.next_is('double_quote')
            let quote = parser.consume()['matched_text']
            if empty(quoteStack) 
                call List.push(quoteStack, quote)
                let insideBraceText = quote . insideBraceText
                let insideQuote = 1
                let insideBraceText  .= methodPropertyText
                let methodPropertyText = ""
            else
                if empty(quoteStack)
                    continue
                endif
                let qstack = copy(quoteStack)
                let q = List.pop(qstack)
                if q == quote 
                    call List.pop(quoteStack)
                    let insideQuote = 0
                    let insideBraceText = quote . insideBraceText
                endif
            endif
        elseif parser.next_is('escaped_single_quote') || parser.next_is('escaped_double_quote')
            let escaped_quote = parser.consume()['matched_text']
            let insideBraceText = escaped_quote . insideBraceText

        elseif parser.next_is('comma')
            let comma = parser.consume()['matched_text']
            if len(insideBraceText) && insideBraceText[0] =~ '''\|"'
                let startSkipping = 1
            endif

        elseif parser.next_is("dollar")
            let dollar = parser.consume()['matched_text']
            if empty(braceStack)
                let parsedObject = {}
                let parsedObject.start = 1
                let parsedObject.methodPropertyText = "$" .methodPropertyText
                let parsedObject.insideBraceText = insideBraceText
                let expectResolutor = 0
                let insideBraceText = ""
                call insert(parsedTokens, parsedObject)
                break
            else
                let insideBraceText = dollar .insideBraceText
            endif

        elseif parser.next_is("object_resolutor") || parser.next_is("static_resolutor")
            let resolutor = parser.consume()['matched_text']
            if resolutor == "::"
                let isStatic = 1
            endif
            if expectResolutor  && empty(braceStack)
                let parsedObject = {}
                let parsedObject.start = 0
                let parsedObject.methodPropertyText = methodPropertyText
                let parsedObject.insideBraceText = insideBraceText
                let parsedObject.isMethod = 0
                if insideQuote
                    let parsedObject.insideQuote = 1
                    "let parsedObject.isMethod = 1
                    let insideQuote = 0
                endif
                if isMethod
                    let parsedObject.isMethod = 1
                    let isMethod = 0
                endif
                call insert(parsedTokens, parsedObject)
                let insideBraceText = ""
                let methodPropertyText = ""
            else
                if !empty(braceStack)
                    let insideBraceText = resolutor .insideBraceText
                else
                    echoerr "resol"
                    return []
                endif
            endif
        elseif parser.next_is("parent")
            let parent = parser.consume()['matched_text']
            if empty(braceStack)
                let parsedObject = {}
                let parsedObject.start = 1
                let parsedObject.methodPropertyText = "parent"
                let parsedObject.insideBraceText = insideBraceText
                let expectResolutor = 0
                let insideBraceText = ""
                call insert(parsedTokens, parsedObject)
                break
            else
                let insideBraceText = parent .insideBraceText
            endif

        elseif parser.next_is("new")
            let new = parser.consume()['matched_text']
            if empty(braceStack)
                let parsedObject = {}
                let parsedObject.start = 1
                let parsedObject.isNew = 1
                let parsedObject.methodPropertyText = methodPropertyText
                let parsedObject.insideBraceText = insideBraceText
                let expectResolutor = 0
                let insideBraceText = insideBraceText
                call insert(parsedTokens, parsedObject)
                break
            else
                let insideBraceText = new .insideBraceText
            endif
        elseif parser.next_is("underscore")
            let underscore = parser.consume()['matched_text']
            if empty(braceStack)
                let methodPropertyText = underscore . methodPropertyText
            else
                let insideBraceText = underscore . insideBraceText
            endif
        elseif parser.next_is("ns_seperator")
            let ns_seperator = parser.consume()['matched_text']
            if empty(braceStack)
                let methodPropertyText = ns_seperator . methodPropertyText
            else
                let insideBraceText = ns_seperator . insideBraceText
            endif
            if parser.end()
                let parsedObject = {}
                let parsedObject.start = 1
                let parsedObject.methodPropertyText = methodPropertyText
                let parsedObject.insideBraceText = insideBraceText

                let expectResolutor = 0
                let insideBraceText = ""
                call insert(parsedTokens, parsedObject)
                break
            endif

        elseif parser.next_is("dot")
            let dot = parser.consume()['matched_text']
            let insideBraceText = '\.' . insideBraceText
        else 
            let extraData = parser.consume()['matched_text']
            if !empty(braceStack)
                let insideBraceText = extraData . insideBraceText
            else
                let methodPropertyText = extraData . methodPropertyText
            endif
        endif
    endwhile

    for token in parsedTokens
        if token['methodPropertyText'][0] == "\\"
            let token['methodPropertyText'] = matchstr(token['methodPropertyText'], '\\\zs.*')
        endif
    endfor

    return parsedTokens
endfunction "}}}


function! phpcomplete_extended#parser#forwardParse(line, parsedTokens) "{{{
    let line = a:line
    let parsedTokens = a:parsedTokens
    let braceStack = []
    let L = phpcomplete_extended#util#getLexer()
    let P = phpcomplete_extended#util#getParser()
    let List = phpcomplete_extended#util#getDataList()

    let lexerTokens = L.lexer(s:lexer_symbols).exec(line)
    let parser = P.parser().exec(lexerTokens)
    let next = parser.next()
    let insideBraceText = ""
    let expectResolutor = 1
    let methodPropertyText  = ""
    let isMethod = 0
    let isDollar = 0
    let isNew = 0
    "if !parser.next_is('dollar')
        "return []
    "endif

    while !parser.end()
        "PrettyPrint parser.next()
        "PrettyPrint braceStack
        "PrettyPrint parsedTokens
        if parser.next_is("whitespace")
            call parser.consume()
        elseif parser.next_is('dollar')
            let dollar = parser.consume()['matched_text']
            if empty(braceStack)
                let expectResolutor = 1
                let isDollar = 1
            else
                let insideBraceText .= dollar
            endif

        elseif parser.next_is('new')
            let new = parser.consume()['matched_text']
            if empty(braceStack)
                let expectResolutor = 1
                let isNew = 1
            else
                let insideBraceText .= new
            endif
        elseif parser.next_is('parent')
            let parent = parser.consume()['matched_text']
            if empty(braceStack)
                let expectResolutor = 1
                let methodPropertyText = parent
            else
                let insideBraceText .= parent
            endif
        elseif parser.next_is('ns_seperator')
            let ns_seperator = parser.consume()['matched_text']
            if empty(braceStack)
                let expectResolutor = 1
                let methodPropertyText .= ns_seperator
            else
                let insideBraceText .= ns_seperator
            endif
        elseif parser.next_is('open_brace')
            let open_brace = parser.consume()['matched_text']
            if empty(braceStack)
                call List.push(braceStack, open_brace)
            else
                let insideBraceText .= open_brace
            endif
        elseif parser.next_is('close_brace')
            let close_brace = parser.consume()['matched_text']
            if !empty(braceStack)
                call List.pop(braceStack)
                let expectResolutor = 1
                let isMethod = 1
            else
                let insideBraceText .= close_brace
            endif
        elseif parser.next_is("alnum")
            let alnum = parser.consume()['matched_text']
            if parser.end()
                let parsedObject = {}
                let parsedObject.methodPropertyText = alnum
                let parsedObject.insideBraceText = insideBraceText
                let parsedObject.pEnd = 1
                let parsedObject.isMethod = isMethod
                let parsedObject.nonClass = 1
                if isNew
                    let parsedObject.isNew = 1
                    let isNew = 0
                endif

                let expectResolutor = 0
                let insideBraceText = ""
                let methodPropertyText = ""
                call add(parsedTokens, parsedObject)
                break
            elseif empty(braceStack)
                let methodPropertyText .= alnum
            else
                let insideBraceText .= alnum
            endif
        elseif parser.next_is("object_resolutor") || parser.next_is("static_resolutor")
            let resolutor = parser.consume()['matched_text']
            if empty(braceStack)
                let parsedObject = {}
                if isDollar
                    let isDollar = 0
                    let parsedObject.start = 1
                    let parsedObject.methodPropertyText = "$". methodPropertyText
                else 
                    let parsedObject.methodPropertyText = methodPropertyText
                endif

                let parsedObject.isMethod = 0
                if isMethod
                    let parsedObject.isMethod = 1
                    let isMethod = 0
                endif

                if isNew
                    let parsedObject.isNew = 1
                    let isNew = 0
                endif
                let parsedObject.insideBraceText = insideBraceText
                let parsedObject.pEnd = 0
                let expectResolutor = 0
                let insideBraceText = ""
                let methodPropertyText = ""
                call add(parsedTokens, parsedObject)
            else 
                if !empty(braceStack)
                    let insideBraceText .= resolutor
                else
                    echoerr "resolutor"
                    return []
                endif
            endif
        elseif parser.next_is("semicolon")
            let semicolon = parser.consume()['matched_text']
            if empty(braceStack)
                let parsedObject = {}
                let parsedObject.methodPropertyText = methodPropertyText
                let parsedObject.insideBraceText = insideBraceText
                let parsedObject.pEnd = 1
                if isNew
                    let parsedObject.isNew = 1
                    let isNew = 0
                endif
                let parsedObject.isMethod = isMethod
                let expectResolutor = 0
                let insideBraceText = ""
                let methodPropertyText = ""
                call add(parsedTokens, parsedObject)
                break
            else 
                echoerr "semicolon"
                return []
            endif
        else 
            let extraData = parser.consume()['matched_text']
            if !empty(braceStack)
                let insideBraceText .= extraData
            endif
        endif
    endwhile

    for token in parsedTokens
        if token['methodPropertyText'][0] == "\\"
            let token['methodPropertyText'] = matchstr(token['methodPropertyText'], '\\\zs.*')
        endif
    endfor

    return parsedTokens
endfunction "}}}

function! ParserTest() "{{{
    "let line = "$this->get($this->set("
    "let tokens = phpcomplete_extended#parser#reverseParse(line, [])
    "PrettyPrint tokens
endfunction "}}}

let &cpo = s:save_cpo
unlet s:save_cpo
