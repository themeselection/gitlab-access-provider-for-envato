<?php

session_start();
include_once("./functions.inc.php");

// Get Form Data
$purchase_code   = $_POST["purchase_code"];
$product_id      = $_POST["product_id"];
$envato_id       = $_POST["requested_repo"];
$email           = $_POST["email"];
$username        = $_POST["username"];


set_form_data($email, $username, $purchase_code, $envato_id);

validate_data($email, $username, $purchase_code, $envato_id);

$res_validate_purchase_code = req_validate_purchase_code($purchase_code);
$purchased_item = handle_validate_purchase_code_response($res_validate_purchase_code);


// check if entered purchase code is of selected item
if($purchased_item->id != $envato_id) {
    set_field_error("purchase_code", "Invalid Purchase Code! You might have entered the purchase code of another item.");
    redirect_to_home();
}


$res_check_req_exist = req_check_req_already_given($purchase_code);
// debug("as");
handle_check_req_exist_response($res_check_req_exist);

// At this point user haven't asked for access - new user asking for access

$res_fetch_user_id = req_fetch_user_id($username);
$gitlab_user_id = handle_fetch_user_id_response($res_fetch_user_id);

// get gitlab project id from envato item id
$res_get_gitlab_project_id = req_get_gitlab_project_id($envato_id);
$gitlab_project_id = handle_get_gitlab_project_id_response($res_get_gitlab_project_id);

// API call to Gitlab: Grant access to user
$res_grant_access_to_repo = req_grant_access_to_repo($gitlab_project_id, $gitlab_user_id);
handle_grant_access_to_repo_response($res_grant_access_to_repo);

show_success("Your access request has been successfully approved.");
clear_form_data();

// Save record in db
$res_save_record_in_db = req_save_record_in_db($email, $username, $purchase_code, $product_id);
handle_save_record_in_db_response($res_save_record_in_db);

redirect_to_home();

?>