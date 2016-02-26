(function(){
	'use strict';

	angular
		.module('app.mailbox')
		.service('SendMessageDialog', SendMessageDialog);

	function SendMessageDialog($uibModal) {

		return function (message) {
			var modal = $uibModal.open({
				animation: true,
				templateUrl: '../app/mailbox/sendmessage-dialog.template.html',
				controller: 'SendmessageController',
				controllerAs: 'vm',
				backdrop: 'static',
				size: 'md',
				resolve: {
					message: () => message
				}
			});

			return modal.result;
		};

	}

})();