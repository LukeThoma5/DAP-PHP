<?php

function upload_tax($file) {
    move_uploaded_file($file['tmp_name'], 'uploads/tax-tables.json');
}

function load_tax() {
    $tax = json_decode(file_get_contents('uploads/tax-tables.json'), TRUE);
    return $tax;
}

?>