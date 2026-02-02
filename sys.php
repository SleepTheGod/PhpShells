<?php
// Safe server info only — no credentials, no private data
function sh($cmd) {
    return htmlspecialchars(shell_exec($cmd) ?? 'N/A');
}

$uname = php_uname();
$phpVersion = PHP_VERSION;
$extensions = implode(', ', get_loaded_extensions());

$osRelease = '';
if (file_exists('/etc/os-release')) {
    $osRelease = htmlspecialchars(file_get_contents('/etc/os-release'));
}

$uptime = sh('uptime -p');
$disk = sh('df -h');
$memory = sh('free -h');
$cpu = sh('lscpu 2>/dev/null');
$ip = sh('hostname -I');
$net = sh('ip addr 2>/dev/null');
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title>Server Info</title>
<meta name="viewport" content="width=device-width, initial-scale=1">
<style>
body {
    background: #050805;
    color: #9fef00;
    font-family: monospace;
    margin: 0;
    padding: 0;
}
header {
    padding: 20px;
    text-align: center;
    border-bottom: 1px solid #133700;
}
h1 {
    margin: 0;
    font-size: 22px;
}
.container {
    padding: 20px;
}
.section {
    margin-bottom: 20px;
    border: 1px solid #133700;
    padding: 15px;
    box-shadow: 0 0 10px rgba(159,239,0,0.15);
}
.section h2 {
    margin-top: 0;
    font-size: 16px;
    border-bottom: 1px solid #133700;
    padding-bottom: 5px;
}
pre {
    white-space: pre-wrap;
    word-wrap: break-word;
    color: #9fef00;
}
.footer {
    text-align: center;
    padding: 10px;
    font-size: 12px;
    opacity: 0.7;
}
</style>
</head>
<body>

<header>
    <h1>Made by Taylor Christian Newsome</h1>
</header>

<div class="container">

<div class="section">
<h2>System</h2>
<pre><?= $uname ?></pre>
</div>

<div class="section">
<h2>OS Release</h2>
<pre><?= $osRelease ?></pre>
</div>

<div class="section">
<h2>PHP</h2>
<pre>Version: <?= $phpVersion ?>


Extensions:
<?= $extensions ?></pre>
</div>

<div class="section">
<h2>Uptime</h2>
<pre><?= $uptime ?></pre>
</div>

<div class="section">
<h2>CPU</h2>
<pre><?= $cpu ?></pre>
</div>

<div class="section">
<h2>Memory</h2>
<pre><?= $memory ?></pre>
</div>

<div class="section">
<h2>Disk</h2>
<pre><?= $disk ?></pre>
</div>

<div class="section">
<h2>Network</h2>
<pre>IPs:
<?= $ip ?>


Interfaces:
<?= $net ?></pre>
</div>

</div>

<div class="footer">
Safe read‑only server information
</div>

</body>
</html>
