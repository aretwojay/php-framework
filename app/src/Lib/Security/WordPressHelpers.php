<?php

/**
 * WordPress-style Security Functions
 * Provide escaping and sanitization functions similar to WordPress
 */

namespace App\Lib\Security;

/**
 * Escape HTML entities
 *
 * @param string $text The text to escape
 * @return string The escaped text
 */
function esc_html($text)
{
    return htmlspecialchars($text, ENT_QUOTES, 'UTF-8');
}

/**
 * Escape HTML attribute values
 *
 * @param string $text The attribute value to escape
 * @return string The escaped value
 */
function esc_attr($text)
{
    return htmlspecialchars($text, ENT_QUOTES, 'UTF-8');
}

/**
 * Escape URLs
 *
 * @param string $url The URL to escape
 * @return string The escaped URL
 */
function esc_url($url)
{
    if ('' === $url) {
        return '';
    }

    $url = preg_replace('|[^a-z0-9-~+_.?#=!&;,/:%@$|*\'()\[\]x]|i', '', $url);

    if ('' === $url) {
        return '';
    }

    if (strpos($url, ':') === false) {
        return $url;
    }

    $allowed_protocols = ['http', 'https', 'ftp', 'ftps', 'mailto', 'tel', 'sms'];
    $protocol          = wp_parse_url($url, PHP_URL_SCHEME);

    if (!in_array($protocol, $allowed_protocols, true)) {
        return '';
    }

    return $url;
}

/**
 * Sanitize text input
 *
 * @param string $str The input to sanitize
 * @return string The sanitized input
 */
function sanitize_text_field($str)
{
    if (is_array($str)) {
        return array_map(__FUNCTION__, $str);
    }

    $filtered = wp_check_plain_text($str);

    return $filtered;
}

/**
 * Parse URL components
 *
 * @param string $url The URL to parse
 * @param int    $component The specific component to return
 * @return mixed The URL component
 */
function wp_parse_url($url, $component = -1)
{
    $parts = parse_url($url);

    return match ($component) {
        PHP_URL_SCHEME => $parts['scheme'] ?? null,
        PHP_URL_HOST => $parts['host'] ?? null,
        PHP_URL_PORT => $parts['port'] ?? null,
        PHP_URL_USER => $parts['user'] ?? null,
        PHP_URL_PASS => $parts['pass'] ?? null,
        PHP_URL_PATH => $parts['path'] ?? null,
        PHP_URL_QUERY => $parts['query'] ?? null,
        PHP_URL_FRAGMENT => $parts['fragment'] ?? null,
        default => $parts,
    };
}

/**
 * Check for plain text
 *
 * @param string $str The input string
 * @return string The sanitized string
 */
function wp_check_plain_text($str)
{
    $str = (string) $str;

    $found = false;
    while (preg_match('/%[a-f0-9]{2}/i', $str)) {
        $str   = urldecode($str);
        $found = true;
    }

    if ($found) {
        $str = trim($str);
    }

    return htmlspecialchars(wp_strip_all_tags($str), ENT_QUOTES, 'UTF-8');
}

/**
 * Strip all HTML tags from a string
 *
 * @param string $text The input string
 * @return string The string without HTML tags
 */
function wp_strip_all_tags($text)
{
    return strip_tags($text);
}

/**
 * Sanitize $_POST data
 *
 * @param array $data The data array to sanitize
 * @return array The sanitized data
 */
function sanitize_post_data($data = []): array
{
    if (empty($data)) {
        $data = $_POST;
    }

    $sanitized = [];
    foreach ($data as $key => $value) {
        if (is_array($value)) {
            $sanitized[$key] = sanitize_post_data($value);
        } else {
            $sanitized[$key] = sanitize_text_field($value);
        }
    }

    return $sanitized;
}

/**
 * Sanitize $_GET data
 *
 * @param array $data The data array to sanitize
 * @return array The sanitized data
 */
function sanitize_query_data($data = []): array
{
    if (empty($data)) {
        $data = $_GET;
    }

    $sanitized = [];
    foreach ($data as $key => $value) {
        if (is_array($value)) {
            $sanitized[$key] = sanitize_query_data($value);
        } else {
            $sanitized[$key] = sanitize_text_field($value);
        }
    }

    return $sanitized;
}
