Style
=====
Very much like the way the Script directory is handled, the Style directory contains a shared collection of CSS files and fonts.

Style sheets are minified together at runtime, just like JavaScript is.

There is also native support for [Sass CSS](http://sass-lang.com/docs.html) files. Any .scss files in this directory will be pre-processed and converted into standard .css before being optionally minified together and copied into the www directory. 

Included here as a git submodule is GTUI.css, from https://github.com/g105b/GTUI. To clone PHP.Gt with all submodules, use this command:

```shell
git clone --recursive http://github.com/g105b/PHP.Gt
```

... or if you already have the repo, and want to grab the submodules:

```shell
cd PHP.Gt
git submodule update --init
```