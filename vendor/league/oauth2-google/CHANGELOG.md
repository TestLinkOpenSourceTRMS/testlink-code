OAuth 2.0 Google Provider Changelog

## 3.0.2 - 2019-11-16

### Fixed

- Allow for `family_name` to be undefined in user information, #79 by @majkel89

## 3.0.1 - 2018-12-28

### Fixed

- Correct conflict handling for prompt option, #69 by @mxdpeep

## 3.0.0 - 2018-12-23

### Changed

- Update to latest version of Google OAuth
- Use only OpenID Connect for user details

### Fixed

- Correct handling of selecting from multiple user accounts, #45
- Prevent conflict when using prompt option, #42

### Added

- Add "locale" to user details, #60
- Support additional scopes at construction

### Removed

- Dropped support for Google+ user details, #34 and #63

## 2.2.0 - 2018-03-19

### Added

- Hosted domain validation, #54 by @pradtke

## 2.1.0 - 2018-03-09

### Added

- OpenID Connect support, #48 by @pradtke

## 2.0.0 - 2017-01-24

### Added

- PHP 7.1 support

### Removed

- Dropped PHP 5.5 support

## 1.0.0 - 2015-08-12

- Initial release
