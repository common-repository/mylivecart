<?php

/**
 * File: ZTCBL_Setting.php
 *
 * Description: This file contains the ZTCBL_Setting class responsible for managing the settings
 * and functional related thing of the ZTCBL plugin.
 *
 * @package mylivecart
 * @version 1.0.0
 */

if (!defined('ABSPATH')) {
	die();
}

/**
 * ZTCBL_Setting Class.
 *
 * This class handles all the setting related and functional related thing.
 */
class ZTCBL_Setting
{
	/**
	 * Constructor automaticaly call when object is created.
	 */
	public function __construct()
	{
		add_action('admin_menu', array($this, 'ztcbl_setting'));
		add_action('admin_post_save_auth_key', array($this, 'ztcbl_save_auth_key'));
		add_action('wp_ajax_high_light_product', array($this, 'ztcbl_high_light_product_fun'));
		add_action('wp_ajax_nopriv_high_light_product', array($this, 'ztcbl_high_light_product_fun'));
		add_action('woocommerce_before_add_to_cart_button', array($this, 'ztcbl_custom_hidden_product_field'), 11);
		add_action('wp_ajax_ztcbl_load_product_quick_view', array($this, 'ztcbl_load_product_quick_view_ajax'));
		add_action('wp_ajax_nopriv_ztcbl_load_product_quick_view', array($this, 'ztcbl_load_product_quick_view_ajax'));
		add_action('woocommerce_add_to_cart', array($this, 'ztcbl_update_status_after_add_to_cart'));
		add_filter('woocommerce_add_cart_item_data', array($this, 'ztcbl_save_custom_field_to_cart_item_data'), 10, 2);
		add_action('wp_ajax_check_event_status', array($this, 'ztcbl_check_event_status'));
		add_action('wp_ajax_nopriv_check_event_status', array($this, 'ztcbl_check_event_status'));
		add_action('woocommerce_add_order_item_meta', array($this, 'ztcbl_save_custom_field_as_order_item_meta'), 10, 2);
		add_action('woocommerce_thankyou', array($this, 'ztcbl_update_third_party_api'), 10, 3);
		add_filter('woocommerce_order_item_get_formatted_meta_data', array($this, 'ztcbl_hide_order_item_meta'), 10, 2);
		add_action('wp_ajax_get_cart_contents_count', array($this, 'ztcbl_get_cart_contents_count_ajax_callback'));
		add_action('wp_ajax_nopriv_get_cart_contents_count', array($this, 'ztcbl_get_cart_contents_count_ajax_callback'));
		add_action('wp_ajax_auth_key_validate', array($this, 'ztcbl_auth_key_validate'));
		add_action('wp_ajax_nopriv_ztcbl_add_to_cart', array($this, 'ztcbl_add_to_cart'));
		add_action('wp_ajax_ztcbl_add_to_cart', array($this, 'ztcbl_add_to_cart'));
	}

