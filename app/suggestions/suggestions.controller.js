(function(){
	'use strict';

	angular.module('tracker.controllers')
		.controller('SuggestionsController', function ($scope, $uibModal, $state, SuggestionsResource) {
			$scope.itemsPerPage = 50;
			$scope.forumId = 25;
			$scope.searchText = '';

			$scope.currentPage = 'hot';

			$scope.voteOptions = {
				0: '---',
				1: 'Färdigt',
				2: 'Godkänt',
				3: 'Nekat',
				4: 'Ingen åtgärd'
			};

			var getSuggestions = function () {
				$scope.suggestions = null;

				SuggestionsResource.Suggest.query({
					'limit': $scope.itemsPerPage,
					'view': $scope.currentPage,
				}, function (data) {
					$scope.suggestions = data;
				});
			};

			$scope.pageChanged = function () {
				getSuggestions();
			};

			$scope.doSearch = function (){
				getSuggestions();
			};

			$scope.switchView = function (viewName) {
				$scope.currentPage = viewName;
				getSuggestions();
			};

			$scope.voteUp = function (suggest) {
				SuggestionsResource.Votes.save({
					id: suggest.id,
					direction: 'up'
				}, function (data) {
					suggest.votes = data.numVotes;
				});
			};

			$scope.voteDown = function (suggest) {
				SuggestionsResource.Votes.save({
					id: suggest.id,
					direction: 'down'
				}, function (data) {
					suggest.votes = data.numVotes;
				});
			};

			$scope.createSuggestion = function () {
				var modalInstance = $uibModal.open({
					animation: true,
					templateUrl: '../app/dialogs/create-suggestion-dialog.html',
					controller: 'CreateSuggestionController',
					size: 'md'
				});

				modalInstance.result.then(function (result) {
					$state.go('forum.topic', {forumid: $scope.forumId, id: result.topicId});
				});
			};

			$scope.updateStatus = function (suggest) {
				SuggestionsResource.Suggest.update(suggest);
			};

			$scope.convertToInt = function(id){
				return parseInt(id, 10);
			};

			getSuggestions();
		});
})();