<?php
function tableRow($key, $value) {
    return "<tr><th style='text-align:left;padding:5px;border:1px solid #ccc'>$key</th><td style='padding:5px;border:1px solid #ccc'>$value</td></tr>";
}

// Gather server info
$phpInfo = [
    "PHP uname" => php_uname(),
    "Server OS" => PHP_OS,
    "PHP Version" => PHP_VERSION,
    "Hostname" => gethostname(),
    "Loaded PHP Extensions" => implode(", ", get_loaded_extensions())
];

// Additional OS info if available
$osRelease = @file_get_contents("/etc/os-release");
if ($osRelease) {
    $osLines = explode("\n", $osRelease);
    foreach ($osLines as $line) {
        if (strpos($line, "=") !== false) {
            list($k, $v) = explode("=", $line, 2);
            $phpInfo[$k] = trim($v, '"');
        }
    }
}

// HTML Output
echo "<!DOCTYPE html><html lang='en'><head><meta charset='UTF-8'><title>Server Info</title>
<style>
body { font-family: monospace; background: #0f0f0f; color: #9fef00; padding: 20px; }
table { border-collapse: collapse; width: 100%; max-width: 1000px; }
th, td { border: 1px solid #ccc; padding: 5px; }
th { background: #111; }
td { background: #000; color: #9fef00; }
</style>
</head><body>";
echo "<h1>Server Information</h1>";
echo "<table>";

foreach ($phpInfo as $key => $value) {
    echo tableRow($key, htmlspecialchars($value));
}

echo "</table></body></html>";
?>
