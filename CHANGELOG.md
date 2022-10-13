# Changelog

## [1.6.2]
* Fix: fixed bug "Problems with special characters in names" https://github.com/fluxapps/Panopto/issues/5
* Fix: fixed bug "No hostname prefix for thumbailUrl" https://github.com/fluxapps/Panopto/pull/7
* special thanks to: jheim2, rob0403 

## [1.6.1]
* Fix: fixed bug for host urls starting with letters h,t,p,s

## [1.6.0]
* ILIAS 7 support
* Remove ILIAS 5.4 support

## [1.5.0]
- Fix: Add missing object translations
- Improvement: eliminated waiting time when sorting videos
- Feature: copy objects (cloned objects show the same videos as the originals)
- Feature: support playlists
- Change: extended rest client for page editor plugin
- **Breaking**: a REST client has to be configured to use the latest features. See: [README REST Client](./README.md#rest-client)

## [1.4.0]
* ILIAS 6 support
* Remove ILIAS 5.3 support
* Min. PHP 7.0
* Fix Docker-ILIAS log

## [1.3.6]
* Last ILIAS 5.3 Release
* Email user mapping
* Fix offline status check

## [1.3.5]
* Bugfix: fixed sorting

## [1.3.4]
* Bugfix: fixed bug in pagination

## [1.3.3]
* Bugfix: removed limit of 25 videos

## [1.3.2]
* Bugfix: Viewing videos granted Creator permissions

## [1.3.1]
* Bugfix: Can't play videos on content tab
* Bugfix: Unexpected error message on content tab with no videos

## [1.3.0]
* Feature: Sorting of added Videos
* Feature: Show scheduled and live sessions
* Bugfix: Offline object still accessible

## [1.2.0]
* Sorting functionality

## [1.1.0]
* Support for ILIAS 5.4
* Dropped support for ILIAS 5.2

## [1.0.1]
* Bugfix: Config "Object Title" can not be saved

## [1.0.0]
* First stable version
* Functionality according to [Specification](doc/34_Spezifikation_2-1.pdf)
