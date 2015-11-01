(function(){
	'use strict';

	angular.module('tracker.directives')
		.directive('poll', function () {
			return {
				restrict: 'E',
				templateUrl: '../app/components/poll.directive.html',
				scope: {
					poll: '=',
					myself: '=',
					vote: '&'
				},
				link: function(scope, element, attributes){
					scope.onlyresult = scope.$eval(attributes.onlyresult) === true ? true : false;
				},
				controller: function ($scope, PollsResource, ConfirmDialog, ErrorDialog, $state, $uibModal) {
					$scope.edit = function () {
						var modalInstance = $uibModal.open({
							animation: true,
							templateUrl: '../app/admin/dialogs/poll-admin-dialog.html',
							controller: 'PollAdminDialogController',
							size: 'md',
							resolve: {
								poll: function () {
									return $scope.poll;
								}
							}
						});

						modalInstance.result.then(function (poll) {
							PollsResource.Polls.update(poll).$promise
								.then(function () {
									$state.reload();
								});
						});
					};

					$scope.delete = function () {
						var dialog = ConfirmDialog('Radera omröstning', 'Vill du radera omröstningen?');
						dialog.then(function () {
							PollsResource.Polls.delete({id: $scope.poll.id}).$promise
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