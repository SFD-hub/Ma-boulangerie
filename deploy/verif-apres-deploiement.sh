#!/bin/bash
# Vérification du site EN LIGNE juste après une mise à jour (ou une première mise en service).
# Usage : bash deploy/verif-apres-deploiement.sh https://boulangerie.gallastech.com
# Ne modifie rien : lit seulement les réponses du site.
SITE="${1:-https://boulangerie.gallastech.com}"
HOTE=$(echo "$SITE" | sed -E 's#https?://##; s#/.*##')
OK=0; KO=0
verif() { # verif "texte" "commande qui renvoie 0 si bon"
    if eval "$2" >/dev/null 2>&1; then echo "  OK  $1"; OK=$((OK+1)); else echo "  KO  $1"; KO=$((KO+1)); fi
}
code() { curl -s -o /dev/null -w '%{http_code}' --max-time 15 "$@"; }
entetes() { curl -s -D - -o /dev/null --max-time 15 "$@"; }

echo "Vérification de $SITE"
echo "== Disponibilité"
verif "la page de connexion répond (200)"                    '[ "$(code $SITE/login)" = "200" ]'
verif "le contrôle de santé /up répond (200)"                '[ "$(code $SITE/up)" = "200" ]'
verif "une page protégée renvoie vers la connexion (302)"    '[ "$(code $SITE/dashboard)" = "302" ]'
verif "une page qui n existe pas donne 404"                  '[ "$(code $SITE/page-inexistante-xyz)" = "404" ]'
echo "== HTTPS"
verif "http:// renvoie vers https://"                        'code=$(code http://$HOTE/login); [ "$code" = "301" ] || [ "$code" = "302" ] || [ "$code" = "308" ]'
verif "certificat valide (au moins 14 jours restants)"       'echo | openssl s_client -servername $HOTE -connect $HOTE:443 2>/dev/null | openssl x509 -noout -checkend 1209600'
verif "en-tête Strict-Transport-Security présent"            'entetes $SITE/login | grep -qi "^strict-transport-security:"'
echo "== En-têtes de sécurité"
verif "X-Frame-Options présent UNE seule fois (DENY)"        '[ "$(entetes $SITE/login | grep -ci "^x-frame-options:")" = "1" ] && entetes $SITE/login | grep -qi "^x-frame-options: *deny"'
verif "Content-Security-Policy présent"                      'entetes $SITE/login | grep -qi "^content-security-policy:"'
verif "Permissions-Policy présent"                           'entetes $SITE/login | grep -qi "^permissions-policy:"'
verif "version de PHP non annoncée (pas de X-Powered-By)"    '! entetes $SITE/login | grep -qi "^x-powered-by:"'
verif "version de nginx non annoncée"                        '! entetes $SITE/login | grep -qi "^server: *nginx/"'
echo "== Cookies"
verif "cookie de session : Secure"                           'entetes $SITE/login | grep -i "^set-cookie: *[^=]*session=" | grep -qi "secure"'
verif "cookie de session : HttpOnly"                         'entetes $SITE/login | grep -i "^set-cookie: *[^=]*session=" | grep -qi "httponly"'
verif "cookie de session : SameSite"                         'entetes $SITE/login | grep -i "^set-cookie: *[^=]*session=" | grep -qi "samesite"'
echo "== Fichiers qui ne doivent jamais être servis"
for f in .env .env.production storage/logs/laravel.log composer.json composer.lock artisan vendor/autoload.php .git/config database/seeders/DatabaseSeeder.php docker-compose.prod.yml deploy.sh; do
    verif "/$f non servi (403 ou 404)"                       'c=$(code $SITE/'"$f"'); [ "$c" = "403" ] || [ "$c" = "404" ]'
done
echo "== Protections de l'application"
# Refusé = jamais servi : le routeur Traefik répond 404 avant même d'atteindre l'application (ou l'application répond 400 / 403).
verif "un faux nom d'hôte n'est pas servi (400, 403 ou 404)"  'c=$(code -H "Host: evil.example.com" $SITE/login); [ "$c" = "400" ] || [ "$c" = "403" ] || [ "$c" = "404" ]'
verif "un faux X-Forwarded-Host n'est pas pris en compte"   'c=$(code -H "X-Forwarded-Host: evil.example.com" $SITE/login); [ "$c" = "200" ] || [ "$c" = "400" ] || [ "$c" = "403" ]'
verif "une écriture sans jeton CSRF est refusée (419)"       '[ "$(code -X POST -d login=x\&password=x $SITE/login)" = "419" ]'
verif "une adresse démesurée est refusée (414)"              '[ "$(code "$SITE/login?q=$(head -c 9000 /dev/zero | tr "\0" a)")" = "414" ]'

echo
echo "$OK vérification(s) réussie(s), $KO échec(s)"
[ "$KO" = "0" ] && echo "Le site est correctement configuré." || { echo "À corriger avant d'ouvrir le site aux utilisateurs (voir deploy/MISE_A_JOUR.md)."; exit 1; }
