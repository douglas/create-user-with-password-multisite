<?php

/**
 * Main plugin class
 * */

class CUWP_Create_User_With_Password {

    /**
     * Initialization
     */
    public function __construct() {
        // show passwords
        add_action('user_new_form', array($this, 'cuwp_plug_pass'));

        // add script
        add_action('admin_print_scripts-user-new.php', array($this, 'cuwp_script'));

        // add css
        wp_enqueue_style('cuwp-style', plugins_url('create-user-with-password-multisite/css/style.css'));

        // listen for REQUEST
        add_action('admin_action_createuser', array($this, 'cuwp_listen'), 3);
    }

    /**
     * Hooks Javascript into administration
     */
    public function cuwp_script() {
        wp_enqueue_script('cuwp_main_script', plugins_url('js/cuwp.js', dirname(__FILE__)));
    }

    /**
     * Adds password input field to the register form
     */
    public function cuwp_plug_pass() {
        ?>
        <table class="form-table hook-pass">
            <tbody>
            <input type="hidden" name="cuwp_security" value="cuwp" />

            <tr>
                <th scope="row"><label for="noactivationkey"><?php _e('Delete the user activation key', 'create-user-with-password-multisite'); ?></label></th>
                <td>
                    <input type="checkbox" name="noactivationkey" id="noactivationkey" value="1" checked="checked">
                    <label for="noactivationkey"><?php _e('As the user is created and activated with a new password, there is no need to keep the activation key stored in the database.', 'create-user-with-password-multisite'); ?></label>
                </td>
            </tr>

            <tr class="form-field form-required">
                <th scope="row"><label for="cuwp_pass1"><?php _e('Password', 'create-user-with-password-multisite'); ?> <span class="description"><?php _e('(required)'); ?></span></label></th>
                <td><input name="cuwp_pass1" type="password" id="pass1" autocomplete="off" /><input class="hidden" value=" " /></td>
            </tr>

            <tr class="form-field form-required">
                <th scope="row"><label for="cuwp_pass2"><?php _e('Repeat Password', 'create-user-with-password-multisite'); ?> <span class="description"><?php _e('(required)'); ?></span></label></th>
                <td><input name="cuwp_pass2" type="password" id="pass2" autocomplete="off" /></td>
            </tr>

            <tr class="pass-error">
                <td><?php _e("Passwords do not match.", 'create-user-with-password-multisite'); ?></td>
            </tr>
        </tbody>
        </table>
        <?php
    }

    /**
     * Listens for REQUEST and fire this code instead of the core's
     */
    public function cuwp_listen() {

        if (isset($_REQUEST['cuwp_security']) && 'cuwp' == $_REQUEST['cuwp_security']) {
            
            if(sanitize_text_field($_REQUEST['cuwp_pass1']) != sanitize_text_field($_REQUEST['cuwp_pass2'])){
                wp_die(__('Passwords do not match.', 'create-user-with-password-multisite'));
            }
            
            global $wpdb;
            check_admin_referer( 'create-user', '_wpnonce_create-user' );
            if ( ! current_user_can( 'create_users' ) ) {
                wp_die(
                    '<h1>' . __( 'Cheatin&#8217; uh?' ) . '</h1>' .
                    '<p>' . __( 'Sorry, you are not allowed to create users.' ) . '</p>',
                    403
                );
            }

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
                $new_user_email = wp_unslash( $_REQUEST['email'] );
                $new_user_password = wp_unslash( $_REQUEST['cuwp_pass1'] );
                $user_details = wpmu_validate_user_signup( $_REQUEST['user_login'], $new_user_email );

                if ( is_wp_error( $user_details[ 'errors' ] ) && !empty( $user_details[ 'errors' ]->errors ) ) {
                    $add_user_errors = $user_details[ 'errors' ];
                } else {
                    /**
                     * Filters the user_login, also known as the username, before it is added to the site.
                     *
                     * @since 2.0.3
                     *
                     * @param string $user_login The sanitized username.
                     */
                    $new_user_login = apply_filters( 'pre_user_login', sanitize_user( wp_unslash( $_REQUEST['user_login'] ), true ) );
                    
                    if ( isset( $_POST[ 'noconfirmation' ] ) && current_user_can( 'manage_network_users' ) ) {
                        add_filter( 'wpmu_signup_user_notification', '__return_false' ); // Disable confirmation email
                        add_filter( 'wpmu_welcome_user_notification', '__return_false' ); // Disable welcome email
                    }

                    wpmu_signup_user( $new_user_login, $new_user_email, array( 'add_to_blog' => $wpdb->blogid, 'new_role' => $_REQUEST['role'] ) );
                    if ( isset( $_POST[ 'noconfirmation' ] ) && current_user_can( 'manage_network_users' ) ) {
                        $key = $wpdb->get_var( $wpdb->prepare( "SELECT activation_key FROM {$wpdb->signups} WHERE user_login = %s AND user_email = %s", $new_user_login, $new_user_email ) );
                        $new_user = wpmu_activate_signup( $key );

                        if (isset( $_POST[ 'noactivationkey' ] )) {
                            // As the user is created and activated with a new password, there
                            // is no need to keep the activation key stored in the database.
                            $wpdb->delete( $wpdb->signups, array( 'activation_key' => $key ) );
                        }

                        if ( is_wp_error( $new_user ) ) {
                            $redirect = add_query_arg( array( 'update' => 'addnoconfirmation' ), 'user-new.php' );
                        } else {
                            $redirect = add_query_arg( array( 'update' => 'addnoconfirmation', 'user_id' => $new_user['user_id'] ), 'user-new.php' );
                        }
                    } else {
                        $redirect = add_query_arg( array('update' => 'newuserconfirmation'), 'user-new.php' );
                    }

                    // set password for user
                    $user = get_user_by('email', sanitize_email($new_user_email));
                    wp_set_password(sanitize_text_field($new_user_password), $user->ID);

                    wp_redirect( $redirect );
                    die();
                }
            }
        }
    }

}
