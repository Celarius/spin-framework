# File Uploads Recipe

## Problem

How do I handle file uploads securely and efficiently in my SPIN Framework application?

This guide covers validation, security checks, storage strategies, and error handling for file uploads.

---

## Solution

SPIN Framework provides `UploadedFile` and `UploadedFiles` classes for working with uploads. Security requires validation at multiple layers: type, size, content, and storage location.

---

## Basic File Upload Handler

Create a controller to accept and process uploads:

```php
<?php
declare(strict_types=1);
namespace App\Controllers;

use Spin\Core\Controller;
use Psr\Http\Message\ResponseInterface;

class FileUploadController extends Controller
{
    public function handlePOST(array $args): ResponseInterface
    {
        $request = getRequest();
        $uploadedFiles = $request->getUploadedFiles();

        if (empty($uploadedFiles)) {
            return responseJson(['error' => 'No files uploaded'], 400);
        }

        $results = [];

        foreach ($uploadedFiles as $fieldName => $file) {
            // Validate single file
            $validation = $this->validateUpload($file);
            if ($validation['valid'] === false) {
                return responseJson(
                    ['error' => $validation['error']],
                    400
                );
            }

            // Store file
            $path = $this->storeFile($file);
            $results[$fieldName] = [
                'filename' => $file->getClientFilename(),
                'path' => $path,
                'size' => $file->getSize(),
            ];
        }

        return responseJson(['files' => $results], 201);
    }

    private function validateUpload($file): array
    {
        // Check for upload errors
        if ($file->getError() !== UPLOAD_ERR_OK) {
            $errors = [
                UPLOAD_ERR_INI_SIZE => 'File exceeds upload_max_filesize',
                UPLOAD_ERR_FORM_SIZE => 'File exceeds MAX_FILE_SIZE',
                UPLOAD_ERR_PARTIAL => 'File was only partially uploaded',
                UPLOAD_ERR_NO_FILE => 'No file was uploaded',
                UPLOAD_ERR_NO_TMP_DIR => 'Missing temporary folder',
                UPLOAD_ERR_CANT_WRITE => 'Failed to write file to disk',
            ];

            return [
                'valid' => false,
                'error' => $errors[$file->getError()] ?? 'Unknown upload error',
            ];
        }

        // Validate filename
        $filename = $file->getClientFilename();
        if (!$this->isValidFilename($filename)) {
            return [
                'valid' => false,
                'error' => 'Invalid filename: use alphanumeric, dash, underscore only',
            ];
        }

        // Validate file size
        $maxSize = 10 * 1024 * 1024; // 10 MB
        if ($file->getSize() > $maxSize) {
            return [
                'valid' => false,
                'error' => 'File size exceeds 10 MB limit',
            ];
        }

        return ['valid' => true];
    }

    private function isValidFilename(string $filename): bool
    {
        // Allow only safe characters
        return (bool)preg_match(
            '/^[a-zA-Z0-9._\-]+$/',
            $filename
        );
    }

    private function storeFile($file): string
    {
        $uploadDir = env('UPLOAD_DIR', '/uploads');
        $storagePath = public_path($uploadDir);

        // Create directory if needed
        if (!is_dir($storagePath)) {
            mkdir($storagePath, 0755, true);
        }

        // Generate unique filename
        $extension = pathinfo($file->getClientFilename(), PATHINFO_EXTENSION);
        $uniqueName = uniqid() . '.' . $extension;
        $fullPath = $storagePath . '/' . $uniqueName;

        // Move uploaded file
        $file->moveTo($fullPath);

        return $uploadDir . '/' . $uniqueName;
    }
}
```

---

## MIME Type Validation

Validate file types strictly:

