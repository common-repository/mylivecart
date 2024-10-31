<?php

/**
 * Plugin Name: MyLiveCart
 * Author: Zehntech Technologies Pvt. Ltd.
 * Author URI: https://www.zehntech.com/
 * Description: Plugin to broadcast MyLiveCart events on the WordPress website.
 * Version: 1.0.3
 * License: GPL2
 * License URI: https://www.gnu.org/licenses/gpl-2.0.html
 * Text Domain: mylivecart
 *
 * @package mylivecart
 */

defined('ABSPATH') || die('you do not have access to this page!');

defined('ZTCBL_PLUGIN_DIR') ? '' : define('ZTCBL_PLUGIN_DIR', plugin_dir_path(__FILE__));
defined('ZTCBL_TEXT_DOMAIN') ? '' : define('ZTCBL_TEXT_DOMAIN', 'mylivecart');
defined('ZTCBL_PLUGIN_URL') ? '' : define('ZTCBL_PLUGIN_URL', plugin_dir_url(__FILE__));
defined('ZTCBL_ASSETS_URL') ? '' : define('ZTCBL_ASSETS_URL', plugin_dir_url(__FILE__) . 'assets/');
defined('ZTCBL_UI_FRONT_DIR') ? '' : define('ZTCBL_UI_FRONT_DIR', ZTCBL_PLUGIN_DIR . 'ui-front/');
defined('ZTCBL_UI_ADMIN_DIR') ? '' : define('ZTCBL_UI_ADMIN_DIR', ZTCBL_PLUGIN_DIR . 'ui-admin/');
defined('ZTCBL_API_URL') ? '' : define('ZTCBL_API_URL', 'https://crystal-tenant.mylivecart.com/api/v1/wp/');
defined('ZTCBL_INF_API_URL') ? '' : define('ZTCBL_INF_API_URL', 'https://crystal-admin.mylivecart.com/api/v1/');
defined('ZTCBL_SOCKET_URL') ? '' : define('ZTCBL_SOCKET_URL', 'https://websocket.mylivecart.com:3003/app');
defined('ZTCBL_WEB_APP_URL') ? '' : define('ZTCBL_WEB_APP_URL', 'https://web.mylivecart.com');

/**
 * Create Event List and Event details page on plugin activation
 *
 * @return void
 */

function ztcbl_add_event_list_page()
{
	if (empty(get_page_by_path(wp_strip_all_tags('Events List')))) {
		$my_post = array(
			'post_title'   => __('Events List', 'mylivecart'),
			'post_content' => '[ztcbl_EventsListPageContent per_page=12]',
			'post_name'    => 'events-list',
			'post_status'  => 'publish',
			'post_author'  => 1,
			'post_type'    => 'page',
		);

		// Insert the post into the database.
		wp_insert_post($my_post);
	}

	if (empty(get_page_by_path(wp_strip_all_tags('Events Detail')))) {
		$page_template = 'ztcbl-single-details.php';
		$my_post       = array(
			'post_title'    => __('Events Detail', 'mylivecart'),
			'post_name'     => 'events-detail',
			'post_status'   => 'publish',
			'post_author'   => 1,
			'post_type'     => 'page',
			'page_template' => $page_template,
		);

		// Insert the post into the database.
		wp_insert_post($my_post);
	}
}


register_activation_hook(__FILE__, 'ztcbl_add_event_list_page');

/**
 * Register js and css file
 *
 * @return void
 */
function ztcbl_include_css_js_file()
{
	global $post;

	// Register styles
	wp_register_style('ztcbl_font_family', ZTCBL_ASSETS_URL . 'css/zt-font-family.css', array(), '1.0.0', 'all');
	wp_register_style('ztcbl_style', ZTCBL_ASSETS_URL . 'css/zt-style.css', array(), '1.0.0', 'all');

	// Register scripts
	wp_register_script('ztcbl_socket', 'https://cdn.socket.io/4.3.2/socket.io.min.js','4.3.2');
	wp_register_script('ztcbl_js_file', ZTCBL_ASSETS_URL . 'js/zt-script.js', array('jquery'), '1.0.0', true);

	// Localize the JavaScript file for AJAX
	$ajax_nonce = wp_create_nonce('ztcbl_ajax_nonce');
	wp_localize_script(
		'ztcbl_js_file',
		'ztcbl_qv',
		array(
			'ajaxurl' => admin_url('admin-ajax.php', 'relative'),
			'nonce'   => $ajax_nonce,
			'ztcbl_api_url' => ZTCBL_API_URL,
			'ztcbl_socket_url' => ZTCBL_SOCKET_URL,
			'ztcbl_site_url'   => site_url(),
		)
	);

	if (is_page('events-detail')) {
		wp_enqueue_style('ztcbl_font_family');
		wp_enqueue_style('ztcbl_style');
		wp_enqueue_script('ztcbl_socket');
		wp_enqueue_script('ztcbl_js_file');
	}
}



