(function(){
	'use strict';

	angular.module('tracker.controllers')
		.controller('SweTvGuideController', function ($scope, TorrentsResource, user) {
			$scope.lastBrowseDate = user['last_tvbrowse'];
			$scope.currentPage = 1;

			$scope.categoriesChanged = function () {
				getReleases();
			};

			var rowFilter = function (data) {
				var rows = [];

				var slices = [2,2,2,2];
				slices.forEach(function (s) {
					rows.push(data.splice(0, s));
				});

				return rows;
			};

			var getReleases = function () {
				var week = $scope.currentPage - 1;
				$scope.tvDataRow = null;
				TorrentsResource.SweTvGuide.query({'week': week}, function (tvData) {
					$scope.tvDataRow = rowFilter(tvData);
				});
			};

			$scope.pageChanged = function () {
				getReleases();
			};

			getReleases();
		});
})();