"=============================================================================
" AUTHOR:  Mun Mun Das <m2mdas at gmail.com>
" FILE: phpcomplete_extended.vim
" Last Modified: September 11, 2013
" License: MIT license  {{{
"     Permission is hereby granted, free of charge, to any person obtaining
"     a copy of this software and associated documentation files (the
"     "Software"), to deal in the Software without restriction, including
"     without limitation the rights to use, copy, modify, merge, publish,
"     distribute, sublicense, and/or sell copies of the Software, and to
"     permit persons to whom the Software is furnished to do so, subject to
"     the following conditions:
"
"     The above copyright notice and this permission notice shall be included
"     in all copies or substantial portions of the Software.
"
"     THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS
"     OR IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF
"     MERCHANTABILITY, FITNESS FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT.
"     IN NO EVENT SHALL THE AUTHORS OR COPYRIGHT HOLDERS BE LIABLE FOR ANY
"     CLAIM, DAMAGES OR OTHER LIABILITY, WHETHER IN AN ACTION OF CONTRACT,
"     TORT OR OTHERWISE, ARISING FROM, OUT OF OR IN CONNECTION WITH THE
"     SOFTWARE OR THE USE OR OTHER DEALINGS IN THE SOFTWARE.
" }}}
"=============================================================================
let s:save_cpo = &cpo
set cpo&vim

if !exists("s:current_project_dir")
    let s:current_project_dir = ""
endif
if !exists("s:update_info")
    let s:update_info = {}
endif
if !exists("s:plugins")
    let s:plugins = {}
endif
if !exists("s:plugin_ftime")
    let s:plugin_ftime = -1
endif
if !exists("s:plugin_index")
    let s:plugin_index = {}
endif
if !exists("s:plugin_php_files")
    let s:plugin_php_files = []
endif

if !exists("s:psr_class_complete")
    let s:psr_class_complete = 0
endif

if !exists("s:phpcomplete_enabled")
    let s:phpcomplete_enabled = 1
endif

let s:disabled_projects = []

let s:T = {
\     'number': type(0),
\     'string': type(''),
\     'function': type(function('function')),
\     'list': type([]),
\     'dictionary': type({}),
\     'float': type(0.0),
\   }


function! phpcomplete_extended#CompletePHP(findstart, base) " {{{
    if a:findstart
        let start = s:get_complete_start_pos()
        if !empty(b:completeContext)
            let b:completeContext.start = start
            let b:completeContext.base = getline('.')[start : col('.')-2]
        endif
        return start
    endif

    if !s:phpcomplete_enabled
        return []
    endif

    if !phpcomplete_extended#is_phpcomplete_extended_project()
        return []
    endif

    if empty(b:completeContext)
        return []
    endif

    let s:psr_class_complete = 0
    if b:completeContext.complete_type == "use"
        return s:getUseMenuEntries(a:base)

    elseif b:completeContext.complete_type == "nonclass"
        let s:psr_class_complete = 1
        return s:getNonClassMenuEntries(a:base)
    elseif b:completeContext.complete_type == "insideQuote"
        return s:get_plugin_inside_qoute_menu_entries(b:completeContext.fqcn, b:completeContext.lastToken)

    elseif b:completeContext.complete_type == "new"
        let s:psr_class_complete = 1
        return s:getClassMenuEntries(a:base)

    elseif b:completeContext.complete_type == "class"
        let is_static = 0
        let is_this = 0
        let lastResolutor = b:completeContext.last_resolutor
        let fqcn = b:completeContext.fqcn

        let sourceFile = phpcomplete_extended#util#substitute_path_separator(expand("%:."))
        let sourceFQCN = s:getFQCNFromFileLocation(sourceFile)

        if sourceFQCN == fqcn
            let is_this = 1
        endif

        if lastResolutor == '::'
            let is_static = 1
        endif
        if b:completeContext.complete_type == 'class'
            \ &&  lastResolutor == ""
            return []
        endif

        let menu_entries = []
        let baseMenuEntries = phpcomplete_extended#getMenuEntries(fqcn, a:base, is_this, is_static)
        "plugin callback
        let plugin_menu_entries = s:get_plugin_menu_entries(fqcn, a:base, is_this, is_static)
        let menu_entries += plugin_menu_entries
        let menu_entries += baseMenuEntries
        return menu_entries
    endif

endfunction "}}}

function! s:get_complete_start_pos() "{{{
    let line = getline('.')
    let start = col('.') - 1

    while start >= 0 && line[start - 1] =~ '[\\a-zA-Z_0-9\x7f-\xff$]'
        let start -= 1
    endwhile

    let b:completeContext = {}
    let completeContext = s:get_complete_context()
    if empty(completeContext) || !s:phpcomplete_enabled
        return start
    endif
    let b:completeContext = completeContext

    if b:completeContext.complete_type == "class" && getline('.')[start-2 : start-1] !~ '->\|::'
        let b:completeContext.last_resolutor = ""
    endif

    if !empty(completeContext)
                \ && has_key(completeContext, 'complete_type')
                \ && completeContext['complete_type'] == 'insideQuote'
                \ && match(completeContext.lastToken['insideBraceText'], '\.') != -1

        "having some problem with dot charater
        let splits = split(completeContext.lastToken['insideBraceText'], '\\\.')

        let displace = len(splits)
        if match(completeContext.lastToken['insideBraceText'], '\\\.$') < 0
            let displace += len(splits[-1]) -1
        endif
        let start = start - len(completeContext.lastToken['insideBraceText']) + displace
    endif
    return start
endfunction "}}}

