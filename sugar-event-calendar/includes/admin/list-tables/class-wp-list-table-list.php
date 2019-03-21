<?php
/**
 * Calendar List Table List Class
 *
 * @package Plugins/Site/Events/Admin/ListTables/List
 */
namespace Sugar_Calendar\Admin\Mode;

// Exit if accessed directly
defined( 'ABSPATH' ) || exit;

/**
 * Event table
 *
 * This list table is responsible for showing events in a traditional table.
 * It will look a lot like `WP_Posts_List_Table` but extends our base, and shows
 * events in a monthly way.
 */
class Basic extends Base_List_Table {

	/**
	 * The mode of the current view
	 *
	 * @since 2.0.0
	 *
	 * @var string
	 */
	public $mode = 'list';

	/**
	 * Unix time month start
	 *
	 * @since 2.0.0
	 *
	 * @var int
	 */
	private $list_start = 0;

	/**
	 * Unix time month end
	 *
	 * @since 2.0.0
	 *
	 * @var int
	 */
	private $list_end = 0;

	/**
	 * The main constructor method
	 *
	 * @since 2.0.0
	 */
	public function __construct( $args = array() ) {
		parent::__construct( $args );

		// Detect the range
		$range_type   = 'year';
		$range_length = '1';

		// View start
		$view_start = "{$this->year}-01-01 00:00:00";
		$boundary   = "+{$range_length} {$range_type} -1 second";

		// Month boundaries
		$this->list_start = mysql2date( 'U', $view_start );
		$this->list_end   = strtotime( $boundary, $this->list_start );

		// View end
		$view_end = date_i18n( 'Y-m-d H:i:s', $this->list_end );

		// Set the view
		$this->set_view( $view_start, $view_end );

		// Override the list table if one was passed in
		if ( ! empty( $args['list_table'] ) ) {
			$this->set_list_table( $args['list_table'] );
		}
	}

	/**
	 * Get the current page number
	 *
	 * @since 2.0.0
	 *
	 * @return string
	 */
	protected function get_order() {
		return $this->get_request_var( 'order', 'strtolower', 'desc' );
	}

	/**
	 * Override date-query arguments with custom recurring criteria.
	 *
	 * @since 2.0.0
	 *
	 * @return array
	 */
	protected function get_date_query_args() {
		return array(
			'relation'      => 'OR',
			'recurring'     => $this->get_recurring_query_args(),
			'non-recurring' => sugar_calendar_get_non_recurring_date_query_args( $this->mode, $this->view_start, $this->view_end ),
		);
	}

	/**
	 * Return array of recurring query arguments, used in Date_Query.
	 *
	 * These change based on the boundaries, and should be overridden in each
	 * individual subclass that has a unique view_start and view_end approach.
	 *
	 * Recurring events
	 * - recurrence starts before the view ends
	 * - recurrence ends after the view starts
	 * - start and end do not matter
	 *
	 * @since 2.0.0
	 *
	 * @return array
	 */
	protected function get_recurring_query_args() {
		return array(
			'relation' => 'AND',

			// Recurrence
			array(
				'column'  => 'recurrence',
				'compare' => 'IN',
				'value'   => array_keys( $this->get_recurrence_types() )
			),


			// Recurring Ends
			array(
				'relation' => 'OR',

				// No end (recurs forever) - exploits math in Date_Query
				// This might break someday. Works great now though!
				array(
					'column'    => 'recurrence_end',
					'inclusive' => true,
					'before'    => '0000-01-01 00:00:00'
				),

				// Ends after the beginning of this view
				array(
					'column'    => 'recurrence_end',
					'inclusive' => true,
					'after'     => $this->view_start
				)
			),

			// Make sure events
			array(
				'relation' => 'AND',
				array(
					'column'  => 'start',
					'compare' => '<=',
					'year'    => $this->year
				),
				array(
					'column'  => 'end',
					'compare' => '<=',
					'year'    => $this->year
				)
			),
		);
	}

	/**
	 * Import object variables from another object.
	 *
	 * @since 2.0.0
	 *
	 * @param object $object
	 */
    private function set_list_table( $object = false ) {

		// Bail if no object passed
		if ( empty( $object ) ) {
			return;
		}

		// Set the old list table
		$this->old_list_table = $object;

		// Loop through object vars and set the key/value
        foreach ( get_object_vars( $object ) as $key => $value ) {
			if ( ! isset( $this->{$key} ) ) {
				$this->{$key} = $value;
			}
        }

		// Set the global list table to this class
		$GLOBALS['wp_list_table'] = $this;
    }

	/**
	 * Mock function for custom list table columns.
	 *
	 * @since 2.0.0
	 *
	 * @return array
	 */
	public function get_columns() {

		// Default columns
		$columns = array(
			'title'    => esc_html__( 'Title',    'sugar-calendar' ),
			'start'    => esc_html__( 'Starts',   'sugar-calendar' ),
			'end'      => esc_html__( 'Ends',     'sugar-calendar' ),
			'duration' => esc_html__( 'Duration', 'sugar-calendar' ),
			'repeat'   => esc_html__( 'Repeats',  'sugar-calendar' )
		);

		// Return columns
		return $columns;
	}

