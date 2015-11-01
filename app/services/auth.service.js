(function(){
	'use strict';

	angular.module('tracker.services', ['ngCookies'])
		.service('AuthService', function ($rootScope, StatusResource, $cookies, $state, $timeout, $location, $q) {
			var lastStateChange = Date.now();
			var activeTimer = null;
			var user;
			var deferred = $q.defer();
			var lastInterval;
			var serverTime = 0;
			var serverTimeUpdated = 0;

			var setUser = function (u) {
				user = u;
				deferred.resolve(u);
			};

			var getPromise = function () {
				if (!deferred.promise.$$state.status) {
					return deferred.promise;
				} else {
					var d = $q.defer();
					d.resolve(user);
					return d.promise;
				}
			};

			var logout = function () {
				user = null;
				$cookies.remove('uid');
				$cookies.remove('pass');
				$cookies.remove('notuseip');
				$cookies.remove('admin');
				if ($location.$$path.substring(1,7) !== 'signup' && $location.$$path.substring(1,8) !== 'recover') {
					$state.go('login');
				}
			};

			var setServerTime = function (st) {
				if (!serverTime) {
					serverTime = st * 1000;

					var date = new Date();
					var unixTime = date.getTime();
					serverTimeUpdated = unixTime;
				}
			};

			var getServerTime = function () {
				var date = new Date();
				var unixTime = date.getTime();
				var diff = unixTime - serverTimeUpdated;

				return serverTime + diff;
			};

			var runStatusCheck = function () {
				lastStateChange = Date.now();
				statusCheck();
			};

			var serverResponse = function (data) {
				setUser(data.user);
				setServerTime(data.settings.serverTime);
				scheduleNextStatusCheck();
			};

			var statusCheck = function () {
				if ($cookies.get('uid') && $cookies.get('pass')) {
					var timeSinceLastCheck = Date.now() - lastStateChange;
					StatusResource.get({timeSinceLastCheck: timeSinceLastCheck}, function(data) {
						serverResponse(data);
					}, function (response) {
						if (response.status === 401) {
							logout();
						} else {
							scheduleNextStatusCheck();
						}
					});
				} else {
					logout();
				}
			};

			var scheduleNextStatusCheck = function (reset) {
				if (reset && lastInterval <= 5000) {
					return;
				}
				$timeout.cancel(activeTimer);
				var timeSinceLastTime = Date.now() - lastStateChange;
				var interval = timeSinceLastTime * 2;

				// at least 30 sec interval
				if (interval < 30000) {
					interval = 30000;
				}

				// at most 30 min interval
				if (interval > 1800000) {
					interval = 1800000;
				}

				if (reset) {
					interval = 5000;
				}

				if (timeSinceLastTime < 1000 * 60 * 60 * 4) {
					activeTimer = $timeout(statusCheck.bind(this), interval);
				}

				lastInterval = interval;
			};

			$rootScope.$on('$stateChangeStart', function () {
				lastStateChange = Date.now();
				if (user !== undefined) {
					scheduleNextStatusCheck(true);
				}
			}.bind(this));

			var getCategoryFilter = function () {
				if (!user || !user['notifs']) {
					return [];
				}
				return user['notifs'];
			};

			var readUnreadMessage = function () {
				user['newMessages'] -= 1;
			};

			var readUnreadNews = function () {
				user['unreadFlashNews'] = 0;
			};

			var readUnreadTorrentComments = function () {
				user['unreadTorrentComments'] = 0;
			};

			var readUnreadAdminMessage = function () {
				user['newAdminMessages'] -= 1;
			};

			var readNewReports = function () {
				user['newReports'] -= 1;
			};

			return {
				getPromise: getPromise,
				getUser: function () { return user; },
				statusCheck: runStatusCheck,
				getCategoryFilter: getCategoryFilter,
				serverResponse: serverResponse,
				logout: logout,
				setUser: setUser,
				readUnreadMessage: readUnreadMessage,
				readUnreadNews: readUnreadNews,
				readUnreadAdminMessage: readUnreadAdminMessage,
				readNewReports: readNewReports,
				readUnreadTorrentComments: readUnreadTorrentComments,
				getServerTime: getServerTime
			};
		});
})();