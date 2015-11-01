(function(){
	'use strict';

	angular
		.module('tracker.controllers')
		.controller('UploadController', function ($scope, $state, $stateParams, user, userClasses, DateService, categories, SweTvResource, UploadService, MovieDataResource) {
			$scope.categories = categories;
			$scope.tvChannels = SweTvResource.Channels.query();

			var arr = [];
			for (var i = 0; i < 21; i++) {
				var d = new Date();
				arr.push(DateService.getYMD(d.getTime()/1000 - i*86400 ));
			}
			$scope.tvDates = arr;

			$scope.settings = {
				reqid: user.class >= userClasses.UPLOADER.id ? 0 : 1,
				anonymousUpload: user['anonym'] === 'yes' ? 1 : 0,
				category: 1,
				p2p: 0,
				swesub: 0,
				nfo: '',
				progress: 0,
				imdbId: 0,
				imdbUrl: '',
				programTitle: '',
				programDate: arr[0],
				programTime: '12:00',
			};

			if ($stateParams.requestId) {
				$scope.settings.reqid = $stateParams.requestId;
				$scope.settings.request = $stateParams.requestName;
			}

			$scope.$watch('settings.nfo', function(newTxt, oldTxt) {
				if (oldTxt.length + 10 > newTxt.length) {
					return;
				}
				$scope.settings.nfo = UploadService.stripAscii($scope.settings.nfo);
				if ($scope.settings.imdbUrl === '') {
					$scope.settings.imdbUrl = UploadService.findImdbUrl($scope.settings.nfo);
					$scope.fetchImdbInfo();
				}
		 	});

		 	$scope.categoryChanged = function () {
		 		if ($scope.settings.category == categories.TV_SWE.id) {
					guessSweTv();
				}
		 	};

		 	var guessSweTv = function () {
		 		$scope.submitDisabled = true;
				SweTvResource.Guess.get({name: $scope.settings.file.name}).$promise
					.then(function (result) {
						if (result.channel !== null && result.program !== null) {
							$scope.settings.program = result.program;
							if ($scope.settings.channel != result.channel) {
								$scope.settings.channel = result.channel;
								$scope.updatePrograms();
							}
						} else {
							$scope.settings.channel = 0;
							$scope.tvPrograms = null;
						}
						$scope.submitDisabled = false;
					});
		 	};

		 	$scope.$watch('settings.file', function (file) {
		 		if (file) {
					$scope.settings.category = UploadService.guessCategoryFromName(file.name);
					if ($scope.settings.category == categories.TV_SWE.id) {
						guessSweTv();
					}
				}
		 	});

		 	$scope.fetchImdbInfo = function () {
		 		if ($scope.settings.imdbUrl.length > 1) {
		 			$scope.submitDisabled = true;
		 			var match = $scope.settings.imdbUrl.match(/\/(tt[0-9]+)(\/|$)/);
		 			if (match && match.length > 1)  {
			 			var imdbId = match[1];
			 			MovieDataResource.Imdb.get({id: imdbId}, function (imdb) {
							$scope.settings.imdbInfo = imdb['title'] + ' (' + imdb['year'] +')';
							$scope.settings.imdbId = imdb['id'];
							$scope.submitDisabled = false;
						}, function (error) {
							$scope.settings.imdbInfo = 'Error: ' + error;
							$scope.submitDisabled = false;
						});
			 		}
		 		}
		 	};

		 	$scope.updatePrograms = function () {
		 		$scope.tvPrograms = null;
				SweTvResource.Programs.query({id:$scope.settings.channel}).$promise
					.then(function (programs) {
						programs = Array.prototype.slice.call(programs);
						$scope.tvPrograms = UploadService.generateProgramSelectList(programs);
					});
		 	};

			$scope.uploadFile = function () {
				$scope.closeAlert();
				$scope.submitDisabled = true;

 				var params = {
					url: '/api/v1/torrents/upload',
					data: {
						reqid:				$scope.settings.reqid,
						category:			$scope.settings.category,
						anonymousUpload:	$scope.settings.anonymousUpload,
						file:				$scope.settings.file,
						nfo:				$scope.settings.nfo,
						imdbId:				$scope.settings.imdbId,
						program:			$scope.settings.program || 0,
						channel:			$scope.settings.channel || 0,
						p2p:				$scope.settings.p2p || 0,
						swesub:				$scope.settings.swesub || 0,
						programTitle:		$scope.settings.programTitle,
						programDate:		$scope.settings.programDate + ' ' + $scope.settings.programTime,
					}
				};

				UploadService.setOnProgress(function (progress) {
					$scope.$apply(function () {
						$scope.settings.progress = progress;
					});
				});

				UploadService.uploadFile(params)
					.then(function (response) {
						$state.go('torrent', {id: response.id, name: response.name, uploaded: true});
					}, function (error) {
						$scope.addAlert({ type: 'danger', msg: error });
						$scope.settings.progress = 0;
						$scope.submitDisabled = false;
					});
			};

			$scope.addAlert = function (obj) {
				$scope.alert = obj;
			};

			$scope.closeAlert = function () {
				$scope.alert = null;
			};

		});
})();