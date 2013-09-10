"=============================================================================
" AUTHOR:  Mun Mun Das <m2mdas at gmail.com>
" FILE: php.vim
" Last Modified: September 10, 2013
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

let s:source = {
      \ 'name' : 'php',
      \ 'kind' : 'manual',
      \ 'mark' : '[P]',
      \ 'rank' : 6,
      \ 'hooks' : {},
      \ 'filetypes' : { 'php' : 1},
      \}

let s:List = vital#of('neocomplete').import('Data.List')

function! s:source.get_complete_position(context) "{{{
    return s:get_complete_position(a:context)
endfunction "}}}

function! s:source.gather_candidates(context) "{{{
    if !phpcomplete_extended#is_phpcomplete_extended_project() || &ft != 'php'
        return []
    endif
    return s:gather_candidates(a:context)
endfunction"}}}

function! s:get_complete_position(context) "{{{
    return phpcomplete_extended#CompletePHP(1, "")
endfunction "}}}

function! s:gather_candidates(context) "{{{
    return phpcomplete_extended#CompletePHP(0, b:completeContext.base)
endfunction "}}}

function! neocomplete#sources#php#define() "{{{
  return s:source
endfunction"}}}

function! s:set_neocomplete_sources() "{{{
    if !phpcomplete_extended#is_phpcomplete_extended_project() || !g:loaded_neocomplete
        return
    endif

    let avail_sources = keys(neocomplete#available_sources())
    if index(avail_sources, "omni") != -1
        call remove(avail_sources, index(avail_sources, 'omni'))
    endif

    let b:neocomplete_sources = avail_sources
endfunction "}}}

augroup neocomplete_php
    autocmd!
    autocmd BufEnter *.php :call <SID>set_neocomplete_sources()
augroup END
let &cpo = s:save_cpo
unlet s:save_cpo

" vim: foldmethod=marker:expandtab:ts=4:sts=4:tw=78 
