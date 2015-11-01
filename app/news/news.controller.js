(function(){
	'use strict';

	angular.module('tracker.controllers')
		.controller('NewsController', function ($scope, $uibModal, $state, AuthService, NewsResource) {

			NewsResource.query({limit: 999, markAsRead: 'true'}, function (data) {
				$scope.news = data;
				AuthService.readUnreadNews();
			});

			$scope.createNews = function () {
				var modalInstance = $uibModal.open({
					animation: true,
					templateUrl: '../app/admin/dialogs/news-admin-dialog.html',
					controller: 'NewsAdminController',
					size: 'md',
					resolve: {
						news: null
					}
				});

				modalInstance.result.then(function (result) {
					$state.go('forum.topic', {forumid: 1, id: result.topicId});
				});
			};

		});
})();