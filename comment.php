<?php
if (isset($_POST['comment'])) {
    $comment = $_POST['comment'];
    echo "<div class='comment-display'>Votre commentaire : " . $comment . "</div>";
}
?>
<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Commentaires</title>
    <style>
        body {
            font-family: 'Arial', sans-serif;
            background-color: #f5f5f5;
            margin: 0;
            padding: 20px;
            color: #333;
        }
        .container {
            max-width: 600px;
            margin: 0 auto;
            background-color: white;
            padding: 30px;
            border-radius: 8px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
        }
        h1 {
            color: #4a6eb5;
            margin-top: 0;
        }
        .form-group {
            margin-bottom: 20px;
        }
        label {
            display: block;
            margin-bottom: 8px;
            font-weight: bold;
        }
        input[type="text"] {
            width: 100%;
            padding: 12px;
            border: 1px solid #ddd;
            border-radius: 4px;
            box-sizing: border-box;
            font-size: 16px;
        }
        button {
            background-color: #4a6eb5;
            color: white;
            border: none;
            padding: 12px 20px;
            border-radius: 4px;
            cursor: pointer;
            font-size: 16px;
            transition: background-color 0.3s;
        }
        button:hover {
            background-color: #3b5998;
        }
        .comment-display {
            margin-top: 20px;
            padding: 15px;
            background-color: #f0f4f8;
            border-left: 4px solid #4a6eb5;
            border-radius: 4px;
        }
    </style>
</head>
<body>
    <div class="container">
        <h1>Espace de commentaires</h1>
        
        <form method="POST">
            <div class="form-group">
                <label for="comment">Laissez un commentaire :</label>
                <input type="text" id="comment" name="comment" placeholder="Votre commentaire ici...">
            </div>
            <button type="submit">Envoyer</button>
        </form>
    </div>
</body>
</html>