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
* jQuery with prettyPhoto (for the default template)

Installation
------------

To install Robinia CMS, please use the following steps:

1. Set up a web host, copy all files, create a database
2. Fill out the necessary settings in the file `control/options.php`
3. Set the correct web root for mod_rewrite in `.htaccess`
4. Point your web browser to `<webroot>/setup.php` to set up the database
5. After a succesful setup, delete the file `setup.php`. This is important,
   otherwise a malicious user can re-run the setup, which will delete all
   contents of the website.
6. Point your browser to `<webroot>/login.html` to log in to the backend. The
   default user name is `admin` with the password `admin`. From the backend,
   you can change the password, add a regular user and start adding
   contents.
7. For the image lightboxes to work, you have to install jQuery as
   `js/jquery.js` and prettyPhoto as `js/jquery.prettyPhoto.js`, plus
   accompanying css and image files.

To simplify things for a personal home page, you can use the following steps:

1. Create a subfolder
2. In this subfolder, create a personal user account
3. Set the root ID for the personal user account to the subfolder ID
4. In this subfolder, create a page with the link name 'index'
5. For the subfolder, the user account and the index page, change the
   permissions as following:
   - Add the group 'everyone' with 'view' permission
   - Add the personal user account with all permissions except 'modify
     permissions'
   - Remove the permissions for the admin account

Now you can log in with your personal user account and update the website
as you want. Any pages you create will inherit the same permissions as set
above (readable for the world, editable with your personal user account),
and you will not be bothered by the rights management system.

Customization
-------------

To customize the design of your website, there are two different
possibilities.

The quickest way is to simply edit the template in `template/default`.

A perhaps cleaner way is to copy the default template and modify the copy.
To use the new template for your website, edit `control/options.php`
correspondingly.
