(function(){
	'use strict';

	angular
		.module('app.shared')
		.controller('TorrentController', TorrentController);

	function TorrentController($scope, $state, $timeout, $translate, $stateParams, user, DeleteDialog, ConfirmDialog, SubtitlesResource, WatchingSubtitlesResource, uploadService, ErrorDialog, TorrentsResource, ReseedRequestsResource, $anchorScroll, $location) {

		this.torrentId = $stateParams.id;
		this.currentUser = user;
		this.itemsPerPage = 10;
		this.postStatus = 0;
		this.editObj = {
			id: null,
			text: ''
		};

		if ($stateParams.uploaded) {
			this.uploaded = true;
		}

		TorrentsResource.TorrentsMulti.get({id: $stateParams.id}, (torrent) => {
			this.torrent = torrent.torrent;
			this.movieData = torrent.movieData;
			this.relatedTorrents = torrent.relatedTorrents;
			this.packContent = torrent.packContent;
			this.tvChannel = torrent.tvChannel;
			this.subtitles = torrent.subtitles;
			this.request = torrent.request;
			this.watchingSubtitle = torrent.watchSubtitles;
			if ($stateParams.scrollTo == 'seeders') {
				this.togglePeers('seeders');
			} else if ($stateParams.scrollTo == 'leechers') {
				this.togglePeers('leechers');
			}
		}, (error) => {
			this.notFoundMessage = error.data;
		});

		this.loadComments = function (scrollToLastPost) {
			var index = this.currentPage * this.itemsPerPage - this.itemsPerPage || 0;
			TorrentsResource.Comments.query({
				id: $stateParams.id,
				limit: this.itemsPerPage,
				index: index,
			}, (comments, responseHeaders) => {
				var headers = responseHeaders();
				this.totalItems = headers['x-total-count'];
				this.numberOfPages = Math.ceil(this.totalItems/this.itemsPerPage);
				this.comments = comments;
				if (scrollToLastPost) {
					if (this.currentPage != this.numberOfPages) {
						this.currentPage = this.numberOfPages;
						this.loadComments(true);
						return;
					}
					this.gotoPostAnchor(comments[comments.length - 1].id);
				}
				if (!this.hasLoadedComments && $stateParams.scrollTo == 'comments') {
					this.gotoAnchor('comments');
					this.hasLoadedComments = true;
				}
			});
		};

		this.toggleFiles = function () {
			this.showFiles = !this.showFiles;
			if (!this.files) {
				TorrentsResource.Files.query({id: $stateParams.id}, (files) => {
					this.files = files;
				});
			}
		};

		this.togglePeers = function (scrollTo) {
			this.showPeers = !this.showPeers;
			if (!this.seeders && !this.leechers) {
				TorrentsResource.Peers.get({id: $stateParams.id}, (peers) => {
					this.seeders = peers.seeders;
					this.leechers = peers.leechers;
					if (scrollTo) {
						this.gotoAnchor(scrollTo);
					}
				});
			}
		};

		this.toggleSnatchLog = function () {
			this.showSnatchLog = !this.showSnatchLog;
			if (!this.snatchLog) {
				TorrentsResource.Snatchlog.query({id: $stateParams.id}, (snatchLog) => {
					this.snatchLog = snatchLog;
				});
			}
		};

		this.addAlert = function (obj) {
			this.alert = obj;
		};

		this.closeAlert = function() {
			this.alert = null;
		};

		this.savePost = function () {
			this.closeAlert();
			this.postStatus = 1;
			TorrentsResource.Comments.save({
				id: $stateParams.id,
				data: this.postText
			}, () => {
				this.postText = '';
				this.loadComments(true);
				this.postStatus = 0;
			}, (error) => {
				this.postStatus = 0;
				if (error.data) {
					this.addAlert({ type: 'danger', msg: error.data });
				} else {
					this.addAlert({ type: 'danger', msg: $translate.instant('ERROR_OCCURED')});
				}
			});
		};

		this.quotePost = function (post) {
			this.postText = ''.concat(this.postText || '') + '[quote=' + post.user.username + ']' + post.body.replace(/\[quote[\S\s]+?\[\/quote\]/g, '') + '\n[/quote]\n';
			$location.hash('newPost');
			$anchorScroll();
			$timeout(function() {
				var element = document.getElementById('postText');
				if (element) {
					element.focus();
				}
			});
		};

		this.addSubtitleWatch = function () {
			WatchingSubtitlesResource.save({torrentid: this.torrent.id}).$promise
				.then(() => {
					this.watchingSubtitle = true;
				});
		};

		this.gotoPostAnchor = function (x) {
			this.gotoAnchor('post' + x);
		};

		this.gotoAnchor = function (newHash) {
			if ($location.hash() !== newHash) {
				$location.hash(newHash).replace();
			}
			$anchorScroll();
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
			TorrentsResource.Comments.update({
				id: $stateParams.id,
				commentId: post.id,
				postData: this.editObj.text
			}, () => {
				this.abortEdit();
				this.loadComments();
			});
		};

		this.deleteSubtitle = function (subtitle){
			DeleteDialog($translate.instant('SUBTITLES.DELETE_TOPIC'), $translate.instant('SUBTITLES.DELETE_BODY'), true)
				.then((reason) => {
					SubtitlesResource.delete({id: subtitle.id, reason: reason}, () => {
						var index = this.subtitles.indexOf(subtitle);
						this.subtitles.splice(index, 1);
					});
				});
		};

		this.deleteComment = function (comment){
			DeleteDialog($translate.instant('COMMENTS.DELETE_TOPIC'), $translate.instant('COMMENTS.DELETE_BODY'), true)
				.then(() => {
					TorrentsResource.Comments.delete({
						id: this.torrent.id,
						commentId: comment.id,
					}, () => {
						var index = this.comments.indexOf(comment);
						this.comments.splice(index, 1);
					});
				});
		};

		this.requestReseed = function () {
			ConfirmDialog($translate.instant('TORRENTS.REQUEST_SEED'), $translate.instant('TORRENTS.REQUEST_SEED_DIALOG_BODY'))
				.then(() => {
					return ReseedRequestsResource.save({torrentid: this.torrent.id}).$promise;
				})
				.catch((error) => {
					if (error) {
						ErrorDialog.display(error.data);
					}
				});
		};

		this.subtitleFileChanged = function () {
 			if (this.subFile.size > 2097152) {
 				ErrorDialog.display($translate.instant('TORRENTS.SUBTITLE_FILE_TOO_LARGE'));
 				return;
 			}

 			var params = {
				url: '/api/v1/subtitles',
				data: {
					torrentid:	this.torrent.id,
					quality: 	this.subtitleQuality,
					file:			this.subFile,
				}
			};

			uploadService.uploadFile(params)
				.then(() => {
					this.showSubtitleUpload = !this.showSubtitleUpload;
					SubtitlesResource.query({torrentid: this.torrent.id}, (subtitles) => {
						this.subtitles = subtitles;
					});
				}, (error) => {
					ErrorDialog.display(error);
				});
	 	};

	 	this.multiDelete = function () {
	 		this.deletingPackFiles = true;
	 		TorrentsResource.PackFiles.delete({id: this.torrent.id}).$promise
	 			.then(function () {
	 				$state.reload();
	 			});
	 	};

		this.loadComments(false);

	}

})();
