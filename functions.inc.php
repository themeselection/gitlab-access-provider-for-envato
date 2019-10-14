<?php

session_start();
include_once("./config.php");

// ///////////////////////////////////////////////////////
// VARIABLES
// ///////////////////////////////////////////////////////

$remote_base_url     = $config["api"]["base"];
$remote_request_url  = $config["api"]["requests"];
$remote_products_url = $config["api"]["products"];
$internal_err        = $config["messages"]["global_error"];
$envato_personal_token = $config["access_tokens"]["envato_personal_token"];
$gitlab_private_token = $config["access_tokens"]["gitlab_private_token"];



// ///////////////////////////////////////////////////////
// HELPER FUNCTIONS
// ///////////////////////////////////////////////////////

function redirect_to_home() {
    header("Location: index.php");
    exit();
}

function debug($msg) {
   var_dump($msg);
   exit();
}

// ///////////////////////////////////////////////////////
// ERROR
// ///////////////////////////////////////////////////////

function show_err($msg)
{
    $_SESSION["form-error-global"] = $msg;
}

function show_success($msg)
{
    $_SESSION["form-success-global"] = $msg;
}

function set_field_error($field, $msg) {
    $_SESSION["form-error"][$field] = $msg;
}


// ///////////////////////////////////////////////////////
// FORM
// ///////////////////////////////////////////////////////

function set_form_data($email, $username, $purchase_code, $item_id) {
  $_SESSION["form-data"]["email"] = $email;
  $_SESSION["form-data"]["username"] = $username;
  $_SESSION["form-data"]["purchase_code"] = $purchase_code;
  $_SESSION["form-data"]["requested_repo"] = $item_id;
}

function clear_form_data() {
  $_SESSION["form-data"] = [];
}


function validate_data($email, $username, $purchase_code, $item_id) {

  $is_err_set = false;

  // Email
  if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    set_field_error("email", "Invalid Email!");
    $is_err_set = true;
  }

  // username
  if(!$username) {
    set_field_error("username", "Username required!");
    $is_err_set = true;
  }

  // purchase code
  if(!$purchase_code) {
    set_field_error("purchase_code", "Purchase Code required!");
    $is_err_set = true;
  }

  // item
  if(!$item_id) {
    set_field_error("requested_repo", "Please select theme!");
    $is_err_set = true;
  }

  if($is_err_set) { redirect_to_home(); }
}

// ///////////////////////////////////////////////////////
// REQUESTS
// ///////////////////////////////////////////////////////

function make_request($url, $headers, $err_field="requested_repo", $post_data_arr=[]) {

    global $internal_err;

    $data = [];
    $curl = curl_init($url);
    curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);

    // if post request
    if(count($post_data_arr) > 0) {

      curl_setopt($curl, CURLOPT_POSTFIELDS, http_build_query($post_data_arr));
    }

    // set headers
    $headers ? curl_setopt($curl, CURLOPT_HTTPHEADER, $headers) : "";
    
    $curl_response = curl_exec($curl);

    // err handling
    if(curl_errno($curl)) {
        show_err($internal_err);
        set_field_error($err_field, curl_error($curl));

        redirect_to_home();
    }

    $status_code = curl_getinfo($curl, CURLINFO_HTTP_CODE);

    // close
    curl_close($curl);

    return array("status" => $status_code, "result" => json_decode($curl_response));
}

function req_validate_purchase_code($code) {

    global $envato_personal_token;

    $verify_envato_url = "https://api.envato.com/v3/market/author/sale?code=$code";
    $envato_headers = [
        "Content-type: application/json",
        "Authorization: Bearer $envato_personal_token",
    ];

    return make_request($verify_envato_url, $envato_headers);
}

function req_check_req_already_given($code) {

    global $remote_request_url;

    $req_existance_url = $remote_request_url . "?purchase_code=" . $code;
    $req_existance_headers = [ "Content-type: application/json" ];

    return make_request($req_existance_url, $req_existance_headers, "username");
}

