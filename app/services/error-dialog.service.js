(function(){
	'use strict';

	angular.module('tracker.services')
		.service('ErrorDialog', function ($uibModal) {

			this.display = function (body) {
				$uibModal.open({
					animation: true,
					templateUrl: '../app/dialogs/error-dialog.html',
					controller: 'ErrorDialogController',
					size: 'md',
					resolve: {
						body: function () {
							return body;
						}
					}
				});
			};

		});
})();