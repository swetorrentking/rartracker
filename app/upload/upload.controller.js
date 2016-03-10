(function(){
	'use strict';

	angular
		.module('app.shared')
		.controller('UploadController', UploadController);

	function UploadController($state, $stateParams, user, userClasses, categories, SweTvResource, uploadService, MovieDataResource) {

		this.currentUser = user;
		this.categories = categories;
		this.tvChannels = SweTvResource.Channels.query();
		this.tvDates = uploadService.getSweTvDates();

		this.settings = {
			reqid: user.class >= userClasses.UPLOADER.id ? 0 : 1,
			anonymousUpload: user['anonym'] === 'yes' ? 1 : 0,
			category: 1,
			p2p: 0,
			swesub: 0,
			sweaudio: 0,
			nfo: '',
			progress: 0,
			imdbId: 0,
			imdbUrl: '',
			programTitle: '',
			programDate: this.tvDates[0],
			programTime: '12:00',
			movieData: null,
		};

		if ($stateParams.requestId) {
			this.settings.reqid = $stateParams.requestId;
			this.settings.request = $stateParams.requestName;
		}

		this.stripNfo = function () {
			this.settings.nfo = uploadService.stripAscii(this.settings.nfo);
			if (this.settings.imdbUrl === '') {
				var imdbUrl = uploadService.findImdbUrl(this.settings.nfo);
				if (imdbUrl) {
					this.settings.imdbUrl = imdbUrl;
					this.fetchImdbInfo();
				} else {
					this.guessImdbFromName();
				}
			}
	 	};

		this.removeImdb = function () {
			this.settings.imdbInfo = '';
			this.settings.imdbId = null;
			this.settings.movieData = null;
		};

		this.isMoveOrTvSeries = function () {
			return [1,2,3,4,5,6,7,13,15].some(catId => catId === this.settings.category);
		};

		this.guessImdbFromName = function () {
			if (!this.settings.file || !this.isMoveOrTvSeries) {
				return;
			}
			this.submitDisabled = true;
			uploadService.$guessImdbFromName(this.settings.file.name)
				.then((imdb) => {
					if (imdb && imdb.id) {
						this.settings.imdbInfo = imdb['title'] + ' (' + imdb['year'] +')';
						this.settings.imdbId = imdb['id'];
						this.settings.movieData = imdb;
					}
					this.submitDisabled = false;
				});
		};

	 	this.categoryChanged = function () {
	 		if (this.settings.category == categories.TV_SWE.id) {
				this.guessSweTv();
			}
	 	};

	 	this.guessSweTv = function () {
			if (!this.settings.file) {
				return;
			}
	 		this.submitDisabled = true;
			SweTvResource.Guess.get({name: this.settings.file.name}).$promise
				.then((result) => {
					if (result.channel !== null && result.program !== null) {
						this.settings.program = result.program;
						if (this.settings.channel != result.channel) {
							this.settings.channel = result.channel;
							this.updatePrograms();
						}
					} else {
						this.settings.channel = 0;
						this.tvPrograms = null;
					}
					this.submitDisabled = false;
				});
	 	};

	 	this.fileChanged = function () {
	 		this.settings.category = uploadService.guessCategoryFromName(this.settings.file.name);
			if (this.settings.category !== 8 && this.settings.file.name.toLowerCase().indexOf('swedish') > -1) {
				this.settings.sweaudio = 1;
			} else {
				this.settings.sweaudio = 0;
			}
			if (this.settings.category == categories.TV_SWE.id) {
				this.guessSweTv();
			}
			/* Set/changed file and NFO already present */
			if (this.settings.nfo.length > 2 && !this.settings.imdbId) {
				this.guessImdbFromName();
			}
		};

	 	this.fetchImdbInfo = function () {
	 		if (this.settings.imdbUrl.length > 1) {
	 			this.submitDisabled = true;
	 			var match = this.settings.imdbUrl.match(/\/(tt[0-9]+)(\/|$)/);
	 			if (match && match.length > 1)  {
		 			var imdbId = match[1];
		 			MovieDataResource.Imdb.get({id: imdbId}, (imdb) => {
						this.settings.imdbInfo = imdb['title'] + ' (' + imdb['year'] +')';
						this.settings.imdbId = imdb['id'];
						this.submitDisabled = false;
						this.settings.movieData = imdb;
					}, (error) => {
						this.settings.imdbInfo = 'Error: ' + error;
						this.submitDisabled = false;
					});
		 		}
	 		}
	 	};

	 	this.updatePrograms = function () {
	 		this.tvPrograms = null;
			SweTvResource.Programs.query({id:this.settings.channel}).$promise
				.then((programs) => {
					programs = Array.prototype.slice.call(programs);
					this.tvPrograms = uploadService.generateProgramSelectList(programs);
				});
	 	};

		this.uploadFile = function () {
			this.closeAlert();
			this.submitDisabled = true;

				var params = {
				url: '/api/v1/torrents/upload',
				data: {
					reqid:				this.settings.reqid,
					category:			this.settings.category,
					anonymousUpload:	this.settings.anonymousUpload,
					file:				this.settings.file,
					nfo:				this.settings.nfo,
					imdbId:				this.settings.imdbId,
					program:			this.settings.program || 0,
					channel:			this.settings.channel || 0,
					p2p:				this.settings.p2p || 0,
					swesub:				this.settings.swesub || 0,
					sweaudio:			this.settings.sweaudio || 0,
					programTitle:		this.settings.programTitle,
					programDate:		this.settings.programDate + ' ' + this.settings.programTime,
				}
			};

			uploadService.setOnProgress((progress) => {
				this.settings.progress = progress;
			});

			uploadService.uploadFile(params)
				.then((response) => {
					$state.go('torrent', {id: response.id, name: response.name, uploaded: true});
				}, (error) => {
					this.addAlert({ type: 'danger', msg: error });
					this.settings.progress = 0;
					this.submitDisabled = false;
				});
		};

		this.addAlert = function (obj) {
			this.alert = obj;
		};

		this.closeAlert = function () {
			this.alert = null;
		};

	}

})();
