<?php
// File: includes/functions.php (Versione Corretta e Completa)

function getUserComune($userId, $pdo) {
    $stmt = $pdo->prepare("SELECT comune_id FROM utenti WHERE id = ?");
    $stmt->execute([$userId]);
    return $stmt->fetchColumn();
}

/**
 * Formatta una data in un formato relativo abbreviato (es. "4h", "2g").
 * @param string $datetime_str La data in formato stringa.
 * @return string Il tempo trascorso formattato.
 */
function format_time_ago($datetime_str) {
    if(empty($datetime_str)) {
        return '';
    }
    try {
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
    } catch (Exception $e) {
        return ''; // In caso di data non valida, non mostra nulla
    }
}

/**
 * Forza l'interruzione delle parole troppo lunghe.
 * @param string $text Il testo di input.
 * @return string Il testo formattato.
 */
function force_word_wrap($text, $maxLength = 30) {
    $regex = '/(\S{' . $maxLength . '})/u';
    return preg_replace($regex, '$1&shy;', $text);
}
?>