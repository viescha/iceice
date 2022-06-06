<?php
    include("config/basic.php");

    $post_json_text     = file_get_contents('php://input');
    $registration_data  = json_decode($post_json_text, true);

    $action = $registration_data["action"];

    switch ($action) {
        case "login": {
            // from :DoGoogleLogin

            $display_name = htmlspecialchars($registration_data["display_name"]);
            $google_id    = $registration_data["google_id"];

            // Either we find the user by their Google ID in the DB or we make a new account.

            // In the docs, the google_id value has a disclaimer "Do not send to your backend!".
            // (Because the client could change it.)
            // Oh well.

            $user = find_user_by_google_id($google_id);

            if (!$user) {
                $query  = "insert into users (display_name, google_id_which_you_are_not_supposed_to_store_in_your_database, is_admin, is_trusted) VALUES('$display_name', '$google_id', false, false)";
                $result = mysqli_query($db, $query);

                if ($result) {
                    $user = find_user_by_google_id($google_id);
                }
                else {
                    error_log("Couldn't insert ['$display_name', '$google_id'] into DB!");
                }
            }

            if ($user) {
                $_SESSION["user_id"]         = $user["id"];
                $_SESSION["display_name"]    = $user["display_name"];
                $_SESSION["user_is_trusted"] = $user["is_trusted"];
            }
        } break;


        case "logout": {
            // Clear user data
            unset($_SESSION["user_id"]);
            unset($_SESSION["display_name"]);
            unset($_SESSION["user_is_trusted"]);
        } break;


        default: error_log("Unknown account control action!");
    }





    function find_user_by_google_id($gid)
    {
        global $db;

        $query  = "select * from users where google_id_which_you_are_not_supposed_to_store_in_your_database=" . $gid;
        $result = mysqli_query($db, $query);
        $data   = mysqli_fetch_all($result, MYSQLI_ASSOC);

        if (!empty($data)) {
            assert(count($data) == 1);
            return $data[0];
        }

        return null;
    }
?>