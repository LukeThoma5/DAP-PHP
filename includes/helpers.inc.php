<?php

function alert_box($text) {
    echo "<div class=\"card-panel teal lighten-2\">$text</div>";
}

function redirect($location) {
    echo "<script>
        window.location.href = '$location';
</script>";
}


?>