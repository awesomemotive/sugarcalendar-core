<?php

function sc_3rd_party_post_type_supports() {
	/* support for Genesis extras */
	add_post_type_support( 'sc_event', 'genesis-seo' );
	add_post_type_support( 'sc_event', 'genesis-layouts' );
	add_post_type_support( 'sc_event', 'genesis-simple-sidebars' );
}
add_action('init', 'sc_3rd_party_post_type_supports');