add_action('wp_enqueue_scripts', 'ztcbl_include_css_js_file');
add_action('admin_enqueue_scripts', 'ztcbl_include_css_js_file');

// Set the timeout to 100 seconds (adjust as needed).

function ztcbl_increase_curl_timeout($timeout)
{
	return 100;
}

add_filter('http_request_timeout', 'ztcbl_increase_curl_timeout');


// Add a filter to modify the allowed HTTP origins for Cross-Origin Resource Sharing (CORS).
add_filter('allowed_http_origins', 'ztcbl_add_allowed_origins');

// Define a function to add custom origins to the allowed list.
function ztcbl_add_allowed_origins($origins)
{
	// Add the specific origin to the list of allowed HTTP origins.
	$origins[] = site_url();

	// Return the updated list of allowed origins.
	return $origins;
}

//add query vars.
function ztcbl_custom_query_vars($query_vars)
{
	$query_vars[] = 'event_key';
	return $query_vars;
}
add_filter('query_vars', 'ztcbl_custom_query_vars');

//add rules for custom url.
function ztcbl_custom_init()
{
	add_rewrite_rule('^events-detail/([^/]+)/?', 'index.php?pagename=events-detail&event_key=$matches[1]', 'top');
	flush_rewrite_rules(); // Remember to flush rules after changing this code
}
add_action('init', 'ztcbl_custom_init');

//route  function for secreat key
function ztcbl_check_event_key($request) {
    // Retrieve the secret key from the options table
    $secret_key = get_option(sanitize_key('ztcbl-auth-key'));

    // Check if the secret key is empty
    if (empty($secret_key)) {
        // Return a structured error response with a 404 status code
        return new WP_REST_Response([
            'error' => 'The secret key is not set or is empty.'
        ], 200);
    }

    // Return the secret key in the desired format with a 200 status code
    return new WP_REST_Response([
        'secret_key' => $secret_key
    ], 200);
}


//create route for secreat key
add_action('rest_api_init', function () {
    register_rest_route('api/v1', '/secret', array(
        'methods' => 'GET',
        'callback' => 'ztcbl_check_event_key',
        'permission_callback' => '__return_true', // Allows public access; adjust as needed
    ));
});

//Show Notices when WooCommerce is not activate.

function ztcbl_check_woocommerce_activation()
{
	if (!is_plugin_active('woocommerce/woocommerce.php')) {
?>
		<div class="notice notice-error is-dismissible">
			<p><?php esc_html_e('WooCommerce is not active. Please activate WooCommerce to use the MyLiveCart Plugin.', 'mylivecart'); ?></p>
		</div>
<?php
	}
}


//Automatic deactivate plugin when WooCommerce is not active.

function ztcbl_deactivate_on_activation_check()
{
	if (!is_plugin_active('woocommerce/woocommerce.php')) {
		deactivate_plugins(plugin_basename(__FILE__));
	}
}

add_action('admin_notices', 'ztcbl_check_woocommerce_activation');
add_action('admin_init', 'ztcbl_deactivate_on_activation_check');

//Includes all mylivecart classes.
require_once ZTCBL_PLUGIN_DIR . 'includes/class-ztcbl-shortcode.php';
require_once ZTCBL_PLUGIN_DIR . 'includes/class-ztcbl-setting.php';
require_once ZTCBL_PLUGIN_DIR . 'includes/class-ztcbl-api.php';
require_once ZTCBL_PLUGIN_DIR . 'includes/class-ztcbl-event.php';
