"=============================================================================
" AUTHOR:  Mun Mun Das <m2mdas at gmail.com>
" FILE: util.vim
" Last Modified: September 09, 2013
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

function! phpcomplete_extended#util#getVital()
    if !exists("s:V")
        let s:V = vital#of('phpcomplete-extended')
    endif
    return s:V
endfunction

function! phpcomplete_extended#util#getFile()
    if !exists("s:File")
        let s:File = phpcomplete_extended#util#getVital().import('System.File')
    endif
    return s:File
endfunction

function! phpcomplete_extended#util#getLexer()
    if !exists("s:L")
        let s:L = phpcomplete_extended#util#getVital().import('Text.Lexer')
    endif
    return s:L
endfunction

function! phpcomplete_extended#util#getParser()
    if !exists("s:P")
        let s:P = phpcomplete_extended#util#getVital().import('Text.Parser')
    endif
    return s:P
endfunction

function! phpcomplete_extended#util#getDataList()
    if !exists("s:List")
        let s:List = phpcomplete_extended#util#getVital().import('Data.List')
    endif
    return s:List
endfunction

function! phpcomplete_extended#util#print_message(message) "{{{
    echohl Comment | echo a:message | echohl none
endfunction "}}}

function! phpcomplete_extended#util#print_error_message(message) "{{{
    echohl ErrorMsg | echo a:message | echohl none
endfunction "}}}

function! phpcomplete_extended#util#getDataString()
    if !exists("s:String")
        let s:String = phpcomplete_extended#util#getVital().import('Data.String')
    endif
    return s:String
endfunction

function! phpcomplete_extended#util#reverse(str)
  return join(reverse(split(a:str, '.\zs')), '')
endfunction

function! phpcomplete_extended#util#split_leftright(expr, pattern)
    return phpcomplete_extended#util#getDataString().split_leftright(a:expr, a:pattern)
endfunction

function! phpcomplete_extended#util#trim(str)
  return matchstr(a:str,'^\s*\zs.\{-}\ze\s*$')
endfunction

function! phpcomplete_extended#util#add_padding(list) "{{{
    let list = a:list
    let max_len = 0

    for e in a:list
        let max_len = len(e) > max_len ? len(e) : max_len
    endfor

    let format = '%-'.max_len. 's'
    return map(list, "printf(format, v:val)")
endfunction "}}}

let s:is_windows = has('win16') || has('win32') || has('win64')

function! phpcomplete_extended#util#json_decode(...)
  return call(s:Json.decode, a:000)
endfunction

function! phpcomplete_extended#util#is_windows(...)
  return s:is_windows
endfunction

if phpcomplete_extended#util#is_windows()
  function! phpcomplete_extended#util#substitute_path_separator(...)
    let V = phpcomplete_extended#util#getVital()
    return call(V.substitute_path_separator, a:000)
  endfunction
else
  function! phpcomplete_extended#util#substitute_path_separator(path)
    return a:path
  endfunction
endif

function! phpcomplete_extended#util#reverse(str)
    return join(reverse(split(a:str, '.\zs')), '')
endfunction

function! phpcomplete_extended#util#copy(...)
  return call(phpcomplete_extended#util#getFile().copy, a:000)
endfunction

function! phpcomplete_extended#util#has_vimproc(...)
  return call(phpcomplete_extended#util#getVital().has_vimproc, a:000)
endfunction

function! phpcomplete_extended#util#system(...)
  return call(phpcomplete_extended#util#getVital().system, a:000)
endfunction

function! phpcomplete_extended#util#input_yesno(message) "{{{
  let yesno = input(a:message . ' [yes/no]: ')
  while yesno !~? '^\%(y\%[es]\|n\%[o]\)$'
    redraw
    if yesno == ''
      echo 'Canceled.'
      break
    endif

    " Retry.
    call phpcomplete_extended#print_error('Invalid input.')
    let yesno = input(a:message . ' [yes/no]: ')
  endwhile

  return yesno =~? 'y\%[es]'
endfunction"}}}

function! phpcomplete_extended#util#getLines(lineNum, lineCount, direction)
    if a:direction == "back"
        let lines = getline( a:lineNum - a:lineCount, a:lineNum)
    else
        let lines = getline(a:lineNum, a:lineNum + a:lineCount)
    endif
    let joinedLine = join(map(lines, 'phpcomplete_extended#util#trim(v:val)'), "")
    return joinedLine
endfunction

let &cpo = s:save_cpo
unlet s:save_cpo
