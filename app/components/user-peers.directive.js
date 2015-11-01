(function(){
	'use strict';

	angular.module('tracker.directives')
		.directive('userPeers', function () {
			return {
				restrict: 'E',
				templateUrl: '../app/components/user-peers.directive.html',
				scope: {
					peers: '=',
				}
			};

		});
})();