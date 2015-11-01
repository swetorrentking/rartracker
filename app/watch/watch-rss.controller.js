(function(){
	'use strict';

	angular.module('tracker.controllers')
		.controller('WatchRssController', function ($scope, user) {
			var url = 'https://rartracker.org/brss.php';

			$scope.model = {
				url: url,
				setting: 0,
				time: false
			};

			$scope.$watchCollection('model', function () {
				var params = [];
				if ($scope.model.setting > 0) {
					params.push('vad=' + $scope.model.setting);
				}
				params.push('passkey=' + user['passkey']);
				if ($scope.model.time) {
					params.push('from=' + Math.floor(Date.now() / 1000));
				}
				$scope.model.url = url + '?' + params.join('&');
			});
		});
})();