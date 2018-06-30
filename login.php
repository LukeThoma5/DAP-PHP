<?php require_once("includes/header_without_auth.php"); ?>
<?php require_once("includes/helpers.inc.php"); ?>

<div>
<div class="row" style="width: 30%;
margin: 0 auto;">
    <form action="" method="post" class="col s12">
    <div class="row" >
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


<?php require_once("includes/footer.php"); ?>

<?php

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    exit();
}
print_r($_POST);
$email = $_POST['email'];
$password = $_POST['password'];
$pw_hash = hash('sha256', $password);
echo $pw_hash;

$users = json_decode(file_get_contents('storage/users.json'), TRUE);

foreach($users as $index => $user) {
    if ($pw_hash === $user['password'] && $email === $user['username']) {
        session_start();
        $_SESSION['auth'] = $user;
        echo '<script>window.location.href=\'employee-list.php\'</script>';
        exit();
    }
}

alert_box('Login Failed, please try again');

?>