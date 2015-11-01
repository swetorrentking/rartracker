(function(){
	'use strict';

	angular.module('tracker.directives')
		.directive('autoFocus', function ($timeout) {
			return {
				restrict: 'AC',
				link: function(_scope, _element) {
					$timeout(function(){
						_element[0].setSelectionRange(0, 0);
						_element[0].focus();
					}, 100);
				}
			};
		});
})();