	/**
	 * Allow columns to be sortable
	 *
	 * @since 2.0.0
	 *
	 * @return array An associative array containing the sortable columns
	 */
	protected function get_sortable_columns() {
		return array(
			'title'  => array( 'title', true ),
			'start'  => array( 'start', true ),
			'end'    => array( 'end',   true ),

			// May want to remove when more complex recurrences exist
			'repeat' => array( 'repeat', true )
		);
	}

	/**
	 * Return the "title" column as the primary column name.
	 *
	 * @since 2.0.0
	 *
	 * @return string
	 */
	protected function get_primary_column_name() {
		return 'title';
	}

	/**
	 * Output the title for the event
	 *
	 * @since 2.0.0
	 *
	 * @param object $object
	 */
	public function column_title( $object = null ) {

		// Get the for event color
		$color = $this->get_item_color( $object );

		// Wrap output in a helper div
		?><div data-color="<?php echo esc_attr( $color ); ?>"><?php

			parent::column_title( $object );

		?></div><?php
	}

	/**
	 * Paginate through months & years
	 *
	 * @since 2.0.0
	 *
	 * @param array $args
	 */
	protected function pagination( $args = array() ) {

		// Parse args
		$r = wp_parse_args( $args, array(
			'small'  => '1 month',
			'large'  => '1 year',
			'labels' => array(
				'today'      => esc_html__( 'Today',          'sugar-calendar' ),
				'next_small' => esc_html__( 'Next Month',     'sugar-calendar' ),
				'next_large' => esc_html__( 'Next Year',      'sugar-calendar' ),
				'prev_small' => esc_html__( 'Previous Month', 'sugar-calendar' ),
				'prev_large' => esc_html__( 'Previous Year',  'sugar-calendar' )
			)
		) );

		// Return pagination
		return parent::pagination( $r );
	}

	/**
	 *
	 * @since 2.0.0
	 */
	public function display_mode() {

		// Attempt to display rows
		if ( ! empty( $this->query->items ) ) {
			foreach ( $this->query->items as $item ) {
				$this->single_row( $item );
			}

		// No rows to display
		} else {
			$this->no_items();
		}
	}

	/**
	 * Message to be displayed when there are no items
	 *
	 * @since 2.0.0
	 */
	public function no_items() {
		?>
		<tr><td colspan="<?php echo count( $this->get_columns() ); ?>"><?php esc_html_e( 'No events found.', 'sugar-calendar' ); ?></td></tr>
		<?php
	}

	/**
	 * @global WP_Post $post
	 *
	 * @param int|WP_Post $post
	 * @param int         $level
	 */
	public function single_row( $item ) {

		// Title
		$link  = $this->get_event_edit_url( $item );
		$title = '<strong><a href="' . esc_url( $link ) . '" class="row-title">' . esc_html( $item->title ) . '</a></strong>';

		// Start
		$start = $this->get_event_date( $item->start ) . '<br>' . $this->get_event_time( $item->start );

		// End
		$end   = $this->get_event_date( $item->end   ) . '<br>' . $this->get_event_time( $item->end   );

		// Duration
		$duration = ! empty( $item->all_day )
			? esc_html__( 'All Day', 'sugar-calendar' )
			: $this->get_human_diff_time( $item->start, $item->end );

		// Recurrence
		$recurrence = '&mdash;';

		// Get recurrence type
		if ( ! empty( $item->recurrence ) ) {
			$intervals = $this->get_recurrence_types();

			// Interval is known
			if ( isset( $intervals[ $item->recurrence ] ) ) {
				$recurrence = $intervals[ $item->recurrence ];
			}
		}

		// Row actions
		$row_actions = $this->get_row_actions( $item ); ?>

		<tr id="event-<?php echo $item->id; ?>" class="">
			<td><?php echo $title . $this->row_actions( $row_actions ); ?></td>
			<td><?php echo $start; ?></td>
			<td><?php echo $end; ?></td>
			<td><?php echo $duration; ?></td>
			<td><?php echo $recurrence; ?></td>
		</tr>

		<?php
	}

	/**
	 * Private method to shim in support for row actions for different object types.
	 *
	 * @since 2.0.0
	 * @param object $item
	 * @return array
	 */
	private function get_row_actions( $item ) {
		switch ( $item->object_type ) {
			case 'post' :
				return $this->get_post_row_actions( $item );
			case 'user' :
				//return $this->get_user_row_actions( $item );
			case 'term' :
				//return $this->get_term_row_actions( $item );
		}
	}

