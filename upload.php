<?php require_once("includes/header.php"); ?>
<?php require_once("includes/employee.inc.php"); ?>
<?php require_once("includes/tax.inc.php"); ?>
<?php require_once("includes/helpers.inc.php"); ?>


<form action="" method="post" enctype="multipart/form-data">
    <input type="file" name="file-data"></input>
    
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
        <i class="fas fa-upload"></i>
    </button>
</form>


<?php


function upload_employees($file) {
    $employees = json_decode(file_get_contents($file['tmp_name']), TRUE);
    $tax = load_tax();
    $employee_objects = array_map(function($employee) use ($tax) {
        $employee_obj = new Employee($employee);
        $employee_obj->update_tax($tax);
        return $employee_obj;
    }, $employees);
    save_employees($employee_objects);
}

function is_json($file) {
    return pathinfo($file['name'])['extension'] == 'json';
}

function validate_employees($file) {
    $is_valid = is_json($file);
    if (!$is_valid) {
        alert_box('The uploaded employee\'s file is not valid, please try again.');
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
            if (is_json($file)) {
                upload_tax($file, 'load_employees', 'save_employees');
            } else {
                alert_box("Invalid tax table file");
            }
            break;

        default:
            echo "<script>alert('Invalid option, please try again')</script>";
            break;
    }
}
?>



<?php require_once("includes/footer.php"); ?>
