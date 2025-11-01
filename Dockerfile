FROM php:8.2-cli

WORKDIR /app

COPY . .

# installer curl si pas présent
RUN apt-get update && apt-get install -y curl

# Render doit voir un port → on expose un port bidon
EXPOSE 10000

# CMD : lancer ton auto_runner + un mini server juste pour occuper le port
CMD php auto_runner.php & php -S 0.0.0.0:10000
