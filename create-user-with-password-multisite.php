<?php

/*
  Plugin Name: Create User With Password Multisite
  Plugin URI: http://www.mooveagency.com
  Description: Add ability to register user with password on WP multisite installation.
  Version: 1.0.4.
  Author: Jakub Glos
  Author URI: http://www.mooveagency.com
  License:
  Text Domain: create-user-with-password-multisite
 */

// no need on cron job
if (defined('DOING_CRON') || isset($_GET['doing_wp_cron'])) {
    return;
}


// fire in administration only
if (is_admin()) {

    require_once( 'php/cuwp.php' );
    $mdu = new Create_User_With_Password();
}

/**
 * Install
 */
function cuwp_activate() {
    // store old message in option
    $old_message = get_site_option('welcome_user_email');
    update_option('cuwp_welcome_user_email', $old_message);

    // set new message
    $text = __('Dear User,
Thank you for the registration.  Please check the email address provided for login details. 

--The Team @ SITE_NAME', 'create-user-with-password-multisite');
    
    update_site_option('welcome_user_email', $text);
}

register_activation_hook(__FILE__, 'cuwp_activate');

/**
 * Deactivation
 */
function cuwp_deactivate() {
    // set old message back
    $old_message = get_option('cuwp_welcome_user_email');
    update_site_option('welcome_user_email', $old_message);

    // delete option with the message
    delete_option('cuwp_welcome_user_email');
}

register_deactivation_hook(__FILE__, 'cuwp_deactivate');


