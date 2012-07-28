SxRequireJs
=======================
Version 0.0.2 Created by Wesley Overdijk 

Introduction
------------
This is a simple RequireJS module for [Zend Framework 2 (from here referred to as ZF2)](https://github.com/zendframework/zf2).
It allows you to use RequireJs within ZF2 by setting up modules / paths and adding applications.

Installation
------------

### Main installation
1. Clone this module into your `vendor` directory. `(usually /path/to/application/vendor)` like this:

```
cd my/project/dir/vendor
git clone git://github.com/RWOverdijk/SxRequireJs.git
```
2. Enable this module in your `application.config.php`.

### Getting it working
SxRequireJs doesn't work out of the box. It has its own `public` directory and therefore the RequireJS file will not be accessible. To get this working, there are a couple of things you can do.

1. Add an `AliasMatch` to your vhost (recommended). Example:
```AliasMatch ^/([a-zA-Z0-9]+)/(css|img|js)/(.*) /Path/To/Your/Application/module/$1/public/$2/$3```  
*Note: This does require you to add a directory to your vhost to allow access outside of your DocumentRoot.*

2. Copy the js in the public directory to your application's public directory.

Usage and configuration
------------
You can find more information, the configuration options and some usage examples in [the SxRequireJs wiki](https://github.com/RWOverdijk/SxRequireJs/wiki).

TODO
------------
* Add flush option to reset helper
* Add docs
* Format generated output (whitespaces)
* Add module to packagist