```php
<?php
declare(strict_types=1);
namespace App\Validators;

class FileValidator
{
    private array $allowedMimes = [
        'image/jpeg' => ['jpg', 'jpeg'],
        'image/png' => ['png'],
        'image/gif' => ['gif'],
        'application/pdf' => ['pdf'],
        'application/msword' => ['doc'],
        'application/vnd.openxmlformats-officedocument.wordprocessingml.document' => ['docx'],
    ];

    public function validateMimeType(object $file, string $allowedType): bool
    {
        $mimeType = mime_content_type($file->getStream()->getMetadata('uri'));

        if (!isset($this->allowedMimes[$mimeType])) {
            return false;
        }

        // Also check extension
        $extension = strtolower(
            pathinfo($file->getClientFilename(), PATHINFO_EXTENSION)
        );

        return in_array($extension, $this->allowedMimes[$mimeType]);
    }

    public function validateImageDimensions(
        string $filepath,
        int $minWidth,
        int $minHeight,
        int $maxWidth,
        int $maxHeight
    ): bool {
        $dimensions = getimagesize($filepath);
        if (!$dimensions) {
            return false;
        }

        [$width, $height] = $dimensions;

        return $width >= $minWidth
            && $height >= $minHeight
            && $width <= $maxWidth
            && $height <= $maxHeight;
    }
}
```

---

## Security: Virus Scanning

Integrate with ClamAV for virus scanning:

```php
<?php
declare(strict_types=1);
namespace App\Services;

class VirusScanService
{
    private string $clamdSocket;

    public function __construct()
    {
        $this->clamdSocket = env('CLAMAV_SOCKET', 'localhost:3310');
    }

    public function scan(string $filepath): bool
    {
        try {
            $socket = fsockopen($this->clamdSocket, 3310, $errno, $errstr, 5);
            if (!$socket) {
                logger()->warning("ClamAV unavailable: $errstr");
                // Fail open: allow upload if service is down
                return true;
            }

            $command = "SCAN $filepath\n";
            fwrite($socket, $command);

            $result = fgets($socket);
            fclose($socket);

            // Result format: "<file>: OK" or "<file>: <virus> FOUND"
            return strpos($result, 'OK') !== false;
        } catch (\Exception $e) {
            logger()->error('Virus scan error: ' . $e->getMessage());
            return false;
        }
    }
}
```

Usage in controller:

```php
private function storeFile($file): string
{
    // Validate MIME type first
    $validator = new FileValidator();
    if (!$validator->validateMimeType($file, 'image/jpeg')) {
        throw new \Exception('Invalid file type');
    }

    // Move to temporary location
    $tempPath = sys_get_temp_dir() . '/' . uniqid();
    $file->moveTo($tempPath);

    // Scan for viruses
    $scanService = new VirusScanService();
    if (!$scanService->scan($tempPath)) {
        unlink($tempPath);
        throw new \Exception('File failed virus scan');
    }

    // Move to final location
    $finalPath = $this->getFinalStoragePath($file);
    rename($tempPath, $finalPath);

    return $finalPath;
}
```

---

## Path Traversal Prevention

Prevent directory traversal attacks:

```php
<?php
declare(strict_types=1);
namespace App\Services;

class SecureStorageService
{
    private string $baseDir;

    public function __construct(string $baseDir)
    {
        $this->baseDir = realpath($baseDir);
        if (!$this->baseDir) {
            throw new \Exception("Base directory does not exist: $baseDir");
        }
    }

    public function store(object $file, string $subdir = ''): string
    {
        // Sanitize subdirectory
        $subdir = trim($subdir, '/\\');
        if (preg_match('/\.\./', $subdir)) {
            throw new \Exception('Invalid subdirectory path');
        }

        $uploadDir = $this->baseDir;
        if (!empty($subdir)) {
            $uploadDir = $this->baseDir . DIRECTORY_SEPARATOR . $subdir;
        }

        // Verify resolved path is within base directory
        $uploadDir = realpath($uploadDir) ?: $uploadDir;
        if (strpos($uploadDir, $this->baseDir) !== 0) {
            throw new \Exception('Path traversal attempt detected');
        }

        if (!is_dir($uploadDir)) {
            mkdir($uploadDir, 0755, true);
        }

        // Generate safe filename
        $extension = pathinfo($file->getClientFilename(), PATHINFO_EXTENSION);
        $filename = bin2hex(random_bytes(16)) . '.' . $extension;

        $fullPath = $uploadDir . DIRECTORY_SEPARATOR . $filename;

        $file->moveTo($fullPath);

        return $fullPath;
    }

    public function delete(string $filepath): bool
    {
        // Verify file is within base directory
        $realPath = realpath($filepath);
        if (!$realPath || strpos($realPath, $this->baseDir) !== 0) {
            throw new \Exception('Attempted to delete file outside base directory');
        }

        return unlink($realPath);
    }
}
```

