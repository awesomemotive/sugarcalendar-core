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

	// Bail if not single event, event archive, or calendar archive
	if ( ! ( is_singular( 'sc_event' ) || is_post_type_archive( 'sc_event' ) || is_tax( 'sc_event_category' ) ) ) {
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
function sc_add_event_details( $post_id ) {

	// Support 1.x
	$start_date = sc_get_event_date( $post_id );
	$start_time = sc_get_event_start_time( $post_id );
	$end_time   = sc_get_event_end_time( $post_id );

	// New in 2.0
	$location   = sugar_calendar_get_event_by_object( $post_id, 'post' )->location; ?>

	<div class="sc_event_details" id="sc_event_details_<?php echo $post_id; ?>">
		<div class="sc_event_details_inner">
			<?php if ( sc_is_recurring( $post_id ) ) : ?>

				<div class="sc_event_date"><?php sc_show_single_recurring_date( $post_id ); ?></div>

			<?php else : ?>

				<div class="sc_event_date"><?php echo __( 'Date:', 'sugar-calendar' ) . ' ' . $start_date; ?></div>

			<?php endif; ?>

			<?php if ( ! empty( $start_time ) ) : ?>

				<div class="sc_event_time">
					<span class="sc_event_start_time"><?php echo __( 'Time:', 'sugar-calendar' ) . ' ' . $start_time; ?></span>

					<?php if ( ! empty( $end_time ) && ( $end_time !== $start_time ) ) : ?>

						<span class="sc_event_time_sep">&nbsp;<?php _e( 'to', 'sugar-calendar' ); ?>&nbsp;</span>
						<span class="sc_event_end_time"><?php echo $end_time; ?></span>

					<?php endif; ?>

				</div>

			<?php endif; ?>

			<?php if ( ! empty( $location ) ) : ?>
				<div class="sc_event_location">
					<?php echo __( 'Location:', 'sugar-calendar' ) . ' ' . esc_html( $location ); ?>
				</div>
			<?php endif; ?>

		</div><!--end .sc_event_details_inner-->
	</div><!--end .sc_event_details-->

	<?php
}
