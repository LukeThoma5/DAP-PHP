<?php

// Singleton class for converting currency
// Singleton so that the list of exchange rates is the same throughout the application
final class CurrencyConverter
{
    // Static function (doesn't need an instance to be called)
    // Get the current currency converter, if there isn't one call the private constructor
    public static function Converter()
    {
        static $inst = null;
        if ($inst === null) {
            $inst = new CurrencyConverter();
        }
        return $inst;
    }

   // Private constructor so that it can't be instantiated outside of the class
    private function __construct()
    {
        $this->rates = array();
    }

    // Function to convert any currency into GBP
    function convert_to_GBP($number, $currency)
    {
        // Try and get the rate from the known conversion rates
        $rate = $this->rates[$currency];
        if (isset($rate)) {
            return $number * $rate;
        } else {
            // If we don't know the rate yet, make a call to currencyconverterapi to get the rate
            $url = file_get_contents('http://free.currencyconverterapi.com/api/v3/convert?q=' . $currency . '_GBP' . '&compact=ultra');
            $json = json_decode($url, true); // Turn into an array
            $rate = $json[$currency . '_GBP']; // Access the conversion rate
            $this->rates[$currency] = $rate; // Set the rate so it doesn't need to do an api call to get it again
            return $number * $rate; // Return the converted value
        }
    }
}

// Class to represent the employee object
class Employee {

    // Helper function to convert from the yes/no strings to
    // true boolean values
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

    // Constructor takes in an employee assoc array and converts into an object
    // It does some name mapping for my sanity, data type conversion and setting up the
    // data to be easily queryable
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
        $this->original_currency = $employee['currency'];
        // If we don't have GBP, convert into gbp
        if ($this->original_currency != "GBP") {
            // Set their salary to its converted equivilent
            $this->salary = CurrencyConverter::Converter()->convert_to_GBP($this->salary, $this->original_currency);
        }
        $this->phone = $employee['phohe']; //Misspelling from the JSON
        $this->email = $employee['email'];
        $this->home_email = $employee['homeemail'];
        $this->address = $employee['homeaddress'];
        $this->nextofkin = $employee['nextofkin'];
        $this->employment_start = $employee['employmentstart'];
        $this->employment_end = $employee['employmentend'];
        $this->dob = $employee['dob'];
        // Map to bool
        $this->pension = $this->convert_to_boolean($employee['pension']);
        $this->pension_type = $employee['pensiontype'];
        // Map to bool
        $this->company_car = $this->convert_to_boolean($employee['companycar']);
        // Set exceptions for decreasing their free allowance
        $this->_set_exceptions();
    }

    // Set exceptions for decreasing their free allowance
    function _set_exceptions() {
        // Ensure attribute is always a valid array
        $this->exceptions = array();
        // If they have a company car, set the company car exception
        if ($this->company_car) {
            array_push($this->exceptions, "Company car");
        }
        // If they earn over the 150,000 so in the top band,
        // add the super tax boundary
        if ($this->salary > 150000) {
            array_push($this->exceptions, "Super tax");
        }

    }

    // Commonly displayed values so cache their value
    function update_pay_stats() {
        $this->net_yearly_pay = $this->salary - $this->tax;
        $this->monthly_take_home_pay = $this->net_yearly_pay / 12;
    }

    // Function for formatting the currency to 2 dp
    function fmt($num) {
        return '£' . number_format((float)$num, 2);
    }

    function update_tax($taxes) {
        // Variable to know how much of a tax discount was given in the previous band
        // For the first band this will be 0
        $tax_from_last_band = 0;
        
        // Clear out the tax explanation array
        $this->tax_values = array();

        // Loop over the tax bands
        foreach($taxes as $key => $band) {
            $values = new Tax_Values();
            // Set the tax explanation values
            $values->min = (int)$band['minsalary'];
            $values->max = (int)$band['maxsalary'];

            // The size of the band is how much income can
            // be in that band
            $band_size = $values->max-$values->min;

            // If no income is in this band, skip the calculation
            if ($this->salary < $values->min) {
                continue;
            }

            // Initially set the income in band to be however much is over
            // the minimum for the band
            $values->income_in_band = $this->salary - $values->min;
            // If the income in the band is more than should be in the band,
            // set it to the max value (the band size)
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
            if ($values->percentage_reduction > 100) {
                $values->percentage_reduction = 100;
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

// Helper class for storing the explanation from the update tax function
class Tax_Values { }

// Serialise the employees to disk so they can be used in other files
function save_employees($employees) {
    file_put_contents('storage/employees.bin', serialize($employees));
}


// Unserialise the employees back into Employee objects
function load_employees() {
    return unserialize(file_get_contents('storage/employees.bin'));
}

?>
