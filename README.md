# EDF Tempo pour Jeedom

Ce plugin Jeedom, va récupérer les informations sur les couleurs  d'aujourd'hui et de demain aurprès du site internet d'**EDF**, il récupère également le nombre de jours restant pour chacunes des trois couleurs.
Aucune configuration particulière ne vous est demandés.

Compatible pour **Jeedom** 4.2

# Installation du plugin

Utilisez le market de **Jeedom** pour télécharger et installer le plugins.
Ou téléchargez le zip, et copier son contenue dans le dossier **/var/www/html/plugins/edf_tempo** de votre machine (sous linux)

# Activation du plugin

* **Activer** le plugin, cela devrais pré définir certaines variable, tels que les urls de récupérations des données, et le prix pour chaque période. Les prix par défaut sont ceux du 1er août 2023. Vous avez la possibilité de les ajuster manuellement.

# Création d'un équipement

* Allez dans les plugins et cherchez **EDF Tempo**
* Cliquez ensuite sur **Ajouter**
* Nommez l'équipement, par exeple **EDF Tempo** et faite **ok**

L'équipement est créé et actif, il vous reste plus qu'à le positionner dans votre environnement, par défaut il est dans la rubrique **aucun**
Le site d'EDF est mis à jours tous les jours vers 11h. Il y a donc une tâche planifié qui vérifie tous les jours entre 11h et 12h05 le status du jour, une erreur est généré si à 12h05 il n'y a toujours aucune donnée.
Si vous voulez planifiez des scénarios en fonction de la couleur du jour (par exemple une alerte mail) faite le après 11h06 de manière à avoir la dernière information.

# changelog

* Mise à jour du 3 Février 2025.
  - Mise à jour des tarifs TEMPO pour l'année 2025
  - 
* Mise à jour du 1 Septembre 2024.
  - Mise à jour du système de récupérations des données d'EDF
  
* Mise à jour du 25 Juin 2024.
  - Comptage automatique du nombre de jour maxium bleu dans l'année tempo (entre le 1er septembre et le 31 août de l'année suivante)
  
* Mise à jour du 28 Avril 2024.
  - Modification de la méthode de récupération des données JSON
    
* 
* Mise à jour du 12 août 2023.
  - Modification du CSS et ses alignements
  - Modification du type de requête php pour récupérer les données via cUrl ou file_get_contents pour améliorer la compatibilités des plateformes
