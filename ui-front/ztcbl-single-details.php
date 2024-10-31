<?php

/**
 * File: ztcbl-single-details.php
 *
 * This File is responsible for displaying event details, including an embedded video or content,
 * and a list of associated products. It also handles cart functionality and updates the event status
 * when products are added to the cart.
 *
 * @package my-live-cart
 * @version 1.0.0
 */

if (!defined('ABSPATH')) {
	exit; // Exit if accessed directly
}

function ztcbl_function_admin_bar()
{
	return false;
}
add_filter('show_admin_bar', 'ztcbl_function_admin_bar');

global $event_id;
// $event_key  = get_query_var('event_key');
$event_key  = !empty(get_query_var('event_key')) ? wp_unslash(sanitize_text_field(get_query_var('event_key'))) : '';

if ($event_key == null) {
	wp_redirect(site_url('/events-list/'));
}
// print_r($event_key);

$secret_key = !empty(get_option(sanitize_key('ztcbl-auth-key'))) ? get_option(sanitize_key('ztcbl-auth-key')) : ' ';


global $woocommerce;
$ztcbl_api = new ZTCBL_Api();

$event_details_api = $ztcbl_api->ztcbl_details_api($event_key);
// sleep(1);
$event_embed_api = $ztcbl_api->ztcbl_embed_code_api($event_key);
$embed_response  = wp_remote_retrieve_response_code($event_embed_api);
// sleep(1);
$ztcbl_product_list    = $ztcbl_api->ztcbl_product_list($event_key);
$product_list_response = wp_remote_retrieve_response_code($ztcbl_product_list);
$event_embeded_data    = json_decode(wp_remote_retrieve_body($event_embed_api));
$events_details_response = wp_remote_retrieve_response_code($event_details_api);
$events_details_data   = json_decode(wp_remote_retrieve_body($event_details_api));
?>

<!DOCTYPE html>
<html lang="en">

<head>
	<meta charset="UTF-8">
	<meta name="viewport" content="width=device-width, initial-scale=1.0">
	<?php
	if (!is_wp_error($event_details_api) && $events_details_response == 200) {
	?>
		<meta property="og:locale" content="en_US" />
		<meta property="og:title" content="<?php echo esc_attr($events_details_data->data->title); ?>" />
		<meta property="og:description" content="<?php echo esc_attr($events_details_data->data->description); ?>" />
		<meta property="og:url" content="<?php echo esc_url(site_url('/events-detail/' . $event_key)); ?>" />
		<meta property="og:image" content="<?php echo esc_url($events_details_data->data->banner_image); ?>" />
		<meta property="og:image:width" content="500" />
		<meta property="og:image:height" content="350" />
		<meta property="og:image:type" content="image/jpeg" />
		<meta name="twitter:card" content="summary_large_image" />
		<meta name="twitter:title" content="<?php echo esc_attr($events_details_data->data->title); ?>" />
		<meta name="twitter:description" content="<?php echo esc_attr($events_details_data->data->description); ?>" />
		<meta name="twitter:image" content="<?php echo esc_url($events_details_data->data->banner_image); ?>" />
	<?php
	}
	wp_head(); ?>
	<style>
		html {
			margin-top: 0px !important;
		}
	</style>
</head>

