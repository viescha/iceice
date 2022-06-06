<?php // <body> ?>
   <?php // <main> ?>
       <?php // <div> ?>


        </div>
    </main>

    <footer class="copy-rights">
        Copyright &copy; 2022
    </footer>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.0.2/dist/js/bootstrap.bundle.min.js" integrity="sha384-MrcW6ZMFYlzcLA8Nl+NtUVF0sA7MsXsP1UyJoMp4YLEuNSfAP+JcXn/tWtIaxVXM" crossorigin="anonymous"></script>

    <script src="./js/google_scripts.js"></script>
    <script src="https://apis.google.com/js/api:client.js?onload=onLoadCallback" async defer></script>
    

    <script>
        var auth2;
        var googleUser = {};

        window.onLoadCallback = function() {
            gapi.load('auth2', function () {
                auth2 = gapi.auth2.init({
                    client_id: '683908291585-s81ljkdrr7h9jib8p6nrvl65fh0ss6me.apps.googleusercontent.com',
                    plugin_name: "FUCK_YOU_GOOGLE_YOU_PIECE_OF_GARBAGE",

                    cookiepolicy: 'single_host_origin',
                    // Request scopes in addition to 'profile' and 'email'
                    scope: 'profile email'
                });

                page_init();
            });
        }
    </script>
    <script>
  document.addEventListener("DOMContentLoaded", function(){

el_autohide = document.querySelector('.autohide');

// add padding-top to bady (if necessary)
navbar_height = document.querySelector('.navbar').offsetHeight;
document.body.style.paddingTop = navbar_height + 'px';

if(el_autohide){
  var last_scroll_top = 0;
  window.addEventListener('scroll', function() {
        let scroll_top = window.scrollY;
       if(scroll_top < last_scroll_top) {
            el_autohide.classList.remove('scrolled-down');
            el_autohide.classList.add('scrolled-up');
        }
        else {
            el_autohide.classList.remove('scrolled-up');
            el_autohide.classList.add('scrolled-down');
        }
        last_scroll_top = scroll_top;
  }); 
  // window.addEventListener
}
// if

}); 
</script>

    <div style="display: none;"><pre><?php echo "SESSION: " . print_r($_SESSION, true); ?></pre></div>
    <div style="display: none;"><pre><?php echo "POST: " . print_r($_POST, true); ?></pre></div>

    </body>
</html>
