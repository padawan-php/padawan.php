<?php
use Evenement\EventEmitterInterface;
use Peridot\Plugin\Prophecy\ProphecyPlugin;

return function(EventEmitterInterface $emitter) {
    new ProphecyPlugin($emitter);
};


times in msec
 clock   self+sourced   self:  sourced script
 clock   elapsed:              other lines

000.010  000.010: --- VIM STARTING ---
000.078  000.068: Allocated generic buffers
000.150  000.072: locale set
000.165  000.015: GUI prepared
000.169  000.004: clipboard setup
000.174  000.005: window checked
000.537  000.363: inits 1
000.542  000.005: parsing arguments
000.542  000.000: expanding arguments
000.554  000.012: shell init
000.744  000.190: Termcap init
000.778  000.034: inits 2
000.887  000.109: init highlight
001.057  000.092  000.092: sourcing /usr/share/vim/vimfiles/archlinux.vim
001.086  000.164  000.072: sourcing /etc/vimrc
021.152  000.094  000.094: sourcing /usr/share/vim/vimfiles/ftdetect/conkyrc.vim
021.249  000.067  000.067: sourcing /usr/share/vim/vimfiles/ftdetect/dockerfile.vim
021.386  020.121  019.960: sourcing /usr/share/vim/vim74/filetype.vim
021.465  000.039  000.039: sourcing /usr/share/vim/vim74/ftplugin.vim
021.535  000.034  000.034: sourcing /usr/share/vim/vim74/indent.vim
023.116  001.408  001.408: sourcing /home/mkusher/.vim/autoload/plug.vim
033.054  000.645  000.645: sourcing /usr/share/vim/vim74/ftoff.vim
034.433  000.013  000.013: sourcing /home/mkusher/.vim/plugged/vim-less/ftdetect/less.vim
034.684  000.025  000.025: sourcing /home/mkusher/.vim/plugged/vim-markdown/ftdetect/markdown.vim
034.927  000.023  000.023: sourcing /home/mkusher/.vim/plugged/tern_for_vim/ftdetect/tern.vim
035.357  000.037  000.037: sourcing /home/mkusher/.vim/plugged/vim-javascript/ftdetect/javascript.vim
035.591  000.015  000.015: sourcing /home/mkusher/.vim/plugged/vim-javascript-syntax/ftdetect/javascript.vim
035.864  000.040  000.040: sourcing /home/mkusher/.vim/plugged/jasmine.vim/ftdetect/jasmine.vim
038.578  000.032  000.032: sourcing /home/mkusher/.vim/plugged/vim-behat/filetype.vim
058.508  000.063  000.063: sourcing /home/mkusher/.vim/plugged/ultisnips/ftdetect/UltiSnips.vim
058.584  000.048  000.048: sourcing /home/mkusher/.vim/plugged/ultisnips/ftdetect/snippets.vim
058.785  000.080  000.080: sourcing /home/mkusher/.vim/plugged/vim-stylus/ftdetect/stylus.vim
058.917  000.064  000.064: sourcing /home/mkusher/.vim/plugged/vim-node/ftdetect/node.vim
059.007  000.041  000.041: sourcing /home/mkusher/.vim/plugged/typescript-vim/ftdetect/typescript.vim
059.084  000.037  000.037: sourcing /home/mkusher/.vim/plugged/tsuquyomi/ftdetect/typescript.vim
059.230  000.056  000.056: sourcing /home/mkusher/.vim/plugged/rust.vim/ftdetect/rust.vim
059.432  000.107  000.107: sourcing /usr/share/vim/vimfiles/ftdetect/conkyrc.vim
059.543  000.082  000.082: sourcing /usr/share/vim/vimfiles/ftdetect/dockerfile.vim
059.707  000.032  000.032: sourcing /home/mkusher/.vim/plugged/vim-jsx/after/jsx-config.vim
059.835  000.206  000.174: sourcing /home/mkusher/.vim/plugged/vim-jsx/after/ftdetect/javascript.vim
059.976  021.305  020.521: sourcing /usr/share/vim/vim74/filetype.vim
060.336  000.011  000.011: sourcing /usr/share/vim/vim74/ftplugin.vim
060.714  000.012  000.012: sourcing /usr/share/vim/vim74/indent.vim
061.719  000.237  000.237: sourcing /usr/share/vim/vim74/syntax/syncolor.vim
061.888  000.719  000.482: sourcing /usr/share/vim/vim74/syntax/synload.vim
061.934  001.125  000.406: sourcing /usr/share/vim/vim74/syntax/syntax.vim
062.886  000.177  000.177: sourcing /usr/share/vim/vim74/syntax/nosyntax.vim
063.633  000.140  000.140: sourcing /usr/share/vim/vim74/syntax/syncolor.vim
063.774  000.587  000.447: sourcing /usr/share/vim/vim74/syntax/synload.vim
063.807  001.134  000.370: sourcing /usr/share/vim/vim74/syntax/syntax.vim
064.398  000.182  000.182: sourcing /usr/share/vim/vim74/syntax/syncolor.vim
065.124  000.194  000.194: sourcing /usr/share/vim/vim74/syntax/syncolor.vim
065.760  000.170  000.170: sourcing /usr/share/vim/vim74/syntax/syncolor.vim
113.286  048.771  048.407: sourcing /home/mkusher/.vim/plugged/gruvbox/colors/gruvbox.vim
114.066  112.945  017.973: sourcing ~/.vim/vimrc
114.074  000.078: sourcing vimrc file(s)
114.243  000.038  000.038: sourcing /home/mkusher/.vim/plugged/vim-misc/plugin/xolox/misc.vim
114.457  000.121  000.121: sourcing /home/mkusher/.vim/plugged/vimproc.vim/plugin/vimproc.vim
114.602  000.066  000.066: sourcing /home/mkusher/.vim/plugged/vim-plugin-AnsiEsc/plugin/AnsiEscPlugin.vim
115.052  000.428  000.428: sourcing /home/mkusher/.vim/plugged/vim-plugin-AnsiEsc/plugin/cecutil.vim
115.218  000.093  000.093: sourcing /home/mkusher/.vim/plugged/vim-bufonly/plugin/BufOnly.vim
118.010  002.619  002.619: sourcing /home/mkusher/.vim/plugged/vim-webdevicons/plugin/webdevicons.vim
118.567  000.180  000.180: sourcing /home/mkusher/.vim/plugged/vim-airline/autoload/airline.vim
118.863  000.078  000.078: sourcing /home/mkusher/.vim/plugged/vim-airline/autoload/airline/init.vim
119.328  000.126  000.126: sourcing /home/mkusher/.vim/plugged/vim-airline/autoload/airline/parts.vim
119.927  001.829  001.445: sourcing /home/mkusher/.vim/plugged/vim-airline/plugin/airline.vim
120.470  000.472  000.472: sourcing /home/mkusher/.vim/plugged/YouCompleteMe/plugin/youcompleteme.vim
120.697  000.148  000.148: sourcing /home/mkusher/.vim/plugged/vim-startify/plugin/startify.vim
120.860  000.066  000.066: sourcing /home/mkusher/.vim/plugged/lh-vim-lib/plugin/let.vim
121.039  000.158  000.158: sourcing /home/mkusher/.vim/plugged/lh-vim-lib/plugin/lhvl.vim
121.794  000.393  000.393: sourcing /home/mkusher/.vim/plugged/lh-vim-lib/autoload/lh/menu.vim
122.165  001.094  000.701: sourcing /home/mkusher/.vim/plugged/lh-vim-lib/plugin/ui-functions.vim
122.248  000.059  000.059: sourcing /home/mkusher/.vim/plugged/lh-vim-lib/plugin/words_tools.vim
122.908  000.436  000.436: sourcing /home/mkusher/.vim/plugged/lh-vim-lib/autoload/lh/path.vim
123.087  000.027  000.027: sourcing /home/mkusher/.vim/plugged/lh-vim-lib/plugin/let.vim
123.269  000.069  000.069: sourcing /home/mkusher/.vim/plugged/lh-vim-lib/autoload/lh/let.vim
124.374  000.301  000.301: sourcing /home/mkusher/.vim/plugged/lh-vim-lib/autoload/lh/list.vim
124.828  002.509  001.676: sourcing /home/mkusher/.vim/plugged/local_vimrc/plugin/local_vimrc.vim
125.627  000.113  000.113: sourcing /home/mkusher/.vim/plugged/nerdtree/autoload/nerdtree.vim
126.922  000.492  000.492: sourcing /home/mkusher/.vim/plugged/nerdtree/lib/nerdtree/path.vim
127.194  000.122  000.122: sourcing /home/mkusher/.vim/plugged/nerdtree/lib/nerdtree/menu_controller.vim
127.420  000.089  000.089: sourcing /home/mkusher/.vim/plugged/nerdtree/lib/nerdtree/menu_item.vim
127.659  000.108  000.108: sourcing /home/mkusher/.vim/plugged/nerdtree/lib/nerdtree/key_map.vim
128.012  000.220  000.220: sourcing /home/mkusher/.vim/plugged/nerdtree/lib/nerdtree/bookmark.vim
128.401  000.255  000.255: sourcing /home/mkusher/.vim/plugged/nerdtree/lib/nerdtree/tree_file_node.vim
128.934  000.399  000.399: sourcing /home/mkusher/.vim/plugged/nerdtree/lib/nerdtree/tree_dir_node.vim
129.260  000.202  000.202: sourcing /home/mkusher/.vim/plugged/nerdtree/lib/nerdtree/opener.vim
129.629  000.245  000.245: sourcing /home/mkusher/.vim/plugged/nerdtree/lib/nerdtree/creator.vim
129.800  000.046  000.046: sourcing /home/mkusher/.vim/plugged/nerdtree/lib/nerdtree/flag_set.vim
130.018  000.096  000.096: sourcing /home/mkusher/.vim/plugged/nerdtree/lib/nerdtree/nerdtree.vim
130.458  000.325  000.325: sourcing /home/mkusher/.vim/plugged/nerdtree/lib/nerdtree/ui.vim
130.698  000.044  000.044: sourcing /home/mkusher/.vim/plugged/nerdtree/lib/nerdtree/event.vim
131.030  000.139  000.139: sourcing /home/mkusher/.vim/plugged/nerdtree/lib/nerdtree/notifier.vim
131.870  000.616  000.616: sourcing /home/mkusher/.vim/plugged/nerdtree/autoload/nerdtree/ui_glue.vim
149.208  000.297  000.297: sourcing /home/mkusher/.vim/plugged/vim-webdevicons/nerdtree_plugin/webdevicons.vim
149.459  000.107  000.107: sourcing /home/mkusher/.vim/plugged/nerdtree/nerdtree_plugin/exec_menuitem.vim
149.906  000.421  000.421: sourcing /home/mkusher/.vim/plugged/nerdtree/nerdtree_plugin/fs_menu.vim
152.198  002.215  002.215: sourcing /home/mkusher/.vim/plugged/nerdtree-git-plugin/nerdtree_plugin/git_status.vim
152.760  027.868  021.317: sourcing /home/mkusher/.vim/plugged/nerdtree/plugin/NERD_tree.vim
153.783  000.526  000.526: sourcing /home/mkusher/.vim/plugged/unite.vim/autoload/unite/custom.vim
153.907  000.980  000.454: sourcing /home/mkusher/.vim/plugged/unite.vim/plugin/unite/bookmark.vim
154.163  000.231  000.231: sourcing /home/mkusher/.vim/plugged/unite.vim/plugin/unite/buffer.vim
154.248  000.062  000.062: sourcing /home/mkusher/.vim/plugged/unite.vim/plugin/unite/history_yank.vim
154.418  000.150  000.150: sourcing /home/mkusher/.vim/plugged/unite.vim/plugin/unite/window.vim
154.829  000.389  000.389: sourcing /home/mkusher/.vim/plugged/unite.vim/plugin/unite.vim
155.100  000.200  000.200: sourcing /home/mkusher/.vim/plugged/neomru.vim/plugin/neomru.vim
155.353  000.014  000.014: sourcing /home/mkusher/.vim/plugged/syntastic/plugin/syntastic/autoloclist.vim
155.385  000.011  000.011: sourcing /home/mkusher/.vim/plugged/syntastic/plugin/syntastic/balloons.vim
155.413  000.010  000.010: sourcing /home/mkusher/.vim/plugged/syntastic/plugin/syntastic/checker.vim
155.441  000.011  000.011: sourcing /home/mkusher/.vim/plugged/syntastic/plugin/syntastic/cursor.vim
155.471  000.012  000.012: sourcing /home/mkusher/.vim/plugged/syntastic/plugin/syntastic/highlighting.vim
155.498  000.010  000.010: sourcing /home/mkusher/.vim/plugged/syntastic/plugin/syntastic/loclist.vim
155.526  000.010  000.010: sourcing /home/mkusher/.vim/plugged/syntastic/plugin/syntastic/modemap.vim
155.554  000.011  000.011: sourcing /home/mkusher/.vim/plugged/syntastic/plugin/syntastic/notifiers.vim
155.583  000.012  000.012: sourcing /home/mkusher/.vim/plugged/syntastic/plugin/syntastic/registry.vim
155.612  000.011  000.011: sourcing /home/mkusher/.vim/plugged/syntastic/plugin/syntastic/signs.vim
156.706  000.735  000.735: sourcing /home/mkusher/.vim/plugged/syntastic/autoload/syntastic/util.vim
168.257  000.124  000.124: sourcing /home/mkusher/.vim/plugged/syntastic/plugin/syntastic/autoloclist.vim
168.407  000.121  000.121: sourcing /home/mkusher/.vim/plugged/syntastic/plugin/syntastic/balloons.vim
168.996  000.568  000.568: sourcing /home/mkusher/.vim/plugged/syntastic/plugin/syntastic/checker.vim
169.324  000.290  000.290: sourcing /home/mkusher/.vim/plugged/syntastic/plugin/syntastic/cursor.vim
169.618  000.259  000.259: sourcing /home/mkusher/.vim/plugged/syntastic/plugin/syntastic/highlighting.vim
170.415  000.765  000.765: sourcing /home/mkusher/.vim/plugged/syntastic/plugin/syntastic/loclist.vim
170.632  000.195  000.195: sourcing /home/mkusher/.vim/plugged/syntastic/plugin/syntastic/modemap.vim
170.723  000.070  000.070: sourcing /home/mkusher/.vim/plugged/syntastic/plugin/syntastic/notifiers.vim
171.102  000.358  000.358: sourcing /home/mkusher/.vim/plugged/syntastic/plugin/syntastic/registry.vim
171.233  000.107  000.107: sourcing /home/mkusher/.vim/plugged/syntastic/plugin/syntastic/signs.vim
172.624  016.995  013.403: sourcing /home/mkusher/.vim/plugged/syntastic/plugin/syntastic.vim
173.033  000.325  000.325: sourcing /home/mkusher/.vim/plugged/TextObjectify/plugin/textobjectify.vim
174.204  000.423  000.423: sourcing /home/mkusher/.vim/plugged/delimitMate/autoload/delimitMate.vim
177.300  004.199  003.776: sourcing /home/mkusher/.vim/plugged/delimitMate/plugin/delimitMate.vim
178.063  000.664  000.664: sourcing /home/mkusher/.vim/plugged/vim-surround/plugin/surround.vim
178.460  000.314  000.314: sourcing /home/mkusher/.vim/plugged/vim-move/plugin/move.vim
183.684  005.146  005.146: sourcing /home/mkusher/.vim/plugged/nerdcommenter/plugin/NERD_commenter.vim
184.152  000.084  000.084: sourcing /home/mkusher/.vim/plugged/ultisnips/autoload/UltiSnips/map_keys.vim
184.262  000.498  000.414: sourcing /home/mkusher/.vim/plugged/ultisnips/plugin/UltiSnips.vim
184.388  000.061  000.061: sourcing /home/mkusher/.vim/plugged/vim-snippets/plugin/vimsnippets.vim
188.865  004.412  004.412: sourcing /home/mkusher/.vim/plugged/vim-fugitive/plugin/fugitive.vim
189.168  000.229  000.229: sourcing /home/mkusher/.vim/plugged/vim-extradite/plugin/extradite.vim
189.284  000.049  000.049: sourcing /home/mkusher/.vim/plugged/vim-merginal/plugin/merginal.vim
190.792  001.426  001.426: sourcing /home/mkusher/.vim/plugged/gitv/plugin/gitv.vim
191.593  000.092  000.092: sourcing /home/mkusher/.vim/plugged/vim-gitgutter/autoload/gitgutter/highlight.vim
192.606  001.721  001.629: sourcing /home/mkusher/.vim/plugged/vim-gitgutter/plugin/gitgutter.vim
192.734  000.052  000.052: sourcing /home/mkusher/.vim/plugged/gist-vim/plugin/gist.vim
193.099  000.272  000.272: sourcing /home/mkusher/.vim/plugged/vim-node/plugin/node.vim
193.358  000.183  000.183: sourcing /home/mkusher/.vim/plugged/tsuquyomi/plugin/tsuquyomi.vim
193.558  000.093  000.093: sourcing /home/mkusher/.vim/plugged/vim-php-refactoring/plugin/phprefactor.vim
193.685  000.066  000.066: sourcing /home/mkusher/.vim/plugged/padawan.vim/plugin/padawan.vim
194.212  000.114  000.114: sourcing /home/mkusher/.vim/plugged/python-mode/autoload/pymode.vim
194.504  000.014  000.014: sourcing /home/mkusher/.vim/plugged/vim-behat/filetype.vim
194.602  000.010  000.010: sourcing /usr/share/vim/vim74/filetype.vim
194.952  000.010  000.010: sourcing /usr/share/vim/vim74/ftplugin.vim
196.048  000.012  000.012: sourcing /home/mkusher/.vim/plugged/vim-behat/filetype.vim
196.134  000.010  000.010: sourcing /usr/share/vim/vim74/filetype.vim
196.451  000.010  000.010: sourcing /usr/share/vim/vim74/ftplugin.vim
196.593  002.830  002.650: sourcing /home/mkusher/.vim/plugged/python-mode/plugin/pymode.vim
196.895  000.011  000.011: sourcing /home/mkusher/.vim/plugged/vim-behat/filetype.vim
196.980  000.010  000.010: sourcing /usr/share/vim/vim74/filetype.vim
197.331  000.010  000.010: sourcing /usr/share/vim/vim74/ftplugin.vim
197.445  000.784  000.753: sourcing /home/mkusher/.vim/plugged/jedi-vim/plugin/jedi.vim
197.551  000.048  000.048: sourcing /home/mkusher/.vim/plugged/rust.vim/plugin/rust.vim
198.214  000.610  000.610: sourcing /home/mkusher/.vim/plugged/vim-cargo/plugin/cargo.vim
198.467  000.191  000.191: sourcing /home/mkusher/.vim/plugged/racer/plugin/racer.vim
198.546  000.018  000.018: sourcing /home/mkusher/.vim/plugged/neco-ghc/plugin/necoghc.vim
198.629  000.017  000.017: sourcing /home/mkusher/.vim/plugged/ghcmod-vim/plugin/ghcmod.vim
198.763  000.012  000.012: sourcing /home/mkusher/.vim/plugged/vim-misc/autoload/xolox/misc.vim
198.859  000.166  000.154: sourcing /home/mkusher/.vim/plugged/vim-lua-ftplugin/plugin/lua-ftplugin.vim
199.110  000.077  000.077: sourcing /usr/share/vim/vim74/plugin/getscriptPlugin.vim
199.430  000.289  000.289: sourcing /usr/share/vim/vim74/plugin/gzip.vim
199.724  000.273  000.273: sourcing /usr/share/vim/vim74/plugin/logiPat.vim
200.032  000.281  000.281: sourcing /usr/share/vim/vim74/plugin/matchparen.vim
200.710  000.655  000.655: sourcing /usr/share/vim/vim74/plugin/netrwPlugin.vim
200.794  000.043  000.043: sourcing /usr/share/vim/vim74/plugin/rrhelper.vim
200.854  000.034  000.034: sourcing /usr/share/vim/vim74/plugin/spellfile.vim
201.079  000.198  000.198: sourcing /usr/share/vim/vim74/plugin/tarPlugin.vim
201.222  000.098  000.098: sourcing /usr/share/vim/vim74/plugin/tohtml.vim
201.436  000.187  000.187: sourcing /usr/share/vim/vim74/plugin/vimballPlugin.vim
201.689  000.203  000.203: sourcing /usr/share/vim/vim74/plugin/zipPlugin.vim
202.163  000.378  000.378: sourcing /home/mkusher/.vim/plugged/indentLine/after/plugin/indentLine.vim
202.422  000.167  000.167: sourcing /home/mkusher/.vim/plugged/ultisnips/after/plugin/UltiSnips_after.vim
202.531  004.513: loading plugins
202.550  000.019: inits 3
202.774  000.224: reading viminfo
202.775  000.001: setup clipboard
202.786  000.011: setting raw mode
202.809  000.023: start termcap
202.873  000.064: clearing screen
203.518  000.221  000.221: sourcing /home/mkusher/.vim/plugged/vim-airline/autoload/airline/extensions.vim
203.742  000.049  000.049: sourcing /home/mkusher/.vim/plugged/vim-airline/autoload/airline/extensions/quickfix.vim
203.954  000.033  000.033: sourcing /home/mkusher/.vim/plugged/vim-airline/autoload/airline/extensions/unite.vim
204.167  000.041  000.041: sourcing /home/mkusher/.vim/plugged/vim-airline/autoload/airline/extensions/netrw.vim
204.474  000.085  000.085: sourcing /home/mkusher/.vim/plugged/vim-airline/autoload/airline/extensions/hunks.vim
204.820  000.147  000.147: sourcing /home/mkusher/.vim/plugged/vim-airline/autoload/airline/extensions/branch.vim
205.139  000.034  000.034: sourcing /home/mkusher/.vim/plugged/vim-airline/autoload/airline/extensions/syntastic.vim
205.449  000.109  000.109: sourcing /home/mkusher/.vim/plugged/vim-airline/autoload/airline/extensions/whitespace.vim
205.848  000.110  000.110: sourcing /home/mkusher/.vim/plugged/vim-airline/autoload/airline/extensions/tabline.vim
206.082  000.063  000.063: sourcing /home/mkusher/.vim/plugged/vim-airline/autoload/airline/extensions/tabline/autoshow.vim
206.399  000.087  000.087: sourcing /home/mkusher/.vim/plugged/vim-airline/autoload/airline/extensions/tabline/tabs.vim
206.823  000.175  000.175: sourcing /home/mkusher/.vim/plugged/vim-airline/autoload/airline/extensions/tabline/buffers.vim
208.814  000.089  000.089: sourcing /home/mkusher/.vim/plugged/vim-airline/autoload/airline/section.vim
209.203  000.157  000.157: sourcing /home/mkusher/.vim/plugged/vim-airline/autoload/airline/highlighter.vim
211.931  000.072  000.072: sourcing /home/mkusher/.vim/plugged/vim-airline/autoload/airline/themes.vim
212.249  000.549  000.477: sourcing /home/mkusher/.vim/plugged/vim-airline/autoload/airline/themes/luna.vim
215.873  000.080  000.080: sourcing /home/mkusher/.vim/plugged/vim-airline/autoload/airline/util.vim
216.509  000.118  000.118: sourcing /home/mkusher/.vim/plugged/vim-airline/autoload/airline/builder.vim
216.944  000.079  000.079: sourcing /home/mkusher/.vim/plugged/vim-airline/autoload/airline/extensions/default.vim
230.127  025.028: opening buffers
230.892  000.333  000.333: sourcing /home/mkusher/.vim/plugged/syntastic/autoload/syntastic/log.vim
236.222  000.158  000.158: sourcing /home/mkusher/.vim/plugged/vim-gitgutter/autoload/gitgutter.vim
236.636  000.164  000.164: sourcing /home/mkusher/.vim/plugged/vim-gitgutter/autoload/gitgutter/utility.vim
237.021  000.099  000.099: sourcing /home/mkusher/.vim/plugged/vim-gitgutter/autoload/gitgutter/hunk.vim
237.060  006.179: BufEnter autocommands
237.062  000.002: editing files in windows
242.283  000.038  000.038: sourcing /home/mkusher/.vim/plugged/vim-airline/autoload/airline/deprecation.vim
252.373  009.777  009.777: sourcing /home/mkusher/.vim/plugged/YouCompleteMe/autoload/youcompleteme.vim
418.233  001.224  001.224: sourcing /home/mkusher/.vim/plugged/vim-startify/autoload/startify.vim
446.414  000.450  000.450: sourcing /home/mkusher/.vim/plugged/ultisnips/autoload/UltiSnips.vim
458.381  000.522  000.522: sourcing /home/mkusher/.vim/plugged/vim-startify/syntax/startify.vim
475.693  226.620: VimEnter autocommands
475.703  000.010: before starting main loop
492.677  000.108  000.108: sourcing /home/mkusher/.vim/plugged/vim-airline/autoload/airline/extensions/tabline/buflist.vim
493.197  000.040  000.040: sourcing /home/mkusher/.vim/plugged/vim-webdevicons/autoload/airline/extensions/tabline/formatters/webdevicons.vim
493.568  000.115  000.115: sourcing /home/mkusher/.vim/plugged/vim-airline/autoload/airline/extensions/tabline/formatters/default.vim
507.110  031.144: first screen update
507.114  000.004: --- VIM STARTED ---


