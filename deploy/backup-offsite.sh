#!/usr/bin/env bash
# Copie les sauvegardes locales (storage/app/backups) vers un bucket
# Cloudflare R2, pour survivre à la perte totale du serveur — un backup
# stocké uniquement sur le VPS ne sert à rien si le VPS lui-même est perdu.
#
# Ne relance PAS mysqldump : synchronise simplement le dossier déjà produit
# par `php artisan backup:database` (planifié chaque nuit à 02h00).
#
# Installation sur le serveur (à faire une seule fois, hors de ce dépôt) :
#   1. Installer rclone : curl https://rclone.org/install.sh | sudo bash
#   2. Configurer le remote "r2" avec les identifiants API du bucket R2
#      (rclone config), stockés dans ~/.config/rclone/rclone.conf —
#      jamais commités dans ce dépôt.
#   3. Copier ce script sur le serveur (ex: /srv/scripts/backup-offsite.sh)
#      et ajuster APP_DIR / REMOTE ci-dessous à la config réelle.
#   4. Ajouter la tâche cron système (30 min après la sauvegarde locale) :
#        30 2 * * * /srv/scripts/backup-offsite.sh >> /var/log/backup-offsite.log 2>&1
#
# Le scheduler Laravel (sauvegarde locale + purge) tourne lui via Supervisor
# dans le conteneur applicatif (voir deploy/supervisor-scheduler.conf) —
# ce script-ci est le seul maillon qui reste un cron système classique,
# car il agit sur le volume Docker depuis l'extérieur du conteneur.

set -euo pipefail

APP_DIR="/var/www/app-de-gestion-boulangerie"        # à ajuster au chemin réel sur le VPS
REMOTE="r2:gestion-boulangerie-backups"               # remote rclone : bucket R2 dédié

echo "[$(date '+%Y-%m-%d %H:%M:%S')] Début synchronisation hors-site"

rclone sync "${APP_DIR}/storage/app/backups" "${REMOTE}" --log-level INFO

echo "[$(date '+%Y-%m-%d %H:%M:%S')] Synchronisation terminée"
