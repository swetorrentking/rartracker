(function(){
	'use strict';

	angular
		.module('app.shared')
		.service('authService', AuthService);

	function AuthService($rootScope, userClasses, StatusResource, $cookies, $state, $timeout, $location, $q, $translate) {

		this.lastStateChange = Date.now();
		this.activeTimer = null;
		this.user = null;
		this.deferred = $q.defer();
		this.lastInterval = 0;
		this.serverTime = 0;
		this.serverTimeUpdated = 0;
		this.settings = [];

		this.isAdmin = function () {
			if (this.user.class == userClasses.STAFF.id) {
				return true;
			}
			return false;
		};

		this.setUser = function (user) {
			this.user = user;
			this.deferred.resolve(user);
			$rootScope.$broadcast('userUpdated', user);
			if (localStorage) {
				try {
					localStorage.setItem('default-language', user.language);
				} catch (error) {}
			}
			return $translate.use(user.language);
		};

		this.setSettings = function (settings) {
			this.settings = settings;
			this.setServerTime(settings.serverTime);
		};

		this.getSettings = function () {
			return this.settings;
		};

		this.getPromise = function () {
			if (!this.deferred.promise.$$state.status) {
				return this.deferred.promise
					.then(user => {
						return $translate.use(user.language).then(() => user);
					});
			} else {
				var d = $q.defer();
				d.resolve(this.user);
				return d.promise;
			}
		};

		this.logout = function () {
			this.user = null;
			$cookies.remove('uid');
			$cookies.remove('pass');
			$rootScope.$broadcast('userUpdated', null);
			$state.go('login');
		};

		this.setServerTime = function (st) {
			if (!this.serverTime) {
				this.serverTime = st * 1000;

				var date = new Date();
				var unixTime = date.getTime();
				this.serverTimeUpdated = unixTime;
			}
		};

		this.getServerTime = function () {
			var date = new Date();
			var unixTime = date.getTime();
			var diff = unixTime - this.serverTimeUpdated;

			return this.serverTime + diff;
		};

		this.runStatusCheck = function () {
			this.lastStateChange = Date.now();
			this.statusCheck();
		};

		this.serverResponse = function (data) {
			this.setSettings(data.settings);
			this.scheduleNextStatusCheck();
			return this.setUser(data.user);
		};

		this.statusCheck = function () {
			if ($cookies.get('uid') && $cookies.get('pass')) {
				this.timeSinceLastCheck = Date.now() - this.lastStateChange;
				StatusResource.get({timeSinceLastCheck: this.timeSinceLastCheck}, (data) => {
					this.serverResponse(data);
				}, (response) => {
					if (response.status === 401) {
						this.logout();
					} else {
						this.scheduleNextStatusCheck();
					}
				});
			} else if ($location.$$path.substring(1,7) !== 'login' && $location.$$path.substring(1,7) !== 'signup' && $location.$$path.substring(1,8) !== 'recover') {
				this.logout();
			}
		};

		this.scheduleNextStatusCheck = function (reset) {
			if (reset && this.lastInterval <= 5000) {
				return;
			}
			$timeout.cancel(this.activeTimer);
			var timeSinceLastTime = Date.now() - this.lastStateChange;
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
				this.activeTimer = $timeout(this.statusCheck.bind(this), interval);
			}

			this.lastInterval = interval;
		};

		$rootScope.$on('$stateChangeSuccess', (event, toState) => {
			this.lastStateChange = Date.now();
			if (this.user !== undefined) {
				this.scheduleNextStatusCheck(true);
			}
			if (this.deferred.promise.$$state.status && this.user === null && toState.name !== 'login' && toState.name !== 'recover' && toState.name !== 'signup') {
				event.preventDefault();
				$state.go('login');
			}
		});

		this.getCategoryFilter = function () {
			if (!this.user || !this.user['notifs']) {
				return [];
			}
			return this.user['notifs'];
		};

		this.readUnreadMessage = function (number) {
			this.user['newMessages'] -= number || 1;
		};

		this.readUnreadNews = function () {
			this.user['unreadFlashNews'] = 0;
		};

		this.readUnreadTorrentComments = function () {
			this.user['unreadTorrentComments'] = 0;
		};

		this.readUnreadAdminMessage = function () {
			this.user['newAdminMessages'] -= 1;
		};

		this.readNewReports = function () {
			this.user['newReports'] -= 1;
		};

		this.getUser = function () {
			return this.user;
		};

		this.runStatusCheck();
	}

})();
