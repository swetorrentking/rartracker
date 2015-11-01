(function(){
	'use strict';

	angular.module('tracker.controllers')
		.controller('EditRequestController', function ($scope, $state, $uibModal, $stateParams, categories, MovieDataResource, RequestsResource) {
			$scope.loaded = false;
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

			RequestsResource.Requests.get({ id: $stateParams.id }).$promise
				.then(function (data) {
					$scope.requestParams.category = data.request.type;
					$scope.requestParams.comment = data.request.comment;
					$scope.requestParams.season = data.request.season;
					$scope.requestParams.imdbId = data.request.imdbid;
					$scope.requestParams.customName = data.request.request;
					$scope.requestParams.imdbInfo = data.request.request;
					for (var i = 0; i < data.movieData['seasoncount']; i++) {
						$scope.requestParams.seasons.push(i+1);
					}
					$scope.loaded = true;
					$scope.changedCategory();
				})
				.catch(function (error) {
					$scope.notFoundMessage = error.data;
				});

			$scope.addRequest = function () {
				$scope.closeAlert();
				RequestsResource.Requests.update({id: $stateParams.id}, $scope.requestParams).$promise
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
		 		if ($scope.requestParams.imdbUrl.length > 1) {
		 			var match = $scope.requestParams.imdbUrl.match(/\/(tt[0-9]+)(\/|$)/);
		 			if (match && match.length > 1)  {
		 				var imdbId = match[1];
			 			MovieDataResource.Imdb.get({id: imdbId}, function (imdb) {
							$scope.requestParams.imdbInfo = imdb['title'] + ' (' + imdb['year'] +')';
							$scope.requestParams.imdbId = imdb['id'];
							$scope.requestParams.seasons = [];
							for (var i = 0; i < imdb['seasoncount']; i++) {
								$scope.requestParams.seasons.push(i+1);
							}
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