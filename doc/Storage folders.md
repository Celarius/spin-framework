<!-- MarkdownTOC -->

- [1. Storage folders](#1-storage-folders)

<!-- /MarkdownTOC -->

# 1. Storage folders
There are two methods to obtain storage folders. The folders have different meaning and usage scenarios.

## `getStoragePath`
Calling `app()->getStoragePath()` obtains the local storage folder `/<path_to_app>/app/src/storage`. This is used for storing instance
specific temporary files that are not persistent for the service. These files are usuall removed when an instance is stopped.

## `getSharedStoragePath`
Calling `app()->getSharedStoragePath()` obtains the configured path in the config file (`storage.shared`) and appends
the envirnoment to it. Files in this folder can be considered persistent as they are shared and accessible between all instances.

If this folder is not found when the application is run() it is set to the same as `getStoragePath` to maintain a folder.
