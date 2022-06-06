console.log("google garbage loaded");


function page_init() {
    element = document.getElementById('googleSignInElement');

    if (!element)
        return;

    var on_google_sign_in_error = function(error) {
        console.log(JSON.stringify(error, undefined, 2));
    };

    auth2.attachClickHandler(element, {}, google_sign_in, on_google_sign_in_error);
}

function reload() {
    window.location.reload();
}

function google_sign_in(user) {
    var profile = user.getBasicProfile();

    // :DoGoogleLogin
    var xhr = new XMLHttpRequest();
    xhr.open("POST", "./account_control.php", true);
    xhr.setRequestHeader('Content-Type', 'application/json');
    xhr.send(JSON.stringify({
        action:       "login",
        display_name: profile.getGivenName(),
        google_id:    profile.getId()
    }));

    // Horrible hack
    setTimeout(reload, 500);
}


function google_sign_out() {
    console.log("signout attempt");

    // :DoGoogleLogout
    var xhr = new XMLHttpRequest();
    xhr.open("POST", "./account_control.php", true);
    xhr.setRequestHeader('Content-Type', 'application/json');
    xhr.send(JSON.stringify({
        action: "logout"
    }));

    // Hack.
    setTimeout(function() {
        window.location.href = "/ice";
    }, 500);
}
