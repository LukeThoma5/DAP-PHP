<?php require_once("includes/header.php"); ?>
<?php require_once("includes/employee.inc.php"); ?>

<script>
    // Helper function for when clicking view on an employee
    // Nav to employee page
    function nav_to_employee(id) {
        window.location.href = `employee.php?id=${id}`;
    }
</script>

<!-- Table for displaying the list of employees -->
<table class="highlight">
    <!-- Table headers -->
    <thead>
        <tr>
            <th>Name</th>
            <th>Salary</th>
            <th>Tax</th>
            <th>Take Home Pay</th>
            <th>Actions</th>
        </tr>
    </thead>
    <tbody>
          

<?php
    // Load the employees from the seralised bin file.
    function array_access($array, $key, $default) {
        if (array_key_exists($key, $array)) {
            return $array[$key];
        }
        return $default;
    }
    $employees = load_employees();

    // Determine the page number
    // If not included default to 1
    $page = array_access($_GET, 'p', 1);
    echo $page;

    // Determine the page size
    // Default to 10 if not set;
    $page_size = array_access($_GET, 's', 10);

    // Determine the current file path exclusing anything from the query string
    $url_base = strtok($_SERVER["REQUEST_URI"],'?');

    //Get the slice of employees that should be displayed on the current page
    // The offset is how many pages along we are times how large a page is
    // How many we are going to take is just the page size
    $paged_employees = array_slice($employees, ($page-1)*$page_size, $page_size);

    // For each employee that we are going to display, echo a table row
    foreach($paged_employees as $index => $employee) {
        echo '<tr>';
        echo "<td>$employee->firstname $employee->lastname</td>"; // Name
        echo "<td>{$employee->fmt($employee->salary)}</td>"; // Salary
        echo "<td>{$employee->fmt($employee->tax)}</td>"; // Yearly Tax
        echo "<td>{$employee->fmt($employee->monthly_take_home_pay)}</td>"; // Monthly take home pay
        echo '<td><button id="' .$employee->id . '" onclick="nav_to_employee(this.id);" class="btn waves-effect waves-light">View
        <i class="far fa-eye left"></i>
    </button></td>'; // View button, id set to the employee's id so that it can be passed to the view employee function
        echo '</tr>';
    }
?>

    </tbody>
</table>

<div class="row">
    <div class="input-field col s12 l3" >
        <!-- On change reload the page with the correct page size -->
        <select onchange="
        const option = this.options[this.selectedIndex].value;
        window.location.href = `employee-list.php?p=1&s=${option}`;
        " style="display: initial;">
            <?php 
                // Generate the select options
                $options = array(3, 5, 10, 20, 50);
                foreach($options as $index => $option) {
                    // If the option is the same as the page_size add the selected tag to the option so it is pre selected
                    $selected = "";
                    if ($option == $page_size) {
                        $selected = "selected";
                    }
                    // Add the option to the document
                    echo "<option value=\"$option\" $selected>$option</option>";
                }
            ?>
        </select>
  </div>
  <!-- Add the pagination floated to the right-->
  <ul class="pagination right">
  <?php
    // Determine how many pages of employees there are
    // Take the ceiling so if there are 2.6 pages, there are 3 page options
    $final_page = ceil(sizeof($employees) / $page_size);

    // Make a closure with a single argument being a function to determine if increasing or decreasing the current page number
    // A closure is used to make the function more ergonomic to call aswell as prevent errors due to incorrect ordering of the arguments
    $get_url = function($direction) use ($page, $url_base, $final_page, $page_size) {
        // Calculate the destination page number
        $target_page = $direction($page);
        // If less than 1 or more than the amount of pages return a link that doesn't navigate
        if ($target_page < 1 || $target_page > $final_page) {
            return "#!";
        } else {
            // Otherwise its valid so return the next page
            return "$url_base?p=$target_page&s=$page_size";
        }
    };

    // Another closure for determining the if the arrows of the pagination should have the disabled class applied
    $get_class = function($comparison) use ($page) {
        $class = 'waves-effect';
        // If the page is at the end specified, disable the arrow
        if ($page == $comparison) {
            $class = 'disabled';
        }
        return $class;
    };   
    
    // Add the left arrow to the document
    // Use get class closure to disable the arrow if on page 1
    // Use get url closure to determine the url for the page before this one
    echo "<li class=\"{$get_class(1)}\"><a href=\"{$get_url(function($p) { return $p-1; })}\"><i class=\"fas fa-angle-left\"></i></a></li>";
    // For all the pages up to and including the final page
    for($i = 1; $i <= $final_page; $i += 1 ) {
        // determine if the current iteration is for the current page
        $class = "waves-effect";
        if ($i == $page) {
            $class = "active";
        }

        // Output the page number, including the page link
        echo "<li class=\"$class\"><a href=\"$url_base?p=$i&s=$page_size\">$i</a></li>";
    }
    
    // Same as the left arrow, except the next page is increasing the page number and it should be disabled if at the final page
    echo "<li class=\"{$get_class($final_page)}\"><a href=\"{$get_url(function($p) { return $p+1; })}\"><i class=\"fas fa-angle-right\"></i></a></li>";

    ?>
  </ul>
</div>
  

<?php require_once("includes/footer.php"); ?>