/* jshint node:true */
module.exports = function (grunt) {
	'use strict';

	grunt.initConfig({
		pkg: grunt.file.readJSON( 'package.json' ),
		// setting folder templates
		dirs: {
			css: 'assets/css',
			fonts: 'assets/fonts',
			images: 'assets/images',
			js: 'assets/js',
			lang: 'languages'
		},

		// Compile all .less files.
		less: {
			compile: {
				options: {
					// These paths are searched for @imports
					paths: ['<%= dirs.css %>/']
				},
				files: [{
					expand: true,
					cwd: '<%= dirs.css %>/',
					src: [
						'*.less',
						'!mixins.less'
					],
					dest: '<%= dirs.css %>/',
					ext: '.min.css'
				}]
			}
		},

		// Minify all .css files.
		cssmin: {
			minify: {
				expand: true,
				cwd: '<%= dirs.css %>/',
				src: ['*.css'],
				dest: '<%= dirs.css %>/',
				ext: '.css'
			}
		},

		// Minify .js files.
		uglify: {
			options: {
				preserveComments: 'some'
			},
			jsfiles: {
				files: [{
					expand: true,
					cwd: '<%= dirs.js %>/',
					src: [
						'*.js',
						'!*.min.js',
						'!Gruntfile.js',
					],
					dest: '<%= dirs.js %>/',
					ext: '.min.js'
				}]
			}
		},

		// Watch changes for assets
		watch: {
			less: {
				files: ['<%= dirs.css %>/*.less'],
				tasks: ['less', 'cssmin'],
			},
			js: {
				files: [
					'<%= dirs.js %>/*js',
					'!<%= dirs.js %>/*.min.js'
				],
				tasks: ['uglify']
			}
		},

		// Generate POT files.
		makepot: {
			options: {
				type: 'wp-plugin',
				domainPath: 'languages',
				potHeaders: {
					'report-msgid-bugs-to': 'https://www.download-monitor.com/',
					'language-team': 'LANGUAGE <EMAIL@ADDRESS>'
				}
			},
			frontend: {
				options: {
					potFilename: 'dlm-cors.pot',
					exclude: [
						'node_modules/.*',
						'tests/.*',
						'tmp/.*'
					],
					processPot: function (pot) {
						return pot;
					}
				}
			}
		},

		po2mo: {
			files: {
				src: '<%= dirs.lang %>/*.po',
				expand: true
			}
		},

		compress: {
			build: {
				options: {
					pretty: true,                           // Pretty print file sizes when logging.
					archive: '<%= pkg.name %>-<%= pkg.version %>.zip'
				},
				expand: true,
				cwd: '',
				src: [ 
				'**',
				'!node_modules/**',
				'!.github/**',
				'!.git/**',
				'!build/**',
				'!readme.md',
				'!README.md',
				'!phpcs.ruleset.xml',
				'!package-lock.json',
				'!svn-ignore.txt',
				'!Gruntfile.js',
				'!package.json',
				'!composer.json',
				'!composer.lock',
				'!postcss.config.js',
				'!webpack.config.js',
				'!set_tags.sh',
				'!*.zip',
				'!old/**',
				'!bin/**',
				'!tests/**',
				'!codeception.dist.yml',
				'!regconfig.json',
				'!nbproject/**' ],
				dest: '<%= pkg.name %>'
			}
		},

	});

	// Load NPM tasks to be used here
	grunt.loadNpmTasks('grunt-contrib-less');
	grunt.loadNpmTasks('grunt-contrib-cssmin');
	grunt.loadNpmTasks('grunt-contrib-uglify-es');
	grunt.loadNpmTasks('grunt-contrib-watch');
	grunt.loadNpmTasks('grunt-wp-i18n');
	grunt.loadNpmTasks('grunt-checktextdomain');
	grunt.loadNpmTasks('grunt-po2mo');
	grunt.loadNpmTasks('grunt-contrib-compress');

	// Register tasks
	grunt.registerTask('default', [
		'less',
		'cssmin',
		'uglify'
	]);

	// Just an alias for pot file generation
	grunt.registerTask('pot', [
		'makepot'
	]);

	// Build task
	grunt.registerTask( 'build-archive', [	
		'compress:build',
	] );

};