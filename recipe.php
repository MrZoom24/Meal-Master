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

// Get the recipe ID from the URL
$recipe_id = isset($_GET['recipe_id']) ? intval($_GET['recipe_id']) : 0;
$user_id = $_SESSION['user_id'];

if ($recipe_id > 0) {
    // Fetch recipe details
    $sql = "SELECT recipes.name AS recipe_name, recipes.description, recipes.ingredients, recipes.instruction, 
                   images.path AS image_path, AVG(reviews.rating) AS average_rating
            FROM recipes
            JOIN images ON recipes.recipe_id = images.recipe_id
            LEFT JOIN reviews ON recipes.recipe_id = reviews.recipe_id
            WHERE recipes.recipe_id = $recipe_id
            GROUP BY recipes.recipe_id, images.path";
    
    $result = $conn->query($sql);
    $recipe = $result->fetch_assoc();

    // Check if the user has already rated this recipe
    $rating_sql = "SELECT rating FROM reviews WHERE user_id = $user_id AND recipe_id = $recipe_id";
    $rating_result = $conn->query($rating_sql);
    $user_rating = $rating_result->fetch_assoc();

    // Check if the recipe is already a favorite
    $favorite_sql = "SELECT favorite_id FROM favorites WHERE user_id = $user_id AND recipe_id = $recipe_id";
    $favorite_result = $conn->query($favorite_sql);
    $is_favorite = $favorite_result->num_rows > 0;

    // Handle rating submission
    if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['rating'])) {
        $rating = intval($_POST['rating-num']);
        if (!$user_rating) {
            $insert_rating_sql = "INSERT INTO reviews (recipe_id, user_id, rating) VALUES ($recipe_id, $user_id, $rating)";
            $conn->query($insert_rating_sql);
        } else {
            $update_rating_sql = "UPDATE reviews SET rating = $rating WHERE user_id = $user_id AND recipe_id = $recipe_id";
            $conn->query($update_rating_sql);
        }
        header("Location: recipe.php?recipe_id=$recipe_id");
        exit;
    }

    // Handle favorite toggling
    if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['favorites'])) {
        if ($is_favorite) {
            $delete_favorite_sql = "DELETE FROM favorites WHERE user_id = $user_id AND recipe_id = $recipe_id";
            $conn->query($delete_favorite_sql);
        } else {
            $insert_favorite_sql = "INSERT INTO favorites (user_id, recipe_id) VALUES ($user_id, $recipe_id)";
            $conn->query($insert_favorite_sql);
        }
        header("Location: recipe.php?recipe_id=$recipe_id");
        exit;
    }

    // Handle comment submission
    if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['comment'])) {
        $comment = $conn->real_escape_string($_POST['comment-text']);
        $insert_comment_sql = "INSERT INTO comments (user_id, recipe_id, content, created_at) VALUES ($user_id, $recipe_id, '$comment', NOW())";
        $conn->query($insert_comment_sql);
        header("Location: recipe.php?recipe_id=$recipe_id");
        exit;
    }

    // Fetch comments
    $comments_sql = "SELECT users.username, comments.content, comments.created_at 
                     FROM comments
                     JOIN users ON comments.user_id = users.user_id
                     WHERE comments.recipe_id = $recipe_id
                     ORDER BY comments.created_at DESC";
    $comments_result = $conn->query($comments_sql);
} else {
    die("Invalid recipe ID.");
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <link href="./css/general.css?v=<?php echo time(); ?>" rel="stylesheet" type="text/css" />
  <link href="./css/recipe.css?v=<?php echo time(); ?>" rel="stylesheet" type="text/css" />
  <title>Recipe</title>
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
        <a class="navbar-element" href="./favorites.php">
          <ion-icon name="star-sharp" class="navbar-icon"></ion-icon>
          <p class="navbar-text">Favorites</p>
        </a> 
        <a class="navbar-element" href="./myprofile.php">
          <ion-icon name="person-sharp" class="navbar-icon"></ion-icon>
          <p class="navbar-text">My Profile</p>
        </a> 
      </div>
    </div>
  </nav>
  <main class="main">
    <div class="main-container">
      <div class="divider">
        <span><?php echo htmlspecialchars($recipe['recipe_name']); ?></span>
      </div>

      <div class="recipe-container">
        <p class="recipe-description">
          <?php echo htmlspecialchars($recipe['description']); ?>
        </p>

        <img src="<?php echo htmlspecialchars($recipe['image_path']); ?>" class="recipe-image" alt="<?php echo htmlspecialchars($recipe['recipe_name']); ?>">

        <div class="recipe-instruction">
          <div class="recipe-ingredients">
            <p class="recipe-header">Ingredients</p>
            <ul>
              <?php
              $ingredients = explode(',', $recipe['ingredients']);
              foreach ($ingredients as $ingredient) {
                  echo '<li>' . htmlspecialchars($ingredient) . '</li>';
              }
              ?>
            </ul>
          </div>
          <div class="recipe-steps">
            <p class="recipe-header">Instruction</p>
            <ol>
              <?php
              $instructions = explode('.', $recipe['instruction']);
              foreach ($instructions as $instruction) {
                  if (trim($instruction) !== '') {
                      echo '<li>' . htmlspecialchars($instruction) . '</li>';
                  }
              }
              ?>
            </ol>
          </div>
        </div>
      </div>

      <div class="recipe-review">
        <div class="review-container">
          <div class="review-left">
            <form method="post">
              <label for="rating-num" class="rating-label">Rate this dish:</label>
              <input type="number" class="rating-input" name="rating-num" id="rating-num" min="1" max="5" required value="<?php echo $user_rating['rating'] ?? ''; ?>" <?php echo $user_rating ? 'disabled' : ''; ?>>
              <input type="submit" class="rating-submit" name="rating" id="rating" value="Rate" <?php echo $user_rating ? 'disabled' : ''; ?>>
            </form>
            <p class="review-rating"><?php echo round($recipe['average_rating'], 1); ?>/5<ion-icon name="star-sharp"></ion-icon></p>
          </div>
          <form method="post">
            <input type="submit" name="favorites" id="favorites" value="<?php echo $is_favorite ? 'Remove from favorites' : 'Add to favorites'; ?>" class="<?php echo $is_favorite ? 'active-submit rating-submit' : 'rating-submit'; ?>">
          </form>
        </div>
        <div class="comment-container">
          <form method="post">
            <textarea class="comment-input" id="comment-text" name="comment-text" required></textarea>
            <input type="submit" class="rating-submit" name="comment" id="comment" value="Comment">
          </form>

          <?php
          if ($comments_result->num_rows > 0) {
              while ($comment_row = $comments_result->fetch_assoc()) {
                  echo '<div class="comment">';
                  echo '<p class="comment-author">' . htmlspecialchars($comment_row['username']) . '</p>';
                  echo '<p class="comment-text">' . htmlspecialchars($comment_row['content']) . '</p>';
                  echo '<p class="comment-date">' . htmlspecialchars($comment_row['created_at']) . '</p>';
                  echo '</div>';
              }
          } else {
              echo '<p>No comments yet.</p>';
          }
          ?>
        </div>
      </div>
    </div>
  </main>

  <!-- Ionic icons -->
  <script type="module" src="https://unpkg.com/ionicons@7.1.0/dist/ionicons/ionicons.esm.js"></script>
  <script nomodule src="https://unpkg.com/ionicons@7.1.0/dist/ionicons/ionicons.js"></script>
</body>
</html>