function! s:get_complete_context() "{{{
    let cursorLine = getline('.')[0:col('.')-2]
    if !exists('phpcomplete_extended_context')
        let completeContext = {}
    endif

    let completeContext.complete_type = "class"

    if cursorLine =~? '^\s*use\s\+'
        " namespace completeion
        let completeContext.complete_type = "use"

    elseif cursorLine =~? '\(\s*new\|extends\)\s\+'
            \ && len(phpcomplete_extended#parsereverse(cursorLine, line('.'))) == 1
        "new class completion
        let completeContext.complete_type = "new"
    else
        if !phpcomplete_extended#is_phpcomplete_extended_project()
            return {}
        endif
        let parsedTokens = phpcomplete_extended#parsereverse(cursorLine, line('.'))

        if empty(parsedTokens)
            let  completeContext = {}
            return {}
        endif

        if has_key(parsedTokens[0], "nonClass") && parsedTokens[0]["nonClass"]
            let completeContext.complete_type = "nonclass"
        elseif has_key(parsedTokens[-1], "insideQuote")
            let lastToken = remove(parsedTokens, -1)
            let fqcn = s:guessTypeOfParsedTokens(deepcopy(parsedTokens))
            let completeContext.lastToken = lastToken
            let completeContext.lastToken['insideBraceText'] = matchstr(lastToken['insideBraceText'], '[''"]\?\zs.*\ze[''"]\?')
            let completeContext.fqcn = fqcn
            let completeContext.complete_type = "insideQuote"
        else
            let lastToken = remove(parsedTokens, -1)
            let fqcn = s:guessTypeOfParsedTokens(deepcopy(parsedTokens))
            let completeContext.complete_type = "class"
            let completeContext.last_resolutor = matchstr(cursorLine, '.*\zs\(->\|::\)\ze.*')

            let completeContext.lastToken = lastToken
            let completeContext.fqcn = fqcn
        endif
    endif
    return completeContext

endfunction "}}}

function! s:guessTypeOfParsedTokens(parsedTokens) "{{{
    if !exists('g:phpcomplete_index')
        return ""
    endif
    let parsedTokens = a:parsedTokens
    let objectGraph = []

    let sourceFile = phpcomplete_extended#util#substitute_path_separator(expand("%:."))
    let sourceFQCN = s:getFQCNFromFileLocation(sourceFile)

    "if empty(sourceFQCN)
        "return ""
    "endif

    if empty(parsedTokens)
        return ""
    endif

    let sourceNamespace = matchstr(sourceFQCN, '\zs.*\ze\\')

    let firstToken = remove(parsedTokens, 0)
    let currentFQCN = ""
    let parentFQCN = ""
    let nonClass = has_key(firstToken, "nonClass")? firstToken['nonClass']: 0

    if len(parsedTokens) == 1 && nonClass
        "normal class/function completion
    endif

    let isThis = 0
    let  pluginFQCN = s:get_plugin_fqcn(currentFQCN, firstToken)


    if firstToken['methodPropertyText'] == "$this"
        \ || firstToken['methodPropertyText'] == "self"
        \ || firstToken['methodPropertyText'] == "static"

        let currentFQCN = sourceFQCN
        let isThis = 1

    elseif firstToken['methodPropertyText'] == "parent"
        "parent
        let currentClassData = phpcomplete_extended#getClassData(sourceFQCN)
        if !has_key(currentClassData, 'parentclass')
            return ""
        endif
        let currentFQCN = currentClassData['parentclass']

    elseif pluginFQCN != ""
        let currentFQCN = pluginFQCN

    elseif has_key(firstToken, "isNew")
        let currentClassData = phpcomplete_extended#getClassData(sourceFQCN)
        let namespaces = {}
        if has_key(currentClassData, 'namespaces')
            let namespaces = currentClassData['namespaces']
        endif
        let currentFQCN = s:getFQCNForLocalVar(
            \firstToken['methodPropertyText'], namespaces)

    elseif firstToken['methodPropertyText'][0] == "$"
        "local variable
        let linesTilFunc = s:getLinesTilFunc(line('.'))
        let currentFQCN = s:geussLocalVarType(firstToken['methodPropertyText'],
                    \ sourceFQCN, linesTilFunc)

    elseif firstToken['methodPropertyText'][0] == "\\"
        let methodPropertyText = firstToken['methodPropertyText']
        let currentFQCN = s:getFQCNForNsKeyword(methodPropertyText, sourceFQCN)
        return currentFQCN
    else

        let methodPropertyText = firstToken['methodPropertyText']
        let sourceData = phpcomplete_extended#getClassData(sourceFQCN)

        let aliases = {}
        if !empty(sourceData) && has_key(sourceData['namespaces'], 'alias') && !empty(sourceData['namespaces']['alias'])
            let aliases = sourceData['namespaces']['alias']
        endif

        if empty(sourceData)
            let currentFQCN = ""

        elseif has_key(g:phpcomplete_index['classes'], methodPropertyText)
            \ || has_key(g:phpcomplete_extended_core_index['classes'], methodPropertyText)
            let currentFQCN = methodPropertyText
        elseif has_key(sourceData, 'namespaces')
                    \ && has_key(sourceData['namespaces'], 'uses')
                    \ && !empty(sourceData['namespaces']['uses'])
                    \ && has_key(sourceData['namespaces']['uses'], methodPropertyText)
            let currentFQCN = sourceData['namespaces']['uses'][methodPropertyText]. "\\" . methodPropertyText

        elseif !empty(aliases) && has_key(aliases, methodPropertyText)
            let uses_key = aliases[methodPropertyText]
            let uses_value = sourceData['namespaces']['uses'][uses_key]
            let currentFQCN = uses_value == uses_key? uses_key : uses_value. "\\" . uses_key
        endif
    endif

    if empty(currentFQCN)
        return ""
    endif

    return s:getFQCNFromTokens(parsedTokens, currentFQCN, isThis)

endfunction "}}}

function! s:getFQCNFromTokens(parsedTokens, currentFQCN, isThis) "{{{
    let parsedTokens = a:parsedTokens
    let currentFQCN = a:currentFQCN

    let isThis = a:isThis

    let isPrevTokenArray = 0

    if currentFQCN =~  '\[\]$' && len(parsedTokens) 
                \ && has_key(parsedTokens[0], 'isArrayElement')
        let currentFQCN = matchstr(currentFQCN, '\zs.*\ze\[\]$')
        let isPrevTokenArray = 1
    endif
    for token in parsedTokens
        let insideBraceText = token['insideBraceText']
        let methodPropertyText = token['methodPropertyText']
        let insideBraceText = token['insideBraceText']
        let isArrayElement = has_key(token, 'isArrayElement')? 1 :0
        let currentClassData = phpcomplete_extended#getClassData(currentFQCN)
        
        let  pluginFQCN = s:get_plugin_fqcn(currentFQCN,token)

        if insideBraceText[0] == "("
            let currentFQCN = ""
        elseif pluginFQCN != ""
            let currentFQCN = pluginFQCN
        elseif isArrayElement 
            let isPrevTokenArray = 0
            if phpcomplete_extended#isClassOfType(currentFQCN, 'ArrayAccess') 
                    \ && has_key(currentClassData['methods']['all'], 'offsetGet')
                let offsetType = currentClassData['methods']['all']['offsetGet']['return']
                if empty(offsetType)
                    return ""
                endif
                let currentFQCN = offsetType
            endif
            continue
        else
            let classPropertyType = token.isMethod == 1 ? 'method' : 'property'
            let [currentFQCN, isPrevTokenArray] = phpcomplete_extended#getFQCNForClassProperty(
                \ classPropertyType, methodPropertyText, currentFQCN, isThis)

        endif

        if isThis == 1
            let isThis = 0
        endif
        if empty(currentFQCN)
            return ""
        endif
    endfor
    if isPrevTokenArray
        return currentFQCN . '[]'
    endif
    return currentFQCN

endfunction "}}}

function! s:isScalar(type) "{{{
    let scalars =['boolean', 'integer',"float", "string", 'array', 'object',
        \ 'resource', 'mixed', 'number', 'callback', 'null', 'void',
        \ 'bool', 'self', 'int', 'callable']
    return index(scalars, a:type)
endfunction "}}}

function! s:getLinesTilFunc(currentLineNum) "{{{
    let lineNum = a:currentLineNum
    let lines = []
    "get the lines till function declaration
    while 1
        let line = phpcomplete_extended#util#trim(getline(lineNum))

        if !empty(line)
            call add(lines, line)
        endif

        if lineNum == 1 || match(line, 'function\s*\w\+\s*(.*)') > 1
            break
        endif

        let lineNum = lineNum - 1
    endwhile
    return lines
endfunction "}}}

function! s:geussLocalVarType(var, sourceFQCN, lines) "{{{
    let lines = a:lines
    let sourceFQCN = a:sourceFQCN
    let var = a:var
    let fqcn = ""
    for line in lines
        "echoerr match(line, a:var.'\s*=.*') >= 0
        if match(line, a:var.'\s*=.*') >= 0
            "var = .*
            "echoerr line
            let compiedLines = deepcopy(lines)
            let parsedTokens = s:parseForward(
                \ reverse(compiedLines),
                \ line)
            if empty(parsedTokens)
                return ""
            endif
            let fqcn = phpcomplete_extended#util#trim(s:guessTypeOfParsedTokens(parsedTokens))
            "echoerr fqcn
        elseif match(line, '@var\s*'.var.'\s*.*') >=0
            "@var $var fqcn"
            let fqcn = phpcomplete_extended#util#trim(matchstr(line, '@var\s*'.var.'\s*\zs.*\ze\s*\*'))
            let fqcn = phpcomplete_extended#getFQCNFromWord(fqcn)
        endif

        if fqcn != ""
            break
        endif
    endfor

    "try to guess from method params
    if empty(fqcn)
        let localMethodName = matchstr(lines[-1], 'function\s*\zs.*\ze(.*)')
        let sourceClassData = phpcomplete_extended#getClassData(sourceFQCN)

        if empty(sourceClassData)
            return ""
        endif

        if !has_key(sourceClassData['methods']['all'], localMethodName)
            return ""
        endif
        let methodParams = sourceClassData['methods']['all'][localMethodName]['params']
        if empty(methodParams)
            let fqcn = ""
        elseif has_key(methodParams, var) && s:isScalar(methodParams[var]) == -1
            let fqcn = methodParams[var]
        endif
    endif

    return fqcn
