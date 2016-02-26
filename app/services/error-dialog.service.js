(function(){
	'use strict';

	angular
		.module('app.shared')
		.service('ErrorDialog', ErrorDialog);

	function ErrorDialog($uibModal) {
		this.display = function (body) {
			$uibModal.open({
				templateUrl: '../app/dialogs/error-dialog.template.html',
				controller: 'ErrorDialogController as vm',
				size: 'md',
				resolve: {
					body: () => body
				}
			});
		};
	}

})();
