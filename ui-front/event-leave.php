<?php

/**
 * File: event-leave.php
 * 
 * This file represents a WordPress template or script that displays a message to a user who has left an event.
 * It includes links to the checkout page and the events list page.
 *
 * @package mylivecart
 * @version 1.0.0
 */

if ( !defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}

wp_enqueue_style( 'ztcbl_font_family' );
wp_enqueue_style( 'ztcbl_style' );
?>
<div class="leave_event_main_div">
	<div class="leave_event_div">
		<h2><?php esc_html_e( 'You have left the event.', 'mylivecart' ); ?></h2>
		<div class='leave_cart_div'>
			<a class="button ztcbl_checkout_url" href="<?php echo esc_url( wc_get_checkout_url() ); ?>"><?php esc_html_e( 'Checkout', 'mylivecart' ); ?></a>
			<a class="button ztcbl_events_url" href="<?php echo esc_url( site_url( 'events-list' ) ); ?>"><?php esc_html_e( 'Events List', 'mylivecart' ); ?></a>
		</div>
	</div>
</div>