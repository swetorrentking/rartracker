(function(){
	'use strict';

	angular
		.module('app.shared')
		.controller('RssController', RssController);

	function RssController(configs, categories, user) {
		var url = configs.SITE_URL + '/api/v1/rss';

		this.settings = {
			url: url,
			setting: 0,
			categories: [],
			p2p: 0,
		};

		this.update = function () {
			var params = [];
			var cats = this.settings.categories.filter(cat => !!cat.checked);
			if (cats.length > 0) {
				cats = cats.map(cat => cat.id);
				params.push('cat=' + cats.join(','));
			}
			if (this.settings.setting > 0) {
				params.push('s=' + this.settings.setting);
			}
			if (this.settings.p2p) {
				params.push('p2p=' + 1);
			}
			params.push('passkey=' + user['passkey']);
			this.settings.url = url + '?' + params.join('&');
		};

		var catArray = [];
		for (var c in categories) {
			catArray.push(categories[c]);
		}

		this.settings.categories = catArray;
		this.update();
	}

})();
