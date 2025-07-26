<?php
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $to = "kontakt@autokord.pl";
    $subject = "Formularz kontaktowy – autokord.pl";

    // Pobierz dane z formularza
    $name = htmlspecialchars($_POST['name']);
    $email = filter_var($_POST['email'], FILTER_VALIDATE_EMAIL);
    $phone = htmlspecialchars($_POST['phone']);
    $message = htmlspecialchars($_POST['message']);

    // Składamy treść maila
    $body = "Imię i nazwisko: $name\n";
    $body .= "Email: $email\n";
    $body .= "Telefon: $phone\n\n";
    $body .= "Wiadomość:\n$message\n";

    $headers = "From: kontakt@autokord.pl\r\n";
    $headers .= "Reply-To: $email\r\n";

    if (mail($to, $subject, $body, $headers)) {
        echo "<p>Dziękujemy za kontakt! Wiadomość wysłana.</p>";
    } else {
        echo "<p>Błąd podczas wysyłania. Spróbuj ponownie lub napisz bezpośrednio.</p>";
    }
} else {
    header("Location: /"); // Jesli ktoś wejdzie bezpośrednio, przekieruj na stronę główną
    exit;
}
?>
