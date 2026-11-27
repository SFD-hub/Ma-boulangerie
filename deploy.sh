#!/bin/bash
# Script de déploiement reproductible et SÛR.
# À exécuter depuis le dossier de l'app sur le serveur (/srv/apps/ma-boulangerie).
#
# Ordre voulu :
#   1. sauvegarde de la base AVANT toute modification (avec l'ancienne version encore en marche) ;
#   2. récupération du code et construction de l'image (le site continue de répondre) ;
#   3. mode maintenance, migration des données, remplacement du conteneur ;
#   4. vérification ; en cas d'échec, mode d'emploi du retour en arrière.
#
# À lancer de préférence la nuit ou avant l'ouverture de la boulangerie.
set -euo pipefail

COMPOSE_FILE="docker-compose.prod.yml"
URL_SANTE="${URL_SANTE:-https://boulangerie.gallastech.com/up}"
DC="docker compose -f $COMPOSE_FILE"

echo "==> 1/6  Sauvegarde de la base AVANT la mise à jour"
AVANT=$($DC exec -T app sh -c 'ls -1 storage/app/backups/backup_*.sql 2>/dev/null | wc -l' || echo 0)
$DC exec -T app php artisan backup:database
SAUVEGARDE=$($DC exec -T app sh -c 'ls -t storage/app/backups/backup_*.sql | head -1' | tr -d '\r')
TAILLE=$($DC exec -T app sh -c "wc -c < $SAUVEGARDE" | tr -d '\r ')
if [ -z "$SAUVEGARDE" ] || [ "${TAILLE:-0}" -lt 20000 ]; then
    echo "ERREUR : la sauvegarde est absente ou vide ($SAUVEGARDE, ${TAILLE:-0} octets). Mise à jour ANNULÉE, rien n'a été modifié."
    exit 1
fi
echo "    Sauvegarde : $SAUVEGARDE (${TAILLE} octets)"
echo "    Copie de sécurité sur le serveur : ./sauvegardes-avant-maj/"
mkdir -p sauvegardes-avant-maj
$DC cp "app:/var/www/html/$SAUVEGARDE" "sauvegardes-avant-maj/$(basename "$SAUVEGARDE")"

echo "==> 2/6  Récupération du code"
git pull origin main

echo "==> 3/6  Construction de l'image (le site répond toujours)"
$DC build

echo "==> 4/6  Mode maintenance puis migration des données"
$DC exec -T app php artisan down --retry=30 || true
if ! $DC run --rm -T app php artisan migrate --force; then
    echo "ERREUR pendant la migration. Le site reste en maintenance."
    echo "  -> Voir deploy/MISE_A_JOUR.md, section « Retour en arrière »."
    echo "  -> Sauvegarde d'avant mise à jour : sauvegardes-avant-maj/$(basename "$SAUVEGARDE")"
    exit 1
fi

echo "==> 5/6  Remplacement du conteneur et mise en cache"
$DC up -d
for i in $(seq 1 30); do
    $DC exec -T app php artisan --version >/dev/null 2>&1 && break
    sleep 2
done
$DC exec -T app php artisan config:cache
$DC exec -T app php artisan route:cache
$DC exec -T app php artisan view:cache
$DC exec -T app php artisan up || true

echo "==> 6/6  Vérification"
OK=0
for i in $(seq 1 15); do
    CODE=$(curl -s -o /dev/null -w '%{http_code}' --max-time 10 "$URL_SANTE" || echo 000)
    if [ "$CODE" = "200" ]; then OK=1; break; fi
    sleep 3
done
if [ "$OK" != "1" ]; then
    echo "ATTENTION : le site ne répond pas correctement après la mise à jour (code $CODE sur $URL_SANTE)."
    echo "  -> Consultez : $DC logs --tail=100 app"
    echo "  -> Si besoin, retour en arrière : deploy/MISE_A_JOUR.md"
    exit 1
fi

echo "==> Nettoyage des anciennes images Docker inutilisées"
docker image prune -f

echo "==> Déploiement terminé. Faites maintenant la vérification : bash deploy/verif-apres-deploiement.sh"
