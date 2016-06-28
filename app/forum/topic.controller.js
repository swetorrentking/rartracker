(function(){
	'use strict';

	angular
		.module('app.forums')
		.controller('TopicController', TopicController);

	function TopicController($scope, $timeout, $translate, user, ForumResource, $stateParams, ErrorDialog, DeleteDialog, $state, $uibModal, authService, $anchorScroll, $location) {

		var firstload = true;
		this.postStatus = 0;
		this.currentUser = user;
		this.showAvatars = user['avatars'] === 'yes';
		this.itemsPerPage = user['postsperpage'] === 0 ? 15 : user['postsperpage'];
		this.currentPage = $stateParams.page;

		var isoDate = new Date(authService.getServerTime()).toISOString();
		var currentDatetime = isoDate.replace(/T/g, ' ').replace(/Z/g, '');

		this.post = {
			id: '?',
			added: currentDatetime,
			editedat: '0000-00-00 00:00:00',
			user: user
		};

		this.editObj = {
			id: null,
			text: ''
		};

		this.fetchPosts = function (scrollToLastPost, replace) {
			$state.go('.', { page: this.currentPage }, { notify: false, location: replace ? 'replace' : true });
			this.posts = null;
			var index = this.currentPage * this.itemsPerPage - this.itemsPerPage || 0;
			ForumResource.Posts.query({
				forumid: $stateParams.forumid,
				topicid: $stateParams.id,
				limit: this.itemsPerPage,
				index: index,
			}, (posts, responseHeaders) => {
				var headers = responseHeaders();
				this.totalItems = headers['x-total-count'];
				this.numberOfPages = Math.ceil(this.totalItems/this.itemsPerPage);
				this.posts = posts;
				if ($location.hash().indexOf('post') > -1) {
					$timeout(function () {
						$anchorScroll();
					}, 100);
				}
				if (firstload) {
					firstload = false;
					this.currentPage = $stateParams.page || 1;
				}
				if (scrollToLastPost) {
					if (this.currentPage != this.numberOfPages) {
						this.currentPage = this.numberOfPages;
						this.fetchPosts(true, true);
						return;
					}
					this.gotoAnchor(posts[posts.length - 1].id);
				}
			});
		};

		this.savePost = function () {
			this.closeAlert();
			this.postStatus = 1;
			ForumResource.Posts.save({
				forumid: $stateParams.forumid,
				topicid: $stateParams.id,
				body: this.post.body,
				quote: this.post.quote
			}, () => {
				this.post.body = '';
				this.postStatus = 0;
				this.post.quote = null;
				this.fetchPosts(true);
			}, (error) => {
				this.postStatus = 0;
				if (error.data) {
					this.addAlert({ type: 'danger', msg: error.data });
				} else {
					this.addAlert({ type: 'danger', msg: $translate.instant('GENERAL.ERROR_OCCURED') });
				}
			});
		};

		this.addAlert = function (obj) {
			this.alert = obj;
		};

		this.closeAlert = function() {
			this.alert = null;
		};

		this.gotoAnchor = function(x) {
			var newHash = 'post' + x;
			if ($location.hash() !== newHash) {
				$location.hash('post' + x).replace();
			} else {
				$anchorScroll();
			}
		};

		this.quotePost = function (post) {
			this.post.body = ''.concat(this.post.body || '') + '[quote=' + post.user.username + ']' + post.body.replace(/\[quote[\S\s]+?\[\/quote\]/g, '') + '\n[/quote]\n';
			this.post.quote = post.user.id;
			$location.hash('newPost');
			$anchorScroll();
			$timeout(function() {
				var element = document.getElementById('postText');
				if (element) {
					element.focus();
				}
			});
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
			ForumResource.Posts.update({
				forumid: $stateParams.forumid,
				topicid: $stateParams.id,
				id: post.id,
				postData: this.editObj.text
			}, () => {
				this.abortEdit();
				this.fetchPosts(false);
			});
		};

		this.deletePost = function (post){
			DeleteDialog($translate.instant('FORUM.DELETE_POST'), $translate.instant('FORUM.DELETE_POST_CONFIRM'), false)
				.then(() => {
					return ForumResource.Posts.delete({
						forumid: $stateParams.forumid,
						topicid: $stateParams.id,
						id: post.id
					}).$promise;
				})
				.then(() => {
					var index = this.posts.indexOf(post);
					this.posts.splice(index, 1);
				})
				.catch((error) => {
					ErrorDialog.display(error.data);
				});
		};

		$scope.$parent.vm.activatePostView();
		this.fetchPosts($stateParams.lastpost === 1);
	}

})();
