(function(){
	'use strict';

	angular
		.module('app.requests')
		.controller('RequestController', RequestController);

	function RequestController($translate, $state, $stateParams, DeleteDialog, ErrorDialog, $uibModal, RequestsResource, $location, $anchorScroll, $timeout, user) {

		this.currentUser = user;
		this.postStatus = 0;
		this.editObj = {
			id: null,
			text: ''
		};

		this.getRequestData = function () {
			RequestsResource.Requests.get({ id: $stateParams.id }).$promise
				.then((data) => {
					this.request = data.request;
					this.votes = data.votes;
					this.movieData = data.movieData;
				})
				.catch((error) => {
					this.notFoundMessage = error.data;
				});
		};

		this.getComments = function () {
			RequestsResource.Comments.query({ requestId: $stateParams.id }).$promise
				.then((comments) => {
					this.comments = comments;

					if ($stateParams.scrollTo == 'comments') {
						$location.hash('comments');
						$anchorScroll();
					}
				});
		};

		this.upload = function (request) {
			$state.go('upload', {requestId: request.id, requestName: request.request});
		};

		this.vote = function () {
			RequestsResource.Votes.save({
				id: this.request.id
			}, () => {
				this.getRequestData();
			});
		};

		this.delete = function () {
			DeleteDialog($translate.instant('REQUESTS.DELETE'), $translate.instant('REQUESTS.DELETE_CONFIRM', {name: this.request.request}), true)
				.then((reason) => {
					return RequestsResource.Requests.delete({ id: $stateParams.id, reason: reason }).$promise;
				})
				.then(() => {
					$state.go('requests.requests');
				})
				.catch((error) => {
					ErrorDialog.display(error.data);
				});
		};

		this.giveReward = function () {
			var modalInstance = $uibModal.open({
				animation: true,
				templateUrl: '../app/requests/request-reward-dialog.template.html',
				controller: 'RequestRewardController',
				controllerAs: 'vm',
				backdrop: 'static',
				size: 'sm',
				resolve: {
					request: () => this.request
				}
			});

			modalInstance.result.then(() => {
				this.getRequestData();
			});
		};

		this.addAlert = function (obj) {
			this.alert = obj;
		};

		this.closeAlert = function() {
			this.alert = null;
		};

		this.editPost = function (post) {
			this.editObj = {
				id: post.id,
				text: post.body
			};
		};

		this.abortEdit = function () {
			this.editObj = {
				id: null,
				text: ''
			};
		};

		this.saveEdit = function (post) {
			RequestsResource.Comments.update({
				requestId: $stateParams.id,
				id: post.id,
				postData: this.editObj.text
			}, () => {
				this.abortEdit();
				this.getComments();
			});
		};

		this.createComment = function () {
			this.closeAlert();
			this.postStatus = 1;
			RequestsResource.Comments.save({
				requestId: $stateParams.id,
				data: this.postText
			}, () => {
				this.postText = '';
				this.getComments();
				this.postStatus = 0;
			}, (error) => {
				this.postStatus = 0;
				if (error.data) {
					this.addAlert({ type: 'danger', msg: error.data });
				} else {
					this.addAlert({ type: 'danger', msg: 'Ett fel inträffade' });
				}
			});
		};

		this.quoteComment = function (post) {
			this.postText = ''.concat(this.postText || '') + '[quote=Anonym]' + post.body.replace(/\[quote[\S\s]+?\[\/quote\]/g, '') + '\n[/quote]\n';
			$location.hash('newPost');
			$anchorScroll();
			$timeout(() => {
				var element = document.getElementById('postText');
				if (element) {
					element.focus();
				}
			});
		};

		this.getRequestData();
		this.getComments();

	}
})();
