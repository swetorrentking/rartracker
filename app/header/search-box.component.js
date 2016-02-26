(function(){
	'use strict';

	angular
		.module('app.shared')
		.component('searchBox', {
			templateUrl: '../app/header/search-box.component.template.html',
			controller: SearchBoxController,
			controllerAs: 'vm'
		});

	function SearchBoxController($state, $rootScope) {

		this.searchText = '';
		this.extendedSearch = false;

		this.broadcast = function () {
			$rootScope.$broadcast('doSearch', {searchText: this.searchText, extended: this.extendedSearch});
		};

		this.doSearch = function () {
			if ($state.current.name !== 'search') {
				$state.go('search', {search: this.searchText, extended: this.extendedSearch});
			}
			this.broadcast();
		};

		this.changeExtendedSearch = function () {
			this.broadcast();
		};

		$rootScope.$on('$stateChangeStart',  (event, toState) => {
			if (toState.name !== 'search') {
				this.searchText = '';
				this.loadingSearch = false;
			}
		});

		$rootScope.$on('updateSearchOptions',  (event, options) => {
			this.searchText = options.searchText;
			this.extendedSearch = !!options.extended;
		});

	}

})();
