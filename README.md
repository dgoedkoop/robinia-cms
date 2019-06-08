Robinia CMS
===========

Robinia CMS is a very simple content management system. It comes with
a user management system which also extends to any assets. By default it
is not configured to be used with a fast CDN for assets.

Prerequisites
-------------

Robinia CMS has the following external dependencies:

* PHP
* MariaDB or MySQL
* mod_rewrite or equivalent

Installation
------------

To install Robinia CMS, please use the following steps:

1. Set up a web host, copy all files, create a database
2. Fill out the necessary settings in the file `control/options.php`
3. Set the correct web root for mod_rewrite in `.htaccess`
4. From the command line, run `php setup.php`
5. Point your browser to `<webroot>/login.html` to log in to the backend. The
   default user name is `admin` with the password `admin`. From the backend,
   you can change the password, add a regular user and start adding
   contents.

Customization
-------------

To customize the design of your website, there are two different
possibilities.

The quickest way is to simply edit the template in `template/default`.

A perhaps cleaner way is to copy the default template and modify the copy.
To use the new template for your website, edit `control/options.php`
correspondingly.
