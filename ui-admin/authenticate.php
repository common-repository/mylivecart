<?php
/**
 * File: authenticate.php
 *
 * Description: This file is responsible for connecting with MyLivecart .
 *
 * @package mylivecart
 * @version 1.0.0
 */

 if (!defined('ABSPATH')) {
	exit; // Exit if accessed directly
}

if ( isset( $_GET['configKey'] ) ) {
	update_option( sanitize_key( 'ztcbl-auth-key' ), wp_unslash( sanitize_text_field( $_GET['configKey'] ) ) );
	wp_safe_redirect( admin_url( 'admin.php?page=ztcbl-setting&tab=configuration' ) );
}

wp_enqueue_style( 'ztcbl_font_family');
wp_enqueue_style( 'ztcbl_style' );
wp_enqueue_script( 'ztcbl_js_file' );
$cons_key            = get_option( sanitize_key( 'ztbcl_consumer_key' ) );
$consumer_key        = isset( $cons_key ) ? $cons_key : '';
$cons_sec_key        = get_option( sanitize_key( 'ztbcl_consumer_secret_key' ) );
$consumer_secret_key = isset( $cons_sec_key ) ? $cons_sec_key : '';
?>
<div class="zt-loader"></div>
<div class='auth_div'>
<form action="" id="mylivecart-connector">  
	<table id='ztcbl_table'>
		<tbody>
			<tr>
				<td><?php esc_html_e( 'Store Url', 'mylivecart' ); ?></td>
				<td><?php echo esc_url( site_url() ); ?></td>
			</tr>

			<tr>
				<td><?php esc_html_e( 'Consumer Key', 'mylivecart' ); ?> <span style="color: #fe5d34;">*</span></td>
				<td>
					<input id='ztcbl_consumer_key' type="text" placeholder="Please enter Consumer key." value = "<?php echo esc_html( $consumer_key ); ?>" required>
				</td>
			</tr>
			<tr>
				<td><?php esc_html_e( 'Consumer Secret Key', 'mylivecart' ); ?> <span style="color: #fe5d34;">*</span></td>
				<td>
				<input id='ztcbl_consumer_secret_key' type="text" placeholder="Please enter Consumer Secret key." value = "<?php echo esc_html( $consumer_secret_key ); ?>" required>
				</td>
			</tr>
		</tbody>
	</table>
	<p><?php esc_html_e( 'Note - Please note that you should generate a WooCommerce REST API key in order to obtain both a Consumer Key and a Consumer Secret Key.','mylivecart'); ?></p>
	<div class='ztcbl_validate_response'></div>
	<button type="submit" class="button button-primary" id='ztcbl_validate'><?php esc_html_e( 'Connect to MyLive Cart', 'mylivecart' ); ?> </button>
	</form>
</div>
<?php
