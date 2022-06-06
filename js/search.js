console.log("search loaded");


function update_visibility_of_manual_ingredient_selection()
{
    // This procedure gets called after the checkbox.checked is
    // updated to the new value.

    var checkbox = document.getElementById("useFridgeCheckbox");

    var container = document.getElementById("manualIngredientEditContainer");

    if (checkbox.checked) {
        container.classList.remove("d-none");
    }
    else {
        container.classList.add("d-none");
    }

}


function remove_ingredient_by_name(ingr_name) {
    var parent = document.getElementById("secretFormContainer");

    parent.innerHTML =
        "<form method='POST'>" +
            "<input type='text' name='remove_ingredient_by_name' value='" + ingr_name + "'>" +
        "</form>";
    var the_form = parent.firstChild;
    the_form.submit();
}


function do_search()
{
    var parent = document.getElementById("secretFormContainer");

    var form_value = document.getElementById("useFridgeCheckbox").checked ? "1" : "0";

    parent.innerHTML =
        "<form method='POST' action='search.php'>" +
            "<input type='text' name='use_manual_ingredient_list' value='" + form_value + "'>" +
        "</form>";
    var the_form = parent.firstChild;
    the_form.submit();
}