let s:save_cpo = &cpo
set cpo&vim

let s:lexer_symbols = [
    \ ['new'               , '\<new\>'] ,
    \ ['identifier'        , '\h\w*'] , ['whitespace'         , '\s*'] ,
    \ ['open_brace'        , '(']     , ['close_brace'        , ')']   ,
    \ ['dollar'            , '\$']    ,
    \ ['single_quote'      , "'"]     , ['double_quote'       , '"']   ,
    \ ["object_resolutor"  , "->"]    , ['static_resolutor'   , "::"]  ,
    \ ['curly_brace_open'  , '{']     , ['curly_brace_close'  , '}']   ,
    \ ['square_brace_open' , '[']     , ['square_brace_close' , ']']   ,
    \ ['ns_seperator'      , "\\"]    , ['equal'              , '=']   ,
    \ ['front_slash'       , "/"]     , ['star'               , '*']   ,
    \ ['alpha'             , "@"]     , ['plus'               , '+']   ,
    \ ['OR'                , "|"]     , ['AND'                , '&']   ,
    \ ['negate'            , "!"]     , ['dash'               , '-']   ,
    \ ['semicolon'         , ';']     , ['colon'              , ':']   ,
    \ ['dot'               , '\.']    ,
    \ ['left_arrow'        , '>']     , ['right_arrow'        , '<']   ,
    \['comma'             , ','],
    \['others'            , '[^()\[\]""'',;:*/@|&+!-]\+'] ,
\]

