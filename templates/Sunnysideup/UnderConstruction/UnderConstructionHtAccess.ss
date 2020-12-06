
####################################
# Start Offline
####################################

Options +FollowSymlinks

RewriteEngine on
<% loop $UnderConstructionIpAddresses %>
RewriteCond %{REMOTE_ADDR} !=$IpEscaped
<% end_loop %>
RewriteCond %{REQUEST_URI} !^/$UnderConstructionFolderName/

RewriteRule .* /$UnderConstructionFolderName/$UnderConstructionFileName [R=302,L]


####################################
# End Offline
####################################
