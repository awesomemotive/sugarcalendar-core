<?php
/**
 * Sugar Calendar Legacy Theme Event Display.
 *
 * @since 1.0.0
 */
// Exit if accessed directly
defined( 'ABSPATH' ) || exit;

/**
 * Before / After Event Content
 *
 * Adds an action to the start and end of event post content
 * that can be hooked to by other functions
 *
 * @access      private
 * @since       1.0
 * @param       $content string the the_content field of the post object
 * @return      $content string the content with any additional data attached
 */
function sc_event_content_hooks( $content = '' ) {

	// Bail if not in the main query loop
	if ( ! ( is_main_query() && in_the_loop() ) ) {
		return $content;
	}

	// Bail if recursing
	if ( doing_filter( 'get_the_excerpt' ) && doing_filter( 'the_content' ) ) {
		return $content;
	}

	// Bail if not doing events
	if ( ! sc_doing_events() ) {
		return $content;
	}

	// Get the global post
	$post_id = get_the_ID();

	// Bail if no post ID
	if ( empty( $post_id ) ) {
		return $content;
	}

	// Start an output buffer
	ob_start();

	// Before content
	do_action( 'sc_before_event_content', $post_id );

	// Output the original content
	echo $content;

	// After content
	do_action( 'sc_after_event_content', $post_id );

	// Return the current output buffer
	return ob_get_clean();
}

/**
 * Output event details before a specific post ID.
 *
 * @since 1.0
 *
 * @param int $post_id Post ID
 */
function sc_add_event_details( $post_id = 0 ) {

	// Start an output buffer
	ob_start();

	/**
	 * Output event details.
	 *
	 * @since 2.0.7
	 */
	do_action( 'sc_event_details', $post_id );

	// Get the current output buffer
	$details = ob_get_clean();

	// Bail if no event details
	if ( empty( $details ) ) {
		return;
	} ?>

	<div class="sc_event_details" id="sc_event_details_<?php echo esc_attr( $post_id ); ?>">
		<div class="sc_event_details_inner"><?php

			// Output the event details, unescaped
			echo $details;

		?></div><!--end .sc_event_details_inner-->
	</div><!--end .sc_event_details-->

	<?php
}

/**
 * Add date & time details to event contents.
 *
 * @since 2.0.7
 *
 * @param int $post_id
 */
function sc_add_date_time_details( $post_id = 0 ) {

	// Support 1.x
	$event      = sugar_calendar_get_event_by_object( $post_id );
	$all_day    = $event->is_all_day();
	$start_date = sc_get_event_date( $post_id );
	$start_time = sc_get_event_start_time( $post_id );
	$end_time   = sc_get_event_end_time( $post_id );
	$recurring  = sc_is_recurring( $post_id )
		? sc_get_recurring_description( $post_id )
		: '';

	// Recurring description
	if ( ! empty( $recurring ) ) : ?>

		<div class="sc_event_date"><?php echo esc_html( $recurring ); ?></div>

	<?php

	// Non-recurring start DATE
	else : ?>

		<div class="sc_event_date"><?php

			esc_html_e( 'Date:', 'sugar-calendar' );

			echo ' ' . $start_date; // Contains HTML - do not escape

		?></div>

	<?php endif;

	// Start & end TIMES
	if ( ! empty( $start_time ) ) :

		// Set to all-day and noop the end time
		if ( ! empty( $all_day ) ) :
			$start_time = esc_html__( 'All-day', 'sugar-calendar' );
			$end_time   = false;
		endif;

		// Default format
		$format = 'Y-m-d\TH:i:s';
		$tz     = 'floating';

		// Non-floating
		if ( ! empty( $event->start_tz ) && ( $end_time !== $start_time ) ) {

			// Get the offset
			$offset = sugar_calendar_get_timezone_offset( array(
				'time'     => $event->start,
				'timezone' => $event->start_tz
			) );

			// Add timezone to format
			$format = "Y-m-d\TH:i:s{$offset}";
		}

		// Format the date/time
		$dt = $event->start_date( $format );

		// All-day Events have floating time zones
		if ( ! empty( $event->start_tz ) && ! $event->is_all_day() ) {
			$tz = $event->start_tz;
		}

		// Output start (or all-day)
		?><div class="sc_event_time">
			<span class="sc_event_start_time"><?php

				esc_html_e( 'Time:', 'sugar-calendar' ); ?>

				<time datetime="<?php echo esc_attr( $dt ); ?>" title="<?php echo esc_attr( $dt ); ?>" data-timezone="<?php echo esc_attr( $tz ); ?>"><?php

					echo esc_html( $start_time );

				?></time>
			</span><?php

			// Maybe output a separator and the end time
			if ( ! empty( $end_time ) && ( $end_time !== $start_time ) ) :

				// Format
				$format = 'Y-m-d\TH:i:s';
				$tz     = 'floating';

				// Non-floating
				if ( ! empty( $event->end_tz ) ) {

					// Get the offset
					$offset = sugar_calendar_get_timezone_offset( array(
						'time'     => $event->end,
						'timezone' => $event->end_tz
					) );

					// Add timezone to format
					$format = "Y-m-d\TH:i:s{$offset}";
				}

				// Format the date/time
				$dt = $event->end_date( $format );

				// All-day Events have floating time zones
				if ( ! empty( $event->end_tz ) && ! $event->is_all_day() ) {
					$tz = $event->end_tz;

				// Maybe fallback to the start time zone
				} elseif ( empty( $event->end_tz ) && ! empty( $event->start_tz ) ) {
					$tz = $event->start_tz;
				} ?>

				<span class="sc_event_time_sep"><?php

					esc_html_e( 'to', 'sugar-calendar' );

				?></span>

				<span class="sc_event_end_time">
					<time datetime="<?php echo esc_attr( $dt ); ?>" title="<?php echo esc_attr( $dt ); ?>" data-timezone="<?php echo esc_attr( $tz ); ?>"><?php

						echo esc_html( $end_time );

					?></time>
				</span>

			<?php endif; ?>

		</div>

	<?php endif;
}

/**
 * Add location details to event contents.
 *
 * @since 2.0.7
 *
 * @param int $post_id
 */
function sc_add_location_details( $post_id = 0 ) {

	// New in 2.0
	$location = sugar_calendar_get_event_by_object( $post_id, 'post' )->location;

	// Maybe add location
	if ( ! empty( $location ) ) : ?>

		<div class="sc_event_location"><?php

			echo esc_html__( 'Location:', 'sugar-calendar' ) . ' ' . esc_html( $location );

		?></div>

	<?php endif;
}
