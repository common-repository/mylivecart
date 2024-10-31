<?php

/**
 * File: class-ztcbl-event.php
 *
 * Description: This file defines the ZTCBL_Event class, which handles various influencer offer and
 *              event-related functionalities, such as creation, deletion, confirmation, and retrieval.
 *
 * @package mylivecart
 * @version 1.0.0
 */
if (!defined('ABSPATH')) {
	die();
}

/**
 * Class ZTCBL_Event
 *
 * This class provides methods for handling influencer offer and event-related functionalities.
 * It communicates with the ZTCBL API to create, delete, confirm, retrieve, and update offers and events.
 */
class ZTCBL_Event
{

	/**
	 * This constructor method initializes the ZTCBL_Event class and sets up action hooks
	 * to handle various influencer offer and event-related functionalities, such as creation,
	 * deletion, confirmation, and retrieval.
	 */
	public function __construct()
	{
		add_action('wp_ajax_offer_influencer', array($this, 'ztcbl_create_influencer_offer'));
		add_action('admin_post_del_inf_offer', array($this, 'ztcbl_del_inf_offer'));
		add_action('admin_post_confirm_inf_offer', array($this, 'ztcbl_confirm_inf_offer'));
		add_action('admin_post_cancel_inf_offer', array($this, 'ztcbl_cancel_inf_offer'));
		add_action('wp_ajax_get_offer_details', array($this, 'ztcbl_get_offer_details'));
		add_action('wp_ajax_update_influencer_offer', array($this, 'ztcbl_update_influencer_offer'));
		add_action('wp_ajax_get_event_details', array($this, 'ztcbl_get_event_details'));
	}

	/**
	 * Retrieves and outputs event details from an API based on the provided event ID.
	 *
	 * This function takes an event ID from the request, makes an API request to fetch event details,
	 * and then prints the response body if the API response code is '200'. If the response code is not '200',
	 * it echoes 'error'.
	 */
	public function ztcbl_get_event_details()
	{
		if (isset($_REQUEST['nonce'])) {
			if (!wp_verify_nonce(wp_unslash(sanitize_text_field($_REQUEST['nonce'])), 'ztcbl_ajax_nonce')) {
				$res = array(
					'message' => esc_html_e('Nonce not verified.', 'mylivecart'),
					'data'    => '',
				);
				echo wp_json_encode($res);
			} else {
				$event_id          = isset($_REQUEST['event_id']) ? wp_unslash(sanitize_text_field($_REQUEST['event_id'])) : '';
				$ztcbl_api         = new ZTCBL_Api();
				$event_details_api = $ztcbl_api->ztcbl_details_api($event_id);
				$response_code     = wp_remote_retrieve_response_code($event_details_api);
				if (200 === $response_code) {
					$response_body = wp_remote_retrieve_body($event_details_api);
					echo (wp_kses_post($response_body));
				} else {
					$res = array(
						'message' => esc_html_e('error', 'mylivecart'),
						'data'    => '',
					);
					echo wp_json_encode($res);
				}
				die();
			}
		}
		die();
	}

	/**
	 * Creates an influencer offer using provided request parameters and communicates with the ZTCBL API.
	 *
	 * This function retrieves influencer offer creation parameters from the request, then calls the ZTCBL_Api
	 * class to create the offer. It sends the influencer's ID, store ID, title, description, start date,
	 * duration, and products as parameters to the API. The API response message is then echoed.
	 */
	public function ztcbl_create_influencer_offer()
	{
		if (isset($_REQUEST['nonce'])) {
			if (!wp_verify_nonce(wp_unslash(sanitize_text_field($_REQUEST['nonce'])), 'ztcbl_ajax_nonce')) {
				echo esc_html('Nonce not verified.');
			} else {
				$influencer_id   = isset($_REQUEST['influencer_id']) ? wp_unslash(sanitize_text_field($_REQUEST['influencer_id'])) : '';
				$store_id        = isset($_REQUEST['store_id']) ? wp_unslash(sanitize_text_field($_REQUEST['store_id'])) : '';
				$title           = isset($_REQUEST['title']) ? wp_unslash(sanitize_text_field($_REQUEST['title'])) : '';
				$description     = isset($_REQUEST['description']) ? wp_unslash(sanitize_text_field($_REQUEST['description'])) : '';
				$show_start_date = isset($_REQUEST['showStartDate']) ? wp_unslash(sanitize_text_field($_REQUEST['showStartDate'])) : '';
				$duration        = isset($_REQUEST['duration']) ? wp_unslash(sanitize_text_field($_REQUEST['duration'])) : '';
				$product = '';
				$products = array();
				if (isset($_REQUEST['products'])) {
					$product = wp_unslash(sanitize_text_field($_REQUEST['products']));
					$products = json_decode($product, true);
				}

				$inf_offer_response = ZTCBL_Api::ztcbl_create_inf_offer($influencer_id, $store_id, $title, $description, $show_start_date, $duration, wp_json_encode($products));
				$response           = wp_remote_retrieve_response_message($inf_offer_response);
				echo wp_kses_post($response);
				wp_die();
			}
		}
		echo esc_html('Nonce not verified.');
		wp_die();
	}

	/**
	 * Deletes an influencer offer using the provided 'offer_id' parameter and communicates with the ZTCBL API.
	 */
	public function ztcbl_del_inf_offer()
	{
		if (isset($_REQUEST['nonce']) && wp_verify_nonce(wp_unslash(sanitize_text_field($_REQUEST['nonce'])), 'ztcbl_nonce')) {
			$offer_id = isset($_REQUEST['offer_id']) ? wp_unslash(sanitize_text_field($_REQUEST['offer_id'])) : '';
			if (!empty($offer_id)) {
				$offer_del_api = ZTCBL_Api::ztcbl_del_offer($offer_id);
				$response      = wp_remote_retrieve_response_code($offer_del_api);
				if (200 === $response) {
					wp_safe_redirect(wp_get_referer());
					exit;
				} else {
					wp_safe_redirect(wp_get_referer());
					exit;
				}
			} else {
				wp_safe_redirect(wp_get_referer());
				exit;
			}
		} else {
			echo esc_html('Nonce not verified.');
		}
		wp_die();
	}

