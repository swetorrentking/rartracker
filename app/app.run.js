(function(){
	'use strict';

	angular.module('tracker')
		.run(function(AuthService, $rootScope, $state) {
			AuthService.statusCheck();

			// To get ui-sref-active to work on child states
			// https://github.com/angular-ui/ui-router/issues/948#issuecomment-75342784
			$rootScope.$on('$stateChangeStart', function(evt, to, params) {
				if (to.redirectTo) {
					evt.preventDefault();
					$state.go(to.redirectTo, params);
				}
			});
		});
})();