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
\$distanceInSeconds = (\$ts + ($SiteConfig.UnderConstructionMinutesOffline * 60)) - \$now;
\$distanceInMilliSeconds = \$distanceInSeconds * 1000;
?>
<!DOCTYPE html>
<html>
    <style>
        body, html {
          height: 100%;
          margin: 0;
          background-color: #333;
          font-size: 25px;
        }

        .bgimg {
          background-image: url('$UnderConstructionImageName');
          height: 100%;
          background-position: center;
          background-size: cover;
          position: relative;
          color: white;
          font-family: "Courier New", Courier, monospace;
        }

        .topleft {
          position: absolute;
          top: 1rem;
          left: 1rem;
          right: 1rem;
          text-align: center;
        }

        .bottomleft {
          position: absolute;
          bottom: 1rem;
          left: 1rem;
          right: 1rem;
          text-align: center;
        }

        .middle {
          position: absolute;
          top: 50%;
          left: 50%;
          transform: translate(-50%, -50%);
          text-align: center;
        }

        h1 {
            font-size: 48px;
        }
        #timer {
            font-size:30px
        }
        hr {
          margin: auto;
          width: 40%;
        }
    </style>

    <title>$SiteConfig.Title - $SiteConfig.UnderConstructionTitle</title>
<body>

<div class="bgimg">
  <div class="topleft">
    <p>$SiteConfig.Title</p>
  </div>
  <div class="middle">
    <h1>$SiteConfig.UnderConstructionTitle</h1>
    <hr>
    <p id="timer"></p>
  </div>
  <div class="bottomleft">
    <p>$SiteConfig.UnderConstructionSubTitle</p>
  </div>
</div>

<script>

var distance = <?php echo \$distanceInMilliSeconds ?>

const countdownfunction = window.setInterval(
    function () {
        distance = distance - 1000;
        // Find the distance between now an the count down date

        // Time calculations for days, hours, minutes and seconds
        // var days = Math.floor(distance / (1000 * 60 * 60 * 24));
        const hours = Math.floor((distance % (1000 * 60 * 60 * 24)) / (1000 * 60 * 60))
        const minutes = Math.floor((distance % (1000 * 60 * 60)) / (1000 * 60))
        const seconds = Math.floor((distance % (1000 * 60)) / 1000)
        if (seconds === 0) {
            document.location.href= '/';
        } else {
            // If the count down is over, write some text
            if (distance < 0) {
                window.clearInterval(countdownfunction)
                document.getElementById('timer').innerHTML = 'We are ready to go!'

            } else {
                // Output the result in an element with id='timer'
                document.getElementById('timer').innerHTML = hours + 'h ' + minutes + 'm ' + seconds + 's '
            }
        }
    },
    1000
)
</script>

</body>
</html>
