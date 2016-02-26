(function(){
	'use strict';

	angular
		.module('app.shared')
		.component('friendList', {
			bindings: {
				friends: '=',
				showSendMessage: '@',
				delete: '&'
			},
			templateUrl: '../app/friends/friend-list.component.template.html',
			controller: FriendListController,
			controllerAs: 'vm'
		});

	function FriendListController(SendMessageDialog, authService) {
		this.currentUser = authService.getUser();
		this.sendMessage = function (receiver) {
			new SendMessageDialog({ user: receiver });
		};

	}

})();
