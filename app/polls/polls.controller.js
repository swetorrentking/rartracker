(function(){
	'use strict';

	angular.module('tracker.controllers')
		.controller('PollsController', function ($scope, $uibModal, $state, PollsResource) {

			PollsResource.Polls.query({}, function (data) {
				$scope.polls = data;
			});

			$scope.Create = function () {
				var modalInstance = $uibModal.open({
					animation: true,
					templateUrl: '../app/admin/dialogs/poll-admin-dialog.html',
					controller: 'PollAdminDialogController',
					size: 'md',
					resolve: {
						poll: function () {
							return {
								question: '',
								option0: '',
								option1: '',
								option2: '',
								option3: '',
								option4: '',
								option5: '',
								option6: '',
								option7: '',
								option8: '',
								option9: '',
								option10: '',
								option11: '',
								option12: '',
								option13: '',
								option14: '',
								option15: '',
								option16: '',
								option17: '',
								option18: '',
								option19: ''
							};
						}
					}
				});

				modalInstance.result
					.then(function (poll) {
						return PollsResource.Polls.save(poll).$promise;
					})
					.then(function () {
						$state.go('start');
					});
			};

		});
})();