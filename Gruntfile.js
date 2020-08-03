module.exports = function( grunt ) {

	// Load multiple grunt tasks using globbing patterns
	require( 'load-grunt-tasks' )( grunt );

	// Project configuration.
	grunt.initConfig( {

		pkg: grunt.file.readJSON( 'package.json' ),

		rtlcss: {
			options: {
				opts: {
					processUrls: false,
					autoRename: false,
					clean: true,
				},
				saveUnmodified: false,
			},

			target: {
				files: [
					{
						expand: true,
						cwd: 'sugar-event-calendar/includes/admin/assets/css/ltr',
						src: [ 'chosen.css' ],
						dest: 'sugar-event-calendar/includes/admin/assets/css/rtl',
						ext: '.css',
					},
					{
						expand: true,
						cwd: 'sugar-event-calendar/includes/admin/assets/css/ltr',
						src: [ 'sc-calendar.css' ],
						dest: 'sugar-event-calendar/includes/admin/assets/css/rtl',
						ext: '.css',
					},
					{
						expand: true,
						cwd: 'sugar-event-calendar/includes/admin/assets/css/ltr',
						src: [ 'sc-chosen.css' ],
						dest: 'sugar-event-calendar/includes/admin/assets/css/rtl',
						ext: '.css',
					},
					{
						expand: true,
						cwd: 'sugar-event-calendar/includes/admin/assets/css/ltr',
						src: [ 'sc-datepicker.css' ],
						dest: 'sugar-event-calendar/includes/admin/assets/css/rtl',
						ext: '.css',
					},
					{
						expand: true,
						cwd: 'sugar-event-calendar/includes/admin/assets/css/ltr',
						src: [ 'sc-menu.css' ],
						dest: 'sugar-event-calendar/includes/admin/assets/css/rtl',
						ext: '.css',
					},
					{
						expand: true,
						cwd: 'sugar-event-calendar/includes/admin/assets/css/ltr',
						src: [ 'sc-meta-box.css' ],
						dest: 'sugar-event-calendar/includes/admin/assets/css/rtl',
						ext: '.css',
					},
					{
						expand: true,
						cwd: 'sugar-event-calendar/includes/admin/assets/css/ltr',
						src: [ 'sc-nav.css' ],
						dest: 'sugar-event-calendar/includes/admin/assets/css/rtl',
						ext: '.css',
					},
					{
						expand: true,
						cwd: 'sugar-event-calendar/includes/admin/assets/css/ltr',
						src: [ 'sc-settings.css' ],
						dest: 'sugar-event-calendar/includes/admin/assets/css/rtl',
						ext: '.css',
					},
				],
			},
		},

		replace: {

			// /README.md
			readme_md: {
				src: [ 'README.md' ],
				overwrite: true,
				replacements: [{
					from: /Current Version:\s*(.*)/,
					to: "Current Version: <%= pkg.version %>",
				}],
			},

			// /readme.txt
			readme_txt: {
				src: [ 'readme.txt' ],
				overwrite: true,
				replacements: [{
					from: /Stable tag:\s*(.*)/,
					to: "Stable tag:        <%= pkg.version %>",
				}],
			},

			// /sugar-calendar.php
			bootstrap_lite_php: {
				src: [ 'sugar-calendar-lite.php' ],
				overwrite: true,
				replacements: [{
					from: /Version:\s*(.*)/,
					to: "Version:           <%= pkg.version %>",
				}],
			},

			// /sugar-calendar.php
			bootstrap_standard_php: {
				src: [ '<%= pkg.name %>.php' ],
				overwrite: true,
				replacements: [{
					from: /Version:\s*(.*)/,
					to: "Version:           <%= pkg.version %>",
				}],
			},

			// /sugar-event-calendar/sugar-calendar.php
			loader_php: {
				src: [ 'sugar-event-calendar/<%= pkg.name %>.php' ],
				overwrite: true,
				replacements: [{
					from: /private\s*\$version\s*=\s*'(.*)'/,
					to: "private $version = '<%= pkg.version %>'",
				}],
			},

			// /sugar-calendar-lite.php
			lite_main: {
				src: [ 'sugar-calendar-lite.php' ],
				overwrite: true,
				replacements: [{
					from: /Plugin Name:\s*(.*)/,
					to: "Plugin Name:       Sugar Calendar (Lite)",
				}],
			},

			// /sugar-calendar.php
			standard_main: {
				src: [ 'sugar-calendar.php' ],
				overwrite: true,
				replacements: [{
					from: /Plugin Name:\s*(.*)/,
					to: "Plugin Name:       Sugar Calendar",
				}],
			},
		},

		cssmin: {
			options: {
				mergeIntoShorthands: false,
			},
			ltr: {
				files: [
					{
						expand: true,
						cwd: 'sugar-event-calendar/includes/admin/assets/css/ltr',
						src: [ 'chosen.css' ],
						dest: 'sugar-event-calendar/includes/admin/assets/css/min/ltr',
						ext: '.css',
					},
					{
						expand: true,
						cwd: 'sugar-event-calendar/includes/admin/assets/css/ltr',
						src: [ 'sc-calendar.css' ],
						dest: 'sugar-event-calendar/includes/admin/assets/css/min/ltr',
						ext: '.css',
					},
					{
						expand: true,
						cwd: 'sugar-event-calendar/includes/admin/assets/css/ltr',
						src: [ 'sc-chosen.css' ],
						dest: 'sugar-event-calendar/includes/admin/assets/css/min/ltr',
						ext: '.css',
					},
					{
						expand: true,
						cwd: 'sugar-event-calendar/includes/admin/assets/css/ltr',
						src: [ 'sc-datepicker.css' ],
						dest: 'sugar-event-calendar/includes/admin/assets/css/min/ltr',
						ext: '.css',
					},
					{
						expand: true,
						cwd: 'sugar-event-calendar/includes/admin/assets/css/ltr',
						src: [ 'sc-menu.css' ],
						dest: 'sugar-event-calendar/includes/admin/assets/css/min/ltr',
						ext: '.css',
					},
					{
						expand: true,
						cwd: 'sugar-event-calendar/includes/admin/assets/css/ltr',
						src: [ 'sc-meta-box.css' ],
						dest: 'sugar-event-calendar/includes/admin/assets/css/min/ltr',
						ext: '.css',
					},
					{
						expand: true,
						cwd: 'sugar-event-calendar/includes/admin/assets/css/ltr',
						src: [ 'sc-nav.css' ],
						dest: 'sugar-event-calendar/includes/admin/assets/css/min/ltr',
						ext: '.css',
					},
					{
						expand: true,
						cwd: 'sugar-event-calendar/includes/admin/assets/css/ltr',
						src: [ 'sc-settings.css' ],
						dest: 'sugar-event-calendar/includes/admin/assets/css/min/ltr',
						ext: '.css',
					},				],
			},
			rtl: {
				files: [
					{
						expand: true,
						cwd: 'sugar-event-calendar/includes/admin/assets/css/rtl',
						src: [ 'chosen.css' ],
						dest: 'sugar-event-calendar/includes/admin/assets/css/min/rtl',
						ext: '.css',
					},
					{
						expand: true,
						cwd: 'sugar-event-calendar/includes/admin/assets/css/rtl',
						src: [ 'sc-calendar.css' ],
						dest: 'sugar-event-calendar/includes/admin/assets/css/min/rtl',
						ext: '.css',
					},
					{
						expand: true,
						cwd: 'sugar-event-calendar/includes/admin/assets/css/rtl',
						src: [ 'sc-chosen.css' ],
						dest: 'sugar-event-calendar/includes/admin/assets/css/min/rtl',
						ext: '.css',
					},
					{
						expand: true,
						cwd: 'sugar-event-calendar/includes/admin/assets/css/rtl',
						src: [ 'sc-datepicker.css' ],
						dest: 'sugar-event-calendar/includes/admin/assets/css/min/rtl',
						ext: '.css',
					},
					{
						expand: true,
						cwd: 'sugar-event-calendar/includes/admin/assets/css/rtl',
						src: [ 'sc-menu.css' ],
						dest: 'sugar-event-calendar/includes/admin/assets/css/min/rtl',
						ext: '.css',
					},
					{
						expand: true,
						cwd: 'sugar-event-calendar/includes/admin/assets/css/rtl',
						src: [ 'sc-meta-box.css' ],
						dest: 'sugar-event-calendar/includes/admin/assets/css/min/rtl',
						ext: '.css',
					},
					{
						expand: true,
						cwd: 'sugar-event-calendar/includes/admin/assets/css/rtl',
						src: [ 'sc-nav.css' ],
						dest: 'sugar-event-calendar/includes/admin/assets/css/min/rtl',
						ext: '.css',
					},
					{
						expand: true,
						cwd: 'sugar-event-calendar/includes/admin/assets/css/rtl',
						src: [ 'sc-settings.css' ],
						dest: 'sugar-event-calendar/includes/admin/assets/css/min/rtl',
						ext: '.css',
					},
				],
			},
		},

		checktextdomain: {
			options: {
				text_domain: '<%= pkg.name %>',
				keywords: [
					'__:1,2d',
					'_e:1,2d',
					'_x:1,2c,3d',
					'esc_html__:1,2d',
					'esc_html_e:1,2d',
					'esc_html_x:1,2c,3d',
					'esc_attr__:1,2d',
					'esc_attr_e:1,2d',
					'esc_attr_x:1,2c,3d',
					'_ex:1,2c,3d',
					'_n:1,2,3,4d',
					'_nx:1,2,4c,5d',
					'_n_noop:1,2,3d',
					'_nx_noop:1,2,3c,4d',
					' __ngettext:1,2,3d',
					'__ngettext_noop:1,2,3d',
					'_c:1,2d',
					'_nc:1,2,4c,5d',
				],
			},
			files: {
				src: [
					'*.php',
					'**/*.php',
					'!\.git/**/*',
					'!bin/**/*',
					'!node_modules/**/*',
					'!tests/**/*',
					'!build/**/*'
				],
				expand: true,
			},
		},

		addtextdomain: {
			options: {
				textdomain: '<%= pkg.name %>',
			},
			update_all_domains: {
				options: {
					updateDomains: true
				},
				src: [
					'*.php',
					'**/*.php',
					'!\.git/**/*',
					'!bin/**/*',
					'!node_modules/**/*',
					'!tests/**/*',
					'!build/**/*'
				]
			}
		},

		wp_readme_to_markdown: {
			your_target: {
				files: {
					'README.md': 'readme.txt'
				}
			},
		},

		makepot: {
			target: {
				options: {
					domainPath: '/sugar-event-calendar/includes/languages/', // Where to save the POT file.
					exclude: [ 'build/.*' ],
					mainFile: '<%= pkg.name %>.php', // Main project file.
					potFilename: '<%= pkg.name %>.pot', // Name of the POT file.
					potHeaders: {
						poedit: true, // Includes common Poedit headers.
						'x-poedit-keywordslist': true, // Include a list of all possible gettext functions.
					},
					type: 'wp-plugin', // Type of project (wp-plugin or wp-theme).
					updateTimestamp: true, // Whether the POT-Creation-Date should be updated without other changes.
					processPot: function( pot, options ) {
						pot.headers[ 'report-msgid-bugs-to' ] = '<%= pkg.homepage %>';
						pot.headers[ 'last-translator' ] = 'WP-Translations (http://wp-translations.org/)';
						pot.headers[ 'language-team' ] = 'WP-Translations <wpt@wp-translations.org>';
						pot.headers.language = 'en_US';
						let translation, // Exclude meta data from pot.
							excluded_meta = [
								'Plugin Name of the plugin/theme',
								'Plugin URI of the plugin/theme',
								'Author of the plugin/theme',
								'Author URI of the plugin/theme',
							];
						for ( translation in pot.translations[ '' ] ) {
							if ( 'undefined' !== typeof pot.translations[ '' ][ translation ].comments.extracted ) {
								if ( excluded_meta.indexOf( pot.translations[ '' ][ translation ].comments.extracted ) >= 0 ) {
									console.log( 'Excluded meta: ' + pot.translations[ '' ][ translation ].comments.extracted );
									delete pot.translations[ '' ][ translation ];
								}
							}
						}
						return pot;
					},
				},
			},
		},

		gitclone: {

			// Shallow clone into /standard directory
			standard: {
				options: {
					repository: 'git@github.com:sugarcalendar/standard.git',
					branch: 'main',
					directory: 'sugar-event-calendar/includes/standard',
					depth: 1
				},
			},
		},

		clean: {

			// Build
			main: [
				'build/',
			],

			// For Lite
			lite: [
				'sugar-calendar.php',
				'sugar-event-calendar/includes/standard/',
			],

			// For Standard
			standard_before_clone: [
				'sugar-event-calendar/includes/standard/',
			],
			standard_after_clone: [
				'sugar-event-calendar/includes/standard/.git',
			],
		},

		copy: {

			// Copy the plugin into the build directory
			build: {
				src: [
					'sugar-event-calendar/**',
					'*.php',
					'*.txt',
				],
				dest: 'build/<%= pkg.name %>/',
			},

			// For Standard
			standard: {
				src: [
					'sugar-calendar-lite.php',
				],
				dest: 'sugar-calendar.php',
			}
		},

		// Compress build directory into <name>.zip and <name>-<version>.zip
		compress: {
			main: {
				options: {
					mode: 'zip',
					archive: './build/<%= pkg.name %>.zip',
				},
				expand: true,
				cwd: 'build/<%= pkg.name %>/',
				src: [ '**/*' ],
				dest: '<%= pkg.name %>/',
			},
		},
	} );

	// Default
	grunt.registerTask( 'default', [
		'i18n',
	] );

	// Internationalization
	grunt.registerTask( 'i18n', [
		'addtextdomain',
		'makepot',
	] );

	// Read Me
	grunt.registerTask( 'readme', [
		'wp_readme_to_markdown',
	] );

	// Bump versions
	grunt.registerTask( 'bump', [
		'replace:readme_md',
		'replace:readme_txt',
		'replace:bootstrap_lite_php',
		'replace:bootstrap_standard_php',
		'replace:loader_php',
	] );

	// Bump assets
	grunt.registerTask( 'update', [
		'bump',
		'cssmin:ltr',
		'rtlcss',
		'cssmin:rtl',
		'force:checktextdomain',
		'makepot',
	] );

	/** Lite ******************************************************************/

	// Convert the files in this repository to Lite
	grunt.registerTask( 'liteize', [
		'replace:lite_main',
		'clean:lite',
	] );

	// Build the Lite .zip to ship somewhere
	grunt.registerTask( 'build', [
		'update',
		'clean:build',
		'clean:lite',
		'copy:build',
		'compress',
	] );

	/** Standard **************************************************************/

	// Clone Standard files for "standardize" task
	grunt.registerTask( 'clone-standard', function() {

		// Clean /standard directory
		grunt.task.run( 'clean:standard_before_clone' );

		// Make /standard directory
		grunt.file.mkdir( 'sugar-event-calendar/includes/standard' );

		// Clone files into /standard directory
		grunt.task.run( 'gitclone:standard' );

		// Clean .git directory from /standard
		grunt.task.run( 'clean:standard_after_clone' );
	} );

	// Convert the files in this repository to Standard
	grunt.registerTask( 'standardize', [
		'copy:standard',
		'replace:standard_main',
		'clone-standard'
	] );

	// Build the Standard .zip to ship somewhere
	grunt.registerTask( 'build-standard', [
		'update',
		'clean:build',
		'standardize',
		'compress',
	] );

	grunt.util.linefeed = '\n';
};
