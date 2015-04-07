#!/usr/bin/php
<?php

if (!isset($argv[1])) {
    exit("Usage: $argv[0] filename.pdf" . PHP_EOL);
}

$urls = array();

$handle = @fopen($argv[1], "r");
if ($handle) {
    while (($buffer = fgets($handle, 4096)) !== false) {
        if (preg_match('|^/URI \((http(s)?.+)\)|', $buffer, $matches)) {
            $urls[] = $matches[1];
        }
    }
    if (!feof($handle)) {
        echo "Error: unexpected fgets() fail\n";
    }
    fclose($handle);
}

$urls = array_unique($urls);

stream_context_set_default(
    array(
        'http' => array(
            'method' => 'HEAD'
        )
    )
);

foreach ($urls as $url) {
    $headers = get_headers($url, 1);

    if ($headers && isset($headers[0]) && preg_match('/(200 OK|301 Moved Permanently|302 Moved Temporarily|302 Found)/', $headers[0])) {
        echo "[OK] $url" . PHP_EOL;
    } else {
        if (isset($headers[0])) {
            echo "[FAIL - $headers[0]] $url" . PHP_EOL;
        } else {
            echo "[FAIL - Unknown reason] $url" . PHP_EOL;
        }
    }
}
