<?php

function upload_tax($file) {
    move_uploaded_file($file['tmp_name'], 'storage/tax-tables.json');
}

function load_tax() {
    $tax = json_decode(file_get_contents('storage/tax-tables.json'), TRUE);
    return $tax;
}

?>