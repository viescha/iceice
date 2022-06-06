<?php
    include("config/basic.php");

    $recipe_id = $_GET["id"] ?? null;
    //@@TODO if this recipe exists and it is not approved show something special

    if (!$recipe_id) {
        exit("No recipe ID specified!");
    }


    $recipe = null;


    // Get basic info
    {
        $query = "select title, body, display_name author_display_name FROM recipes, users WHERE users.id = recipes.user_id && recipes.id=" . $recipe_id;
        $result = mysqli_query($db, $query);
        $data   = mysqli_fetch_all($result, MYSQLI_ASSOC);

        if (count($data) != 1)
            exit("Invalid recipe ID");

        $recipe = $data[0];
    }

    // Get the ingredients
    {
        $query = "select ingredients.name name, amount, units FROM ingredients, recipe_ingredient_list l WHERE l.ingredient_id=ingredients.id && recipe_id=" . $recipe_id;
        $result = mysqli_query($db, $query);
        $ingr_list = mysqli_fetch_all($result, MYSQLI_ASSOC);

        $recipe["ingredients"] = $ingr_list;
    }

    // echo "<pre>" . print_r($recipe, true) . "</pre>";


    $page_title = $recipe["title"] . " | ICE";
    include("header_incl.php");
?>
<div class="container ">
<div class="card">
    <div class="card-header">
        <h1>
            <?php echo $recipe["title"] ?>
        </h1>
        <div>
            Author: <?php echo $recipe["author_display_name"] ?>
        </div>
    </div>
    <div class="card-footer">
        <h2>Ingredients</h2>

        <?php foreach($recipe["ingredients"] as $ingr): ?>
        <div>
            <?php
                echo $ingr["name"] . " (" . format_ingredient_amount_text($ingr["amount"], $ingr["units"]) . ")";
            ?>
        </div>
        <?php endforeach; ?>
    </div>
    <div class="card-body">
        <h2>Instructions</h2>
        <?php
            // A double newline in the text field means a new paragraph.
            // Doing some actual formatting seems like an overkill for this garbage.

            // Deal with the stupid "\n" vs "\r\n" line-ending bullshit.
            $body = str_replace("\r\n", "\n", $recipe["body"]);
            $body = str_replace("\r", "\n", $body);

            $paragraphs = explode("\n\n", $body);

            // echo "<pre>" . print_r($things, true) . "</pre>";

            foreach ($paragraphs as $para) {
                echo "<p>" . $para . "</p>";
            }
        ?>
    </div>
    

    

    


</div>
    


    <!-- <div><?php echo $recipe["title"] ?></div> -->

</div>



<?php
    include("footer_incl.php");
?>