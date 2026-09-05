<?php
// ===== Konfiguracja =====
$TO_EMAIL = "kontakt@autokord.pl";
$MAX_PER_WINDOW = 5;          // maks. liczba wiadomości
$WINDOW_SECONDS = 3600;       // w oknie czasowym (1 godzina)

if ($_SERVER["REQUEST_METHOD"] !== "POST") {
    http_response_code(405);
    echo "invalid method";
    exit;
}

// ===== Rate limit (na sesji + IP) =====
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
$now = time();
$ip = $_SERVER['REMOTE_ADDR'] ?? 'unknown';
$hits = $_SESSION['mail_hits'] ?? [];
// usuń wpisy starsze niż okno
$hits = array_values(array_filter($hits, fn($t) => ($now - $t) < $WINDOW_SECONDS));
if (count($hits) >= $MAX_PER_WINDOW) {
    http_response_code(429);
    echo "rate_limit";
    exit;
}

// ===== Honeypot =====
if (!empty($_POST['website'])) {
    // bot — udajemy sukces, żeby nie skłaniać do prób
    echo "success";
    exit;
}

// ===== Pobranie i walidacja pól =====
$name    = trim($_POST['name'] ?? '');
$email   = trim($_POST['email'] ?? '');
$phone   = trim($_POST['phone'] ?? '');
$message = trim($_POST['message'] ?? '');
$rodo    = !empty($_POST['rodo']);

$errors = [];

if (mb_strlen($name) < 2 || mb_strlen($name) > 100) {
    $errors[] = 'name';
}
if (!filter_var($email, FILTER_VALIDATE_EMAIL) || mb_strlen($email) > 150) {
    $errors[] = 'email';
}
if ($phone !== '' && !preg_match('/^[+]?[0-9\s\-()]{7,20}$/', $phone)) {
    $errors[] = 'phone';
}
if (mb_strlen($message) < 10 || mb_strlen($message) > 3000) {
    $errors[] = 'message';
}
if (!$rodo) {
    $errors[] = 'rodo';
}

if ($errors) {
    http_response_code(422);
    echo "validation_error: " . implode(',', $errors);
    exit;
}

// ===== Sanityzacja =====
$name    = htmlspecialchars($name, ENT_QUOTES, 'UTF-8');
$phone   = htmlspecialchars($phone, ENT_QUOTES, 'UTF-8');
$message = htmlspecialchars($message, ENT_QUOTES, 'UTF-8');
$sanitizedEmail = filter_var($email, FILTER_SANITIZE_EMAIL);
$sanizedName = str_replace(["\r", "\n"], ' ', $name); // ochrona header injection

$subject = "=?UTF-8?B?" . base64_encode("Formularz kontaktowy – autokord.pl – $sanizedName") . "?=";

$body  = "Imię i nazwisko: $name\n";
$body .= "Email: $sanitizedEmail\n";
$body .= "Telefon: " . ($phone !== '' ? $phone : '—') . "\n";
$body .= "Zgoda RODO: tak\n\n";
$body .= "Wiadomość:\n$message\n";

$headers  = "From: $TO_EMAIL\r\n";
$headers .= "Reply-To: $sanitizedEmail\r\n";
$headers .= "MIME-Version: 1.0\r\n";
$headers .= "Content-type: text/plain; charset=UTF-8\r\n";
$headers .= "X-Mailer: PHP/" . phpversion() . "\r\n";

if (mail($TO_EMAIL, $subject, $body, $headers)) {
    $hits[] = $now;
    $_SESSION['mail_hits'] = $hits;
    echo "success";
} else {
    http_response_code(500);
    echo "error";
}
?>

