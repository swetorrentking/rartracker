(function(){
	'use strict';

	angular.module('tracker.directives')
		.directive('user', function () {
			return {
				restrict: 'E',
				templateUrl: '../app/components/user.directive.html',
				scope: {
					user: '=',
				},
				link: function (scope, element, attrs){
					scope.icons = scope.$eval(attrs.icons) === false ? false : true;
					scope.link = scope.$eval(attrs.link) === false ? false : true;
					scope.showclass = scope.$eval(attrs.showclass) === false ? false : true;
					scope.iconsize = attrs.iconsize === 'big' ? 'big' : 'small';
				}
			};

		});
})();