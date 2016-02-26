(function(){
	'use strict';

	angular
		.module('app.suggestions')
		.controller('CreateSuggestionController', CreateSuggestionController);

	function CreateSuggestionController($uibModalInstance, SuggestionsResource) {

		this.create = function () {
			this.closeAlert();
			SuggestionsResource.Suggest.save({}, this.suggestion).$promise
				.then((result) => {
					$uibModalInstance.close(result);
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