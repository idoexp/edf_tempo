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

Le site d'EDF est mis à jour tous les jours vers 11h. Une tâche planifiée vérifie quotidiennement à 11h le statut du jour. En cas d'échec, le plugin effectue automatiquement jusqu'à 3 tentatives supplémentaires (à +3min, +6min et +9min). Si toutes les tentatives échouent, une erreur est logguée.
Si vous voulez planifier des scénarios en fonction de la couleur du jour (par exemple une alerte mail), faites-le après 11h15 de manière à avoir la dernière information.

# Gestion des tarifs

Le plugin propose deux modes de gestion des tarifs :

* **Synchronisation automatique** : dans la configuration du plugin, cliquez sur **Vérifier les tarifs** pour comparer vos tarifs actuels avec ceux du fichier distant. Un tableau comparatif s'affiche avec les différences. Cliquez sur **Appliquer ces tarifs** pour les mettre à jour.
* **Modification manuelle** : modifiez directement les valeurs dans la configuration et sauvegardez. Le plugin indique alors la date et la source de la dernière mise à jour (synchronisé ou manuel par l'utilisateur).

Le plugin vérifie également chaque jour si de nouveaux tarifs sont disponibles et vous notifie via un log de niveau warning. Vous pouvez ignorer une version de tarifs via le bouton **Ignorer cette version**.

# Changelog

* Mise à jour du 8 Mars 2026.
  - Système de mise à jour des tarifs via fichier JSON distant (GitHub)
  - Tableau comparatif avant/après dans la configuration du plugin
  - Notification automatique en cas de nouveaux tarifs disponibles
  - Suivi de la source de mise à jour des tarifs (synchronisé / manuel par l'utilisateur)
  - Bouton Logs dans la page de gestion du plugin
  - Correction du template mobile (jours max blanc/rouge dynamiques)
  - Amélioration des logs (niveaux, messages, format de date)
  - Factorisation du code cURL pour les appels distants
  - Système de retry automatique (3 tentatives à +3min, +6min, +9min) en cas d'échec de synchronisation

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
