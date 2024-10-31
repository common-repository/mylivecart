<?php
/**
 * File: ztcbl-event-list.php
 *
 * This file is responsible for displaying a list of events and their details
 * based on the provided authentication key. It enqueues necessary styles and scripts,
 * retrieves event data from an API, and renders event cards with pagination.
 *
 * @package mylivecart
 * @version 1.0.0
 */

 if (!defined('ABSPATH')) {
	exit; // Exit if accessed directly
}

wp_enqueue_script( 'ztcbl_js_file' );
wp_enqueue_style( 'ztcbl_font_family');
wp_enqueue_style( 'ztcbl_style' );
$secret_key = ! empty( get_option( sanitize_key( 'ztcbl-auth-key' ) ) ) ? get_option( sanitize_key( 'ztcbl-auth-key' ) ) : '';

if ( $secret_key !== null || $secret_key !== '' ) {
	$ztpage         = isset( $_GET['ztcbl_page'] ) ? wp_unslash( sanitize_text_field( $_GET['ztcbl_page'] ) ) : '1';
	$ztbl_api       = new ZTCBL_Api();
	$event_list_api = $ztbl_api->ztcbl_list_api( $ztpage, $param['per_page'] );
	$response       = wp_remote_retrieve_response_code( $event_list_api );
	if ( $response === 200 ) {
		$total_event_list = json_decode( wp_remote_retrieve_body( $event_list_api ), true );
		$total_event      = $total_event_list['data']['total'];
		$events_per_page  = $total_event_list['data']['per_page'];
		if ( $total_event > 0 ) {
			$total_pages     = ceil( $total_event / $events_per_page );
			$event_list_data = array();
			$event_list_data = $total_event_list['data']['data'];
			?>
			<div class="ztcbl_row">
			<?php
			foreach ( $event_list_data as $value ) {
				$host_first_name = isset( $value['hosts_details'][0][0]['first_name'] ) ? $value['hosts_details'][0][0]['first_name'] : '';
				$host_last_name  = isset( $value['hosts_details'][0][0]['last_name'] ) ? $value['hosts_details'][0][0]['last_name'] : '';

				if ( strtolower($value['status'] ) === 'live' ) {
					$event_status = 'ztcbl-status-live';
				} elseif ( strtolower($value['status'] ) === 'upcoming') {
					$event_status = 'ztcbl-status-upcoming';
				} elseif ( strtolower( $value['status'] ) === 'completed' ) {
					$event_status = 'ztcbl-status-completed';
				}
				?>
				<a href="<?php echo esc_url( site_url( '/events-detail/' . $value['event_key'] ) ); ?>">
				<div class='ztcbl_card'>
					<div class='ztcbl_badge <?php echo esc_attr( $event_status ); ?>'><?php echo esc_html( $value['status'] ); ?></div>
					<div class='ztcbl_img'>
						<?php
						if ( $value['file_type'] === 'video' ) {
							?>
							<video autoplay muted loop>
								<source src="<?php echo esc_url( $value['banner_image'] ); ?>" type="video/mp4">
							</video>
							<?php
						} else {
							?>
							<img src="<?php echo esc_url( $value['banner_image'] ); ?>" alt="ztcbl_img1">
							<?php
						}
						?>
					</div>
					<div class='ztcbl_name' title="<?php echo esc_attr( $value['title'] ); ?>"><?php echo esc_html( $value['title'] ); ?></div>
					<div class='Ztcbl_host_name'>Hosted by - <?php echo esc_html( $host_first_name ); ?> <?php echo esc_html( $host_last_name ); ?></div>
					<div class='ztcbl_desc'><?php echo esc_html( $value['description'] ); ?></div>
					<div class='ztcbl_date_time'>
						<div class='ztcbl_left_date_time'>
							<div class='ztcbl_left_cal_icon'>
								<img src="<?php echo esc_url( ZTCBL_ASSETS_URL . 'images/calendar_icon.png' ); ?>" alt="calendar_icon">
							</div>
							<div class='ztcbl_left_date_text'>
								<p class='ztcbl_left_date_p_1'><?php esc_html_e(' Start Date & Time','mylivecart'); ?></p>
								<p class='ztcbl_left_date_p_2'> <?php echo esc_html( $value['start_date'] ); ?> & <?php echo esc_html( $value['start_times'] ); ?> </p>
							</div>
						</div>
						<div class='ztcbl_line_icon'> <img src="<?php echo esc_url( ZTCBL_ASSETS_URL . 'images/line_icon.png' ); ?>" alt="line_icon"> </div>
						<div class='ztcbl_right_date_time'>
							<div class='ztcbl_right_cal_icon'>
								<img src="<?php echo esc_url( ZTCBL_ASSETS_URL . 'images/calendar_icon.png' ); ?>" alt="calendar_icon">
							</div>
							<div class='ztcbl_left_date_text'>
								<p class='ztcbl_left_date_p_1'><?php esc_html_e('End Date & Time','mylivecart'); ?></p>
								<p class='ztcbl_left_date_p_2'> <?php echo esc_html( $value['end_date'] ); ?> & <?php echo esc_html( $value['end_times'] ); ?> </p>
							</div>
						</div>
					</div>
				</div>
				</a>
				<?php
			}
			?>
			</div>
			<?php
			$current_page        = $ztpage;
			$max_pages_displayed = 5;
			$ztbl_api            = new ZTCBL_Api();
			$pagination          = $ztbl_api->ztcbl_generate_pagination( $current_page, $total_pages, $max_pages_displayed );
			?>
			<div class="ztcbl_pagination">
			<?php
			// Output "Previous" button.
			if ( $current_page > 1 ) {
				?>
				<a href="<?php echo esc_url('?ztcbl_page=' . ($current_page - 1)) ?>" class="ztcbl_pagination_btn"><img src="<?php echo esc_url( ZTCBL_ASSETS_URL . 'images/icon_left.png' ) ?>" alt="Prev"></a>
				<?php
			}

			foreach ( $pagination as $ztpage ) {
				// Highlight the current page.
				if ( $ztpage == $current_page ) {
					?>
					<strong class="ztcbl_pagination_active_btn"><?php echo esc_html( $ztpage ); ?></strong>
					<?php
				} else {
					?>
					<a href="<?php echo esc_url('?ztcbl_page=' . ( $ztpage )) ?>" class="ztcbl_pagination_btn"><?php echo esc_html( $ztpage ); ?></a>
					<?php
				}
			}

			// Output "Next" button.
			if ( $current_page < $total_pages ) {
				?>
				<a href="<?php echo esc_url('?ztcbl_page=' . ($current_page + 1)) ?>" class="ztcbl_pagination_btn"><img src="<?php echo esc_url(ZTCBL_ASSETS_URL . 'images/icon_right.png'); ?>" alt="Next"></a>
				<?php
			}
		} else {
			?>
			<div class="no_event_found_front_page">
				<img src="<?php echo esc_url( ZTCBL_ASSETS_URL .'images/not-found.gif' ); ?>" alt="noeventfound">
				<h4><?php esc_html_e('No Event Found.','mylivecart'); ?></h4>
			</div>
			<?php
		}
	} else {
		?>
		<div class="no_event_found_front_page">
			<img src="<?php echo esc_url( ZTCBL_ASSETS_URL .'images/not-found.gif' ); ?>" alt="noeventfound">
			<h4><?php esc_html_e('No Event Found.','mylivecart'); ?></h4>
		</div>
		<?php
	}
} else {
	?>
	<div class="no_event_found_front_page">
		<img src="<?php echo esc_url( ZTCBL_ASSETS_URL .'images/not-found.gif' ); ?>" alt="noeventfound">
		<h4><?php esc_html_e('No Event Found.','mylivecart'); ?></h4>
	</div>
	<?php
}
