<!-- Needs the header without redirect logic as this is the page that gets redirected to. -->
<?php require_once("includes/header_without_auth.php"); ?>
<!-- Helpers for redirect and alert box -->
<?php require_once("includes/helpers.inc.php"); ?>

<!-- Form -->
<div>
<!-- Only 30% width, margin auto centers it -->
  <div style="width: 30%; margin: 0 auto;">
    <form action="" method="post" class="col s12">
    <div class="row">
        <div class="input-field col s12">
          <input id="email" name="email" type="email" class="validate">
          <label for="email">Email</label>
        </div>
      </div>
      <div class="row">
        <div class="input-field col s12">
          <input id="password" name="password" type="password" class="validate">
          <label for="password">Password</label>
        </div>
      </div>
      <button class="btn waves-effect waves-light" type="submit" name="action">Submit
      <i class="fas fa-sign-in-alt right"></i>
      </button>      
    </form>
  </div>
</div>

<?php
// If we didn't just try to login, stop processing
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    //Add the footer to complete the document
    require_once("includes/footer.php");
    exit();
}
// Get the form fields
$email = $_POST['email'];
$password = $_POST['password'];

// Get the sha256 hash of the password, how the password is stored in the users.json to make it
// harder to decode what the password was. Ideally would include a salt however a simplified version has
// been used due to other simplifications such as no DB access.
$pw_hash = hash('sha256', $password);

// Load the list of users and turn into a php array.
$users = json_decode(file_get_contents('storage/users.json'), TRUE);

//Search through all the users for a matching password and email
foreach($users as $index => $user) {
    if ($pw_hash === $user['password'] && $email === $user['username']) {
        // Correct password and email entered. Set the auth session variable to be the logged in user.
        session_start();
        $_SESSION['auth'] = $user;

        // Redirect to the login page
        redirect('employee-list.php');

        // Include the header to complete the document
        require_once("includes/footer.php");
        exit();
    }
}

// If we haven't exited yet, the user entered an incorrect combination
// So display a blue info box to say something went wrong.
alert_box('Login Failed, please try again');
require_once("includes/footer.php");

?>