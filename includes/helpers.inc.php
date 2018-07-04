<?php

function alert_box($text) {
    // Add a div to the DOM with the passed in text
    echo "<div class=\"card-panel teal lighten-2\">$text</div>";
}

function redirect($location) {
    // Add some javascript to override the current location
    echo "<script>
        window.location.href = '$location';
</script>";
}


?>