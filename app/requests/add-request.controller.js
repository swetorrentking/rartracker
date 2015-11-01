(function(){
	'use strict';

	angular.module('tracker.controllers')
		.controller('AddRequestController', function ($scope, $state, $uibModal, categories, TorrentsResource, MovieDataResource, RequestsResource) { 

			$scope.categories = categories;
			$scope.noimdb = false;
			var imdbLessCategories = [
				categories.TV_SWE.id,
				categories.AUDIOBOOKS.id,
				categories.EBOOKS.id,
				categories.EPAPERS.id,
				categories.MUSIC.id,
			];

			$scope.requestParams = {
				category: 1,
				imdbUrl: '',
				imdbId: 0,
				season: 0,
				seasons: [],
				comment: '',
			};

			var fetchRelatedTorrents = function (id) {
				TorrentsResource.Related.query({id: id}, function (torrents) {
					$scope.relatedTorrents = torrents;
				});
			};

			$scope.addRequest = function () {
				$scope.closeAlert();
				RequestsResource.Requests.save({}, $scope.requestParams).$promise
					.then(function (response) {
						$state.go('requests.request', {id: response.id, name: response.name});
					})
					.catch(function (error) {
						$scope.addAlert({ type: 'danger', msg: error.data });
					});
			};

			$scope.changedCategory = function () {
				if (imdbLessCategories.indexOf($scope.requestParams.category) > -1) {
					$scope.noimdb = true;
				} else {
					$scope.noimdb = false;
				}
			};

			$scope.fetchImdbInfo = function () {
				$scope.closeAlert();
		 		if ($scope.requestParams.imdbUrl.length > 1) {
		 			var match = $scope.requestParams.imdbUrl.match(/\/(tt[0-9]+)(\/|$)/);
		 			if (match && match.length > 1)  {
		 				var imdbId = match[1];
			 			MovieDataResource.Imdb.get({id: imdbId}, function (imdb) {
							$scope.requestParams.imdbInfo = imdb['title'] + ' (' + imdb['year'] +')';
							$scope.requestParams.imdbId = imdb['id'];
							for (var i = 0; i < imdb['seasoncount']; i++) {
								$scope.requestParams.seasons.push(i+1);
							}
							fetchRelatedTorrents(imdb['id']);
						}, function (error) {
							$scope.requestParams.imdbInfo = 'Error: ' + error;
						});
			 		}
		 		}
		 	};

		 	$scope.addAlert = function (obj) {
				$scope.alert = obj;
			};

			$scope.closeAlert = function () {
				$scope.alert = null;
			};

		});
})();