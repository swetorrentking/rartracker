(function(){
	'use strict';

	angular
		.module('app.admin')
		.controller('NewsAdminController', NewsAdminController);

	function NewsAdminController($uibModalInstance, NewsResource, news) {
		if (news) {
			this.news = news;
		} else {
			this.news = {
				announce: 0
			};
		}

		this.create = function () {
			var promise;
			this.closeAlert();
			if (this.news.id) {
				promise = NewsResource.update({id: this.news.id}, this.news).$promise;
			} else {
				promise = NewsResource.save({}, this.news).$promise;
			}

			promise
				.then((result) => {
					$uibModalInstance.close(result);
				}, (error) => {
					addAlert({ type: 'danger', msg: error.data });
				});
		};

		this.cancel = function () {
			$uibModalInstance.dismiss();
		};

		var addAlert = function (obj) {
			this.alert = obj;
		};

		this.closeAlert = function() {
			this.alert = null;
		};

	}
})();
