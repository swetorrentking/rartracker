(function(){
	'use strict';

	angular
		.module('app.shared')
		.service('DeleteDialog', DeleteDialog);

	function DeleteDialog($uibModal) {
		return function (title, body, wantReason, reason) {
			var modal = $uibModal.open({
				templateUrl: '../app/dialogs/delete-dialog.template.html',
				controller: 'DeleteDialogController as vm',
				backdrop: 'static',
				size: 'md',
				resolve: {
					settings: () => {
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
	}

})();
