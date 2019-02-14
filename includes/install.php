<?php

/**
 * Install
 *
 * Runs on plugin install.
 *
 * @access      private
 * @since       1.0.0
 * @return      void
*/
function sc_install() {
	
	// setup the download custom post type
	sc_setup_post_types();
	
	// clear permalinks
	flush_rewrite_rules();

	update_option( 'sc_version', SEC_PLUGIN_VERSION );
}
register_activation_hook( SC_PLUGIN_FILE, 'sc_install' );