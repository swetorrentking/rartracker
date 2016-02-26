(function(){
	'use strict';

	angular
		.module('app.shared')
		.service('ConfirmDialog', ConfirmDialog);

	function ConfirmDialog($uibModal) {
		return function (title, body, wantReason, reasonTitle) {
			var modal = $uibModal.open({
				templateUrl: '../app/dialogs/confirm-dialog.template.html',
				controller: 'ConfirmDialogController as vm',
				size: 'md',
				backdrop: 'static',
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
	}

})();
