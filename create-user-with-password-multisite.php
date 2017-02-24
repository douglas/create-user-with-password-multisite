<?php

/*
  Plugin Name: Create User With Password Multisite
  Plugin URI: http://www.mooveagency.com
  Description: Add ability to register user with password on WP multisite installation.
  Version: 1.0.6.
  Author: Jakub Glos / Douglas Soares de Andrade
  Author URI: http://www.mooveagency.com
  License:
  Text Domain: create-user-with-password-multisite
  Domain Path: /languages
 */

// no need on cron job
if (defined('DOING_CRON') || isset($_GET['doing_wp_cron'])) {
    return;
}

/**
 * Loads the plugin translations
 */
add_action('init', 'cuwp_load_translations');
function cuwp_load_translations() {
    load_plugin_textdomain('create-user-with-password-multisite', false, dirname(plugin_basename(__FILE__)) . '/languages'); 
}

// fire in administration only
if (is_admin()) {
    require_once( 'php/cuwp.php' );
    $mdu = new CUWP_Create_User_With_Password();
}

