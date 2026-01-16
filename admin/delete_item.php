<?php
session_start();
require_once "../db.php";

if (isset($_GET['id']) && $_SESSION['role'] === 'admin') {
    $id = $_GET['id'];
    $stmt = $pdo->prepare("DELETE FROM products WHERE id = ?");
    $stmt->execute([$id]);
}
header("Location: admin_products.php");
exit;