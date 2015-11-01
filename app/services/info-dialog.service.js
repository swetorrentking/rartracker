(function(){
	'use strict';

	angular.module('tracker.services')
		.service('InfoDialog', function ($uibModal) {

			return function (title, body) {
				$uibModal.open({
					animation: true,
					templateUrl: '../app/dialogs/info-dialog.html',
					controller: 'InfoDialogController',
					size: 'md',
					resolve: {
						settings: function () {
							return {
								title: title,
								body: body,
							};
						}
					}
				});
			};

		});
})();