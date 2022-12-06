<?php

header('HTTP/1.1 503 Service Temporarily Unavailable');
header('Status: 503 Service Temporarily Unavailable');
header('Retry-After: 90'); //90 seconds

\$now = time();
\$ts = 0;
\$fileLocation = '$UnderConstructionFileName' . '.txt';
if(file_exists(\$fileLocation)) {
  \$ts = intval(file_get_contents(\$fileLocation)) - 0;
}
if(\$ts > \$now) {
  \$ts = 0;
}
if(\$ts === 0) {
  \$ts = \$now;
  file_put_contents(\$fileLocation, \$ts);
}
\$distanceInSeconds = (\$ts + ($SiteConfig.UnderConstructionMinutesOfflineAlwaysInt * 60)) - \$now;
\$distanceInMilliSeconds = \$distanceInSeconds * 1000;
?><!DOCTYPE html>
