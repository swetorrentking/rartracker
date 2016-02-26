(function(){
	'use strict';

	angular
		.module('app.admin')
		.controller('PollAdminDialogController', PollAdminDialogController);

	function PollAdminDialogController($uibModalInstance, poll) {
		this.poll = poll;

		this.create = function () {
			$uibModalInstance.close(this.poll);
		};

		this.cancel = function () {
			$uibModalInstance.dismiss();
		};

	}
})();
