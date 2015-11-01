(function(){
	'use strict';

	angular.module('tracker.services')
		.service('WatchDialog', function ($uibModal, AuthService) {

			return function (movieModel) {
				var modal = $uibModal.open({
					animation: true,
					templateUrl: '../app/dialogs/watch-selector-dialog.html',
					controller: 'WatchSelectorController',
					size: 'md',
					resolve: {
						movie: function () {
							return movieModel;
						},
						user: function () {
							return AuthService.getPromise();
						}
					}
				});
				return modal.result;
			};

		});
})();