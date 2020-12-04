Go to the siteconfig, choose your settings and save.

Then set up a .htaccess file to redirect all traffic.

Add to `.htacces` file in public folder:

```.htaccess
Options +FollowSymlinks

RewriteEngine on

RewriteCond %{REMOTE_ADDR} !=123.45.67.89
RewriteCond %{REQUEST_URI} !^\/offline\.php|\/offline\.jpg

RewriteRule .* /offline.php [R=302,L]
```
make sure to replace 123.45.67.89 with your own ip address ..

Add to `robots.txt`:

```.txt
User-agent: *
Disallow: /offline.php
```
