
<?php
    $page_title = "My Fridge | ICE";
    include("header_incl.php");

    if (!user_is_signed_in())
        exit("User not signed in.");

    $failed_to_add_msg = null;
    $ingr_to_add = $_POST["ingredient_name_to_add"] ?? null;

    if ($ingr_to_add) {
        $query  = "select id from ingredients where name='" . $ingr_to_add . "'";
        $result = mysqli_query($db, $query);
        $data   = mysqli_fetch_all($result, MYSQLI_ASSOC);

        if (empty($data)) {
            $failed_to_add_msg = "No ingredient '$ingr_to_add' found!";
        }
        else {
            assert(count($data) == 1);
            $ingr_id = $data[0]["id"];

            $query  = "insert into user_ingredients (user_id, ingredient_id) VALUES (" . $_SESSION["user_id"] . ", " . $ingr_id . ")";
            try {
                $result = mysqli_query($db, $query);
            }
            catch (Exception $e) {
                $failed_to_add_msg = "Ingredient already added!"; // Probably. We might have other errors but I don't care.
            }
        }
    }


    $ingr_to_remove_id = $_POST["remove_ingredient_by_id"] ?? null;
    if ($ingr_to_remove_id) {
        $query  = "delete from user_ingredients WHERE user_id=" . $_SESSION["user_id"] . " && ingredient_id=" . $ingr_to_remove_id;
        $result = mysqli_query($db, $query);
    }


?>

<?php
//
// A lot of copy-paste from index.php
//
?>
<div class="container row" style="flex-direction: row-reverse;">
    <h1 class="col-12 mb-5" style="text-align: left;">My Fridge</h1>


    <form method="POST" class="col-7">
        <input list="ingredientList" name="ingredient_name_to_add" id="ingredientName">
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

        <input type="submit" name="submit" value="Add">
    </form>


    <div class="my-frige col-3">
        <?php if ($failed_to_add_msg): ?>
            <div class="alert alert-danger">
                <?php echo $failed_to_add_msg; ?>
            </div>
        <?php endif; ?>

        <div id="ingredientList" >
            <?php
                $ingredient_list = null;
                {
                    $query  = "select i.id, name FROM user_ingredients ui, ingredients i WHERE ui.ingredient_id=i.id && ui.user_id=" . $_SESSION["user_id"];
                    $result = mysqli_query($db, $query);
                    $ingredient_list = mysqli_fetch_all($result, MYSQLI_ASSOC);
                }

                foreach ($ingredient_list as $ingr) {
                    $remove_html = "<a class='text-decoration-none text-reset' href='#' onclick='remove_ingredient_from_fridge(" . $ingr["id"] . ")'>🗙</a>";
                    echo "<div>" . $ingr["name"] . $remove_html . "</div>";
                }
            ?>
        </div>
    </div>

    <!-- An invisible form gets created as a child of this element for submitting a POST request -->
    <div id="secretFormContainer" style="display: none;">
    </div>



    <script type="text/javascript">
        function remove_ingredient_from_fridge(ingr_id) {
            var parent = document.getElementById("secretFormContainer");

            parent.innerHTML =
                "<form method='POST'>" +
                    "<input type='text' name='remove_ingredient_by_id' value='" + ingr_id + "'>" +
                "</form>";
            var the_form = parent.firstChild;
            the_form.submit();
        }
    </script>
<?php
    include("footer_incl.php");
?>