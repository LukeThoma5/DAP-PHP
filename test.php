<?php require_once("header.php"); ?>

<form action="" method="post" enctype=multipart/form-data>
    <ul>
    <li>Employee Data: <input type="file" name="employee-data"></input></li>
    <li>Tax Data: <input type="file" name="tax-data"></input></li>
    </ul>
    <button type="submit">Upload files</button>
</form>

<?php

function upload_file($file, $destination_name) {
    print_r($file);
    print_r(pathinfo($file['name']));
    if (pathinfo($file['name'])['extension'] != 'json') {
        echo "<h1>Rejected $destination_name file</h1>";
    } else {
        move_uploaded_file($file['tmp_name'], "uploads/$destination_name.json");
    }
}

if ($_SERVER['REQUEST_METHOD'] == "POST") {
    upload_file($_FILES['employee-data'], 'employees');
    upload_file($_FILES['tax-data'], 'tax-data');
}
?>



<?php require_once("footer.php"); ?>
