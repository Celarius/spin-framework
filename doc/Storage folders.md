
- [1. Storage folders](#1-storage-folders)
  - [1.1 getStoragePath()](#11-getstoragepath)
  - [1.2 getSharedStoragePath()](#12-getsharedstoragepath)

# 1. Storage folders
There are two methods to obtain storage folders. The folders have different meaning and usage scenarios.

## 1.1 getStoragePath()
The files in this folder are considered temporary and instance specific. They exist only for as long as the instance exists.

Calling `app()->getStoragePath()` obtains the local storage folder `/<path_to_app>/app/src/storage`.

## 1.2 getSharedStoragePath()
Files in this folder can be considered persistent as they are shared and accessible between all instances of the service.

Calling `app()->getSharedStoragePath()` obtains the configured path in the config file (`storage.shared`) and appends
the `envirnoment` and `application.code` to it to make it unique among all services.

> **NOTE** If this folder is not found when the application is run() it is set to the same as `getStoragePath` to maintain a folder.
