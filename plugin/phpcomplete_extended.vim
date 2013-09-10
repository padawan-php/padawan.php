"=============================================================================
" AUTHOR:  Mun Mun Das <m2mdas at gmail.com>
" FILE: phpcomplete_extended.vim
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

if !phpcomplete_extended#util#has_vimproc()
    echoerr "Vimproc is a requirement for phpcomplete-extended plugin"
endif

if !executable("php")
    echoerr "php executable not found. Put the directory containing php executable in your $PATH environment variable"
endif

let g:phpcomplete_index_composer_command =
      \ get(g:, 'phpcomplete_index_composer_command', 'php composer.phar')

if exists('g:loaded_phpcomplete_extended')
  finish
endif

let g:loaded_phpcomplete_extended = 1

let g:phpcomplete_extended_tags_cache_dir =
      \ get(g:, 'phpcomplete_extended_tags_cache_dir', expand('~/.phpcomplete_extended'))

let g:phpcomplete_extended_cache_disable =
      \ get(g:, 'phpcomplete_extended_cache_disable', 0)

let g:phpcomplete_extended_load_cache_at_buf_enter =
      \ get(g:, 'phpcomplete_extended_load_cache_at_buf_enter', 1)

let g:phpcomplete_extended_auto_add_use =
      \ get(g:, 'phpcomplete_extended_auto_add_use', 1)


let g:phpcomplete_extended_root_dir = fnamemodify(expand("<sfile>"), ':p:h:h')

let g:phpcomplete_extended_use_default_mapping =
      \ get(g:, 'phpcomplete_extended_use_default_mapping', 1)


command! -nargs=0 -bar PHPCompleteExtendedReload
      \ call phpcomplete_extended#reload()

command! -nargs=0 -bar PHPCompleteExtendedClearIndexCache
      \ call phpcomplete_extended#clearIndexCache()

command! -nargs=? -bar PHPCompleteExtendedGenerateIndex
      \ call phpcomplete_extended#generateIndex("<args>")

command! -nargs=0 -bar PHPCompleteExtendedUpdateIndex
      \ call phpcomplete_extended#updateIndex(0)

command! -nargs=0 -bar PHPCompleteExtendedCheckUpdate
      \ call phpcomplete_extended#checkUpdates()

command! -nargs=0 -bar PHPCompleteExtendedEnable
      \ call phpcomplete_extended#enable()

command! -nargs=0 -bar PHPCompleteExtendedDisable
      \ call phpcomplete_extended#disable()



nnoremap <silent> <Plug>(phpcomplete-extended-goto) :<C-u>call phpcomplete_extended#gotoSymbolORDoc('goto')<CR>
nnoremap <silent> <Plug>(phpcomplete-extended-doc) :<C-u>call phpcomplete_extended#gotoSymbolORDoc('doc')<CR>
nnoremap <silent> <Plug>(phpcomplete-extended-add-use) :<C-u>call phpcomplete_extended#addUse(expand('<cword>'), "")<CR>


if g:phpcomplete_extended_use_default_mapping
    silent! nmap <silent> <unique> K <Plug>(phpcomplete-extended-doc)
    silent! nmap <silent> <unique> <C-]> <Plug>(phpcomplete-extended-goto)
    silent! nmap <silent> <unique> <Leader><Leader>u <Plug>(phpcomplete-extended-add-use)
endif

call phpcomplete_extended#enable()

let &cpo = s:save_cpo
unlet s:save_cpo

" vim: foldmethod=marker:expandtab:ts=4:sts=4:tw=78
