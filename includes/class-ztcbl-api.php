<?php

/**
 * File: class-ztcbl-api.php
 *
 * Description: This file contains the ZTCBL API class responsible for handling API interactions
 * and data retrieval for the ZTCBL plugin. It provides methods for communicating with external APIs,
 * processing API responses, and performing various operations related to the plugin's functionality.
 *
 * @package mylivecart
 * @version 1.0.0
 */

if (!defined('ABSPATH')) {
	exit; // Exit if accessed directly
}
/**
 * ZTCBL API Class.
 *
 * The ZTCBL API class handles API interactions and data retrieval for the ZTCBL plugin.
 */
class ZTCBL_Api
{

	/**
	 * Verify User
	 *
	 * @return mixed $response The response data in JSON format.
	 */
	public function ztcbl_user_verify()
	{
		$secret_key   = get_option(sanitize_key('ztcbl-auth-key'));
		$api_url      = ZTCBL_API_URL . 'verify/user';

		$args = array(
			'headers' => array(
				'secret-key' => $secret_key,
				'api-type'   => 1,
				'store-url'  => site_url(),
			),

		);
		$response = wp_remote_get($api_url, $args);
		return $response;
	}

	/**
	 * Fetching data of Event list.
	 *
	 * @param int $page The page number.
	 * @param int $per_page The number of items per page.
	 * @return mixed $response The response data in JSON format.
	 */
	public function ztcbl_list_api($page = 1, $per_page = 10)
	{
		$secret_key   = get_option(sanitize_key('ztcbl-auth-key'));
		$api_url      = ZTCBL_API_URL . 'event/list/';
		$cons_sec_key        = get_option( sanitize_key( 'ztbcl_consumer_secret_key' ) );
		$consumer_secret_key = isset( $cons_sec_key ) ? $cons_sec_key : '';
		$query_params = array(
			'page'     => $page,
			'per_page' => $per_page,
		);

		$url  = add_query_arg($query_params, $api_url);
		$args = array(
			'headers' => array(
				'secret-key' => $secret_key,
				'api-type'   => 1,
				'store-url'  => site_url(),
				'consumer-secret' => $consumer_secret_key,
			),

		);
		$response = wp_remote_get($url, $args);
		return $response;
	}

	/**
	 * Fetching Data from Events Detail Api.
	 *
	 * @param string $event_key The key of particular event.
	 * @return mixed $response The response data in JSON format.
	 */
	public function ztcbl_details_api($event_key)
	{
		$secret_key = get_option(sanitize_key('ztcbl-auth-key'));
		$api_url    = ZTCBL_API_URL . 'event/' . $event_key . '/details';
		$args       = array(
			'headers' => array(
				'secret-key' => $secret_key,
				'api-type'   => 1,
			),
		);
		$response   = wp_remote_get($api_url, $args);
		return $response;
	}

	/**
	 * Fetching Embeded Code for Events
	 *
	 * @param string $event_key The key of particular event.
	 * @return mixed $response The response data in JSON format.
	 */
	public function ztcbl_embed_code_api($event_key)
	{
		$secret_key = get_option(sanitize_key('ztcbl-auth-key'));
		$api_url    = ZTCBL_API_URL . 'event/' . $event_key . '/embed/code';
		$args       = array(
			'headers' => array(
				'secret-key' => $secret_key,
				'api-type'   => 1,
			),
		);
		$response   = wp_remote_get($api_url, $args);

		return $response;
	}

	/**
	 * Fetching product list of particular event.
	 *
	 * @param string $event_key The key of particular event.
	 * @return mixed $response The response data in JSON format.
	 */
	public function ztcbl_product_list($event_key)
	{
		$secret_key = get_option(sanitize_key('ztcbl-auth-key'));
		$api_url    = ZTCBL_API_URL . 'event/' . $event_key . '/products';
		$args       = array(
			'headers' => array(
				'secret-key' => $secret_key,
				'api-type'   => 1,
			),

		);
		$response = wp_remote_get($api_url, $args);
		return $response;
	}

	/**
	 * Update status of product when product is view,add_to_cart and sell.
	 *
	 * @param string $event_key The key of particular event.
	 * @param int    $product_id The Product ID of particular Product.
	 * @param int    $event_id The Event ID of particular Event.
	 * @param string $status The Status of product like view,add_to_cart and sell.
	 * @param string $watcher_id It is unique Id to identify unique user.
	 * @param int    $quantity Quantity of product.
	 * @return mixed $response.
	 */
	public function ztcbl_update_product_status($event_key, $product_id, $event_id, $status, $watcher_id, $quantity)
	{
		$secret_key = get_option(sanitize_key('ztcbl-auth-key'));
		$api_url    = ZTCBL_API_URL . 'event/' . $event_key . '/update/product/status';
		$body       = array(
			'product_id' => $product_id,
			'event_id'   => $event_id,
			'status'     => $status,
			'user_key'   => $watcher_id,
			'quantity'   => $quantity,
		);
		$args       = array(
			'headers' => array(
				'secret-key' => $secret_key,
				'api-type'   => 1,
			),
			'body'    => $body,
		);
		$response   = wp_remote_post($api_url, $args);
		return $response;
	}

