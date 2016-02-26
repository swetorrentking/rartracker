(function(){
	'use strict';

	angular
		.module('app.shared')
		.directive('autoFocus', AutoFocusDirective);

	function AutoFocusDirective($timeout) {
		return {
			restrict: 'A',
			link: link
		};

		function link(scope, element) {
			$timeout(() => {
				element[0].setSelectionRange(0, 0);
				element[0].focus();
			}, 100);
		}
	}

})();