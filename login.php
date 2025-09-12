<?php
require_once 'config.php';

// Only handle POST requests
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['message' => 'Method not allowed']);
    exit;
}

// Support both JSON (AJAX) and form-encoded POSTs. Prefer JSON when Content-Type is application/json.
$contentType = $_SERVER['CONTENT_TYPE'] ?? '';
if (stripos($contentType, 'application/json') !== false) {
    $input = json_decode(file_get_contents('php://input'), true);
    $username = htmlspecialchars(trim($input['username'] ?? ''), ENT_QUOTES, 'UTF-8');
    $password = $input['password'] ?? '';
    $is_form = false;
} else {
    // form submission (application/x-www-form-urlencoded or multipart)
    $username = htmlspecialchars(trim($_POST['username'] ?? ''), ENT_QUOTES, 'UTF-8');
    $password = $_POST['password'] ?? '';
    $is_form = true;
}

// Basic validation
if (empty($username) || empty($password)) {
    if ($is_form) {
        // Redirect back with error flag for simple form UX
        header('Location: admin_login.php?error=1');
        exit;
    }
    http_response_code(400);
    echo json_encode(['message' => 'Username and password required']);
    exit;
}

// Get client IP
$ip_address = $_SERVER['HTTP_X_FORWARDED_FOR'] ?? $_SERVER['REMOTE_ADDR'] ?? 'unknown';

// Ensure login_attempts table exists (idempotent)
$pdo->exec(
    "CREATE TABLE IF NOT EXISTS login_attempts (
        id INTEGER PRIMARY KEY AUTOINCREMENT,
        email TEXT,
        ip_address TEXT,
        attempted_at DATETIME DEFAULT (datetime('now')),
        success INTEGER DEFAULT 0
    );"
);

// DEVELOPMENT: allow temporary bypass of rate-limiting by creating a file named
// DISABLE_RATE_LIMIT in the project root. This is handy when resetting or bootstrapping
// the admin account. Remove the file to re-enable rate limiting.
$bypass_rate_limit = false;
if (file_exists(__DIR__ . '/DISABLE_RATE_LIMIT')) {
    $bypass_rate_limit = true;
}

// Check rate limits (5 attempts per email in 15 minutes, 10 attempts per IP in 15 minutes)
$now = date('Y-m-d H:i:s');
$fifteen_minutes_ago = date('Y-m-d H:i:s', strtotime('-15 minutes'));

// Check username rate limit (skip if bypass enabled)
if (!$bypass_rate_limit) {
    $stmt = $pdo->prepare("SELECT COUNT(*) as attempts FROM login_attempts WHERE email = ? AND attempted_at > ? AND success = 0");
    $stmt->execute([$username, $fifteen_minutes_ago]);
    $username_attempts = $stmt->fetch()['attempts'];

    if ($username_attempts >= 5) {
        // Record failed attempt
        $stmt = $pdo->prepare("INSERT INTO login_attempts (email, ip_address, success) VALUES (?, ?, 0)");
        $stmt->execute([$username, $ip_address]);
        
        http_response_code(429);
        echo json_encode(['message' => 'Too many failed attempts for this username. Try again in 15 minutes.', 'retry_after' => 900]);
        exit;
    }
}

// Check IP rate limit (skip if bypass enabled)
if (!$bypass_rate_limit) {
    $stmt = $pdo->prepare("SELECT COUNT(*) as attempts FROM login_attempts WHERE ip_address = ? AND attempted_at > ? AND success = 0");
    $stmt->execute([$ip_address, $fifteen_minutes_ago]);
    $ip_attempts = $stmt->fetch()['attempts'];

    if ($ip_attempts >= 10) {
        // Record failed attempt
        $stmt = $pdo->prepare("INSERT INTO login_attempts (email, ip_address, success) VALUES (?, ?, 0)");
        $stmt->execute([$username, $ip_address]);
        
        http_response_code(429);
        echo json_encode(['message' => 'Too many failed attempts from this IP. Try again in 15 minutes.', 'retry_after' => 900]);
        exit;
    }
}

// Find user (do not assume `is_active` column exists in older DB schema)
$stmt = $pdo->prepare("SELECT * FROM admin_users WHERE username = ?");
$stmt->execute([$username]);
$user = $stmt->fetch(PDO::FETCH_ASSOC);

// If the `is_active` column exists, enforce it; otherwise assume active by default
if ($user && isset($user['is_active']) && (int)$user['is_active'] !== 1) {
    // Record failed attempt
    $stmt = $pdo->prepare("INSERT INTO login_attempts (email, ip_address, success) VALUES (?, ?, 0)");
    $stmt->execute([$username, $ip_address]);

    http_response_code(401);
    echo json_encode(['message' => 'Invalid credentials']);
    exit;
}

