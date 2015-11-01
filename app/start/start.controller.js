(function(){
	'use strict';

	angular.module('tracker.controllers')
		.controller('StartController', function ($scope, $timeout, $stateParams, $window, StartTorrentsResource, NewsResource, StatisticsResource, PollsResource) {
			var numberOfNewsItems = 2;

			function fetchPoll() {
				PollsResource.Latest.get({}, function (data) {
					$scope.poll = data;
				});
			} 
	
			StartTorrentsResource.query({}, function (data) {
				$scope.highligtedTorrents = data;
			});

			NewsResource.query({limit: numberOfNewsItems}, function (data) {
				$scope.news = data;
			});

			StatisticsResource.get({id: 'start'}, function (data) {
				$scope.stats = data;
			});

			$scope.pollAnswer = '';

			$scope.vote = function () {
				PollsResource.Votes.save({id: $scope.poll.id, choise: $scope.poll.myChoise}, function () {
					fetchPoll();
				});
			};

			fetchPoll();
		});
})();