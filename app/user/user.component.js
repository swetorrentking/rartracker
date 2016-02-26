(function(){
	'use strict';

	angular
		.module('app.shared')
		.component('user', {
			bindings: {
				user: '<',
				showIcons: '@',
				showLink: '@',
				showClass: '@',
				iconSize: '@',
				link: '@'
			},
			templateUrl: '../app/user/user.component.template.html',
			controllerAs: 'vm'
		});

})();
