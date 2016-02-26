(function(){
	'use strict';

	angular
		.module('app.shared')
		.component('statusBox', {
			templateUrl: '../app/header/status-box.template.html',
			controller: StatusBoxController,
			controllerAs: 'vm'
		});

	function StatusBoxController($state, $interval, $scope, $filter, authService) {

		this.$onInit = function () {
			this.currentUser = authService.getUser();
			this.updateLeechTime();
		};

		this.updateLeechTime = function () {
			if (this.currentUser && this.currentUser.leechstart) {
				if ($filter('dateDiff')(this.currentUser.leechstart) > 0) {
					this.leechTime = $filter('dateDifference')(this.currentUser.leechstart);
				} else {
					this.leechTime = null;
				}
			}
		};

		this.logout = function () {
			authService.logout();
		};

		$interval(() => {
			this.updateLeechTime();
		}, 60000);

		$scope.$on('userUpdated', (event, user) => {
			this.updateLeechTime();
			this.currentUser = user;
		});

	}

})();
