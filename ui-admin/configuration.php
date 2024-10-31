<?php
/**
 * API Authentication Setting Template
 *
 * Description: The `authenticate.php` file is used to take configuration key of API and verify it.
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

if ( isset( $_GET['configKey'] ) ) {
	update_option( sanitize_key( 'ztcbl-auth-key' ), wp_unslash( sanitize_text_field( $_GET['configKey'] ) ) );
	wp_safe_redirect( admin_url( 'admin.php?page=ztcbl-setting&tab=configuration' ) );
}

$secret_key = get_option( sanitize_key( 'ztcbl-auth-key' ) );
?>
<div class="auth_div">
	<form id="auth_form" action="<?php echo esc_url( admin_url( 'admin-post.php' ) ); ?>" method="post">
		<input type="hidden" name="action" value="save_auth_key">
		<?php wp_nonce_field( 'save_auth_key', 'auth_key_handler' ); ?>
		<table>
			<tr>
				<td><label for=""><?php esc_html_e( 'Authentication Key', 'mylivecart' ); ?> <span style="color: #fe5d34;">*</span></label></td>
				<td class="pass_field"> <input type="password" name="auth_key" id="auth_key" value="<?php echo esc_attr( $secret_key ); ?>" required><i class="dashicons dashicons-hidden"></i></td>
			</tr>
			<tr>
				<td></td>
				<td>
					<?php
					if( $secret_key == '' ){
						?>
						<p class="inf_error_msg"><?php esc_html_e( 'Firstly create a store and connect to MyLiveCart to obtain a key.', 'mylivecart' ); ?></p>
						<?php
					}
					else{
					$ztbl_api       = new ZTCBL_Api();
					$event_list_api = $ztbl_api->ztcbl_user_verify();
					if ( is_wp_error( $event_list_api ) ) {
						?>
						<p class="response_invalid"><?php esc_html_e('Host not found', 'mylivecart'); ?></p>
						<?php
					} else {
						$server_response = $event_list_api['response']['code'];
						if ( 200 === $server_response ) {
							?>
							<p class="response_success"><?php esc_html_e('You are authenticated', 'mylivecart'); ?></p>
							<?php
						} elseif ( 400 === $server_response ) {
							?>
							<p class="response_invalid"><?php esc_html_e('Invalid Api Key', 'mylivecart'); ?></p>
							<?php
						} elseif ( 404 === $server_response ) {
							?>
							<p class="response_failed"><?php esc_html_e('Unable to connect with server', 'mylivecart'); ?></p>
							<?php
						} elseif ( is_wp_error( $event_list_api ) ) {
							?>
							<p class="response_invalid"><?php esc_html_e('Host not found', 'mylivecart'); ?></p>
							<?php
						} else {
							?>
							<p class="response_failed"><?php esc_html_e('Unable to connect with server', 'mylivecart'); ?></p>
							<?php
						}
					}
				}
					?>
				</td>
			</tr>
			<tr>
				<td><button class="button button-primary" name="submit"><?php esc_html_e( 'Submit', 'mylivecart' ); ?></button></td>
			</tr>
		</table>
	</form>
</div>
