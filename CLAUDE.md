# Gestion Boulangerie - Documentation Projet

## Objectif

Application Laravel de gestion de boulangerie destinée aux petites et moyennes boulangeries sénégalaises.

L'application possède deux rôles :

### Gérant

Peut gérer :

- Stock
- Production
- Livreurs
- Abonnés
- Dépenses

### Propriétaire

Possède toutes les permissions du gérant plus :

- Gestion des gérants
- Paramètres de la boulangerie
- Bilan financier global

---

# Règles métier

## Stock

Version V1 :

Uniquement :

- Farine
- Levure

Les achats de stock :

- augmentent automatiquement le stock
- créent automatiquement une dépense

---

## Production

La production consomme :

- Farine
- Levure

Champs :

- Date
- Nombre de sacs de farine
- Nombre de paquets de levure
- Nombre de pains produits

Après validation :

- diminution automatique du stock
- mise à jour immédiate de l'historique

---

## Livreurs

IMPORTANT :

Le versement du jour concerne toujours la distribution de la veille.

Exemple :

09 juin :

Moussa prend 100 pains.

10 juin :

Moussa prend 100 pains.

Il verse les ventes du 09 juin.

11 juin :

Moussa prend 100 pains.

Il verse les ventes du 10 juin.

Cette règle est obligatoire dans toute l'application.

---

## Distribution

Le module Distribution est supprimé.

Toute la gestion passe par le module Livreurs.

Depuis un livreur :

- attribuer des pains
- enregistrer un versement
- consulter l'historique

---

## Détail livreur

Afficher :

- Pains attribués
- Pains vendus
- Pains invendus
- Montant attendu
- Montant versé
- Reliquat du jour
- Reliquat total

Historique par date :

- Pains attribués
- Pains vendus
- Pains invendus
- Montant attendu
- Montant versé
- Reliquat

---

## Abonnés

Un abonné possède :

- Nom
- Téléphone
- Adresse

Les consommations enregistrent :

- Date
- Nombre de pains

La facture est générée automatiquement à partir des consommations mensuelles.

Le module Factures séparé ne doit plus exister.

---

## Dépenses

Types :

- Achat farine
- Achat levure
- Salaire
- Divers

Les achats de stock créent automatiquement une dépense.

Toutes les dépenses peuvent être :

- créées
- modifiées
- supprimées

par le gérant ou le propriétaire.

---

## Bilan financier

Réservé au propriétaire.

Calcul :

Recettes =
Versements livreurs +
Paiements abonnés

Dépenses =
Achats stock +
Salaires +
Dépenses diverses

Bénéfice =
Recettes - Dépenses

Afficher :

- Recettes totales
- Dépenses totales
- Pains produits
- Pains vendus
- Pains invendus
- Reliquats totaux
- Bénéfice

---

# Interface utilisateur

La maquette validée est la référence principale.

Respecter :

- Structure
- Navigation
- Organisation des écrans
- Logique métier

---

## Connexion

Afficher :

- Icône pain
- Nom de la boulangerie
- Formulaire de connexion

Le logo personnalisé n'est pas utilisé en V1.

Le nom affiché est celui défini dans les paramètres de la boulangerie.

Si aucune boulangerie n'existe :

"Ma Boulangerie"

---

## Navigation

Menu inférieur type mobile :

- Accueil
- Stock
- Production
- Livreurs
- Plus

Le bouton Plus affiche :

- Abonnés
- Dépenses

Pour le propriétaire uniquement :

- Gestion des gérants
- Paramètres
- Bilan financier

---

## Dashboard

Ne jamais afficher :

- Ventes du jour
- Invendus du jour
- Reliquats du jour

car ces données ne sont pas connues immédiatement.

Afficher :

- Production du jour
- Dernière activité
- Alertes stock faible
- Nombre de livreurs actifs
- Nombre d'abonnés actifs

---

## Stock

Écran principal :

- Farine
- Levure

Afficher :

- Stock disponible
- Bouton Ajouter achat
- Historique des achats

---

## Achat stock

Champs :

- Produit
- Quantité
- Prix total
- Date achat

Ne pas demander :

- Fournisseur
- Prix unitaire
- Informations inutiles

---

## Production

Écran principal :

Afficher :

- Dernière production
- Historique production

Bouton :

Ajouter production

Formulaire :

- Date
- Sacs farine utilisés
- Paquets levure utilisés
- Nombre pains produits

---

## Livreurs

Liste :

- Nom
- Téléphone
- Statut

Depuis le détail livreur :

- Attribuer des pains
- Voir historique
- Enregistrer versement

---

## Abonnés

Liste :

- Nom
- Téléphone

Détail :

- Nom
- Téléphone
- Adresse
- Historique consommations

Actions :

- Ajouter consommation
- Générer facture

---

# Architecture

Avant toute modification :

1. Vérifier les migrations.
2. Vérifier les modèles.
3. Vérifier les relations Eloquent.
4. Vérifier les contrôleurs.
5. Vérifier les vues Blade.
6. Vérifier les routes.

Ne jamais casser une fonctionnalité existante.

---

# Consignes pour Claude Code

Avant de modifier du code :

- analyser les impacts
- identifier les relations concernées
- proposer les migrations nécessaires
- éviter les régressions

Après chaque tâche :

fournir :

- fichiers modifiés
- fichiers créés
- migrations créées
- résumé des changements
- plan de test