<body style="background-color:#fff !important">
	<?php

	if (!is_wp_error($event_embed_api) && $embed_response == 200) {
		if ($event_embeded_data != 'error' && $embed_response == 200 && $event_embeded_data != null) {
	?>
			<div style='height:100vh;'>
				<!-- event_detail_embed_addon -->
				<section class='event_detail_embed ' id='event_detail_embed' event-key='<?php echo esc_attr($event_key); ?>'>
					<span class='show_mobile_view'>&#9776;</span>
					<?php
					// Define the allowed HTML tags and attributes.
					$allowed_tags = array(
						'div'    => array(
							'id' => true,
						),
						'script' => array(
							'src' => true,
						),
						'iframe' => array(
							'src'             => true,
							'id'              => true,
							'class'           => true,
							'allow'           => true,
							'allowfullscreen' => true,
						),
					);
					?>
					<?php echo wp_kses($event_embeded_data->data, $allowed_tags); ?>
				</section>
			<?php
		} else {
			?>
				<div><?php esc_html_e('Iframe not found', 'my-live-cart'); ?></div>
			<?php
		}
	} else {
			?>
			<div class="no_event_fount_msg">
				<h3><?php esc_html_e('We are currently experiencing difficulties connecting to our streaming services', 'my-live-cart'); ?></h3>
				<h3><?php esc_html_e('Please try again', 'my-live-cart'); ?></h3>
			</div>
			<?php
		}

		if (!is_wp_error($ztcbl_product_list) && $product_list_response == 200) {
			if (!is_wp_error($event_details_api) && $events_details_response == 200 && strtolower($events_details_data->data->status) == 'live') {
				$highlight_product_section = 'highlight_product_section';
				$product_list_section      = 'product_list_section';
			} else {
				$highlight_product_section = 'highlight_product_section';
				$product_list_section      = 'product_list_section';
			}
			$product_list_body = json_decode(wp_remote_retrieve_body($ztcbl_product_list));
			$product_list_data = array();
			$product_list_data = $product_list_body->data;

			$cart_count = WC()->cart->get_cart_contents_count();

			if (!empty($product_list_data)) {
			?>
				<div class="mobile_view_product_list">
					<span class="close-side-nav-button">&times;</span>
					<div class="mobile_product_view"></div>
				</div>
				<section class='product_detail_section'>
					<div style="display: flex;flex-wrap: wrap;justify-content: space-between; margin-bottom:10px">
						<div class='ztcbl_leave'>
							<?php if (!is_wp_error($event_details_api) && $events_details_response == 200 &&  strtolower( $events_details_data->data->status)  != 'completed' ) {?>
								<a href="javascript:void(0)" class="button"><?php esc_html_e('Leave', 'my-live-cart'); ?></a>
							<?php } ?>
						</div>
						<div class='ztcbl_cart'>
							<a href="<?php echo esc_url(wc_get_cart_url()); ?>" target="_blank"> <img src="<?php echo esc_url(ZTCBL_ASSETS_URL . 'images/cart_icon.png'); ?>" alt="cart_icon" height="50px" width="50px"></a>
							<span class='cart_item_value'><?php echo $cart_count; ?></span>
						</div>
					</div>
					<div id="section_highlight_product_section" class='high_light_product <?php echo esc_attr($highlight_product_section); ?>' data-action-url='<?php echo esc_url(admin_url('admin-ajax.php')); ?>' event-key="<?php echo esc_attr($event_key); ?>" random-key="<?php echo esc_attr($secret_key); ?>">
						<?php
						foreach ($product_list_data  as $value) {
							$product_id = $value->product_id;
							$product = wc_get_product($product_id);
							$image_url = get_the_post_thumbnail_url($product_id, 'full');
						?>
							<div style='display:none;' class='product_card hide_product_card' id='high_light_product_card' product_id='<?php echo esc_html($product_id); ?>' event_id='<?php echo esc_attr($value->event_id); ?>'>
								<img class='product_img' src='<?php echo esc_url($image_url); ?>' width='100' height='100' alt='Product Image '>
								<div class='badge'>Highlights</div>
								<?php
								if ($product) {
								?>
									<p class='product_name'><?php echo esc_html($product->get_title()); ?></p>
									<p class='product_price'>Price: <?php echo wp_kses_post($product->get_price_html()); ?></p>
									<a href='#' class='ztcbl-qv-button' data-product_id='<?php echo esc_attr($product_id); ?>' data-redirect-key='<?php echo esc_attr($event_key); ?>' data-redirect-id='<?php echo esc_attr($value->event_id); ?>'>View Details <span style='margin-left:8px;padding-top:3px;'><img src='<?php echo esc_url(ZTCBL_ASSETS_URL . 'images/arrow-icon_1.png'); ?>' alt='cart_icon' height='15px' width='15px'></span></a>
									<button data-product_id="<?php echo esc_attr($product->get_id()); ?>" data-product_sku="<?php echo esc_attr($product->get_sku()); ?>" data-redirect-key="<?php echo esc_attr($event_key); ?>" data-redirect-id="<?php echo esc_attr($value->event_id); ?>" class="button zt_cart_button product_type_<?php echo esc_attr($product->get_type()); ?>"><?php echo esc_html($product->add_to_cart_text()); ?></button>
							</div>
						<?php
								} else {
						?>
							<p class='product_name'><?php echo  esc_html($value->product_name); ?> </p>
							<p class='product_price'>Price: <?php echo esc_html($value->price); ?></p>
					</div>
			<?php
								}
							}

			?>
			<hr style="display:none;" class="highlight_product_separator">
			</div>
			<div class='product_list <?php echo esc_attr($product_list_section); ?>' id='product_list'>
				<?php
				foreach ($product_list_data as $value) {
					$event_id   = $value->event_id;
					$product_id = $value->product_id;
					$product    = wc_get_product($product_id);
					$image_url  = $value->image;
				?>
					<div class='product_card'>
						<img class='product_img' src='<?php echo esc_url($image_url); ?>' width='100' height='100' alt='Product Image '>
						<?php
						if ($product == true) {
						?>
							<p class='product_name' title="<?php echo esc_attr($product->get_title()); ?>"><?php echo esc_html($product->get_title()); ?></p>
							<p class='product_price'>Price: <?php echo wp_kses_post($product->get_price_html()); ?></p>
						<?php
						} else {
						?>
							<p class='product_name' title="<?php echo esc_html($value->product_name); ?>"><?php echo esc_html($value->product_name); ?></p>
							<p class='product_price'>Price: <?php echo esc_html($value->price); ?></p>
						<?php
						}
						if ($product == true) {
						?>
							<a href="#" class="ztcbl-qv-button" data-product_id="<?php echo esc_attr($product_id); ?>" data-redirect-key="<?php echo esc_attr($event_key); ?>" data-redirect-id="<?php echo esc_attr($value->event_id); ?>"> <?php echo esc_html('View Details'); ?> <span style="margin-left:8px;padding-top:3px"><img src="<?php echo esc_url(ZTCBL_ASSETS_URL . 'images/arrow-icon_1.png'); ?>" alt="cart_icon" height="15px" width="15px"></span></a>
							<button data-product_id="<?php echo esc_attr($product->get_id()); ?>" data-product_sku="<?php echo esc_attr($product->get_sku()); ?>" data-redirect-key="<?php echo esc_attr($event_key); ?>" data-redirect-id="<?php echo esc_attr($value->event_id); ?>" class="button zt_cart_button product_type_<?php echo esc_attr($product->get_type()); ?>"><?php echo esc_html($product->add_to_cart_text()); ?></button>
						<?php
						}
						?>
					</div>
				<?php
				}

				?>
			</div>
			</section>
			</div>
			<div id="cart-popup" class="cart-popup">
				<span class="close-button" onclick="closePopup()">&times;</span>
				<p id="cart-popup-text"></p>
			</div>

			<div id="ztcbl-feedback" class="ztcbl-modal">

				<!-- Modal content -->
				<div class="ztcbl-modal-content">
					<span class="close">&times;</span>
					<h3>Your Feedback</h3>
					<form action="#" id="feedback-form">
						<label for="ztcbl-user-name">Your Name <span style="color: #fe5d34;">*</span></label>
						<input type="text" name="user-name" id="ztcbl-user-name" required>
						<label for="ztcbl-user-email">Your Email <span style="color: #fe5d34;">*</span></label>
						<input type="text" name="user-email" id="ztcbl-user-email" required>
						<label for="ztcbl-user-feedback">Your Message <span style="color: #fe5d34;">*</span></label>
						<textarea name="feedback" id="ztcbl-user-feedback"></textarea>
						<p id="feedback-count"></p>
						<button id="ztcbl_sent_button" type="submit">Submit</button>
						<p id="feedback-message"></p>
					</form>
				</div>
			</div>

	<?php
			}
		}
	?>

	<script>
		jQuery(document).ready(function() {
			const characters = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';
			let key = '';

			for (var i = 0; i < 8; i++) {
				key += characters.charAt(Math.floor(Math.random() * characters.length));
			}

			let watcherId = localStorage.getItem('watcher_unique_id');

			if (!watcherId) {
				localStorage.setItem('watcher_unique_id', key);
			} else {
				key = watcherId;
			}

			function closePopup() {
				var popup = document.getElementById('cart-popup');
				popup.style.display = 'none';
			}
			jQuery('.mobile_view_product_list').hide();
			jQuery('.show_mobile_view').click(function() {
				var html = jQuery('.product_detail_section').html();
				jQuery('.mobile_product_view').html(html);
				jQuery('.show_mobile_view').hide();
				jQuery('.mobile_view_product_list').show();
				jQuery('.mobile_view_product_list').css('width', '363px');
			});

			jQuery('.close-side-nav-button').click(function() {
				jQuery('.mobile_view_product_list').hide();
				jQuery('.show_mobile_view').show();
				jQuery('.mobile_view_product_list').css('width', '0');
			});
		});

		jQuery("#ztcbl-user-feedback").on("input", function() {
			let valCount = jQuery(this).val().length;

			if (valCount > 100) {
				jQuery(this).val(jQuery(this).val().substring(0, 100));
				jQuery("#feedback-count").text('You cannot enter more than 100 characters.');
			} else {
				jQuery("#feedback-count").text('');
			}
		});

		jQuery(document).on('click','.ztcbl_leave .button',function(){
			jQuery(".ztcbl-modal").css("display", "block");
		});
	</script>
	<?php
	wp_footer();
	?>
</body>

</html>