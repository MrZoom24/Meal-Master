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

$recipe_id = isset($_GET['recipe_id']) ? intval($_GET['recipe_id']) : 0;

if ($recipe_id > 0) {
    $stmt = $conn->prepare("SELECT recipe_id, name, description, ingredients, instruction, category_id FROM recipes WHERE recipe_id = ?");
    $stmt->bind_param("i", $recipe_id);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
        echo json_encode($result->fetch_assoc());
    } else {
        echo json_encode([]);
    }
} else {
    echo json_encode([]);
}
?>
