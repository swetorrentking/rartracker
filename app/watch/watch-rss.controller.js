(function(){
	'use strict';

	angular
		.module('app.watcher')
		.controller('WatchRssController', WatchRssController);

	function WatchRssController(user, configs) {

		var url = configs.SITE_URL + '/api/v1/watcher-rss';

		this.model = {
			url: url,
			setting: 0,
			time: false
		};

		this.updateRssUrl = function () {
			var params = [];
			if (this.model.setting > 0) {
				params.push('vad=' + this.model.setting);
			}
			params.push('passkey=' + user['passkey']);
			if (this.model.time) {
				params.push('from=' + Math.floor(Date.now() / 1000));
			}
			this.model.url = url + '?' + params.join('&');
		};

		this.updateRssUrl();
	}

})();