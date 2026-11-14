<?php
include 'db_connect.php';
$id = intval($_GET['id']);
mysqli_query($conn, "DELETE FROM reservations WHERE id = $id");
header("Location: index.php");
exit();
?>