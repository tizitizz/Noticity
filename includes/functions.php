<?php
// File: includes/functions.php

function format_time_ago($datetime_str) {
    $datetime = new DateTime($datetime_str);
    $now = new DateTime();
    $interval = $now->diff($datetime);

    if ($interval->y > 0) return $interval->y . 'a'; // Anni
    if ($interval->m > 0) return $interval->m . 'm'; // Mesi
    if ($interval->d >= 7) return floor($interval->d / 7) . 's'; // Settimane
    if ($interval->d > 0) return $interval->d . 'g'; // Giorni
    if ($interval->h > 0) return $interval->h . 'h'; // Ore
    if ($interval->i > 0) return $interval->i . 'min'; // Minuti

    return 'adesso';
}

/**
 * Forza l'interruzione delle parole troppo lunghe inserendo un'opportunità di "a capo".
 * @param string $text Il testo di input.
 * @param int $maxLength La lunghezza massima di una parola prima di forzare l'interruzione.
 * @return string Il testo formattato.
 */
function force_word_wrap($text, $maxLength = 30) {
    $regex = '/(\S{' . $maxLength . '})/u';
    // Aggiunge un "soft hyphen" (&shy;), un punto di interruzione invisibile.
    return preg_replace($regex, '$1&shy;', $text);
}
?>