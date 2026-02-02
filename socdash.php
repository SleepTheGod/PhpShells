<?php
function tableRow($key, $value) {
    return "<tr><th style='text-align:left;padding:5px;border:1px solid #ccc'>$key</th><td style='padding:5px;border:1px solid #ccc'>$value</td></tr>";
}

// Gather basic server info
$phpInfo = [
    "PHP uname" => php_uname(),
    "Server OS" => PHP_OS,
    "PHP Version" => PHP_VERSION,
    "Hostname" => gethostname(),
    "Loaded PHP Extensions" => implode(", ", get_loaded_extensions())
];

// Get OS release info
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

// Gather network interfaces
$interfaces = shell_exec("ip -o addr show 2>/dev/null");
if ($interfaces) {
    $phpInfo["Network Interfaces"] = nl2br(htmlspecialchars($interfaces));
} else {
    $phpInfo["Network Interfaces"] = "No network interfaces detected";
}

// Gather top 10 processes by CPU usage
$processes = shell_exec("ps -eo pid,ppid,cmd,%mem,%cpu --sort=-%cpu | head -n 11");
$phpInfo["Top Processes"] = "<pre style='margin:0'>" . htmlspecialchars($processes) . "</pre>";

// Optional: list listening ports
$ports = shell_exec("ss -tuln 2>/dev/null");
if ($ports) {
    $phpInfo["Listening Ports"] = "<pre style='margin:0'>" . htmlspecialchars($ports) . "</pre>";
} else {
    $phpInfo["Listening Ports"] = "Cannot detect ports or ss not available";
}

// HTML Output
echo "<!DOCTYPE html><html lang='en'><head><meta charset='UTF-8'><title>Server SOC Dashboard</title>
<style>
body { font-family: monospace; background: #0f0f0f; color: #9fef00; padding: 20px; }
h1 { color: #00ff99; }
table { border-collapse: collapse; width: 100%; max-width: 1200px; margin-bottom: 20px; }
th, td { border: 1px solid #333; padding: 5px; vertical-align: top; }
th { background: #111; }
td { background: #000; color: #9fef00; }
pre { white-space: pre-wrap; }
</style>
</head><body>";
echo "<h1>Server SOC Dashboard</h1>";
echo "<table>";

foreach ($phpInfo as $key => $value) {
    echo tableRow($key, $value);
}

echo "</table></body></html>";
?>
