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