	/**
	 * Retrieves and outputs details of an offer using the provided 'offer_id' parameter.
	 */
	public function ztcbl_get_offer_details()
	{
		if (isset($_REQUEST['nonce'])) {
			if (!wp_verify_nonce(wp_unslash(sanitize_text_field($_REQUEST['nonce'])), 'ztcbl_ajax_nonce')) {
				echo esc_html('Nonce not verified.');
			} else {
				if (isset($_REQUEST['offer_id'])) {
					$offer_id          = wp_unslash(sanitize_text_field($_REQUEST['offer_id']));
					$offer_details_api = ZTCBL_Api::ztcbl_get_offer($offer_id);
					$response          = wp_remote_retrieve_response_code($offer_details_api);
					if (200 === $response) {
						$offer_details_body = wp_remote_retrieve_body($offer_details_api);
						echo wp_kses_post($offer_details_body);
					} else {
						echo esc_html('Not Found');
					}
				}
				die();
			}
		} else {
			echo esc_html('Nonce not verified.');
		}
		wp_die();
	}

	/**
	 * Updates an influencer offer using provided request parameters.
	 */
	public function ztcbl_update_influencer_offer()
	{
		if (isset($_REQUEST['nonce'])) {
			if (!wp_verify_nonce(wp_unslash(sanitize_text_field($_REQUEST['nonce'])), 'ztcbl_ajax_nonce')) {
				echo esc_html('Nonce not verified.');
			} else {
				$influencer_id   = isset($_REQUEST['influencer_id']) ? wp_unslash(sanitize_text_field($_REQUEST['influencer_id'])) : '';
				$offer_id        = isset($_REQUEST['offer_id']) ? wp_unslash(sanitize_text_field($_REQUEST['offer_id'])) : '';
				$store_id        = isset($_REQUEST['store_id']) ? wp_unslash(sanitize_text_field($_REQUEST['store_id'])) : '';
				$title           = isset($_REQUEST['title']) ? wp_unslash(sanitize_text_field($_REQUEST['title'])) : '';
				$description     = isset($_REQUEST['description']) ? wp_unslash(sanitize_text_field($_REQUEST['description'])) : '';
				$show_start_date = isset($_REQUEST['showStartDate']) ? wp_unslash(sanitize_text_field($_REQUEST['showStartDate'])) : '';
				$duration        = isset($_REQUEST['duration']) ? wp_unslash(sanitize_text_field($_REQUEST['duration'])) : '';
				$product = '';
				$products = array();
				if (isset($_REQUEST['products'])) {
					$product = wp_unslash(sanitize_text_field($_REQUEST['products']));
					$products = json_decode($product, true);
				}
				$inf_offer_response = ZTCBL_Api::ztcbl_update_inf_offer($offer_id, $influencer_id, $store_id, $title, $description, $show_start_date, $duration, wp_json_encode($products));
				$response           = wp_remote_retrieve_response_message($inf_offer_response);
				echo wp_kses_post($response);
				wp_die();
			}
		}
		echo esc_html('Nonce not verified.');
		wp_die();
	}

	/**
	 * Confirms or accepts an influencer offer using the provided 'offer_id' parameter.
	 */
	public function ztcbl_confirm_inf_offer()
	{
		if (isset($_REQUEST['nonce']) && wp_verify_nonce(wp_unslash(sanitize_text_field($_REQUEST['nonce'])), 'ztcbl_nonce')) {
			$offer_id = isset($_REQUEST['offer_id']) ? wp_unslash(sanitize_text_field($_REQUEST['offer_id'])) : '';
			if (!empty($offer_id)) {
				$offer_confirm_api = ZTCBL_Api::ztcbl_accept_offer($offer_id);
				$response          = wp_remote_retrieve_response_code($offer_confirm_api);
				if (200 === $response) {
					wp_safe_redirect(admin_url('admin.php?page=all-events&zt-page=create-event&offer-id=' . $offer_id));
					exit;
				} else {
					wp_safe_redirect(wp_get_referer());
					exit;
				}
			} else {
				wp_safe_redirect(wp_get_referer());
				exit;
			}
		} else {
			echo esc_html('Nonce not verified.');
		}
		wp_die();
	}

	/**
	 * Cancels an influencer offer using the provided 'offer_id' parameter.
	 */
	public function ztcbl_cancel_inf_offer()
	{
		if (isset($_REQUEST['nonce']) && wp_verify_nonce(wp_unslash(sanitize_text_field($_REQUEST['nonce'])), 'ztcbl_nonce')) {
			$offer_id = isset($_REQUEST['offer_id']) ? wp_unslash(sanitize_text_field($_REQUEST['offer_id'])) : '';
			if (!empty($offer_id)) {
				$offer_cancel_api = ZTCBL_Api::ztcbl_cancel_offer($offer_id);
				$response         = wp_remote_retrieve_response_code($offer_cancel_api);
				if (200 === $response) {
					wp_safe_redirect(wp_get_referer());
					exit;
				} else {
					wp_safe_redirect(wp_get_referer());
					exit;
				}
			} else {
				wp_safe_redirect(wp_get_referer());
				exit;
			}
		} else {
			echo esc_html('Nonce not verified. ');
		}

		wp_die();
	}
}

new ZTCBL_Event();
