<?php
/**
 * Meta-box Section Class
 *
 * @package Plugins/Site/Event/Admin/Metaboxes
 */
namespace Sugar_Calendar\Admin\Editor\Meta;

// Exit if accessed directly
defined( 'ABSPATH' ) || exit;

/**
 * Main meta box section class for interfacing with the events and eventmeta
 * database tables.
 *
 * @since 2.0.18
 */
class Section {

	/**
	 * Unique ID for this section
	 *
	 * @since 2.0.18
	 * @var string
	 */
	public $id = '';

	/**
	 * Unique label for this section
	 *
	 * @since 2.0.18
	 * @var string
	 */
	public $label = '';

	/**
	 * Unique ID for this section
	 *
	 * @since 2.0.18
	 * @var string
	 */
	public $icon = 'editor-help';

	/**
	 * Unique ID for this section
	 *
	 * @since 2.0.18
	 * @var int
	 */
	public $order = 50;

	/**
	 * Unique ID for this section
	 *
	 * @since 2.0.18
	 * @var string
	 */
	public $callback = '';

	/**
	 * Handle parameters if passed in during construction
	 *
	 * @since 2.0.18
	 * @param array $args
	 */
	public function __construct( $args = array() ) {
		if ( ! empty( $args ) ) {
			$this->init( $args );
		}
	}

	/**
	 * Initialize the section
	 *
	 * @since 2.0.18
	 * @param array $args
	 */
	protected function init( $args = array() ) {

		// Get default object variables
		$defaults =  get_object_vars( $this );

		// Parse the arguments
		$r = wp_parse_args( $args, $defaults );

		// Set the object variables
		$this->set_vars( $r );
	}

	/**
	 * Set class variables from arguments.
	 *
	 * @since 2.0.18
	 * @param array $args
	 */
	protected function set_vars( $args = array() ) {

		// Bail if empty or not an array
		if ( empty( $args ) ) {
			return;
		}

		// Cast to an array
		if ( ! is_array( $args ) ) {
			$args = (array) $args;
		}

		// Set all properties
		foreach ( $args as $key => $value ) {
			$this->{$key} = $value;
		}
	}
}
