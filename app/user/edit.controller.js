(function(){
	'use strict';

	angular.module('tracker.controllers')
		.controller('UserEditController', function ($scope, $http, $stateParams, $state, DeleteDialog, UsersResource, userClasses, categories, cssDesigns, AuthService, user) {
			$scope.userClasses = userClasses;
			$scope.cssDesigns = cssDesigns;

			var fetchUser = function () {
				UsersResource.Users.get({id: $stateParams.id}, function (user) {
					if (user) {
						var c;
						$scope.user = user;

						var catArray = [];
						for (c in categories) {
							var cat = categories[c];
							if ($scope.user.notifs.indexOf(cat.id) > -1) {
								cat.checked = true;
							} else {
								cat.checked = false;
							}
							catArray.push(cat);
						}

						$scope.maskClasses = [];
						for (c in userClasses) {
							if (userClasses[c].id <= user['class']) {
								$scope.maskClasses.push(userClasses[c]);
							}
						}

						$scope.user.notifs = catArray;

					} else {
						$scope.user = 'error';
					}
				}, function (error) {
					$scope.notFoundMessage = error.data;
				});
			};

			$scope.generatePasskey = function () {
				var chars = '0123456789abcdef';
				var randomstring = '';
				for (var i=0; i < 32; i++) {
					var rnum = Math.floor(Math.random() * chars.length);
					randomstring += chars.substring(rnum,rnum+1);
				}
				$scope.user.passkey = randomstring;
			};

			$scope.deleteUser = function () {
				var dialog = DeleteDialog('Radera användaren', 'Vill du radera \''+$scope.user.username+'\' ifrån databasen?');

				dialog.then(function () {
					UsersResource.Users.delete({id: $stateParams.id}, function () {
						$state.go('user', {id: $scope.user.id, username: $scope.user.username});
					});
				});
			};
							
			$scope.SaveEditProfile = function () {
				$scope.editButtonDisabled = true;
				var saveUser = angular.extend({}, $scope.user);
				saveUser.notifs = saveUser.notifs.filter(function (cat) {
					return cat.checked;
				}).map(function (cat) {
					return cat.id;
				});

				UsersResource.Users.update({id: saveUser.id}, saveUser, function () {
					$scope.addAlert({ type: 'success', msg: 'Inställningarna sparades.' });
					$scope.editButtonDisabled = false;
					if ($scope.user.id == user.id) {
						AuthService.statusCheck();
					} else {
						fetchUser();
					}
				}, function (error) {
					$scope.addAlert({ type: 'danger', msg: error.data });
					$scope.editButtonDisabled = false;
				});
			};

			$scope.addAlert = function (obj) {
				$scope.alert = obj;
			};

			$scope.closeAlert = function() {
				$scope.alert = null;
			};

			fetchUser();
		});
})();