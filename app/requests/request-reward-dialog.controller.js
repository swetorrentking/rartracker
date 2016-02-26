(function(){
	'use strict';

	angular
		.module('app.requests')
		.controller('RequestRewardController', RequestRewardController);

	function RequestRewardController($uibModalInstance, RequestsResource, request) {

		this.reward = 20;

		this.create = function () {
			this.closeAlert();
			RequestsResource.Votes.save({
				id: request.id,
				reward: this.reward
			}, (response) => {
				$uibModalInstance.close(response);
			}, (error) => {
				this.addAlert({ type: 'danger', msg: error.data });
			});
		};

		this.cancel = function () {
			$uibModalInstance.dismiss();
		};

		this.addAlert = function (obj) {
			this.alert = obj;
		};

		this.closeAlert = function() {
			this.alert = null;
		};

	}

})();