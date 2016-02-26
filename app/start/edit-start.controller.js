(function(){
	'use strict';

	angular
		.module('app.shared')
		.controller('EditStartController', EditStartController);

	function EditStartController(StartTorrentsResource, ErrorDialog) {

		this.data = {
			tid: [
				{ id: 0, name: 'Dagens' },
				{ id: 1, name: 'Veckans' },
				{ id: 2, name: 'Månadens' }
			],
			typ: [
				{ id: 0, name: 'Filmer' },
				{ id: 1, name: 'TV-serier' }
			],
			format: [
				{ id: 0, name: 'DVDR' },
				{ id: 1, name: '720p HD' },
				{ id: 2, name: '1080p HD' }
			],
			sektion: [
				{ id: 0, name: 'Nya releaser' },
				{ id: 1, name: 'Nya på arkivet' }
			],
			sort: [
				{ id: 2, name: 'Sortera efter popularitet' },
				{ id: 0, name: 'Sortera efter högst betyg' },
				{ id: 1, name: 'Sortera efter datum' }
			],
			genre: [{
				id: 'Action',
			}, {
				id: 'Adventure',
			}, {
				id: 'Animation',
			}, {
				id: 'Biography',
			}, {
				id: 'Crime',
			}, {
				id: 'Documentary',
			}, {
				id: 'Drama',
			}, {
				id: 'Family',
			}, {
				id: 'Fantasy',
			}, {
				id: 'History',
			}, {
				id: 'Horror',
			}, {
				id: 'Music',
			}, {
				id: 'Musical',
			}, {
				id: 'Mystery',
			}, {
				id: 'Romance',
			}, {
				id: 'Sci-fi',
			}, {
				id: 'Sport',
			}, {
				id: 'Thriller',
			}, {
				id: 'War',
			}]
		};

		this.list = {
			tid: this.data.tid[1],
			typ: this.data.typ[0],
			format: this.data.format[1],
			sektion: this.data.sektion[0],
			sort: this.data.sort[0],
			genre: ''	
		};

		this.fetchTorrents = function () {
			StartTorrentsResource.query({}, (data) => {
				this.highligtedTorrents = data;
			});
		};

		this.add = function () {
			StartTorrentsResource.save({
				tid: this.list.tid.id,
				format: this.list.format.id,
				sektion: this.list.sektion.id,
				typ: this.list.typ.id,
				sort: this.list.sort.id,
				genre: this.list.genre ? this.list.genre.id : ''
			}, () => {
				this.fetchTorrents();
			}, (error) => {
				ErrorDialog.display(error.data);
			});
		};

		this.delete = function (id) {
			StartTorrentsResource.delete({
				id: id
			}, () => {
				this.fetchTorrents();
			}, (error) => {
				ErrorDialog.display(error.data);
			});
		};

		this.move = function (id, direction) {
			StartTorrentsResource.update({
				id: id,
				direction: direction,
				action: 'move',
			}, () => {
				this.fetchTorrents();
			});
		};

		this.reset = function (category) {
			StartTorrentsResource.update({
				category: category,
				action: 'reset',
			}, () => {
				this.fetchTorrents();
			});
		};

		this.fetchTorrents();

	}

})();