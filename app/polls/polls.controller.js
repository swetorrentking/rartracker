(function(){
	'use strict';

	angular
		.module('app.shared')
		.controller('PollsController', PollsController);

	function PollsController($state, $stateParams, $uibModal, PollsResource, user) {

		this.currentPage = $stateParams.page;
		this.itemsPerPage = 10;
		this.currentUser = user;

		this.getPolls = function () {
			$state.go('.', { page: this.currentPage }, { notify: false });
			var index = this.currentPage * this.itemsPerPage - this.itemsPerPage;
			PollsResource.Polls.query({
				'limit': this.itemsPerPage,
				'index': index,
			}, (data, responseHeaders) => {
				var headers = responseHeaders();
				this.totalItems = headers['x-total-count'];
				this.polls = data;
				if (!this.hasLoaded) {
					this.currentPage = $stateParams.page;
					this.hasLoaded = true;
				}
			});
		};

		this.create = function () {
			var modalInstance = $uibModal.open({
				animation: true,
				templateUrl: '../app/admin/dialogs/poll-admin-dialog.template.html',
				controller: 'PollAdminDialogController as vm',
				size: 'md',
				resolve: {
					poll: () => {
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
				.then((poll) => {
					return PollsResource.Polls.save(poll).$promise;
				})
				.then(() => {
					$state.go('start');
				});
		};

		this.getPolls();

	}

})();
