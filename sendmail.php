<?php
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $to = "kontakt@autokord.pl";
    $subject = "=?UTF-8?B?" . base64_encode("Formularz kontaktowy – autokord.pl") . "?=";

    $name = htmlspecialchars($_POST['name']);
    $email = filter_var($_POST['email'], FILTER_VALIDATE_EMAIL);
    $phone = htmlspecialchars($_POST['phone']);
    $message = htmlspecialchars($_POST['message']);

    $body = "Imię i nazwisko: $name\n";
    $body .= "Email: $email\n";
    $body .= "Telefon: $phone\n\n";
    $body .= "Wiadomość:\n$message\n";

    $headers = "From: kontakt@autokord.pl\r\n";
    $headers .= "Reply-To: $email\r\n";
    $headers .= "MIME-Version: 1.0\r\n";
    $headers .= "Content-type: text/plain; charset=UTF-8\r\n";

    if (mail($to, $subject, $body, $headers)) {
        echo "success"; // znak, że poszło OK
    } else {
        http_response_code(500); // błąd serwera
        echo "error";
    }
} else {
    http_response_code(405); // metoda nieobsługiwana
    echo "invalid method";
}
?>
