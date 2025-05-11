<?php
$conn = new mysqli("localhost", "root", "", "vulnapp");
if ($conn->connect_error) {
    die("Connexion échouée : " . $conn->connect_error);
}
?>