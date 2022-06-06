<?php
    $page_title = "ICE";
    include("header_incl.php");

    // This page should probably use GET instead of POST.

    // If use_manual_ingredient_list is not set, it means
    // we got here in an incorrect way (e.g. manually typed
    // the URL of this page).
    // For now we just redirect you to the search page.
    if (!isset($_POST["use_manual_ingredient_list"])) {
        header("Location: /ice");
    }

    $custom_search = $_POST["use_manual_ingredient_list"];

    $ingr_ids = [];

    if ($custom_search) {
        $ingr_names = $_SESSION["ingredient_list"] ?? [];

        if (count($ingr_names) > 0) {
            $query = "select id from ingredients where ";
            foreach ($ingr_names as $name) {
                $query .= "name='" . $name . "'" . " || ";
            }
            $query = substr($query, 0, strlen($query) - strlen(" || "));

            $result = mysqli_query($db, $query);
            $data   = mysqli_fetch_all($result, MYSQLI_ASSOC);

            foreach ($data as $it) {
                array_push($ingr_ids, $it["id"]);
            }
        }
    }
    else { // use fridge instead
        if (user_is_signed_in()) {
            $query  = "select ingredient_id id FROM user_ingredients WHERE user_id=" . $_SESSION["user_id"];
            $result = mysqli_query($db, $query);
            $data   = mysqli_fetch_all($result, MYSQLI_ASSOC);

            foreach ($data as $it) {
                array_push($ingr_ids, $it["id"]);
            }
        }
    }


    $required_ingredient_count_for_each_recipe = [];
    {
        $query = "select recipe_id, count(ingredient_id) cnt from recipe_ingredient_list, recipes where recipe_id=recipes.id && approved=1 group by recipe_id";
        $result = mysqli_query($db, $query);
        $data   = mysqli_fetch_all($result, MYSQLI_ASSOC);

        foreach ($data as $it) {
            $required_ingredient_count_for_each_recipe[$it["recipe_id"]] = $it["cnt"];
        }

        // echo "<div>" . "req res: " . print_r($required_ingredient_count_for_each_recipe, true) . "</div>";
    }


    $present_ingredient_count_for_each_recipe = [];

    if (count($ingr_ids) > 0) {
        $query = "select recipe_id, count(ingredient_id) cnt from recipe_ingredient_list, recipes where recipe_id=recipes.id && approved=1 && (";
        foreach ($ingr_ids as $id) {
            $query .= "ingredient_id=" . $id . " || ";
        }
        $query = substr($query, 0, strlen($query) - strlen(" || "));
        $query .= ") group by recipe_id";

        // echo "<div>" . "q2: " . $query . "</div>";

        {
            $result = mysqli_query($db, $query);
            $data   = mysqli_fetch_all($result, MYSQLI_ASSOC);

            foreach ($data as $it) {
                $present_ingredient_count_for_each_recipe[$it["recipe_id"]] = $it["cnt"];
            }

            // echo "<div>" . "present res: " . print_r($present_ingredient_count_for_each_recipe, true) . "</div>";
        }
    }



    $recommended_recipe_ids = [];
    {
        // The user cannot have more than 3 missing ingredients,
        // and the missing ingredients cannot make up more
        // than 50% of the recipe.
        // This is done because there could be a weird situation,
        // e.g. the user only has 1/4 ingredients, but the recipe
        // still gets recommended.
        //
        // Overall, recipes with few ingredients are usually
        // discarded by $max_missing_ingr_fraction, but recipes
        // with many ingredients -- by $max_missing_ingr_count.
        $max_missing_ingr_count = 3;
        $max_missing_ingr_fraction = 0.50;

        foreach ($present_ingredient_count_for_each_recipe as $recipe_id => $present_count) {
            $required_count = $required_ingredient_count_for_each_recipe[$recipe_id];
            $missing_count  = $required_count - $present_count;
            $missing_frac   = $missing_count / $required_count;

            if ($missing_count <= $max_missing_ingr_count && $missing_frac <= $max_missing_ingr_fraction) {
                array_push($recommended_recipe_ids, [
                    "id" => $recipe_id,
                    "missing_count" => $missing_count,
                    "missing_frac"  => $missing_frac,
                ]);
            }
        }
    }

    // echo "<div>" . "recommended: " . print_r($recommended_recipe_ids, true) . "</div>";


    $recipe_display_list = [];
    if (count($recommended_recipe_ids) > 0) {
        foreach ($recommended_recipe_ids as $recipe) {
            $display_item = [];

            $display_item["id"]            = $recipe["id"];
            $display_item["missing_count"] = $recipe["missing_count"];
            $display_item["missing_frac"]  = $recipe["missing_frac"];

            {
                $query = "select display_name, title, body FROM recipes, users WHERE approved=1 && users.id = recipes.user_id && recipes.id=" . $recipe["id"];
                $result = mysqli_query($db, $query);
                $data   = mysqli_fetch_all($result, MYSQLI_ASSOC);

                assert(count($data) == 1);
                $data = $data[0];

                $display_item["user_name"] = $data["display_name"];
                $display_item["title"]     = $data["title"];
                $display_item["body"]      = $data["body"];
            }


            array_push($recipe_display_list, $display_item);
        }
    }

    // Sort the array such that we show the recipes which have all the required ingredients first.
    usort($recipe_display_list, function($a, $b) {
        return $a["missing_frac"] > $b["missing_frac"];
    });

    // echo "<pre>" . "recipe_display_list: " . print_r($recipe_display_list, true) . "</pre>";
?>


<div class="container ">



    <h1>Search results</h1>
    <p><?php echo $custom_search ? "(Custom search)" : "(Using ingredients from the fridge)" ?></p>

    <?php if (count($recipe_display_list) == 0): ?>
        <div style="text-align: center;">
        <img src="https://i.pinimg.com/originals/ef/4c/23/ef4c232dab28b7581497cee047f21969.gif">
        </div>
    <?php else: ?>
        <?php function shorten_body_str($s) {
            $max_length = 420;
            if (strlen($s) <= $max_length)
                return $s;
            return substr($s, 0, $max_length-3) . "...";
        }
        ?>
        <?php $have_started_to_output_partial_matches = false; ?>


        <?php foreach ($recipe_display_list as $index => $recipe):?>
        <div>
            <?php
                if ($recipe["missing_count"] > 0 && !$have_started_to_output_partial_matches) {
                    if ($index == 0) {
                        echo "<h3>No recipe was a perfect match</h3>";
                    }
                    else
                        echo "<h3>These recipes are almost possible...</h3>";
                    $have_started_to_output_partial_matches = true;
                }
            ?>
            <div class="recipe card">
                <div class="card-header">
                    <h2>
                        <a href="./recipe.php?id=<?php echo $recipe['id']; ?>"><?php echo $recipe["title"]; ?></a>
                    </h2>

                </div>
                <?php if ($recipe["missing_count"] > 0): ?>

                
                <div>
                    <?php
                        $t = $recipe["missing_count"] == 1 ? "ingredient" : "ingredients"; // Sigh.
                        echo "(" . $recipe["missing_count"] . " " . $t . " missing)";
                    ?>
                </div>
            
            
                <?php endif; ?>

                <div>
                    <?php echo shorten_body_str($recipe["body"]); ?>
                </div>

                <div class="card-footer text-muted">
                    by <?php echo $recipe["user_name"]; ?>
                </div>
            </div>
        </div>
        <?php endforeach;?>

</div>

    <?php endif;?>
<?php
    include("footer_incl.php");
?>