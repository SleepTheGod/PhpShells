<?php
$DOCROOT = realpath($_SERVER['DOCUMENT_ROOT']);
$browsePath = isset($_GET['path'])
    ? realpath($_GET['path'])
    : $DOCROOT;

// Prevent directory escape
if ($browsePath === false || strpos($browsePath, $DOCROOT) !== 0) {
    $browsePath = $DOCROOT;
}

function h($s) {
    return htmlspecialchars((string)$s, ENT_QUOTES);
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title>Server Dashboard</title>
<meta name="viewport" content="width=device-width, initial-scale=1">
<style>
body {
    background:#050805;
    color:#9fef00;
    font-family:monospace;
    margin:0;
    padding:20px;
}
h1,h2 {
    color:#00ff00;
}
section {
    border:1px solid #00ff00;
    margin-bottom:15px;
    padding:10px;
    border-radius:6px;
}
button {
    width:100%;
    background:#000;
    color:#00ff00;
    border:1px solid #00ff00;
    padding:6px;
    cursor:pointer;
    font-family:monospace;
}
button:hover {
    background:#00ff00;
    color:#000;
}
pre {
    background:#001100;
    padding:10px;
    overflow:auto;
    max-height:400px;
}
a {
    color:#9fef00;
    text-decoration:none;
}
a:hover {
    text-decoration:underline;
}
.content {
    display:none;
    margin-top:10px;
}
table {
    border-collapse:collapse;
    width:100%;
}
td,th {
    border:1px solid #00ff00;
    padding:4px;
}
.footer {
    margin-top:30px;
    opacity:0.6;
}
</style>
</head>
<body>

<h1>🟢 Server Dashboard</h1>
<div class="footer">Made by Taylor Christian Newsome</div>

<?php
$info = [
    "Hostname" => gethostname(),
    "OS" => php_uname(),
    "PHP Version" => PHP_VERSION,
    "Server Software" => $_SERVER['SERVER_SOFTWARE'] ?? 'N/A',
    "Document Root" => $DOCROOT,
    "Server IP" => $_SERVER['SERVER_ADDR'] ?? 'N/A',
    "Client IP" => $_SERVER['REMOTE_ADDR'] ?? 'N/A',
];
?>

<section>
<button class="toggle">System Info</button>
<div class="content">
<pre><?php foreach ($info as $k=>$v) echo "$k: $v\n"; ?></pre>
</div>
</section>

<section>
<button class="toggle">Disk & Memory</button>
<div class="content">
<pre>
Disk Total: <?php echo disk_total_space("/") ?> bytes
Disk Free : <?php echo disk_free_space("/") ?> bytes
Memory Use: <?php echo memory_get_usage() ?> bytes
</pre>
</div>
</section>

<section>
<button class="toggle">Network Interfaces</button>
<div class="content">
<pre><?php echo shell_exec("ip addr 2>/dev/null || ifconfig -a"); ?></pre>
</div>
</section>

<section>
<button class="toggle">Running Processes</button>
<div class="content">
<pre><?php echo shell_exec("ps aux"); ?></pre>
</div>
</section>

<section>
<button class="toggle">Loaded PHP Extensions</button>
<div class="content">
<pre><?php echo implode("\n", get_loaded_extensions()); ?></pre>
</div>
</section>

<section>
<button class="toggle">File Browser (read‑only)</button>
<div class="content">
<p>Current path: <b><?php echo h($browsePath); ?></b></p>
<ul>
<?php
if ($browsePath !== $DOCROOT) {
    echo '<li><a href="?path=' . h(dirname($browsePath)) . '">[..]</a></li>';
}
foreach (scandir($browsePath) as $f) {
    if ($f === '.' || $f === '..') continue;
    $full = $browsePath . '/' . $f;
    if (is_dir($full)) {
        echo '<li>[DIR] <a href="?path=' . h($full) . '">' . h($f) . '</a></li>';
    } else {
        echo '<li>' . h($f) . ' (' . filesize($full) . ' bytes)</li>';
    }
}
?>
</ul>
</div>
</section>

<section>
<button class="toggle">MySQL Databases (safe)</button>
<div class="content">
<pre>
<?php
try {
    $pdo = new PDO("mysql:host=localhost", "root", "");
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    foreach ($pdo->query("SHOW DATABASES") as $db) {
        echo $db[0] . "\n";
    }
} catch (Throwable $e) {
    echo "MySQL unavailable: " . $e->getMessage();
}
?>
</pre>
</div>
</section>

<script>
document.querySelectorAll(".toggle").forEach(btn => {
    btn.onclick = () => {
        let c = btn.nextElementSibling;
        c.style.display = c.style.display === "block" ? "none" : "block";
    };
});
</script>

</body>
</html>
