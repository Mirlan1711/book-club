<?php

declare(strict_types=1);

$sessionDirectory = __DIR__ . '/storage/sessions';

if (!is_dir($sessionDirectory)) {
    mkdir($sessionDirectory, 0775, true);
}

session_save_path($sessionDirectory);
session_start();
