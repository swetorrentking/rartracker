(function(){
	'use strict';

	angular
		.module('app.shared')
		.service('uploadService', UploadService);

	function UploadService($rootScope, $q, $translate, categories, DateService, MovieDataResource) {

		this.onProgressFn = function () {};
		this.setOnProgress = function (fn) {
			this.onProgressFn = fn;
		};

		this.callOnProgress = function (progress) {
			$rootScope.$apply(() => {
				this.onProgressFn(progress);
			});
		};

		this.uploadFile = function (params) {
			var def = $q.defer();

			var fd = new FormData();
			var xhr = new XMLHttpRequest();

			xhr.upload.addEventListener('progress', (e) => {
				if (e.lengthComputable) {
					var percentage = Math.round((e.loaded * 100) / e.total);
					this.callOnProgress(percentage);
				}
			}, false);

			xhr.addEventListener('load', function () {
				if (xhr.status === 200) {
					def.resolve(angular.fromJson(xhr.responseText));
				} else {
					def.reject(xhr.responseText);
				}
			});

			xhr.addEventListener('error', function () {
				def.reject();
			});

			xhr.upload.addEventListener('error', function () {
				def.reject();
			}, false);

			for (var d in params.data) {
				fd.append(d, params.data[d]);
			}

			xhr.open('POST', params.url, true);
			xhr.send(fd);
			return def.promise;
		};

		this.stripAscii = function (str) {
			str = str.replace(/[^A-zåäö0-9.:\-_\/\\\n\s]/g, '').trim();
			var splitted = str.split('\n');
			var i = splitted.length;
			while (i--) {
				if (!splitted[i] && !splitted[i-1] || splitted[i].trim().length === 0 && splitted[i-1] && splitted[i-1].trim().length === 0) {
					splitted.splice(i, 1); // remove double 'new lines'
				} else if (splitted[i]) {
					splitted[i] = splitted[i].trim();
				}
			}
			return splitted.join('\n');
		};

		this.guessCategoryFromName = function (name) {
			name = name.toLowerCase();

			if (name.indexOf('.4k.') > -1 || name.indexOf('2160p') > -1){
				return categories.MOVIE_4K.id;
			}
			if (name.indexOf('ebook') > -1){
				return categories.EBOOKS.id;
			}
			if (name.indexOf('subpack') > -1){
				return categories.SUBPACK.id;
			}
			if (name.indexOf('audiobook') > -1) {
				return categories.AUDIOBOOKS.id;
			}
			if (name.indexOf('x264') == -1 && name.indexOf('bluray') > -1){
				return categories.BLURAY.id;
			}
			if (name.indexOf('.pal.') > -1) {

				if (name.indexOf('custom') > -1) {
					return categories.DVDR_CUSTOM.id;
				}
				if (name.match(/.s[0-9]/)) {
					return categories.DVDR_TV.id;
				}

				return categories.DVDR_PAL.id;
			}
			if ((name.indexOf('pdtv') > -1 || name.indexOf('xvid') > -1 || name.indexOf('webrip') > -1 || name.indexOf('hdtv') > -1) && name.indexOf('swedish')  > -1) {
				return categories.TV_SWE.id;
			}
			if (name.match(/.s[0-9]/) || name.indexOf('hdtv') > -1) {

				if (name.indexOf('720p') > -1) {
					return categories.TV_720P.id;
				}

				if (name.indexOf('1080p') > -1) {
					return categories.TV_1080P.id;
				}

			} else {

				if (name.indexOf('720p') > -1) {
					return categories.MOVIE_720P.id;
				}

				if (name.indexOf('1080p') > -1) {
					return categories.MOVIE_1080P.id;
				}

			}

			return categories.MUSIC.id;
		};

		this.findImdbUrl = function (txt) {
			var match = txt.match(/(http:\/\/.*\/title\/.*)(\/|\n|$)/);
			if (match && match[1]) {
				return match[1];
			}
		};

		this.generateProgramSelectList = function (programs) {
			var result = [];

			result.push({
				id: 1,
				program: $translate.instant('TORRENTS.ENTER_TV_MANUALLY')
			});

			var lastDate = '';

			for (var i = 0, len = programs.length; i < len; i++) {
				var date = DateService.getYMD(programs[i].datum);
				if (lastDate != date) {
					result.push(
						{
							id: 0,
							program: ''
						},
						{
							id: 0,
							program: DateService.getWeekDay(programs[i].datum) + ' (' + date + ')'
						}
					);
					lastDate = date;
				}

				result.push({
					id: programs[i].id,
					program: DateService.getHI(programs[i].datum) + ' - ' + programs[i].program
				});
			}

			return result;
		};

		this.getSweTvDates = function () {
			var arr = [];
			for (var i = 0; i < 21; i++) {
				var d = new Date();
				arr.push(DateService.getYMD(d.getTime()/1000 - i*86400 ));
			}
			return arr;
		};

		this.$guessImdbFromName = function (name) {
			return MovieDataResource.Guess.get({ name: name }).$promise;
		};

	}
})();
