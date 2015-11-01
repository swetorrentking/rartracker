(function(){
	'use strict';

	angular
		.module('tracker.services')
		.service('UploadService', function ($rootScope, $q, categories, DateService) {
			this.onProgressFn = function () {};
			this.setOnProgress = function (fn) {
				this.onProgressFn = fn;
			};

			this.callOnProgress = function (progress) {
				this.onProgressFn(progress);
			};

			this.uploadFile = function (params) {
				var that = this;
				var def = $q.defer();

				var fd = new FormData();
				var xhr = new XMLHttpRequest();

				xhr.upload.addEventListener('progress', function(e) {
					if (e.lengthComputable) {
						var percentage = Math.round((e.loaded * 100) / e.total);
						that.callOnProgress(percentage);
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

				if (name.indexOf('ebook') > -1){
					return categories.EBOOKS.id;
				}
				if (name.indexOf('audiobook') > -1) {
					return categories.AUDIOBOOKS.id;
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
				} else {
					return '';
				}
			};

			this.generateProgramSelectList = function (programs) {
				var result = [];

				result.push(
					{
						id: 1,
						program: ' -- Fyll i ett program manuellt --'
					}
				);

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

					result.push(
						{
							id: programs[i].id,
							program: DateService.getHI(programs[i].datum) + ' - ' + programs[i].program
						}
					);
				}

				return result;
			};
		});
})();