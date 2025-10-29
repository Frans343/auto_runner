<?php
/**
 * AUTO RUNNER — simulateur de navigateur PHP pour exécuter des scripts distants périodiquement.
 * Fonctionne parfaitement sur Render ou tout autre VPS/hébergement libre.
 */

ini_set('max_execution_time', 0);
ini_set('display_errors', 1);
error_reporting(E_ALL);

date_default_timezone_set('UTC');

// 📦 URLs des scripts à exécuter
$tasks = [
    'refreshTokens' => 'https://reatouch.appli-genie.com/scripts/refreshTokens.php',
    'dispatcher'    => 'https://reatouch.appli-genie.com/scripts/dispatcher.php'
];

// ⏱ Intervalle entre deux cycles (en secondes)
$interval = 900; // 15 minutes
$logFile = __DIR__ . '/auto_runner_log.txt';

// 🔧 Fonction de log
function logMsg($message)
{
    global $logFile;
    $timestamp = '[' . date('Y-m-d H:i:s') . '] ';
    echo $timestamp . $message . "\n";
    file_put_contents($logFile, $timestamp . $message . "\n", FILE_APPEND);
}

logMsg("🚀 Auto-runner démarré");
logMsg("Intervalle : $interval secondes");
logMsg("----------------------------------------------");

while (true) {
    foreach ($tasks as $name => $url) {
        $start = microtime(true);
        logMsg("⏳ [$name] Lancement → $url");

        $ch = curl_init($url);
        curl_setopt_array($ch, [
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_TIMEOUT => 60,
            CURLOPT_FOLLOWLOCATION => true,
            CURLOPT_USERAGENT => 'AutoRunner/1.0 (Render PHP)',
        ]);
        $response = curl_exec($ch);
        $code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $err  = curl_error($ch);
        curl_close($ch);

        $elapsed = round(microtime(true) - $start, 2);

        if ($err) {
            logMsg("❌ [$name] Erreur cURL : $err");
        } elseif ($code >= 200 && $code < 300) {
            logMsg("✅ [$name] Exécuté avec succès (HTTP $code, ${elapsed}s)");
        } else {
            logMsg("⚠️ [$name] Réponse inattendue (HTTP $code, ${elapsed}s)");
            logMsg("↪️ Réponse partielle : " . substr($response, 0, 200));
        }
    }

    logMsg("🕒 Pause de $interval secondes avant le prochain cycle");
    logMsg("----------------------------------------------");
    sleep($interval);
}
?>