<?php
session_start();
define('DB_HOST', 'localhost');
define('DB_USER', 'root');
define('DB_PASS', '');
define('DB_NAME', 'mealmasters');

$conn = new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME);

if($conn->connect_error){
  die('Connection failed: ' . $conn->connect_error);
}

if($_SERVER['REQUEST_METHOD'] == 'POST'){
  if(isset($_POST['login'])){

    $username = $_POST['username'];
    $password = $_POST['password'];

    
    $sql = "SELECT user_id, username, password FROM users WHERE username = '$username' AND password = '$password'";
    $result = $conn->query($sql);

    if ($result->num_rows > 0) {
      $row = $result->fetch_assoc();

      $_SESSION['user_id'] = $row['user_id'];

      header("Location: homepage.php");
      exit();
  } else {
      $message = "Invalid username or password!";
      echo "<script>alert('$message');</script>";
  }

  }elseif (isset($_POST['register'])){
    $username = $_POST['username'];
    $password = $_POST['password'];

    $sql = "INSERT INTO users (username, password) VALUES ('$username', '$password')";

    if(mysqli_query($conn, $sql)){
      $message = "Registered Succesfully!";
      echo "<script>alert('$message');</script>";
    }else {
      $message = "Registration Failed!";
      echo "<script>alert('$message');</script>";
    }
  }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <link href="./css/general.css" rel="stylesheet">
  <link href="./css/login.css" rel="stylesheet">
  <title>Meal Master | Login</title>
</head>
<body>
  <main class="main">
    <div class="main-container">
      <div class="logo-log">
        <p class="logo-log-text-top">MEAL</p>
        <p class="logo-log-text-bottom">MASTER</p>
      </div>

      <form class="form-login" method="post">
        <div class="form-login-fields">
          <label for="username" class="login-label">Username:</label>
          <input type="text" class="login-input" name="username" id="username" maxlength="25" required>
          <label for="password" class="login-label">Password:</label>
          <input type="password" class="login-input" name="password" id="password" maxlength="25" required>
        </div>
        <div class="form-login-actions">
          <input type="submit" class="login-submit" name="login" id="login" value="Login">
          <input type="submit" class="login-submit" name="register" id="register" value="Register">
        </div>
      </form>
    </div>
  </main>

  </body>
</html>