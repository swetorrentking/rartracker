(function(){
	'use strict';

	angular.module('tracker.services')
		.service('DeleteDialog', function ($uibModal) {

			return function (title, body, wantReason, reason) {
				var modal = $uibModal.open({
					animation: true,
					templateUrl: '../app/dialogs/delete-dialog.html',
					controller: 'DeleteDialogController',
					size: 'sm',
					resolve: {
						settings: function () {
							return {
								title: title,
								body: body,
								wantReason: wantReason,
								reason: reason || ''
							};
						}
					}
				});
				return modal.result;
			};

		});
})();