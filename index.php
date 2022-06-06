<?php

error_reporting(0);

switch ($_GET['filename']) {
    case 'addrecipe':
        include('add_recipe.php');
    break;
    case 'fridge':
        include('fridge.php');
    break;
    case '':
        include('home.php');
    break;
    default:
        header('HTTP/1.0 404 Not Found');
        include('page_not_found.php');
    break;
}
?>