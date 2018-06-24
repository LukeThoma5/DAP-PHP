<?php require_once("header.php"); ?>
<?php require_once("employee.inc.php"); ?>
<?php require_once("tax.inc.php"); ?>


<form action="" method="post" enctype="multipart/form-data">
    <ul>
    <li>Employee Data: <input type="file" name="file-data"></input></li>
    </ul>
    
    <div class="row">
    <div 
    class="input-field col s12 l3"
    >
    <select name="file-type" style="display: initial;">
      <option value="" disabled selected>Choose with file to upload</option>
      <option value="employee">Employee Data</option>
      <option value="tax">Tax Data</option>
    </select>
  </div>
    </div>
    <button class="btn waves-effect waves-light" type="submit" name="action">Submit
        <i class="material-icons right">send</i>
    </button>
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


function upload_employees($file) {
    $employees = json_decode(file_get_contents($file['tmp_name']), TRUE);
    $tax = load_tax();
    print_r($tax);
    $employee_objects = array_map(function($employee) use ($tax) {
        $employee_obj = new Employee($employee);
        $employee_obj->update_tax($tax);
        return $employee_obj;
    }, $employees);
    save_employees($employees);
    echo "<pre>"; print_r($employee_objects);
}

function validate_employees($file) {
    $is_valid = pathinfo($file['name'])['extension'] == 'json';
    if (!$is_valid) {
        echo '<div class="card-panel teal lighten-2">The uploaded employee\'s file is not valid, please try again.</div>';
    }
    return $is_valid;
}

if ($_SERVER['REQUEST_METHOD'] == "POST") {
    $file_type = $_POST['file-type'];
    $file = $_FILES['file-data'];
    switch ($file_type) {

        case 'employee':

            if (validate_employees($file)) {
                upload_employees($file);
            }
            break;
        
        case 'tax':
            upload_tax($file);
            break;

        default:
            echo "<script>alert('Invalid option, please try again')</script>";
            break;
    }
}
?>



<?php require_once("footer.php"); ?>
