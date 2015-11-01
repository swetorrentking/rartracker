(function(){
	'use strict';

	angular.module('tracker.services')
		.service('ConfirmDialog', function ($uibModal) {

			return function (title, body, wantReason, reasonTitle) {
				var modal = $uibModal.open({
					animation: true,
					templateUrl: '../app/dialogs/confirm-dialog.html',
					controller: 'ConfirmDialogController',
					size: 'md',
					resolve: {
						settings: function () {
							return {
								title: title,
								body: body,
								wantReason: wantReason,
								reasonText: reasonTitle,
								reason: '',
							};
						}
					}
				});
				return modal.result;
			};

		});
})();