(function(){
	'use strict';

	angular
		.module('app.swetv')
		.controller('SweTvController', SweTvController);

	function SweTvController($rootScope, $scope, $state, user, $stateParams) {

		if ($state.current.name === 'swetv.torrents') {
			this.tvView = 1;
		} else if ($state.current.name === 'swetv.guide') {
			this.tvView = 0;
		}

		this.autoSwitchView = function () {
			this.tvView = user['tvvy'];

			if (user['tvvy'] === 0) {
				$state.go('swetv.guide');
			} else {
				this.tvView = 1;
				$state.go('swetv.torrents');
			}
		};

		this.switchView = function (newView) {
			if (newView == 1) {
				$state.go('swetv.torrents');
			} else {
				$state.go('swetv.guide');
			}
		};

		if ($stateParams.autoSwitchView) {
			this.autoSwitchView();
		}

		var listener = $rootScope.$on('$stateChangeSuccess', (event, toState) => {
			if (toState.name == 'swetv.guide') {
				this.tvView = 0;
			} else {
				this.tvView = 1;
			}
		});

		$scope.$on('$destroy', listener);
	}

})();
