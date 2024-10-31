<?php
/**
 * File: all-event.php
 *
 * Description: This file is responsible for show all events list and according to their status in admin side.
 *
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
$url = admin_url( 'admin.php?page=all-events&zt-page=create-event' );
?>

	<?php
	$current_tab = isset( $_GET['tab'] ) ? wp_unslash( sanitize_text_field( $_GET['tab'] ) ) : 'All';
	$status_tabs = array(
		'All'       => 'All',
		'Upcoming'  => 'Upcoming',
		'Live'      => 'Live',
		'Completed' => 'Completed',
	);
	?>
<div class="wrap">
	<div id='zt-create-div'>
		<a class="button zt-create" href="<?php echo esc_url( $url ); ?>">
			<div class='zt-create-btn'>
				<span class="zt-create-span"><?php esc_html_e( 'Create Event', 'mylivecart' ); ?></span>
				<img class="zt-create-img" src="<?php echo esc_url( ZTCBL_ASSETS_URL . 'images/create_icon.png' ); ?>" alt="icon">
			</div>
		</a>
	</div>
	<h1 style="font-family:poppins;"><?php echo esc_html( get_admin_page_title() ); ?></h1>
	<?php
	echo '<h2 class="nav-tab-wrapper" style="margin-bottom:20px;font-family:poppins;">';
	foreach ( $status_tabs as $tab => $status ) {
		$current_active_tab = ( $tab === $current_tab ) ? 'nav-tab-active' : '';
		printf(
			'<a class="nav-tab %s" href="%s" >%s</a>',
			esc_attr( $current_active_tab ),
			esc_url( admin_url( 'admin.php?page=all-events' . "&tab={$tab}" ) ),
			esc_html( $status )
		);
	}
	echo '</h2>';
	?>
	<div class="tab-content">
		<?php
		if ( isset( $current_tab ) && $current_tab == 'All' ) {
			?>
			<div>
				<?php
				$page           = isset( $_REQUEST['p'] ) ? wp_unslash( sanitize_text_field( $_REQUEST['p'] ) ) : '1';
				$ztbl_api       = new ZTCBL_Api();
				$event_list_api = $ztbl_api->ztcbl_list_api( $page, '10' );
				$response       = wp_remote_retrieve_response_code( $event_list_api );
				if ( $response == 200 ) {
					$total_event_list = json_decode( wp_remote_retrieve_body( $event_list_api ), true );
					$total_event      = $total_event_list['data']['total'];
					$events_per_page  = $total_event_list['data']['per_page'];
					if ( $total_event > 0 ) {
						$total_pages     = ceil( $total_event / $events_per_page );
						$event_list_data = array();
						$event_list_data = $total_event_list['data']['data'];
						?>
						<table id='ztcbl_inf_table'>
							<tr>
								<th><?php esc_html_e( '#', 'mylivecart' ); ?></th>
								<th><?php esc_html_e( 'Name', 'mylivecart' ); ?></th>
								<th><?php esc_html_e( 'Host', 'mylivecart' ); ?></th>
								<th><?php esc_html_e( 'Products', 'mylivecart' ); ?></th>
								<th><?php esc_html_e( 'Start Date', 'mylivecart' ); ?></th>
								<th><?php esc_html_e( 'Start Time', 'mylivecart' ); ?></th>
								<th><?php esc_html_e( 'Status', 'mylivecart' ); ?></th>
								<th><?php esc_html_e( 'Action', 'mylivecart' ); ?></th>
							</tr>
							<?php
							$count =  ($page - 1) * 10 + 1;
							foreach ( $event_list_data as $value ) {
								$host_first_name = isset( $value['hosts_details'][0][0]['first_name'] ) ? $value['hosts_details'][0][0]['first_name'] : '-';
								if ( strtolower( $value['status'] ) == 'live' ) {
									$event_status = 'ztcbl-live';
								} elseif ( strtolower( $value['status'] ) == 'upcoming' ) {
									$event_status = 'ztcbl-upcoming';
								} elseif ( strtolower( $value['status'] ) == 'completed' ) {
									$event_status = 'ztcbl-completed';
								}
								?>
								<tr>
									<td><?php echo esc_html( $count++ ); ?></td>
									<td><?php echo esc_html( $value['title'] ); ?></td>
									<td><?php echo esc_html( $host_first_name ); ?></td>
									<td><?php echo esc_html( count( $value['products'] ) ); ?></td>
									<td><?php echo esc_html( date("d.m.Y", strtotime( $value['start_date'] )) ); ?></td>
									<td><?php echo esc_html( $value['start_times'] ); ?></td>
									<td> <span class='<?php echo esc_attr( $event_status ); ?>'><?php echo esc_html( $value['status'] ); ?></span> </td>
									<td>
										<?php
										if ( strtolower( $value['status'] ) == 'upcoming' && $value['offer_id'] == null ) {
											?>
											<a href="<?php echo esc_url(admin_url('admin.php?page=all-events&zt-page=update-event&e-id=' . $value['event_key'])); ?>">
											<span class="dashicons dashicons-edit"></span></a>
											<?php
										} elseif ( strtolower( $value['status'] ) == 'upcoming' && isset( $value['offer_id'] ) ) {
											?>
											<a href="<?php echo esc_url(admin_url('admin.php?page=all-events&zt-page=update-event&e-id=' . $value['event_key'])); ?>">
											<span class="dashicons dashicons-edit"></span>
											</a>
											<?php
										} else {
											?>
											<span style="padding:0 5px">-</span>
											<?php
										}
										?>
										<a href="<?php echo esc_url(site_url('events-detail/?event_key=' . $value['event_key'])) ?>" target="_blank">
											<svg  width="18" height="18" viewBox="4 0 40 44">
												<path d="M 35.478516 5.9804688 A 2.0002 2.0002 0 0 0 34.085938 9.4140625 L 35.179688 10.507812 C 23.476587 10.680668 14 20.256715 14 32 A 2.0002 2.0002 0 1 0 18 32 C 18 22.427546 25.627423 14.702715 35.154297 14.517578 L 34.085938 15.585938 A 2.0002 2.0002 0 1 0 36.914062 18.414062 L 41.236328 14.091797 A 2.0002 2.0002 0 0 0 41.228516 10.900391 L 36.914062 6.5859375 A 2.0002 2.0002 0 0 0 35.478516 5.9804688 z" fill="#FE5D34"></path>
												<path d="M 12.5 6 C 8.9338464 6 6 8.9338464 6 12.5 L 6 35.5 C 6 39.066154 8.9338464 42 12.5 42 L 35.5 42 C 39.066154 42 42 39.066154 42 35.5 L 42 28 A 2.0002 2.0002 0 1 0 38 28 L 38 35.5 C 38 36.903846 36.903846 38 35.5 38 L 12.5 38 C 11.096154 38 10 36.903846 10 35.5 L 10 12.5 C 10 11.096154 11.096154 10 12.5 10 L 20 10 A 2.0002 2.0002 0 1 0 20 6 L 12.5 6 z" fill="#6D737E"></path>
											</svg>
										</a>
									</td>
								</tr>
								<?php
							}
							?>
							</tr>
						</table>


						<?php
						$current_page        = $page;
						$max_pages_displayed = 5;
						$ztbl_api            = new ZTCBL_Api();
						$pagination          = $ztbl_api->ztcbl_generate_pagination( $current_page, $total_pages, $max_pages_displayed );
						?>
						<div class="ztcbl_pagination">
						<?php
						// Output "Previous" button.
						if ( $current_page > 1 ) {
							?>
							<a href="<?php echo esc_url( '?page=all-events&p=' . ($current_page - 1)) ?>" class="ztcbl_pagination_btn">
							<img src="<?php echo esc_url(ZTCBL_ASSETS_URL . 'images/icon_left.png'); ?>" alt=""></a>
							<?php
						}

						foreach ( $pagination as $page ) {
							// Highlight the current page.
							if ( $page == $current_page ) {
								?>
								<strong class="ztcbl_pagination_active_btn"><?php echo esc_html($page); ?></strong>
								<?php
							} else {
								?>
								<a href="<?php echo esc_url( '?page=all-events&p=' . $page)?>" class="ztcbl_pagination_btn"><?php echo esc_html($page); ?></a>
								<?php
							}
						}

						// Output "Next" button.
						if ( $current_page < $total_pages ) {
							?>
							<a href="<?php echo esc_url( '?page=all-events&p='. ($current_page + 1)); ?>" class="ztcbl_pagination_btn"><img src="<?php echo esc_url(ZTCBL_ASSETS_URL . 'images/icon_right.png'); ?>" alt=""></a>
							<?php
						}
					}else{
						?>
						<div class="no_event_fount">
							<img src="<?php echo esc_url(ZTCBL_ASSETS_URL . 'images/not-found.gif'); ?>" alt="noeventfound">
							<h1><?php esc_html_e( 'No Event Found.', 'mylivecart' ); ?></h1>
						</div>
						<?php
					}
				}else{
					?>
					<p class="inf_error_msg"><?php esc_html_e( 'Firstly create a store or check your configuration.', 'mylivecart' ); ?></p>
					<?php
				}
				?>
			</div>
			
			<?php
		} else {
			?>
			<div>
				<?php
				$page           = isset( $_REQUEST['p'] ) ? wp_unslash( sanitize_text_field( $_REQUEST['p'] ) ) : '1';
				$event_list_api = ZTCBL_Api::ztcbl_event_list( $current_tab, $page, '10' );
				$response       = wp_remote_retrieve_response_code( $event_list_api );
				if ( $response == 200 ) {
					$total_event_list = json_decode( wp_remote_retrieve_body( $event_list_api ), true);
					$total_event      = $total_event_list['data']['total'];
					$events_per_page  = $total_event_list['data']['per_page'];
					if ( $total_event > 0 ) {
						$total_pages     = ceil( $total_event / $events_per_page );
						$event_list_data = array();
						$event_list_data = $total_event_list['data']['data'];
						?>
						<table id='ztcbl_inf_table'>
							<tr>
								<th><?php esc_html_e( '#', 'mylivecart' ); ?></th>
								<th><?php esc_html_e( 'Name', 'mylivecart' ); ?></th>
								<th><?php esc_html_e( 'Host', 'mylivecart' ); ?></th>
								<th><?php esc_html_e( 'Products', 'mylivecart' ); ?></th>
								<th><?php esc_html_e( 'Start Date', 'mylivecart' ); ?></th>
								<th><?php esc_html_e( 'Start Time', 'mylivecart' ); ?></th>
								<th><?php esc_html_e( 'Status', 'mylivecart' ); ?></th>
								<th><?php esc_html_e( 'Action', 'mylivecart' ); ?></th>
							</tr>
							<?php
							$count =  ($page - 1) * 10 + 1;
							foreach ( $event_list_data as $value ) {
								$host_first_name = isset( $value['hosts_details'][0][0]['first_name'] ) ? $value['hosts_details'][0][0]['first_name'] : '-';
								if ( strtolower( $value['status'] ) == 'live' ) {
									$event_status = 'ztcbl-live';
								} elseif ( strtolower( $value['status'] ) == 'upcoming' ) {
									$event_status = 'ztcbl-upcoming';
								} elseif ( strtolower( $value['status'] ) == 'completed' ) {
									$event_status = 'ztcbl-completed';
								}
								?>
								<tr>
									<td><?php echo esc_html( $count++ ); ?></td>
									<td><?php echo esc_html( $value['title'] ); ?></td>
									<td><?php echo esc_html( $host_first_name ); ?></td>
									<td><?php echo esc_html( count( $value['products'] ) ); ?></td>
									<td><?php echo esc_html( date("d.m.Y", strtotime( $value['start_date'] )) ); ?></td>
									<td><?php echo esc_html( $value['start_times'] ); ?></td>
									<td> <span class='<?php echo esc_attr( $event_status ); ?>'><?php echo esc_html( $value['status'] ); ?></span> </td>
									<td>
										<?php
										if ( strtolower( $value['status'] ) == 'upcoming' && $value['offer_id'] == null ) {
											?>
											<a href="<?php echo esc_url(admin_url('admin.php?page=all-events&zt-page=update-event&e-id=' . $value['event_key'])); ?>">
											<span class="dashicons dashicons-edit"></span></a>
											<?php
										} elseif ( strtolower( $value['status'] ) == 'upcoming' && isset( $value['offer_id'] ) ) {
											?>
											<a href="<?php echo esc_url(admin_url('admin.php?page=all-events&zt-page=update-event&e-id=' . $value['event_key'])); ?>">
											<span class="dashicons dashicons-edit"></span>
											</a>
											<?php
										} else {
											?>
											<span style="padding:0 5px">-</span>
											<?php
										}
										?>
										<a href="<?php echo esc_url(site_url('events-detail/?event_key=' . $value['event_key'])) ?>" target="_black">
											<svg width="18" height="18" viewBox="4 0 40 44">
												<path d="M 35.478516 5.9804688 A 2.0002 2.0002 0 0 0 34.085938 9.4140625 L 35.179688 10.507812 C 23.476587 10.680668 14 20.256715 14 32 A 2.0002 2.0002 0 1 0 18 32 C 18 22.427546 25.627423 14.702715 35.154297 14.517578 L 34.085938 15.585938 A 2.0002 2.0002 0 1 0 36.914062 18.414062 L 41.236328 14.091797 A 2.0002 2.0002 0 0 0 41.228516 10.900391 L 36.914062 6.5859375 A 2.0002 2.0002 0 0 0 35.478516 5.9804688 z" fill="#FE5D34"></path>
												<path d="M 12.5 6 C 8.9338464 6 6 8.9338464 6 12.5 L 6 35.5 C 6 39.066154 8.9338464 42 12.5 42 L 35.5 42 C 39.066154 42 42 39.066154 42 35.5 L 42 28 A 2.0002 2.0002 0 1 0 38 28 L 38 35.5 C 38 36.903846 36.903846 38 35.5 38 L 12.5 38 C 11.096154 38 10 36.903846 10 35.5 L 10 12.5 C 10 11.096154 11.096154 10 12.5 10 L 20 10 A 2.0002 2.0002 0 1 0 20 6 L 12.5 6 z" fill="#6D737E"></path>
											</svg>
										</a>
									</td>
								</tr>
								<?php
							}
							?>
							</tr>
						</table>

						<?php
						$current_page        = $page;
						$max_pages_displayed = 5;
						$ztbl_api            = new ZTCBL_Api();
						$pagination          = $ztbl_api->ztcbl_generate_pagination( $current_page, $total_pages, $max_pages_displayed );
						?>
						<div class="ztcbl_pagination">
						<?php
						// Output "Previous" button.
						if ( $current_page > 1 ) {
							?>
							<a href="<?php echo esc_url('?page=all-events&tab='.$current_tab .'&p='.($current_page - 1)); ?>" class="ztcbl_pagination_btn"><img src="<?php echo esc_url(ZTCBL_ASSETS_URL . 'images/icon_left.png'); ?>" alt=""></a>
							<?php
						}

						foreach ( $pagination as $page ) {
							// Highlight the current page.
							if ( $page == $current_page ) {
								?>
								<strong class="ztcbl_pagination_active_btn"><?php echo esc_html($page); ?></strong>
								<?php
							} else {
								?>
								<a href="<?php echo esc_url( '?page=all-events&tab= ' . $current_tab .'&p='. $page);?>" class="ztcbl_pagination_btn"><?php echo esc_html($page); ?></a>
								<?php
							}
						}

						// Output "Next" button.
						if ( $current_page < $total_pages ) {
							?>
							<a href="<?php echo esc_url( '?page=all-events&tab='. $current_tab .'&p='.( $current_page + 1 ) ); ?>" class="ztcbl_pagination_btn"><img src="<?php echo esc_url(ZTCBL_ASSETS_URL . 'images/icon_right.png'); ?>" ></a>
							<?php
						}
					}else{
						?>
						<div class="no_event_fount">
							<img src="<?php echo esc_url(ZTCBL_ASSETS_URL . 'images/not-found.gif'); ?>" alt="noeventfound">
							<h1><?php esc_html_e( 'No Event Found.', 'mylivecart' ); ?></h1>
						</div>
						<?php
					}
				}else{
					?>
					<p class="inf_error_msg"><?php esc_html_e( 'Firstly create a store or check your configuration.', 'mylivecart' ); ?></p>
					<?php
				}
				?>
			</div>
			<?php
		}
		?>
	</div>
</div>
