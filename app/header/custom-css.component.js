(function(){
	'use strict';

	angular
		.module('app.shared')
		.component('customCss', {
			template: `
				<link ng-if="vm.currentUser.design == 1" ng-href="{{ vm.currentUser.css }}" rel="stylesheet" />
				<link ng-if="vm.currentUser.design == 2" ng-href="/css/themes/blue/blue-angular-tracker.css" rel="stylesheet" />
			`,
			controller: CustomCssController,
			controllerAs: 'vm'
		});

	function CustomCssController($scope, authService) {

		$scope.$on('userUpdated', (event, user) => {
			this.currentUser = user;
		});

		this.currentUser = authService.getUser();

	}

})();
