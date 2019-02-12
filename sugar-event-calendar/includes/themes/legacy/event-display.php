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
	global $post;

	if ( is_singular( 'sc_event' ) || is_post_type_archive( 'sc_event' ) || is_tax( 'sc_event_category' ) && is_main_query() ) {
		ob_start();
		do_action( 'sc_before_event_content', $post->ID );
		echo $content;
		do_action( 'sc_after_event_content', $post->ID );
		$content = ob_get_clean();
	}

	return $content;
}

function sc_add_event_details( $event_id ) {
	ob_start();

	// Support 1.x
	$start_date = sc_get_event_date( $event_id );
	$start_time = sc_get_event_start_time( $event_id );
	$end_time   = sc_get_event_end_time( $event_id );

	// New in 2.0
	$location   = sugar_calendar_get_event_by_object( $event_id, 'post' )->location; ?>

	<div class="sc_event_details" id="sc_event_details_<?php echo $event_id; ?>">
		<div class="sc_event_details_inner">
			<?php if ( sc_is_recurring( $event_id ) ) : ?>

				<div class="sc_event_date"><?php sc_show_single_recurring_date( $event_id ); ?></div>

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
	echo ob_get_clean();
}
