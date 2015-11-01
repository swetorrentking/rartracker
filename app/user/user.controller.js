(function(){
	'use strict';

	angular
		.module('tracker.controllers')
		.controller('UserController', function ($scope, $http, $stateParams, $state, ConfirmDialog, SendMessageDialog, ReportDialog, FriendsResource, BlocksResource, BonusShopResource, ErrorDialog, UsersResource, AuthService) {

			var loadUser = function () {
				UsersResource.Users.get({id: $stateParams.id}, function (user) {
					$scope.user = user;
				}, function (error) {
					$scope.notFoundMessage = error.data;
				});
			};

			$scope.togglePeers = function () {
				$scope.showPeers = !$scope.showPeers;
				if (!$scope.seeding) {
					UsersResource.Peers.get({id: $stateParams.id}, function (peers) {
						$scope.seeding = peers.seeding;
						$scope.leeching = peers.leeching;
					});
				}
			};

			$scope.toggleInvites = function () {
				$scope.showInvites = !$scope.showInvites;
				if (!$scope.invites) {
					UsersResource.Invitees.query({id: $stateParams.id}, function (invitees) {
						$scope.invitees = invitees;
					});
				}
			};

			$scope.toggleBonusLog = function (loadmore) {
				var limit = 0;
				if (!loadmore) {
					$scope.showBonuslog = !$scope.showBonuslog;
				} else {
					limit = 9999;
					$scope.bonuslog = null;
				}
				
				if (!$scope.bonuslog || limit) {
					UsersResource.Bonuslog.query({id: $stateParams.id, limit: limit}, function (bonuslog) {
						$scope.bonuslog = bonuslog;
					});
				}
			};

			$scope.toggleIpLog = function (loadmore) {
				var limit = 0;
				if (!loadmore) {
					$scope.showIpLog = !$scope.showIpLog;
				} else {
					limit = 9999;
					$scope.ipLog = null;
				}
				
				if (!$scope.iplog || limit) {
					UsersResource.Iplog.query({id: $stateParams.id, limit: limit}, function (iplog) {
						$scope.iplog = iplog;
					});
				}
			};

			$scope.toggleTorrents = function () {
				$scope.showTorrents = !$scope.showTorrents;
				if (!$scope.torrents) {
					UsersResource.Torrents.query({id: $stateParams.id}, function (torrents) {
						$scope.torrents = torrents;
					});
				}
			};

			$scope.toggleSnatchLog = function () {
				$scope.showSnatchLog = !$scope.showSnatchLog;
				if (!$scope.snatchLog) {
					UsersResource.Snatchlog.query({id: $stateParams.id}, function (snatchLog) {
						$scope.snatchLog = snatchLog;
					});
				}
			};

			$scope.toggleRequests = function () {
				$scope.showRequests = !$scope.showRequests;
				if (!$scope.requests) {
					UsersResource.Torrents.query({id: $stateParams.id, requests: 1}, function (requests) {
						$scope.requests = requests;
					});
				}
			};

			$scope.sendMessage = function () {
				new SendMessageDialog({user: $scope.user});
			};

			$scope.buyHeart = function () {
				var dialog = ConfirmDialog('Köp hjärta till vän', 'Vill du spendera 25p på ett hjärta till \''+$scope.user.username+'\'?', true, 'Anledning:');

				dialog.then(function (reason) {
					BonusShopResource.save({ id: 1, userId: $scope.user.id, motivation: reason }).$promise
						.then(function () {
							AuthService.statusCheck();
							loadUser();
						})
						.catch(function (error) {
							ErrorDialog.display(error.data);
						});
				});
			};

			$scope.addFriend = function () {
				var dialog = ConfirmDialog('Lägg till vän', 'Vill du lägga till \'' + $scope.user.username + '\' som vän?', true, 'Kommentar (valfritt):');

				dialog.then(function (reason) {
					FriendsResource.save({friendid: $scope.user.id, comment: reason}).$promise
						.then(function () {
							$state.go('friends');
						})
						.catch(function(error) {
							ErrorDialog.display(error.data);
						});
				});
			};

			$scope.blockUser = function () {
				var dialog = ConfirmDialog('Blockera användare', 'Vill du lägga till \'' + $scope.user.username + '\' på blocklistan?', true, 'Kommentar (valfritt):');

				dialog.then(function (reason) {
					BlocksResource.save({blockid: $scope.user.id, comment: reason}).$promise
						.then(function () {
							$state.go('friends');
						})
						.catch(function(error) {
							ErrorDialog.display(error.data);
						});
				});
			};

			$scope.report = function () {
				new ReportDialog('user', $scope.user.id, $scope.user.username);
			};

			loadUser();

		});
})();