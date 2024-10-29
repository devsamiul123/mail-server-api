<?php

if (!function_exists('parseEmailHeaders')) {
    function parseEmailHeaders($rawHeaders) {
        $headers = [];
        $lines = explode("\r\n", $rawHeaders); // Split by lines
        $currentKey = ''; 
        $currentValue = '';
    
        foreach ($lines as $line) {
            if (preg_match('/^\s+/', $line)) {
                $currentValue .= ' ' . trim($line);
            } else {
                if (!empty($currentKey)) {
                    $headers[$currentKey] = trim($currentValue);
                }
                $parts = explode(':', $line, 2);
                if (count($parts) === 2) {
                    $currentKey = trim($parts[0]); // Header key
                    $currentValue = trim($parts[1]); // Header value
                }
            }
        }
    
        if (!empty($currentKey)) {
            $headers[$currentKey] = trim($currentValue);
        }
    
        return $headers;
    }
}


if (!function_exists('extractNameAndEmail')) {
    function extractNameAndEmail($input) {
        // Use regex to match the name and email
        if (preg_match('/^(.*?)(?:\s*<(.+?)>)?$/', $input, $matches)) {
            // $matches[1] contains the name part (trimmed)
            // $matches[2] contains the email part (trimmed)
            return [
                'name' => trim($matches[1]),
                'email' => isset($matches[2]) ? trim($matches[2]) : null,
            ];
        }

        return [
            'name' => null,
            'email' => null,
        ];
    }
}

