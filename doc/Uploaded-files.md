<!-- MarkdownTOC -->

- [1. Uploaded Files](#1-uploaded-files)

<!-- /MarkdownTOC -->

# 1. Uploaded Files
Files are handled by the `UploadedFilesManager` in Spin. This manager manages all uploaded (sent) files.

It provides a method calleg `getFiles()` to retreive a list of `UploadedFile` objects.

**Example**
```php
  # Create Manager
  $manager = new UploadedFilesManager();

  # Loop the received files
  foreach ($manager->getFiles() as $fileInfo) { // $fileInfo is an instance of UploadedFile
    # Move the file to our /tmp directory
    $fileInfo->move('/tmp', $fileInfo->getname());
  }
```
