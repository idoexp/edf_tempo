# EDF Tempo pour Jeedom

Ce plugin Jeedom récupère les informations sur les couleurs d'aujourd'hui et de demain auprès de l'API **EDF**, ainsi que le nombre de jours restants pour chacune des trois couleurs.

Compatible **Jeedom** 4.2+

# Installation du plugin

Utilisez le market de **Jeedom** pour télécharger et installer le plugin.
Ou téléchargez le zip et copiez son contenu dans le dossier **/var/www/html/plugins/edf_tempo** de votre machine (sous Linux).

# Activation du plugin

* **Activez** le plugin, cela prédéfinit certaines variables telles que les URLs de récupération des données et les tarifs pour chaque période.
* Les tarifs peuvent être mis à jour automatiquement via un fichier JSON distant hébergé sur GitHub, ou ajustés manuellement dans la configuration du plugin.

# Création d'un équipement

* Allez dans les plugins et cherchez **EDF Tempo**
* Cliquez ensuite sur **Ajouter**
* Nommez l'équipement, par exemple **EDF Tempo** et faites **OK**

L'équipement est créé et actif, il vous reste à le positionner dans votre environnement (par défaut dans la rubrique **aucun**).

Le plugin récupère automatiquement les couleurs du jour et du lendemain entre **11h00 et 11h30**. EDF publie généralement la couleur du lendemain vers 11h05-11h06. Le plugin retente chaque minute jusqu'à obtenir la donnée. Si après 30 minutes la couleur est toujours indisponible, une erreur est logguée.

> **Important** : si vous planifiez des scénarios en fonction de la couleur du jour (alerte mail, pilotage chauffage, etc.), programmez-les **à partir de 11h10 minimum** pour être sûr que les données sont disponibles.

# Gestion des tarifs

Le plugin propose deux modes de gestion des tarifs :

* **Synchronisation automatique** : dans la configuration du plugin, cliquez sur **Vérifier les tarifs** pour comparer vos tarifs actuels avec ceux du fichier distant. Un tableau comparatif s'affiche avec les différences. Cliquez sur **Appliquer ces tarifs** pour les mettre à jour.
* **Modification manuelle** : modifiez directement les valeurs dans la configuration et sauvegardez. Le plugin indique alors la date et la source de la dernière mise à jour (synchronisé ou manuel par l'utilisateur).

Le plugin vérifie également chaque jour si de nouveaux tarifs sont disponibles et vous notifie via un log de niveau warning. Vous pouvez ignorer une version de tarifs via le bouton **Ignorer cette version**.

# Changelog

* Mise à jour du 6 Avril 2026.
  - **Sécurité** : suppression de l'utilisation de la clé matérielle Jeedom (`hardwareKey`) pour l'authentification API. Je vous prie de m'excuser d'avoir utilisé cette donnée privée dans les versions précédentes sans votre consentement. La base de données côté serveur a été purgée de toutes les clés matérielles collectées.
  - Un identifiant d'installation unique est désormais généré localement lors de l'installation ou de la mise à jour du plugin, et utilisé pour authentifier les requêtes auprès de l'API. Cela garantit que seules les installations du plugin peuvent interroger le endpoint, sans exposer de donnée sensible.

* Mise à jour du 15 Mars 2026.
  - Optimisation des appels API : ne requête que les données manquantes lors des retries
  - Les erreurs HTTP 404 attendues (couleur pas encore publiée par EDF) ne polluent plus les logs
  - Système de retry automatique chaque minute entre 11h00 et 11h30
  - Correction bug ordre des commandes dans postSave ($info/$refresh)
  - Correction format de date dans le log du refresh forcé
  - Documentation : planifier les scénarios à partir de 11h10 minimum

* Mise à jour du 8 Mars 2026.
  - Système de mise à jour des tarifs via fichier JSON distant (GitHub)
  - Tableau comparatif avant/après dans la configuration du plugin
  - Notification automatique en cas de nouveaux tarifs disponibles
  - Suivi de la source de mise à jour des tarifs (synchronisé / manuel par l'utilisateur)
  - Bouton Logs dans la page de gestion du plugin
  - Correction du template mobile (jours max blanc/rouge dynamiques)
  - Amélioration des logs (niveaux, messages, format de date)
  - Factorisation du code cURL pour les appels distants

* Mise à jour du 3 Février 2025.
  - Mise à jour des tarifs TEMPO pour l'année 2025

* Mise à jour du 1 Septembre 2024.
  - Mise à jour du système de récupération des données d'EDF

* Mise à jour du 25 Juin 2024.
  - Comptage automatique du nombre de jours maximum bleus dans l'année tempo (entre le 1er septembre et le 31 août de l'année suivante)

* Mise à jour du 28 Avril 2024.
  - Modification de la méthode de récupération des données JSON

* Mise à jour du 12 Août 2023.
  - Modification du CSS et ses alignements
  - Modification du type de requête PHP pour récupérer les données via cURL ou file_get_contents pour améliorer la compatibilité des plateformes
