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
			template: `<button ng-class="{'disabled': vm.watching}" class="btn btn-default btn-xs" ng-click="vm.addWatch()"><i class="fa fa-eye"></i> Bevakning</button> <i ng-bind-html="vm.getWatchInformation() | bbCode"></i>`,
			controller: WatchButtonController,
			controllerAs: 'vm'
		});

	function WatchButtonController(authService, WatchDialog, UsersResource, categories) {

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
			var string = 'Du bevakar';
			if (this.watching.typ === 1) {
				string += ' nya avsnitt av denna serien';
			} else {
				string += ' denna film';
			}
			var format;
			if (typeof this.watching.format === 'number') {
				format = [this.watching.format];
			} else {
				format = this.watching.format.split(',');
			}

			var formats = format.map(cat => this.getCatName(cat)).join(', ');
			string += ' i formaten [b]' + formats + '[/b]';

			if (this.watching.swesub === true) {
				string += ' med [b]Svensk text[/b]';
			}
			string += '.';
			return string;
		};

	}

})();
