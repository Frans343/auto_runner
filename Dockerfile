# Utilise PHP 8.2 CLI (mode console, idéal pour scripts et cron)
FROM php:8.2-cli

# Définit le dossier de travail
WORKDIR /app

# Copie tous les fichiers de ton dépôt vers /app dans le conteneur
COPY . .

# Commande exécutée au lancement du conteneur
CMD ["php", "auto_runner.php"]
