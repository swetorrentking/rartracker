(function(){
	'use strict';

	angular
		.module('app.watcher')
		.service('WatchDialog', WatchDialog);

	function WatchDialog($uibModal, authService) {
		return function (movieModel) {
			var modal = $uibModal.open({
				templateUrl: '../app/watch/watch-selector-dialog.template.html',
				controller: 'WatchSelectorController as vm',
				size: 'md',
				backdrop: 'static',
				resolve: {
					movie: () => movieModel,
					user: () => authService.getPromise()
				}
			});
			return modal.result;
		};
	}

})();
