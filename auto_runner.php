<?php
/**
 * 🌐 Auto Runner pour exécuter périodiquement des scripts distants.
 * Fonctionne parfaitement sur Render en mode Background Worker.
 * 
 * 🚀 Exécute automatiquement :
 *  - refreshTokens.php
 *  - dispatcher.php
 */

ini_set('display_errors', 1);
error_reporting(E_ALL);

// 🕒 Configuration principale
$intervalSeconds = 900; // 15 minutes
$logFile = __DIR__ . '/runner.log';
$urls = [
    'refreshTokens' => 'https://reatouch.appli-genie.com/scripts/refreshTokens.php',
    'dispatcher'    => 'https://reatouch.appli-genie.com/scripts/dispatcher.php'
];

// 📦 Fonction pour journaliser proprement
function logMsg(string $msg): void {
    global $logFile;
    $timestamp = date('Y-m-d H:i:s');
    file_put_contents($logFile, "[{$timestamp}] {$msg}\n", FILE_APPEND);
    echo "[{$timestamp}] {$msg}\n";
}

// 🔁 Fonction pour exécuter une requête distante
function runScript(string $name, string $url): bool {
    logMsg("⏳ [{$name}] Lancement → {$url}");

    $ch = curl_init($url);
    curl_setopt_array($ch, [
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_TIMEOUT => 60,
        CURLOPT_SSL_VERIFYPEER => false, // 🚫 Ignore les erreurs SSL
        CURLOPT_SSL_VERIFYHOST => false
    ]);

    $response = curl_exec($ch);
    $error = curl_error($ch);
    $code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);

    if ($error) {
        logMsg("❌ [{$name}] Erreur cURL : {$error}");
        return false;
    }

    if ($code >= 200 && $code < 300) {
        logMsg("✅ [{$name}] Réponse HTTP {$code} : succès");
        return true;
    }

    logMsg("⚠️ [{$name}] Réponse HTTP {$code} : {$response}");
    return false;
}

// 🚀 Lancement de la boucle principale
logMsg("🚀 Auto-runner démarré");
logMsg("⏱ Intervalle : {$intervalSeconds} secondes");
logMsg("----------------------------------------------");

while (true) {
    foreach ($urls as $name => $url) {
        runScript($name, $url);
    }

    logMsg("🕒 Pause de {$intervalSeconds} secondes avant le prochain cycle");
    logMsg("----------------------------------------------");
    sleep($intervalSeconds);
}
?>
