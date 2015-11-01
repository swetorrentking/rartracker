(function(){
	'use strict';

	angular.module('tracker.controllers')
		.controller('HeaderController', function ($scope, $rootScope, $window, $filter, $interval, $location, AuthService, $uibModal, $state, userClasses) {
			$scope.loggedIn = false;
			$scope.hideCompletely = true;

			/* Search */
			$scope.isSearching = false;
			$scope.searchText = '';
			$scope.itemsPerPage = 20;
			$scope.extendedSearch = $location.search().extended === '1';

			$scope.menuClass = function (page) {
				var current = $location.path().substring(1);
				return page === current ? 'active' : '';
			};

			$scope.$watch(function () {
				return $state.current;
			},
			function(state) {
				$scope.stateName = state.name;
			});

			$scope.$watch(function () {
				return AuthService.getUser();
			},
			function(user) {
				if (angular.isUndefined(user)) {
					return;
				}

				$scope.hideCompletely = false;

				if (!angular.isUndefined(user) && user !== null) {
					$scope.myself = user;
					$scope.loggedIn = true;
					if (user.class == userClasses.STAFF.id) {
						$scope.isAdmin = true;
					}
					updateLeechTime();
					setDonatedProgress(user.donatedAmount);
				} else {
					$scope.isAdmin = false;
					$scope.myself = null;
					$scope.loggedIn = false;
					if ($location.$$path.substring(1,7) !== 'signup' && $location.$$path.substring(1,8) !== 'recover') {
						$state.go('login');
					}
				}
			}, true);

			$scope.logout = function () {
				AuthService.logout();
			};

			$scope.openUserPicker = function () {
				var modalInstance = $uibModal.open({
					templateUrl: '../app/header/user-picker.html',
					controller: 'UserPickerController',
					size: 'sm',
					resolve: {
						items: function () {
							return $scope.items;
						}
					}
				});

				modalInstance.result.then(function (user) {
					$state.go('user', {id: user.id, username: user.username});
				});
			};

			$scope.doSearch = function () {
				if ($state.current.name !== 'search') {
					$state.go('search', {searchText: $scope.searchText, extended: $scope.extendedSearch});
				}
				broadcastSearch();
			};

			$scope.changeExtendedSearch = function () {
				if ($scope.extendedSearch) {
					$location.search('extended', '1');
				} else {
					$location.search('extended', null);
				}
				broadcastSearch();
			};

			$rootScope.$on('$stateChangeStart',  function (event, toState){
				if (toState.name !== 'search') {
					$scope.searchText = '';
					$scope.loadingSearch = false;
				}
			});

			var broadcastSearch = function () {
				$rootScope.$broadcast('doSearch', $scope.searchText);
			};

			var setDonatedProgress = function (amount) {
				var savedBuffer = 1300;
				var percent = amount/savedBuffer;
				percent = Math.round(percent * 100);
				if (percent >= 100) {
					percent = 100;
					$scope.donatedProgressColor = '#6bc75c';
				} else if (percent >= 50) {
					$scope.donatedProgressColor = '#c7c65c';
				} else {
					$scope.donatedProgressColor = '#c75c5c';
				}
				$scope.donatedProgress = percent;
			};

			var updateLeechTime = function () {
				if ($scope.myself && $scope.myself.leechstart) {
					if ($filter('dateDiff')($scope.myself.leechstart) > 0) {
						$scope.leechTime = $filter('dateDifference')($scope.myself.leechstart);
					} else {
						$scope.leechTime = null;
					}
				}
			};

			$interval(function () {
				updateLeechTime();
			}, 60000);
		});
})();