"let s:save_cpo = &cpo
"set cpo&vim

function! unite#kinds#phpcomplete_extended#define() "{{{
  "return s:kind
endfunction"}}}

let s:kind = {
      \ 'name' : 'phpcomplete_extended',
      \ 'default_action' : 'insert',
      \ 'action_table': {},
      \}

let s:kind.action_table.insert_namespace = {
      \ 'description' : 'insert namespace',
      \ }
" TBD

"let &cpo = s:save_cpo
"unlet s:save_cpo

" vim: foldmethod=marker
