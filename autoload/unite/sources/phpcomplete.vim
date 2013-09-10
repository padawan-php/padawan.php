"=============================================================================
" AUTHOR:  Mun Mun Das <m2mdas at gmail.com>
" FILE: phpcomplete.vim
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

if !g:loaded_unite
    finish
endif


function! unite#sources#phpcomplete#define() "{{{
    return [s:source, s:source_find_extends, s:source_find_implements, s:vendors_source]
endfunction"}}}

let s:source = {
            \ 'name' : 'phpcomplete/files',
            \ 'description' : 'File candidates read from the phpcomplete index',
            \ 'action_table' : {},
            \ 'default_kind' : 'file',
            \}

function! s:source.gather_candidates(args, context) "{{{
    if !phpcomplete_extended#is_phpcomplete_extended_project() || !exists("g:phpcomplete_index_loaded") || !g:phpcomplete_index_loaded
        call unite#print_error("Not a valid composer project")
        return []
    endif

    let is_relative_path = 1
    let files = keys(g:phpcomplete_index['file_fqcn'])
    let entries = []

    for file in files
       let dict = {
          \ 'word' : file,
          \ 'action__path' : file,
          \ 'action__line' : 0,
          \ }
        call add(entries, dict)
    endfor
    return entries

endfunction"}}}

let s:source_find_extends = {
            \ 'name' : 'phpcomplete/extends',
            \ 'description' : 'File candidate class that extends form source argument word',
            \ 'action_table' : {},
            \ 'default_kind' : 'file',
            \}
function! s:source_find_extends.gather_candidates(args, context) "{{{
    if !phpcomplete_extended#is_phpcomplete_extended_project() || !g:phpcomplete_index_loaded
        call unite#print_error("Not a valid composer project")
        return []
    endif
    let word = get(a:args, 0, expand("<cword>"))
    return s:getCandidates('extends', word)
endfunction "}}}

let s:source_find_implements = {
            \ 'name' : 'phpcomplete/implements',
            \ 'description' : 'File candidate class that implements source argument word',
            \ 'action_table' : {},
            \ 'default_kind' : 'file',
            \}

function! s:source_find_implements.gather_candidates(args, context) "{{{
    if !phpcomplete_extended#is_phpcomplete_extended_project() || !g:phpcomplete_index_loaded
        call unite#print_error("Not a valid composer project")
        return []
    endif
    let word = get(a:args, 0, expand("<cword>"))
    return s:getCandidates('implements', word)
endfunction "}}}

function! s:getCandidates(type, word) "{{{
    if !phpcomplete_extended#is_phpcomplete_extended_project() || !g:phpcomplete_index_loaded
        call unite#print_error("Not a valid composer project")
        return []
    endif
    let word_fqcn = phpcomplete_extended#getFQCNFromWord(a:word)
    if word_fqcn == ""
        return []
    endif

    let fqcns = []
    let candidates = []
    if a:type == 'implements'
        let fqcns = get(g:phpcomplete_index['implements'], word_fqcn, [])
    elseif a:type == 'extends'
        let fqcns = get(g:phpcomplete_index['extends'], word_fqcn, [])
    endif
    if !len(fqcns)
        return []
    endif
    for fqcn in fqcns
        let filelocation = phpcomplete_extended#getFileFromFQCN(fqcn)
        if filelocation == ""
            continue
        endif
       let dict = {
          \ 'word' : fqcn,
          \ 'action__path' : unite#util#substitute_path_separator(
          \     filelocation),
          \ 'action__line' : 0,
          \ }
       call add(candidates, dict)
    endfor
    return candidates
endfunction "}}}

let s:vendors_source = {
      \ 'name' : 'phpcomplete/vendors',
      \ 'description' : 'Vendor library candidates',
      \ 'action_table' : {},
      \ }

function! s:vendors_source.gather_candidates(args, context) "{{{
    if !phpcomplete_extended#is_phpcomplete_extended_project() || !g:phpcomplete_index_loaded
        call unite#print_error("Not a valid composer project")
        return []
    endif
    return s:get_vendors()
endfunction "}}}

function! s:get_vendors() "{{{
    let vendor_list = deepcopy(g:phpcomplete_index['vendor_libs'])
    let vendor_names = keys(vendor_list)
    let padded_vendor_list = phpcomplete_extended#util#add_padding(copy(vendor_names))
    let entries = []
    for vendor in vendor_names
        let dir = unite#util#substitute_path_separator(vendor_list[vendor])
        let vendor_index = index(vendor_names, vendor)
        let dict = {}
        let dict = {
          \ 'word' : dir,
          \ 'abbr' : printf('%s %s', padded_vendor_list[vendor_index], dir ),
        \   'kind' : 'directory',
          \ 'action__path' : dir,
          \ 'action__directory' : dir,
          \ }
        "let entries += dict
        call add(entries, dict)
    endfor
    return entries
endfunction "}}}

let &cpo = s:save_cpo
unlet s:save_cpo
" vim: foldmethod=marker sw=4 ts=4 sts=4 expandtab
