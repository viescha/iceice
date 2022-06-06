<?php
//
// Database stuff
//
define("DB_HOST", "localhost");
define("DB_USER", "icebaby");
define("DB_PASS", "1234");
define("DB_NAME", "iceicebaby");

$db = new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME);


if ($db->connect_error) {
    die("DB connection failed. Error:" . $db->connect_error);
}


session_start();


function user_is_signed_in()
{
    return isset($_SESSION["user_id"]);
}

function format_ingredient_amount_text($amount, $units) {
    if ($units)
        return $amount . " " . $units;
    return "×" . $amount;
}

?>
