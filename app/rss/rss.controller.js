(function(){
	'use strict';

	angular.module('tracker.controllers')
		.controller('RssController', function ($scope, configs, categories, user) {
			var url = configs.SITE_URL + '/rss.php';

			$scope.model = {
				url: url,
				setting: 0,
				categories: [],
				p2p: 0,
			};

			$scope.$watch('model', function () {
				var params = [];
				var cats = $scope.model.categories.filter(function(cat) { return !!cat.checked;});
				if (cats.length > 0) {
					cats = cats.map(function(cat) { return cat.id; });
					params.push('cat=' + cats.join(','));
				} 
				if ($scope.model.setting > 0) {
					params.push('s=' + $scope.model.setting);
				}
				if ($scope.model.p2p) {
					params.push('p2p=' + 1);
				}
				params.push('passkey=' + user['passkey']);
				$scope.model.url = url + '?' + params.join('&');
			}, true);

			var catArray = [];
			for (var c in categories) {
				catArray.push(categories[c]);
			}

			$scope.model.categories = catArray;
		});
})();