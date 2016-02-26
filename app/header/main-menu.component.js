(function(){
	'use strict';

	angular
		.module('app.shared')
		.component('mainMenu', {
			templateUrl: '../app/header/main-menu.template.html',
			controller: MainMenuController,
			controllerAs: 'vm'
		});

	function MainMenuController($scope, $state, $uibModal, authService) {
		$scope.$on('userUpdated', (event, user) => {
			this.currentUser = user;
		});

		this.currentUser = authService.getUser();
	}

})();