---

## Storage Strategies

### Local Storage with Organization

```php
<?php
declare(strict_types=1);
namespace App\Services;

class FileOrganizer
{
    private StorageService $storage;

    public function __construct(StorageService $storage)
    {
        $this->storage = $storage;
    }

    public function storeUserAvatar(int $userId, object $file): string
    {
        // Organize by user: /avatars/user_1234/avatar_abc123.jpg
        $subdir = 'avatars/user_' . $userId;
        return $this->storage->store($file, $subdir);
    }

    public function storeDocumentAttachment(int $documentId, object $file): string
    {
        // Organize by date and document: /documents/2026/03/doc_5678/file.pdf
        $date = date('Y/m');
        $subdir = "documents/$date/doc_$documentId";
        return $this->storage->store($file, $subdir);
    }

    public function storeTemporaryUpload(object $file): string
    {
        // Temporary files: /temp/uploads/2026-03-15/unique_id
        $date = date('Y-m-d');
        $subdir = "temp/uploads/$date";
        return $this->storage->store($file, $subdir);
    }
}
```

### Cloud Storage (S3 Example)

```php
<?php
declare(strict_types=1);
namespace App\Services;

use Aws\S3\S3Client;

class S3StorageService
{
    private S3Client $s3;
    private string $bucket;

    public function __construct()
    {
        $this->s3 = new S3Client([
            'version' => 'latest',
            'region' => env('AWS_REGION', 'us-east-1'),
            'credentials' => [
                'key' => env('AWS_ACCESS_KEY'),
                'secret' => env('AWS_SECRET_KEY'),
            ],
        ]);

        $this->bucket = env('AWS_BUCKET');
    }

    public function store(object $file, string $key): string
    {
        $stream = $file->getStream();

        $this->s3->putObject([
            'Bucket' => $this->bucket,
            'Key' => $key,
            'Body' => $stream,
            'ContentType' => $file->getClientMediaType(),
            'ServerSideEncryption' => 'AES256',
        ]);

        return "s3://{$this->bucket}/$key";
    }

    public function getUrl(string $key, int $expiresIn = 3600): string
    {
        $cmd = $this->s3->getCommand('GetObject', [
            'Bucket' => $this->bucket,
            'Key' => $key,
        ]);

        $request = $this->s3->createPresignedRequest($cmd, "+$expiresIn seconds");
        return (string)$request->getUri();
    }

    public function delete(string $key): bool
    {
        $this->s3->deleteObject([
            'Bucket' => $this->bucket,
            'Key' => $key,
        ]);

        return true;
    }
}
```

---

## Error Handling and User Feedback

