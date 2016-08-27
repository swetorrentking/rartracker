# rartracker changelog

# 0.3.2 (2016-08-27)

### New features and improvements

* Refactored (removed) rss.php, brss.php and subdownload.php into new /api/v1 URLs
* Categories are now specified in Config.php on server side
* API for "torrents matcher" which is a client application for matching and loading torrents into bittorrent clients. (Application soon to be released on this github account)

### Bugfixes

* SweTV fetch now works again
* Genre selector in custom start page edit was missing
* Bootstrap fonts on /start were missing
* Additional language fixes

# 0.3.1 (2016-08-04)

### New features and improvements

* JS-libs and CSS files are now bundled and bower_components are no longer needed in deployment

### Bugfixes

* Language fixed

# 0.3.0 (2016-06-29)

### New features and improvements

* Dynamic multi language support with English as default language, finally!

# 0.2.2 (2016-06-24)

### New features and improvements

* Youtube trailers
* Torrent lists
* Admin can delete IP log entries

### Bugfixes

* Requests lists now save sorted state
* Swesub form
* Requests can now be in new section due to new/archive separation instead of using reqid
* Weekly earned bonus points calculated correctly on profile view
* Wide NFO files are now wrapped
* Swetv data source fetch working again
* Increased topic length
* Related torrents leaked uploader user id

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
