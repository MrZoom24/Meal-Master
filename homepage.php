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

$sql_recent = "SELECT recipes.recipe_id, recipes.name AS recipe_name, recipes.description, users.username, images.path AS image_path, 
        AVG(reviews.rating) AS average_rating
        FROM recipes
        JOIN users ON recipes.user_id = users.user_id
        LEFT JOIN images ON recipes.recipe_id = images.recipe_id
        LEFT JOIN reviews ON recipes.recipe_id = reviews.recipe_id
        GROUP BY recipes.recipe_id, recipes.name, recipes.description, users.username, images.path
        ORDER BY recipes.recipe_id DESC
        LIMIT 6";

$result_recent = $conn->query($sql_recent);

$sql_popular = "SELECT recipes.recipe_id, recipes.name AS recipe_name, recipes.description, users.username, images.path AS image_path, 
        AVG(reviews.rating) AS average_rating
        FROM recipes
        JOIN users ON recipes.user_id = users.user_id
        LEFT JOIN images ON recipes.recipe_id = images.recipe_id
        LEFT JOIN reviews ON recipes.recipe_id = reviews.recipe_id
        GROUP BY recipes.recipe_id, recipes.name, recipes.description, users.username, images.path
        ORDER BY average_rating DESC
        LIMIT 6";

$result_popular = $conn->query($sql_popular);
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <link href="./css/general.css?v=<?php echo time(); ?>" rel="stylesheet" type="text/css" />
  <link href="./css/homepage.css?v=<?php echo time(); ?>" rel="stylesheet" type="text/css" />
  <title>Meal Master</title>
</head>
<body>
  
  <nav class="nav">
    <div class="nav-container">
      <div class="logo">
        <p class="logo-text-top">MEAL</p>
        <p class="logo-text-bottom">MASTER</p>
      </div>

      <div class="navbar">
        <a class="navbar-element navbar-element-active" href="#">
          <ion-icon name="home-sharp" class="navbar-icon"></ion-icon>
          <p class="navbar-text">Homepage</p>
        </a>
        <a class="navbar-element" href="./categories.php">
          <ion-icon name="fast-food-sharp" class="navbar-icon"></ion-icon>
          <p class="navbar-text">Categories</p>
        </a>
        <a class="navbar-element" href="favorites.php">
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
    <div class="main-container">
      <div class="divider">
        <span>LATEST RECIPES</span>
      </div>
      <?php
      if ($result_recent->num_rows > 0) {
        echo '<div class="cards-container">';
        while($row = $result_recent->fetch_assoc()) {
          echo '<div class="card">';
          echo '<img src="' . $row["image_path"] . '" class="card-image">';
          echo '<div class="card-description">';
          echo '<p class="card-author">' . $row["username"] . '</p>';
          echo '<p class="card-rating">' . round($row["average_rating"], 1) . '/5<ion-icon name="star-sharp"></ion-icon></p>';
          echo '<p class="card-name">' . $row["recipe_name"] . '</p>';
          echo '<p class="card-text">' . $row["description"] . '</p>';
          echo '<a class="card-button" href="recipe.php?recipe_id=' . $row["recipe_id"] . '">READ MORE</a>';
          echo '</div>';
          echo '</div>';
        }
        echo '</div>';
        } else {
          echo "No recipes found.";
        }
      ?>

      <div class="divider">
        <span>MOST LIKED RECIPES</span>
      </div>
      <?php
      if ($result_popular->num_rows > 0) {
        echo '<div class="cards-container">';
        while($row = $result_popular->fetch_assoc()) {
          echo '<div class="card">';
          echo '<img src="' . $row["image_path"] . '" class="card-image">';
          echo '<div class="card-description">';
          echo '<p class="card-author">' . $row["username"] . '</p>';
          echo '<p class="card-rating">' . round($row["average_rating"], 1) . '/5<ion-icon name="star-sharp"></ion-icon></p>';
          echo '<p class="card-name">' . $row["recipe_name"] . '</p>';
          echo '<p class="card-text">' . $row["description"] . '</p>';
          echo '<a class="card-button" href="recipe.php?recipe_id=' . $row["recipe_id"] . '">READ MORE</a>';
          echo '</div>';
          echo '</div>';
        }
        echo '</div>';
        } else {
          echo "No recipes found.";
        }
      ?>
    </div>
  </main>


  <!-- Ionic icons -->
  <script type="module" src="https://unpkg.com/ionicons@7.1.0/dist/ionicons/ionicons.esm.js"></script>
  <script nomodule src="https://unpkg.com/ionicons@7.1.0/dist/ionicons/ionicons.js"></script>
</body>
</html>