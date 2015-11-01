(function(){
	'use strict';

	angular.module('tracker.controllers')
		.controller('EditTorrentController', function ($scope, $stateParams, DateService, ErrorDialog, SweTvResource, MovieDataResource, categories, $state, TorrentsResource, UploadService) {
			
			$scope.tvChannels = SweTvResource.Channels.query();

			var arr = [];
			for (var i = 0; i < 21; i++) {
				var d = new Date();
				arr.push(DateService.getYMD(d.getTime()/1000 - i*86400 ));
			}
			$scope.tvDates = arr;

			$scope.categories = categories;

			TorrentsResource.Torrents.get({id: $stateParams.id}, function (torrent) {
				$scope.torrent = torrent;
				if ($scope.torrent.tv_kanalid > 0) {
					$scope.updatePrograms();
				}
				if ($scope.torrent.imdbid) {
					TorrentsResource.Related.query({id: $scope.torrent.imdbid}, function (torrents) {
						torrents = torrents.filter(function (torrent) { return torrent.id !== $scope.torrent.id; });
						$scope.relatedTorrents = torrents;
					});
				}
			}, function (error){
				$scope.notFoundMessage = error.data;
			});

			$scope.$watch('torrent.descr', function (newTxt, oldTxt) {
				if (!newTxt || oldTxt && oldTxt.length + 10 > newTxt.length) {
					return;
				}
				$scope.torrent.descr = UploadService.stripAscii($scope.torrent.descr);
		 	});

			$scope.updateTorrent = function () {
				TorrentsResource.Torrents.update({id: $scope.torrent.id}, $scope.torrent, function () {
					$state.go('torrent', {id: $scope.torrent.id, name: $scope.torrent.name});
				}, function (error) {
					ErrorDialog.display(error.data);
				});
			};

			$scope.deleteTorrent = function () {
				TorrentsResource.Torrents.remove({
					id: $scope.torrent.id,
					reason: $scope.deleteVars.reason,
					pmUploader: $scope.deleteVars.pmUploader,
					pmPeers: $scope.deleteVars.pmPeers,
					banRelease: $scope.deleteVars.banRelease,
					attachTorrentId: $scope.deleteVars.attachTorrentId,
					restoreRequest: $scope.deleteVars.restoreRequest,
				}, function () {
					$scope.notFoundMessage = 'Torrenten är nu raderad.';
				}, function (error) {
					ErrorDialog.display(error.data);
				});
			};

			$scope.fetchImdbInfo = function () {
		 		if ($scope.torrent.imdbUrl.length > 1) {
		 			$scope.submitDisabled = true;
		 			var imdbId = $scope.torrent.imdbUrl.match(/\/(tt[0-9]+)(\/|$)/)[1];
		 			MovieDataResource.Imdb.get({id: imdbId}, function (imdb) {
						$scope.torrent.imdbInfo = imdb['title'] + ' (' + imdb['year'] +')';
						$scope.torrent.imdbid = imdb['id'];
						$scope.submitDisabled = false;
					}, function (error) {
						$scope.torrent.imdbInfo = 'Error: ' + error;
						$scope.submitDisabled = false;
					});
		 		}
		 	};

			$scope.removeImdb = function () {
				$scope.torrent.imdbid = 0;
				$scope.torrent.imdbUrl = '';
				$scope.torrent.imdbInfo = '';
			};

			$scope.updatePrograms = function () {
		 		$scope.tvPrograms = null;
				SweTvResource.Programs.query({id:$scope.torrent.tv_kanalid}).$promise
					.then(function (programs) {
						programs = Array.prototype.slice.call(programs);
						programs = UploadService.generateProgramSelectList(programs);
						if (!programs.some(function (p) { return p.id == $scope.torrent.tv_programid; })) {
							programs.unshift({
								id: 2,
								program: DateService.getHI($scope.torrent.tv_klockslag) + ' - ' + $scope.torrent.tv_program,
							});
						}
						$scope.tvPrograms = programs;
					});
		 	};

		});
})();