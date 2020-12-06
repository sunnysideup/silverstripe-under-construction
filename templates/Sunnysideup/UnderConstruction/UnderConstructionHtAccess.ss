
####################################
# Start Offline
####################################

Options +FollowSymlinks

RewriteEngine on

RewriteCond %{REQUEST_URI} !^/$UnderConstructionFolderName/

<% loop $UnderConstructionIpAddresses %>
RewriteCond %{REMOTE_ADDR} !^$IpEscaped
<% end_loop %>

RewriteRule .* /$UnderConstructionFolderName/$UnderConstructionFileName [R=302,L]


####################################
# End Offline
####################################
