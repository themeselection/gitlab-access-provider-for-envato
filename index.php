<?php

session_start();
// session_destroy();

$config = json_decode(file_get_contents("./config.json"), true);

function clear_error($field) {
  unset($_SESSION["form-error"][$field]);
}

function show_error($field) {
  $err_msg = "";

  if($_SESSION["form-error"][$field]) {
    $err_msg = $_SESSION['form-error'][$field];
    unset($_SESSION['form-error'][$field]);
  }

  return $err_msg;
}

function show_field_data($field) {
  $data = "";

  if($_SESSION['form-data'][$field]) {
    $data = $_SESSION['form-data'][$field];
    unset($_SESSION['form-data'][$field]);
  }

  return $data;
}

?>

<!DOCTYPE html>
<html lang="en">
  <head>
    <meta charset="UTF-8" />
    <title>GitLab Access</title>

    <!-- Meta Tags -->
    <meta charset="utf-8" />
    <meta
      name="viewport"
      content="width=device-width, initial-scale=1, shrink-to-fit=no"
    />

    <!-- Fonts -->
    <link
      href="https://fonts.googleapis.com/css?family=Cabin&display=swap"
      rel="stylesheet"
    />

    <link rel="stylesheet" href="./style.css">
  </head>
  <body>

      <div class="lds-ripple loading">
          <div></div>
          <div></div>
      </div>
      <div class="loading-bg"></div>
      <div class="container">
          <!-- Branding -->
          <h1 class="title"><?= $config["branding"]["title"]; ?></h1>
          <p class="subtitle"><?= $config["branding"]["subtitle_markup"]; ?></p>
          <!-- /Branding -->

          <div class="form-container">

              <!-- Global Messages -->
              <div class="form-error global">
                  <?php if(isset($_SESSION["form-error-global"])): ?>
                    <p><?= $_SESSION["form-error-global"] ?></p>
                    <?php unset($_SESSION['form-error-global']); ?>
                  <?php endif; ?>
              </div>
              <div class="form-success global">
                  <?php if(isset($_SESSION["form-success-global"])): ?>
                    <p><?= $_SESSION["form-success-global"] ?></p>
                    <?php unset($_SESSION['form-success-global']); ?>
                  <?php endif; ?>
              </div>
              <!-- /Global Messages -->

              <form id="request-form" method="post" action="process-form.php" onsubmit="return validate_form(this)">

                  <!-- requested_repo -->
                  <div class="form-group">
                      <label for="requested_repo" class="text-muted">GitLab Repository<span class="text-danger">*</span></label>
                      <input type="hidden" name="requested_repo" id="input-requested-repo" required />
                      <input type="hidden" name="product_id" id="input-product-id" required />
                      <div class="custom-select">
                          <p class="text-muted" id="custom-select-placeholder">Select Option</p>
                          <ul class="select-options"></ul>
                      </div>
                      <span class="form-error"><?= show_error("requested_repo"); ?></span>
                  </div>
                  <!-- /requested_repo -->

                  <!-- Email -->
                  <div class="form-group">
                      <label for="email" class="text-muted">Your Email<span class="text-danger">*</span></label>
                      <input value="<?= show_field_data('email'); ?>" type="email" name="email" required />
                      <span class="form-error"><?= show_error("email"); ?></span>
                  </div>
                  <!-- /Email -->

                  <!-- purchase_code -->
                  <div class="form-group">
                      <label for="purchase_code" class="text-muted">
                          <span>Envato Purchase Code<span class="text-danger">*</span></span>
                          <a target="_blank" href="https://help.market.envato.com/hc/en-us/articles/202822600-Where-Is-My-Purchase-Code-">How To Find Purchase Code?</a>
                      </label>
                      <input value="<?= show_field_data('purchase_code'); ?>" type="text" name="purchase_code" required />
                      <span class="form-error"><?= show_error("purchase_code"); ?></span>
                  </div>
                  <!-- /purchase_code -->
        
                  <!-- username -->
                  <div class="form-group">
                      <label for="username" class="text-muted">
                          <span>GitLab Username</span><span class="text-danger">*</span>
                          <span>(Don't use <code>@</code> in username)</span>
                      </label>
                      <input value="<?= show_field_data('username'); ?>" type="text" name="username" required />
                      <span class="form-error"><?= show_error("username"); ?></span>
                  </div>
                  <!-- /username -->

                  <div class="form-group">
                      <button type="submit" class="submitBtn">Submit</button>
                  </div>
              </form>
          </div>
      </div>

    <script>
      let products  = []
      let error_obj = {}
      let config    = null

      const custom_select        = document.querySelector(".custom-select")
      const select_options_ul    = document.querySelector(".select-options")
      const requested_repo_input = document.getElementById("input-requested-repo")
      const product_id_input     = document.getElementById("input-product-id")

      const loading_bg = document.querySelector(".loading-bg")
      const loading    = document.querySelector(".loading")

      fetch_config(load_config)

      custom_select.addEventListener("click", toggle_select_options)

      function fetch_config(callback) {
        const xhr = new XMLHttpRequest()
        xhr.overrideMimeType("application/json")
        xhr.open('GET', 'config.json', true)
        xhr.onreadystatechange = function() {
          if (xhr.readyState === 4) {
            if(xhr.status === 200) {
              callback(JSON.parse(xhr.responseText))
            }else {
              error_obj.requested_repo = "Error loading configurations!"
              error_obj.global = "Oops! There was error while displaying form. Please report us with received error message."
              displayErrors()
            }
          }
        }

        xhr.send(null)
      }

      function load_config(json) {
        config = json
        load_products()
      }

      function load_products() {

        fetch(config.api.products)
          .then((response) => response.json())
          .then(function(response) {

            if(response.length !== 0) {
              products = response
              render_products()
              // toggleLoading(false)
            }else {
              error_obj.requested_repo = "Error Fetching products details!"
              error_obj.global = config.messages.global_error
              displayErrors()
            }
          })
          .catch(function(err) {
            // console.log(JSON.parse(xhr.responseText).message)
            error_obj.requested_repo = "Error Fetching products details!"
            error_obj.global = config.messages.global_error
            displayErrors()
          })
      }

      // /////////////////////////////////////////////////////////////
      // UI Method: Render Product list from products var <- API
      // /////////////////////////////////////////////////////////////
      function render_products() {
        // generate list from products variable
        for(i=0;i<products.length;i++) {
          li_ele = document.createElement('li')
          li_ele.innerHTML = products[i].name
          li_ele.setAttribute("data-id", products[i].id)
          li_ele.setAttribute("data-envato-id", products[i].envato_id)
          li_ele.addEventListener("click", select_option)
          select_options_ul.appendChild(li_ele)
        }

        toggleLoading(false);
      }

      // /////////////////////////////////////////////////////////////
      // UI Method: When Option is selected from custom select
      // /////////////////////////////////////////////////////////////
      function select_option(e) {

        // Select Val in Custom-Select
        const select_label     = document.getElementById("custom-select-placeholder")
        select_label.innerHTML = e.target.innerHTML

        // Set val in hidden field
        const envato_id = e.target.getAttribute("data-envato-id")

        requested_repo_input.setAttribute("value", envato_id)

        // Set product_id input
        const product_id = e.target.getAttribute("data-id")
        product_id_input.setAttribute("value", product_id)
      }

      // /////////////////////////////////////////////////////////////
      // UI Method: Show/Hide Custom Select dropDown
      // /////////////////////////////////////////////////////////////
      function toggle_select_options(e) {
        let setValue = "none"
        if(window.getComputedStyle(select_options_ul).display === "none") setValue = "block"
        select_options_ul.style.display = setValue

        if(setValue === "block") {
          document.addEventListener("click", function(event) {
            if (event.target.closest("#custom-select-placeholder")) return
            select_options_ul.style.display = "none"
          })
        }
      }

      // /////////////////////////////////////////////////////////////
      // UI Method: Show/Hide Errors in Form
      // /////////////////////////////////////////////////////////////
      function displayErrors() {

        toggleLoading(false);

        // toggleLoading(false)
        const form_ele = document.getElementById("request-form")

        let inputs = form_ele.elements
        for( i=0; i<inputs.length-1; i++ ) {
          let errMsg = ""
          if(error_obj[inputs[i].name]) errMsg = error_obj[inputs[i].name]
          inputs[i].parentElement.querySelector(".form-error").innerHTML = errMsg
        }

        if(error_obj["global"]) {
          document.querySelector(".form-error.global").innerHTML = `<p>${error_obj["global"]}</p>`
        }
      }

      function validate_form(form) {

        error_obj = {}

        // ////////////////////////////////////////////
        // Vars
        // ////////////////////////////////////////////
        const email_regex = /^(([^<>()[\]\\.,;:\s@\"]+(\.[^<>()[\]\\.,;:\s@\"]+)*)|(\".+\"))@((\[[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}\])|(([a-zA-Z\-0-9]+\.)+[a-zA-Z]{2,}))$/
        const form_ele = document.getElementById("request-form")

        // ////////////////////////////////////////////
        // Validate data
        // ////////////////////////////////////////////
        const form_data = new FormData(form_ele)

        const email          = form_data.get("email")
        const purchase_code  = form_data.get("purchase_code")
        const username       = form_data.get("username")
        const requested_repo = form_data.get("requested_repo")

        if(!email) { error_obj.email = "Email Field is Required!" }
        else if(!email_regex.test(email)) { error_obj.email = "Email is not valid!" }

        if(!purchase_code)  { error_obj.purchase_code  = "Purchase Code Field is Required!" }
        if(!username)       { error_obj.username       = "Username Field is Required!"      }
        if(!requested_repo) { error_obj.requested_repo = "Repository is not selected!"      }

        Object.keys(error_obj).length === 0 ? toggleLoading() : displayErrors()        

        return (Object.keys(error_obj).length === 0)
      }

      // /////////////////////////////////////////////////////////////
      // UI Method: Show/Hide Loading
      // /////////////////////////////////////////////////////////////
      function toggleLoading(show=true) {

        if(show) {
          if (loading_bg.className.match("hidden"))  loading_bg.classList.remove("hidden")
          if (loading.className.match("hidden"))     loading.classList.remove("hidden")
        }else {
          if (!loading_bg.className.match("hidden")) loading_bg.classList.add("hidden")
          if (!loading.className.match("hidden"))    loading.classList.add("hidden")
        }
      }

    </script>
  </body>
</html>
