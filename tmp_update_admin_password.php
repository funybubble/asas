<?php
$db = __DIR__ . '/cebelarstvo_cigoj.db';
$pdo = new PDO('sqlite:' . $db);
$pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
$hash = '$2y$10$B8XjImKVuSlqv60dOG7z7eiFXY12v4m0Nf5pfVVcwpFGpeTxB8R8C';
$stmt = $pdo->prepare('UPDATE admin_users SET password_hash = ? WHERE username = ?');
$stmt->execute([$hash, 'admin']);
$row = $pdo->query("SELECT id, username, password_hash FROM admin_users WHERE username='admin'")->fetch(PDO::FETCH_ASSOC);
print_r($row);
echo "\n";
?>
