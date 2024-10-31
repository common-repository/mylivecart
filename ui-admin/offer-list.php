<?php

/**
 * File: offer-list.php
 *
 * Description: This file is responsible for displaying a list of offers from individual influencers and for updating it.
 *
 * @package mylivecart
 * @version 1.0.0
 */

 if (!defined('ABSPATH')) {
	exit; // Exit if accessed directly
}

wp_enqueue_style( 'ztcbl_font_family');
wp_enqueue_style('ztcbl_style');
wp_enqueue_script('ztcbl_js_file');

?>
<h1 style="font-family:poppins;"><?php esc_html_e('Offers List', 'mylivecart'); ?></h1>
<?php
if (isset($_REQUEST['inf-id'])) {
	$inf_id         = wp_unslash( sanitize_text_field( $_REQUEST['inf-id'] ) );
	$offer_list_api = ZTCBL_Api::ztcbl_get_offer_list( $inf_id );
	$response_code  = wp_remote_retrieve_response_code( $offer_list_api );

	if ( $response_code == 200 ) {
		$offer_list_body = json_decode( wp_remote_retrieve_body( $offer_list_api ) );
		if ( $offer_list_body->data != null ) {
			$offer_list_body_data = $offer_list_body->data->data;
			$nonce = wp_create_nonce( 'ztcbl_nonce' );

			$count = 0;
?>
			<table id='ztcbl_inf_table'>
				<tr>
					<th><?php esc_attr_e('#', 'mylivecart'); ?></th>
					<th><?php esc_attr_e('Event Name', 'mylivecart'); ?></th>
					<th><?php esc_attr_e('Status', 'mylivecart'); ?></th>
					<th><?php esc_attr_e('Start Date & Time', 'mylivecart'); ?></th>
					<th><?php esc_attr_e('Event Duration(In Min)', 'mylivecart'); ?></th>
					<th><?php esc_attr_e('Confirm/Cancel', 'mylivecart'); ?></th>
					<th><?php esc_attr_e('Actions', 'mylivecart'); ?></th>
				</tr>
				<?php
				foreach ($offer_list_body_data as $value) {
					if ($value->store_id === 1) {
				?>
						<tr>
							<td><?php echo esc_html(++$count); ?></td>
							<td><?php echo esc_html($value->title); ?></td>
							<td>
								<?php
								if ($value->influencer_status == null) {
									echo esc_html( '-' );
								} else {
									echo esc_html($value->influencer_status);
								}
								?>
							</td>
							<td><?php echo esc_html(date("d.m.Y", strtotime( $value->start_date ) ) . ', ' . $value->start_times ); ?></td>
							<td><?php echo esc_html($value->duration); ?></td>
							<td>
								<?php
								if ($value->influencer_status == null || $value->influencer_status == 'Rejected') {
									?>
									<a class="button" style="margin-right:5px" disabled><span offer_id="<?php echo esc_attr($value->id); ?>"><?php echo esc_html('Confirm'); ?></span></a>
									<a class="button" disabled><span offer_id="<?php echo esc_attr($value->id); ?>"><?php echo esc_html('Cancel'); ?></span></a>
									<?php
								} elseif (isset($value->confirm_offer)) {
									echo esc_html($value->confirm_offer);
								} else {
									?>
									<a class="button" href="<?php echo esc_url(admin_url('admin-post.php?action=confirm_inf_offer&offer_id=' . $value->id) . '&nonce=' . esc_html($nonce)); ?>" style="margin-right:5px;"><span offer_id="<?php echo esc_attr($value->id); ?>"><?php echo esc_html('Confirm'); ?></span></a>
									<a class="button" href="<?php echo esc_url(admin_url('admin-post.php?action=cancel_inf_offer&offer_id=' . $value->id) . '&nonce=' . esc_html($nonce)); ?>"><span offer_id="<?php echo esc_attr($value->id); ?>"><?php echo esc_html('Cancel'); ?></span></a>
									<?php
								}
								?>
							</td>
							<td>
								<?php
								if ($value->influencer_status == null) {
									?>
									<span id="update-deshicon" class="dashicons dashicons-edit update-deshicon" data-offer-id="<?php echo esc_attr( $value->id ); ?>"></span>
									<a class="del_deshi_link" href="<?php echo esc_url(admin_url('admin-post.php?action=del_inf_offer&offer_id=' . $value->id . '&nonce=' . esc_html( $nonce ) ) ); ?>">
										<span class="dashicons dashicons-trash delete-deshicon" offer_id="<?php echo esc_attr( $value->id ); ?>"></span>
									</a>
									<?php
								} else {
									echo esc_html( '-' );
								}
								?>
							</td>
						</tr>
	<?php
					}
				}
				?>
				</table>
				<?php
			} else {
				?>
				<p class="inf_error_msg"><?php esc_html_e('Firstly create an offer.', 'mylivecart'); ?></p>
				<?php
			}
		} else {
			?>
			<p class="inf_error_msg"><?php esc_html_e('Firstly create a store or check your configuration.', 'mylivecart'); ?></p>
			<?php
		}
	}
	?>
	<div class="zt-loader"></div>
	<div id="infModal" class="ztcbl-modal">

		<!-- Modal content -->
		<div class="ztcbl-modal-content">
			<span class="close">&times;</span>
			<?php
			$cons_key            = get_option('ztbcl_consumer_key');
			$consumer_key        = isset($cons_key) ? $cons_key : '';
			$cons_sec_key        = get_option('ztbcl_consumer_secret_key');
			$consumer_secret_key = isset($cons_sec_key) ? $cons_sec_key : '';
			$store_api           = ZTCBL_Api::ztcbl_get_store_list($consumer_key, $consumer_secret_key);
			$response            = wp_remote_retrieve_response_code($store_api);

			if ($response != 200) {
				$store_name_ = '';
				$store_id_   = '';
				?>
				<p class="inf_error_msg"><?php esc_html_e('Firstly create a store or check your configuration.', 'mylivecart'); ?></p>
				<?php
			} else {
				$store_name = json_decode(wp_remote_retrieve_body($store_api));

				if ($store_name->data != null) {
					$store_name_ = $store_name->data[0]->name;
					$store_id_   = $store_name->data[0]->id;
				} else {
					$store_name_ = $store_name->data;
					$store_name_ = $store_name->data;
					?>
					<p class="inf_error_msg"><?php esc_html_e('Firstly create a store or check your configuration.', 'mylivecart'); ?></p>
					<?php
				}
			}
			?>
			<h2><?php esc_html_e('Event Details ', 'mylivecart'); ?></h2>
			<form action="" id="inf_offer_update" class='inf_offer'>
				<input type="hidden" name="influencer_id" id="influencer_id">
				<input type="hidden" name="url" id="url" value="<?php echo esc_url( admin_url( 'admin.php?page=all-influencer&inf-id' ) ); ?>">
				<input type="hidden" name="redirect_url" id="redirect_url">
				<input type="hidden" name="offer_id" id="offer_id">
				<div>
					<div class='inf_first_row'>
						<div class='store_name_div'>
							<label for="store_name"><?php esc_html_e('Store', 'mylivecart'); ?> <span style="color: #fe5d34;">*</span></label>
							<select name="store_name" id="store_name" name="all_product_list" id="all_product_list">
								<option value="" disabled><?php esc_html_e( 'Choose Store', 'mylivecart' ); ?></option>
								<option value="<?php echo esc_html( $store_id_ ); ?>"><?php echo esc_html( $store_name_ ); ?></option>
								<select>
						</div>
						<div class='store_name_div'>
							<label for="event_title"><?php esc_html_e( 'Title', 'mylivecart' ); ?> <span style="color: #fe5d34;">*</span></label>
							<input type="text" name="event_title" id="event_title" required>
						</div>
					</div>
					<div>
						<div>
							<label for="zt-table-search" class="category-title"><?php esc_html_e( 'Product', 'mylivecart' ); ?> <span style="color: #fe5d34;">*</span></label>
							<div class="zt-category-dropdown">
								<span id="zt-badge-dismiss-default" class="zt-selected-product">
									<div><?php esc_html_e( 'Select Product', 'mylivecart' ); ?></div>
									<button type="button" class="zt-dropdown-toggle" data-dismiss-target="#badge-dismiss-default" aria-label="Remove">
										<span class="dashicons dashicons-arrow-down-alt2" id="zt-arrowDown"></span>
										<span class="dashicons dashicons-arrow-up-alt2" id="zt-arrowUp"></span>
									</button>
								</span>
							</div>
						</div>
						<div class="zt-product-dropdown">
							<div class="zt-relative">
								<input type="text" class="zt-search-field" placeholder="Search Product" />
								<svg class="zt-search-icon" fill="none" viewBox="0 0 24 24" stroke="currentColor">
									<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
								</svg>
							</div>
							<ul class="zt-product-list" by-offer='false'>
								<?php
								$product_list = wc_get_products(
									array(
										'status' => 'publish',
										'limit'  => -1,
									)
								);
								foreach ( $product_list as $product ) {
								?>
									<li value="<?php echo esc_attr( $product->get_id() ); ?>"><?php echo esc_html( $product->get_title() ); ?></li>
								<?php
								}
								?>
								<!-- Product list items will be generated dynamically -->
							</ul>
							<p class="zt-no-result" style="display: none;text-align:center;"><?php esc_html_e( 'No result found', 'mylivecart' ); ?></p>
						</div>
					</div>
					<div>
						<label for="event_desc"><?php esc_html_e( 'Description', 'mylivecart' ); ?> <span style="color: #fe5d34;">*</span></label>
						<textarea name="event_desc" id="event_desc" cols="30" rows="2"></textarea>
					</div>
					<div class='inf_events_details'>
						<div class='inf_event_date'>
							<label for="event_date"><?php esc_html_e('Show Start Date', 'mylivecart'); ?> <span style="color: #fe5d34;">*</span></label>
							<input type="date" name="event_date" id="event_date">
						</div>
						<div class='inf_event_time'>
							<label for="event_time"><?php esc_html_e('Show Start Time', 'mylivecart'); ?> <span style="color: #fe5d34;">*</span></label>
							<input type="time" name="event_time" id="event_time">
						</div>
						<div class='inf_event_duration'>
							<label for="event_duration"><?php esc_html_e('Duration', 'mylivecart'); ?> <span style="color: #fe5d34;">*</span></label>
							<select name="event_duration" id="event_duration">
								<option value="" selected disabled><?php esc_html_e('00:00:00', 'mylivecart'); ?></option>
								<option value="15"><?php esc_html_e('15min', 'mylivecart'); ?></option>
								<option value="30"><?php esc_html_e('30min', 'mylivecart'); ?></option>
								<option value="45"><?php esc_html_e('45min', 'mylivecart'); ?></option>
								<option value="60"><?php esc_html_e('1hour', 'mylivecart'); ?></option>
								<option value="75"><?php esc_html_e( '1.25hour', 'mylivecart' ); ?></option>
								<option value="90"><?php esc_html_e( '1.30hour', 'mylivecart' ); ?></option>
								<option value="120"><?php esc_html_e( '2hour', 'mylivecart' ); ?></option>
							</select>
						</div>
					</div>
					<div style='display:flex;justify-content:center;'>
						<?php
						if ($response != 200 || $store_name->data == null) {
							?>
							<button type="submit" id="offer_submit" disabled><?php esc_html_e( 'Submit', 'mylivecart' ); ?></button>
							<?php
						} else {
							?>
							<button type="submit" id="offer_submit"><?php  esc_html_e( 'Submit', 'mylivecart' ); ?></button>
							<?php
						}
						?>
					</div>
				</div>
			</form>
			<div id='response_message'></div>
		</div>

	</div>