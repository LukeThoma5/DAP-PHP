<?php

function upload_tax($file, $load_func, $save_func) {
    move_uploaded_file($file['tmp_name'], 'storage/tax-tables.json');
    $tax = load_tax();
    $employees = $load_func();
    foreach($employees as $index => $employee) {
        $employee->update_tax($tax);
    }
    $save_func($employees);
}

function load_tax() {
    $tax = json_decode(file_get_contents('storage/tax-tables.json'), TRUE);
    return $tax;
}

?>