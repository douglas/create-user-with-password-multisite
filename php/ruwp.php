<?php

/**
 * Main plugin class
 * */
class Register_User_With_Password {

    /**
     * Initialization
     */
    public function __construct() {
        // show passwords
        add_action('user_new_form', array($this, 'ruwp_plug_pass'));

        // add script
        add_action('admin_print_scripts-user-new.php', array($this, 'ruwp_script'));

        // listen for REQUEST
        add_action('init', array($this, 'ruwp_listen'), 3);
    }

    /**
     * Hooks Javascript into administration
     */
    public function ruwp_script() {
        wp_enqueue_script('ruwp_main_script', plugins_url('js/ruwp.js', dirname(__FILE__)));
    }

    /**
     * Adds password input field to the register form
     */
    public function ruwp_plug_pass() {
        ?>
        <table class="form-table hook-pass">
            <tbody>
            <input type="hidden" name="ruwp_security" value="ruwp" />
            <tr class="form-field form-required">
                <th scope="row"><label for="pass1"><?php _e('Password'); ?> <span class="description"><?php /* translators: password input field */_e('(required)'); ?></span></label></th>
                <td><input name="pass1" type="password" id="pass1" autocomplete="off" /><input class="hidden" value=" " /></td>
            </tr>

            <tr class="form-field form-required">
                <th scope="row"><label for="pass2"><?php _e('Repeat Password'); ?> <span class="description"><?php /* translators: password input field */_e('(required)'); ?></span></label></th>
                <td><input name="pass2" type="password" id="pass2" autocomplete="off" /></td>
            </tr>

            <tr>
                <td>
                    <div class="pass-error">
                        Passwords are not the same.
                    </div>
                </td>
            </tr>
        </tbody>
        </table>
        <style>
            #createuser .pass-error{
                color:red;
                display:none;
            }
            #adduser .hook-pass{
                display:none;
            }
        </style>
        <?php
    }

    /**
     * Listens for REQUEST and fire this code instead of the core's
     */
    public function ruwp_listen() {
        if (isset($_REQUEST['action']) && 'createuser' == $_REQUEST['action'] && $_REQUEST['ruwp_security'] == 'ruwp') {
            global $wpdb;
            check_admin_referer('create-user', '_wpnonce_create-user');
            if (!current_user_can('create_users'))
                wp_die(__('Cheatin&#8217; uh?'));

            if (!is_multisite()) {
                $user_id = edit_user();

                if (is_wp_error($user_id)) {
                    $add_user_errors = $user_id;
                } else {
                    if (current_user_can('list_users'))
                        $redirect = 'users.php?update=add&id=' . $user_id;
                    else
                        $redirect = add_query_arg('update', 'add', 'user-new.php');
                    wp_redirect($redirect);
                    die();
                }
            } else {
                $user_details = wpmu_validate_user_signup($_REQUEST['user_login'], $_REQUEST['email']);
                if (is_wp_error($user_details['errors']) && !empty($user_details['errors']->errors)) {
                    $add_user_errors = $user_details['errors'];
                } else {
                    /**
                     * Filter the user_login, also known as the username, before it is added to the site.
                     *
                     * @since 2.0.3
                     *
                     * @param string $user_login The sanitized username.
                     */
                    $new_user_login = apply_filters('pre_user_login', sanitize_user(wp_unslash($_REQUEST['user_login']), true));
                    if (isset($_POST['noconfirmation']) && is_super_admin()) {
                        add_filter('wpmu_signup_user_notification', '__return_false'); // Disable confirmation email
                    }
                    wpmu_signup_user($new_user_login, $_REQUEST['email'], array('add_to_blog' => $wpdb->blogid, 'new_role' => $_REQUEST['role']));
                    if (isset($_POST['noconfirmation']) && is_super_admin()) {
                        $key = $wpdb->get_var($wpdb->prepare("SELECT activation_key FROM {$wpdb->signups} WHERE user_login = %s AND user_email = %s", $new_user_login, $_REQUEST['email']));
                        wpmu_activate_signup($key);
                        $redirect = add_query_arg(array('update' => 'add'), 'user-new.php');
                    } else {
                        $redirect = add_query_arg(array('update' => 'add'), 'user-new.php');
                    }

                    // set password for user
                    $user = get_user_by('email', $_REQUEST['email']);
                    wp_set_password($_REQUEST['pass1'], $user->ID);

                    wp_redirect($redirect);
                    die();
                }
            }
        }
    }

}
