<!-- Header redirects if the user isn't logged in and adds the top navigation -->
<?php require_once("includes/header.php"); ?>
<!-- Employee include to get the Employee class and the load/save funcs -->
<?php require_once("includes/employee.inc.php"); ?>
<!-- Load/Save tax tables -->
<?php require_once("includes/tax.inc.php"); ?>
<!-- Include for alert box -->
<?php require_once("includes/helpers.inc.php"); ?>


<form action="" method="post" enctype="multipart/form-data">
    <input type="file" name="file-data"></input>
    <!-- Input for selecting what type of file is being uploaded and therefore how it should be validated -->
    <div class="row">
        <div class="input-field col s12 l3">
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
    //Load the uploaded json into a php array
    $employees_JSON = json_decode(file_get_contents($file['tmp_name']), TRUE);
    // Load the tax tables in order to update the employees tax numbers
    $tax = load_tax();
    // Map from the array of arrays to an array of php objects
    $employee_objects = array_map(
        // Array map takes a function to transform one object to another
        // Bring the tax variable into scope with the use statement
        function($employee) use ($tax) {
            // Map from a json array to a PHP object using its constructor
            $employee_obj = new Employee($employee);
            // Update the tax values using the uploaded tax tables
            $employee_obj->update_tax($tax);
            // Return the newly created employee to add it to the list
            return $employee_obj;
    }, $employees_JSON); // Array map is operating on the array $employees_JSON
    // Serialise the employee objects to a file so they can be used later
    save_employees($employee_objects); 
}

function is_json($file) {
    // Is the extension of the uploaded file json, then its json
    return pathinfo($file['name'])['extension'] == 'json';
}

function validate_employees($file) {
    // Determine if the upload is valid
    $is_valid = is_json($file);
    if (!$is_valid) {
        // If not valid, add a warning box
        alert_box('The uploaded employee\'s file is not valid, please try again.');
    }
    // Return if its valid for use by caller
    return $is_valid;
}

// If we have uploaded a file
if ($_SERVER['REQUEST_METHOD'] == "POST") {
    // Get out the file and which type it is
    $file_type = $_POST['file-type'];
    $file = $_FILES['file-data'];

    switch ($file_type) {

        
        case 'employee':
            // If an employee file, check its valid and upload
            if (validate_employees($file)) {
                upload_employees($file);
            }
            break;
        
        case 'tax':
            // If a tax file, check its json then upload it, otherwise display warning
            if (is_json($file)) {
                // Pass in the file to upload, load/save functions as uploading new tax tables
                // Requires updating all the existing employees. Functions are passed in rather than
                // Including in tax.inc.php to prevent circular references/ it pulling too much into scope.
                upload_tax($file, 'load_employees', 'save_employees');
            } else {
                alert_box("Invalid tax table file");
            }
            break;

        // If they didn't select anything display an alert
        default:
            alert_box('Invalid option, please try again');
            break;
    }
}
?>

<!-- Finish the document -->
<?php require_once("includes/footer.php"); ?>