	/**
	 * Check status of event like Upcoming,Live and Completed.
	 *
	 * @param string $event_key The key of particular event.
	 * @return mixed $response The response data in JSON format.
	 */
	public function ztcbl_check_event_status($event_key)
	{
		$secret_key = get_option(sanitize_key('ztcbl-auth-key'));
		$api_url    = ZTCBL_API_URL . 'event/' . $event_key . '/status';
		$args       = array(
			'headers' => array(
				'secret-key' => $secret_key,
				'api-type'   => 1,
			),
		);
		$response   = wp_remote_get($api_url, $args);
		return $response;
	}

	/**
	 * Generates a pagination string for displaying page numbers.
	 *
	 * @param int $current_page The current page number.
	 * @param int $total_pages The total number of pages.
	 * @param int $max_pages_displayed The maximum number of pages to display.
	 * @return  string The generated pagination string.
	 */
	public function ztcbl_generate_pagination($current_page, $total_pages, $max_pages_displayed)
	{
		$start_page = (ceil($current_page / $max_pages_displayed) - 1) * $max_pages_displayed + 1;
		$end_page   = min($start_page + $max_pages_displayed - 1, $total_pages);

		// Generate the page numbers.
		$page_numbers = range($start_page, $end_page);

		return $page_numbers;
	}

	/**
	 * Show Influencer List
	 *
	 * @return mixed $response The response data in JSON format.
	 */
	public static function ztcbl_influencer_list()
	{
		$api_url  = ZTCBL_API_URL . 'inf/list';
		$response = wp_remote_get($api_url);
		return $response;
	}

	/**
	 * Create an offer for a specific Influencer.
	 *
	 * @param int    $influencer_id The unique identifier of the Influencer receiving the offer.
	 * @param int    $store_id The unique identifier of the store making the offer.
	 * @param string $title The title or name of the offer.
	 * @param string $description A brief description of the offer.
	 * @param string $start_time The start date and time of the offer in a valid date-time format.
	 * @param int    $duration The duration of the offer in minutes.
	 * @param object $products An object representing the products included in the offer.
	 * @return mixed $response The response data in JSON format.
	 */
	public static function ztcbl_create_inf_offer($influencer_id, $store_id, $title, $description, $start_time, $duration, $products)
	{
		$secret_key = get_option(sanitize_key('ztcbl-auth-key'));
		$api_url    = ZTCBL_API_URL . 'create/offer';
		$body       = array(
			'influencer_id' => $influencer_id,
			'store_id'      => $store_id,
			'title'         => $title,
			'description'   => $description,
			'start_time'    => $start_time,
			'duration'      => $duration,
			'products'      => $products,
		);
		$args       = array(
			'headers' => array(
				'secret-key' => $secret_key,
				'api-type'   => 1,
			),
			'body'    => $body,
		);
		$response   = wp_remote_post($api_url, $args);
		return $response;
	}

	/**
	 * Get the name of store
	 *
	 * @param string $cons_key It is wocommerce consumer key.
	 * @param string $cons_secret It is wocommerce consumer secret key.
	 * @return mixed $response The response data in JSON format.
	 */
	public static function ztcbl_get_store_list($cons_key, $cons_secret)
	{
		$secret_key = get_option(sanitize_key('ztcbl-auth-key'));
		$api_url    = ZTCBL_API_URL . 'get/store/list';
		$body       = array(
			'consumer_secret' => $cons_secret,
			'consumer_key'    => $cons_key,
		);
		$args       = array(
			'headers' => array(
				'secret-key' => $secret_key,
				'api-type'   => 1,
			),
			'body'    => $body,
		);
		$response   = wp_remote_post($api_url, $args);
		return $response;
	}

	/**
	 * Get the list of offers of particular influencer
	 *
	 * @param int $inf_id The unique identifier of the Influencer.
	 * @return mixed $response The response data in JSON format.
	 */
	public static function ztcbl_get_offer_list($inf_id)
	{
		$secret_key = get_option(sanitize_key('ztcbl-auth-key'));
		$api_url    = ZTCBL_API_URL . 'get/inf/' . $inf_id . '/offer/list';
		$args       = array(
			'headers' => array(
				'secret-key' => $secret_key,
				'api-type'   => 1,
			),
		);
		$response   = wp_remote_get($api_url, $args);
		return $response;
	}

	/**
	 * Delete the offer of Influencer
	 *
	 * @param int $offer_id The unique identifier of the Influencer offer.
	 * @return mixed $response The response data in JSON format.
	 */
	public static function ztcbl_del_offer($offer_id)
	{
		$secret_key = get_option(sanitize_key('ztcbl-auth-key'));
		$api_url    = ZTCBL_API_URL . 'delete/offer/' . $offer_id;
		$args       = array(
			'method'  => 'DELETE',
			'headers' => array(
				'secret-key' => $secret_key,
				'api-type'   => 1,
			),
		);
		$response   = wp_remote_request($api_url, $args);
		return $response;
	}

