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

$action = isset($_GET['action']) ? $_GET['action'] : '';

if ($action === 'add' && isset($_FILES['image'])) {
    $user_id = $_SESSION['user_id'];
    $name = $_POST['name'];
    $description = $_POST['description'];
    $ingredients = $_POST['ingredients'];
    $instruction = $_POST['instruction'];
    $category_id = $_POST['category'];

    $target_dir = "uploads/";
    $target_file = $target_dir . basename($_FILES["image"]["name"]);
    move_uploaded_file($_FILES["image"]["tmp_name"], $target_file);

    $stmt = $conn->prepare("INSERT INTO recipes (user_id, category_id, name, description, ingredients, instruction) VALUES (?, ?, ?, ?, ?, ?)");
    $stmt->bind_param("iissss", $user_id, $category_id, $name, $description, $ingredients, $instruction);
    $stmt->execute();

    $recipe_id = $stmt->insert_id;

    $stmt = $conn->prepare("INSERT INTO images (recipe_id, path) VALUES (?, ?)");
    $stmt->bind_param("is", $recipe_id, $target_file);
    $stmt->execute();

    header("Location: myprofile.php");
    exit();
}

if ($action === 'edit' && isset($_POST['recipe_id'])) {
    $recipe_id = $_POST['recipe_id'];
    $name = $_POST['name'];
    $description = $_POST['description'];
    $ingredients = $_POST['ingredients'];
    $instruction = $_POST['instruction'];
    $category_id = $_POST['category'];

    $stmt = $conn->prepare("UPDATE recipes SET category_id = ?, name = ?, description = ?, ingredients = ?, instruction = ? WHERE recipe_id = ?");
    $stmt->bind_param("issssi", $category_id, $name, $description, $ingredients, $instruction, $recipe_id);
    $stmt->execute();

    if (isset($_FILES['image']) && $_FILES['image']['error'] == UPLOAD_ERR_OK) {
        $target_dir = "uploads/";
        $target_file = $target_dir . basename($_FILES["image"]["name"]);
        move_uploaded_file($_FILES["image"]["tmp_name"], $target_file);

        $stmt = $conn->prepare("UPDATE images SET path = ? WHERE recipe_id = ?");
        $stmt->bind_param("si", $target_file, $recipe_id);
        $stmt->execute();
    }

    header("Location: myprofile.php");
    exit();
}

if ($action === 'delete' && isset($_GET['recipe_id'])) {
    $recipe_id = $_GET['recipe_id'];

    $stmt = $conn->prepare("DELETE FROM images WHERE recipe_id = ?");
    $stmt->bind_param("i", $recipe_id);
    $stmt->execute();

    $stmt = $conn->prepare("DELETE FROM recipes WHERE recipe_id = ?");
    $stmt->bind_param("i", $recipe_id);
    $stmt->execute();

    header("Location: myprofile.php");
    exit();
}
?>
