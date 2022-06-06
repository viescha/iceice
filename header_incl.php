<?php
    include_once("config/basic.php");
?>


<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.0.2/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-EVSTQN3/azprG1Anm3QDgpJLIm9Nao0Yz1ztcQTwFspd3yD65VohhpuuCOmLASjC" crossorigin="anonymous">
    <title><?php echo $page_title ?? null ?></title>

    <!-- <meta name="google-signin-client_id" content="683908291585-s81ljkdrr7h9jib8p6nrvl65fh0ss6me.apps.googleusercontent.com"> -->
    <!-- <meta name="google-signin-plugin_name" content="FUCK_YOU_GOOGLE_YOU_PIECE_OF_GARBAGE"> -->
    <link href="css/styles.css" rel="stylesheet">
</head>

<body>
    <nav class="autohide navbar fixed-top navbar-expand-md navbar-light bg-light mb-4">
        <div class="container">
            <a class="navbar-brand" href="./">
                <img id="navbarLogo" src="uploads\logo\logo1.png" alt="ICE">
            </a>
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse" id="navbarNav">
                <ul class="navbar-nav ms-auto">
                    <li class="nav-item">
                        <a class="nav-link" href="./">Home</a>
                    </li>

                    <?php if (user_is_signed_in()): ?>
                        <li class="nav-item">
                            <a class="nav-link" href="./addrecipe">Add Recipe</a>
                        </li>

                        <li class="nav-item">
                            <a class="nav-link" href="./fridge">My Fridge</a>
                        </li>

                        <li class="nav-item">
                            <a class="nav-link" href="#" onclick="google_sign_out()">Sign out</a>
                        </li>
                    <?php else: ?>
                        <li class="nav-item">
                            <a class="nav-link" href="#" id="googleSignInElement">Sign in</a>
                        </li>
                    <?php endif; ?>
                </ul>

                <?php if (user_is_signed_in()): ?>
                <?php   echo "Hello, " . $_SESSION["display_name"] ?>
                <?php endif; ?>
            </div>
        </div>
    </nav>

    <main>
        
