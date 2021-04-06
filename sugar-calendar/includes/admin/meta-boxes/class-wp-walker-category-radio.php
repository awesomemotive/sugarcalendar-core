<?php
/**
 * Events Walker Radio
 *
 * @package Plugins/Site/Events/Admin/Walker
 */
namespace Sugar_Calendar\Admin\Taxonomy;

// Exit if accessed directly
defined( 'ABSPATH' ) || exit;

/** Walker_Category_Checklist class */
require_once ABSPATH . 'wp-admin/includes/class-walker-category-checklist.php';

/**
 * Extends Walker_Category_Checklist and uses radio input instead of checklist
 *
 * @since 2.0.0
 */
class Walker_Category_Radio extends \Walker_Category_Checklist {

	/**
	 * Start the element output.
	 *
	 * @see Walker::start_el()
	 *
	 * @since 2.0.0
	 *
	 * @param string $output   Passed by reference. Used to append additional content.
	 * @param object $category The current term object.
	 * @param int    $depth    Depth of the term in reference to parents. Default 0.
	 * @param array  $args     An array of arguments. @see wp_terms_checklist()
	 * @param int    $id       ID of the current term.
	 */
	public function start_el( &$output, $category, $depth = 0, $args = array(), $id = 0 ) {

		// Note that Walker classes are trusting with their previously
		// validated object properties.
		$taxonomy = sanitize_key( $args['taxonomy'] );
		$name     = "tax_input[{$taxonomy}]";

		// Maybe show popular categories tab
		$args['popular_cats'] = empty( $args['popular_cats'] )
			? array()
			: $args['popular_cats'];

		// Maybe add popular category class
		$class = in_array( $category->term_id, $args['popular_cats'] )
			? ' class="popular-category"'
			: '';

		// Maybe use already selected categories
		$args['selected_cats'] = empty( $args['selected_cats'] )
			? array()
			: $args['selected_cats'];

		// List item ID
		$item_id  = sanitize_key( "{$taxonomy}-{$category->term_id}" );
		$checked  = checked( in_array( $category->term_id, $args['selected_cats'], true ), true, false );
		$disabled = disabled( empty( $args['disabled'] ), false, false );
		$text     = apply_filters( 'the_category', $category->name, '', '' );

		// Calendar color
		$bg_color = sugar_calendar_get_calendar_color( $category->term_id );
		$color    = sugar_calendar_get_contrast_color( $bg_color );

		// Start an output buffer
		ob_start(); ?>

		<li id="<?php echo esc_attr( $item_id ); ?>" <?php echo $class; ?>>
			<style type="text/css" id="sc-radio-style-<?php echo esc_attr( $item_id ); ?>">
				#in-<?php echo esc_attr( $item_id ); ?> {
					background-color: <?php echo esc_html( $bg_color ); ?>;
				}
				#in-<?php echo esc_attr( $item_id ); ?>:checked:before {
					 background: <?php echo esc_html( $color ); ?>;
				}
			</style>
			<label class="selectit">
				<input value="<?php echo esc_attr( $category->term_id ); ?>" type="radio" name="<?php echo $name; ?>[]" id="in-<?php echo $item_id; ?>" <?php echo $checked; ?> <?php echo $disabled; ?> /><?php

				echo esc_html( $text );

			?></label>

		<?php

		// Add the list item to the output
		$output .= ob_get_clean();
	}
}
