Go to the siteconfig, open the `offline tab`, choose your settings and save.

You can put your site in offline and in online mode from here.

It adds the following to your `.htacces` file in public folder:

```.htaccess
Options +FollowSymlinks

RewriteEngine on

RewriteCond %{REMOTE_ADDR} !^123.45.67.89 
RewriteCond %{REQUEST_URI} !^\/offline/

RewriteRule .* /offline/offline.php [R=302,L]
```
Where 123.45.67.89 is your ip address ..