times in msec
 clock   self+sourced   self:  sourced script
 clock   elapsed:              other lines

000.008  000.008: --- VIM STARTING ---
000.078  000.070: Allocated generic buffers
000.151  000.073: locale set
000.167  000.016: GUI prepared
000.170  000.003: clipboard setup
000.175  000.005: window checked
000.524  000.349: inits 1
000.529  000.005: parsing arguments
000.529  000.000: expanding arguments
000.541  000.012: shell init
000.735  000.194: Termcap init
000.779  000.044: inits 2
000.881  000.102: init highlight
001.049  000.092  000.092: sourcing /usr/share/vim/vimfiles/archlinux.vim
001.077  000.161  000.069: sourcing /etc/vimrc
020.693  000.079  000.079: sourcing /usr/share/vim/vimfiles/ftdetect/conkyrc.vim
020.790  000.071  000.071: sourcing /usr/share/vim/vimfiles/ftdetect/dockerfile.vim
020.909  019.669  019.519: sourcing /usr/share/vim/vim74/filetype.vim
020.990  000.040  000.040: sourcing /usr/share/vim/vim74/ftplugin.vim
021.060  000.034  000.034: sourcing /usr/share/vim/vim74/indent.vim
022.726  001.478  001.478: sourcing /home/mkusher/.vim/autoload/plug.vim
032.161  000.675  000.675: sourcing /usr/share/vim/vim74/ftoff.vim
033.572  000.012  000.012: sourcing /home/mkusher/.vim/plugged/vim-less/ftdetect/less.vim
033.817  000.025  000.025: sourcing /home/mkusher/.vim/plugged/vim-markdown/ftdetect/markdown.vim
034.055  000.021  000.021: sourcing /home/mkusher/.vim/plugged/tern_for_vim/ftdetect/tern.vim
034.450  000.036  000.036: sourcing /home/mkusher/.vim/plugged/vim-javascript/ftdetect/javascript.vim
034.676  000.013  000.013: sourcing /home/mkusher/.vim/plugged/vim-javascript-syntax/ftdetect/javascript.vim
034.943  000.039  000.039: sourcing /home/mkusher/.vim/plugged/jasmine.vim/ftdetect/jasmine.vim
037.588  000.032  000.032: sourcing /home/mkusher/.vim/plugged/vim-behat/filetype.vim
056.977  000.082  000.082: sourcing /home/mkusher/.vim/plugged/ultisnips/ftdetect/UltiSnips.vim
057.053  000.047  000.047: sourcing /home/mkusher/.vim/plugged/ultisnips/ftdetect/snippets.vim
057.246  000.080  000.080: sourcing /home/mkusher/.vim/plugged/vim-stylus/ftdetect/stylus.vim
057.354  000.051  000.051: sourcing /home/mkusher/.vim/plugged/vim-node/ftdetect/node.vim
057.476  000.077  000.077: sourcing /home/mkusher/.vim/plugged/typescript-vim/ftdetect/typescript.vim
057.554  000.038  000.038: sourcing /home/mkusher/.vim/plugged/tsuquyomi/ftdetect/typescript.vim
057.683  000.042  000.042: sourcing /home/mkusher/.vim/plugged/rust.vim/ftdetect/rust.vim
057.846  000.077  000.077: sourcing /usr/share/vim/vimfiles/ftdetect/conkyrc.vim
057.932  000.068  000.068: sourcing /usr/share/vim/vimfiles/ftdetect/dockerfile.vim
058.096  000.044  000.044: sourcing /home/mkusher/.vim/plugged/vim-jsx/after/jsx-config.vim
058.243  000.235  000.191: sourcing /home/mkusher/.vim/plugged/vim-jsx/after/ftdetect/javascript.vim
058.400  020.729  019.932: sourcing /usr/share/vim/vim74/filetype.vim
058.740  000.009  000.009: sourcing /usr/share/vim/vim74/ftplugin.vim
059.060  000.009  000.009: sourcing /usr/share/vim/vim74/indent.vim
059.979  000.220  000.220: sourcing /usr/share/vim/vim74/syntax/syncolor.vim
060.209  000.757  000.537: sourcing /usr/share/vim/vim74/syntax/synload.vim
060.263  001.125  000.368: sourcing /usr/share/vim/vim74/syntax/syntax.vim
061.632  000.271  000.271: sourcing /usr/share/vim/vim74/syntax/nosyntax.vim
062.849  000.243  000.243: sourcing /usr/share/vim/vim74/syntax/syncolor.vim
063.067  000.965  000.722: sourcing /usr/share/vim/vim74/syntax/synload.vim
063.129  001.825  000.589: sourcing /usr/share/vim/vim74/syntax/syntax.vim
064.048  000.285  000.285: sourcing /usr/share/vim/vim74/syntax/syncolor.vim
065.181  000.323  000.323: sourcing /usr/share/vim/vim74/syntax/syncolor.vim
066.115  000.301  000.301: sourcing /usr/share/vim/vim74/syntax/syncolor.vim
110.146  045.898  045.274: sourcing /home/mkusher/.vim/plugged/gruvbox/colors/gruvbox.vim
110.932  109.819  017.865: sourcing ~/.vim/vimrc
110.941  000.080: sourcing vimrc file(s)
111.113  000.039  000.039: sourcing /home/mkusher/.vim/plugged/vim-misc/plugin/xolox/misc.vim
111.326  000.123  000.123: sourcing /home/mkusher/.vim/plugged/vimproc.vim/plugin/vimproc.vim
111.528  000.072  000.072: sourcing /home/mkusher/.vim/plugged/vim-plugin-AnsiEsc/plugin/AnsiEscPlugin.vim
112.060  000.507  000.507: sourcing /home/mkusher/.vim/plugged/vim-plugin-AnsiEsc/plugin/cecutil.vim
112.222  000.093  000.093: sourcing /home/mkusher/.vim/plugged/vim-bufonly/plugin/BufOnly.vim
115.182  002.776  002.776: sourcing /home/mkusher/.vim/plugged/vim-webdevicons/plugin/webdevicons.vim
115.708  000.241  000.241: sourcing /home/mkusher/.vim/plugged/vim-airline/autoload/airline.vim
116.015  000.078  000.078: sourcing /home/mkusher/.vim/plugged/vim-airline/autoload/airline/init.vim
116.488  000.140  000.140: sourcing /home/mkusher/.vim/plugged/vim-airline/autoload/airline/parts.vim
117.175  001.900  001.441: sourcing /home/mkusher/.vim/plugged/vim-airline/plugin/airline.vim
117.713  000.465  000.465: sourcing /home/mkusher/.vim/plugged/YouCompleteMe/plugin/youcompleteme.vim
117.959  000.178  000.178: sourcing /home/mkusher/.vim/plugged/vim-startify/plugin/startify.vim
118.120  000.065  000.065: sourcing /home/mkusher/.vim/plugged/lh-vim-lib/plugin/let.vim
118.223  000.082  000.082: sourcing /home/mkusher/.vim/plugged/lh-vim-lib/plugin/lhvl.vim
118.903  000.389  000.389: sourcing /home/mkusher/.vim/plugged/lh-vim-lib/autoload/lh/menu.vim
119.320  001.077  000.688: sourcing /home/mkusher/.vim/plugged/lh-vim-lib/plugin/ui-functions.vim
119.410  000.064  000.064: sourcing /home/mkusher/.vim/plugged/lh-vim-lib/plugin/words_tools.vim
120.050  000.395  000.395: sourcing /home/mkusher/.vim/plugged/lh-vim-lib/autoload/lh/path.vim
120.227  000.027  000.027: sourcing /home/mkusher/.vim/plugged/lh-vim-lib/plugin/let.vim
120.408  000.068  000.068: sourcing /home/mkusher/.vim/plugged/lh-vim-lib/autoload/lh/let.vim
121.441  000.300  000.300: sourcing /home/mkusher/.vim/plugged/lh-vim-lib/autoload/lh/list.vim
121.981  002.486  001.696: sourcing /home/mkusher/.vim/plugged/local_vimrc/plugin/local_vimrc.vim
122.799  000.114  000.114: sourcing /home/mkusher/.vim/plugged/nerdtree/autoload/nerdtree.vim
124.010  000.439  000.439: sourcing /home/mkusher/.vim/plugged/nerdtree/lib/nerdtree/path.vim
124.339  000.143  000.143: sourcing /home/mkusher/.vim/plugged/nerdtree/lib/nerdtree/menu_controller.vim
124.558  000.089  000.089: sourcing /home/mkusher/.vim/plugged/nerdtree/lib/nerdtree/menu_item.vim
124.793  000.106  000.106: sourcing /home/mkusher/.vim/plugged/nerdtree/lib/nerdtree/key_map.vim
125.127  000.202  000.202: sourcing /home/mkusher/.vim/plugged/nerdtree/lib/nerdtree/bookmark.vim
125.643  000.375  000.375: sourcing /home/mkusher/.vim/plugged/nerdtree/lib/nerdtree/tree_file_node.vim
126.168  000.371  000.371: sourcing /home/mkusher/.vim/plugged/nerdtree/lib/nerdtree/tree_dir_node.vim
126.492  000.199  000.199: sourcing /home/mkusher/.vim/plugged/nerdtree/lib/nerdtree/opener.vim
126.926  000.295  000.295: sourcing /home/mkusher/.vim/plugged/nerdtree/lib/nerdtree/creator.vim
127.098  000.044  000.044: sourcing /home/mkusher/.vim/plugged/nerdtree/lib/nerdtree/flag_set.vim
127.312  000.092  000.092: sourcing /home/mkusher/.vim/plugged/nerdtree/lib/nerdtree/nerdtree.vim
127.749  000.314  000.314: sourcing /home/mkusher/.vim/plugged/nerdtree/lib/nerdtree/ui.vim
127.890  000.019  000.019: sourcing /home/mkusher/.vim/plugged/nerdtree/lib/nerdtree/event.vim
128.045  000.035  000.035: sourcing /home/mkusher/.vim/plugged/nerdtree/lib/nerdtree/notifier.vim
128.591  000.411  000.411: sourcing /home/mkusher/.vim/plugged/nerdtree/autoload/nerdtree/ui_glue.vim
145.376  000.325  000.325: sourcing /home/mkusher/.vim/plugged/vim-webdevicons/nerdtree_plugin/webdevicons.vim
145.636  000.105  000.105: sourcing /home/mkusher/.vim/plugged/nerdtree/nerdtree_plugin/exec_menuitem.vim
146.273  000.610  000.610: sourcing /home/mkusher/.vim/plugged/nerdtree/nerdtree_plugin/fs_menu.vim
148.231  001.842  001.842: sourcing /home/mkusher/.vim/plugged/nerdtree-git-plugin/nerdtree_plugin/git_status.vim
148.791  026.740  020.610: sourcing /home/mkusher/.vim/plugged/nerdtree/plugin/NERD_tree.vim
149.725  000.502  000.502: sourcing /home/mkusher/.vim/plugged/unite.vim/autoload/unite/custom.vim
149.843  000.885  000.383: sourcing /home/mkusher/.vim/plugged/unite.vim/plugin/unite/bookmark.vim
150.099  000.233  000.233: sourcing /home/mkusher/.vim/plugged/unite.vim/plugin/unite/buffer.vim
150.183  000.061  000.061: sourcing /home/mkusher/.vim/plugged/unite.vim/plugin/unite/history_yank.vim
150.380  000.178  000.178: sourcing /home/mkusher/.vim/plugged/unite.vim/plugin/unite/window.vim
150.709  000.307  000.307: sourcing /home/mkusher/.vim/plugged/unite.vim/plugin/unite.vim
151.147  000.304  000.304: sourcing /home/mkusher/.vim/plugged/neomru.vim/plugin/neomru.vim
151.379  000.015  000.015: sourcing /home/mkusher/.vim/plugged/syntastic/plugin/syntastic/autoloclist.vim
151.410  000.011  000.011: sourcing /home/mkusher/.vim/plugged/syntastic/plugin/syntastic/balloons.vim
151.438  000.010  000.010: sourcing /home/mkusher/.vim/plugged/syntastic/plugin/syntastic/checker.vim
151.465  000.010  000.010: sourcing /home/mkusher/.vim/plugged/syntastic/plugin/syntastic/cursor.vim
151.493  000.011  000.011: sourcing /home/mkusher/.vim/plugged/syntastic/plugin/syntastic/highlighting.vim
151.520  000.010  000.010: sourcing /home/mkusher/.vim/plugged/syntastic/plugin/syntastic/loclist.vim
151.547  000.010  000.010: sourcing /home/mkusher/.vim/plugged/syntastic/plugin/syntastic/modemap.vim
151.574  000.010  000.010: sourcing /home/mkusher/.vim/plugged/syntastic/plugin/syntastic/notifiers.vim
151.602  000.012  000.012: sourcing /home/mkusher/.vim/plugged/syntastic/plugin/syntastic/registry.vim
151.631  000.012  000.012: sourcing /home/mkusher/.vim/plugged/syntastic/plugin/syntastic/signs.vim
152.820  000.923  000.923: sourcing /home/mkusher/.vim/plugged/syntastic/autoload/syntastic/util.vim
164.524  000.135  000.135: sourcing /home/mkusher/.vim/plugged/syntastic/plugin/syntastic/autoloclist.vim
164.682  000.128  000.128: sourcing /home/mkusher/.vim/plugged/syntastic/plugin/syntastic/balloons.vim
165.170  000.465  000.465: sourcing /home/mkusher/.vim/plugged/syntastic/plugin/syntastic/checker.vim
165.424  000.219  000.219: sourcing /home/mkusher/.vim/plugged/syntastic/plugin/syntastic/cursor.vim
165.674  000.216  000.216: sourcing /home/mkusher/.vim/plugged/syntastic/plugin/syntastic/highlighting.vim
166.415  000.716  000.716: sourcing /home/mkusher/.vim/plugged/syntastic/plugin/syntastic/loclist.vim
166.646  000.207  000.207: sourcing /home/mkusher/.vim/plugged/syntastic/plugin/syntastic/modemap.vim
166.739  000.072  000.072: sourcing /home/mkusher/.vim/plugged/syntastic/plugin/syntastic/notifiers.vim
167.042  000.284  000.284: sourcing /home/mkusher/.vim/plugged/syntastic/plugin/syntastic/registry.vim
167.166  000.103  000.103: sourcing /home/mkusher/.vim/plugged/syntastic/plugin/syntastic/signs.vim
168.575  016.928  013.460: sourcing /home/mkusher/.vim/plugged/syntastic/plugin/syntastic.vim
168.986  000.326  000.326: sourcing /home/mkusher/.vim/plugged/TextObjectify/plugin/textobjectify.vim
170.201  000.401  000.401: sourcing /home/mkusher/.vim/plugged/delimitMate/autoload/delimitMate.vim
173.247  004.194  003.793: sourcing /home/mkusher/.vim/plugged/delimitMate/plugin/delimitMate.vim
174.039  000.687  000.687: sourcing /home/mkusher/.vim/plugged/vim-surround/plugin/surround.vim
174.409  000.284  000.284: sourcing /home/mkusher/.vim/plugged/vim-move/plugin/move.vim
179.652  005.159  005.159: sourcing /home/mkusher/.vim/plugged/nerdcommenter/plugin/NERD_commenter.vim
180.151  000.083  000.083: sourcing /home/mkusher/.vim/plugged/ultisnips/autoload/UltiSnips/map_keys.vim
180.318  000.559  000.476: sourcing /home/mkusher/.vim/plugged/ultisnips/plugin/UltiSnips.vim
180.446  000.045  000.045: sourcing /home/mkusher/.vim/plugged/vim-snippets/plugin/vimsnippets.vim
185.484  004.978  004.978: sourcing /home/mkusher/.vim/plugged/vim-fugitive/plugin/fugitive.vim
185.789  000.229  000.229: sourcing /home/mkusher/.vim/plugged/vim-extradite/plugin/extradite.vim
185.906  000.049  000.049: sourcing /home/mkusher/.vim/plugged/vim-merginal/plugin/merginal.vim
186.818  000.832  000.832: sourcing /home/mkusher/.vim/plugged/gitv/plugin/gitv.vim
187.643  000.091  000.091: sourcing /home/mkusher/.vim/plugged/vim-gitgutter/autoload/gitgutter/highlight.vim
188.540  001.646  001.555: sourcing /home/mkusher/.vim/plugged/vim-gitgutter/plugin/gitgutter.vim
188.668  000.052  000.052: sourcing /home/mkusher/.vim/plugged/gist-vim/plugin/gist.vim
189.030  000.270  000.270: sourcing /home/mkusher/.vim/plugged/vim-node/plugin/node.vim
189.280  000.179  000.179: sourcing /home/mkusher/.vim/plugged/tsuquyomi/plugin/tsuquyomi.vim
189.437  000.085  000.085: sourcing /home/mkusher/.vim/plugged/vim-php-refactoring/plugin/phprefactor.vim
189.552  000.060  000.060: sourcing /home/mkusher/.vim/plugged/padawan.vim/plugin/padawan.vim
190.076  000.105  000.105: sourcing /home/mkusher/.vim/plugged/python-mode/autoload/pymode.vim
190.340  000.012  000.012: sourcing /home/mkusher/.vim/plugged/vim-behat/filetype.vim
190.430  000.010  000.010: sourcing /usr/share/vim/vim74/filetype.vim
190.780  000.011  000.011: sourcing /usr/share/vim/vim74/ftplugin.vim
191.799  000.011  000.011: sourcing /home/mkusher/.vim/plugged/vim-behat/filetype.vim
191.884  000.010  000.010: sourcing /usr/share/vim/vim74/filetype.vim
192.211  000.022  000.022: sourcing /usr/share/vim/vim74/ftplugin.vim
192.375  002.752  002.571: sourcing /home/mkusher/.vim/plugged/python-mode/plugin/pymode.vim
192.740  000.013  000.013: sourcing /home/mkusher/.vim/plugged/vim-behat/filetype.vim
192.825  000.010  000.010: sourcing /usr/share/vim/vim74/filetype.vim
193.138  000.009  000.009: sourcing /usr/share/vim/vim74/ftplugin.vim
193.248  000.765  000.733: sourcing /home/mkusher/.vim/plugged/jedi-vim/plugin/jedi.vim
193.352  000.046  000.046: sourcing /home/mkusher/.vim/plugged/rust.vim/plugin/rust.vim
194.027  000.621  000.621: sourcing /home/mkusher/.vim/plugged/vim-cargo/plugin/cargo.vim
194.268  000.173  000.173: sourcing /home/mkusher/.vim/plugged/racer/plugin/racer.vim
194.348  000.019  000.019: sourcing /home/mkusher/.vim/plugged/neco-ghc/plugin/necoghc.vim
194.418  000.016  000.016: sourcing /home/mkusher/.vim/plugged/ghcmod-vim/plugin/ghcmod.vim
194.591  000.024  000.024: sourcing /home/mkusher/.vim/plugged/vim-misc/autoload/xolox/misc.vim
194.699  000.218  000.194: sourcing /home/mkusher/.vim/plugged/vim-lua-ftplugin/plugin/lua-ftplugin.vim
195.003  000.114  000.114: sourcing /usr/share/vim/vim74/plugin/getscriptPlugin.vim
195.255  000.231  000.231: sourcing /usr/share/vim/vim74/plugin/gzip.vim
195.527  000.251  000.251: sourcing /usr/share/vim/vim74/plugin/logiPat.vim
195.764  000.217  000.217: sourcing /usr/share/vim/vim74/plugin/matchparen.vim
196.488  000.702  000.702: sourcing /usr/share/vim/vim74/plugin/netrwPlugin.vim
196.573  000.043  000.043: sourcing /usr/share/vim/vim74/plugin/rrhelper.vim
196.633  000.034  000.034: sourcing /usr/share/vim/vim74/plugin/spellfile.vim
196.840  000.180  000.180: sourcing /usr/share/vim/vim74/plugin/tarPlugin.vim
196.968  000.099  000.099: sourcing /usr/share/vim/vim74/plugin/tohtml.vim
197.163  000.170  000.170: sourcing /usr/share/vim/vim74/plugin/vimballPlugin.vim
197.431  000.233  000.233: sourcing /usr/share/vim/vim74/plugin/zipPlugin.vim
197.869  000.324  000.324: sourcing /home/mkusher/.vim/plugged/indentLine/after/plugin/indentLine.vim
198.083  000.130  000.130: sourcing /home/mkusher/.vim/plugged/ultisnips/after/plugin/UltiSnips_after.vim
198.170  004.583: loading plugins
198.188  000.018: inits 3
198.411  000.223: reading viminfo
198.412  000.001: setup clipboard
198.423  000.011: setting raw mode
198.446  000.023: start termcap
198.511  000.065: clearing screen
199.215  000.218  000.218: sourcing /home/mkusher/.vim/plugged/vim-airline/autoload/airline/extensions.vim
199.440  000.049  000.049: sourcing /home/mkusher/.vim/plugged/vim-airline/autoload/airline/extensions/quickfix.vim
199.651  000.033  000.033: sourcing /home/mkusher/.vim/plugged/vim-airline/autoload/airline/extensions/unite.vim
199.883  000.046  000.046: sourcing /home/mkusher/.vim/plugged/vim-airline/autoload/airline/extensions/netrw.vim
200.210  000.084  000.084: sourcing /home/mkusher/.vim/plugged/vim-airline/autoload/airline/extensions/hunks.vim
200.527  000.121  000.121: sourcing /home/mkusher/.vim/plugged/vim-airline/autoload/airline/extensions/branch.vim
200.832  000.031  000.031: sourcing /home/mkusher/.vim/plugged/vim-airline/autoload/airline/extensions/syntastic.vim
201.103  000.103  000.103: sourcing /home/mkusher/.vim/plugged/vim-airline/autoload/airline/extensions/whitespace.vim
201.449  000.105  000.105: sourcing /home/mkusher/.vim/plugged/vim-airline/autoload/airline/extensions/tabline.vim
201.732  000.099  000.099: sourcing /home/mkusher/.vim/plugged/vim-airline/autoload/airline/extensions/tabline/autoshow.vim
202.063  000.084  000.084: sourcing /home/mkusher/.vim/plugged/vim-airline/autoload/airline/extensions/tabline/tabs.vim
202.437  000.175  000.175: sourcing /home/mkusher/.vim/plugged/vim-airline/autoload/airline/extensions/tabline/buffers.vim
204.780  000.079  000.079: sourcing /home/mkusher/.vim/plugged/vim-airline/autoload/airline/section.vim
205.173  000.163  000.163: sourcing /home/mkusher/.vim/plugged/vim-airline/autoload/airline/highlighter.vim
207.720  000.081  000.081: sourcing /home/mkusher/.vim/plugged/vim-airline/autoload/airline/themes.vim
208.052  000.601  000.520: sourcing /home/mkusher/.vim/plugged/vim-airline/autoload/airline/themes/luna.vim
211.606  000.081  000.081: sourcing /home/mkusher/.vim/plugged/vim-airline/autoload/airline/util.vim
212.232  000.112  000.112: sourcing /home/mkusher/.vim/plugged/vim-airline/autoload/airline/builder.vim
212.705  000.094  000.094: sourcing /home/mkusher/.vim/plugged/vim-airline/autoload/airline/extensions/default.vim
225.706  024.917: opening buffers
226.221  000.204  000.204: sourcing /home/mkusher/.vim/plugged/syntastic/autoload/syntastic/log.vim
231.235  000.161  000.161: sourcing /home/mkusher/.vim/plugged/vim-gitgutter/autoload/gitgutter.vim
231.647  000.162  000.162: sourcing /home/mkusher/.vim/plugged/vim-gitgutter/autoload/gitgutter/utility.vim
232.026  000.097  000.097: sourcing /home/mkusher/.vim/plugged/vim-gitgutter/autoload/gitgutter/hunk.vim
232.065  005.735: BufEnter autocommands
232.066  000.001: editing files in windows
237.318  000.038  000.038: sourcing /home/mkusher/.vim/plugged/vim-airline/autoload/airline/deprecation.vim
247.079  009.414  009.414: sourcing /home/mkusher/.vim/plugged/YouCompleteMe/autoload/youcompleteme.vim
393.725  000.671  000.671: sourcing /home/mkusher/.vim/plugged/vim-startify/autoload/startify.vim
401.357  000.276  000.276: sourcing /home/mkusher/.vim/plugged/ultisnips/autoload/UltiSnips.vim
402.089  000.226  000.226: sourcing /home/mkusher/.vim/plugged/vim-startify/syntax/startify.vim
411.506  168.815: VimEnter autocommands
411.510  000.004: before starting main loop
423.250  000.068  000.068: sourcing /home/mkusher/.vim/plugged/vim-airline/autoload/airline/extensions/tabline/buflist.vim
423.533  000.025  000.025: sourcing /home/mkusher/.vim/plugged/vim-webdevicons/autoload/airline/extensions/tabline/formatters/webdevicons.vim
423.776  000.066  000.066: sourcing /home/mkusher/.vim/plugged/vim-airline/autoload/airline/extensions/tabline/formatters/default.vim
431.201  019.532: first screen update
431.204  000.003: --- VIM STARTED ---
