<?php
$host = 'db';
$db   = 'db';
$user = 'db';
$pass = 'db';
$charset = 'utf8mb4';

$dsn = "mysql:host=$host;dbname=$db;charset=$charset";
$options = [
  PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
  PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
];

function is_hex_format($str) {
  // IPv4 in hex: 8 hex digits, IPv6 in hex: 32 hex digits (all uppercase, as we output)
  return (bool)preg_match('/^[A-F0-9]{8}$|^[A-F0-9]{32}$/', $str);
}

try {
  $pdo = new PDO($dsn, $user, $pass, $options);

  $stmt = $pdo->query("SELECT id, ip FROM rptrecords");

  while ($row = $stmt->fetch()) {
    $id = $row['id'];
    $ip = $row['ip'];

    // Skip if already hex
    if (is_hex_format($ip)) {
      echo "Skipped ID {$id}: Already in HEX format: {$ip}\n";
      continue;
    }

    // Validate and convert IP
    if (filter_var($ip, FILTER_VALIDATE_IP)) {
      $packed = inet_pton($ip);
      if ($packed !== false) {
        $hex = strtoupper(bin2hex($packed));
        $updateStmt = $pdo->prepare("UPDATE rptrecords SET ip = :hex WHERE id = :id");
        $updateStmt->execute([
          ':hex' => $hex,
          ':id'  => $id
        ]);
        echo "Updated ID {$id}: {$ip} → {$hex}\n";
      } else {
        echo "Skipped ID {$id}: Could not convert IP: {$ip}\n";
      }
    } else {
      echo "Skipped ID {$id}: Invalid IP format: {$ip}\n";
    }
  }

} catch (PDOException $e) {
  echo "Database connection failed: " . $e->getMessage();
}
