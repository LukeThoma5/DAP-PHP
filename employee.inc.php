<?php
class Employee {

    function convert_to_boolean($str_bool) {
        switch ($str_bool) {

            case 'y':
                return TRUE;

            case 'n':
                return FALSE;
            default:
                return FALSE;
        }
    }

    function __construct($employee) {
        $this->id = $employee['id'];
        $this->firstname = $employee['firstname'];
        $this->lastname = $employee['lastname'];
        $this->grade = $employee['grade'];
        $this->jobtitle = $employee['jobtitle'];
        $this->ni = $employee['nationalinsurance'];
        $this->photo = $employee['photo'];
        $this->department = $employee['department'];
        $this->reportees = $employee['reports'];
        $this->manager = $employee['linemanager'];
        $this->salary = (int)$employee['salary'];
        $this->currency = $employee['currency'];
        $this->phone = $employee['phohe']; //Misspelling from the JSON
        $this->email = $employee['email'];
        $this->home_email = $employee['homeemail'];
        $this->address = $employee['homeaddress'];
        $this->nextofkin = $employee['nextofkin'];
        $this->employment_start = $employee['employmentstart'];
        $this->employment_end = $employee['employmentend'];
        $this->dob = $employee['dob'];
        $this->pension = $this->convert_to_boolean($employee['pension']);
        $this->pension_type = $employee['pensiontype'];
        $this->company_car = $this->convert_to_boolean($employee['companycar']);
        $this->_set_exceptions();
    }

    function _set_exceptions() {
        $this->exceptions = array();
        if ($this->company_car) {
            array_push($this->exceptions, "Company car");
        }
        if ($this->salary > 150000) {
            array_push($this->exceptions, "Super tax");
        }

    }

    function update_pay_stats() {
        $this->net_yearly_pay = $this->salary - $this->tax;
        $this->monthly_take_home_pay = $this->net_yearly_pay / 12;
    }

    function update_tax($taxes) {
        $tax_from_last_band = 0;
        
        $this->tax_values = array();
        foreach($taxes as $key => $band) {
            $values = new Tax_Values();
            $values->min = (int)$band['minsalary'];
            $values->max = (int)$band['maxsalary'];
            $band_size = $values->max-$values->min;
            if ($this->salary < $values->min) {
                continue;
            }
            $values->income_in_band = $this->salary - $values->min;
            if ($values->income_in_band > $band_size) {
                $values->income_in_band = $band_size;
            }

            $values->percentage_reduction = 0;
            $values->reductions_applied = array();
            foreach($band['exceptions'] as $_index => $exception) {
                foreach($exception as $exception_key => $percentage) {
                    if (in_array($exception_key, $this->exceptions)) {
                        $values->percentage_reduction += $percentage;
                        array_push($values->reductions_applied, $exception_key);
                    }
                }
            }
            $values->tax_from_last_band = $tax_from_last_band;

            $values->tax_reduction = $values->income_in_band * ($values->percentage_reduction/100);
            $values->taxable_amount = $values->income_in_band + $values->tax_from_last_band - $values->tax_reduction;
            $tax_from_last_band = $values->tax_reduction;


            $values->rate = $band['rate'] / 100;
            $values->tax_paid = $values->taxable_amount * $values->rate;
            if ($values->taxable_amount < 0) {
                $values->tax_paid = 0;
            }
            array_push($this->tax_values, $values);
        }
        $this->tax = array_sum(
            array_map(function($values) {
                return $values->tax_paid;
            }, $this->tax_values)
        );

        $this->update_pay_stats();
    }
}

class Tax_Values { }

function save_employees($employees) {
    file_put_contents('employees.bin', serialize($employees));
}

function load_employees() {
    return unserialize(file_get_contents('employees.bin'));
}

?>