	/**
	 * Adds a product to the WooCommerce cart based on POST data.
	 *
	 * @return void
	 */
	function ztcbl_add_to_cart()
	{
		if (isset($_POST['ajax_nonce'])) {
			if (!wp_verify_nonce(wp_unslash(sanitize_text_field($_POST['ajax_nonce'])), 'ztcbl_ajax_nonce')) {
				esc_html_e('Invalid nonce ', 'mylivecart');
				exit;
			}
		}
		$product_id = isset($_POST['product_id']) ? intval(sanitize_text_field($_POST['product_id'])) : 0;
		$event_key = isset($_POST['event_key']) ? sanitize_text_field($_POST['event_key']) : '';
		$event_id = isset($_POST['event_id']) ? intval(sanitize_text_field($_POST['event_id'])) : 0;
		$watcher_id = isset($_POST['watcher_id']) ? sanitize_text_field($_POST['watcher_id']) : 0;

		// Check if the product exists and is purchasable
		$product = wc_get_product($product_id);

		if (!$product || !$product->is_purchasable()) {
			esc_html_e('Product is not available for purchase.', 'mylivecart');
			wp_die();
		}

		// Prepare cart item data
		$cart_item_data = array(
			'product_id' => $product_id,
			'ztcbl_event_key' => $event_key,
			'ztcbl_event_id' => $event_id,
			'ztcbl_watcher_id' => $watcher_id,
		);

		// Check if a similar item already exists in the cart
		$cart_item_key = WC()->cart->generate_cart_id($product_id, 0, array(), $cart_item_data);
		$cart_item_exists = WC()->cart->find_product_in_cart($cart_item_key);

		if ($cart_item_exists) {
			// If the item exists, update its quantity instead of adding a new one
			WC()->cart->set_quantity($cart_item_exists, WC()->cart->get_cart_item_quantity($cart_item_exists) + 1);
			$ztcbl_api = new ZTCBL_Api();
			$ztcbl_api->ztcbl_update_product_status($event_key, $product_id, $event_id, 'add to cart', $watcher_id, 0);
			esc_html_e('Product added successfully. ', 'mylivecart');
		} else {
			// Add the item to the cart using the filter
			add_filter('woocommerce_add_cart_item_data', function ($cart_item_data, $product_id) use ($event_key, $event_id, $watcher_id) {
				$cart_item_data['ztcbl_event_key'] = $event_key;
				$cart_item_data['ztcbl_event_id'] = $event_id;
				$cart_item_data['ztcbl_watcher_id'] = $watcher_id;
				return $cart_item_data;
			}, 10, 2);

			$quantity = 1;

			// Add the item to the cart
			$cart_item_key = WC()->cart->add_to_cart($product_id, $quantity, 0, array());

			if ($cart_item_key) {
				$ztcbl_api = new ZTCBL_Api();
				$ztcbl_api->ztcbl_update_product_status($event_key, $product_id, $event_id, 'add to cart', $watcher_id, 0);
				esc_html_e('Product added successfully. ', 'mylivecart');
			} else {
				esc_html_e('Something went wrong OR Product is out of stock. ', 'mylivecart');
			}
		}

		wp_die();
	}

	/**
	 * Validates and updates the authentication keys for the ZTCBL integration.
	 */
	public function ztcbl_auth_key_validate()
	{
		if (isset($_REQUEST['nonce'])) {
			if (!wp_verify_nonce(wp_unslash(sanitize_text_field($_REQUEST['nonce'])), 'ztcbl_ajax_nonce')) {
				esc_html_e('Nonce not verified.', 'mylivecart');
			} else {
				$consumer_key        = isset($_REQUEST['consumer_key']) ? wp_unslash(sanitize_text_field($_REQUEST['consumer_key'])) : '';
				$consumer_secret_key = isset($_REQUEST['consumer_secret_key']) ? wp_unslash(sanitize_text_field($_REQUEST['consumer_secret_key'])) : ' ';
				$site_icon_url = get_site_icon_url();

				update_option(sanitize_key('ztbcl_consumer_key'), sanitize_text_field($consumer_key));
				update_option(sanitize_key('ztbcl_consumer_secret_key'), sanitize_text_field($consumer_secret_key));

				global $wpdb;
				$table_name = $wpdb->prefix . 'woocommerce_api_keys';
				$query      = "SELECT consumer_key, consumer_secret, permissions FROM $table_name";
				$key        = $wpdb->get_row($query);
				if (isset($key->consumer_key)) {
					update_option(sanitize_key('ztbcl_consumer_key'), sanitize_text_field($consumer_key));
					$url = sanitize_url(ZTCBL_WEB_APP_URL .'/?storeType=WordPress&consumerKey=' . $consumer_key . '&consumerSecretKey=' . $consumer_secret_key . '&storeUrl=' . urldecode(site_url()) . '&callbackURL=' . admin_url('admin.php?page=ztcbl-setting&tab=configuration') . '&storeName=' . get_bloginfo('name') . '&logo=' . $site_icon_url);
					wp_send_json_success(array('url' => $url));
				} else {
					echo '';
				}
			}
			wp_die();
		}
	}

	/**
	 * Undocumented function
	 * Callback function for AJAX request to get the cart contents count.
	 */
	public function ztcbl_get_cart_contents_count_ajax_callback()
	{
		$cart_count = WC()->cart->get_cart_contents_count();
		echo esc_html($cart_count);
		wp_die();
	}


