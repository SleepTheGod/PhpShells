<?php
// ================================
// SAFE READ-ONLY SERVER AUDIT
// Made by Taylor Christian Newsome
// ================================

// helper
function sh($cmd) {
    $out = shell_exec($cmd . ' 2>/dev/null');
    return htmlspecialchars(trim($out ?: 'N/A'));
}

function yesno($cond) {
    return $cond ? '<span class="ok">PASS</span>' : '<span class="bad">FAIL</span>';
}

function warn($cond) {
    return $cond ? '<span class="warn">WARN</span>' : '<span class="ok">OK</span>';
}

// ---------------- SYSTEM ----------------
$uname = php_uname();
$os = file_exists('/etc/os-release') ? htmlspecialchars(file_get_contents('/etc/os-release')) : 'N/A';
$uptime = sh('uptime -p');
$kernel = sh('uname -r');
$arch = sh('uname -m');

// ---------------- PHP ----------------
$phpVersion = PHP_VERSION;
$phpIni = php_ini_loaded_file();
$phpExpose = ini_get('expose_php');
$phpErrors = ini_get('display_errors');
$phpFunctions = ini_get('disable_functions');

// ---------------- USER / PERMS ----------------
$currentUser = get_current_user();
$uid = function_exists('posix_getuid') ? posix_getuid() : 'N/A';
$euid = function_exists('posix_geteuid') ? posix_geteuid() : 'N/A';
$isRoot = ($euid === 0);

// ---------------- FILESYSTEM ----------------
$disk = sh('df -h');
$mounts = sh('mount | grep -E "noexec|nosuid|nodev"');
$worldWritable = sh('find / -xdev -type d -perm -0002 2>/dev/null | head -n 20');

// ---------------- NETWORK ----------------
$ips = sh('hostname -I');
$interfaces = sh('ip addr');
$listening = sh('ss -tulpen | head -n 30');
$firewall = sh('iptables -L -n');

// ---------------- SERVICES ----------------
$cron = sh('crontab -l');
$services = sh('ps aux --sort=-%mem | head -n 15');

// ---------------- SECURITY CHECKS ----------------
$checks = [
    'PHP expose_php disabled' => ($phpExpose == 0),
    'PHP display_errors disabled' => ($phpErrors == 0),
    'Disabled dangerous PHP funcs' => !empty($phpFunctions),
    'Not running as root (PHP)' => !$isRoot,
    '/tmp noexec or nosuid' => (strpos($mounts, '/tmp') !== false),
];

// ---------------- SCORE ----------------
$total = count($checks);
$pass = 0;
foreach ($checks as $c) if ($c) $pass++;
$score = round(($pass / $total) * 100);
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title>Server Security Audit</title>
<meta name="viewport" content="width=device-width, initial-scale=1">
<style>
body {
    background:#050805;
    color:#9fef00;
    font-family:monospace;
    margin:0;
}
header {
    padding:20px;
    text-align:center;
    border-bottom:1px solid #133700;
}
h1 { margin:0; font-size:22px; }
.container { padding:20px; }
.section {
    border:1px solid #133700;
    margin-bottom:20px;
    padding:15px;
    box-shadow:0 0 12px rgba(159,239,0,.15);
}
.section h2 {
    margin-top:0;
    font-size:16px;
    border-bottom:1px solid #133700;
    padding-bottom:5px;
}
pre {
    white-space:pre-wrap;
    word-wrap:break-word;
}
.ok { color:#00ff66; }
.warn { color:#ffee55; }
.bad { color:#ff4444; }
.score {
    font-size:28px;
    text-align:center;
}
footer {
    text-align:center;
    opacity:.6;
    padding:10px;
}
</style>
</head>
<body>

<header>
    <h1>Made by Taylor Christian Newsome</h1>
</header>

<div class="container">

<div class="section score">
Security Score: <?= $score ?>%
</div>

<div class="section">
<h2>System</h2>
<pre><?= $uname ?>


Kernel: <?= $kernel ?>

Arch: <?= $arch ?>

Uptime: <?= $uptime ?></pre>
</div>

<div class="section">
<h2>OS Release</h2>
<pre><?= $os ?></pre>
</div>

<div class="section">
<h2>PHP Audit</h2>
<pre>
Version: <?= $phpVersion ?>

php.ini: <?= $phpIni ?>

expose_php: <?= $phpExpose ?>

display_errors: <?= $phpErrors ?>

disabled_functions:
<?= $phpFunctions ?: 'None' ?>
</pre>
</div>

<div class="section">
<h2>User & Privileges</h2>
<pre>
User: <?= $currentUser ?>

UID: <?= $uid ?>

EUID: <?= $euid ?>

Running as root: <?= yesno($isRoot === false) ?>
</pre>
</div>

<div class="section">
<h2>Filesystem</h2>
<pre>
Disk Usage:
<?= $disk ?>


Secure Mounts (noexec/nosuid/nodev):
<?= $mounts ?>


World-writable directories (sample):
<?= $worldWritable ?>
</pre>
</div>

<div class="section">
<h2>Network</h2>
<pre>
IPs:
<?= $ips ?>


Listening Services (top):
<?= $listening ?>


Firewall (iptables):
<?= $firewall ?>
</pre>
</div>

<div class="section">
<h2>Processes</h2>
<pre><?= $services ?></pre>
</div>

<div class="section">
<h2>Cron Jobs (current user)</h2>
<pre><?= $cron ?></pre>
</div>

<div class="section">
<h2>Security Checks</h2>
<pre>
<?php foreach ($checks as $name => $ok): ?>
<?= $name ?>: <?= $ok ? '<span class="ok">PASS</span>' : '<span class="bad">FAIL</span>' ?>


<?php endforeach; ?>
</pre>
</div>

</div>

<footer>
Read‑only audit · no exploitation · no sensitive data exposure
</footer>

</body>
</html>
