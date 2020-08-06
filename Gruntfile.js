module.exports = function( grunt ) {

	// Force Unix newlines
	grunt.util.linefeed = '\n';

	// Load multiple grunt tasks using globbing patterns
	require( 'load-grunt-tasks' )( grunt );

	// Look for "lite" & "standard" options - default "lite"
	var lite     = grunt.option( 'lite' ),
		standard = grunt.option( 'standard' ),
		type     = 'lite';

	// Override to standard if option is set
	if ( standard && ! lite ) {
		type = 'standard';
	}

	// Set main file name
	var file = ( 'lite' === type )
		? 'sugar-calendar-lite.php'
		: 'sugar-calendar.php';

	// Set main name string
	var name = ( 'lite' === type )
		? 'Sugar Calendar (Lite)'
		: 'Sugar Calendar';

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
					},
				],
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

		makepot: {
			target: {
				options: {
					domainPath: '/sugar-event-calendar/includes/languages/', // Where to save the POT file.
					exclude: [ 'build/.*' ],
					mainFile: 'sugar-calendar-lite.php', // Main project file.
					potFilename: 'sugar-calendar.pot', // Name of the POT file.
					potHeaders: {
						poedit: true, // Includes common Poedit headers.
						'x-poedit-keywordslist': true, // Include a list of all possible gettext functions.
					},
					type: 'wp-plugin', // Type of project (wp-plugin or wp-theme).
					updateTimestamp: true, // Whether the POT-Creation-Date should be updated without other changes.
					processPot: function( pot, options ) {
						pot.headers[ 'report-msgid-bugs-to' ] = 'https://sugarcalendar.com';
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

		wp_readme_to_markdown: {
			your_target: {
				files: {
					'README.md': 'readme.txt'
				}
			},
		},

		clean: {

			// All temporary directories
			main: [
				'build/',
				'sugar-event-calendar/includes/standard/',
			],

			// Build
			build: [
				'build/' + type + '/'
			],

			// For Standard
			standard_before_clone: [
				'sugar-event-calendar/includes/standard/',
			],
			standard_after_clone: [
				'sugar-event-calendar/includes/standard/.git',
				'sugar-event-calendar/includes/standard/README.md',
			],
			standard_after_build: [
				'sugar-event-calendar/includes/standard/',
			]
		},

		copy: {

			// Copy the main plugin file
			bootstrap: {
				src: [
					'sugar-calendar-lite.php',
				],
				dest: 'build/' + type + '/' + file,
			},

			// Copy the plugin contents
			contents: {
				src: [
					'sugar-event-calendar/**',
					'*.txt',
				],
				dest: 'build/' + type + '/',
			},
		},

		replace: {

			// README.md
			readme_md: {
				src: [ 'README.md' ],
				overwrite: true,
				replacements: [{
					from: /Current Version:\s*(.*)/,
					to: "Current Version: <%= pkg.version %>",
				}],
			},

			// readme.txt
			readme_txt: {
				src: [ 'readme.txt' ],
				overwrite: true,
				replacements: [{
					from: /Stable tag:\s*(.*)/,
					to: "Stable tag:        <%= pkg.version %>",
				}],
			},

			// sugar-calendar-lite.php
			bootstrap_php: {
				src: [ 'sugar-calendar-lite.php' ],
				overwrite: true,
				replacements: [{
					from: /Version:\s*(.*)/,
					to: "Version:           <%= pkg.version %>",
				}],
			},

			// sugar-event-calendar/sugar-calendar.php
			loader_php: {
				src: [ 'sugar-event-calendar/sugar-calendar.php' ],
				overwrite: true,
				replacements: [{
					from: /private\s*\$version\s*=\s*'(.*)'/,
					to: "private $version = '<%= pkg.version %>'",
				}],
			},

			// Standard/Lite build bootstrap
			build_bootstrap_php: {
				src: [ 'build/' + type + '/' + file ],
				overwrite: true,
				replacements: [{
					from: /Plugin Name:\s*(.*)/,
					to: "Plugin Name:       " + name,
				}],
			},

			// POT Main file
			build_pot_bootstrap: {
				src: [ 'build/' + type + '/sugar-event-calendar/includes/languages/sugar-calendar.pot' ],
				overwrite: true,
				replacements: [{
					from: 'sugar-calendar-lite.php',
					to: file,
				}],
			},

			// POT Name
			build_pot_name: {
				src: [ 'build/' + type + '/sugar-event-calendar/includes/languages/sugar-calendar.pot' ],
				overwrite: true,
				replacements: [{
					from: 'Sugar Calendar (Lite)',
					to: name,
				}],
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
					archive: './build/' + type + '/<%= pkg.name %>.zip',
				},
				expand: true,
				cwd: 'build/' + type + '/<%= pkg.name %>/',
				src: [ '**/*' ],
				dest: '<%= pkg.name %>/',
			},
		},
	} );

	// Default
	grunt.registerTask( 'default', [
		'update',
	] );

	// Internationalization
	grunt.registerTask( 'i18n', [
		'addtextdomain',
		'force:checktextdomain',
	] );

	// Read Me
	grunt.registerTask( 'readme', [
		'wp_readme_to_markdown',
	] );

	// Bump versions
	grunt.registerTask( 'bump', [
		'replace:readme_md',
		'replace:readme_txt',
		'replace:bootstrap_php',
		'replace:loader_php',
	] );

	// Bump assets
	grunt.registerTask( 'update', [
		'bump',
		'cssmin:ltr',
		'rtlcss',
		'cssmin:rtl',
		'i18n',
		'makepot',
	] );

	// Clone Standard files for "standardize" task
	grunt.registerTask( 'standardize', function() {

		// Clean /standard directory
		grunt.task.run( 'clean:standard_before_clone' );

		// Make /standard directory
		grunt.file.mkdir( 'sugar-event-calendar/includes/standard' );

		// Ordered tasks
		grunt.task.run(

			// Clone files into /standard directory
			'gitclone:standard',

			// Clean .git directory from /standard
			'clean:standard_after_clone'
		);
	} );

	// Build the Standard .zip to ship somewhere
	grunt.registerTask( 'build', function() {

		// Default tasks
		var tasks = [ 'clean:build' ];

		// Maybe standardize
		if ( 'standard' === type ) {
			tasks.push( 'standardize' );
		}

		// Update
		tasks.push( 'update' );

		// Copy files
		tasks.push( 'copy:bootstrap' );
		tasks.push( 'copy:contents' );

		// Maybe replace name
		tasks.push( 'replace:build_bootstrap_php' );

		//
		if ( 'standard' === type ) {
			tasks.push( 'replace:build_pot_bootstrap' );
			tasks.push( 'replace:build_pot_name' );
		}

		// Compress
		tasks.push( 'compress' );

		// Clean up
		if ( 'standard' === type ) {
			tasks.push( 'clean:standard_after_build' );
			tasks.push( 'makepot' );
		}

		// Run all the tasks
		grunt.task.run( tasks );
	} );
};
