# EDF Tempo pour Jeedom

Ce plugin Jeedom, va récupérer les informations sur les couleurs  d'aujourd'hui et de demain aurprès du site internet d'**EDF**, il récupère également le nombre de jours restant pour chacunes des trois couleurs.
Aucune configuration particulière ne vous est demandés.

Compatible pour **Jeedom** 4.2

# Installation du plugin

Utilisez le market de **Jeedom** pour télécharger et installer le plugins.

# Activation du plugin

* **Activer** le plugin, cela devrais pré définir certaines variable, tels que les urls de récupérations des données, et le prix pour chaque période. Les prix par défaut sont ceux du 1er août 2023. Vous avez la possibilité de les ajuster manuellement.

# Création d'un équipement

* Allez dans les plugins et cherchez **EDF Tempo**
* Cliquez ensuite sur **Ajouter**
* Nommez l'équipement, par exeple **EDF Tempo** et faite **ok**

L'équipement est créé et actif, il vous reste plus qu'à le positionner dans votre environnement, par défaut il est dans la rubrique **aucun**
Le site d'EDF est mis à jours tous les jours vers 11h. Il y a donc une tâche planifié qui vérifie tous les jours à 11h06 le status du jour.
Si vous voulez planifiez des scénarios en fonction de la couleur du jour (par exemple une alerte mail) faite le après 11h06 de manière à avoir la dernière information.

# changelog
* Mise à jour le 3 août 2023.
