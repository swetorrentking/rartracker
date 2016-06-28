(function(){
	'use strict';

	angular
		.module('app.torrentLists')
		.component('torrentListInfo', {
			bindings: {
				torrentList: '<',
			},
			template: `
				<table style="margin: auto;" ng-if="::$ctrl.torrentList">
					<tr>
						<td>
							<img ng-if="::$ctrl.torrentList.imdbid2" ng-src="/img/imdb/{{ ::$ctrl.torrentList.imdbid2 }}.jpg" style="width: 180px; margin-right: 50px;" />
							<div ng-if="::!$ctrl.torrentList.imdbid2" style="display: flex; align-items: center; justify-content: center; width: 180px; height: 250px; margin-right:50px;"><i class="fa fa-list" style="font-size: 160px; color: #e0e0e0;"></i></div>
						</td>
						<td>
							<h1><a href="" ui-sref="torrent-lists.torrent-list({ id: $ctrl.torrentList.id, slug: $ctrl.torrentList.slug})">{{ ::$ctrl.torrentList.name }}</a></h1>

							<p style="font-style: italic; max-width: 500px; font-size: 14px;" ng-bind-html="::$ctrl.torrentList.description | nltobr"></p>

							{{ 'LIST.CREATED_BY' | translate }} <user user="::$ctrl.torrentList.user"></user>
						</td>
					</tr>
				</table>
			`
		});

})();
