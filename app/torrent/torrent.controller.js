(function(){
	'use strict';

	angular.module('tracker.controllers')
		.controller('TorrentController', function ($scope, $state, $http, $timeout, $stateParams, ReportDialog, WatchDialog, categories, user, UsersResource, DeleteDialog, ConfirmDialog, SubtitlesResource, WatchingSubtitlesResource, UploadService, ErrorDialog, BookmarksResource, TorrentsResource, ReseedRequestsResource, $anchorScroll, $location) {
			$scope.itemsPerPage = 15;
			$scope.postStatus = 0;
			$scope.editObj = {
				id: null,
				text: ''
			};

			if ($stateParams.uploaded) {
				$scope.uploaded = true;
			}

			TorrentsResource.TorrentsMulti.get({id: $stateParams.id}, function (torrent) {
				$scope.torrent = torrent.torrent;
				$scope.movieData = torrent.movieData;
				$scope.relatedTorrents = torrent.relatedTorrents;
				$scope.packContent = torrent.packContent;
				$scope.tvChannel = torrent.tvChannel;
				$scope.subtitles = torrent.subtitles;
				$scope.watching = torrent.watching;
				$scope.request = torrent.request;
				$scope.watchingSubtitle = torrent.watchSubtitles;
				if ($stateParams.scrollTo == 'seeders') {
					$scope.togglePeers('seeders');
				} else if ($stateParams.scrollTo == 'leechers') {
					$scope.togglePeers('leechers');
				}
			}, function (error) {
				$scope.notFoundMessage = error.data;
			});

			var loadComments = function (scrollToLastPost, firstload) {
				var index = $scope.currentPage * $scope.itemsPerPage - $scope.itemsPerPage || 0;
				TorrentsResource.Comments.query({
					id: $stateParams.id,
					limit: $scope.itemsPerPage,
					index: index,
				}, function (comments, responseHeaders) {
					var headers = responseHeaders();
					$scope.totalItems = headers['x-total-count'];
					$scope.numberOfPages = Math.ceil($scope.totalItems/$scope.itemsPerPage);
					$scope.comments = comments;
					if (scrollToLastPost) {
						if ($scope.currentPage != $scope.numberOfPages) {
							$scope.currentPage = $scope.numberOfPages;
							loadComments(true);
							return;
						}
						$scope.gotoPostAnchor(comments[comments.length - 1].id);
					}
					if (firstload && $stateParams.scrollTo == 'comments') {
						gotoAnchor('comments');
					}
				});
			};

			$scope.toggleFiles = function () {
				$scope.showFiles = !$scope.showFiles;
				if (!$scope.files) {
					TorrentsResource.Files.query({id: $stateParams.id}, function (files) {
						$scope.files = files;
					});
				}
			};

			$scope.togglePeers = function (scrollTo) {
				$scope.showPeers = !$scope.showPeers;
				if (!$scope.seeders && !$scope.leechers) {
					TorrentsResource.Peers.get({id: $stateParams.id}, function (peers) {
						$scope.seeders = peers.seeders;
						$scope.leechers = peers.leechers;
						if (scrollTo) {
							gotoAnchor(scrollTo);
						}
					});
				}
			};

			$scope.toggleSnatchLog = function () {
				$scope.showSnatchLog = !$scope.showSnatchLog;
				if (!$scope.snatchLog) {
					TorrentsResource.Snatchlog.query({id: $stateParams.id}, function (snatchLog) {
						$scope.snatchLog = snatchLog;
					});
				}
			};

			$scope.addAlert = function (obj) {
				$scope.alert = obj;
			};

			$scope.closeAlert = function() {
				$scope.alert = null;
			};

			$scope.pageChanged = function () {
				loadComments();
			};

			$scope.savePost = function () {
				$scope.closeAlert();
				$scope.postStatus = 1;
				TorrentsResource.Comments.save({
					id: $stateParams.id,
					data: $scope.postText
				}, function () {
					$scope.postText = '';
					loadComments(true);
					$scope.postStatus = 0;
				}, function (error) {
					$scope.postStatus = 0;
					if (error.data) {
						$scope.addAlert({ type: 'danger', msg: error.data });
					} else {
						$scope.addAlert({ type: 'danger', msg: 'Ett fel inträffade' });
					}
				});
			};

			$scope.QuotePost = function (post) {
				$scope.postText = ''.concat($scope.postText || '') + '[quote=' + post.user.username + ']' + post.body.replace(/\[quote[\S\s]+?\[\/quote\]/g, '') + '\n[/quote]\n';
				$location.hash('newPost');
				$anchorScroll();
				$timeout(function() {
					var element = document.getElementById('postText');
					if (element) {
						element.focus();
					}
				});
			};

			$scope.addSubtitleWatch = function () {
				WatchingSubtitlesResource.save({torrentid: $scope.torrent.id}).$promise
					.then(function () {
						$scope.watchingSubtitle = true;
					});
			};

			$scope.gotoPostAnchor = function (x) {
				gotoAnchor('post' + x);
			};

			var gotoAnchor = function (newHash) {
				if ($location.hash() !== newHash) {
					$location.hash(newHash).replace();
				}
				$anchorScroll();
			};

			$scope.editPost = function (post) {
				$scope.editObj = {
					id: post.id,
					text: post.body
				};
			};

			$scope.abortEdit = function () {
				$scope.editObj = {
					id: null,
					text: ''
				};
			};

			$scope.saveEdit = function (post) {
				TorrentsResource.Comments.update({
					id: $stateParams.id,
					commentId: post.id,
					postData: $scope.editObj.text
				}, function () {
					$scope.abortEdit();
					loadComments();
				});
			};

			$scope.bookmark = function (torrent) {
				BookmarksResource.save({torrentid: torrent.id});
				torrent.bookmarked = true;
			};

			$scope.deleteSubtitle = function (subtitle){
				var dialog = DeleteDialog('Radera undertext', 'Vill du radera undertexten?', true);

				dialog.then(function (reason) {
					SubtitlesResource.delete({id: subtitle.id, reason: reason}, function () {
						var index = $scope.subtitles.indexOf(subtitle);
						$scope.subtitles.splice(index, 1);
					});
				});
			};

			$scope.addWatch = function () {
				$scope.movieData.category = $scope.torrent.category;
				var watchDialog = WatchDialog($scope.movieData);
				watchDialog.then(function () {
					UsersResource.Watching.query({id: user.id, imdbid: $scope.torrent.imdbid}, function (watching) {
						$scope.watching = watching[0];
					});
					$scope.asyncSelected = '';
				});
			};

			$scope.deleteComment = function (comment){
				var dialog = DeleteDialog('Radera kommentar', 'Vill du radera torrentkommentaren?', false);

				dialog.then(function () {
					TorrentsResource.Comments.delete({
						id: $scope.torrent.id,
						commentId: comment.id,
					}, function () {
						var index = $scope.comments.indexOf(comment);
						$scope.comments.splice(index, 1);
					});
				});
			};

			var getCatName = function (id) {
				for (var cat in categories) {
					if (categories[cat].id == id) {
						return categories[cat].text;
					}
				}
				return '-';
			};

			$scope.getBevakaInformation = function () {
				if (!$scope.watching) {
					return;
				}
				var string = 'Du bevakar';
				if ($scope.watching.typ === 1) {
					string += ' nya avsnitt av denna serien';
				} else {
					string += ' denna film';
				}
				var format;
				if (typeof $scope.watching.format === 'number') {
					format = [$scope.watching.format];
				} else {
					format = $scope.watching.format.split(',');
				}
				
				var formats = format.map(function(cat) { return getCatName(cat);}).join(', ');
				string += ' i formaten [b]' + formats + '[/b]';

				if ($scope.watching.swesub === true) {
					string += ' med [b]Svensk text[/b]';
				}
				string += '.';
				return string;
			};

			$scope.reportTorrent = function () {
				new ReportDialog('torrent', $scope.torrent.id, $scope.torrent.name);
			};

			$scope.reportSubtitle = function (subtitle) {
				new ReportDialog('subtitle', subtitle.id, subtitle.filename);
			};

			$scope.reportComment = function (comment) {
				new ReportDialog('comment', comment.id, comment.body);
			};

			$scope.requestReseed = function () {
				var dialog = ConfirmDialog('Önska seed', 'Vill du önska seed på denna torrent för [b]5p[/b]?\nAlla som har laddat ner eller seedat torrenten det senaste halvåret kommer få ett PM.');

				dialog.then(function () {
					ReseedRequestsResource.save({torrentid: $scope.torrent.id}).$promise
						.catch(function (error) {
							ErrorDialog.display(error.data);
						});
				});
			};

			$scope.$watch('settings.file', function (file) {
		 		if (file) {
		 			if (file.size > 2097152) {
		 				ErrorDialog.display('Filen är för stor. Max 2 MB tack.');
		 				return;
		 			}

		 			var params = {
						url: '/api/v1/subtitles',
						data: {
							torrentid:	$scope.torrent.id,
							file:		file,
						}
					};

					UploadService.uploadFile(params)
						.then(function () {
							$scope.showSubtitleUpload = !$scope.showSubtitleUpload;
							SubtitlesResource.query({torrentid: $scope.torrent.id}, function (subtitles) {
								$scope.subtitles = subtitles;
							});
						}, function (error) {
							ErrorDialog.display(error);
						});
			 	}
		 	});

		 	$scope.multiDelete = function () {
		 		$scope.deletingPackFiles = true;
		 		TorrentsResource.PackFiles.delete({id: $scope.torrent.id}).$promise
		 			.then(function () {
		 				$state.reload();
		 			});
		 	};

			loadComments(false, true);

		});
})();