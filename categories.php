<?php
session_start();
define('DB_HOST', 'localhost');
define('DB_USER', 'root');
define('DB_PASS', '');
define('DB_NAME', 'mealmasters');

$conn = new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME);

if ($conn->connect_error) {
    die('Connection failed: ' . $conn->connect_error);
}

// Fetch categories
$sql = "SELECT category_id, name FROM categories";
$result = $conn->query($sql);
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <link href="./css/general.css?v=<?php echo time(); ?>" rel="stylesheet" type="text/css" />
  <link href="./css/categories.css?v=<?php echo time(); ?>" rel="stylesheet" type="text/css" />
  <title>Categories</title>
</head>
<body>
  <nav class="nav">
    <div class="nav-container">
      <div class="logo">
        <p class="logo-text-top">MEAL</p>
        <p class="logo-text-bottom">MASTER</p>
      </div>

      <div class="navbar">
        <a class="navbar-element" href="./homepage.php">
          <ion-icon name="home-sharp" class="navbar-icon"></ion-icon>
          <p class="navbar-text">Homepage</p>
        </a>
        <a class="navbar-element navbar-element-active" href="#">
          <ion-icon name="fast-food-sharp" class="navbar-icon"></ion-icon>
          <p class="navbar-text">Categories</p>
        </a>
        <a class="navbar-element" href="./favorites.php">
          <ion-icon name="star-sharp" class="navbar-icon"></ion-icon>
          <p class="navbar-text">Favorites</p>
        </a> 
        <a class="navbar-element" href="myprofile.php">
          <ion-icon name="person-sharp" class="navbar-icon"></ion-icon>
          <p class="navbar-text">My Profile</p>
        </a> 
      </div>
    </div>
  </nav>  

  <main class="main">
  <?php while ($row = $result->fetch_assoc()) { ?>
        <div class="category-card">
          <a href="category.php?category_id=<?php echo $row['category_id']; ?>">
            <img src="./images/<?php echo strtolower($row['name']); ?>.jpg" class="category-image" alt="<?php echo htmlspecialchars($row['name']); ?>">
            <p class="category-name"><?php echo htmlspecialchars($row['name']); ?></p>
          </a>
        </div>
  <?php } ?>
  </main>

  <!-- Ionic icons -->
  <script type="module" src="https://unpkg.com/ionicons@7.1.0/dist/ionicons/ionicons.esm.js"></script>
  <script nomodule src="https://unpkg.com/ionicons@7.1.0/dist/ionicons/ionicons.js"></script>
</body>
</html>