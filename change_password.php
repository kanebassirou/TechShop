<?php
include 'db.php';
if (isset($_POST['new_password'])) {
    $new_password = $_POST['new_password'];
    $sql = "UPDATE users SET password = '$new_password' WHERE username = 'admin'";
    $conn->query($sql);
    echo "Mot de passe modifié !";
}
?>
<form method="POST">
    Nouveau mot de passe : <input type="text" name="new_password">
    <input type="submit" value="Changer">
</form>