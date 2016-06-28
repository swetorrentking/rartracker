(function(){
	'use strict';

	angular
		.module('app.admin')
		.controller('BannedController', BannedController);

	function BannedController($state, $stateParams, AdminResource, ErrorDialog) {

		this.initForm = function () {
			this.addnonscene = {
				whitelist: 1,
				comment: ''
			};
		};

		this.loadData = function () {
			AdminResource.Nonscene.query().$promise
				.then((data) => {
					this.nonscene = data;
				});
		};

		this.delete = function (item) {
			AdminResource.Nonscene.delete({ id: item.id }, () => {
				var index = this.nonscene.indexOf(item);
				this.nonscene.splice(index, 1);
			});
		};

		this.create = function () {
			AdminResource.Nonscene.save(this.addnonscene).$promise
				.then(() => {
					this.initForm();
					this.loadData();
				})
				.catch((error) => {
					ErrorDialog.display(error.data);
				});
		};

		this.loadData();
		this.initForm();

	}

})();
