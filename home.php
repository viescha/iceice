
<?php
    $page_title = "ICE";
    include("header_incl.php");

    $time_string = date("h:m:s");

    $tried_to_edit_list = false;

    if (!isset($_SESSION["ingredient_list"]))
        $_SESSION["ingredient_list"] = [];

    $failed_to_add_msg = null;

    if (isset($_POST["clear_ingredients"])) {
        $_SESSION["ingredient_list"] = [];
        $tried_to_edit_list = true;
    }
    elseif (isset($_POST["remove_ingredient_by_name"])) {
        $name = $_POST["remove_ingredient_by_name"];

        $index = array_search($name, $_SESSION["ingredient_list"], true);

        if ($index !== false) { // Apparently 0 == false. This is why retards shouldn't be making programming languages.
            array_splice($_SESSION["ingredient_list"], $index, 1);
        }
    }
    else {
        $ingr_to_add = $_POST["ingredient_name_to_add"] ?? null;

        if ($ingr_to_add) {
            $tried_to_edit_list = true;

            $query  = "select id from ingredients where name='" . $ingr_to_add . "'";
            $result = mysqli_query($db, $query);
            $data   = mysqli_fetch_all($result, MYSQLI_ASSOC);

            if (empty($data)) {
                $failed_to_add_msg = "No ingredient '$ingr_to_add' found!";
            }
            else {
                if (!in_array($ingr_to_add, $_SESSION["ingredient_list"])) {
                    array_push($_SESSION["ingredient_list"], $ingr_to_add);
                }
            }
        }
    }

    $show_manual_ingredient_editor = true;
    if (user_is_signed_in()) {
        $show_manual_ingredient_editor = $tried_to_edit_list  ||  count($_SESSION["ingredient_list"]) > 0;
    }

?>
<div class="container contentWrapper">


    <h1 style="margin-left: -80%; margin-bottom: -40px; font-size:3vw;">Hello,</h1>
    <h1><strong><span style="font-family: 'Product Sans',sans-serif;font-size:12vw;"><span class="g-blue">G</span><span
        class="o-red">o</span><span class="o-yellow">u</span><span class="g-blue">r</span><span
        class="l-green">m</span><span class="o-red e-red">e</span><span class="o-yellow">t</span></span></strong>
    </h1>

    <p style="width: 100%; text-align: right;">The time is 
        <span id="clock" onload="currentTime()"></span>
    </p>
    
    <script src="./js/clock.js"></script>

    <div>
        <button type="button" class="btn btn-outline-primary" onclick="do_search()">I'm feeling HUNGRY</button>
    </div>


    <script src="./js/search.js"></script>

    <div class="ingredientsForm">
        <input
            type="checkbox"
            id="useFridgeCheckbox"
            onclick="update_visibility_of_manual_ingredient_selection()"
            <?php echo $show_manual_ingredient_editor ? "checked" : null; ?>
            <?php echo user_is_signed_in() ? null : "disabled"; ?>
        >
        <label
            for="useFridgeCheckbox"
        >
            Manually enter ingredients
        </label>
    </div>

    <div
        id="manualIngredientEditContainer"
        <?php echo $show_manual_ingredient_editor ? null : 'class="d-none"' ?>
    >
        <form method="POST">
            <input class="btn btn-sm btn-light" list="ingredientList" name="ingredient_name_to_add" id="ingredientName" autofocus>
            <datalist id="ingredientList">

                <?php
                    $possible_ingredients = null;
                    {
                        $query  = "select name from ingredients";
                        $result = mysqli_query($db, $query);
                        $possible_ingredients = mysqli_fetch_all($result, MYSQLI_ASSOC);
                    }

                    foreach ($possible_ingredients as $ingr) {
                        echo '<option value="' . $ingr["name"] . '">';
                    }
                ?>
            </datalist>
            
            <input class="btn btn-sm btn-primary" type="submit" name="submit" value="Submit">
            <input class="btn btn-sm btn-outline-primary" type="submit" name="clear_ingredients" value="Clear List">
        </form>


        <?php if ($failed_to_add_msg): ?>
            <div class="alert alert-danger">
                <?php echo $failed_to_add_msg; ?>
            </div>
        <?php endif; ?>

        <div id="ingredientList" >
            <?php
                $ingr_list = $_SESSION["ingredient_list"] ?? null;
                if ($ingr_list) {
                    foreach ($ingr_list as $ingr) {
                        $remove_html = "<a " .
                            "class='text-decoration-none text-reset' href='#'" .
                            "onclick='remove_ingredient_by_name(\"". $ingr . "\")'>🗙</a>";

                        echo "<div>" . $ingr . " " . $remove_html . "</div>";
                    }
                }
            ?>
        </div>
    </div>


    <!-- An invisible form gets created as a child of this element for submitting a POST request -->
    <div id="secretFormContainer" style="display: none;">

    </div>
    
<?php
    include("footer_incl.php");
?>