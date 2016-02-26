(function(){
	'use strict';

	angular
		.module('app.shared')
		.component('categoriesCheckboxes', {
			bindings: {
				userCategories: '=',
				limitToCategories: '=',
				changed: '&'
			},
			template: `<span ng-repeat="cat in vm._categories">
							<button style="margin-bottom: 2px;" class="btn btn-default btn-xs" ng-model="cat.checked" ng-change="vm.updateModel()" uib-btn-checkbox>{{ ::cat.text }}</button>
						</span>`,
			controller: CategoriesCheckboxesController,
			controllerAs: 'vm'
		});

	function CategoriesCheckboxesController($scope, $timeout, categories) {

		this._categories = [];

		$scope.$watchCollection(() => this.userCategories, () => {
			if (!this.userCategories) return;

			var catArray = [];
			for (var c in categories) {
				var cat = categories[c];

				if (this.limitToCategories !== undefined && this.limitToCategories.length > 0 && !this.findCategoryInLimitedCategories(cat)) {
					continue;
				}

				if (this.userCategories.indexOf(cat.id) > -1) {
					cat.checked = true;
				} else {
					cat.checked = false;
				}
				catArray.push(cat);
			}

			var numberChecked = catArray.filter(cat => cat.checked).length;
			if (numberChecked === Object.keys(categories).length || this.limitToCategories !== undefined && numberChecked === this.limitToCategories.length) {
				catArray = catArray.map((cat) => {
					cat.checked = false;
					return cat;
				});
			}

			this._categories = catArray;
			this.updateOut();
		});

		this.findCategoryInLimitedCategories = function (cat) {
			return !!this.limitToCategories.some(category => category.id === cat.id);
		};

		this.updateModel = function () {
			this.updateOut();
			$timeout(() => {
				this.changed();
			});
		};

		/* For user profile */
		this.updateOut = function () {
			this.userCategories = this._categories
				.filter(cat => cat.checked)
				.map(cat => cat.id);

			if (this.userCategories.length === 0 && this.limitToCategories) {
				this.userCategories = this.limitToCategories.map(cat => cat.id);
			}
		};

	}

})();
