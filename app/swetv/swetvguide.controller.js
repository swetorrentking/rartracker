(function(){
	'use strict';

	angular
		.module('app.swetv')
		.controller('SweTvGuideController', SweTvGuideController);

	function SweTvGuideController(TorrentsResource, user, $state, $stateParams) {

		this.lastBrowseDate = user['last_tvbrowse'];
		this.currentPage = $stateParams.page;

		this.rowFilter = function (data) {
			var rows = [];

			var slices = [2,2,2,2];
			slices.forEach((s) => {
				rows.push(data.splice(0, s));
			});

			return rows;
		};

		this.getReleases = function () {
			$state.go($state.current.name, { page: this.currentPage }, { notify: false });
			var week = this.currentPage - 1;
			TorrentsResource.SweTvGuide.query({'week': week}, (tvData) => {
				this.tvDataRow = this.rowFilter(tvData);
			});
		};

		this.pageChanged = function () {
			this.getReleases();
		};

		this.getReleases();

	}

})();