function req_fetch_user_id($username) {

  $url = "https://gitlab.com/api/v4/users?username=$username";
  $headers = [ "Content-type: application/json" ];

  return make_request($url, $headers, "username");

}

function req_get_gitlab_project_id($envato_item_id) {

  global $remote_products_url;

  $url = $remote_products_url . "?envato_id=" . $envato_item_id;
  $headers = [ "Content-type: application/json" ];
  return make_request($url, $headers);
}

function req_grant_access_to_repo($project_id, $user_id) {

  global $gitlab_private_token;

  $access_level = 20; //reporter

  $url = "https://gitlab.com/api/v4/projects/" . $project_id . "/members";
  $headers = [
    "PRIVATE-TOKEN: $gitlab_private_token"
  ];

  $post_data_arr = array("user_id"=> $user_id, "access_level"=> $access_level);

  return make_request($url, $headers, "username", $post_data_arr);
}

function req_save_record_in_db($email, $username, $purchase_code, $product_id) {
  global $remote_request_url;

  // $headers = [ "Content-type: application/json" ];
  $post_data_arr = array("email"=> $email, "username"=> $username, "purchase_code"=> $purchase_code, "product_id"=> $product_id);

  return make_request($remote_request_url, [], "username", $post_data_arr);
}

// ///////////////////////////////////////////////////////
// RESPONSE HANDLERS
// ///////////////////////////////////////////////////////


// If error redirect
function handle_validate_purchase_code_response($response) {

  global $internal_err;

  if($response["status"] !== 200) {

    // If error exist
    if($response["status"] === 404) {
        set_field_error("purchase_code", "Invalid Purchase Code!");
    }else {
        set_field_error("purchase_code", json_decode(json_encode($response["result"]), true)["error"]);
        show_err($internal_err);
    }

    redirect_to_home();
  }
  return $response["result"]->item;
}


// If error redirect
function handle_check_req_exist_response($response) {

    global $internal_err;

    if($response["status"] !== 200) {

      set_field_error("purchase_code", json_encode($response["result"]));
      show_err($internal_err);
      redirect_to_home();
    }

  if(count($response["result"]) > 0) {
    set_field_error("purchase_code", "Access for this purchase code is already given!");
    show_err("Only one user can have access to purchased item for security reasons.");
    redirect_to_home();
  }
}


// return user id
function handle_fetch_user_id_response($response) {

  global $internal_err;

  if($response["status"] !== 200) {
    set_field_error("username", "Error Fetching your gitlab user details!");
    show_err($internal_err);
    redirect_to_home();
  }

  if(count($response["result"]) > 0) {
    return $response["result"][0]->id;
  }else {
    set_field_error("username", "Error Fetching your user details! Please check your gitlab username.");
    redirect_to_home();
  }
}


// return gitlab project id from database
function handle_get_gitlab_project_id_response($response) {

  global $internal_err;

  if($response["status"] !== 200) {
    set_field_error("requested_repo", "Error Fetching your gitlab project details!");
    show_err($internal_err);
    redirect_to_home();
  }

  if(count($response["result"]) > 0) {
    return $response["result"][0]->gitlab_project_id;
  }else {
    set_field_error("requested_repo", "Error Fetching selected theme details! Please check your selected theme.");
    redirect_to_home();
  }
}


// If error redirect
function handle_grant_access_to_repo_response($response) {

  global $internal_err;

  if($response["status"] !== 201) {

    // If error exist
    if($response["status"] === 404) {
      set_field_error("requested_repo", "Error granting access to this project.");
      show_err($internal_err);
    }else if($response["status"] === 409) {
      set_field_error("username", "Access is already given to this user.");
    }else {
      set_field_error("requested_repo", json_encode($response["result"]));
      show_err($internal_err);
    }
    redirect_to_home();
  }
}


// Save data in remote db
function handle_save_record_in_db_response($response) {
  global $internal_err;

  if($response["status"] !== 200) {
    set_field_error("username", "Error saving your user details!");
    show_err($internal_err);
    redirect_to_home();
  }
}

?>