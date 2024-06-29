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

// Get the category ID from the URL
$category_id = isset($_GET['category_id']) ? intval($_GET['category_id']) : 0;

if ($category_id > 0) {
    // Fetch category name
    $category_sql = "SELECT name FROM categories WHERE category_id = $category_id";
    $category_result = $conn->query($category_sql);
    $category = $category_result->fetch_assoc();

    // Fetch recipes for the selected category
    $recipes_sql = "SELECT recipes.recipe_id, recipes.name AS recipe_name, recipes.description, images.path AS image_path, 
                           users.username, AVG(reviews.rating) AS average_rating
                    FROM recipes
                    JOIN images ON recipes.recipe_id = images.recipe_id
                    JOIN users ON recipes.user_id = users.user_id
                    LEFT JOIN reviews ON recipes.recipe_id = reviews.recipe_id
                    WHERE recipes.category_id = $category_id
                    GROUP BY recipes.recipe_id, images.path, users.username";
    $recipes_result = $conn->query($recipes_sql);
} else {
    die("Invalid category ID.");
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <link href="./css/general.css?v=<?php echo time(); ?>" rel="stylesheet" type="text/css" />
  <link href="./css/category.css?v=<?php echo time(); ?>" rel="stylesheet" type="text/css" />
  <title><?php echo htmlspecialchars($category['name']); ?> Recipes</title>
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
        <a class="navbar-element" href=".favorites.php">
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
        <span><?php echo htmlspecialchars($category['name']); ?> Recipes</span>
    </div>
    <div class="main-container">
    <?php
      if ($recipes_result->num_rows > 0) {
        while ($row = $recipes_result->fetch_assoc()) {
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