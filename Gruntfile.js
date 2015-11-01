'use strict';

module.exports = function(grunt) {

	grunt.initConfig({
		pkg: grunt.file.readJSON('package.json'),
		uglify: {
			dist: {
				files: [
					{'dist/app-dist.js': ['dist/app-dist.js']},
					{'dist/app-admin-dist.js': ['dist/app-admin-dist.js']},
				],
				options: {
					mangle: true,
					quoteStyle: 0
				}
			}
		},
		html2js: {
			dist: {
				files: [
					{src: ['app/**/*.html', '!app/admin/**'], dest: 'dist/app-templates.js'},
					{src: ['app/admin/**/*.html'], dest: 'dist/app-admin-templates.js'}
				]
			},
			options: {
				module: 'tracker.templates',
				existingModule: true,
				singleModule: true,
				htmlmin: {
					collapseBooleanAttributes: true,
					collapseWhitespace: true,
					conservativeCollapse: true,
					removeAttributeQuotes: true,
					removeComments: true,
					removeEmptyAttributes: true,
					removeRedundantAttributes: true,
					removeScriptTypeAttributes: true,
					removeStyleLinkTypeAttributes: true
				},
  				watch: true
			},
		},
		concat: {
			dist: {
				files: [
					{src: ['app/app.module.js', 'app/**/*.js', '!app/admin/**'], dest: 'dist/app-dist.js'},
					{src: ['app/admin/**/*.js'], dest: 'dist/app-admin-dist.js'}
				]
			}
		},
		ngAnnotate: {
			options: {
				singleQuotes: true
			},
			app: {
				files: [
					{'dist/app-dist.js': ['dist/app-dist.js']},
					{'dist/app-admin-dist.js': ['dist/app-admin-dist.js']},
				],
			}
		},
		jshint: {
			all: ['app/**/*.js'],
			options: {
				'jshintrc': true
			}
		},
		watch: {
			dev: {
				files: ['app/**/*.js', 'app/**/*.html'],
				tasks: ['concat'],
				options: {
					debounceDelay: 100,
					atBegin: true
				},
			},
		}

	});

	grunt.loadNpmTasks('grunt-html2js');
	grunt.loadNpmTasks('grunt-contrib-jshint');
	grunt.loadNpmTasks('grunt-contrib-concat');
	grunt.loadNpmTasks('grunt-ng-annotate');
	grunt.loadNpmTasks('grunt-contrib-uglify');
	grunt.loadNpmTasks('grunt-contrib-watch');

	grunt.registerTask('dist', ['jshint', 'concat', 'ngAnnotate', 'html2js', 'uglify']);
	grunt.registerTask('dev', ['concat', 'html2js', 'watch']);
};