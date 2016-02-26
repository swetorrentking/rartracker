(function(){
	'use strict';

	angular
		.module('app.requests')
		.controller('EditRequestController', EditRequestController);

	function EditRequestController($state, $uibModal, $stateParams, categories, MovieDataResource, RequestsResource) {

		this.loaded = false;
		this.categories = categories;
		this.noimdb = false;
		var imdbLessCategories = [
			categories.TV_SWE.id,
			categories.AUDIOBOOKS.id,
			categories.EBOOKS.id,
			categories.EPAPERS.id,
			categories.MUSIC.id,
		];

		this.requestParams = {
			category: 1,
			imdbUrl: '',
			imdbId: 0,
			season: 0,
			seasons: [],
			comment: '',
		};

		RequestsResource.Requests.get({ id: $stateParams.id }).$promise
			.then((data) => {
				this.requestParams.category = data.request.type;
				this.requestParams.comment = data.request.comment;
				this.requestParams.season = data.request.season;
				this.requestParams.imdbId = data.request.imdbid;
				this.requestParams.customName = data.request.request;
				this.requestParams.imdbInfo = data.request.request;
				for (var i = 0; i < data.movieData['seasoncount']; i++) {
					this.requestParams.seasons.push(i+1);
				}
				this.loaded = true;
				this.changedCategory();
			})
			.catch((error) => {
				this.notFoundMessage = error.data;
			});

		this.addRequest = function () {
			this.closeAlert();
			RequestsResource.Requests.update({id: $stateParams.id}, this.requestParams).$promise
				.then((response) => {
					$state.go('requests.request', {id: response.id, name: response.name});
				})
				.catch((error) => {
					this.addAlert({ type: 'danger', msg: error.data });
				});
		};

		this.changedCategory = function () {
			if (imdbLessCategories.indexOf(this.requestParams.category) > -1) {
				this.noimdb = true;
			} else {
				this.noimdb = false;
			}
		};

		this.fetchImdbInfo = function () {
	 		if (this.requestParams.imdbUrl.length > 1) {
	 			var match = this.requestParams.imdbUrl.match(/\/(tt[0-9]+)(\/|$)/);
	 			if (match && match.length > 1)  {
	 				var imdbId = match[1];
		 			MovieDataResource.Imdb.get({id: imdbId}, (imdb) => {
						this.requestParams.imdbInfo = imdb['title'] + ' (' + imdb['year'] +')';
						this.requestParams.imdbId = imdb['id'];
						this.requestParams.seasons = [];
						for (var i = 0; i < imdb['seasoncount']; i++) {
							this.requestParams.seasons.push(i+1);
						}
					}, (error) => {
						this.requestParams.imdbInfo = 'Error: ' + error;
					});
		 		}
	 		}
	 	};

	 	this.addAlert = function (obj) {
			this.alert = obj;
		};

		this.closeAlert = function () {
			this.alert = null;
		};
	}

})();
