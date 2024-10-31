<?php

/**
 * File: class-ztcbl-shortcode.php
 *
 * Description: This file defines the ZTCBL_Shortcode class, which is responsible for handling the shortcode functionality.
 *              Shortcodes allow users to embed dynamic content or functionality into their pages.
 *
 * @package mylivecart
 * @version 1.0.0
 */

if (!defined('ABSPATH')) {
	die();
}

/**
 * ZTCBL_Shortcode class.
 *
 * Description: The ZTCBL_Shortcode class handles the shortcode functionality for displaying ZTCBL content.
 */

class ZTCBL_Shortcode
{

	/**
	 * Constructor automaticaly call when object is created.
	 */
	public function __construct()
	{
		add_shortcode('ztcbl_EventsListPageContent', array($this, 'ztcbl_add_event_list_page_content'));
		add_shortcode('ztcbl_EventsLeavePageContent', array($this, 'ztcbl_add_event_leave_page_content'));
		add_filter('theme_page_templates', array($this, 'ztcbl_template_register'), 10, 3);
		add_filter('template_include', array($this, 'ztcbl_template_select'), 99);
		add_action('template_redirect', array($this, 'ztcbl_custom_template_redirect'));
	}

	/**
	 * Custom template redirect function for handling the 'events-detail' page.
	 * This function checks if the current page is 'events-detail' and extracts the event_id
	 * from the query variables. It then modifies the query to include the event_id and loads
	 * a custom template for the 'events-detail' page. The template file is included from the
	 * ZTCBL_UI_FRONT_DIR directory, and the function exits afterwards.
	 * @return void
	 * 
	 */
	public function ztcbl_custom_template_redirect()
	{
		if (is_page('events-detail') && $event_id = get_query_var('event_id')) {
			// Modify the query to include the event_id
			global $wp_query;
			$wp_query->set('event_id', $event_id);

			// Load the template for events-detail
			include ZTCBL_UI_FRONT_DIR . $page_temp_slug;
			exit();
		}
	}

	/**
	 * Slugs of Custom template.
	 *
	 * @return array $temps template list.
	 */
	public function ztcbl_template_array()
	{
		$temps                             = array();
		$temps['ztcbl-single-details.php'] = 'ZTCBL_DETAILS_PAGE';
		return $temps;
	}

	/**
	 * Selects a custom template for events' details pages.
	 *
	 * @param string $template The default template path.
	 * @return @return string The path of the custom template page if found, or the default template path if not found.
	 */
	public function ztcbl_template_select($template)
	{
		global $post;
		if (isset($post->ID) && null !== get_page_template_slug($post->ID)) {
			$page_temp_slug = get_page_template_slug($post->ID);
			$templates      = $this->ztcbl_template_array();
			if (isset($templates[$page_temp_slug])) {
				$template = ZTCBL_UI_FRONT_DIR . $page_temp_slug;
			}
		}
		return $template;
	}

	/**
	 * Registers a custom page template for a specific theme and post type.
	 *
	 * @param array         $page_templates An associative array of page templates where the keys represent template file names.
	 * @param @param string $theme The theme to which this page template is being registered.
	 * @param WP_Post|null  $post (Optional) The WordPress post object to which this template applies.
	 * @return array        The updated array of page templates with the new template added.
	 */
	public function ztcbl_template_register($page_templates, $theme, $post)
	{
		$page_templates['ztcbl-single-details.php'] = 'ZTCBL_DETAILS_PAGE';
		return $page_templates;
	}

	/**
	 * Retrieve and return the content of the 'event-leave.php' file.
	 *
	 * @return string The content of the 'event-leave.php' file.
	 */
	public function ztcbl_add_event_leave_page_content()
	{
		ob_start();
		include ZTCBL_UI_FRONT_DIR . 'event-leave.php';
		$res = ob_get_clean();
		return $res;
	}

	/**
	 * Event List page shortcode replacement.
	 *
	 * @param string $atts The parameters which is passess in shortcode.
	 * @return $res
	 */
	public function ztcbl_add_event_list_page_content($atts)
	{
		$param = shortcode_atts(
			array(
				'per_page' => 10,
			),
			$atts
		);
		ob_start();
		include ZTCBL_UI_FRONT_DIR . 'ztcbl-event-list.php';
		$res = ob_get_clean();
		return $res;
	}
}

new ZTCBL_Shortcode();
