# Mettre à jour le site en production (VPS Hetzner)

Ce guide sert à passer d'une ancienne version à la version actuelle **sans perdre de données** et en
sachant revenir en arrière. Compter environ 30 minutes, la première fois.

---

## A. Avant de commencer (la veille)

### A1. Savoir quelle version tourne
Sur le serveur, dans `/srv/apps/ma-boulangerie` :

```bash
git log -1 --format='%h %ad %s' --date=short
docker compose -f docker-compose.prod.yml exec -T app php artisan migrate:status | grep Pending
```

La seconde commande liste les migrations **en attente** (celles qui vont s'appliquer). Notez le résultat.

### A2. Répéter la mise à jour sur une COPIE de vos vraies données (fortement recommandé)
C'est la seule façon de voir ce que la mise à jour fera à **vos** données avant de la lancer pour de vrai.

1. Sur le serveur, faire une copie de la base :
   `docker compose -f docker-compose.prod.yml exec -T app php artisan backup:database`
   puis télécharger le fichier `storage/app/backups/backup_….sql`.
2. Sur votre machine, créer une base de test et y charger la copie :
   ```bash
   mysql -uroot -e "CREATE DATABASE copie_test CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci"
   mysql -uroot copie_test < backup_….sql
   ```
3. Répéter (la base s'appelle `copie_test` ; le script **refuse** de tourner sur la vraie base) :
   ```bash
   DB_DATABASE=copie_test php deploy/repetition-migration.php avant
   DB_DATABASE=copie_test php artisan migrate --force
   DB_DATABASE=copie_test php deploy/repetition-migration.php apres
   ```
4. Résultat attendu : toutes les lignes `OK`. Tout `KO` doit être compris **avant** de continuer.
   Un écart sur « matieres_premieres » qui augmente est normal (4 produits de stock ajoutés).

### A3. Vérifier les numéros de téléphone en double
La mise à jour range les numéros de téléphone des comptes sous une seule écriture (`77 123 45 67` devient
`771234567`). Deux comptes qui ont le même numéro écrit différemment se bloqueraient. Chercher avant :

```sql
SELECT telephone, name FROM users WHERE telephone IS NOT NULL ORDER BY REPLACE(REPLACE(REPLACE(telephone,' ',''),'-',''),'+221','');
```
Si deux lignes désignent la même personne ou le même numéro, corriger l'un des deux comptes (Mon compte).

### A4. Le fichier `.env` du serveur
Ouvrir `/srv/apps/ma-boulangerie/.env` et vérifier **chaque ligne** :

| Variable | Valeur attendue |
|---|---|
| `APP_ENV` | `production` |
| `APP_DEBUG` | `false` |
| `APP_URL` | `https://boulangerie.gallastech.com` — **exactement** l'adresse du site. Toute autre adresse reçue est refusée (erreur 400). |
| `TRUSTED_HOSTS` | vide, ou d'autres noms de domaine du site séparés par des virgules |
| `SESSION_SECURE_COOKIE` | `true` |
| `DB_USERNAME` | un utilisateur MySQL **dédié** à cette application (pas `root`) |
| `TEST_ACCOUNT_PASSWORD` | ne pas définir (inutile en production) |

> Si `APP_URL` est faux, **tout le site répond 400** après la mise à jour. C'est la première chose à vérifier si le site ne s'ouvre plus.

### A4 bis. Prévenir la boulangerie
Choisir une heure creuse (nuit, ou avant l'ouverture). Le site sera en maintenance 1 à 2 minutes.
Prévenir les gérants des changements de fonctionnement (voir section E).

---

## B. Lancer la mise à jour

```bash
cd /srv/apps/ma-boulangerie
bash deploy.sh
```

Le script fait, dans cet ordre, et **s'arrête au moindre problème** :

1. **sauvegarde de la base** (annule tout si elle est vide) et copie dans `sauvegardes-avant-maj/` ;
2. récupération du code, construction de l'image (le site répond encore) ;
3. mode maintenance, **migration des données**, remplacement du conteneur ;
4. mise en cache, retour en service, vérification que le site répond.

Puis vérifier le site depuis n'importe quelle machine :

```bash
bash deploy/verif-apres-deploiement.sh https://boulangerie.gallastech.com
```
Tout doit être `OK`.

---

## C. Contrôle avec la boulangerie (10 minutes)

Avec le propriétaire ou un gérant, sur un téléphone :

1. Se connecter (avec le numéro **tel qu'ils le tapent d'habitude**, puis avec un autre format : `+221 77…`).
2. Ouvrir **Stock** : Farine et Levure ont leur ancien stock, une **valeur** s'affiche pour le propriétaire.
   Les nouveaux produits (**Produit, Sel, Huile, Gasoil**) sont à 0 : saisir leur stock de départ dans
   **Stock → Stock initial** (quantité et valeur en FCFA, sans créer de dépense).
3. Ouvrir **Livreurs** : les anciennes distributions et leurs reliquats sont là. Vérifier un livreur connu
   (sa dette doit être la même qu'avant la mise à jour).
4. Ouvrir **Bilan financier** (propriétaire) : les bilans enregistrés avant la mise à jour sont conservés
   tels quels. Un mois **recalculé** utilise désormais la nouvelle méthode (voir E).
5. Faire un enregistrement de test réel : une production, une attribution, un versement.

---

## D. Retour en arrière (si quelque chose ne va pas)

À utiliser **seulement** si la mise à jour échoue ou si les données sont fausses. Les saisies faites
depuis la mise à jour seraient perdues : décidez vite et prévenez la boulangerie.

1. Remettre l'ancien code :
   ```bash
   cd /srv/apps/ma-boulangerie
   git log --oneline -5                    # repérer la version d'avant
   git checkout <ancien_commit>
   docker compose -f docker-compose.prod.yml build
   ```
2. Remettre la base d'avant (le fichier est dans `sauvegardes-avant-maj/`) :
   ```bash
   docker compose -f docker-compose.prod.yml up -d
   docker compose -f docker-compose.prod.yml cp sauvegardes-avant-maj/backup_….sql app:/var/www/html/storage/app/backups/
   docker compose -f docker-compose.prod.yml exec -T app php artisan backup:restore-production backup_….sql
   ```
3. Vérifier que le site répond (`/login`), puis prévenir la boulangerie.

Si la migration a échoué en cours de route, la base peut être à moitié modifiée : **ne pas relancer
`deploy.sh`**, restaurer d'abord la sauvegarde ci-dessus.

---

## E. Ce qui change pour la boulangerie

À expliquer aux gérants et au propriétaire.

- **Pas de distribution sans production du jour.** On enregistre d'abord la production ; on ne peut pas
  attribuer plus de pains que ce qui a été produit ce jour-là. Le pain d'un jour ne se distribue pas un autre jour.
- **Retour** (pain rapporté le jour même, remis au stock du jour) et **restant** (invendu rendu le lendemain
  au versement) sont deux choses différentes.
- **Boutique** : un vendeur est un type de livreur ; il note ses ventes dans sa fiche et verse le soir.
- **Pain restant** (menu) : vente de sacs de pain invendu, suivi estimé du stock.
- **Les abonnés** : leur pain est pris sur la production du jour. Un mois facturé ne peut plus être modifié.
- **Stock** : Farine, Levure, **Produit, Sel, Huile, Gasoil**. Le module **Achats** est séparé du stock.
- **Versements** : on peut les **corriger ou annuler** (menu « ··· » de l'historique d'un livreur).
- **Bénéfice** : calculé sur ce qui est **consommé** (matières valorisées au prix moyen), plus sur ce qui est
  acheté. Un gros achat de farine ne fait plus baisser le bénéfice du mois. Les bilans déjà enregistrés
  gardent leur ancienne valeur.
- **Mot de passe** : au moins 8 caractères avec une lettre et un chiffre, pas de mot de passe courant.
  Changer son mot de passe déconnecte les autres appareils.
- **8 mauvaises tentatives** de connexion sur un compte le bloquent 15 minutes.

---

## F. Après la mise en service (une fois)

- [ ] La sauvegarde automatique tourne chaque nuit : `docker compose -f docker-compose.prod.yml exec -T app php artisan schedule:list`
- [ ] Une sauvegarde peut être relue : `php artisan backup:restore-verify <fichier>`
- [ ] La copie hors serveur (Cloudflare R2) est en place : voir `deploy/backup-offsite.sh`
- [ ] Un compte super administrateur existe (créé via `SUPERADMIN_EMAIL` / `SUPERADMIN_PASSWORD` dans le `.env`, puis ces deux lignes retirées).
- [ ] `composer audit` propre avant chaque mise à jour.
