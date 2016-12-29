Padawan.php smart php intelligent code completion for php projects
==================================================================

[![Join the chat at https://gitter.im/mkusher/padawan.php](https://badges.gitter.im/Join%20Chat.svg)](https://gitter.im/mkusher/padawan.php?utm_source=badge&utm_medium=badge&utm_campaign=pr-badge&utm_content=badge)

[![Build Status](https://travis-ci.org/mkusher/padawan.php.svg?branch=master)](https://travis-ci.org/mkusher/padawan.php)
[![Total Downloads](https://poser.pugx.org/mkusher/padawan/downloads)](https://packagist.org/packages/mkusher/padawan)
[![Latest Stable Version](https://poser.pugx.org/mkusher/padawan/v/stable)](https://packagist.org/packages/mkusher/padawan)
[![Latest Unstable Version](https://poser.pugx.org/mkusher/padawan/v/unstable)](https://packagist.org/packages/mkusher/padawan)
[![Scrutinizer Code Quality](https://scrutinizer-ci.com/g/mkusher/padawan.php/badges/quality-score.png?b=master)](https://scrutinizer-ci.com/g/mkusher/padawan.php/?branch=master)
[![License](https://poser.pugx.org/mkusher/padawan/license)](https://packagist.org/packages/mkusher/padawan)

***Looking for maintainers! Please join gitter channel for discussion***

Padawan.php is an http server that parses your project and gives you
completions.
Padawan.php looks recursively for all php files of a composer project, parses
doc-comments and function declarations of each class and creates an index
from them. After that it autoupdates the index and gives you completion
as you type.

Padawan.php can be extended by various plugins, which will bring some
extra completion, framework integrations or so.
[See this paragraph to learn more](https://github.com/mkusher/padawan.php#pluginsextensions-for-padawanphp)

It tries to be a [Jedi](https://github.com/davidhalter/jedi),
but currently it's only a padawan :)

Plugins for editors
-------------------

1. [Vim](https://github.com/mkusher/padawan.vim)
2. [Neovim](https://github.com/pbogut/deoplete-padawan)
3. Sublime Text: [Padawan for ST3](https://github.com/mkusher/padawan.sublime) and [SublimePHPCompanion](https://github.com/erichard/SublimePHPCompanion)

If you wish to write your own plugin, vim plugin example may serve
as a source of inspiration. Look at
[wiki page](https://github.com/mkusher/padawan.php/wiki/Editors'-plugins) for
some documentation.
You are welcome to open an issue if you have any questions.

### Demo videos

Watch this short videos to see what it can already do(image is clickable)
[![ScreenShot](http://i1.ytimg.com/vi/qpLJD24DYcU/maxresdefault.jpg)](https://www.youtube.com/watch?v=qpLJD24DYcU)
[![ScreenShot](http://i1.ytimg.com/vi/Y54P2N1T6-I/maxresdefault.jpg)](https://www.youtube.com/watch?v=Y54P2N1T6-I)

How to use
==========

- Install padawan.php through `composer`:
```bash
$ composer global require mkusher/padawan
```
- Add [composer global bin to your $PATH](https://getcomposer.org/doc/03-cli.md#global):
```bash
PATH=$PATH:$HOME/.composer/vendor/bin
```
- Install plugin for your editor.
- Run index generation command in your php composer
project folder:
```bash
padawan generate
```
- Start padawan's server
```bash
padawan-server
```
- Enjoy smart completion

Check out how to do this in the plugin documentation for specific editor above.

Plugins(extensions) for padawan.php
-----------------------------------

Padawan.php can be extended by plugins, there are:
- Symfony2 plugin
- PHP-DI plugin

Look at [full plugins list](https://github.com/mkusher/padawan.php/wiki/Plugins-list)

Why not the original plugin
---------------------------

This project was inspired by
[phpcomplete-extended by M2mdas](https://github.com/m2mdas/phpcomplete-extended)
and started as a fork with a completely rewritten index generation part.
But as of now it is a completely new project with different design principles

M2mdas's plugin is pretty good, but has some core bugs due to
self-written parser:

* It does not support files with 2 or more classes in it
* It fails on parsing RabbitMQ classes and many others
* So it has some design faults and needs a global plugin redesign
* It is ill-suited for adding assignment parsing
* It is vim-only and is written in VimScript

So, I decided to create my own project.

Note
----

Install `igbinary` PHP extension to get optimized index file size and load speed.

Roadmap
-------

Now in progress:

* Implement `go to definition`, `go to assingment`, `show documentation`
* Add plugins for editors(emacs, atom and etc.)
* Extend type guessing(process classes' contructors, class doc-comment, foreach loops)
* Implement index updating

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
