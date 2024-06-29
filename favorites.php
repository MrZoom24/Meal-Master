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

// Get the user ID from the session
$user_id = isset($_SESSION['user_id']) ? intval($_SESSION['user_id']) : 0;

if ($user_id > 0) {
    // Fetch favorite recipes for the user
    $favorites_sql = "SELECT recipes.recipe_id, recipes.name AS recipe_name, recipes.description, images.path AS image_path, 
                             users.username, AVG(reviews.rating) AS average_rating
                      FROM favorites
                      JOIN recipes ON favorites.recipe_id = recipes.recipe_id
                      JOIN images ON recipes.recipe_id = images.recipe_id
                      JOIN users ON recipes.user_id = users.user_id
                      LEFT JOIN reviews ON recipes.recipe_id = reviews.recipe_id
                      WHERE favorites.user_id = $user_id
                      GROUP BY recipes.recipe_id, images.path, users.username";
    $favorites_result = $conn->query($favorites_sql);
} else {
    die("Invalid user ID.");
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <link href="./css/general.css?v=<?php echo time(); ?>" rel="stylesheet" type="text/css" />
  <link href="./css/favorites.css?v=<?php echo time(); ?>" rel="stylesheet" type="text/css" />
  <title>Favorite Recipes</title>
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
        <a class="navbar-element" href="./categories.php">
          <ion-icon name="fast-food-sharp" class="navbar-icon"></ion-icon>
          <p class="navbar-text">Categories</p>
        </a>
        <a class="navbar-element navbar-element-active" href="#">
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
    <div class="divider">
        <span>Favorite Recipes</span>
    </div>
    <div class="main-container">
    <?php
      if ($favorites_result->num_rows > 0) {
        while ($row = $favorites_result->fetch_assoc()) {
          echo '<div class="card">';
          echo '<img src="' . $row["image_path"] . '" class="card-image">';
          echo '<div class="card-description">';
          echo '<p class="card-author">' . htmlspecialchars($row["username"]) . '</p>';
          echo '<p class="card-rating">' . round($row["average_rating"], 1) . '/5<ion-icon name="star-sharp"></ion-icon></p>';
          echo '<p class="card-name">' . htmlspecialchars($row["recipe_name"]) . '</p>';
          echo '<p class="card-text">' . htmlspecialchars($row["description"]) . '</p>';
          echo '<a class="card-button" href="recipe.php?recipe_id=' . $row["recipe_id"] . '">READ MORE</a>';
          echo '</div>';
          echo '</div>';
        }
      } else {
        echo "No favorite recipes found.";
      }
      ?>
    </div>
  </main>

  <!-- Ionic icons -->
  <script type="module" src="https://unpkg.com/ionicons@7.1.0/dist/ionicons/ionicons.esm.js"></script>
  <script nomodule src="https://unpkg.com/ionicons@7.1.0/dist/ionicons/ionicons.js"></script>
</body>
</html>
