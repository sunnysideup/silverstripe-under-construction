<?php

header('HTTP/1.1 503 Service Temporarily Unavailable');
header('Status: 503 Service Temporarily Unavailable');
header('Retry-After: 60'); //60 seconds

?>
<!DOCTYPE html>
<html>
    <style>
        body, html {
          height: 100%;
          margin: 0;
          background-color: #333;
        }

        .bgimg {
          background-image: url('/$UnderConstructionImageName');
          height: 100%;
          background-position: center;
          background-size: cover;
          position: relative;
          color: white;
          font-family: "Courier New", Courier, monospace;
          font-size: 25px;
        }

        .topleft {
          position: absolute;
          top: 0;
          left: 16px;
        }

        .bottomleft {
          position: absolute;
          bottom: 0;
          left: 16px;
        }

        .middle {
          position: absolute;
          top: 50%;
          left: 50%;
          transform: translate(-50%, -50%);
          text-align: center;
        }

        hr {
          margin: auto;
          width: 40%;
        }
    </style>

    <title>$UnderConstructionTitle</title>
<body>

<div class="bgimg">
  <div class="topleft">
  <p>$Title</p>
  </div>
  <div class="middle">
    <h1>$UnderConstructionTitle</h1>
    <hr>
    <p id="demo" style="font-size:30px"></p>
  </div>
  <div class="bottomleft">
  <p>$UnderConstructionSubTitle</p>
  </div>
</div>

<script>
    // Set the date we're counting down to - two hours in the future
    // Update the count down every 1 second
    const startNow = new Date();
    //todo: set universal start time
    var countDownDate = new Date(startNow.getTime() + ((1000 * 60 * $UnderConstructionMinutesOffline))).getTime();

    const countdownfunction = window.setInterval(
        function() {
            // Get todays date and time
            const now = new Date().getTime();

            // Find the distance between now an the count down date
            const distance = countDownDate - now;

            // Time calculations for days, hours, minutes and seconds
            // var days = Math.floor(distance / (1000 * 60 * 60 * 24));
            const hours = Math.floor((distance % (1000 * 60 * 60 * 24)) / (1000 * 60 * 60));
            const minutes = Math.floor((distance % (1000 * 60 * 60)) / (1000 * 60));
            const seconds = Math.floor((distance % (1000 * 60)) / 1000);

            // Output the result in an element with id="demo"
            document.getElementById("demo").innerHTML = hours + "h " + minutes + "m " + seconds + "s ";

            // If the count down is over, write some text
            if (distance < 0) {
                window.clearInterval(countdownfunction);
                document.getElementById("demo").innerHTML = "We are ready to go!";
            }
        },
        1000
    );
</script>

</body>
</html>
