<?php
// TEMPORARY DIAGNOSTIC — DELETE AFTER USE.
// Browse to: https://aiu.edu.eg/upload-diag.php
// This shows the live PHP upload limits and lets you POST a file to see
// exactly what arrives at the server (bypassing Laravel/Livewire entirely).

header('Content-Type: text/html; charset=utf-8');

$ini = [
    'upload_max_filesize' => ini_get('upload_max_filesize'),
    'post_max_size'       => ini_get('post_max_size'),
    'memory_limit'        => ini_get('memory_limit'),
    'max_execution_time'  => ini_get('max_execution_time'),
    'max_input_time'      => ini_get('max_input_time'),
    'file_uploads'        => ini_get('file_uploads'),
    'max_file_uploads'    => ini_get('max_file_uploads'),
    'sapi'                => php_sapi_name(),
    'php_version'         => PHP_VERSION,
];

echo "<h1>PHP upload diagnostics</h1>";
echo "<table border='1' cellpadding='6'><tr><th>Setting</th><th>Value</th></tr>";
foreach ($ini as $k => $v) {
    echo "<tr><td><b>" . htmlspecialchars($k) . "</b></td><td><code>" . htmlspecialchars((string) $v) . "</code></td></tr>";
}
echo "</table>";

echo "<p><b>Expected (after .user.ini / MultiPHP):</b> upload_max_filesize=128M, post_max_size=128M.<br>"
   . "If you see <code>2M</code> / <code>8M</code> / <code>40M</code>, then .user.ini is being IGNORED — "
   . "you must use cPanel → MultiPHP INI Editor.</p>";

echo "<hr><h2>Upload test (raw — no Laravel)</h2>";

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    echo "<h3>POST received</h3>";
    echo "<p>CONTENT_LENGTH header: <code>" . htmlspecialchars($_SERVER['CONTENT_LENGTH'] ?? 'none') . "</code></p>";
    echo "<p>php://input length: <code>" . strlen(file_get_contents('php://input')) . "</code></p>";
    echo "<p><b>\$_POST:</b><pre>" . htmlspecialchars(print_r($_POST, true)) . "</pre></p>";
    echo "<p><b>\$_FILES:</b><pre>" . htmlspecialchars(print_r($_FILES, true)) . "</pre></p>";

    if (empty($_FILES) && empty($_POST) && (int) ($_SERVER['CONTENT_LENGTH'] ?? 0) > 0) {
        echo "<p style='color:red;font-weight:bold'>"
           . "❗ \$_FILES and \$_POST are EMPTY but CONTENT_LENGTH is non-zero. "
           . "PHP silently rejected the upload — almost certainly because the file exceeded post_max_size. "
           . "This is the same condition that produces the Livewire 422 error."
           . "</p>";
    }
} else {
    echo '<form method="post" enctype="multipart/form-data">'
       . '<input type="file" name="testfile" required> '
       . '<button type="submit">Upload test file</button>'
       . '</form>';
}
