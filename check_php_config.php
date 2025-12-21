<?php
/**
 * Скрипт для проверки конфигурации PHP
 * Откройте в браузере: http://localhost/Blog/check_php_config.php
 */

echo "<h2>PHP Configuration Check</h2>";

echo "<h3>1. PHP Version</h3>";
echo "Version: " . phpversion() . "<br>";
echo "SAPI: " . php_sapi_name() . "<br>";

echo "<h3>2. PDO Extensions</h3>";
$pdoExtensions = [
    'pdo' => 'PDO',
    'pdo_mysql' => 'PDO MySQL',
    'mysqli' => 'MySQLi',
];

foreach ($pdoExtensions as $ext => $name) {
    $loaded = extension_loaded($ext);
    $status = $loaded ? '<span style="color: green;">✓ LOADED</span>' : '<span style="color: red;">✗ NOT LOADED</span>';
    echo "{$name} ({$ext}): {$status}<br>";
}

echo "<h3>3. PHP Configuration File</h3>";
echo "php.ini location: " . php_ini_loaded_file() . "<br>";
echo "Additional .ini files: " . php_ini_scanned_files() . "<br>";

echo "<h3>4. Database Connection Test</h3>";
try {
    $pdo = new PDO('mysql:host=localhost', 'root', '');
    echo '<span style="color: green;">✓ PDO MySQL connection successful!</span><br>';
} catch (PDOException $e) {
    echo '<span style="color: red;">✗ PDO MySQL connection failed: ' . $e->getMessage() . '</span><br>';
}

echo "<h3>5. Instructions</h3>";
echo "<p>If PDO MySQL is NOT LOADED, you need to:</p>";
echo "<ol>";
echo "<li>Open php.ini file (location shown above)</li>";
echo "<li>Find the line: <code>;extension=pdo_mysql</code></li>";
echo "<li>Remove the semicolon to uncomment it: <code>extension=pdo_mysql</code></li>";
echo "<li>Save the file</li>";
echo "<li>Restart Apache in XAMPP Control Panel</li>";
echo "</ol>";

echo "<h3>6. Alternative: Check XAMPP php.ini</h3>";
echo "<p>In XAMPP, the php.ini file is usually located at:</p>";
echo "<code>C:\\xampp\\php\\php.ini</code><br>";
echo "<p>Make sure you edit the correct php.ini file (the one shown above).</p>";

