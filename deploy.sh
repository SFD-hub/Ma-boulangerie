#!/bin/bash
# Script de déploiement reproductible.
# À exécuter depuis le dossier de l'app sur le serveur (/srv/apps/ma-boulangerie).
set -euo pipefail

COMPOSE_FILE="docker-compose.prod.yml"

echo "==> Récupération du code"
git pull origin main

echo "==> Construction de l'image"
docker compose -f "$COMPOSE_FILE" build

echo "==> Redémarrage des conteneurs"
docker compose -f "$COMPOSE_FILE" up -d

echo "==> Migrations base de données"
docker compose -f "$COMPOSE_FILE" exec -T app php artisan migrate --force

echo "==> Mise en cache de la configuration"
docker compose -f "$COMPOSE_FILE" exec -T app php artisan config:cache
docker compose -f "$COMPOSE_FILE" exec -T app php artisan route:cache
docker compose -f "$COMPOSE_FILE" exec -T app php artisan view:cache

echo "==> Nettoyage des anciennes images Docker inutilisées"
docker image prune -f

echo "==> Déploiement terminé"
