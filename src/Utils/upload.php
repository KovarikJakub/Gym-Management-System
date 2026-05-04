<?php

/**
 * Saves an uploaded image to the specified directory.
 * 
 * @param array $file The $_FILES entry for the uploaded file.
 * @param string $targetDir The directory where the file should be saved.
 * @return array Result array with format: ['success' => true, 'filename' => {newFileName}]; or ['error' => {message}]
 */
function validateAndSaveImage($file, $targetDir, $maxSize = 2097152)
{
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
    if ($_FILES['file']['size'] > $maxSize)
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

