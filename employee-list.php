<?php require_once("includes/header.php"); ?>
<?php require_once("includes/employee.inc.php"); ?>

<script>
    function nav_to_employee(id) {
        window.location.href = `employee.php?id=${id}`;
    }
</script>


      <table class="highlight">
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
    $employees = load_employees();

    $page = $_GET['p'];
    if (!isset($page)) {
        $page = 1;
    }

    $page_size = $_GET['s'];
    if (!isset($page_size)) {
        $page_size = 10;
    }
    $url_base = strtok($_SERVER["REQUEST_URI"],'?');

    foreach(array_slice($employees, ($page-1)*$page_size, $page_size) as $index => $employee) {
        echo '<tr>';
        echo "<td>$employee->firstname $employee->lastname</td>";
        echo "<td>{$employee->fmt($employee->salary)}</td>";
        echo "<td>{$employee->fmt($employee->tax)}</td>";
        echo "<td>{$employee->fmt($employee->monthly_take_home_pay)}</td>";
        echo '<td><button id="' .$employee->id . '" onclick="nav_to_employee(this.id);" class="btn waves-effect waves-light">View
        <i class="far fa-eye left"></i>
    </button></td>';
        echo '</tr>';
    }
?>

        </tbody>
      </table>

<div>
<div 
    class="input-field col s12 l3"
    >
    <select onchange="
    const option = this.options[this.selectedIndex].value;
    window.location.href = `employee-list.php?p=1&s=${option}`;
    " style="display: initial;">
    <?php 
        $options = array(5, 10, 20, 50);
        foreach($options as $index => $option) {
            $selected = "";
            if ($option == $page_size) {
                $selected = "selected";
            }
            echo "<option value=\"$option\" $selected>$option</option>";
        }
    ?>
    </select>
  </div>
</div>
  <ul class="pagination">
  <?php
    $final_page = ceil(sizeof($employees) / $page_size);
    $getUrl = function($direction) use ($page, $url_base, $final_page, $page_size) {
        $target_page = $direction($page);
        if ($target_page < 1 || $target_page > $final_page) {
            return "#!";
        } else {
            return "$url_base?p=$target_page&s=$page_size";
        }
    };

    $get_class = function($comparison) use ($page) {
        $class = 'waves-effect';
        if ($page == $comparison) {
            $class = 'disabled';
        }
        return $class;
    };   
    
    
    echo "<li class=\"{$get_class(1)}\"><a href=\"{$getUrl(function($p) { return $p-1; })}\"><i class=\"fas fa-angle-left\"></i></a></li>";
    for($i = 1; $i <= $final_page; $i += 1 ) {
        $class = "waves-effect";
        if ($i == $page) {
            $class = "active";
        }
        echo "<li class=\"$class\"><a href=\"$url_base?p=$i&s=$page_size\">$i</a></li>";
    }
    
    echo "<li class=\"{$get_class($final_page)}\"><a href=\"{$getUrl(function($p) { return $p+1; })}\"><i class=\"fas fa-angle-right\"></i></a></li>";

    ?>
  </ul>

<?php require_once("includes/footer.php"); ?>