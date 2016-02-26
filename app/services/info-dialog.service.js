(function(){
	'use strict';

	angular
		.module('app.shared')
		.service('InfoDialog', InfoDialog);

	function InfoDialog($uibModal) {
		return function (title, body) {
			$uibModal.open({
				templateUrl: '../app/dialogs/info-dialog.template.html',
				controller: 'InfoDialogController as vm',
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
	}

})();