	/**
	 * Filters the formatted meta data for an order item.
	 *
	 * @param array         $formatted_meta The formatted meta data.
	 * @param WC_Order_Item $item The order item object.
	 * @return array The modified formatted meta data.
	 */
	public function ztcbl_hide_order_item_meta($formatted_meta, $item)
	{
		if (is_order_received_page()) {
			// Return an empty array to hide the order item meta.
			return array();
		}
		// Return the original formatted meta data for other pages.
		return $formatted_meta;
	}

	/**
	 * Updates the third-party API with order data.
	 *
	 * @param int      $order_id The ID of the order being processed.
	 * @param array    $posted_data The data posted during order processing.
	 * @param WC_Order $order The WC_Order object representing the order.
	 * @return void
	 */
	public function ztcbl_update_third_party_api($order_id)
	{
		$flag = get_option(sanitize_key('ztcbl_update_sell_status'));
		if ($flag == 'false') {
			$order        = wc_get_order($order_id);
			$order_items  = $order->get_items();
			foreach ($order_items as $item_id => $item) {
				$product_id = $item->get_product_id();
				$event_key  = $item->get_meta('ztcbl_event_key');
				$event_id   = $item->get_meta('ztcbl_event_id');
				$watcher_id = $item->get_meta('ztcbl_watcher_id');
				$quantity   = $item['quantity'];
				$ztcbl_api  = new ZTCBL_Api();
				$ztcbl_api->ztcbl_update_product_status($event_key, $product_id, $event_id, 'sell', $watcher_id, $quantity);
				update_option(sanitize_key('ztcbl_update_sell_status'), 'true');
			}
		}
	}

	/**
	 * Saves a custom field as order item meta.
	 *
	 * @param int   $item_id The ID of the order item being processed.
	 * @param array $cart_item The cart item data.
	 * @return void
	 */
	public function ztcbl_save_custom_field_as_order_item_meta($item_id, $cart_item)
	{
		if (isset($cart_item['ztcbl_event_key']) && isset($cart_item['ztcbl_event_id'])) {
			wc_add_order_item_meta($item_id, sanitize_key('ztcbl_event_key'), sanitize_text_field($cart_item['ztcbl_event_key']));
			wc_add_order_item_meta($item_id, sanitize_key('ztcbl_event_id'), sanitize_text_field($cart_item['ztcbl_event_id']));
			wc_add_order_item_meta($item_id, sanitize_key('ztcbl_watcher_id'),  sanitize_text_field($cart_item['ztcbl_watcher_id']));
			update_option(sanitize_key('ztcbl_update_sell_status'), 'false');
		}
	}

	/**
	 * Checks the status of an event.
	 *
	 * @return void The status of the event.
	 */
	public function ztcbl_check_event_status()
	{
		if (isset($_REQUEST['nonce']) && wp_verify_nonce(wp_unslash(sanitize_text_field($_REQUEST['nonce'])), 'ztcbl_ajax_nonce')) {
			$event_key = isset($_REQUEST['event_key']) ? wp_unslash(sanitize_text_field($_REQUEST['event_key'])) : '';
			if (isset($event_key)) {
				$ztcbl_api           = new ZTCBL_Api();
				$event_status_api    = $ztcbl_api->ztcbl_check_event_status($event_key);
				$events_details_data = json_decode(wp_remote_retrieve_body($event_status_api));
				echo esc_html(strtolower($events_details_data->data->status));
			}
		}
		wp_die();
	}
	/**
	 * Saves a custom field to the cart item data.
	 *
	 * @param  array $cart_item_data The cart item data.
	 * @param  int   $product_id The ID of the product.
	 * @return array The modified cart item data.
	 */
	public function ztcbl_save_custom_field_to_cart_item_data($cart_item_data, $product_id)
	{

		if (isset($_POST['cart_data_nonce']) && wp_verify_nonce(sanitize_text_field($_POST['cart_data_nonce']), 'cart_data_nonce')) {
			$event_key = isset($_POST['ztcbl_event_key']) ? wp_unslash(sanitize_text_field($_POST['ztcbl_event_key'])) : '';
			$event_id  = isset($_POST['ztcbl_event_id']) ? wp_unslash(sanitize_text_field($_POST['ztcbl_event_id'])) : '';
			$watcher_id = isset($_POST['ztcbl_watcher_id']) ? wp_unslash(sanitize_text_field($_POST['ztcbl_watcher_id'])) : '';
			if (isset($event_key) && isset($event_id)) {
				$cart_item_data['ztcbl_event_key'] = $event_key;
				$cart_item_data['ztcbl_event_id']  = $event_id;
				$cart_item_data['ztcbl_watcher_id']  = $watcher_id;
			}
		}
		return $cart_item_data;
	}

