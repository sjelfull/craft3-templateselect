# Template Select Changelog

All notable changes to this project will be documented in this file.

The format is based on [Keep a Changelog](http://keepachangelog.com/) and this project adheres to [Semantic Versioning](http://semver.org/).

## Unreleased

## 5.0.1 - 2026-02-16
### Fixed
- Fixed fatal error in Craft 5.9+ caused by removal of Stringy from craftcms/cms — replaced `Stringy\Stringy` with Craft's native `StringHelper` ([#21](https://github.com/sjelfull/craft3-templateselect/pull/21))

### Added
- Added support for template descriptions via Twig comment syntax `{# @description: ... #}`
- Template descriptions now appear in the dropdown selector to help users identify templates

## 5.0.0 - 2022-10-03
### Added
- Made plugin compatible with Craft 5
- Added support for environment variables in settings 
- Added `field.subfolder()` method to get subfolder from the field instance 
- Added `field.withSubfolder()` method to get template path including subfolder if set 
- Added `field.filename()` method to get the template filename without path 

## 3.0.0 - 2022-10-03
### Added
- Made plugin compatible with Craft 4

## 2.0.2 - 2019-05-22
### Fixed
- Fixed template path resolution on Windows

## 2.0.1 - 2019-01-18
### Changed
- Changed craftcms dependency to support 3.1

## 2.0.0 - 2017-10-28
### Added
- Initial release