endfunction "}}}

function! s:parseLocalVar(lines, varLine) "{{{
    let parsedTokens = s:parseForward(a:lines, a:varLine)
endfunction "}}}

function! phpcomplete_extended#trackMenuChanges() "{{{
    let g:complete_word = ""
    let current_char = getline('.')[col('.') -2]

    if !pumvisible() && s:psr_class_complete
        \ && (current_char == '(' || current_char == ':' || current_char == ' ')
        let s:psr_class_complete = 0
        let cur_pos = getpos(".")
        let prev_pos = copy(cur_pos)
        let new_pos = copy(cur_pos)
        let new_pos[2] = new_pos[2] -2
        call setpos(".", new_pos)
        let cur_word = expand("<cword>")
        if cur_word[0] =~ '\d'
            let keyword = matchstr(getline('.')[0:new_pos[2]-1], '\s*\zs.\{-}-\(\d\+\)\?\ze')
            if match(keyword, '-') > 0
                let cur_word = keyword
            endif
            call feedkeys("\<C-o>dF-")
            call feedkeys("\<C-o>x")
            call feedkeys("\<C-o>l")
        else
            call setpos(".", prev_pos)
        endif
        let fqcn = ""
        if match(cur_word, '-') > 0
            let word = split(cur_word, '-')[0]
            let idx = split(cur_word, '-')[1] - 1
            "echoerr word

            if has_key(g:phpcomplete_index['class_fqcn'], word)
                \ && string(g:phpcomplete_index['class_fqcn'][word])[0] == '['

                let fqcn = g:phpcomplete_index['class_fqcn'][word][idx]
            endif
        elseif has_key(g:phpcomplete_index['class_fqcn'], cur_word)
                    \ && string(g:phpcomplete_index['class_fqcn'][cur_word])[0] == "'"
            let fqcn = g:phpcomplete_index['class_fqcn'][cur_word]
        endif

        if fqcn != "" && g:phpcomplete_extended_auto_add_use
            call phpcomplete_extended#addUse(cur_word, fqcn)
        endif
    endif
endfunction "}}}