	/**
	 * Updates the status after adding a product to the cart.
	 *
	 * @param string $cart_item_key The cart item key.
	 * @param int    $product_id The ID of the product.
	 * @return  void
	 */
	public function ztcbl_update_status_after_add_to_cart($cart_item_key = null, $product_id = null)
	{
		global $woocommerce;
		if (isset($_POST['cart_data_nonce']) && wp_verify_nonce(sanitize_text_field($_POST['cart_data_nonce']), 'cart_data_nonce')) {
			$event_key  = isset($_POST['ztcbl_event_key']) ? wp_unslash(sanitize_text_field($_POST['ztcbl_event_key'])) : '';
			$event_id   = isset($_POST['ztcbl_event_id']) ? wp_unslash(sanitize_text_field($_POST['ztcbl_event_id'])) : '';
			$cart_item  = $woocommerce->cart->get_cart_item($cart_item_key);
			$watcher_id = isset($_POST['ztcbl_watcher_id']) ? wp_unslash(sanitize_text_field($_POST['ztcbl_watcher_id'])) : '';
			$product_id = $cart_item['product_id'];
			if (isset($event_key) && isset($event_id)) {
				$ztcbl_api = new ZTCBL_Api();
				$ztcbl_api->ztcbl_update_product_status($event_key, $product_id, $event_id, 'add to cart', $watcher_id, 0);
			}
		}
	}

	/**
	 * Renders a custom hidden product field.
	 *
	 * @return void
	 */
	public function ztcbl_custom_hidden_product_field()
	{
		$get_event_key = isset($_GET['redirect-key']) ? wp_unslash(sanitize_text_field($_GET['redirect-key'])) : '';
		$get_event_id  = isset($_GET['redirect-id']) ? intval(sanitize_text_field($_GET['redirect-id'])) : '';

		if (isset($get_event_key) && isset($get_event_id)) {
?>
			<input type="hidden" name="cart_data_nonce" value="<?php echo esc_attr(wp_create_nonce('cart_data_nonce')); ?>" />
			<input type="hidden" id="ztcbl_event_key" name="ztcbl_event_key" class="ztcbl_event_key" value="<?php echo esc_attr($get_event_key); ?>">
			<input type="hidden" id="ztcbl_event_id" name="ztcbl_event_id" class="ztcbl_event_id" value="<?php echo esc_attr($get_event_id); ?>">
			<input type="hidden" id="ztcbl_watcher_id" name="ztcbl_watcher_id" class="ztcbl_watcher_id" value="">
			<script>
				let watcherId = localStorage.getItem('watcher_unique_id');
				if (watcherId) {
					jQuery('#ztcbl_watcher_id').val(watcherId);
				}
			</script>
		<?php
		}
	}

