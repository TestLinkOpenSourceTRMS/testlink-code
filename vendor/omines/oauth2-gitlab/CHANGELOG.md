# Changelog
All notable changes to `oauth2-gitlab` will be documented in this file
This project adheres to [Semantic Versioning](http://semver.org/).

## [Unreleased]
Nothing yet.

## [3.6.0] - 2023-11-06
Maintenance release

 - Upped code quality to PHPStan max level
 - Officially support PHP 8.3
 - Switched to Github Actions

## [3.5.0] - 2022-10-18
### Changed
 - Maintenance release dropping support for PHP versions below 8.0

## [3.4.0] - 2021-02-08
### Added
 - Compatibility with php-gitlab-api v11
 - Test suite compatible with PHP8

## [3.3.0] - 2020-02-10
### Added
 - Compatibility with php-gitlab-api v10

## [3.2.0] - 2020-02-10
### Changed
 - Updated dependencies to those requiring up to date PHP versions
 
### Removed
 - Support for outdated and unsupported PHP versions (<7.2)

## [3.1.2] - 2018-11-23
### Changed
 - Added conflict with `oauth2-client:2.4.0` due to [breaking change upstream](https://github.com/thephpleague/oauth2-client/issues/752) (#6)
 
## [3.1.1] - 2018-10-01
### Added
 - PHP 7.2 and nightly added to test suite
 - Infection testing added
 
### Changed
 - Test suite upgraded to PHPUnit 5/7 hybrid

## [3.1.0] - 2017-11-01
### Added
 - Access scope support was implemented

## [3.0.0] - 2017-05-31
### Changed
 - **Breaking**: Upgrade Gitlab API from v3 to v4
 - Test suite upgraded from PHPUnit 4 to 5/6 hybrid

## [2.0.0] - 2017-02-03
### Added
 - PHP 7.1 is now officially supported and tested

### Changed
 - **Breaking**: Upgrade league/oauth2-client to major version 2
 - Included PHP-CS-Fixer

### Removed
 - PHP 5.5 is end of life and no longer supported

## [1.1.0] - 2016-08-28
### Added
 - Added `getApiClient` method on `GitlabResourceOwner` to get an API connector

## [1.0.0] - 2016-05-20
### Changed
 - Cleaned up everything after definitive testing for stable release

## 1.0.0-alpha-1 - 2016-05-16
### Added
 - Original fork, feature complete

[Unreleased]: https://github.com/omines/oauth2-gitlab/compare/3.6.0...master
[3.6.0]: https://github.com/omines/oauth2-gitlab/compare/3.5.0...3.6.0
[3.5.0]: https://github.com/omines/oauth2-gitlab/compare/3.4.0...3.5.0
[3.4.0]: https://github.com/omines/oauth2-gitlab/compare/3.3.0...3.4.0
[3.3.0]: https://github.com/omines/oauth2-gitlab/compare/3.2.0...3.3.0
[3.2.0]: https://github.com/omines/oauth2-gitlab/compare/3.1.2...3.2.0
[3.1.2]: https://github.com/omines/oauth2-gitlab/compare/3.1.1...3.1.2
[3.1.1]: https://github.com/omines/oauth2-gitlab/compare/3.1.0...3.1.1
[3.1.0]: https://github.com/omines/oauth2-gitlab/compare/3.0.0...3.1.0
[3.0.0]: https://github.com/omines/oauth2-gitlab/compare/2.0.0...3.0.0
[2.0.0]: https://github.com/omines/oauth2-gitlab/compare/1.1.0...2.0.0
[1.1.0]: https://github.com/omines/oauth2-gitlab/compare/1.0.0...1.1.0
[1.0.0]: https://github.com/omines/oauth2-gitlab/compare/1.0.0-alpha.1...1.0.0