```php
<?php
declare(strict_types=1);
namespace App\Controllers;

use Spin\Core\Controller;
use Psr\Http\Message\ResponseInterface;

class SafeUploadController extends Controller
{
    public function handlePOST(array $args): ResponseInterface
    {
        try {
            $uploadedFiles = getRequest()->getUploadedFiles();

            if (empty($uploadedFiles)) {
                return responseJson(
                    ['error' => 'Please select a file to upload'],
                    400
                );
            }

            $file = current($uploadedFiles);
            $this->validateUpload($file);

            $path = $this->storeFile($file);

            return responseJson([
                'message' => 'File uploaded successfully',
                'file' => [
                    'path' => $path,
                    'name' => $file->getClientFilename(),
                    'size' => $file->getSize(),
                ],
            ], 201);

        } catch (\InvalidArgumentException $e) {
            logger()->warning('Upload validation failed: ' . $e->getMessage());

            return responseJson([
                'error' => 'File validation failed',
                'details' => $e->getMessage(),
            ], 400);

        } catch (\RuntimeException $e) {
            logger()->error('Upload storage failed: ' . $e->getMessage());

            return responseJson([
                'error' => 'Failed to save file. Please try again.',
                'code' => 'STORAGE_ERROR',
            ], 500);

        } catch (\Exception $e) {
            logger()->error('Unexpected upload error: ' . $e->getMessage());

            return responseJson([
                'error' => 'An unexpected error occurred',
                'code' => 'UNKNOWN_ERROR',
            ], 500);
        }
    }

    private function validateUpload($file): void
    {
        if ($file->getError() !== UPLOAD_ERR_OK) {
            throw new \InvalidArgumentException('Upload error: ' . $file->getError());
        }

        $maxSize = 10 * 1024 * 1024;
        if ($file->getSize() > $maxSize) {
            throw new \InvalidArgumentException('File exceeds 10 MB limit');
        }

        if (!$this->isAllowedMimeType($file)) {
            throw new \InvalidArgumentException('File type not allowed');
        }
    }

    private function isAllowedMimeType($file): bool
    {
        $allowed = ['image/jpeg', 'image/png', 'application/pdf'];
        return in_array($file->getClientMediaType(), $allowed);
    }

    private function storeFile($file): string
    {
        $uploadDir = storage_path('uploads');
        if (!is_dir($uploadDir)) {
            mkdir($uploadDir, 0755, true);
        }

        $filename = uniqid() . '.' . pathinfo(
            $file->getClientFilename(),
            PATHINFO_EXTENSION
        );

        $file->moveTo($uploadDir . '/' . $filename);
        return '/uploads/' . $filename;
    }
}
```

---

## Testing File Uploads

```php
<?php
declare(strict_types=1);
namespace Tests\Feature;

use PHPUnit\Framework\TestCase;

class FileUploadTest extends TestCase
{
    public function test_upload_valid_file(): void
    {
        $file = $this->createTestFile('test.jpg', 'image/jpeg');

        $response = $this->post('/upload', ['file' => $file]);

        $this->assertEquals(201, $response->getStatusCode());
        $body = json_decode((string)$response->getBody(), true);
        $this->assertArrayHasKey('file', $body);
    }

    public function test_reject_oversized_file(): void
    {
        $file = $this->createTestFile('large.jpg', 'image/jpeg', 15 * 1024 * 1024);

        $response = $this->post('/upload', ['file' => $file]);

        $this->assertEquals(400, $response->getStatusCode());
        $body = json_decode((string)$response->getBody(), true);
        $this->assertStringContainsString('exceeds', $body['error']);
    }

    public function test_reject_invalid_mime_type(): void
    {
        $file = $this->createTestFile('virus.exe', 'application/x-msdownload');

        $response = $this->post('/upload', ['file' => $file]);

        $this->assertEquals(400, $response->getStatusCode());
    }

    private function createTestFile(string $name, string $mime, int $size = 1024): object
    {
        // Implementation to create mock UploadedFile
    }
}
```

---

## Configuration

```env
UPLOAD_DIR=/uploads
MAX_UPLOAD_SIZE=10485760
ALLOWED_MIME_TYPES=image/jpeg,image/png,application/pdf
CLAMAV_ENABLED=true
CLAMAV_SOCKET=localhost:3310
AWS_ENABLED=false
AWS_REGION=us-east-1
AWS_BUCKET=my-app-files
```

---

## Best Practices

1. **Always validate MIME types** - Check both extension and content
2. **Implement size limits** - Prevent disk exhaustion
3. **Use unique filenames** - Prevent overwrite attacks and guessing
4. **Scan for malware** - Integrate ClamAV or similar
5. **Prevent directory traversal** - Validate all paths
6. **Store outside webroot** - When possible, serve via download handler
7. **Set proper permissions** - Use 644 for files, 755 for directories
8. **Log all uploads** - Track who uploaded what and when
9. **Implement quotas** - Per-user or per-resource limits
10. **Use HTTPS only** - Encrypt uploads in transit

---

## Related Documentation

- [User-Guide: Controllers](../User-Guide/Controllers.md)
- [Best-Practices: Security](../Best-Practices/Security.md)
- [Reference: UploadedFiles](../Reference/Http-Messages.md#uploadedfiles)
