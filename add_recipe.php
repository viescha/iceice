
<?php
    $page_title = "My Account | ICE";
    include("header_incl.php");

    if (!user_is_signed_in()) {
        header("Location: /ice");
    }

    $failed_to_add_msg = null;

    $top_alert = null;

    if (!isset($_SESSION["new_recipe_ingredient_list"]))
        $_SESSION["new_recipe_ingredient_list"] = [];


    if (isset($_POST["submit_recipe"])) {
        $title       = $_POST["recipe_title"];
        $ingredients = $_SESSION["new_recipe_ingredient_list"];
        $user_id     = $_SESSION["user_id"];
        $text = $_POST["recipe_text"];

        $initial_approval_value = 0;
        if ($_SESSION["user_is_trusted"])
            $initial_approval_value = 1;

        {
            $query  = "select count(*) cnt from ingredients where ";
            foreach ($ingredients as $ingr)
                $query .= "name='" . $ingr["name"] . "'" . " || ";
            $query = substr($query, 0, strlen($query) - strlen(" || "));

            //echo "Q is " . $query;

            $result = mysqli_query($db, $query);
            $data   = mysqli_fetch_all($result, MYSQLI_ASSOC);

            assert(count($data) == 1);
            if ($data[0]["cnt"] == count($ingredients)) { // all of the ingredients were valid
                $query = "insert into recipes (id, user_id, title, body, approved) VALUES (NULL, '$user_id', '$title', '$text', '$initial_approval_value');";
                $result = mysqli_query($db, $query);

                if ($result === false) {
                    $top_alert = [];
                    $top_alert["kind"] = "danger";
                    $top_alert["text"] = "Couldn't add the recipe!";
                }
                else {
                    // there's probably a better way to do this.
                    $query  = "select max(id) id from recipes;";
                    $result = mysqli_query($db, $query);
                    $data   = mysqli_fetch_all($result, MYSQLI_ASSOC);
                    $new_recipe_id = $data[0]["id"];

                    $failed = false;
                    foreach ($ingredients as $ingr) {
                        $query = "insert into recipe_ingredient_list (recipe_id, ingredient_id, amount, units) VALUES ("
                            . $new_recipe_id    . ", "
                            . $ingr["id"]       . ", "
                            . $ingr["amount"]   . ", "
                            . "'" . $ingr["units"] . "');";
                        $result = mysqli_query($db, $query);

                        if(!$result) {
                            $failed = true;
                            break;
                            // we should do a rollback here.
                        }
                    }


                    if ($failed) {
                        $top_alert = [];
                        $top_alert["kind"] = "danger";
                        $top_alert["text"] = "Couldn't add the recipe!";
                    }
                    else {
                        $top_alert = [];
                        if ($initial_approval_value == 0) {
                            $top_alert["kind"] = "info";
                            $top_alert["text"] = "The recipe will be added after a trusted member reviews it!";
                        }
                        else {
                            $top_alert["kind"] = "success";
                            $top_alert["text"] = "Recipe successfully added!";
                        }
                    }

                }
            }
        }

        $_SESSION["new_recipe_ingredient_list"] = [];
        // unset($_POST["recipe_title"]);
        // unset($_POST["recipe_text"]);
    }
    elseif (isset($_POST["clear_ingredients"])) {
        $_SESSION["new_recipe_ingredient_list"] = [];
    }
    elseif (isset($_POST["remove_ingredient_by_name"])) {
        $name = $_POST["remove_ingredient_by_name"];

        $index = -1;
        foreach ($_SESSION["new_recipe_ingredient_list"] as $i => $ingr) {
            if ($ingr["name"] === $name) {
                $index = $i;
                break;
            }
        }

        if ($index != -1) {
            array_splice($_SESSION["new_recipe_ingredient_list"], $index, 1);
        }
    }
    else {
        $new_name   = $_POST["new_ingr_name"] ?? null;
        $new_amount = $_POST["new_ingr_amount"] ?? null;
        $new_units  = $_POST["new_ingr_units"] ?? null;

        if (!is_numeric($new_amount)) {
            if ($new_amount !== null) // meaning it was set
                $failed_to_add_msg = "You must enter a valid amount!";
        }
        else if ($new_name) {
            $query  = "select id from ingredients where name='" . $new_name . "'";
            $result = mysqli_query($db, $query);
            $data   = mysqli_fetch_all($result, MYSQLI_ASSOC);

            if (empty($data)) {
                $failed_to_add_msg = "No ingredient '$new_name' found!";
            }
            else {
                $new_id = $data[0]["id"];

                $already_present = false;
                foreach ($_SESSION["new_recipe_ingredient_list"] as $ingr) {
                    if ($ingr["name"] == $new_name) {
                        $already_present = true;
                        break;
                    }
                }

                if (!$already_present) {
                    array_push($_SESSION["new_recipe_ingredient_list"], [
                        "id"     => $new_id,
                        "name"   => $new_name,
                        "amount" => $new_amount,
                        "units"  => $new_units == "count" ? null : $new_units,
                    ]);
                }
            }
        }
    }
