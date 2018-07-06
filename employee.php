<!-- Header redirects if the user isn't logged in and adds the top navigation -->
<?php require_once("includes/header.php"); ?>
<!-- Employee include to get the Employee class and the load/save funcs -->
<?php require_once("includes/employee.inc.php"); ?>
<!-- Include for alert box -->
<?php require_once("includes/helpers.inc.php"); ?>
<!-- Include the config file to get the url for jsreport server -->
<?php require_once("includes/config.php"); ?>

<?php
    // Find the current employee from the query string

    // Load all employees
    $employees = load_employees();
    
    // Get the id out of the query string
    if (array_key_exists('id', $_GET)) {
        $id = $_GET['id'];
    } else {
        alert_box('No Id specified');
        require_once("includes/footer.php");
        exit();
    }

    // Place holder for the found employee
    $employee = NULL;
    foreach($employees as $index => $e) {
        if ($e->id == $id) { // If the id matches
            // We've found the employee so set it to the outer variable and end looping
            $employee = $e;
            break;
        }
    }
    // If employee is still null print an alert box and exit early
    if (!isset($employee)) {
        alert_box("Unable to find employee $id");
        require_once("includes/footer.php");
        exit();
    }

    function render_bool($bool) {
        if ($bool) { return "Yes"; }
        return "No";
    }
    
?>

<script>

/* saveBlob function taken from https://jsfiddle.net/koldev/cW7W5/ to pollyfill many browsers lack of window.saveAs */
var saveBlob = (function () {
    var a = document.createElement("a");
    document.body.appendChild(a);
    a.style = "display: none";
    return function (blob, fileName) {
        var url = window.URL.createObjectURL(blob);
        a.href = url;
        a.download = fileName;
        a.click();
        window.URL.revokeObjectURL(url);
    };
}());
/* end saveBlob */

// Function called when view is used on a payslip
function download_payslip() {

    // Dump the employee's json into the document so the JS can format it
    let employee = <?php echo json_encode($employee); ?>;


    let payslip_data = {
        "number": employee.id,
        "employee": {
                "name": `${employee.firstname} ${employee.lastname}`,
                "ni": employee.ni,
                "jobTitle": employee.jobtitle,
                "takeHomePay": employee.monthly_take_home_pay.toFixed(2)
            },
        "items": [
            {
                "name": "Salary",
                "price": (employee.salary / 12).toFixed(2)
            },
            {
            "name": "Tax",
            "price": (-employee.tax / 12).toFixed(2)
            }
        ]
    };

    // Define the body of the request. Which template to render + the data needed to render
    let body = {
            template: { shortid: GET_TEMPLATE_ID() }, // Get the ID from the config, to enable different environments eg Local and Business Server
            data : payslip_data
        };


    // Downloading of file from https://developer.mozilla.org/en-US/docs/Web/API/Body/body
    // fetch returns a promise for the request
    fetch(GET_JS_REPORT_URL(), // Get the report server url from the config php file
    {
        headers: {
            'User-Agent': 'request',
            'Content-Type': 'application/json'
        },
        method: "POST",
        body: JSON.stringify(body)
    })
    .then(response => {
        const reader = response.body.getReader();
        return new ReadableStream({
        start(controller) {
        return pump();
        function pump() {
            return reader.read().then(({ done, value }) => {
            // When no more data needs to be consumed, close the stream
            if (done) {
                controller.close();
                return;
            }
            // Enqueue the next data chunk into our target stream
            controller.enqueue(value);
            return pump();
            });
        }
        }  
    })
    }) // Read the stream, read to the end to get the contents of the pdf
    .then(stream => new Response(stream)) // Turn into a response
    .then(response => response.blob()) // To access the file as a blob
    .then(blob => window.saveBlob(blob, "payslip.pdf")) // To save the blob as payslip.pdf

}
</script>

