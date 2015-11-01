(function(){
	'use strict';

	angular.module('tracker.controllers')
		.controller('BannedController', function ($scope, AdminResource, ErrorDialog) {

			fetchNonsceneData();
			initAddNonsceneForm();

			function initAddNonsceneForm() {
				$scope.addnonscene = {
					whitelist: 1,
					comment: 'Ej scene'
				};
			}

			function fetchNonsceneData() {
				AdminResource.Nonscene.query().$promise
					.then(function (data) {
						$scope.nonscene = data;
					});
			}

			$scope.delete = function (item) {
				AdminResource.Nonscene.delete({ id: item.id }, function () {
					var index = $scope.nonscene.indexOf(item);
					$scope.nonscene.splice(index, 1);
				});
			};

			$scope.create = function () {
				AdminResource.Nonscene.save($scope.addnonscene).$promise
					.then(function () {
						initAddNonsceneForm();
						fetchNonsceneData();
					})
					.catch(function (error) {
						ErrorDialog.display(error.data);
					});
			};

		});
})();