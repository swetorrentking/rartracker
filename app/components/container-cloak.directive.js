/*
	This directive is used to cloak the UI before data has loaded first time.
	This prevents a "flicker" before the UI has been populated.
*/

(function(){
	'use strict';

	angular
		.module('app.shared')
		.directive('containerCloak', ContainerCloak);

	function ContainerCloak() {
		return {
			restrict: 'C',
			scope: {},
			controller: ContainerCloakController
		};
	}

	function ContainerCloakController($scope, $element) {
		this.removeClass = function () {
			$element.removeClass('container-cloak');
		};

		if (document.cookie.indexOf('uid=') < 0) {
			this.removeClass();
		} else {
			$scope.$on('userUpdated', () => {
				this.removeClass();
			});
		}
	}

})();
