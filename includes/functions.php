<?php

/**
 * Format currency amount
 */
function formatCurrency($amount)
{
    return '$' . number_format($amount, 2);
}

/**
 * Format date to readable format
 */
function formatDate($date)
{
    return date('F j, Y', strtotime($date));
}

/**
 * Format datetime to readable format
 */
function formatDateTime($datetime)
{
    return date('F j, Y g:i A', strtotime($datetime));
}

/**
 * Get time elapsed string (e.g., "2 hours ago")
 */
function timeElapsed($datetime)
{
    $now = new DateTime;
    $ago = new DateTime($datetime);
    $diff = $now->diff($ago);

    if ($diff->y > 0) {
        return $diff->y . ' year' . ($diff->y > 1 ? 's' : '') . ' ago';
    }
    if ($diff->m > 0) {
        return $diff->m . ' month' . ($diff->m > 1 ? 's' : '') . ' ago';
    }
    if ($diff->d > 0) {
        return $diff->d . ' day' . ($diff->d > 1 ? 's' : '') . ' ago';
    }
    if ($diff->h > 0) {
        return $diff->h . ' hour' . ($diff->h > 1 ? 's' : '') . ' ago';
    }
    if ($diff->i > 0) {
        return $diff->i . ' minute' . ($diff->i > 1 ? 's' : '') . ' ago';
    }
    return 'just now';
}

/**
 * Truncate text to specified length
 */
function truncateText($text, $length = 100, $append = '...')
{
    if (strlen($text) > $length) {
        $text = substr($text, 0, $length);
        $text = substr($text, 0, strrpos($text, ' '));
        $text .= $append;
    }
    return $text;
}

/**
 * Generate random string
 */
function generateRandomString($length = 10)
{
    $characters = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';
    $randomString = '';
    for ($i = 0; $i < $length; $i++) {
        $randomString .= $characters[rand(0, strlen($characters) - 1)];
    }
    return $randomString;
}

/**
 * Sanitize file name
 */
function sanitizeFileName($fileName)
{
    // Remove any character that is not alphanumeric, dot, dash or underscore
    $fileName = preg_replace('/[^a-zA-Z0-9.-_]/', '', $fileName);
    // Remove any dots except the last one
    $fileName = preg_replace('/\.(?=.*\.)/', '', $fileName);
    return $fileName;
}

/**
 * Get file extension
 */
function getFileExtension($fileName)
{
    return strtolower(pathinfo($fileName, PATHINFO_EXTENSION));
}

/**
 * Check if file is an image
 */
function isImage($fileName)
{
    $allowedTypes = ['jpg', 'jpeg', 'png', 'gif'];
    $fileExtension = getFileExtension($fileName);
    return in_array($fileExtension, $allowedTypes);
}

/**
 * Format file size
 */
function formatFileSize($bytes)
{
    if ($bytes >= 1073741824) {
        return number_format($bytes / 1073741824, 2) . ' GB';
    }
    if ($bytes >= 1048576) {
        return number_format($bytes / 1048576, 2) . ' MB';
    }
    if ($bytes >= 1024) {
        return number_format($bytes / 1024, 2) . ' KB';
    }
    return $bytes . ' bytes';
}

/**
 * Get user's IP address
 */
function getUserIP()
{
    if (!empty($_SERVER['HTTP_CLIENT_IP'])) {
        return $_SERVER['HTTP_CLIENT_IP'];
    }
    if (!empty($_SERVER['HTTP_X_FORWARDED_FOR'])) {
        return $_SERVER['HTTP_X_FORWARDED_FOR'];
    }
    return $_SERVER['REMOTE_ADDR'];
}

/**
 * Check if request is AJAX
 */
function isAjax()
{
    return !empty($_SERVER['HTTP_X_REQUESTED_WITH']) &&
        strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) == 'xmlhttprequest';
}

/**
 * Redirect with flash message
 */
function redirectWithMessage($url, $message, $type = 'success')
{
    $_SESSION['flash_message'] = $message;
    $_SESSION['flash_type'] = $type;
    header("Location: $url");
    exit;
}

/**
 * Display flash message
 */
function displayFlashMessage()
{
    if (isset($_SESSION['flash_message'])) {
        $message = $_SESSION['flash_message'];
        $type = $_SESSION['flash_type'] ?? 'success';
        unset($_SESSION['flash_message'], $_SESSION['flash_type']);

        $bgColor = $type === 'success' ? 'bg-green-100 border-green-400 text-green-700' : 'bg-red-100 border-red-400 text-red-700';

        echo "<div class='$bgColor border px-4 py-3 rounded relative' role='alert'>
                <span class='block sm:inline'>" . htmlspecialchars($message) . "</span>
              </div>";
    }
}