?>
<div class="container ">
<div class="addrecform">


    <h1 class="mb-5" style="text-align: left;">Add a new recipe</h1>

    <?php if ($top_alert): ?>
        <div class="alert alert-<?php echo $top_alert["kind"] ?>">
            <?php echo $top_alert["text"] ?>
        </div>
    <?php endif; ?>

    <?php
    // The hacky way of preserving recipe title and body
    // between ingredient POST submissions is just to send the
    // values of title and body too.
    //
    // This was the simplest way to do it, even though it's gross.
    ?>
    <form class="g-3 needs-validation"  method="POST">
        <!-- Title -->

        <div class="col mt-2" style="text-align: left;">
            <h4 style="text-align: left;">
                 <label for="recipeTitle" class="d-block">Title</label>
            </h4>
           
            <input class="col-4" id="recipeTitle" class="d-block" type="text" name="recipe_title" value="<?php echo $_POST['recipe_title'] ?? null ?>">
        </div>
        
        <!-- Ingredient to add -->
        <label style="text-align: left;" class="d-block mt-4" for="ingredientName">Add ingredient</label>
        <div class="row">
            <div class="col-sm" style="text-align: left;">
                <input class="col-8 m-2" list="possibleIngredientList" name="new_ingr_name" id="ingredientName" placeholder="Ingredient name">
                <datalist id="possibleIngredientList">
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

                <input class="col-8 m-2" name="new_ingr_amount" type="text" placeholder="Ingredient amount">
                <select class="col-8 m-2" style="text-align: left;" name="new_ingr_units">
                    <option value="" disabled selected>Select units</option>
                    <option value="count">(count)</option>
                    <option value="g">g</option>
                    <option value="kg">kg</option>
                    <option value="mL">mL</option>
                    <option value="L">L</option>
                    <option value="tbsp">tbsp</option>
                </select>

                <!-- Submit button for the ingredient addition. Also submits title and body. -->
                <input class="col-4 btn-sm btn-primary" type="submit" name="submit" value="Add">
                <input class="col-4 btn-sm btn-outline-primary" type="submit" name="clear_ingredients" value="Clear">

                <!-- Warning for invalid input -->
                <?php if ($failed_to_add_msg): ?>
                    <div class="alert alert-danger">
                        <?php echo $failed_to_add_msg; ?>
                    </div>
                <?php endif; ?>
            </div>
            <div class="col">
            
                  <!-- The list of ingredients already added. This is not a part of the form (it doesn't get POSTed). -->
                <div id="ingredientList">
                    <?php $ingr_list = $_SESSION["new_recipe_ingredient_list"] ?? []; ?>
                    <?php foreach ($ingr_list as $ingr): ?>
                    <?php
                        $name   = $ingr["name"];
                        $amount = $ingr["amount"];
                        $units  = $ingr["units"];
                    ?>
                        <div class="recipeIngredientContainer" data-ingr-name='<?php echo $name ?>'>
                            <span><?php echo $name . " (" . format_ingredient_amount_text($amount, $units) . ")" ?></span>
                            <a class='text-decoration-none text-reset' href='#' onclick='remove_ingredient_by_name("<?php echo $name ?>")'>🗙</a>
                        </div>
                    <?php endforeach; ?>
                </div>  

            </div>
        </div>
        

        

        

        <!-- Recipe body -->
        <label style="text-align: left;" for="recipeText" class="d-block" >Instructions</label>
        <textarea id="recipeText" class="d-block" name="recipe_text"  rows="10"><?php echo $_POST['recipe_text'] ?? null ?></textarea>
    
    </form>

    <button class="col btn-sm btn-primary m-2" onclick="submit_recipe()">Submit</button>



    <!-- An invisible form gets created as a child of this element for submitting a POST request -->
    <div id="secretFormContainer" style="display: none;">
    </div>
</div>
    <script type="text/javascript">
        function remove_ingredient_by_name(ingr_name) {
            var parent = document.getElementById("secretFormContainer");

            // preserve title and body.
            title = document.getElementById("recipeTitle").value;
            text  = document.getElementById("recipeText").value;

            parent.innerHTML =
                "<form method='POST'>" +
                    "<input type='text' name='remove_ingredient_by_name' value='" + ingr_name + "'>" +

                    "<input type='text' name='recipe_title' value='" + title + "'>" +
                    "<input type='text' name='recipe_text' value='" + text  + "'>" +
                "</form>";
            var the_form = parent.firstChild;
            the_form.submit();
        }

        function submit_recipe() {
            var parent = document.getElementById("secretFormContainer");

            title = document.getElementById("recipeTitle").value;
            text  = document.getElementById("recipeText").value;

            parent.innerHTML =
                "<form method='POST'>" +
                    "<input type='text' name='submit_recipe'>" +

                    "<input type='text' name='recipe_title' value='" + title + "'>" +
                    "<input type='text' name='recipe_text' value='" + text  + "'>" +
                "</form>";
            var the_form = parent.firstChild;
            the_form.submit();
        }
    </script>

<?php
    include("footer_incl.php");
?>