	/**
	 * Renders the settings page for the Crystal Ball plugin.
	 *
	 * @return void
	 */
	public function ztcbl_setting_page()
	{
		$current_tab  = isset($_GET['tab']) ? wp_unslash(sanitize_text_field($_GET['tab'])) : 'authenticate';
		$setting_tabs = array(
			'authenticate'  => 'Authentication',
			'configuration' => 'Configuration',
		);
		?>

		<div class="wrap">
			<h1 style="font-family: poppins;font-weight:500px;"><?php echo esc_html(get_admin_page_title()); ?></h1>
			<?php
			echo '<h2 class="nav-tab-wrapper" style="margin-bottom:20px;font-family: poppins;">';
			foreach ($setting_tabs as $tab => $title) {
				$current_active_tab = ($tab === $current_tab) ? 'nav-tab-active' : '';
				printf(
					'<a class="nav-tab %s" href="%s" >%s</a>',
					esc_attr($current_active_tab),
					esc_url(admin_url('admin.php?page=ztcbl-setting' . "&tab={$tab}")),
					esc_html($title)
				);
			}
			echo '</h2>';
			?>

			<div class="tab-content">
				<?php
				if (isset($current_tab)) {
					require_once ZTCBL_UI_ADMIN_DIR . $current_tab . '.php';
				}
				?>
			</div>
		</div>
<?php
	}

	/**
	 * Display the appropriate page based on the 'zt-page' request parameter.
	 */
	public function ztcbl_events_page()
	{
		if (isset($_REQUEST['zt-page']) && $_REQUEST['zt-page'] === 'create-event') {
			require_once ZTCBL_UI_ADMIN_DIR . 'create-event.php';
		} elseif (isset($_REQUEST['zt-page']) && $_REQUEST['zt-page'] === 'update-event' && $_REQUEST['e-id']) {
			require_once ZTCBL_UI_ADMIN_DIR . 'update-event.php';
		} else {
			require_once ZTCBL_UI_ADMIN_DIR . 'all-event.php';
		}
	}

	/**
	 * Display the appropriate page based on the 'inf-id' request parameter.
	 */
	public function ztcbl_influencer_page()
	{
		if (isset($_REQUEST['inf-id'])) {
			require_once ZTCBL_UI_ADMIN_DIR . 'offer-list.php';
		} else {
			require_once ZTCBL_UI_ADMIN_DIR . 'influencer-list.php';
		}
	}

	/**
	 * Adding Setting menu and page.
	 */
	public function ztcbl_setting()
	{
		if (is_plugin_active('woocommerce/woocommerce.php')) {
			add_menu_page(
				__('Settings', 'mylivecart'),
				__('MyLiveCart Setting', 'mylivecart'),
				'edit_posts',
				sanitize_key('ztcbl-setting'),
				array($this, 'ztcbl_setting_page'),
				'dashicons-admin-generic',
				25,
			);

			add_submenu_page(
				'ztcbl-setting',
				__('MyLiveCart Setting', 'mylivecart'),
				__('Setting', 'mylivecart'),
				'edit_posts',
				sanitize_key('ztcbl-setting'),
				array($this, 'ztcbl_setting_page'),
				25
			);
			add_submenu_page(
				'ztcbl-setting',
				__('MyLiveCart Events', 'mylivecart'),
				__('All Events', 'mylivecart'),
				'edit_posts',
				sanitize_key('all-events'),
				array($this, 'ztcbl_events_page'),
				25,
			);
		}
	}

	/**
	 * Save authentication key in option table.
	 *
	 * @return void
	 */
	public function ztcbl_save_auth_key()
	{
		if (isset($_POST['auth_key_handler'])) {
			if (empty($_POST) || !wp_verify_nonce(wp_unslash(sanitize_text_field($_POST['auth_key_handler'])), 'save_auth_key')) {
				esc_html_e('Verification failed. Try again.', 'mylivecart');
				exit;
			} else {
				$auth_key = isset($_POST['auth_key']) ? wp_unslash(sanitize_text_field($_POST['auth_key'])) : '';
				update_option(sanitize_key('ztcbl-auth-key'),  sanitize_text_field($auth_key));
			}
			wp_safe_redirect(wp_get_referer());
			exit;
		}
	}

