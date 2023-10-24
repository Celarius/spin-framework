# Changelog
SPIN Framework Changelog

## 0.0.26
- Updated composer.json with newver versions of packages

## 0.0.25
- New options for logging. "max_buffered_lines" and "flush_overflow_to_disk"

## 0.0.24
- Composer packages updated to newer versions
- Redis adapter fixes
- Tests updated
- Docblock corrections
- VERSION file added

## 0.0.23
- Added extended cipher methods

## 0.0.22
- Added "PHP Fatal Error" catching, making framework return a 500 Error to the caller with the last error message

## 0.0.21
- Variable definitions in classes with `/** @var` docblocks in most places

## 0.0.20
- Guzzle v2.0.0 stream support via `Utils::streamFor()`

## 0.0.19
- Started using Ramsey\UUID for UUID generation
- Added UUID v6 generation as default in UUID::generate() method

## 0.0.18
- app()->setCookie() internally uses setCookie() with array to set HTTPOnly=true and 'SameSite'=>'Strict' for cookies by default

## 0.0.17
- container() now uses internal globalVars instead when get/set variables

## 0.0.16
- Added `copy` to UploaedFiles class

## 0.0.15
- Storing/Getter for initial memory usage
- Determine Mime-Type for file (if not set)

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
