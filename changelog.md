# Changelog
SPIN Framework Changelog

## 0.0.15
- Storing/Getter for initial memory usage

## 0.0.14
- Support for empty `path` in routeGroups

## 0.0.13
- PDO param binding examines the type of value passed, and uses correct PDO::PARAM_* for it

## 0.0.12
- Composer v2 compatibility, class names corrected

## 0.0.11
- responseXML() fixed
- Package.json version corrected
- PHP v7.4.x compatibility verified
- responseFile() has new param for removing file after sending

## 0.0.10
- UploadedFile and UploadedFilesmanager fixes

## 0.0.9
- Added `getSharedStoragePath()` (docker persitent storage compatibility)
- Root namespaces for `Helpers.php`
- Added error-checking for some variables on application startup
- License date updated
- Unittests updated to work with PHPUnit v8
- Technical documentation pages
- Monolog v2+ package in composer
- Leauge/Container v3+
-

## 0.0.8
- Added \ for all root namespace functions (performance enhancement)
- Code cleanup
- Unittests dir /tests rearranged
- Apcu basic unittest added

## 0.0.7
- Added postParams() method to helpers

## 0.0.6
- No recorded changes

## 0.0.5
- No recorded changes

## 0.0.4
- Changed the Database Connection system. New AbstractConnection class.

## 0.0.x
- Initial code
