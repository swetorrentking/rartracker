(function(){
	'use strict';

	angular.module('tracker.controllers')
		.controller('CommonTorrentsController', function ($scope, $http, $location, $state, $stateParams, user, TorrentsResource, categories, AuthService, settings) {
			$scope.checkboxCategories = settings.checkboxCategories;
			$scope.deleteVars = {
			};
			$scope.checkMode = false;
			$scope.deletingMulti = false;
			$scope.itemsPerPage = user['torrentsperpage'] > 0 ? user['torrentsperpage'] : 15;
			$scope.hideOld = settings.pageName == 'last_browse' && user['visagammalt'] === 0;
			$scope.lastBrowseDate = user[settings.pageName];
			var firstLoad = true;
			if (settings.pageName === 'search' && user['search_sort'] === 'name') {
				$scope.sort = $location.search().sort || 'n';
				$scope.order = $location.search().order || 'asc';
			} else {
				$scope.sort = $location.search().sort || 'd';
				$scope.order = $location.search().order || 'desc';
			}
			$scope.$parent.searchText = $scope.searchText = $stateParams.searchText || $location.search().search;

			if ($scope.searchText) {
				$location.search('search', $scope.searchText);
			}

			var initialPage = $scope.currentPage = $location.search().page || 1;

			$scope.checkboxCategories.forEach(function (cat) {
				if (user['notifs'].indexOf(cat.id) > -1) {
					cat.checked = true;
				} else {
					cat.checked = false;
				}
			});

			$scope.showHideOldCheckbox = settings.showHideOldCheckbox;
			$scope.checkboxChannels = settings.checkboxChannels;

			if (settings.pageName === 'search' && $stateParams.extended) {
				$location.search('extended', '1');
			}

			if (settings.pageName === 'search') {
				$scope.$on('doSearch', function (event, searchText) {
					doSearch(searchText);
				});
			}

			var doSearch = function (searchText) {
				if (searchText.length === 0) {
					$scope.torrents = null;
				}
				if (firstLoad) {
					return;
				}
				$scope.searchText = searchText;
				if (searchText) {
					if ($location.search().search && searchText.indexOf($location.search().search) === 0) {
						$location.search('search', searchText).replace();
					} else {
						$location.search('search', searchText);
					}
				}
				$scope.currentPage = 1;
				getTorrents();
			};

			var defaultSelectedCats = settings.defaultSelectedCats;

			function getWantedCategories() {
				var selectedCats = $scope.checkboxCategories.filter(function(cat) {
					return !!cat.checked;
				});
				if (selectedCats.length > 0) {
					return selectedCats.map(function (cat) {
						return cat.id;
					});
				} else {
					return defaultSelectedCats;
				}
			}

			$scope.CategoriesChanged = function () {
				getTorrents();
			};

			$scope.HideOldChange = function () {
				getTorrents();
			};

			$scope.filterCategory = function (category) {
				$scope.checkboxCategories.forEach(function (cat) {
					if (category === cat.id) {
						cat.checked = true;
					} else {
						cat.checked = false;
					}
				});
				getTorrents();
			};

			var getTorrents = function () {
				if (settings.pageName === 'search') {
					$scope.$parent.loadingSearch = true;
				}
				var index = (initialPage || $scope.currentPage) * $scope.itemsPerPage - $scope.itemsPerPage || 0;
				TorrentsResource.Torrents.query({
					'categories[]': getWantedCategories(),
					'index': index,
					'hideOld': $scope.hideOld,
					'p2p': settings.p2p,
					'section': settings.section,
					'limit': $scope.itemsPerPage,
					'page': settings.pageName,
					'sort': $scope.sort,
					'order': $scope.order,
					'searchText': $scope.searchText,
					'extendedSearch': $location.search().extended === '1',
				}, function (torrents, responseHeaders) {
					var headers = responseHeaders();
					$scope.totalItems = headers['x-total-count'];
					$scope.torrents = torrents;
					if (initialPage) {
						initialPage = null;
					}
					if (settings.pageName === 'search') {
						$scope.$parent.loadingSearch = false;
					}
					if (firstLoad) {
						$scope.currentPage = $location.search().page || 1;
						firstLoad = false;
					}
					$scope.checkMode = false;
				});
			};

			$scope.pageChanged = function () {
				if (firstLoad) return;
				$location.search('page', $scope.currentPage).replace();
				getTorrents();
			};

			$scope.$watch(function(){ return $location.search(); }, function (url){
				if (url.search !== $scope.searchText && url.search !== undefined) {
					doSearch(url.search);
					$scope.$parent.searchText = url.search;
				}
				$scope.$parent.extendedSearch = url.extended === '1';

				if (!firstLoad && (url.sort !== undefined && url.sort != $scope.sort) ||
					(url.order !== undefined && url.order != $scope.order)) {
					if (url.order) {
						$scope.order = url.order;
					}
					if (url.sort) {
						$scope.sort = url.sort;
					}
					getTorrents();
				}
			});

			if ($scope.searchText) {
				doSearch($scope.searchText);
			}

			var setOrder = function (order) {
				$scope.order = order;
				$location.search('order', order);
			};

			var setSort = function (sort) {
				$scope.sort = sort;
				$location.search('sort', sort);
			};

			var getSelectedTorrents = function () {
				return $scope.torrents && $scope.torrents.filter(function (torrent) {
					return torrent.selected === 'yes';
				});
			};

			$scope.getCheckedAmount = function () {
				var selectedTorrents = getSelectedTorrents();
				return selectedTorrents && selectedTorrents.length;
			};

			$scope.sortTorrents = function (sort) {
				if ($scope.sort == sort) {
					setOrder($scope.order == 'asc' ? $scope.order = 'desc' : $scope.order = 'asc');
				} else {
					setSort(sort);
					if (sort == 'n') {
						setOrder('asc');
					} else {
						setOrder('desc');
					}
				}
				getTorrents();
			};

			$scope.multiDelete = function () {
				$scope.deletingMulti = true;
				var torrents = getSelectedTorrents();
				torrents = torrents.map(function (torrent) {
					return torrent.id;
				});

				TorrentsResource.Multi.remove({
					reason: $scope.deleteVars.reason,
					pmUploader: $scope.deleteVars.pmUploader,
					pmPeers: $scope.deleteVars.pmPeers,
					attachTorrentId: $scope.deleteVars.attachTorrentId,
					'torrents[]': torrents
				}, function () {
					$scope.deletingMulti = false;
					$scope.checkMode = false;
					$scope.torrents = $scope.torrents.filter(function (torrent) {
						return torrents.indexOf(torrent.id) === -1;
					});
				});
			};

			$scope.loadRelated = function () {
				if ($scope.torrents && $scope.checkMode) {
					var imdbId;
					for(var i = 0; i < $scope.torrents.length; i++) {
						if ($scope.torrents[i].imdbid) {
							imdbId = $scope.torrents[i].imdbid;
							break;
						}
					}
					if (imdbId) {
						TorrentsResource.Related.query({id: imdbId}, function (torrents) {
							$scope.relatedTorrents = torrents;
						});
					} else {
						$scope.relatedTorrents = null;
					}
				}
			};

			getTorrents();
		});
})();