function! phpcomplete_extended#parser#reverseParse(line, parsedTokens) "{{{
    let line = phpcomplete_extended#util#trim(a:line)
    if line =~ ';$'
        let line = phpcomplete_extended#util#getDataString().chop(line)
    endif
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
    try
    catch
        return []
    endtry
    let next = parser.next()
    let insideBraceText = ""
    let expectResolutor = 1
    let methodPropertyText  = ""
    let isMethod = 0
    let isArray = 0
    let isStatic = 0
    let maybeNew = 0
    let insideQuote = 0
    let startSkipping = 0
    let previousToken = ""
    while !parser.end()
        "PrettyPrint parser.next()
        "PrettyPrint braceStack
        "PrettyPrint parsedTokens
        "call parser.consume()
        "continue
        if parser.next_is('whitespace')
            call parser.consume()
            if empty(braceStack) && previousToken == "identifier" && !parser.next_is('new')
                let parsedObject = s:createParsedObject(insideBraceText, methodPropertyText, isMethod, 1)
                let parsedObject.nonClass = 1
                call insert(parsedTokens, parsedObject)
                break
            endif
            continue
        elseif startSkipping && !parser.next_is('open_brace')
            call parser.consume()
            continue
        elseif parser.next_is('identifier')
            let identifier = parser.consume()['matched_text']
            if empty(braceStack) && isArray 
                    \ && (parser.next_is('object_resolutor') || parser.next_is('static_resolutor') || parser.next_is('dollar'))
                    \ && previousToken == 'square_brace_open'
                "'$foo->bar["baz"]->bzz'
                let parsedObject = s:createParsedObject(insideBraceText, methodPropertyText, 0, 0)
                let parsedObject.isArrayElement = 1
                let insideBraceText = ""
                let methodPropertyText = identifier
                if insideQuote
                    let parsedObject.insideQuote = 1
                    let insideQuote = 0
                endif
                call insert(parsedTokens, parsedObject)
                let isArray = 0
            elseif (empty(insideBraceText) && empty(methodPropertyText) && parser.end()) 
                        \ || (parser.end() && previousToken == "open_brace")
                        \ || (parser.end() && previousToken == "static_resolutor")
                        \ || parser.next_is('open_brace')
                        \ || parser.next_is('square_brace_open')
                "foo_bar, foo(, Foo::, $this->get(foo_func('bar, $foo[Foo::Bar
                let methodPropertyText = identifier . methodPropertyText
                let start = 1
                let isMethod = 0
                let parsedObject = s:createParsedObject(insideBraceText, methodPropertyText, isMethod, start)
                let methodPropertyText = ""
                let insideBraceText = ""
                if insideQuote
                    let parsedObject.insideQuote = 1
                    let insideQuote = 0
                endif
                if previousToken == "static_resolutor"
                    let parsedObject.isStatic = 1
                    let parsedObject.nonClass = 0
                else
                    let parsedObject.nonClass = 1
                endif
                call insert(parsedTokens, parsedObject)
                break

            elseif empty(braceStack) && (
                \ parser.next_is('dollar')
                \ || parser.next_is('whitespace')
                \ || parser.next_is("object_resolutor")
                \ || parser.next_is("static_resolutor")
                \ || parser.next_is("ns_seperator")
                \)
                let methodPropertyText = identifier .methodPropertyText

            else
                if(isArray) 
                    let isArray  = 0
                endif
                let insideBraceText = identifier .insideBraceText
            endif
            let previousToken = "identifier"

        elseif parser.next_is('open_brace') || parser.next_is("square_brace_open")
            let open_brace = parser.consume()['matched_text']
            if !empty(braceStack) && open_brace == "(" && braceStack[-1] == ")"
                if startSkipping 
                    let startSkipping = 0
                endif
                call List.pop(braceStack)
                let isMethod = 1
            elseif !empty(braceStack) && open_brace == "[" && braceStack[-1] == "]"
                call List.pop(braceStack)
                let isArray = 1
            elseif (previousToken == "single_quote" || previousToken == "double_quote")
                    \ && parser.next_is("identifier") && open_brace == "["
                if !empty(quoteStack)
                    call List.pop(quoteStack)
                endif
                let isArray = 1
            elseif len(insideBraceText) &&  insideBraceText[0] !~ '''\|"'
                let insideBraceText = open_brace . insideBraceText
            endif
            let previousToken = "open_brace"
            if open_brace == "["
                let previousToken = "square_brace_open"
            endif
        elseif parser.next_is('close_brace') || parser.next_is("square_brace_close")
            let close_brace = parser.consume()['matched_text']
            "if empty(braceStack)
                    "\ && (previousToken == "static_resolutor" || previousToken == "object_resolutor")
                    "\ && (parser.next_is('close_brace'))

                ""(new Foo())->bar
                "let maybeNew = 1
                "continue
            if (empty(quoteStack)  && (
                        \ (close_brace == ')' && parser.next_is('open_brace'))
                        \ || (close_brace == ']' && parser.next_is('square_brace_open'))
                        \ || (previousToken == 'object_resolutor' || previousToken == 'static_resolutor')
                        \ || (parser.next_is('single_quote') || parser.next_is('double_quote'))
                        \ || (parser.next_is('identifier'))
                    \))
                call List.push(braceStack, close_brace)
                if (close_brace == ")" && isArray && previousToken == "square_brace_open")
                    "'$this->foo()[]->'
                    let parsedObject = s:createParsedObject(insideBraceText, '', 0, 0)
                    let parsedObject.isArrayElement = 1
                    let isArray = 0
                    let insideBraceText = ""
                    let methodPropertyText = ""
                    call insert(parsedTokens, parsedObject)
                endif
            else
                let insideBraceText = close_brace . insideBraceText
            endif
            let previousToken = "close_brace"
            if previousToken == "]"
                let previousToken = "square_brace_close"
            endif

        elseif parser.next_is('single_quote') || parser.next_is('double_quote')
            let quote = parser.consume()['matched_text']
            if empty(quoteStack) && !parser.next_is('ns_seperator')
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
                if q == quote && !parser.next_is('ns_seperator')
                    call List.pop(quoteStack)
                    let insideQuote = 0
                    let insideBraceText = quote . insideBraceText
                endif
            endif
            let previousToken = "single_quote"
            if quote == '"'
                let previousToken = "double_quote"
            endif

        elseif parser.next_is('static_resolutor') || parser.next_is('object_resolutor')
            let resolutor = parser.consume()['matched_text']
            if empty(braceStack) && (previousToken == "identifier" || empty(previousToken))
                    \ && (parser.next_is('identifier') 
                            \ ||  parser.next_is('close_brace') 
                            \ || parser.next_is('square_brace_close'))

                let parsedObject = s:createParsedObject(insideBraceText, methodPropertyText, isMethod, 0)
                let isMethod = 0
                if insideQuote
                    let parsedObject.insideQuote = 1
                    let insideQuote = 0
                endif
                if resolutor == "::"
                    let parsedObject.isStatic = 1
                    let isStatic = 0
                endif
                let methodPropertyText = ""
                let insideBraceText = ""
                call insert(parsedTokens, parsedObject)
            else
                let insideBraceText = resolutor .insideBraceText
            endif
            let previousToken = "object_resolutor"
            if resolutor == "::"
                let previousToken = "static_resolutor"
            endif

        elseif parser.next_is('new')
            let new = parser.consume()['matched_text']
            if empty(braceStack) 
                        \ &&  (parser.next_is('whitespace') || parser.end() || parser.next_is('open_brace'))
                let parsedObject = s:createParsedObject(insideBraceText, methodPropertyText, 0, 1)
                let parsedObject.isNew = 1
                call insert(parsedTokens, parsedObject)
                break
            else
                let insideBraceText = new .insideBraceText
            endif
            let previousToken = "new"
        elseif parser.next_is('dollar')
            let dollar = parser.consume()['matched_text']
            if empty(braceStack)
                let methodPropertyText = dollar . methodPropertyText
                let parsedObject = s:createParsedObject(insideBraceText, methodPropertyText, 0, 1)
                let methodPropertyText = ""
                let insideBraceText = ""
                if isArray
                    let parsedObject.isArrayElement = 1
                endif
                call insert(parsedTokens, parsedObject)
                break
            else
                let insideBraceText = dollar . insideBraceText
            endif

            let previousToken = "dollar"

        elseif parser.next_is('ns_seperator')
            let ns_seperator = parser.consume()['matched_text']
            if empty(braceStack)
                let methodPropertyText = ns_seperator . methodPropertyText
            else
                let insideBraceText = ns_seperator . insideBraceText
            endif
            let previousToken = "ns_seperator"
        elseif parser.end()
            let parsedObject = s:createparsedObject(insideBraceText, methodPropertyText, 0, 1)
            call insert(parsedTokens, parsedObject)
            break
        elseif parser.next_is("dot")
            let dot = parser.consume()['matched_text']
            let insideBraceText = '\.' . insideBraceText
        elseif parser.next_is('comma')
            let comma = parser.consume()['matched_text']
            "start skipping previous method arguments
            if len(insideBraceText) && (previousToken == 'single_quote' || parser.next_is('double_quote'))
                "TODO: have to refine skiping mechanism
                let startSkipping = 0
                let insideBraceText = comma . insideBraceText
            else
                let insideBraceText = comma . insideBraceText
            endif

        else
            let extraData = parser.consume()['matched_text']
            if !empty(braceStack)
                let insideBraceText = extraData . insideBraceText
            else
                let methodPropertyText = extraData . methodPropertyText
            endif
            let previousToken = "extradata"
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

    try
        let lexerTokens = L.lexer(s:lexer_symbols).exec(line)
        let parser = P.parser().exec(lexerTokens)
    catch 
        return []
    endtry
    let next = parser.next()
    let insideBraceText = ""
    let expectResolutor = 1
    let methodPropertyText  = ""
    let isMethod = 0
    let isDollar = 0
    let isNew = 0
    let previousToken = ""
    let isArray = 0

    while !parser.end()
        "PrettyPrint parser.next()
        "PrettyPrint braceStack
        "PrettyPrint parsedTokens
        if parser.next_is('whitespace')
            call parser.consume()
            let previousToken = "whitespace"
            continue
        elseif parser.next_is('dollar')
            let dollar = parser.consume()['matched_text']
            if empty(braceStack)
                let methodPropertyText .= dollar
            else
                let insideBraceText .= dollar
            endif
            let previousToken = "dollar"
        elseif parser.next_is('identifier')
            let identifier = parser.consume()['matched_text']

            if !empty(braceStack)
                let insideBraceText .= identifier

            elseif empty(previousToken) && parser.next_is('static_resolutor')
                let parsedObject = s:createParsedObject('', identifier, 0, 1)
                let parsedObject.isStatic = 1
                let parsedObject.nonClass = 1
                call add(parsedTokens, parsedObject)
                let methodPropertyText = ""
                let insideBraceText = ""
            elseif previousToken == "dollar" 
                    \ && (parser.next_is('object_resolutor') 
                        \ || parser.next_is('static_resolutor') 
                        \ || parser.next_is('square_brace_open') 
                        \ || parser.next_is('semicolon'))
                let methodPropertyText .= identifier
            elseif previousToken == "object_resolutor" && (parser.next_is('open_brace') 
                            \ || parser.next_is('square_brace_open') 
                            \ || parser.next_is('semicolon') 
                            \ || parser.next_is('object_resolutor'))
                let methodPropertyText .= identifier

            elseif previousToken == "whitespace"
                    \ && isNew && (parser.next_is('open_brace') || parser.next_is('close_brace'))
                let methodPropertyText = identifier

            endif
            let previousToken = "identifier"
        elseif parser.next_is("object_resolutor") || parser.next_is('static_resolutor')
            let resolutor = parser.consume()['matched_text']
            if empty(braceStack)
                \ && (previousToken == 'identifier' || previousToken == 'close_brace' || previousToken == 'square_brace_close')
                if resolutor == "::"
                    continue
                endif
                let parsedObject = s:createParsedObject(insideBraceText, methodPropertyText, isMethod, 0)
                if methodPropertyText[0] == "$"
                    let parsedObject.start = 1
                endif
                if isNew 
                    let parsedObject.isNew = 1
                    let isNew = 0
                endif
                if isArray
                    let parsedObject.isArrayElement = 1
                    let isArray = 0
                endif
                call add(parsedTokens, parsedObject)
                let insideBraceText = ""
                let methodPropertyText =""
            elseif !empty(braceStack)
                let insideBraceText .= resolutor
            endif
            let previousToken = "object_resolutor"
            if resolutor == "static_resolutor"
                let previousToken = "static_resolutor"
            endif
        elseif parser.next_is('new')
            let new_operator = parser.consume()['matched_text']
            if empty(braceStack) 
                    \ && (empty(previousToken) || previousToken == 'open_brace')
                let isNew = 1
            elseif !empty(braceStack)
                let insideBraceText .= new_operator
            endif
            let previousToken = "new"
        elseif parser.next_is('open_brace') || parser.next_is('square_brace_open')
            let open_brace = parser.consume()['matched_text']
            if (empty(previousToken)) && parser.next_is('new')
                continue

            elseif (previousToken == "close_brace" && open_brace == "[") 
                    \ || (previousToken == 'identifier')
                call List.push(braceStack, open_brace)
                if open_brace == "["
                    let isArray  = 1
                endif
            else
                let insideBraceText .= open_brace
            endif
            let previousToken = "open_brace"
            if open_brace == "["
                let previousToken = "square_brace_open"
            endif

        elseif parser.next_is('close_brace') || parser.next_is('square_brace_close')
            let close_brace = parser.consume()['matched_text']
            if empty(braceStack) && isNew && close_brace == ")" 
                    \ && (previousToken == "identifier" || previousToken == "close_brace")
                continue
            elseif !empty(braceStack)
                if (close_brace == ")" && braceStack[-1] == "(")
                        \ || (close_brace == "]" && braceStack[-1] == "[")
                    call List.pop(braceStack)
                    if close_brace == ')'
                        let isMethod = 1
                    endif
                    if (close_brace == ")" && parser.next_is('square_brace_open'))
                        let parsedObject = s:createParsedObject(insideBraceText, methodPropertyText, 0, 0)
                        let parsedObject.isArrayElement = 1
                        let parsedObject.isMethod = 1
                        call add(parsedTokens, parsedObject)
                        let methodPropertyText = ""
                        let insideBraceText = ""
                    endif
                endif
            else
                let insideBraceText .= close_brace
            endif
            let previousToken = "close_brace"
            if close_brace == "]"
                let previousToken = "square_brace_close"
            endif
        elseif parser.next_is('semicolon')
            let semicolon = parser.consume()['matched_text']
            if empty(braceStack) 
                    \&& (previousToken == "close_brace" 
                        \ || previousToken == "square_brace_close"
                        \ || previousToken == "identifier"
                        \ || empty(previousToken) && methodPropertyText[0] == "$")
                let parsedObject = s:createParsedObject(insideBraceText, methodPropertyText, 1, 0)
                let parsedObject.pEnd = 1
                if previousToken == "square_brace_close"
                    let parsedObject.isMethod = 0
                    let parsedObject.isArrayElement = 1
                elseif previousToken == "identifier"
                    let parsedObject.isMethod = 0
                endif
                call add(parsedTokens, parsedObject)
                break
            else
                let insideBraceText .= semicolon
            endif
            let previousToken = "semicolon"
        else
            let extraData = parser.consume()['matched_text']
            if !empty(braceStack)
                let insideBraceText .= extraData
            endif
            let previousToken = "extra_data"
        endif
    endwhile

    for token in parsedTokens
        if token['methodPropertyText'][0] == "\\"
            let token['methodPropertyText'] = matchstr(token['methodPropertyText'], '\\\zs.*')
        endif
    endfor

    return parsedTokens
endfunction "}}}

function! ParserTest(line) "{{{
    let line = a:line
    PrettyPrint line
    let tokens = phpcomplete_extended#parser#reverseParse(line, [])
    PrettyPrint tokens
    return tokens
endfunction "}}}

function! s:createParsedObject(insideBraceText, methodPropertyText, isMethod, start)
    let parsedObject = {}
    let parsedObject.insideBraceText = a:insideBraceText
    let parsedObject.methodPropertyText = a:methodPropertyText
    let parsedObject.isMethod = a:isMethod
    let parsedObject.start = a:start
    return parsedObject
endfunction


let &cpo = s:save_cpo
unlet s:save_cpo