	/**
	 * Get offer datails of particular influencer
	 *
	 * @param int $offer_id The unique identifier of the Influencer offer.
	 * @return mixed $response The response data in JSON format.
	 */
	public static function ztcbl_get_offer($offer_id)
	{
		$secret_key = get_option(sanitize_key('ztcbl-auth-key'));
		$api_url    = ZTCBL_API_URL . 'get/offer/' . $offer_id;
		$args       = array(
			'headers' => array(
				'secret-key' => $secret_key,
				'api-type'   => 1,
			),
		);
		$response   = wp_remote_get($api_url, $args);
		return $response;
	}

	/**
	 * Update an existing offer for a specific Influencer.
	 *
	 * @param int    $offer_id The unique identifier of the offer being updated.
	 * @param int    $influencer_id The unique identifier of the Influencer associated with the offer.
	 * @param int    $store_id The unique identifier of the store associated with the offer.
	 * @param string $title The updated title or name of the offer.
	 * @param string $description The updated description of the offer.
	 * @param string $start_time The updated start date and time of the offer in a valid date-time format.
	 * @param int    $duration The updated duration of the offer in minutes.
	 * @param object $products An object representing the products included in the offer.
	 * @return mixed $response The response data in JSON format.
	 */
	public static function ztcbl_update_inf_offer($offer_id, $influencer_id, $store_id, $title, $description, $start_time, $duration, $products)
	{
		$secret_key = get_option(sanitize_key('ztcbl-auth-key'));
		$api_url    = ZTCBL_API_URL . 'update/offer/' . $offer_id;
		$body       = array(
			'influencer_id' => $influencer_id,
			'store_id'      => $store_id,
			'title'         => $title,
			'description'   => $description,
			'start_time'    => $start_time,
			'duration'      => $duration,
			'products'      => $products,
		);
		$args       = array(
			'headers' => array(
				'secret-key' => $secret_key,
				'api-type'   => 1,
			),
			'body'    => $body,
		);
		$response   = wp_remote_post($api_url, $args);
		return $response;
	}

	/**
	 * Accepting offer by admin
	 *
	 * @param int $offer_id The unique identifier of the Influencer offer.
	 * @return mixed $response The response data in JSON format.
	 */
	public static function ztcbl_accept_offer($offer_id)
	{
		$secret_key = get_option(sanitize_key('ztcbl-auth-key'));
		$api_url    = ZTCBL_API_URL . 'offer/' . $offer_id . '/confirm';
		$args       = array(
			'headers' => array(
				'secret-key' => $secret_key,
				'api-type'   => 1,
			),
		);
		$response   = wp_remote_get($api_url, $args);
		return $response;
	}

	/**
	 * Cancel offer by admin
	 *
	 * @param int $offer_id The unique identifier of the Influencer offer.
	 * @return mixed $response The response data in JSON format.
	 */
	public static function ztcbl_cancel_offer($offer_id)
	{
		$secret_key = get_option(sanitize_key('ztcbl-auth-key'));
		$api_url    = ZTCBL_API_URL . 'offer/' . $offer_id . '/cancel';
		$args       = array(
			'headers' => array(
				'secret-key' => $secret_key,
				'api-type'   => 1,
			),
		);
		$response   = wp_remote_get($api_url, $args);
		return $response;
	}

	/**
	 * Get Events list according to its status
	 *
	 * @param string  $status status like upcoming,Live and completed.
	 * @param integer $page current page.
	 * @param integer $per_page data show per page.
	 * @return mixed  $response The response data in JSON format.
	 */
	public static function ztcbl_event_list($status, $page = 1, $per_page = 10)
	{
		$secret_key   = get_option(sanitize_key('ztcbl-auth-key'));
		$api_url      = ZTCBL_API_URL . $status . '/event/list';
		$query_params = array(
			'page'     => $page,
			'per_page' => $per_page,
		);

		$url  = add_query_arg($query_params, $api_url);
		$args = array(
			'headers' => array(
				'secret-key' => $secret_key,
				'api-type'   => 1,
			),

		);
		$response = wp_remote_get($url, $args);
		return $response;
	}

	/**
	 *  Retrieves user details from the ZTCBL API.
	 *
	 * @return mixed  $response The response data in JSON format.
	 */
	public static function ztcbl_user_details() {
		$secret_key   = get_option(sanitize_key('ztcbl-auth-key'));
		$api_url      = ZTCBL_API_URL .'user/details';
		$args = array(
			'headers' => array(
				'secret-key' => $secret_key,
				'api-type'   => 1,
			),

		);
		$response = wp_remote_get($api_url, $args);
		return $response;
	}
}
