// jshint ignore: start
var gulp = require('gulp'),
	uglify = require('gulp-uglify'),
	concat = require('gulp-concat'),
	templateCache = require('gulp-angular-templatecache'),
	htmlmin = require('gulp-htmlmin'),
	ngAnnotate = require('gulp-ng-annotate'),
	jshint = require('gulp-jshint'),
	babel = require('gulp-babel');

// Settings

var filePaths = {
	JS_FILES: ['app/**/*.module.js', 'app/**/*.js'],
	HTML_FILES: ['app/**/*.html'],
	OUTPUT_DEST: 'dist/',
	OUTPUT_JS_FILE: 'app-dist.js',
	OUTPUT_JS_TEMPLATES_FILE: 'app-templates.js'
}

var templateCacheSettings = {
	module: 'app.templates',
	moduleSystem: 'IIFE',
	standalone: true,
	root: '../app'
};

var htmlminSettings = {
	collapseWhitespace: true,
	conservativeCollapse: true,
	removeComments: true,
};

// Tasks

gulp.task('dist-js', ['lint'], function() {
	return gulp.src(filePaths.JS_FILES)
		.pipe(babel({ presets: ['es2015'] }))
		.pipe(ngAnnotate())
		.pipe(uglify())
		.pipe(concat(filePaths.OUTPUT_JS_FILE))
		.pipe(gulp.dest(filePaths.OUTPUT_DEST));
});

gulp.task('dist-html', function() {
	return gulp.src(filePaths.HTML_FILES)
		.pipe(htmlmin(htmlminSettings))
		.pipe(templateCache(templateCacheSettings))
		.pipe(concat(filePaths.OUTPUT_JS_TEMPLATES_FILE))
		.pipe(gulp.dest(filePaths.OUTPUT_DEST));
});

gulp.task('dev-js', function() {
	return gulp.src(filePaths.JS_FILES)
		.pipe(concat(filePaths.OUTPUT_JS_FILE))
		.pipe(babel({ presets: ['es2015'], compact: false }))
		.pipe(gulp.dest(filePaths.OUTPUT_DEST));
});

gulp.task('dev-html', function() {
	return gulp.src(filePaths.HTML_FILES)
		.pipe(templateCache(templateCacheSettings))
		.pipe(concat(filePaths.OUTPUT_JS_TEMPLATES_FILE))
		.pipe(gulp.dest(filePaths.OUTPUT_DEST));
});

gulp.task('lint', function() {
	return gulp.src(filePaths.JS_FILES)
		.pipe(jshint())
		.pipe(jshint.reporter('default'));
});

gulp.task('watch', ['dev-js', 'dev-html'], function() {
	gulp.watch(filePaths.JS_FILES, ['dev-js']);
	gulp.watch(filePaths.HTML_FILES, ['dev-html']);
});

gulp.task('dist', ['dist-js', 'dist-html']);