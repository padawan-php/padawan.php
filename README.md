Padawan.php for composer projects
=================================

[![Build Status](https://travis-ci.org/mkusher/padawan.php.svg?branch=master)](https://travis-ci.org/mkusher/padawan.php)

Smart php intelligent code completion server for composer projects.
It tries to be a [Jedi](https://github.com/davidhalter/jedi-vim),
but currently it's only a padawan.

This plugin was inspired by
[phpcomplete-extended by M2mdas](https://github.com/m2mdas/phpcomplete-extended)
and started as a fork with completely rewritten index generation part.
But as of now it is completely new project with different design and principles

### Demo video

Currently it have basic completion for classes and methods based on doc comments
and methods signature.

Watch this short video to see what it can already do(image is clickable)
[![ScreenShot](http://i1.ytimg.com/vi/Y54P2N1T6-I/maxresdefault.jpg)](https://www.youtube.com/watch?v=Y54P2N1T6-I)

Project
=======

Padawan.php is an http server that parses your project and gives you
completions.
Padawan.php reads autoload classmap of a composer project, parses
doc-comments and functions declarations of each class and creates index
from them. After that it autoupdates index and give you completion
as you type.

How to use
==========

- First of all you should install plugin for your editor.
- Next you have to run index generation command in your php composer
project folder.
- Last step is to start padawan's server and enjoy smart completion

Checkout editor's documentation on how to do it.

Plugins for editors
-------------------

1. [Vim](https://github.com/mkusher/padawan.vim)

You are welcome to write your own plugin, look through [this YCMD completer
example](https://gist.github.com/43bcff85d5e2f3ec3c55) and open an issue if you
have any question.

Why not the original plugin
===========================

The M2mdas's plugin is pretty good, but have some core bugs due to
self-written parser:

* It does not support files with 2 or more classes in it
* It fails on parsing RabbitMQ classes and many others
* So it has some design fails which needs global plugin redesign
* It will be really hard to add assignments parsing and
other to original plugin, not this one
* It is vim-only and is written on VimScript

So, I decided to create my own project.

Roadmap
-------

Now in progress:

* Add plugins for editors(vim, emacs and etc.)
* Add plugins support
* Add symfony2 plugin

License
-------
MIT licensed.

Acknowledgement
---------------

This plugin would not be possible without the works of
[Nikita Popov](https://github.com/nikic) for his amazing PHP-Parser,
[React team](https://github.com/reactphp) for theirs http server,
[M2mdas](https://github.com/m2mdas),
[Dave Halter](https://github.com/davidhalter)
and many others.
