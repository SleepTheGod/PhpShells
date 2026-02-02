<?php
// Simple green/black hacker-style server info dashboard
?>

<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title>Server Dashboard</title>
<meta name="viewport" content="width=device-width, initial-scale=1">
<style>
body {
    margin: 0;
    padding: 0;
    background: #050805;
    color: #9fef00;
    font-family: monospace;
}
h1, h2 {
    color: #00ff00;
}
pre {
    background: #001100;
    padding: 10px;
    overflow-x: auto;
}
section {
    margin: 10px 0;
    border: 1px solid #00ff00;
    padding: 10px;
    border-radius: 5px;
}
button {
    background: #000;
    color: #00ff00;
    border: 1px solid #00ff00;
    padding: 5px 10px;
    cursor: pointer;
    margin-bottom: 5px;
}
button:hover {
    background: #0f0;
    color: #000;
}
.collapsible {
    cursor: pointer;
    width: 100%;
    text-align: left;
    outline: none;
}
.content {
    display: none;
    padding: 5px 10px;
    margin-top: 5px;
    border-top: 1px dashed #00ff00;
}
</style>
</head>
<body>

<h1>Server Dashboard</h1>

<?php
// System Info
$system_info = [
    "PHP Version" => phpversion(),
    "Server Software" => $_SERVER['SERVER_SOFTWARE'] ?? 'N/A',
    "Document Root" => $_SERVER['DOCUMENT_ROOT'] ?? 'N/A',
    "OS" => PHP_OS,
    "Hostname" => gethostname(),
    "Current User" => get_current_user(),
    "Server IP" => $_SERVER['SERVER_ADDR'] ?? 'N/A',
    "Client IP" => $_SERVER['REMOTE_ADDR'] ?? 'N/A',
];
?>

<section>
<button class="collapsible">System Info</button>
<div class="content">
<pre>
<?php
foreach ($system_info as $key => $value) {
    echo "$key: $value\n";
}
?>
</pre>
</div>
</section>

<section>
<button class="collapsible">Disk & Memory</button>
<div class="content">
<pre>
Disk Total: <?php echo disk_total_space("/") . " bytes\n"; ?>
Disk Free: <?php echo disk_free_space("/") . " bytes\n"; ?>
<?php if (function_exists('memory_get_usage')): ?>
Memory Usage: <?php echo memory_get_usage() . " bytes\n"; ?>
<?php endif; ?>
</pre>
</div>
</section>

<section>
<button class="collapsible">Running Processes</button>
<div class="content">
<pre>
<?php
if (strtoupper(substr(PHP_OS, 0, 3)) !== 'WIN') {
    echo shell_exec("ps aux");
} else {
    echo shell_exec("tasklist");
}
?>
</pre>
</div>
</section>

<section>
<button class="collapsible">Network Interfaces</button>
<div class="content">
<pre>
<?php
if (strtoupper(substr(PHP_OS, 0, 3)) !== 'WIN') {
    echo shell_exec("ifconfig -a");
} else {
    echo shell_exec("ipconfig /all");
}
?>
</pre>
</div>
</section>

<section>
<button class="collapsible">MySQL Databases</button>
<div class="content">
<pre>
<?php
$host = 'localhost';
$user = 'root';
$pass = ''; // use your own password safely
try {
    $pdo = new PDO("mysql:host=$host", $user, $pass);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    $stmt = $pdo->query("SHOW DATABASES");
    $dbs = $stmt->fetchAll(PDO::FETCH_COLUMN);
    foreach ($dbs as $db) echo "$db\n";
} catch (PDOException $e) {
    echo "Cannot connect to MySQL: " . $e->getMessage();
}
?>
</pre>
</div>
</section>

<script>
// Collapsible sections
var coll = document.getElementsByClassName("collapsible");
for (let i = 0; i < coll.length; i++) {
    coll[i].addEventListener("click", function() {
        this.classList.toggle("active");
        var content = this.nextElementSibling;
        content.style.display = content.style.display === "block" ? "none" : "block";
    });
}
</script>

</body>
</html>