if (!$user || !password_verify($password, $user['password_hash'])) {
    // Record failed attempt
    $stmt = $pdo->prepare("INSERT INTO login_attempts (email, ip_address, success) VALUES (?, ?, 0)");
    $stmt->execute([$username, $ip_address]);
    
    http_response_code(401);
    echo json_encode(['message' => 'Invalid credentials']);
    exit;
}

// Record successful attempt
$stmt = $pdo->prepare("INSERT INTO login_attempts (email, ip_address, success) VALUES (?, ?, 1)");
$stmt->execute([$username, $ip_address]);

// Generate JWT token (15 minutes expiry)
$payload = [
    'user_id' => $user['id'],
    'username' => $user['username'],
    'email' => $user['email'] ?? '',
    'role' => $user['role'] ?? 'admin',
    'iat' => time(),
    'exp' => time() + (15 * 60) // 15 minutes
];

// Use fully-qualified class name to avoid file-local "use" dependency issues
$jwt = \Firebase\JWT\JWT::encode($payload, JWT_SECRET, 'HS256');

// Set secure cookie (for refresh token - simplified for demo)
// Choose cookie options depending on whether the request appears to be HTTPS.
// When served over HTTPS (e.g., GitHub.dev preview), use Secure + SameSite=None so the
// cookie can be accepted in some embedded contexts. For local HTTP development we keep
// Secure=false and SameSite=Strict.
$is_https = false;
if (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') {
    $is_https = true;
} elseif (!empty($_SERVER['HTTP_X_FORWARDED_PROTO']) && $_SERVER['HTTP_X_FORWARDED_PROTO'] === 'https') {
    $is_https = true;
} elseif (!empty($_SERVER['HTTP_CF_VISITOR']) && strpos($_SERVER['HTTP_CF_VISITOR'], 'https') !== false) {
    $is_https = true;
}

$cookie_options = [
    'expires' => time() + (7 * 24 * 60 * 60), // 7 days
    'path' => '/',
    'domain' => '',
    'secure' => $is_https, // require secure when over HTTPS
    'httponly' => true,
    'samesite' => $is_https ? 'None' : 'Strict'
];

// PHP's setcookie accepted an array of options since 7.3; this usage is compatible.
setcookie('refresh_token', $jwt, $cookie_options);

if ($is_form) {
    // For browser form submissions, instead of a bare 302 (which may be followed inside an iframe
    // or blocked by preview environments), return a small HTML page that attempts a top-level
    // navigation. The Set-Cookie header has already been emitted above so the cookie will apply.
    header('Content-Type: text/html; charset=utf-8');
    $dashboard = 'backend.php';
    echo "<!doctype html>\n";
    echo "<html><head><meta charset=\"utf-8\"><title>Prijava uspešna</title>\n";
    // Meta refresh as an extra fallback
    echo "<meta http-equiv=\"refresh\" content=\"0;url={$dashboard}\">\n";
    echo "</head><body>\n";
    echo "<script>\n";
    echo "try {\n";
    echo "  if (window.top && window.top !== window) {\n";
    echo "    window.top.location.replace('{$dashboard}');\n";
    echo "  } else {\n";
    echo "    window.location.replace('{$dashboard}');\n";
    echo "  }\n";
    echo "} catch (e) {\n";
    echo "  try { window.location.replace('{$dashboard}'); } catch (e) { /* ignore */ }\n";
    echo "}\n";
    echo "</script>\n";
    echo "<p>Prijava uspešna. Če se brskalnik ne preusmeri samodejno, <a href=\"{$dashboard}\">kliknite tukaj</a>.</p>\n";
    // Large fallback button to open dashboard in a new tab or top window
    echo "<div style=\"margin-top:16px;\">\n";
    echo "  <a href=\"{$dashboard}\" target=\"_blank\" rel=\"noopener noreferrer\" style=\"display:inline-block;background:#f59e0b;color:#fff;padding:8px 12px;border-radius:6px;text-decoration:none\">Open dashboard in new tab</a>\n";
    echo "  <button id=\"openTopBtn\" style=\"margin-left:8px;padding:8px 12px;border-radius:6px;background:#ef4444;color:#fff;border:none;cursor:pointer\">Open dashboard (top)</button>\n";
    echo "</div>\n";
    echo "<script>document.getElementById('openTopBtn').addEventListener('click', function(){ try{ if(window.top && window.top !== window){ window.top.location.href='${dashboard}'; } else { window.location.href='${dashboard}'; } }catch(e){ window.open('${dashboard}','_blank'); } });</script>\n";
    echo "</body></html>\n";
    exit;
}

// Default: JSON response for API/AJAX clients
echo json_encode([
    'message' => 'Login successful',
    'token' => $jwt,
    'expires_in' => 900, // 15 minutes
    'user' => [
        'id' => $user['id'],
        'username' => $user['username'],
        'email' => $user['email'] ?? '',
        'role' => $user['role'] ?? 'admin'
    ]
]);
?>