function! phpcomplete_extended#addUse(word, fqcn) "{{{
    let word = a:word
    let fqcn = a:fqcn
    let cur_pos = getpos('.')

    if empty(fqcn)
        if !has_key(g:phpcomplete_index['class_fqcn'], word)
            return
        endif

        let fqcn_data = g:phpcomplete_index['class_fqcn'][word]

        if empty(fqcn) && string(fqcn_data)[0] == '['
            let fqcn_data = deepcopy(g:phpcomplete_index['class_fqcn'][word])
            let prompt_data =  map(copy(fqcn_data), 'v:key+1.". " . v:val')
            call insert(prompt_data, "Select FQCN:")
            let selected = inputlist(prompt_data)
            let fqcn = fqcn_data[selected-1]
        elseif empty(fqcn) && string(fqcn_data)[0] == "'"
            let fqcn = fqcn_data
        endif

        if empty(fqcn)
            return
        endif

    endif


    let lines_to_class = getline(0, search('^\s*\(class\|interface\)'))
    call setpos('.', cur_pos)

    if empty(lines_to_class) || phpcomplete_extended#util#trim(lines_to_class[0]) != "<?php"
        return
    endif

    let last_use_pos = -1
    let namespace_pos = -1

    for line in lines_to_class
        if match(line, '^\s*use\s*'.escape(fqcn, '\').'\s*;') >= 0
            return
        endif
        if match(line, '^\s*use\s*.*;') >=0
            let last_use_pos = index(lines_to_class, line)+1
        endif
        if match(line, '^\s*namespace\s*.*;') >=0
            let namespace_pos = index(lines_to_class, line)+1
        endif

    endfor
    if last_use_pos == -1
        let last_use_pos = namespace_pos == -1? 1 : namespace_pos + 1
    endif
    call append(last_use_pos, ['use '. fqcn. ';'])
    let cur_pos[1] = cur_pos[1] +1
    call setpos('.', cur_pos)

endfunction "}}}

function! phpcomplete_extended#gotoSymbolORDoc(type) "{{{
    let data = {}
    let type = a:type
    if match(expand('%'), 'phpcomplete_extended-Doc') == 0
        let type = 'doc'
    endif

    let cur_word  = expand("<cword>")
    let word_fqcn = s:get_plugin_resolve_fqcn(cur_word)
    if word_fqcn == cur_word
        let word_fqcn = phpcomplete_extended#getFQCNFromWord(cur_word)
    endif

    "messed up
    if word_fqcn != ""
        let classData = phpcomplete_extended#getClassData(word_fqcn)
        if !has_key(g:phpcomplete_index['fqcn_file'], word_fqcn)
            if type == "doc"
                let data = {
                    \'doc': classData['docComment'],
                    \ 'title': word_fqcn
                \}
            elseif type == "goto"
                let data = {}
            endif
        else
            let filename = g:phpcomplete_index['fqcn_file'][word_fqcn]
            let line = classData['startLine']
            let data = {}
            let data.file = filename
            let data.command = '+' . line
            let data.title = word_fqcn
            let data.doc = classData['docComment']
        endif
    else
        let data = s:getJumpDataOfCurrentWord(type)
    endif


    if empty(data)
        if type == 'doc'
            call feedkeys('K', 'n')
        elseif type == 'goto'
            call feedkeys("\<C-]>", 'n')
        endif
        return
    endif

    if type == 'doc'
        call s:openDoc(data)

    elseif type == 'goto'
        call s:gotoLine(data)
    endif
endfunction "}}}

function! phpcomplete_extended#getFQCNFromWord(word) "{{{
    let word = a:word
    let current_file_location = phpcomplete_extended#util#substitute_path_separator(fnamemodify(bufname('%'), ':p:.')) "current file
    let current_fqcn = s:getFQCNFromFileLocation(current_file_location)
    if current_fqcn == ""
        return ""
    endif
    let current_class_data = phpcomplete_extended#getClassData(current_fqcn)
    if empty(current_class_data)
        return ""
    endif

    let fqcn = s:getFQCNForLocalVar(word, current_class_data['namespaces'])
    if fqcn == ""
        return ""
    endif
    return fqcn
endfunction "}}}

function! s:gotoLine(data) "{{{
    silent! execute "e ". a:data.command . ' ' . a:data.file
    silent! execute "normal! zt"
    normal! zv
    normal! zz
endfunction "}}}

function! s:openDoc(data) "{{{
        let doc_buf_exists = 0
        for i in range(1, winnr('$'))
            let bufname = bufname(winbufnr(i))
            if match(bufname, 'phpcomplete_extended-Doc') != -1
                let doc_buf_exists = 1
                let winnr = i
            endif
        endfor

        "TODO: go to previous doc

        if doc_buf_exists
            silent! execute winnr."wincmd w"
            setlocal modifiable noreadonly
            normal ggdG
        else
            silent! execute "new"
            setlocal modifiable noreadonly
            setlocal nobuflisted
            setlocal buftype=nofile noswapfile
            setlocal bufhidden=delete
            setlocal nonumber
            let bufname = printf("%s: %s", 'phpcomplete_extended-Doc', a:data['title'])
            silent! file `=bufname`
        endif


        let doc = map(split(a:data['doc'], "\n"), "substitute(v:val, '    ', '', 'g')")
        call append(0, doc)
        "TODO: set ft=php "slow, have to do some thing about it
        normal! gg
        setlocal nomodifiable readonly
endfunction "}}}

function! s:getJumpDataOfCurrentWord(type) "{{{
    let cur_word  = expand("<cword>")
    let match_till_cur_word = matchstr(getline('.'), '\s*\zs\C.\{-}\<'.cur_word . '\>' .'(\?')
    if match_till_cur_word[len(match_till_cur_word)-1] == "("
        let match_till_cur_word .= "'')"
    endif
    let parsedTokens = phpcomplete_extended#parsereverse(match_till_cur_word, line('.'))

    if empty(parsedTokens)
        return {}
    endif

    "very messed up, have to break it
    let static_or_ref = 0
    if len(parsedTokens) == 1 &&
            \ (
            \ has_key(parsedTokens[0], 'isNew')
            \ || has_key(parsedTokens[0], 'nonClass')
            \ || parsedTokens[0]['methodPropertyText'][0] == "$"
            \)
        let lastToken = parsedTokens[0]
    "elseif len(parsedTokens) == 2
            "\ && match(match_till_cur_word, "::") > 0
        "let lastToken = remove(parsedTokens, 0)
        "let static_or_ref = 1
    else
        let lastToken = remove(parsedTokens, -1)
    endif

    if has_key(lastToken, 'nonClass')
        let fqcn = lastToken['methodPropertyText']
        let fqcn = s:get_plugin_resolve_fqcn(fqcn)
    else
        let fqcn = s:guessTypeOfParsedTokens(parsedTokens)
        let fqcn = s:get_plugin_resolve_fqcn(fqcn)

        if fqcn == ""
            return {}
        endif
    endif

    if static_or_ref
        let methodPropertyText = parsedTokens[0]['methodPropertyText']
    else
        let methodPropertyText = lastToken['methodPropertyText']
    endif


    let fqcn_data = {}

    let isInternal = 0
    let isMethod = 0
    let isClass = 0
    let isProperty = 0
    if has_key(lastToken, 'nonClass') && lastToken['nonClass']
        let isInternal = 1

        if static_or_ref
            let isMethod = 1
            let cur_word = fqcn
        endif

        if has_key(g:phpcomplete_extended_core_index['functions'], cur_word)
            let fqcn_data = g:phpcomplete_extended_core_index['functions'][cur_word]
        elseif has_key(g:phpcomplete_extended_core_index['classes'], cur_word)
            let fqcn_data = g:phpcomplete_extended_core_index['classes'][cur_word]
        endif

        if static_or_ref
            \ && has_key(fqcn_data['methods']['all'], methodPropertyText)
            let fqcn_data = fqcn_data['methods']['all'][methodPropertyText]
        endif

    elseif has_key(lastToken, 'isMethod')
        let isProperty = !lastToken['isMethod']
        let isMethod = lastToken['isMethod']
        let fqcn_data = {}

        let method_property_key = lastToken['isMethod']? 'methods' : 'properties'
        if has_key(g:phpcomplete_index['classes'], fqcn)
            \ && has_key(g:phpcomplete_index['classes'][fqcn][method_property_key]['all'], methodPropertyText)

            let fqcn_data = g:phpcomplete_index['classes'][fqcn][method_property_key]['all'][methodPropertyText]

        elseif has_key(g:phpcomplete_extended_core_index['classes'], fqcn)
            \ && has_key(g:phpcomplete_extended_core_index['classes'][fqcn][method_property_key]['all'], methodPropertyText)

            let fqcn_data = g:phpcomplete_extended_core_index['classes'][fqcn][method_property_key]['all'][methodPropertyText]
        endif

    elseif has_key(lastToken, 'isNew')
        let isClass = 1
        let fqcn_data = phpcomplete_extended#getClassData(fqcn)
    else
        let isClass = 1
        let fqcn_data = phpcomplete_extended#getClassData(fqcn)
    endif
    if empty(fqcn_data)
        return {}
    endif
    if a:type == 'doc'

        let title = fqcn
        if isMethod
            let title = fqcn. "--". methodPropertyText . "()"
        endif

        if isProperty
            let title = fqcn. "--". methodPropertyText
        endif
        if isInternal
            let title = fqcn
        endif
        return {
                \'doc': fqcn_data['docComment'],
                \ 'title': title
            \}
    elseif a:type == 'goto'
        if isInternal
            return {}
        endif
        let classData = phpcomplete_extended#getClassData(fqcn)

        let gotoData = {}

        if isClass
            let gotoData.file = fqcn_data['file']
            let gotoData.command = '+'.fqcn_data['startLine']
        elseif isMethod
            let gotoData.file = fqcn_data['origin']
            let gotoData.command = '+'.fqcn_data['startLine']
        elseif isProperty
            let gotoData.file = fqcn_data['origin']
            let gotoData.command ='+/^\\s*\\(private\\|public\\|protected\\|static\\)\\s*.*'.'$'.methodPropertyText
        endif
        return gotoData
    endif
    return {}
endfunction "}}}

function! s:parseForward(lines, varLine) "{{{
    call remove(a:lines, 0, index(a:lines, a:varLine))
    let lines = a:lines
    let varDecLine = phpcomplete_extended#util#trim(matchstr(a:varLine, '=\zs.*'))
    let joinedLine = varDecLine.join(lines, "")
    let parsedTokens = []
    let parsedTokens = phpcomplete_extended#parser#forwardParse(joinedLine, parsedTokens)

    if len(parsedTokens) && !has_key(parsedTokens[-1], 'pEnd')
        return []
    endif
    return parsedTokens
endfunction "}}}

function! phpcomplete_extended#parsereverse(cursorLine, cursorLineNumber) "{{{
    if !exists('g:phpcomplete_index')
        return []
    endif
    let cursorLine = phpcomplete_extended#util#trim(a:cursorLine)
    let parsedTokens = []
    let parsedTokens = phpcomplete_extended#parser#reverseParse(cursorLine, [])


    if empty(parsedTokens) 
            \ || (len(parsedTokens) && has_key(parsedTokens[0], 'start') && parsedTokens[0].start == 0)
        let linesTillFunc = s:getLinesTilFunc(a:cursorLineNumber)
        let joinedLines = join(reverse(linesTillFunc),"")
        let parsedTokens =  phpcomplete_extended#parser#reverseParse(joinedLines, [])
        return parsedTokens
    endif
    return parsedTokens
endfunction "}}}

function! s:getFQCNForNsKeyword(keyword, sourceFQCN) "{{{
    let sourceData = phpcomplete_extended#getClassData(a:sourceFQCN)
    let keyword = matchstr(a:keyword, '\\\zs.*')
    let fqcn = ""

    if empty(sourceData)
        return ""
    endif
    let sourceUses = sourceData['namespaces']['uses']

    let splitedKeyword = split(keyword, "\\")
    if has_key(sourceUses, splitedKeyword[0])
        let key = remove(splitedKeyword, 0)
        let fqcn = sourceUses[key] . "\\" .keyword
    else
        let keywordClassData = phpcomplete_extended#getClassData(keyword)
        if !empty(keywordClassData)
            let fqcn = keyword
        endif
    endif
    return fqcn

endfunction "}}}

function! s:getFQCNForLocalVar(classname, namespaces) " {{{
    let classname = a:classname
    let namespaces = a:namespaces
    let aliases = {}
    if has_key(a:namespaces, 'alias') && !empty(a:namespaces['alias'])
        let aliases = a:namespaces['alias']
    endif

    let fqcn = ""

    if has_key(g:phpcomplete_index['fqcn_file'], classname)
        return classname
    endif
    if has_key(namespaces, 'uses') && len(namespaces['uses']) != 0 && has_key(namespaces['uses'], classname) "if no data found json_encode make it to dictionary
        let fqcn = namespaces['uses'][classname]. "\\". classname
        return fqcn
    endif
    if !empty(aliases) && has_key(aliases, classname)
        let uses_key = aliases[classname]
        let uses_value = namespaces['uses'][uses_key]
        let fqcn = uses_value == uses_key? uses_key : uses_value. "\\" . uses_key
        return fqcn
    endif
    if has_key(g:phpcomplete_extended_core_index['classes'], classname)
        return classname
    endif
    let namespace_section = ""
    if has_key(namespaces, 'file')
        let namespace_section = namespaces['file'] . "\\"
    endif
    let full_fqcn = namespace_section . classname
    if has_key(g:phpcomplete_index['fqcn_file'], full_fqcn)
        return full_fqcn
    endif
    return ""
endfunction
" }}}

function! phpcomplete_extended#getFQCNForClassProperty(type, property, parent_fqcn, is_this) " {{{
    let type = a:type
    let is_this = a:is_this
    let property = a:property
    let classname = ''
    let isArray = 0

    let this_fqcn = a:parent_fqcn
    let this_fqcn = s:get_plugin_resolve_fqcn(this_fqcn)
    let this_class_data = phpcomplete_extended#getClassData(this_fqcn)

    if empty(this_class_data)
        return ["", 0]
    endif


    "in same namespace folder. so not declared in use section
    if type == 'property'
        let this_properties = this_class_data['properties']['all']
        if !has_key(this_properties, property)
            return ['', 0]
        endif
        let classname = this_properties[property]['type']
        if has_key(this_properties[property], 'array_type')
            let isArray = this_properties[property]['array_type']
        endif
    elseif type == 'method'
        let this_methods = this_class_data['methods']['all']
        if !has_key(this_methods, property)
            return ['', 0]
        endif
        if has_key(this_methods[property], 'return')
            let classname = this_methods[property]['return']
            if has_key(this_methods[property], 'array_return')
                let isArray = this_methods[property]['array_return']
            endif
        endif
    endif

    return [classname, isArray]
endfunction " }}}

function! s:getClassMenuEntries(base) "{{{
    let class_menu_entries = deepcopy(g:phpcomplete_index['class_func_menu_entries'])
    let class_menu_entries = filter(class_menu_entries, 'v:val.word =~ "^' . a:base .'" && v:val.kind == "c"')
    return class_menu_entries
endfunction "}}}

function! s:getUseMenuEntries(base) "{{{
    let menu_list = []
    let fqcns = deepcopy(keys(g:phpcomplete_index['fqcn_file']))
    if a:base != ''
        "echoerr escape(a:base, ' \')
        "let fqcns  = filter(fqcns, 'v:val =~ "^' . escape(a:base, ' \') .'"')
        let fqcns  = filter(fqcns, 'v:val =~ "^' . escape(a:base, ' \\') .'"')
    endif
    for fqcn in fqcns
        let menu_list += [{'word': fqcn,'kind': 'v', 'menu': fqcn, 'info': fqcn}]
    endfor
    return menu_list
endfunction "}}}

function! s:getNonClassMenuEntries(base) "{{{
    let menu_entries = []
    let class_func_menu_entries = deepcopy(g:phpcomplete_index['class_func_menu_entries'])
    let class_func_menu_entries = filter(class_func_menu_entries, 'v:val.word =~# "^' . a:base .'"')
    let plugin_menu_entries = s:get_plugin_menu_entries("", a:base, 1, 0)

    let menu_entries += plugin_menu_entries
    let menu_entries += class_func_menu_entries

    return menu_entries
endfunction "}}}

function! phpcomplete_extended#getMenuEntries(fqcn, base, is_this, is_static) " {{{
    let empty_dict = []
    let fqcn = a:fqcn
    let is_this = a:is_this
    let is_static = a:is_static
    if fqcn == ""
        return []
    endif

    let menu_list = []

    let class_data = phpcomplete_extended#getClassData(fqcn)
    if len(class_data) == 0
        return empty_dict
    endif

    "constants
    if is_static
        if len(class_data['constants']) != 0
            let constants = deepcopy(keys(class_data['constants']))
            if a:base != ''
                let constants  = filter(constants, 'v:val =~ "^' . a:base .'"')
            endif
            for constant in constants
                let menu_list += [{'word': constant,'kind': 'v', 'menu': constant, 'info': constant}]
            endfor
        endif
    endif

    "properties
    if len(class_data['properties']['all']) != 0
        if is_this
            let properties = deepcopy(keys(class_data['properties']['all']))
        elseif is_static
            let properties = deepcopy(class_data['properties']['modifier']['static'])
        else
            let properties = deepcopy(class_data['properties']['modifier']['public'])
        endif
        if a:base != ''
            let properties  = filter(properties, 'v:val =~ "^' . a:base .'"')
        endif
        for property in properties
            let property_data = class_data['properties']['all'][property]
            let menu_list += [{'word': property,'kind': 'v', 'menu': property, 'info': property_data['docComment']}]
        endfor
    endif

    "methods
    if len(class_data['methods']['all']) != 0
        if is_this
            let methods = deepcopy(keys(class_data['methods']['all']))
        elseif is_static
            let m = deepcopy(class_data['methods']['modifier']['static'])
            "sometime json_encode makes methods array as dictionary
            if type(m) == 4
                let methods = values(m)
            else
                let methods = m
            endif
        else
            let m = deepcopy(class_data['methods']['modifier']['public'])
            "sometime json_encode makes methods array as dictionary
            if type(m) == 4
                let methods = values(m)
            else
                let methods = m
            endif
        endif

        if a:base != ''
            let methods  = filter(methods, 'v:val =~ "^' . a:base .'"')
        endif
        for method in methods
            if match(method, '__') == 0
                continue
            endif

            let method_info = class_data['methods']['all'][method]
            let menu_list += [{'word': method,'kind': 'f', 'menu': method_info['signature'], 'info': method_info['docComment']}]
        endfor
    endif
    return menu_list
endfunction
" }}}

function! s:getFQCNFromFileLocation(filelocation) " {{{
    "TODO: add cache
    if !exists('g:phpcomplete_index')
        return ""
    endif
    let filelocation = a:filelocation
    if has('win32') || has('win64')
        let filelocation = substitute(filelocation,'\\', '/', 'g')
    endif
    let fqcn = ""
    if has_key(g:phpcomplete_index['file_fqcn'], filelocation)
        let fqcn = g:phpcomplete_index['file_fqcn'][filelocation]
    endif
    return fqcn
endfunction
" }}}

function! phpcomplete_extended#getFileFromFQCN(fqcn) "{{{
    let fqcn = a:fqcn
    let filelocation = ""
    if has_key(g:phpcomplete_index['fqcn_file'], fqcn)
        let filelocation = g:phpcomplete_index['fqcn_file'][fqcn]
        if has('win32') || has('win64')
            let filelocation = substitute(filelocation,'\\', '/', 'g')
        endif
    endif
    return filelocation
endfunction "}}}

function! phpcomplete_extended#getClassKeyFromFQCN(fqcn) "{{{
    let fqcn = a:fqcn
    let classKey = ""
    if !g:phpcomplete_extended_cache_disable && has_key(g:phpcomplete_index_cache['fqcn_classkey_cache'], fqcn)
        return g:phpcomplete_index_cache['fqcn_classkey_cache'][fqcn]
    else
        if has_key(g:phpcomplete_index['fqcn_classkey'], fqcn)
            let classKey = g:phpcomplete_index['fqcn_classkey'][fqcn]
            let g:phpcomplete_index_cache['fqcn_classkey_cache'][fqcn] = classKey
        endif
    endif
    return classKey
endfunction "}}}

function! phpcomplete_extended#getClassData(fqcn) " {{{
    let fqcn = a:fqcn
    let empty_dict = {}
    if g:phpcomplete_extended_cache_disable == 0 && has_key(g:phpcomplete_index_cache['classname_cache'], fqcn)
        let data = g:phpcomplete_index_cache['classname_cache'][fqcn]
    else

        if has_key(g:phpcomplete_index['classes'], fqcn)
            let data = g:phpcomplete_index['classes'][fqcn]
        elseif has_key(g:phpcomplete_extended_core_index['classes'], fqcn)
            let data = g:phpcomplete_extended_core_index['classes'][fqcn]
        else
            return empty_dict
        endif
        if has_key(data['methods']['all'], 'nnnnnnnn')
            call remove(data['methods']['all'], "nnnnnnnn")
        endif

        if has_key(data['properties']['all'], 'nnnnnnnn')
            call remove(data['properties']['all'], "nnnnnnnn")
        endif

        let g:phpcomplete_index_cache['classname_cache'][fqcn] = data
        return data
    endif
    return g:phpcomplete_index_cache['classname_cache'][fqcn]
endfunction
" }}}

function! s:setClassData(fqcn, file,  classData) "{{{
    let fqcn = a:fqcn
    let file = a:file
    let className = a:classData['classname']
    let classData = a:classData
    let g:phpcomplete_index['classes'][fqcn] = classData
    let g:phpcomplete_index['file_fqcn'][file] = fqcn
    let g:phpcomplete_index['fqcn_file'][fqcn] = file

    if index(g:phpcomplete_index['class_list'], className) == -1
        call add(g:phpcomplete_index['class_list'], className)
    endif

    if has_key(g:phpcomplete_index['class_fqcn'], className)
        if string(g:phpcomplete_index['class_fqcn'][className])[0] == "'"
            \ && g:phpcomplete_index['class_fqcn'][className] != fqcn

            let tmp_class_fqcn = []
            call add(tmp_class_fqcn, g:phpcomplete_index['class_fqcn'][className])
            call add(tmp_class_fqcn, fqcn)
            call remove(g:phpcomplete_index['class_fqcn'], className)
            let g:phpcomplete_index['class_fqcn'][className] = tmp_class_fqcn

        elseif string(g:phpcomplete_index['class_fqcn'][className])[0] == "["
                \ && index(g:phpcomplete_index['class_fqcn'][className], fqcn) == -1

            call add(g:phpcomplete_index['class_fqcn'][className], fqcn)
        endif
    else
        let g:phpcomplete_index['class_fqcn'][className] = fqcn
    endif

    if index(g:phpcomplete_index['class_func_const_list'], className) == -1
        call add(g:phpcomplete_index['class_func_const_list'], className)
    endif

    if has_key(g:phpcomplete_index_cache['classname_cache'], fqcn)
        call remove(g:phpcomplete_index_cache['classname_cache'], fqcn)
    endif
endfunction "}}}

function! s:makeCacheDir() "{{{
    let cache_dir = phpcomplete_extended#util#substitute_path_separator(fnamemodify(getcwd(), ':p:h').'/.phpcomplete_extended')
    if !isdirectory(cache_dir)
        call mkdir(cache_dir)
    endif
endfunction "}}}

function! phpcomplete_extended#saveIndexCache() " {{{
    if !phpcomplete_extended#is_phpcomplete_extended_project()
        return
    endif

    let cache_dir = phpcomplete_extended#util#substitute_path_separator(fnamemodify(getcwd(), ':p:h').'/.phpcomplete_extended')
    let index_cache_file = phpcomplete_extended#util#substitute_path_separator(fnamemodify(cache_dir, ':p:h')."/index_cache")
    let content = []
    if exists('g:phpcomplete_index_cache')
        call add(content, string(g:phpcomplete_index_cache))
        call writefile(content, index_cache_file)
    endif
endfunction
" }}}

function! s:getCacheFile() " {{{
    if !phpcomplete_extended#is_phpcomplete_extended_project()
        return
    endif
    let index_cache_file = phpcomplete_extended#util#substitute_path_separator(fnamemodify(getcwd(), ':p:h')."/.phpcomplete_extended/index_cache")
    return index_cache_file
endfunction
" }}}

function! s:loadIndexCache() " {{{
    if !phpcomplete_extended#is_phpcomplete_extended_project()
        return
    endif
    if exists('g:phpcomplete_index_cache_loaded') && g:phpcomplete_index_cache_loaded
        return
    endif

    let index_cache_file = s:getCacheFile()

    if !filereadable(index_cache_file)
        let g:phpcomplete_index_cache = {}
        let g:phpcomplete_index_cache['classname_cache'] = {}
        let g:phpcomplete_index_cache['namespace_cache'] = {}
        let g:phpcomplete_index_cache['methods_cache'] = {}
        let g:phpcomplete_index_cache['fqcn_classkey_cache'] = {}
    else
        let content = readfile(index_cache_file)
        if len(content) == 0
            let g:phpcomplete_index_cache = {}
            let g:phpcomplete_index_cache['classname_cache'] = {}
            let g:phpcomplete_index_cache['namespace_cache'] = {}
            let g:phpcomplete_index_cache['methods_cache'] = {}
            let g:phpcomplete_index_cache['fqcn_classkey_cache'] = {}
        else
            let true = 1
            let false = 0
            let null = 0
            sandbox let g:phpcomplete_index_cache = eval(join(content, '\n'))
        endif
    endif
    let g:phpcomplete_index_cache_loaded = 1
endfunction
" }}}

function! s:loadIndex() " {{{
    if !phpcomplete_extended#is_phpcomplete_extended_project()
        return
    endif

    if exists('g:phpcomplete_index_loaded') && g:phpcomplete_index_loaded
        return
    endif

    if index(s:disabled_projects, getcwd()) != -1
        return
    endif

    let index_file = phpcomplete_extended#util#substitute_path_separator(getcwd().'/.phpcomplete_extended/phpcomplete_index')
    let plugin_index_file = s:getPluginIndexFileName()

    if !filereadable(index_file)
        let initial_message = "Composer project detected, Do you want to create index?"
        let ret = phpcomplete_extended#util#input_yesno(initial_message)
        if !ret
            call add(s:disabled_projects, getcwd())
            return
        endif
        echo "\n\n"
        call phpcomplete_extended#generateIndex()
    endif

    if !g:phpcomplete_index_loaded
        call phpcomplete_extended#util#print_message("Loading Index")
        if filereadable(plugin_index_file)
            let s:plugin_index = s:readIndex(plugin_index_file)
            call s:set_plugin_indexes(plugin_index_file)
            let s:plugin_ftime = getftime(plugin_index_file)
        endif

        let g:phpcomplete_index = s:readIndex(index_file)
        call phpcomplete_extended#util#print_message("Index Loaded.")
        let g:phpcomplete_index_loaded = 1
    endif

endfunction
" }}}
"
function! phpcomplete_extended#clear_disabled_project() "{{{
    let s:disabled_projects = []
endfunction "}}}

function! s:clearIndex() " {{{
    if !phpcomplete_extended#is_phpcomplete_extended_project()
        return
    endif
    let g:phpcomplete_index = {}
    let g:phpcomplete_index_loaded = 0
endfunction
" }}}

function! phpcomplete_extended#readDataForProject() "{{{
    if !s:isCurrentProject()
        let s:current_project_dir = getcwd()
        let g:phpcomplete_index_loaded = 0
        call phpcomplete_extended#loadProject()
    endif
endfunction "}}}

function! phpcomplete_extended#clearIndexCache() " {{{
    if !phpcomplete_extended#is_phpcomplete_extended_project()
        return
    endif
    let index_cache_file = s:getCacheFile()
    if filereadable(index_cache_file)
        call delete(index_cache_file)
    endif
    let g:phpcomplete_index_cache = {}
    let g:phpcomplete_index_cache['classname_cache'] = {}
    let g:phpcomplete_index_cache['namespace_cache'] = {}
    let g:phpcomplete_index_cache['methods_cache'] = {}
    let g:phpcomplete_index_cache_loaded = 0
endfunction
" }}}

function! s:isCurrentProject() "{{{
    return s:current_project_dir == getcwd()
endfunction "}}}

function! phpcomplete_extended#is_phpcomplete_extended_project() " {{{
    if filereadable(getcwd(). '/composer.json') && filereadable(getcwd(). "/vendor/autoload.php")
        return 1
    endif
    return 0
endfunction
" }}}

function! phpcomplete_extended#loadProject() "{{{
    if !phpcomplete_extended#is_phpcomplete_extended_project()
        return
    endif

    let s:update_info = {}
    call s:register_plugins()

    call s:makeCacheDir()
    call phpcomplete_extended#loadCoreIndex()

    call s:loadIndex()
    call s:loadIndexCache()

endfunction "}}}

function! phpcomplete_extended#reload() " {{{
    if !phpcomplete_extended#is_phpcomplete_extended_project()
        return
    endif

    let s:update_info = {}
    call s:register_plugins()

    call s:makeCacheDir()
    call phpcomplete_extended#loadCoreIndex()

    call s:clearIndex()
    call phpcomplete_extended#clearIndexCache()

    call s:loadIndex()
    call s:loadIndexCache()
endfunction
" }}}

function! s:copyCoreIndex() "{{{
    let src  = phpcomplete_extended#util#substitute_path_separator(g:phpcomplete_extended_root_dir . "/bin/core_index")
    let cache_dir = phpcomplete_extended#util#substitute_path_separator(fnamemodify(getcwd(), ':p:h').'/.phpcomplete_extended')
    let dest = phpcomplete_extended#util#substitute_path_separator(cache_dir. "/core_index")
    if empty(findfile(dest, cache_dir))
        call phpcomplete_extended#util#copy(src, dest)
    endif
endfunction "}}}

function! phpcomplete_extended#loadCoreIndex() "{{{
    if exists('g:core_index_loaded') && g:core_index_loaded
        return
    endif
    let cache_dir = phpcomplete_extended#util#substitute_path_separator(fnamemodify(getcwd(), ':p:h').'/.phpcomplete_extended')
    let location = phpcomplete_extended#util#substitute_path_separator(cache_dir. "/core_index")

    if !filereadable(location)
        call s:copyCoreIndex()
    endif

    let content = readfile(location)
    let true = 1
    let false = 0
    let null = 0
    sandbox let g:phpcomplete_extended_core_index = eval(join(content, '\n'))
    let g:core_index_loaded = 1
endfunction "}}}

function! phpcomplete_extended#generateIndex(...) "{{{
    if !s:valid_composer_command()
        echoerr printf('The composer command "%s" is not a valid Composer command. Please set g:phpcomplete_index_composer_command in your .vimrc file', g:phpcomplete_index_composer_command)
        return
    endif

    call s:makeCacheDir()
    call s:copyCoreIndex()
    call s:register_plugins()

    let input = g:phpcomplete_extended_root_dir . "/bin/IndexGenerator.php generate"
    if len(a:000) == 1 && a:1 == '-verbose'
        let input .= ' -verbose'
    endif
    let input = phpcomplete_extended#util#substitute_path_separator(input)

    let plugin_php_file_command = join(map(copy(s:plugin_php_files), '" -u ".v:val'))

    let cmd = 'php ' . input . plugin_php_file_command
    "echoerr cmd
    "return

    let composer_command = g:phpcomplete_index_composer_command . " dumpautoload --optimize  1>/dev/null 2>&1"
    echomsg "Generating autoload classmap"
    call vimproc#system(composer_command)

    echomsg "Generating index..."
    let out =  vimproc#system(cmd)
    if out == "success"
        echomsg "Index generated"
    endif
    echo out
endfunction "}}}

function! s:valid_composer_command() "{{{
    let cmd    = printf('%s --version', g:phpcomplete_index_composer_command)
    let output = system(cmd)
    return output =~ 'Composer version'
endfunction "}}}

function! phpcomplete_extended#updateIndex(background) "{{{
    let s:update_info = {}
    if !phpcomplete_extended#is_phpcomplete_extended_project() || &ft != 'php'
        return
    endif
    let file_location = phpcomplete_extended#util#substitute_path_separator(fnamemodify(bufname('%'), ':p:.')) "current file
    let update_time = getftime(bufname('%'))
    let fileName = 'update_cache_'. update_time
    let plugin_php_file_command = join(map(copy(s:plugin_php_files), '" -u ".v:val'))
    let input = printf('%s %s %s %s', g:phpcomplete_extended_root_dir . "/bin/IndexGenerator.php update" , file_location,  fileName, plugin_php_file_command)
    let input = phpcomplete_extended#util#substitute_path_separator(input)
    let cmd = 'php '. input

    if a:background
        let cmd .= ' 1>/dev/null 2>/dev/null
        call vimproc#system_bg(cmd)
    else
        let out =  vimproc#system(cmd)
        echo out
    endif

    let s:update_info['update_available'] = 1
    let s:update_info['update_time'] = update_time
    let s:update_info['update_file_name'] = fileName
    let s:update_info['updated_file'] = file_location
endfunction "}}}

function! s:get_update_command() "{{{

    return cmd
endfunction "}}}

function! phpcomplete_extended#checkUpdates() "{{{
    if !phpcomplete_extended#is_phpcomplete_extended_project()
        return
    endif
    let timeout = 1000
    "echoerr string(s:update_info)
    if has_key(s:update_info, 'update_available') && s:update_info['update_available']
        if localtime() - s:update_info['update_time'] > timeout
            let s:update_info = {}
            return
        endif
        let update_file = phpcomplete_extended#util#substitute_path_separator(fnamemodify(getcwd(), ':p:h').'/.phpcomplete_extended/'.s:update_info['update_file_name'])

        if filereadable(update_file)
            try
                let updateData = s:readIndex(update_file)
            catch 
                echoerr "Error occured while reading update index"
                return
            endtry

            call s:updateLocalCache(updateData)
            call delete(update_file)
            let s:update_info = {}
        endif
    endif

    let plugin_index_file = s:getPluginIndexFileName()

    if !filereadable(plugin_index_file)
        return
    endif

    let plugin_file_time = getftime(plugin_index_file)
    if plugin_file_time > s:plugin_ftime
        call s:set_plugin_indexes(plugin_index_file)
        let s:plugin_ftime = plugin_file_time
    endif

endfunction "}}}

function! s:getPluginIndexFileName() "{{{
    return phpcomplete_extended#util#substitute_path_separator(fnamemodify(getcwd(), ':p:h').'/.phpcomplete_extended/plugin_index')
endfunction "}}}

function! s:readIndex(filename) "{{{
    if(!filereadable(a:filename))
        echoerr printf('Could not read index file %s', fnamemodify(a:filename, ':.'))
        return
    endif
    let file_content = readfile(a:filename)
    let true = 1
    let false = 0
    let null = 0
    sandbox let eval_data = eval(file_content[0])
    return eval_data
endfunction "}}}

function! s:updateLocalCache(updateData) "{{{
    let updateData = a:updateData
    let fqcn = updateData['classdata']['fqcn']
    let file = updateData['classdata']['file']
    let classData = updateData['classdata']['data']
    let extendsData = updateData['extends']
    let implementsData = updateData['interfaces']
    call s:setClassData(fqcn, file, classData)
    call s:updateIntrospectionData('extends', fqcn, extendsData)
    call s:updateIntrospectionData('implements', fqcn, implementsData)
    call s:updateMenuEntries(classData['classname'])
endfunction "}}}

function! s:updateIntrospectionData(type,fqcn, data) "{{{
    let type = a:type "type is extends/implements
    let fqcn = a:fqcn
    let data = a:data
    let collection = g:phpcomplete_index[type]
    if type(data) != type({})
        return
    endif
    for added in data['added']
        if !has_key(collection, added)
            let collection[added] = []
        endif
        call add(collection[added], fqcn)
    endfor
    for removed in data['removed']
        if !has_key(collection, removed)
            continue
        endif

        let index = index(collection[removed], fqcn)
        if index < 0
            continue
        endif

        call remove(collection[removed], index(collection[removed], fqcn))
    endfor
endfunction "}}}

function! s:updateMenuEntries(className) "{{{
    let className = a:className
    let fqcns = g:phpcomplete_index['class_fqcn'][className]
    let class_menu_entries = g:phpcomplete_index['class_func_menu_entries']
    let idx = 0

    for class_menu_entry in class_menu_entries
        if class_menu_entry['word'] =~ '^'. className
            call remove(class_menu_entries, idx)
        else
            let idx = idx + 1
        endif
    endfor

    let menu_entries = []
    if type(fqcns) == type('')
        call add(menu_entries,  {
            \ 'word': className,
            \ 'kind': 'c',
            \ 'menu': fqcns,
            \ 'info': fqcns
            \ }
        \)

    elseif type(fqcns) == type([])
        let i = 1
        for fqcn in fqcns
            call add(menu_entries , {
                \ 'word': className. '-'. i,
                \ 'kind': 'c',
                \ 'menu': fqcn,
                \ 'info': fqcn
                \}
            \)
            let i = i + 1
        endfor
    endif
    for menu_entry in menu_entries
        " add at last for now
        call add(class_menu_entries, menu_entry)
    endfor

endfunction "}}}

function! phpcomplete_extended#isClassOfType(classFQCN, typeFQCN) "{{{
    if a:classFQCN == a:typeFQCN
        return 1
    endif

    if has_key(g:phpcomplete_index['extends'], a:typeFQCN)
        \ && index(g:phpcomplete_index['extends'][a:typeFQCN], a:classFQCN) >= 0
        return 1
    endif

    if has_key(g:phpcomplete_index['implements'], a:typeFQCN)
        \ && index(g:phpcomplete_index['implements'][a:typeFQCN], a:classFQCN) >= 0
        return 1
    endif

    return 0
endfunction "}}}

" Blatantly copied form thinca/vim-ref :)
let s:plugin_prototype = {}  " {{{ plugin prototype
let s:plugin_prototype.plugin_name = ""
function! s:plugin_prototype.set_index(index)
endfunction
function! s:plugin_prototype.get_fqcn(parentFQCN, token_data)
endfunction
function! s:plugin_prototype.get_menu_entries(fqcn, base, is_this, is_static)
endfunction
"}}}

function! s:register_plugins() "{{{
    let list = split(globpath(&runtimepath, 'autoload/phpcomplete_extended/*.vim'), "\n")
    let s:plugins = {}
    let s:plugin_php_files = []
    for script_file in list
        try
            let script_name = fnamemodify(script_file, ":t:r")
            call s:register(script_file, phpcomplete_extended#{script_name}#define())
        catch /:E\%(117\|716\):/
        endtry
    endfor
endfunction "}}}

function! s:register(script_file, plugin) "{{{
    if empty(a:plugin)
        return
    endif
    let plugin = extend(copy(s:plugin_prototype), a:plugin)
    call plugin.init()
    let s:plugins[plugin.name] = plugin

    let script_name = fnamemodify(a:script_file, ":t:r")
    let plugin_dir = fnamemodify(a:script_file, ":p:h:h:h")
    let plugin_php_file = phpcomplete_extended#util#substitute_path_separator(
                \ plugin_dir . "/bin/".script_name.".php"
                \)
    call add(s:plugin_php_files, plugin_php_file)

endfunction "}}}

function! s:validate(plugin, key, type) "{{{
  if !has_key(a:plugin, a:key)
    throw 'phpcomplete_extended: Invalid plugin: Without key ' . string(a:key)
  elseif type(a:plugin[a:key]) != s:T[a:type]
    throw 'phpcomplete_extended: Invalid plugin: Key ' . key . ' must be ' . a:type . ', ' .
    \     'but given value is' string(a:plugin[a:key])
  endif
endfunction "}}}

function! s:set_plugin_indexes(plugin_index_file) "{{{
    let s:plugin_index = s:readIndex(a:plugin_index_file)
    for plugin_name in keys(s:plugins)
        if has_key(s:plugin_index, plugin_name)
            call s:plugins[plugin_name].set_index(deepcopy(s:plugin_index[plugin_name]))
        endif
    endfor
endfunction "}}}

function! s:get_plugin_php_files() "{{{
    let php_files = []
    for plugin in keys(s:plugins)
        let php_file = s:plugins[plugin].get_php_filename()
        call add(php_files, php_file)
    endfor
    return php_files
endfunction "}}}

function! s:get_plugin_fqcn(parentFQCN, token_data) "{{{
    let fqcn = ""
    for plugin in keys(s:plugins)
        let fqcn = s:plugins[plugin].get_fqcn(a:parentFQCN, a:token_data)
        if !empty(fqcn)
            return fqcn
        endif
    endfor
    return fqcn
endfunction "}}}

function! s:get_plugin_resolve_fqcn(fqcn) "{{{
    let fqcn = a:fqcn
    for plugin in keys(s:plugins)
        let fqcn = s:plugins[plugin].resolve_fqcn(a:fqcn)
        if !empty(fqcn)
            return fqcn
        endif
    endfor
    return fqcn
endfunction "}}}

function! s:get_plugin_menu_entries(fqcn, base, is_this, is_static) "{{{
     let menu_entries = []
    for plugin in keys(s:plugins)
        let plugin_menu_entries = s:plugins[plugin].get_menu_entries(a:fqcn, a:base, a:is_this, a:is_static)
        if !empty(plugin_menu_entries)
            let menu_entries += plugin_menu_entries
            return menu_entries
        endif
    endfor
    return menu_entries
endfunction "}}}

function! s:get_plugin_inside_qoute_menu_entries(fqcn, lastToken) "{{{
      let menu_entries = []
    for plugin in keys(s:plugins)
        let plugin_menu_entries = s:plugins[plugin].get_inside_quote_menu_entries(a:fqcn, a:lastToken)
        if !empty(plugin_menu_entries)
            let menu_entries += plugin_menu_entries
            return menu_entries
        endif
    endfor
    return menu_entries
endfunction "}}}

function! phpcomplete_extended#init_autocmd() "{{{
    augroup phpcomplete-extended
        autocmd!
        "Todo add configuration option to load later
        autocmd BufWinEnter,BufEnter  * call phpcomplete_extended#readDataForProject()
        autocmd VimLeave *     call phpcomplete_extended#saveIndexCache()
        autocmd BufWritePost *.php call phpcomplete_extended#updateIndex(1)

        autocmd CursorHold *     call phpcomplete_extended#saveIndexCache()
        autocmd CursorHold *     call phpcomplete_extended#checkUpdates()
        autocmd CursorMoved,CursorMovedI *.php call phpcomplete_extended#checkUpdates()
        autocmd CursorMovedI *.php call phpcomplete_extended#trackMenuChanges()
    augroup END
endfunction "}}}

function! phpcomplete_extended#enable() "{{{
    let s:phpcomplete_enabled = 1
    call phpcomplete_extended#init_autocmd()

    command! -nargs=0 -bar PHPCompleteExtendedDisable
          \ call phpcomplete_extended#disable()

endfunction "}}}

function! phpcomplete_extended#disable() "{{{
    let s:phpcomplete_enabled = 0
  augroup phpcomplete-extended
    autocmd!
  augroup END

  silent! delcommand PHPCompleteExtendedDisable
endfunction "}}}

let &cpo = s:save_cpo
unlet s:save_cpo

" vim: foldmethod=marker:expandtab:ts=4:sts=4:tw=78
