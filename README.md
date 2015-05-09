Padawan.php for composer projects
=================================

[![Build Status](https://travis-ci.org/mkusher/padawan.php.svg?branch=master)](https://travis-ci.org/mkusher/padawan.php)

Smart php intelligent code completion server for composer projects.
It tries to be a [Jedi](https://github.com/davidhalter/jedi-vim),
but currently it's only a padawan.

This project was inspired by
[phpcomplete-extended by M2mdas](https://github.com/m2mdas/phpcomplete-extended)
and started as a fork with a completely rewritten index generation part.
But as of now it is a completely new project with different design principles

### Demo video

Currently it has basic completion for classes and methods based on doc comments
and methods signature.

Watch this short video to see what it can already do(image is clickable)
[![ScreenShot](http://i1.ytimg.com/vi/Y54P2N1T6-I/maxresdefault.jpg)](https://www.youtube.com/watch?v=Y54P2N1T6-I)

Project
=======

Padawan.php is an http server that parses your project and gives you
completions.
Padawan.php reads autoload classmap of a composer project, parses
doc-comments and function declarations of each class and creates an index
from them. After that it autoupdates the index and gives you completion
as you type.

How to use
==========

- Install plugin for your editor.
- Run index generation command in your php composer
project folder.
- Start padawan's server
- Enjoy smart completion

Check out how to do this in the plugin documentation for specific editor below.

Plugins for editors
-------------------

1. [Vim](https://github.com/mkusher/padawan.vim)

If you wish to write your own plugin, this YCMD completer example may serve
as a source of inspiration. You are welcome to open an issue if
you have any questions.

Why not the original plugin
===========================

M2mdas's plugin is pretty good, but has some core bugs due to
self-written parser:

* It does not support files with 2 or more classes in it
* It fails on parsing RabbitMQ classes and many others
* So it has some design faults and needs a global plugin redesign
* It is ill-suited for adding assignment parsing
* It is vim-only and is written in VimScript

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

Acknowledgements
----------------

This plugin would not have been possible without the works of
[Nikita Popov](https://github.com/nikic) on his amazing PHP-Parser,
[React team](https://github.com/reactphp) on their http server,
[M2mdas](https://github.com/m2mdas),
[Dave Halter](https://github.com/davidhalter)
and many others.
