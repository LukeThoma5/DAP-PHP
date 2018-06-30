<?php require_once("includes/header.php"); ?>
<?php require_once("includes/employee.inc.php"); ?>

<script>
    function nav_to_employee(id) {
        alert(id);
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

    foreach($employees as $index => $employee) {
        echo '<tr>';
        echo "<td>$employee->firstname $employee->lastname</td>";
        echo "<td>£$employee->salary</td>";
        echo "<td>£$employee->tax</td>";
        echo "<td>£$employee->monthly_take_home_pay</td>";
        echo '<td><button id="' .$employee->id . '" onclick="nav_to_employee(this.id);" class="btn waves-effect waves-light">View
        <i class="far fa-eye left"></i>
    </button></td>';
        echo '</tr>';
    }
?>

        </tbody>
      </table>

<?php require_once("includes/footer.php"); ?>