(function(){
	'use strict';

	angular.module('tracker.controllers')
		.controller('NewTopicController', function ($scope, ForumResource, $state, $stateParams, user, AuthService) {
			$scope.$parent.activateTopicsView();
			$scope.postStatus = 0;

			var isoDate = new Date(AuthService.getServerTime()).toISOString();
			var currentDatetime = isoDate.replace(/T/g, ' ').replace(/Z/g, '');

			$scope.post = {
				id: '?',
				added: currentDatetime,
				editedat: '0000-00-00 00:00:00',
				user: user
			};

			$scope.CreateTopic = function () {
				$scope.closeAlert();
				$scope.postStatus = 1;
				ForumResource.Topics.save({
					forumid: $stateParams.id,
					subject: $scope.post.subject,
					sub: $scope.post.sub,
					body: $scope.post.body,
				}, function (result) {
					$state.go('forum.topic', {forumid: $stateParams.id, id: result.topicId});
				}, function (error) {
					$scope.postStatus = 0;
					if (error.data) {
						$scope.addAlert({ type: 'danger', msg: error.data });
					} else {
						$scope.addAlert({ type: 'danger', msg: 'Ett fel inträffade' });
					}
				});
			};

			$scope.addAlert = function (obj) {
				$scope.alert = obj;
			};

			$scope.closeAlert = function() {
				$scope.alert = null;
			};

		});
})();