<?php
/**
 * Plugin Uninstall
 *
 * @package mylivecart
 * @version 1.0.0
 */

// if uninstall.php is not called by WordPress, die!
if ( ! defined( 'WP_UNINSTALL_PLUGIN' ) ) {
	die;
}

$page_titles = array(
	'page_1' =>  __( 'Events List', 'mylivecart' ),
	'page_2' =>  __( 'Events Detail','mylivecart' ),
);

// Loop through the page titles and delete the corresponding pages.
foreach ( $page_titles as $page_slug => $page_title ) {
	$pages = get_posts(
		array(
			'post_type'   => 'page',
			'post_status' => 'publish',
			'name'        => sanitize_title( $page_title ),
			'numberposts' => 1,
		)
	);

	if ( ! empty( $pages ) ) {
		$page_id = $pages[0]->ID;
		wp_delete_post( $page_id, true );
	}
}

delete_option( sanitize_key( 'ztbcl_consumer_key' ) );
delete_option( sanitize_key( 'ztbcl_consumer_secret_key' ) );
delete_option( sanitize_key( 'ztcbl-auth-key') );
