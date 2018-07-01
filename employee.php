<?php require_once("includes/header.php"); ?>
<?php require_once("includes/employee.inc.php"); ?>
<?php require_once("includes/helpers.inc.php"); ?>
<?php require_once("includes/config.php"); ?>

<?php
    $employees = load_employees();
    $id = $_GET['id'];
    $employee = NULL;
    foreach($employees as $index => $e) {
        if ($e->id == $id) {
            $employee = $e;
            break;
        }
    }
    if (!isset($employee)) {
        alert_box("Unable to find employee $id");
        exit();
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

function download_payslip() {

    let employee = <?php echo json_encode($employee); ?>;

    let data = {
    "number": employee.id,
    "employee": {
        "name": `${employee.firstname} ${employee.lastname}`,
        "ni": employee.ni,
        "jobTitle": employee.jobtitle,
        "takeHomePay": employee.monthly_take_home_pay.toFixed(2)
    },
    "items": [{
        "name": "Salary",
        "price": (employee.salary / 12).toFixed(2)
    },
    {
        "name": "Tax",
        "price": (-employee.tax / 12).toFixed(2)
    }
    ]
};

let body = {
        template: {  "shortid": GET_TEMPLATE_ID()
        },
        data : data
    }


// Downloading of file from https://developer.mozilla.org/en-US/docs/Web/API/Body/body
fetch(GET_JS_REPORT_URL(),
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
})
.then(stream => new Response(stream))
.then(response => response.blob())
.then(blob => window.saveBlob(blob, "report.pdf"))




}


</script>




<div class="row">
    <div class="card-panel grey lighten-5 z-depth-2 col s9">
            <dl>
                <dt>Name</dt>
                <dd><?php echo $employee->firstname, " ", $employee->lastname; ?></dd>

                <dt>Job Title</dt>
                <dd><?php echo $employee->jobtitle; ?></dd>
            </dl>
    </div>

    <div class="card-panel right valign-wrapper grey lighten-5 z-depth-2 col s1">
        <img src="https://media.creativemornings.com/uploads/user/avatar/89900/Profile_picture_square.jpg" height="120" width="120" alt="" class="circle responsive-img"> 
    </div>
</div>


<div class="row">
    <?php
    function display_max($max) {
        if ($max > 1000000) return "&infin;";
        return '£' . $max;
    }
    foreach($employee->tax_values as $index => $values) {
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


<?php

echo "<div class=\"card-panel center grey lighten-5 z-depth-2 col l3 \" style=\"width: 30%; margin:auto\">
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
    </div>"

?>

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
        date_default_timezone_set('UTC');
        for ($i = 0; $i < 6; $i++) {
            $payDay = date('F y', mktime(0, 0, 0, date("m")-$i  , 0 , date("Y")));
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