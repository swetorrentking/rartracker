(function(){
	'use strict';

	angular.module('tracker.controllers')
		.controller('EditStartController', function ($scope, StartTorrentsResource, ErrorDialog) {

			var fetchTorrents = function () {
				StartTorrentsResource.query({}, function (data) {
					$scope.highligtedTorrents = data;
				});
			};

			$scope.add = function () {
				StartTorrentsResource.save({
					tid: $scope.list.tid.id,
					format: $scope.list.format.id,
					sektion: $scope.list.sektion.id,
					typ: $scope.list.typ.id,
					sort: $scope.list.sort.id,
					genre: $scope.list.genre ? $scope.list.genre.id : ''
				}, function () {
					fetchTorrents();
				}, function (error) {
					ErrorDialog.display(error.data);
				});
			};

			$scope.delete = function (id) {
				StartTorrentsResource.delete({
					id: id
				}, function () {
					fetchTorrents();
				}, function (error) {
					ErrorDialog.display(error.data);
				});
			};

			$scope.move = function (id, direction) {
				StartTorrentsResource.update({
					id: id,
					direction: direction,
					action: 'move',
				}, function () {
					fetchTorrents();
				});
			};

			$scope.reset = function (category) {
				StartTorrentsResource.update({
					category: category,
					action: 'reset',
				}, function () {
					fetchTorrents();
				});
			};

			fetchTorrents();

			$scope.data = {
				tid: [
					{
						id: 0,
						name: 'Dagens'
					},
					{
						id: 1,
						name: 'Veckans'
					},
					{
						id: 2,
						name: 'Månadens'
					}],
				typ: [
					{
						id: 0,
						name: 'Filmer'
					},
					{
						id: 1,
						name: 'TV-serier'
					}
				],
				format: [
					{
						id: 0,
						name: 'DVDR'
					},
					{
						id: 1,
						name: '720p HD'
					},
					{
						id: 2,
						name: '1080p HD'
					}
				],
				sektion: [
					{
						id: 0,
						name: 'Nya releaser'
					},
					{
						id: 1,
						name: 'Nya på arkivet'
					}
				],
				sort: [
					{
						id: 2,
						name: 'Sortera efter popularitet'
					},
					{
						id: 0,
						name: 'Sortera efter högst betyg'
					},
					{
						id: 1,
						name: 'Sortera efter datum'
					}
				],
				genre: [
				{
					id: 'Action',
				},
				{
					id: 'Adventure',
				},
				{
					id: 'Animation',
				},
				{
					id: 'Biography',
				},
				{
					id: 'Crime',
				},
				{
					id: 'Documentary',
				},
				{
					id: 'Drama',
				},
				{
					id: 'Family',
				},
				{
					id: 'Fantasy',
				},
				{
					id: 'History',
				},
				{
					id: 'Horror',
				},
				{
					id: 'Music',
				},
				{
					id: 'Musical',
				},
				{
					id: 'Mystery',
				},
				{
					id: 'Romance',
				},
				{
					id: 'Sci-fi',
				},
				{
					id: 'Sport',
				},
				{
					id: 'Thriller',
				},
				{
					id: 'War',
				}
				]

			};

			$scope.list = {
				tid: $scope.data.tid[1],
				typ: $scope.data.typ[0],
				format: $scope.data.format[1],
				sektion: $scope.data.sektion[0],
				sort: $scope.data.sort[0],
				genre: ''	
			};

		});
})();