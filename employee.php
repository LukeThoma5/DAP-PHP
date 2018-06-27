<?php require_once("header.php"); ?>
<?php require_once("employee.inc.php"); ?>
<?php require_once("helpers.inc.php"); ?>


<?php
    $employees = load_employees();
    $id = $_GET['id'];
    $employee = NULL;
    foreach($employees as $index => $e) {
        if ($e->id === $id) {
            $employee = $e;
            break;
        }
    }
    if (!isset($employee)) {
        alert_box("Unable to find employee $id");
        exit();
    }
?>

<div class="row">
    <div class="card-panel grey lighten-5 z-depth-2 col s9">
            <dl>
                <dt>Name</dt>
                <dd>Mr Luke Foreman</dd>

                <dt>Job Title</dt>
                <dd>Degree Apprentice</dd>
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
                    <dd>£$values->income_in_band</dd>
    
                    <dt>Reductions Applied<dt>
                    <dd><ul>";
                    foreach ($values->reductions_applied as $i => $reduction) {
                        echo "<li>$reduction</li>";
                    }
                    echo "</ul></dd>
                </dl>
    
                <dl>
                    <dt>Tax from last band</dt>
                    <dd>£$values->tax_from_last_band</dd>
    
                    <dt>Tax reduction</dt>
                    <dd>£$values->tax_reduction</dd>
    
                    <dt>Taxable amount<dt>
                    <dd>£$values->taxable_amount</dd>
                </dl>
    
                <dl>
                    <dt>Rate</dt>
                    <dd>$values->rate</dd>
    
                    <dt>Tax paid</dt>
                    <dd>£$values->tax_paid</dd>
    
                </dl>
        </div>
        ";
    }
    ?>
    

</div>

<?php require_once("footer.php"); ?>