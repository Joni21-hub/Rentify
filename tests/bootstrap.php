<?php

require_once __DIR__ . '/../vendor/autoload.php';

$worktreeApp = realpath(__DIR__ . '/../app');
$worktreeTests = realpath(__DIR__ . '/../tests');

// Prepend the worktree autoloader so that classes from this worktree's app/ and tests/
// are loaded before any vendor / main repository class mappings.
spl_autoload_register(function ($class) use ($worktreeApp, $worktreeTests) {
    if (str_starts_with($class, 'App\\')) {
        $rel = str_replace('\\', DIRECTORY_SEPARATOR, substr($class, 4)) . '.php';
        $file = $worktreeApp . DIRECTORY_SEPARATOR . $rel;
        if (file_exists($file)) {
            require_once $file;
            return true;
        }
    }
    if (str_starts_with($class, 'Tests\\')) {
        $rel = str_replace('\\', DIRECTORY_SEPARATOR, substr($class, 6)) . '.php';
        $file = $worktreeTests . DIRECTORY_SEPARATOR . $rel;
        if (file_exists($file)) {
            require_once $file;
            return true;
        }
    }
    return false;
}, true, true);

require_once __DIR__ . '/TestCase.php';
