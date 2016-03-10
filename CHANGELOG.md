# rartracker changelog

# 0.2.1 (2016-03-10)

### New features and improvements

* Archive torrents section removed. "New" and "archive" torrents are now mixed
* Filters for displaying new/archive, swesub, p2p, freeleech and default settings on user profile
* View filled requests
* Validate invite codes "before" registration.
* Email hashed. (run migrate.php on existing database)
* Watch toplists now based on recent activity

### Bugfixes

* Several disabled buttons were clickable
* Date in "watch" table (bevaka) fixed (run migrate.php), and for displaying more recent toplists data
* Buy -10 GB on friend now works
* Watch torrent page now works

# 0.2.0 (2016-02-26)

### New features and improvements

* Request comments
* Slug urls for requests and forum topics
* Subtitle quality selector
* IMDB info are fetched/guessed before upload
* Improved admin user search
* Multi-delete in Mailbox
* Polls paginated
* Better predb source
* Admins can delete suggestions and updated status will post into forum.
* Improved "IMDB guessing" for TV-series based on release name title

### Bugfixes
* Anyone could delete torrent comments
* Performance improvements
* History/back behaviour when sorting/browsing the site in general now works as expected
* Search info when upl
* Minclassread now works on forumheads
* IP check on signup could match itself
* Snatch log not logged correctly
* "Banlist" on friends-page did not work
* Countless minor bugfixes...

### Architectural and code improvements

* Better Config.php and app.config.js for general configurations
* Switching from grunt to the much faster and easier **gulp** as build script.
* Adding babel(es2015) to build step for es6 support, mostly for fat arrows.
* All JS-files, module and folder stucture has been reorganized and improved alot
* Code splitted into feature modules, now much easier to plug and play modules/features from app.modules.js
* Dropped $scope in favour for controllerAs 'vm'
* Alot of code cleanup in controllers, lots of stuff moved to components instead.
* Taking full use of ui-router URL state params for a proper browser history behaviour
* Upgrading all libs (angular, ui-router, ui-bootstrap etc) and support to latest version.
* Switched from memcache to memcached which actually works
* Fixed php strict errors (~E_STRICT no longer required)

# 0.1.0 (2015-11-01)

* Initial Release
