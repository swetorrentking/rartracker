(function(){
	'use strict';

	angular
		.module('app.shared')
		.controller('TopTorrentsController', TopTorrentsController);

	function TopTorrentsController(TorrentsResource) {

		TorrentsResource.Torrents.get({id: 'toplists'}, (toplists) => {
			this.toplists = toplists;
		});

	}

})();