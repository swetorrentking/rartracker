(function(){
	'use strict';

	angular.module('tracker.controllers')
		.controller('TopicController', function ($scope, $timeout, user, ForumResource, $stateParams, ReportDialog, DeleteDialog, $state, $uibModal, AuthService, $anchorScroll, $location, userClasses) {
			$scope.postStatus = 0;
			$scope.currentUser = user;
			$scope.showAvatars = user['avatars'] === 'yes';
			$scope.itemsPerPage = user['postsperpage'] === 0 ? 15 : user['postsperpage'];
			$scope.currentPage = $stateParams.page || 1;

			var isoDate = new Date(AuthService.getServerTime()).toISOString();
			var currentDatetime = isoDate.replace(/T/g, ' ').replace(/Z/g, '');

			$scope.post = {
				id: '?',
				added: currentDatetime,
				editedat: '0000-00-00 00:00:00',
				user: user
			};

			$scope.editObj = {
				id: null,
				text: ''
			};
			var firstload = true;

			var fetchPosts = function (scrollToLastPost, dontClear) {
				if (!dontClear) {
					$scope.posts = null;
				}
				var index = $scope.currentPage * $scope.itemsPerPage - $scope.itemsPerPage || 0;
				ForumResource.Posts.query({
					forumid: $stateParams.forumid,
					topicid: $stateParams.id,
					limit: $scope.itemsPerPage,
					index: index,
				}, function (posts, responseHeaders) {
					var headers = responseHeaders();
					$scope.totalItems = headers['x-total-count'];
					$scope.numberOfPages = Math.ceil($scope.totalItems/$scope.itemsPerPage);
					$scope.posts = posts;
					if ($location.hash().indexOf('post') > -1) {
						$timeout(function () {
							$anchorScroll();
						}, 100);
					}
					if (firstload) {
						firstload = false;
						$scope.currentPage = $stateParams.page || 1;
					}
					if (scrollToLastPost) {
						if ($scope.currentPage != $scope.numberOfPages) {
							$scope.currentPage = $scope.numberOfPages;
							$scope.pageChanged();
							return;
						}
						$scope.gotoAnchor(posts[posts.length - 1].id);
					}
				});
			};

			$scope.$parent.activatePostView();

			$scope.SavePost = function () {
				$scope.closeAlert();
				$scope.postStatus = 1;
				ForumResource.Posts.save({
					forumid: $stateParams.forumid,
					topicid: $stateParams.id,
					body: $scope.post.body,
					quote: $scope.post.quote
				}, function () {
					$scope.post.body = '';
					$scope.postStatus = 0;
					$scope.post.quote = null;
					fetchPosts(true, true);
				}, function (error) {
					$scope.postStatus = 0;
					if (error.data) {
						$scope.addAlert({ type: 'danger', msg: error.data });
					} else {
						$scope.addAlert({ type: 'danger', msg: 'Ett fel inträffade' });
					}
				});
			};

			$scope.pageChanged = function () {
				if (firstload) return;
				$state.transitionTo('forum.topic', { page: $scope.currentPage, forumid: $stateParams.forumid, id: $stateParams.id, }, { notify: false });
				fetchPosts();
			};

			$scope.addAlert = function (obj) {
				$scope.alert = obj;
			};

			$scope.closeAlert = function() {
				$scope.alert = null;
			};

			$scope.gotoAnchor = function(x) {
				var newHash = 'post' + x;
				if ($location.hash() !== newHash) {
					$location.hash('post' + x).replace();
				} else {
					$anchorScroll();
				}
			};

			$scope.QuotePost = function (post) {
				$scope.post.body = ''.concat($scope.post.body || '') + '[quote=' + post.user.username + ']' + post.body.replace(/\[quote[\S\s]+?\[\/quote\]/g, '') + '\n[/quote]\n';
				$scope.post.quote = post.user.id;
				$location.hash('newPost');
				$anchorScroll();
				$timeout(function() {
					var element = document.getElementById('postText');
					if (element) {
						element.focus();
					}
				});
			};

			$scope.EditPost = function (post) {
				$scope.editObj = {
					id: post.id,
					text: post.body
				};
			};

			$scope.AbortEdit = function () {
				$scope.editObj = {
					id: null,
					text: ''
				};
			};

			$scope.SaveEdit = function (post) {
				ForumResource.Posts.update({
					forumid: $stateParams.forumid,
					topicid: $stateParams.id,
					id: post.id,
					postData: $scope.editObj.text
				}, function () {
					$scope.AbortEdit();
					fetchPosts(false, true);
				});
			};

			$scope.deleteTopic = function (){
				var dialog = DeleteDialog('Radera tråd', 'Vill du radera tråden?', false);

				dialog.then(function () {
					ForumResource.Topics.delete({
						forumid: $stateParams.forumid,
						id: $stateParams.id,
					}, function () {
						$state.go('forum.topics', {id: $stateParams.forumid});
					});
				});
			};

			$scope.DeletePost = function (post){
				var dialog = DeleteDialog('Radera inlägg', 'Vill du radera foruminlägget?', false);

				dialog.then(function () {
					ForumResource.Posts.delete({
						forumid: $stateParams.forumid,
						topicid: $stateParams.id,
						id: post.id
					}, function () {
						var index = $scope.posts.indexOf(post);
						$scope.posts.splice(index, 1);
					});
				});
			};

			$scope.report = function (post) {
				new ReportDialog('post', post.id, post.body);
			};

			fetchPosts($stateParams.lastpost === 1);

			/* Generate a nice drop-down for selecting forums in the admin-panel */
			if (user.class >= userClasses.STAFF.id) {
				ForumResource.Forums.query({}, function (forums) {
					forums = Array.prototype.slice.call(forums);
					var subforums = [];
					forums.forEach(function(forums) {
						forums.forums = forums.forums.map(function(forum) {
							forum.forumhead = forums.name;
							return forum;
						});
						subforums = subforums.concat(forums.forums);
					});
					$scope.forums = subforums;
				});
			}
		});
})();