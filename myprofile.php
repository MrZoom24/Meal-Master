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
    $sql = "SELECT recipes.recipe_id, recipes.name AS recipe_name, recipes.description, images.path AS image_path, 
                          recipes.ingredients, recipes.instruction, users.username, AVG(reviews.rating) AS average_rating
                    FROM recipes
                    JOIN images ON recipes.recipe_id = images.recipe_id
                    JOIN users ON recipes.user_id = users.user_id
                    LEFT JOIN reviews ON recipes.recipe_id = reviews.recipe_id
                    WHERE recipes.user_id = $user_id
                    GROUP BY recipes.recipe_id, images.path, users.username";
    $result = $conn->query($sql);
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
  <link href="./css/myprofile.css?v=<?php echo time(); ?>" rel="stylesheet" type="text/css" />
  <title>My Profile</title>
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
        <a class="navbar-element" href="favorites.php">
          <ion-icon name="star-sharp" class="navbar-icon"></ion-icon>
          <p class="navbar-text">Favorites</p>
        </a> 
        <a class="navbar-element navbar-element-active" href="myprofile.php">
          <ion-icon name="person-sharp" class="navbar-icon"></ion-icon>
          <p class="navbar-text">My Profile</p>
        </a> 
      </div>
    </div>
  </nav>
  
  <main class="main">
    <div class="divider">
      <span>MY RECIPES</span>
    </div>
    
    <div class="main-container">
      <div class="card card-add">
        <ion-icon name="add-circle-sharp" class="card-icon"></ion-icon>
        <div class="card-description">
          <p class="card-name card-name-big">Add a Recipe</p>
        </div>
      </div>

      <?php
      if ($result->num_rows > 0) {
        while($row = $result->fetch_assoc()) {
          echo '<div class="card">';
          echo '<img src="' . $row["image_path"] . '" class="card-image">';
          echo '<div class="card-description">';
          echo '<p class="card-author">' . $row["username"] . '</p>';
          echo '<p class="card-rating">' . round($row["average_rating"], 1) . '/5<ion-icon name="star-sharp"></ion-icon></p>';
          echo '<p class="card-name">' . $row["recipe_name"] . '</p>';
          echo '<p class="card-text">' . $row["description"] . '</p>';
          echo '<a class="card-button" href="recipe.php?recipe_id=' . $row["recipe_id"] . '">READ MORE</a>';
          echo '<p class="card-button">EDIT</p>';
          echo '<p class="card-button">DELETE</p>';
          echo '</div>';
          echo '</div>';
        }
        }
      ?>
    </main>
  </main>
  
  <!-- Ionic icons -->
  <script type="module" src="https://unpkg.com/ionicons@7.1.0/dist/ionicons/ionicons.esm.js"></script>
  <script nomodule src="https://unpkg.com/ionicons@7.1.0/dist/ionicons/ionicons.js"></script>

  <!-- Modal Structure -->
<div id="recipeModal" class="modal">
  <div class="modal-content">
    <span class="close">&times;</span>
    <form id="recipeForm" action="handle_recipe.php" method="post" enctype="multipart/form-data">
      <input type="hidden" name="recipe_id" id="recipe_id">
      <label for="image">Recipe Image</label>
      <input type="file" name="image" id="image">
      <label for="name">Recipe Name</label>
      <input type="text" name="name" id="name" required>
      <label for="description">Description</label>
      <textarea name="description" id="description" required></textarea>
      <label for="ingredients">Ingredients</label>
      <textarea name="ingredients" id="ingredients" required></textarea>
      <label for="instruction">Instruction</label>
      <textarea name="instruction" id="instruction" required></textarea>
      <label for="category">Category</label>
      <select name="category" id="category" required>
        <?php
        $categories_result = $conn->query("SELECT category_id, name FROM categories");
        while ($category = $categories_result->fetch_assoc()) {
          echo '<option value="' . $category['category_id'] . '">' . $category['name'] . '</option>';
        }
        ?>
      </select>
      <button type="submit" id="submitButton">Add Recipe</button>
    </form>
  </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', (event) => {
  const modal = document.getElementById("recipeModal");
  const addBtn = document.querySelector(".card-add");
  const span = document.getElementsByClassName("close")[0];
  const form = document.getElementById("recipeForm");
  const submitButton = document.getElementById("submitButton");

  addBtn.onclick = function() {
    modal.style.display = "block";
    form.reset();
    submitButton.textContent = "Add Recipe";
    form.action = "handle_recipe.php?action=add";
  }

  span.onclick = function() {
    modal.style.display = "none";
  }

  window.onclick = function(event) {
    if (event.target == modal) {
      modal.style.display = "none";
    }
  }

  document.querySelectorAll('.card-button').forEach(button => {
    button.addEventListener('click', function() {
      if (this.textContent === 'EDIT') {
        const card = this.closest('.card');
        const recipeId = card.querySelector('a').href.split('recipe_id=')[1];
        fetchRecipeData(recipeId);
        modal.style.display = "block";
        submitButton.textContent = "Edit Recipe";
        form.action = "handle_recipe.php?action=edit";
      } else if (this.textContent === 'DELETE') {
        const recipeId = this.closest('.card').querySelector('a').href.split('recipe_id=')[1];
        if (confirm('Are you sure you want to delete this recipe?')) {
          window.location.href = 'handle_recipe.php?action=delete&recipe_id=' + recipeId;
        }
      }
    });
  });

  function fetchRecipeData(recipeId) {
    fetch('get_recipe.php?recipe_id=' + recipeId)
      .then(response => response.json())
      .then(data => {
        document.getElementById('recipe_id').value = data.recipe_id;
        document.getElementById('name').value = data.name;
        document.getElementById('description').value = data.description;
        document.getElementById('ingredients').value = data.ingredients;
        document.getElementById('instruction').value = data.instruction;
        document.getElementById('category').value = data.category_id;
      });
  }
});
</script>

</body>
</html>