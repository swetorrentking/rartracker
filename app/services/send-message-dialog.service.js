(function(){
	'use strict';

	angular.module('tracker.services')
		.service('SendMessageDialog', function ($uibModal) {

			return function (message) {
				var modal = $uibModal.open({
					animation: true,
					templateUrl: '../app/dialogs/sendmessage-dialog.html',
					controller: 'SendmessageController',
					size: 'md',
					resolve: {
						message: function () {
							return message;
						}
					}
				});

				return modal.result;
			};

		});
})();