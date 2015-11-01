(function(){
	'use strict';

	angular.module('tracker.directives')
		.directive('news', function () {
			return {
				restrict: 'E',
				templateUrl: '../app/components/news.directive.html',
				scope: {
					news: '=',
					myself: '='
				},
				controller: function ($scope, NewsResource, ConfirmDialog, ErrorDialog, $state, $uibModal) {
					$scope.edit = function (news) {
						var modalInstance = $uibModal.open({
							animation: true,
							templateUrl: '../app/admin/dialogs/news-admin-dialog.html',
							controller: 'NewsAdminController',
							size: 'md',
							resolve: {
								news: function () {
									return news;
								}
							}
						});

						modalInstance.result.then(function () {
							$state.reload();
						});
					};

					$scope.delete = function (news) {
						var dialog = ConfirmDialog('Radera nyhet', 'Vill du radera nyheten?');
						dialog.then(function () {
							NewsResource.delete({id: news.id}).$promise
								.then(function () {
									$state.reload();
								})
								.catch(function (error) {
									ErrorDialog.display(error.data);
								});
						});
					};
				}
			};

		});
})();