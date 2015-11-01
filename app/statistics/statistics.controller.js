(function(){
	'use strict';

	angular.module('tracker.controllers')
		.controller('StatisticsController', function ($scope, StatisticsResource, userClasses, categories) {

			StatisticsResource.query({}, function (data) {
				var firstItem = data[0];
				data.reverse();

				$scope.statsDateLabels = [];
				data.forEach(function (stats) {
					$scope.statsDateLabels.push(stats['datum']);
				});

				/* Users per class */
				$scope.userClassesLabels = [
					userClasses.STATIST.name,
					userClasses.SKADIS.name,
					userClasses.FILMSTJARNA.name,
					userClasses.REGISSAR.name
				];
				$scope.userClassesData = [
					firstItem['numusersclass0'],
					firstItem['numusersclass1'],
					firstItem['numusersclass2'],
					firstItem['numusersclass3']
				];

				/* Users per class over time */
				$scope.userClassesLineData = [[], [], [], []];

				data.forEach(function (stats) {
					$scope.userClassesLineData[0].push(stats['numusersclass0']);
					$scope.userClassesLineData[1].push(stats['numusersclass1']);
					$scope.userClassesLineData[2].push(stats['numusersclass2']);
					$scope.userClassesLineData[3].push(stats['numusersclass3']);
				});

				/* Active users over time */
				$scope.activeUsersData = [[]];
				data.forEach(function (stats) {
					$scope.activeUsersData[0].push(stats['activeusers']);
				});

				/* Active users over time */
				$scope.activeClientsData = [[]];
				data.forEach(function (stats) {
					$scope.activeClientsData[0].push(stats['activeclients']);
				});

				/* Shared GB per active user */
				$scope.userSharedData = [[]];
				data.forEach(function (stats) {
					$scope.userSharedData[0].push(parseInt(stats['totalsharegb']/stats['activeclients'], 10));
				});

				/* Users with 100% Leech bonus */
				$scope.usersWith100LeechBonus = [[]];
				data.forEach(function (stats) {
					$scope.usersWith100LeechBonus[0].push(stats['100leechbonus']);
				});

				/* Registred users */
				$scope.registeredUsersData = [[]];
				data.forEach(function (stats) {
					$scope.registeredUsersData[0].push(stats['newusers']);
				});

				/* CSS themes used */
				$scope.cssThemesLabels = ['Standard', 'Standard Blå', 'SweBits'];
				$scope.cssThemesData = [
					firstItem['userdesign0'],
					firstItem['userdesign2'],
					firstItem['userdesign3']
				];

				/* Torrents per category */
				$scope.torrentsPerCategoryLabels = [
					categories.DVDR_PAL.text,
					categories.DVDR_CUSTOM.text,
					categories.DVDR_TV.text,
					categories.MOVIE_720P.text,
					categories.MOVIE_1080P.text,
					categories.TV_720P.text,
					categories.TV_1080P.text,
					categories.TV_SWE.text
				];
				$scope.torrentsPerCategoryData = [
					firstItem['cat1torrents'],
					firstItem['cat2torrents'],
					firstItem['cat3torrents'],
					firstItem['cat4torrents'],
					firstItem['cat5torrents'],
					firstItem['cat6torrents'],
					firstItem['cat7torrents'],
					firstItem['cat8torrents']
				];

				/* Torrents per category over time */
				$scope.torrentsPerCategoryLineData = [[], [], [], [], [], [], [], []];

				data.forEach(function (stats) {
					$scope.torrentsPerCategoryLineData[0].push(stats['cat1torrents']);
					$scope.torrentsPerCategoryLineData[1].push(stats['cat2torrents']);
					$scope.torrentsPerCategoryLineData[2].push(stats['cat3torrents']);
					$scope.torrentsPerCategoryLineData[3].push(stats['cat4torrents']);
					$scope.torrentsPerCategoryLineData[4].push(stats['cat5torrents']);
					$scope.torrentsPerCategoryLineData[5].push(stats['cat6torrents']);
					$scope.torrentsPerCategoryLineData[6].push(stats['cat7torrents']);
					$scope.torrentsPerCategoryLineData[7].push(stats['cat8torrents']);
				});

				/* Amount shared data */
				$scope.totalSharedData = [[]];
				data.forEach(function (stats) {
					$scope.totalSharedData[0].push(parseInt(stats['totalsharegb']/1000, 10));
				});

				/* Peers amount */
				$scope.totalPeersData = [[]];
				data.forEach(function (stats) {
					$scope.totalPeersData[0].push(stats['seeders']+stats['leechers']);
				});

				/* New forum posts */
				$scope.forumPostsData = [[]];
				data.forEach(function (stats) {
					$scope.forumPostsData[0].push(stats['newforumposts']);
				});

				/* New torrent comments */
				$scope.torrentCommentsData = [[]];
				data.forEach(function (stats) {
					$scope.torrentCommentsData[0].push(stats['newcomments']);
				});
			});

		});
})();