	/**
	 * Highlights the product function.
	 *
	 * @return void
	 */
	public function ztcbl_high_light_product_fun()
	{
		// Check for AJAX nonce
		if (isset($_POST['ajax_nonce']) && wp_verify_nonce(wp_unslash(sanitize_text_field($_POST['ajax_nonce'])), 'ztcbl_ajax_nonce')) {
			$event_key = isset($_POST['event_key']) ? wp_unslash(sanitize_text_field($_POST['event_key'])) : '';

			$ztbl_api = new ZTCBL_Api();
			$ztcbl_product_list = $ztbl_api->ztcbl_product_list($event_key);
			$ztcbl_product_lists = json_decode(wp_remote_retrieve_body($ztcbl_product_list));

			if (isset($ztcbl_product_lists->data) && is_array($ztcbl_product_lists->data)) {
				ob_flush();

				foreach ($ztcbl_product_lists->data as $value) {
					$product_id = $value->product_id;
					$product = wc_get_product($product_id);

					if ($value->highlight) {
						$image_url = get_the_post_thumbnail_url($product_id, 'full');
						$html = "<div class='product_card' id='high_light_product_card' product_id='" . esc_html($product_id) . "'>
							<img class='product_img' src='" . esc_url($image_url) . "' width='100' height='100' alt='Product Image '>
							<div class='badge'>Highlights</div>";

						if ($product) {
							$html .= "<p class='product_name'>" . esc_html($product->get_title()) . "</p>" .
								"<p class='product_price'>Price: " . wp_kses_post($product->get_price_html()) . "</p>";
							$html .= "<a href='#' class='ztcbl-qv-button' data-product_id='" . esc_attr($product_id) . "' data-redirect-key='" . esc_attr($event_key) . "' data-redirect-id='" . esc_attr($value->event_id) . "' >View Details <span style='margin-left:8px;padding-top:3px;'><img src='" . esc_url(ZTCBL_ASSETS_URL . 'images/arrow-icon_1.png') . "' alt='cart_icon' height='15px' width='15px'></span></a>";
							$html .= sprintf(
								'<button data-product_id="%s" data-product_sku="%s" data-redirect-key="%s" data-redirect-id="%s" class="button zt_cart_button product_type_%s">%s</button>',
								esc_attr($product->get_id()),
								esc_attr($product->get_sku()),
								esc_attr($event_key),
								esc_attr($value->event_id),
								esc_attr($product->get_type()),
								esc_html($product->add_to_cart_text())
							);

							$html .= '</div>';
							$html .= '<hr class="highlight_product_separator">';
						} else {
							$html .= "<p class='product_name'>" . esc_html($value->product_name) . "</p>
								<p class='product_price'>Price: " . esc_html($value->price) . "</p>";
							$html .= '</div>';
						}

						echo wp_json_encode(array('product_id' => $product_id, 'html' => $html));
						wp_die();
					}
				}
			}
		}

		// Return null if no products matched the criteria
		echo wp_json_encode('null');
		wp_die();
	}


	/**
	 * Loads the product single page in new tab and also update the status of product.
	 *
	 * @return void
	 */
	public function ztcbl_load_product_quick_view_ajax()
	{
		if (isset($_POST['ajax_nonce'])) {
			if (!wp_verify_nonce(wp_unslash(sanitize_text_field($_POST['ajax_nonce'])), 'ztcbl_ajax_nonce')) {
				wp_send_json_error('Invalid nonce.');
				exit;
			}
		}
		global $product;
		// Get product ID from AJAX request.
		$product_id = isset($_REQUEST['product_id']) ? absint(sanitize_text_field($_REQUEST['product_id'])) : 0;
		$event_key  = isset($_POST['event_key']) ? wp_unslash(sanitize_text_field($_POST['event_key'])) : '';
		$event_id   = isset($_REQUEST['event_id']) ? absint(sanitize_text_field($_REQUEST['event_id'])) : 0;
		$watcher_id = isset($_POST['watcherId']) ? wp_unslash(sanitize_text_field($_POST['watcherId'])) : '';

		$ztcbl_api = new ZTCBL_Api();
		$ztcbl_api->ztcbl_update_product_status($event_key, $product_id, $event_id, 'view', $watcher_id, 0);
		$product_details   = wc_get_product($product_id);
		$product_permalink = $product_details->get_permalink();
		$link = sanitize_url("$product_permalink?redirect-key=$event_key&redirect-id=$event_id");
		wp_send_json_success(array('url' => $link));
		wp_die();
	}
}

new ZTCBL_Setting();