	/**
	 * Private method copied from WP_Posts_List_Table::handle_row_actions()
	 *
	 * @since 2.0.0
	 * @param object $item
	 * @return array
	 */
	private function get_post_row_actions( $item ) {

		// Attempt to get the post
		$post = get_post( $item->object_id );

		// Bail if no post was found
		if ( empty( $post ) ) {
			return array();
		}

		$post_type_object = get_post_type_object( $post->post_type );
		$can_edit_post = current_user_can( 'edit_post', $post->ID );
		$actions = array();
		$title = _draft_or_post_title();

		if ( $can_edit_post && 'trash' != $post->post_status ) {
			$actions['edit'] = sprintf(
				'<a href="%s" aria-label="%s">%s</a>',
				get_edit_post_link( $post->ID ),
				/* translators: %s: post title */
				esc_attr( sprintf( __( 'Edit &#8220;%s&#8221;', 'sugar-calendar' ), $title ) ),
				__( 'Edit', 'sugar-calendar' )
			);
		}

		if ( current_user_can( 'delete_post', $post->ID ) ) {
			if ( 'trash' === $post->post_status ) {
				$actions['untrash'] = sprintf(
					'<a href="%s" aria-label="%s">%s</a>',
					wp_nonce_url( admin_url( sprintf( $post_type_object->_edit_link . '&amp;action=untrash', $post->ID ) ), 'untrash-post_' . $post->ID ),
					/* translators: %s: post title */
					esc_attr( sprintf( __( 'Restore &#8220;%s&#8221; from the Trash', 'sugar-calendar' ), $title ) ),
					__( 'Restore', 'sugar-calendar' )
				);
			} elseif ( EMPTY_TRASH_DAYS ) {
				$actions['trash'] = sprintf(
					'<a href="%s" class="submitdelete" aria-label="%s">%s</a>',
					get_delete_post_link( $post->ID ),
					/* translators: %s: post title */
					esc_attr( sprintf( __( 'Move &#8220;%s&#8221; to the Trash', 'sugar-calendar' ), $title ) ),
					_x( 'Trash', 'verb', 'sugar-calendar' )
				);
			}
			if ( 'trash' === $post->post_status || ! EMPTY_TRASH_DAYS ) {
				$actions['delete'] = sprintf(
					'<a href="%s" class="submitdelete" aria-label="%s">%s</a>',
					get_delete_post_link( $post->ID, '', true ),
					/* translators: %s: post title */
					esc_attr( sprintf( __( 'Delete &#8220;%s&#8221; permanently', 'sugar-calendar' ), $title ) ),
					__( 'Delete Permanently', 'sugar-calendar' )
				);
			}
		}

		if ( is_post_type_viewable( $post_type_object ) ) {
			if ( in_array( $post->post_status, array( 'pending', 'draft', 'future' ) ) ) {
				if ( $can_edit_post ) {
					$preview_link = get_preview_post_link( $post );
					$actions['view'] = sprintf(
						'<a href="%s" rel="bookmark" aria-label="%s">%s</a>',
						esc_url( $preview_link ),
						/* translators: %s: post title */
						esc_attr( sprintf( __( 'Preview &#8220;%s&#8221;', 'sugar-calendar' ), $title ) ),
						__( 'Preview', 'sugar-calendar' )
					);
				}
			} elseif ( 'trash' != $post->post_status ) {
				$actions['view'] = sprintf(
					'<a href="%s" rel="bookmark" aria-label="%s">%s</a>',
					get_permalink( $post->ID ),
					/* translators: %s: post title */
					esc_attr( sprintf( __( 'View &#8220;%s&#8221;', 'sugar-calendar' ), $title ) ),
					__( 'View', 'sugar-calendar' )
				);
			}
		}

		if ( 'wp_block' === $post->post_type ) {
			$actions['export'] = sprintf(
				'<button type="button" class="wp-list-reusable-blocks__export button-link" data-id="%s" aria-label="%s">%s</button>',
				$post->ID,
				/* translators: %s: post title */
				esc_attr( sprintf( __( 'Export &#8220;%s&#8221; as JSON', 'sugar-calendar' ), $title ) ),
				__( 'Export as JSON', 'sugar-calendar' )
			);
		}

		if ( is_post_type_hierarchical( $post->post_type ) ) {

			/**
			 * Filters the array of row action links on the Pages list table.
			 *
			 * The filter is evaluated only for hierarchical post types.
			 *
			 * @since 2.8.0
			 *
			 * @param array $actions An array of row action links. Defaults are
			 *                         'Edit', 'Quick Edit', 'Restore', 'Trash',
			 *                         'Delete Permanently', 'Preview', and 'View'.
			 * @param WP_Post $post The post object.
			 */
			$actions = apply_filters( 'page_row_actions', $actions, $post );
		} else {

			/**
			 * Filters the array of row action links on the Posts list table.
			 *
			 * The filter is evaluated only for non-hierarchical post types.
			 *
			 * @since 2.8.0
			 *
			 * @param array $actions An array of row action links. Defaults are
			 *                         'Edit', 'Quick Edit', 'Restore', 'Trash',
			 *                         'Delete Permanently', 'Preview', and 'View'.
			 * @param WP_Post $post The post object.
			 */
			$actions = apply_filters( 'post_row_actions', $actions, $post );
		}

		// Return actions
		return $actions;
	}
}