<!-- Display core details about the employee -->
<div class="row">
    <div class="card-panel grey lighten-5 z-depth-2 col s9">
            <dl>
                <dt>Name</dt>
                <dd><?php echo $employee->firstname, " ", $employee->lastname; ?></dd>

                <dt>Job Title</dt>
                <dd><?php echo $employee->jobtitle; ?></dd>
            </dl>
            <dl>
                <dt>Email</dt>
                <dd><?php echo $employee->email; ?></dd>

                <dt>Home Email</dt>
                <dd><?php echo $employee->home_email; ?></dd>
            </dl>

            <dl>
                <dt>Pension</dt>
                <dd><?php echo render_bool($employee->pension); ?></dd>

                <dt>Company Car?</dt>
                <dd><?php echo render_bool($employee->company_car); ?></dd>
            </dl>
    </div>

    <!-- Display placeholder image -->
    <div class="card-panel right valign-wrapper grey lighten-5 z-depth-2 col s1">
        <img src="https://media.creativemornings.com/uploads/user/avatar/89900/Profile_picture_square.jpg" height="120" width="120" alt="" class="circle responsive-img"> 
    </div>
</div>

<!-- Display tax explanation -->
<div class="row">
    <?php
    // Helper function for displaying infinity rather than a large number
    function display_max($max) {
        if ($max > 1000000) return "&infin;";
        return '£' . $max; // Safe to return '£' as all currencies have been converted
    }

    // for each tax band, display its explanation
    foreach($employee->tax_values as $index => $values) {
        // employee fmt used to format the values to 2dp and add currency sign
        echo "
        <div class=\"card-panel grey lighten-5 z-depth-2 col l3 m6 s12\">
                <dl>
                    <dt>Band</dt>
                    <dd>£$values->min -> ", display_max($values->max), "</dd>
    
                    <dt>Income in band</dt>
                    <dd>{$employee->fmt($values->income_in_band)}</dd>
    
                    <dt>Reductions Applied<dt>
                    <dd><ul style=\"margin: 0;\">";
                    foreach ($values->reductions_applied as $i => $reduction) {
                        echo "<li>$reduction</li>";
                    }
                    if (count($values->reductions_applied) == 0) {
                        echo "<li>-</li>";
                    }
                    echo "</ul></dd>
                </dl>
    
                <dl>
                    <dt>Tax from last band</dt>
                    <dd>{$employee->fmt($values->tax_from_last_band)}</dd>
    
                    <dt>Tax reduction</dt>
                    <dd>{$employee->fmt($values->tax_reduction)}</dd>
    
                    <dt>Taxable amount<dt>
                    <dd>{$employee->fmt($values->taxable_amount)}</dd>
                </dl>
    
                <dl>
                    <dt>Rate</dt>
                    <dd>$values->rate</dd>
    
                    <dt>Tax paid</dt>
                    <dd>{$employee->fmt($values->tax_paid)}</dd>
    
                </dl>
        </div>
        ";
    }
    ?>
</div>

<!-- Display the overall tax information in the center-->
<div class="card-panel center grey lighten-5 z-depth-2 col l3 " style="width: 30%; margin:auto">
<?php
    echo "
        <dl>
            <dt>Salary</dt>
            <dd>{$employee->fmt($employee->salary)}</dd>

            <dt>Total Tax Paid</dt>
            <dd>{$employee->fmt($employee->tax)}</dd>

            
        </dl>
        <dl>
            <dt>Net Yearly Pay</dt>
            <dd>{$employee->fmt($employee->net_yearly_pay)}</dd>

            <dt>Monthly Take Home Pay</dt>
            <dd>{$employee->fmt($employee->monthly_take_home_pay)}</dd>
        </dl>    
  ";
?>
</div>

<!-- Display the last 5 payslips -->
<table>
    <thead>
    <tr>
        <th>Income</th>
        <th>Tax Month</th>
        <th>Actions</th>
    </tr>
    </thead>
    <tbody>
        <?php
            // Set the time zone so it doesn't interpret the date incorrectly using the date constructor
            date_default_timezone_set('UTC');
            // For the last 5 months
            for ($i = 0; $i < 5; $i++) {
                // Get a date formatted to be the Long month and Year, passing in the start of the current month and year
                $payDay = date('F y', mktime(0, 0, 0, date("m")-$i  , 0 , date("Y")));
                // Output the table row, income, month and download payslip button.
                echo "<tr><td>{$employee->fmt($employee->monthly_take_home_pay)}</td>
                <td>$payDay</td>
                <td><button id=\"$payDay\" onclick=\"download_payslip(this.id);\" class=\"btn waves-effect waves-light\">View
                <i class=\"far fa-eye left\"></i>
            </button></td></tr>";
            }

        ?>
    </tbody>
</table>

<?php require_once("includes/footer.php"); ?>