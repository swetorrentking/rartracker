// jshint ignore: start
var gulp = require('gulp'),
	uglify = require('gulp-uglify'),
	concat = require('gulp-concat'),
	templateCache = require('gulp-angular-templatecache'),
	htmlmin = require('gulp-htmlmin'),
	ngAnnotate = require('gulp-ng-annotate'),
	jshint = require('gulp-jshint'),
	babel = require('gulp-babel'),
	copydir = require('copy-dir');

// Settings

var filePaths = {
	JS_FILES: ['app/**/*.module.js', 'app/**/*.js'],
	HTML_FILES: ['app/**/*.html'],
	JS_LIBS_FILES: [
		'bower_components/angular/angular.min.js',
		'bower_components/angular-cookies/angular-cookies.min.js',
		'bower_components/angular-resource/angular-resource.min.js',
		'bower_components/angular-sanitize/angular-sanitize.min.js',
		'bower_components/angular-ui-router/release/angular-ui-router.min.js',
		'bower_components/angular-bootstrap/ui-bootstrap-tpls.min.js',
		'bower_components/Chart.js/Chart.js',
		'bower_components/angular-chart.js/dist/angular-chart.min.js',
		'bower_components/angular-translate/angular-translate.min.js',
		'bower_components/angular-translate-loader-static-files/angular-translate-loader-static-files.min.js'
	],
	CSS_FILES: [
		'bower_components/bootstrap/dist/css/bootstrap.min.css',
		'bower_components/font-awesome/css/font-awesome.min.css',
		'bower_components/angular-chart.js/dist/angular-chart.min.css',
		'css/rartracker.css'
	],
	FONT_FOLDERS: [
		'bower_components/font-awesome/fonts',
		'bower_components/bootstrap/fonts'
	],
	OUTPUT_DEST: 'dist/',
	OUTPUT_JS_LIBS_FILE: 'libs.bundle.js',
	OUTPUT_CSS_FILE: 'styles.bundle.css',
	OUTPUT_JS_FILE: 'app.bundle.js',
	OUTPUT_JS_TEMPLATES_FILE: 'app.templates.bundle.js'
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
		.pipe(gulp.dest(filePaths.OUTPUT_DEST));
});

gulp.task('libs', function() {
	return gulp.src(filePaths.JS_LIBS_FILES)
		.pipe(concat(filePaths.OUTPUT_JS_LIBS_FILE))
		.pipe(gulp.dest(filePaths.OUTPUT_DEST));
});

gulp.task('css', function() {
	return gulp.src(filePaths.CSS_FILES)
		.pipe(concat(filePaths.OUTPUT_CSS_FILE))
		.pipe(gulp.dest(filePaths.OUTPUT_DEST));
});

gulp.task('copy-fonts', function() {
	filePaths.FONT_FOLDERS.forEach(function (folder) {
		copydir(folder, './fonts', function(err){
			if (err){ console.log(err); }
		});
	});
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

gulp.task('watch', ['dev-js', 'dev-html', 'libs', 'css', 'copy-fonts'], function() {
	gulp.watch(filePaths.JS_FILES, ['dev-js']);
	gulp.watch(filePaths.HTML_FILES, ['dev-html']);
	gulp.watch(filePaths.CSS_FILES, ['css']);
});

gulp.task('dist', ['dist-js', 'dist-html', 'libs', 'css', 'copy-fonts']);
