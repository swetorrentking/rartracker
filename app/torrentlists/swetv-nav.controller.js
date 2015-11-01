(function(){
	'use strict';

	angular.module('tracker.controllers')
		.controller('SweTvController', function ($scope, $state, user) {
			$scope.user = user;

			if (user['tvvy'] == 1) {
				$state.go('swetv.torrents');
			} else {
				$state.go('swetv.guide');
			}

			$scope.SwitchView = function (newTvView) {
				if (newTvView == 1) {
					$state.go('swetv.torrents');
				} else {
					$state.go('swetv.guide');
				}
			};
		});
})();