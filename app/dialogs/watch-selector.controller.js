(function(){
	'use strict';

	angular.module('tracker.controllers')
		.controller('WatchSelectorController', function ($scope, $timeout, $uibModalInstance, ErrorDialog, user, UsersResource, movie) {
			$scope.movie = movie;
			$scope.dialogStatus = 0;
			$scope.model = {
				swesub: false,
				formats: {
					hd720: movie.category ? movie.category === 4 || movie.category === 6 : true,
					hd1080: movie.category === 5 || movie.category === 7,
					dvdrpal: movie.category === 1 || movie.category === 3,
					dvdrcustom: movie.category === 2
				},
				imdbinfoid: movie.imdbinfoid || movie.id,
				typ: movie.typ || (movie.seasoncount === 0 ? 0 : 1)
			};

			$scope.ok = function () {
				$scope.dialogStatus = 1;
				UsersResource.Watching.save({id: user.id}, $scope.model).$promise
					.then(function () {
						$scope.dialogStatus = 2;
						$timeout(function () {
							$uibModalInstance.close();
						}, 800);
					})
					.catch(function (error) {
						ErrorDialog.display(error.data);
						$scope.dialogStatus = 0;
					});
			};

			$scope.cancel = function () {
				$uibModalInstance.dismiss();
			};
		});
})();