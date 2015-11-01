(function(){
	'use strict';

	angular.module('tracker.controllers')
		.controller('NewsAdminController', function ($scope, $uibModalInstance, NewsResource, news) {
			if (news) {
				$scope.news = news;
			} else {
				$scope.news = {
					announce: 0
				};
			}

			$scope.create = function () {
				var promise;
				$scope.closeAlert();
				if ($scope.news.id) {
					promise = NewsResource.update({id: $scope.news.id}, $scope.news).$promise;
				} else {
					promise = NewsResource.save({}, $scope.news).$promise;
				}

				promise
					.then(function (result) {
						$uibModalInstance.close(result);
					}, function (error) {
						addAlert({ type: 'danger', msg: error.data });
					});
			};

			$scope.cancel = function () {
				$uibModalInstance.dismiss();
			};

			var addAlert = function (obj) {
				$scope.alert = obj;
			};

			$scope.closeAlert = function() {
				$scope.alert = null;
			};

		});
})();