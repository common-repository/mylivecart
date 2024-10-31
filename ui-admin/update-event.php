<?php
/**
 * File: update-event.php
 *
 * Description: This file is responsible for updating events.
 *
 * @package mylivecart
 * @version 1.0.0
 */

 if (!defined('ABSPATH')) {
	exit; // Exit if accessed directly
}

wp_enqueue_style( 'ztcbl_font_family');
wp_enqueue_style( 'ztcbl_style' );
wp_enqueue_script( 'ztcbl_js_file' );
$secret_key = ! empty( get_option( sanitize_key( 'ztcbl-auth-key' ) ) ) ? get_option( sanitize_key( 'ztcbl-auth-key' ) ) : ' ';
$event_key  = isset( $_REQUEST['e-id'] ) ? wp_unslash( sanitize_text_field( $_REQUEST['e-id'] ) ) : ' ';
?>
<?php
$host_details = ZTCBL_Api::ztcbl_user_details();
$host_detail  = json_decode( wp_remote_retrieve_body( $host_details ) );
?>
<div class="zt-loader"></div>
<div class='create-event-div'>
	<h3><?php esc_html_e( 'Update Event', 'mylivecart' ); ?></h3>
	<?php
	$cons_key            = get_option( sanitize_key( 'ztbcl_consumer_key' ) );
	$consumer_key        = isset( $cons_key ) ? $cons_key : '';
	$cons_sec_key        = get_option( sanitize_key( 'ztbcl_consumer_secret_key' ) );
	$consumer_secret_key = isset( $cons_sec_key ) ? $cons_sec_key : '';
	$store_api           = ZTCBL_Api::ztcbl_get_store_list( $consumer_key, $consumer_secret_key );
	$response            = wp_remote_retrieve_response_code( $store_api );
	$store_name_ = '';
	$store_id_   = '';
	if ( $response != 200 ) {
		$store_name_ = '';
		$store_id_   = '';
		?>
		<p class="inf_error_msg"><?php esc_html_e('Firstly create a store or check your configuration.', 'mylivecart'); ?></p>
		<?php
	} else {
		$store_name = json_decode( wp_remote_retrieve_body( $store_api ) );

		if ( $store_name->data != null ) {
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
	<form action="" id="update_event_form" enctype='multipart/form-data'>
		<input type="hidden" name="zt-event-key" id="zt-event-key" value="<?php echo esc_attr( $event_key ); ?>">
		<input type="hidden" name="zt-secret-key" id="zt-secret-key" value="<?php echo esc_attr( $secret_key ); ?>">
		<input type="hidden" name="event_list_url" id="event_list_url" value = "<?php echo esc_url(admin_url('admin.php?page=all-events'))?>">
		<div class='inf_row'>
			<div class='inf_row_div'>
				<label for="store_name"><?php esc_html_e( 'Store', 'mylivecart' ); ?> <span style="color: #fe5d34;">*</span></label>
                <input type="hidden" name="zt-self-host" id="zt-self-host" value="<?php echo esc_html( $host_detail->data->id ); ?>">
				<select name="store_name" id="store_name" name="all_product_list" id="all_product_list" required>
					<option value="" disabled><?php esc_html_e( 'Choose Store', 'mylivecart' ); ?></option>
					<option value="<?php echo esc_attr( $store_id_ ); ?>"><?php echo esc_html( $store_name_ ); ?></option>
					<select>
			</div>
			<div class="inf_row_div" style="position:relative;">
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
							$product_image = wp_get_attachment_image_src( get_post_thumbnail_id( $product->get_id() ), 'single-post-thumbnail' );
							?>
							<li value="<?php echo esc_attr( $product->get_id() ); ?>">								
								<div class='product_img_name'>
									<div class='product_img'><img src="<?php echo isset($product_image[0]) ? esc_url( $product_image[0] ) : ''; ?>" alt="product_img" width="30" height="30"></div>
									<div class='product_name'><?php echo esc_html( $product->get_title() ); ?></div>
								</div>
							</li>
							<?php
						}
						?>
						<!-- Product list items will be generated dynamically -->
					</ul>
					<p class="zt-no-result" style="display: none;text-align:center;"><?php esc_html_e( 'No result found', 'mylivecart' ); ?></p>
				</div>
			</div>

		</div>
		<div class='inf_row'>
			<div class='inf_row_div'>
				<label for="store_title"><?php esc_html_e( 'Title', 'mylivecart' ); ?> <span style="color: #fe5d34;">*</span></label>
				<input type="text" name="store_title" id="store_title" value="" required>
			</div>
			<div class='inf_row_div'>
				<label for="store_desc"><?php esc_html_e( 'Description', 'mylivecart' ); ?> <span style="color: #fe5d34;">*</span></label>
				<input type="text" name="store_desc" id="store_desc" required>
			</div>
		</div>
		<div class='inf_row'>
			<div class='inf_event_date'>
				<label for="event_date"><?php esc_html_e( 'Show Start Date', 'mylivecart' ); ?> <span style="color: #fe5d34;">*</span></label>
				<input type="date" name="event_date" id="event_date" required>
			</div>
			<div class='inf_event_time'>
				<label for="event_time"><?php esc_html_e( 'Show Start Time', 'mylivecart' ); ?> <span style="color: #fe5d34;">*</span></label>
				<input type="time" name="event_time" id="event_time" required>
			</div>
			<div class='inf_event_date'>
				<label for="event_date"><?php esc_html_e( 'Show End Date', 'mylivecart' ); ?></label>
				<input type="date" name="event_end_date" id="event_end_date" disabled>
			</div>
			<div class='inf_event_time'>
				<label for="event_date"><?php esc_html_e( 'Show End Time', 'mylivecart' ); ?></label>
				<input type="time" name="event_end_time" id="event_end_time" disabled>
			</div>
		</div>
		<div class="inf_row">
			<div class='inf_row_div'>
				<label for="zt-banner-img"><?php esc_html_e( 'Add Banner Image/Video', 'mylivecart' ); ?> <span style="color: #fe5d34;">*</span></label>
				<div id="zt-banner-preview"></div>
				<input type="file" name="zt-banner-img" id="zt-banner-img">
				<input type="hidden" name="zt-banner" id="zt-banner">
				<p><span class="zt-upload-img" id="zt-banner-img-span"><?php esc_html_e( 'Uplaod ', 'mylivecart' ); ?></span></p>
				<p style="color:red;" id="image-response"></p>
			</div>
			<div class='inf_row_div'>
				<label for="zt-cover-img"><?php esc_html_e( 'Choose cover Image', 'mylivecart' ); ?> <span style="color: #fe5d34;">*</span></label>
				<div id="zt-cover-preview"></div>
				<input type="file" name="zt-cover-img" id="zt-cover-img">
				<input type="hidden" name="zt-cover" id="zt-cover">
				<p><span class="zt-upload-img" id="zt-cover-img-span"><?php esc_html_e( 'Uplaod', 'mylivecart' ); ?></span></p>
				<p style="color:red;" id="cover-image-response"></p>
			</div>
		</div>
		<div class='inf_row'>
			<div class='inf_row_div'>
				<label for="zt-host-name"><?php esc_html_e( 'Choose or Add a Host', 'mylivecart' ); ?> <span style="color: #fe5d34;">*</span></label>
				<select name="" id="zt-host-name" required>
					<option value="" disabled>Choose Host</option>
					<option value="<?php echo esc_html( $host_detail->data->id ); ?>"><?php echo esc_html( $host_detail->data->first_name .'(Self)'); ?></option>
				</select>
			</div>
			<div class='inf_row_div'>
				<label for="event_duration"><?php esc_html_e( 'Duration', 'mylivecart' ); ?> <span style="color: #fe5d34;">*</span></label>
				<select name="event_duration" id="event_duration" required>
					<option value="" selected disabled><?php esc_html_e( '00:00:00', 'mylivecart' ); ?></option>
					<option value="15"><?php esc_html_e( '15min', 'mylivecart' ); ?></option>
					<option value="30"><?php esc_html_e( '30min', 'mylivecart' ); ?></option>
					<option value="45"><?php esc_html_e( '45min', 'mylivecart' ); ?></option>
					<option value="60"><?php esc_html_e( '1hour', 'mylivecart' ); ?></option>
					<option value="75"><?php esc_html_e( '1.25hour', 'mylivecart' ); ?></option>
					<option value="90"><?php esc_html_e( '1.30hour', 'mylivecart' ); ?></option>
					<option value="120"><?php esc_html_e( '2hour', 'mylivecart' ); ?></option>
				</select>
			</div>
		</div>
		<div class="inf_row checkbox_div" >
			<div>
				<div class='zt-chat-div'>
					<?php esc_html_e( 'Allow Anonymous Chat', 'mylivecart' ); ?>
					<label class="zt-switch" for="zt-chat-checkbox">
						<input class="zt-chat-checkbox" type="checkbox" id="zt-chat-checkbox">
						<span class="zt-slider round"></span>
					</label>
				</div>
				<p style='color:#999;'>
				<?php
				esc_html_e('Note - By enabling anonymous chat functionality, the watcher can engage in real-time text conversations with the host and allow the host to interact directly during the broadcast.','mylivecart');
				?>
				</p>
			</div>
			<div class='zt-highlight'>
			<div class='zt-highlight-div'>
					<?php esc_html_e( 'Enable AI to generate product highlight clips', 'mylivecart' ); ?>
					<label class="zt-switch" for="zt-hightlight-checkbox">
						<input class="zt-hightlight-checkbox" type="checkbox" id="zt-hightlight-checkbox">
						<span class="zt-highlight-slider round"></span>
					</label>
				</div>
				<p style='color:#999;'>
				<?php
				esc_html_e('Note - By enabling AI to generate product highlight clips, you may experience a slight delay in the video recording process. We truly appreciate your understanding and patience while AI creates amazing product highlight videos.','mylivecart');
				?>
				</p>
			</div>
		</div>
		<div style='display:flex;justify-content:center;'>
			<?php
			if ( $response != 200 || $store_name->data == null ) {
				?>
				<button type="submit" id="offer_submit" disabled><?php esc_html_e('Submit', 'mylivecart'); ?></button>
				<?php
			} else {
				?>
				<button type="submit" id="offer_submit"><?php esc_html_e('Submit', 'mylivecart'); ?></button>
				<?php
			}
			?>
		</div>
		<div style='display:flex;justify-content:center;color:red;margin-top:20px;' id='create_response'></div>

	</form>

</div>

<script>
	document.getElementById('zt-cover-img').addEventListener('change', function(event) {
		const imagePreview = document.getElementById('zt-cover-preview');
		imagePreview.innerHTML = '';

		const allowedFileTypes = ['jpeg', 'jpg', 'png'];

		const selectedFile = event.target.files[0];

		if (selectedFile) {
			const fileName = selectedFile.name;
			const extension = fileName.split('.').pop().toLowerCase();

			if (allowedFileTypes.includes(extension)) {
				const reader = new FileReader();

				reader.onload = function() {
					const img = document.createElement('img');
					img.src = reader.result;
					img.alt = 'Selected Image';
					img.style.maxWidth = '100%';
					imagePreview.appendChild(img);
				};
				jQuery('#cover-image-response').html('');
				reader.readAsDataURL(selectedFile);
			} else {
				jQuery('#cover-image-response').html('File type must in jpeg, jpg, png');
				// Display an error message or prevent file upload
			}
		}
	});

	document.getElementById('zt-banner-img').addEventListener('change', function(event) {
		const imagePreview = document.getElementById('zt-banner-preview');
		imagePreview.innerHTML = '';

		const allowedFileTypes = ['jpeg', 'jpg', 'png', 'mp4', 'webm'];

		const selectedFile = event.target.files[0];

		if (selectedFile) {
			const fileName = selectedFile.name;
			const extension = fileName.split('.').pop().toLowerCase();

			if (allowedFileTypes.includes(extension)) {
				if (selectedFile.type.startsWith('image/')) {
					// If the selected file is an image
					const reader = new FileReader();

					reader.onload = function() {
						const img = document.createElement('img');
						img.src = reader.result;
						img.alt = 'Selected Image';
						img.style.maxWidth = '100%';
						imagePreview.appendChild(img);
					};

					reader.readAsDataURL(selectedFile);
				} else if (selectedFile.type.startsWith('video/')) {
					// If the selected file is a video
					const video = document.createElement('video');
					video.src = URL.createObjectURL(selectedFile);
					video.alt = 'Selected Video';
					video.controls = false;
					video.autoplay = true;
					video.muted = true;
					video.loop = true;
					video.style.maxWidth = '100%';
					imagePreview.appendChild(video);
				}
			} else {
				jQuery('#image-response').html('File type must in jpeg, jpg, png, mp4, webm');
				// Display an error message or prevent file upload
			}
		}
	});
</script>
