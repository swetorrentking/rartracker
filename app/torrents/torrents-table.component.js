(function(){
	'use strict';

	angular
		.module('app.shared')
		.component('torrentsTable', {
			bindings: {
				torrents: '<',
				lastBrowseDate: '<',
				searchText: '<',
				onDelete: '&',
				onSort: '&',
				onFilterCategory: '&',
				checkMode: '<',
				showHeader: '@',
				colDownload: '@',
				colBookmark: '@',
				colComments: '@',
				colDate: '@',
				colSize: '@',
				colTimesCompleted: '@',
				colSeeders: '@',
				colLeechers: '@',
				colData: '@',
				colIndex: '@',
				colDelete: '@',
				colCheck: '@',
			},
			templateUrl: '../app/torrents/torrents-table.component.template.html',
			controller: TorrentsTableController,
			controllerAs: 'vm'
		});

	function TorrentsTableController() {

		this.checkAll = function () {
			var torrents = Array.prototype.slice.call(this.torrents);
			if (torrents.length === 0) {
				return;
			}

			if (!torrents[0].selected || torrents[0].selected === 'no') {
				torrents.forEach((torrent) => {
					torrent.selected = 'yes';
				});
			} else {
				torrents.forEach((torrent) => {
					torrent.selected = 'no';
				});
			}
		};

	}

})();
