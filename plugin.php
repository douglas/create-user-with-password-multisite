<?php

/*
  Plugin Name: Create User With Password Multisite
  Plugin URI: http://www.mooveagency.com
  Description: Add ability to register user with password on WP multisite instalation.
  Version: 1.0.1
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



