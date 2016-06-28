(function(){
	'use strict';

	angular
		.module('app.shared')
		.component('poll', {
			bindings: {
				poll: '=',
				vote: '&',
				onlyresult: '@'
			},
			templateUrl: '../app/polls/poll.component.template.html',
			controller: PollController,
			controllerAs: 'vm'
		});

	function PollController(PollsResource, ConfirmDialog, ErrorDialog, $state, $uibModal, authService, $translate) {

		this.currentUser = authService.getUser();

		this.edit = function () {
			var modalInstance = $uibModal.open({
				animation: true,
				templateUrl: '../app/admin/dialogs/poll-admin-dialog.template.html',
				controller: 'PollAdminDialogController as vm',
				size: 'md',
				resolve: {
					poll: () => this.poll
				}
			});

			modalInstance.result
				.then((poll) => {
					return PollsResource.Polls.update(poll).$promise;
				})
				.then(() => {
					$state.reload();
				});
		};

		this.delete = function () {
			ConfirmDialog($translate.instant('POLL.DELETE_TITLE'), $translate.instant('POLL.DELETE_BODY'))
				.then(() => {
					return PollsResource.Polls.delete({id: this.poll.id}).$promise;
				})
				.then(() => {
					$state.reload();
				})
				.catch((error) => {
					if (error) {
						ErrorDialog.display(error.data);
					}
				});
		};

	}

})();
