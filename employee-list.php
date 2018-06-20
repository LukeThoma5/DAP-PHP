<?php require_once("header.php"); ?>

<?php

    

    function calculate_pension($employee) {
        $PENSION_CONTRIB = 0.03;
        $salary = $employee['salary'];
        return $salary * $PENSION_CONTRIB;
    }

    function band_calculation($salary, $taxes, $NIC_CODE) {
        $tax = 0;
        foreach($taxes as $key => $band) {
            if ($salary >= $band['band-start']) {

                $taxable_amount = $salary - $band['band-start'];
                if (isset($band['band-end'])) {
                    $band_max = $band['band-end'] - $band['band-start'];
                    if ($taxable_amount > $band_max) {
                        $taxable_amount = $band_max;
                    }
                }
                $rate =  $band['rate'];
                if (is_array($rate)) {
                    $rate = $rate[$NIC_CODE];
                }
                $tax += $taxable_amount * $rate;

            }
        }
        return $tax;
    }
?>

<?php
    $employees = json_decode(file_get_contents("uploads/employees.json"), TRUE);
    $taxes = json_decode(file_get_contents("uploads/tax-data.json"), TRUE);
    $NIC = json_decode(file_get_contents("uploads/NIC.json"), TRUE);

    foreach($employees as $index => $employee) {
        $salary = $employee['salary'];
        $yearly_pension_contribution = calculate_pension($employee);
        $taxable_income = $salary - $yearly_pension_contribution;
        $yearly_tax = band_calculation($taxable_income, $taxes);
        $yearly_NIC = band_calculation($taxable_income, $NIC, $employee['NIC']);
        $year_end_pay = $employee['salary'] - $yearly_tax - $yearly_NIC;
        $monthly_pay = $year_end_pay / 12;
        echo "<hr><ul>";
        echo "<li><b>Name:</b> $employee[title] $employee[firstname] $employee[lastname]</li>";
        echo "<li><b>Yearly taxes:</b> £$yearly_tax</li>";
        echo "<li><b>Yearly NI:</b> £$yearly_NIC</li>";
        echo "<li><b>Monthly take home pay:</b> £$monthly_pay</li>";
        echo "<li><b>Yearly pension summary:</b> £$yearly_pension_contribution</li>";
        echo "</ul>";
    }
?>

<?php require_once("footer.php"); ?>