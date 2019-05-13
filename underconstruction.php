<?php

$backgroundImage = '';
$minutesDown = 120;
$title = 'We are updating our website';
$subtitle = 'Please come back soon.';

?>
<!DOCTYPE html>
<html>
    <style>
    body, html {
      height: 100%;
      margin: 0;
    }

    .bgimg {
      background-image: url('<?php echo $backgroundImage ?>');
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
    <title><?php echo $title ?>))</title>
<body>

<div class="bgimg">
  <div class="topleft">
    <p>Logo</p>
  </div>
  <div class="middle">
    <h1><?php echo $title ?></h1>
    <hr>
    <p id="demo" style="font-size:30px"></p>
  </div>
  <div class="bottomleft">
    <p><?php echo $subtitle ?></p>
  </div>
</div>

<script>
    // Set the date we're counting down to - two hours in the future
    var now = new Date();
    var countDownDate = new Date(now.getTime() + (<?php echo (1000*60*$minutesDown) ?>)).getTime();

    // Update the count down every 1 second
    var countdownfunction = window.setInterval(
        function() {

            // Get todays date and time
            var now = new Date().getTime();

            // Find the distance between now an the count down date
            var distance = countDownDate - now;

            // Time calculations for days, hours, minutes and seconds
            // var days = Math.floor(distance / (1000 * 60 * 60 * 24));
            var hours = Math.floor((distance % (1000 * 60 * 60 * 24)) / (1000 * 60 * 60));
            var minutes = Math.floor((distance % (1000 * 60 * 60)) / (1000 * 60));
            var seconds = Math.floor((distance % (1000 * 60)) / 1000);

            // Output the result in an element with id="demo"
            document.getElementById("demo").innerHTML = hours + "h "
            + minutes + "m " + seconds + "s ";

            // If the count down is over, write some text
            if (distance < 0) {
                window.clearInterval(countdownfunction);
                document.getElementById("demo").innerHTML = "EXPIRED";
            }
        },
        1000
    );
</script>

</body>
</html>
