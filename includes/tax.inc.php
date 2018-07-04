<?php

function upload_tax($file, $load_func, $save_func) {
    // Move the uploaded file into persistent storage
    move_uploaded_file($file['tmp_name'], 'storage/tax-tables.json');
    // Reload the tax to check it saved correctly and to get its decoded value
    $tax = load_tax();
    // Load the employees as we need to update how much tax they pay
    $employees = $load_func();
    foreach($employees as $index => $employee) {
        // For each employee update their tax
        $employee->update_tax($tax);
    }
    // Save the changes to employees back to disk
    $save_func($employees);
}

function load_tax() {
    // Load the contents of the file into a string and then decode into an array
    $tax = json_decode(file_get_contents('storage/tax-tables.json'), TRUE);
    return $tax;
}

?>