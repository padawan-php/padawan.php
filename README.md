Padawan.php for composer projects
=================================

Smart php intelligent code completion plugin for composer projects.
It tries to be a [Jedi](https://github.com/davidhalter/jedi-vim),
but currently it's only a padawan.

This plugin was inspired by
[phpcomplete-extended by M2mdas](https://github.com/m2mdas/phpcomplete-extended)
and started as a fork with completely rewritten index generation part.
But as of now it is completely new project with different design and principles

Currently it's under development and does not support some of the original
plugin features.

Project
=======

Padawan.php is an http server that parses your project and gives you
completions.
Padawan.php reads autoload classmap of a composer project, parses
doc-comments and functions declarations of each class and creates index
from them. After that it autoupdated index and give you completion
as you send requests to http.

* See documentation of current word, be it class name, method or property. It is
  context aware.
* Go to definition of a symbol. Also context aware.
* Automatically add use statement of current completed word. Also added plugin
  command of this action.


Why not original plugin
-----------------------

The M2mdas's plugin is pretty good, but have some core bugs due to
self-written parser:

* It does not support files with 2 or more classes in it
* It fails on parsing RabbitMQ classes and many others
* So it has some design fails which needs global plugin redesign
* It will be really hard to add assignments parsing and
other to original plugin, not this one
* It is vim-only and is written on VimScript

So, I decided to write my own plugin.

Roadmap
-------

As of now this plugin sucks, but I'm working hard on it and these features are
now in progress:

* Fix bugs with symbol type detection
* Fix plugin user interface
* Add doc-comment parsing
* Add class inheritance indexing
* Add plugins support
* Add symfony2 plugin

License
-------
MIT licensed.

Acknowledgement
---------------

This plugin would not be possible without the works of
[M2mdas](https://github.com/m2mdas),
[Dave Halter](https://github.com/davidhalter)
and many others.
