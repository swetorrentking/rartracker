(function(){
	'use strict';

	angular
		.module('app.shared')
		.component('watchButton', {
			bindings: {
				torrentId: '<',
				torrentCategory: '<',
				movieData: '<'
			},
			template: `<button ng-class="{'disabled': vm.watching}" class="btn btn-default btn-xs" ng-click="vm.addWatch()"><i class="fa fa-eye"></i> {{ 'WATCHER.TITLE' | translate }}</button> <i ng-bind-html="vm.getWatchInformation() | bbCode"></i>`,
			controller: WatchButtonController,
			controllerAs: 'vm'
		});

	function WatchButtonController($translate, authService, WatchDialog, UsersResource, categories) {

		this.$onInit = function () {
			this.user = authService.getUser();
			UsersResource.Watch.get({ id: this.user.id, imdbId: this.movieData.id }).$promise
				.then((data) => {
					this.watching = data;
				});
		};

		this.addWatch = function () {
			this.movieData.category = this.torrentCategory;
			WatchDialog(this.movieData)
				.then(() => {
					return UsersResource.Watch.get({ id: this.user.id, imdbId: this.movieData.id }).$promise;
				})
				.then((watching) => {
					this.watching = watching;
				});
		};

		this.getCatName = function (id) {
			for (var cat in categories) {
				if (categories[cat].id == id) {
					return categories[cat].text;
				}
			}
			return '-';
		};

		this.getWatchInformation = function () {
			if (!this.watching) {
				return;
			}
			var string = $translate.instant('WATCHER.WATCHING_STR_1');
			if (this.watching.typ === 1) {
				string += $translate.instant('WATCHER.WATCHING_NEW_TV');
			} else {
				string += $translate.instant('WATCHER.WATCHING_MOVIE');
			}
			var format;
			if (typeof this.watching.format === 'number') {
				format = [this.watching.format];
			} else {
				format = this.watching.format.split(',');
			}

			var formats = format.map(cat => this.getCatName(cat)).join(', ');
			string += $translate.instant('WATCHER.WATCHING_FORMATS', {formats: formats});

			if (this.watching.swesub === true) {
				string += $translate.instant('WATCHER.WATCHING_WITH_SUBS');
			}
			string += '.';
			return string;
		};

	}

})();
