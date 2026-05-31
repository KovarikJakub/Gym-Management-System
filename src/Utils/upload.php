<?php

function ensureDirectoryExists($targetDir)
{
    if (!is_dir($targetDir))
    {
        mkdir($targetDir, 0775, true);
    }
}

function normalizeUploadedFiles(array $files)
{
    $normalized = [];

    if (!isset($files['name']) || !is_array($files['name']))
        return $normalized;

    foreach ($files['name'] as $index => $name)
    {
        $normalized[] = [
            'name' => $name,
            'type' => $files['type'][$index] ?? '',
            'tmp_name' => $files['tmp_name'][$index] ?? '',
            'error' => $files['error'][$index] ?? UPLOAD_ERR_NO_FILE,
            'size' => $files['size'][$index] ?? 0,
        ];
    }

    return $normalized;
}

/**
 * Saves an uploaded image to the specified directory.
 * 
 * @param array $file The $_FILES entry for the uploaded file.
 * @param string $targetDir The directory where the file should be saved.
 * @return array Result array with format: ['success' => true, 'filename' => {newFileName}]; or ['error' => {message}]
 */
function validateAndSaveImage($file, $targetDir, $maxSize = 2097152)
{
    ensureDirectoryExists($targetDir);

    // Check PHP Upload Errors
    if ($file['error'] !== UPLOAD_ERR_OK)
    {
        $result = ['error' => translateUploadError($file['error'])];
        // note: the following condition checks the PHP file upload limit, not the "max profile pic size" limit
        // check if the error is file size error to provide better error message:
        $uploadSizeErrors = [UPLOAD_ERR_INI_SIZE, UPLOAD_ERR_FORM_SIZE];
        if (in_array($file['error'], $uploadSizeErrors) )
            return ['error' => "Image is too large. Max size is " . ($maxSize / 1024 / 1024) . "MB."];
        // otherwise return the inital upload error
        return $result;
    }
    // check if image is bigger than 2MB
    if (($file['size'] ?? 0) > $maxSize)
        return ['error' => "Image exceeds maximum size of " . ($maxSize / 1024 / 1024) . "MB"];

    // Verify its an actual image
    $check = getimagesize($file['tmp_name']);
    if ($check === false)
        return ['error' => "Uploaded file is not a valid image."];

    // Validate Mime Type/Extension
    $allowedTypes = ['image/jpeg', 'image/jpg', 'image/png'];
    if (!in_array($check['mime'], $allowedTypes))
        return ['error' => "Only JPG, JPEG, and PNG files are allowed."];

    // Generate unique name and save
    $extension = pathinfo($file['name'], PATHINFO_EXTENSION);
    // filename format: "pp_{timestamp}_{random_code}.{png|jpg|jpeg}"
    $newFileName =  "pp_" . time() . "_"  . bin2hex(random_bytes(8)) . "." . $extension;
    
    if (move_uploaded_file($file['tmp_name'], $targetDir . $newFileName))
        return ['success' => true, 'filename' => $newFileName];

    return ['error' => "Server failed to save the image."];
}

function validateAndSaveCertificateFile($file, $targetDir, $maxSize = 5242880)
{
    ensureDirectoryExists($targetDir);

    if ($file['error'] !== UPLOAD_ERR_OK)
    {
        $result = ['error' => translateUploadError($file['error'])];
        $uploadSizeErrors = [UPLOAD_ERR_INI_SIZE, UPLOAD_ERR_FORM_SIZE];
        if (in_array($file['error'], $uploadSizeErrors))
            return ['error' => "File is too large. Max size is " . ($maxSize / 1024 / 1024) . "MB."];
        return $result;
    }

    if (($file['size'] ?? 0) > $maxSize)
        return ['error' => "File exceeds maximum size of " . ($maxSize / 1024 / 1024) . "MB."];

    $extension = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
    $allowedExtensions = ['pdf', 'png', 'jpg', 'jpeg'];
    if (!in_array($extension, $allowedExtensions))
        return ['error' => "Only PDF, PNG, JPG, and JPEG files are allowed."];

    $mimeType = null;
    if (function_exists('finfo_open'))
    {
        $finfo = finfo_open(FILEINFO_MIME_TYPE);
        if ($finfo)
        {
            $mimeType = finfo_file($finfo, $file['tmp_name']);
            finfo_close($finfo);
        }
    }

    if ($mimeType === null && function_exists('mime_content_type'))
        $mimeType = mime_content_type($file['tmp_name']);

    $allowedMimeTypes = [
        'application/pdf',
        'application/x-pdf',
        'image/png',
        'image/jpeg',
        'image/jpg',
        'image/pjpeg',
    ];

    if (!in_array($mimeType, $allowedMimeTypes))
        return ['error' => "Only PDF, PNG, JPG, and JPEG files are allowed."];

    $newFileName = 'cert_' . time() . '_' . bin2hex(random_bytes(8)) . '.' . $extension;
    $destination = rtrim($targetDir, DIRECTORY_SEPARATOR) . DIRECTORY_SEPARATOR . $newFileName;

    if (move_uploaded_file($file['tmp_name'], $destination))
    {
        return [
            'success' => true,
            'filename' => $newFileName,
            'mime_type' => $mimeType,
            'original_name' => $file['name'],
            'path' => $destination,
        ];
    }

    return ['error' => "Server failed to save the file."];
}

function translateUploadError($errorCode)
{
    return match ($errorCode)
    {
        UPLOAD_ERR_INI_SIZE   => "File exceeds the server's upload limit.",
        UPLOAD_ERR_FORM_SIZE  => "File exceeds the form's upload limit.",
        UPLOAD_ERR_PARTIAL    => "The file was only partially uploaded.",
        UPLOAD_ERR_NO_FILE    => "No file was uploaded.",
        default               => "Unknown upload error.",
    };
}
