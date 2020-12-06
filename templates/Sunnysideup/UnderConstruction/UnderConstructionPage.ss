<?php

header('HTTP/1.1 503 Service Temporarily Unavailable');
header('Status: 503 Service Temporarily Unavailable');
header('Retry-After: 60'); //60 seconds
\$now = time();
\$ts = 0;
\$fileLocation = 'offline-timestamp.txt')
if(file_exists(\$fileLocation) {
  \$ts = intval(file_get_contents(timestamp.txt));
}
if(\$ts > \$now) {
  \$ts = 0;
}
if(\$ts === 0) {
  \$ts = \$now;
  file_put_contents(\$fileLocation, \$ts);
}
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
          top: 1rem;
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
// Set the date we're counting down to - two hours in the future
// Update the count down every 1 second
const startNow = new Date()
// todo: set universal start time
var countDownDate = new Date(<?php echo \$ts ?> + ((1000 * 60 * $SiteConfig.UnderConstructionMinutesOffline))).getTime();

const countdownfunction = window.setInterval(
    function () {
        // Get todays date and time
        const now = new Date().getTime()

        // Find the distance between now an the count down date
        const distance = countDownDate - now

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
