<?php
/* This file is part of Jeedom.
*
* Jeedom is free software: you can redistribute it and/or modify
* it under the terms of the GNU General Public License as published by
* the Free Software Foundation, either version 3 of the License, or
* (at your option) any later version.
*
* Jeedom is distributed in the hope that it will be useful,
* but WITHOUT ANY WARRANTY; without even the implied warranty of
* MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
* GNU General Public License for more details.
*
* You should have received a copy of the GNU General Public License
* along with Jeedom. If not, see <http://www.gnu.org/licenses/>.
*/

/* * ***************************Includes********************************* */
require_once __DIR__  . '/../../../../core/php/core.inc.php';

class edf_tempo extends eqLogic {
  /*     * *************************Attributs****************************** */

  /*
  * Permet de définir les possibilités de personnalisation du widget (en cas d'utilisation de la fonction 'toHtml' par exemple)
  * Tableau multidimensionnel - exemple: array('custom' => true, 'custom::layout' => false)
  public static $_widgetPossibility = array();
  */

  /*
  * Permet de crypter/décrypter automatiquement des champs de configuration du plugin
  * Exemple : "param1" & "param2" seront cryptés mais pas "param3"
  public static $_encryptConfigKey = array('param1', 'param2');
  */

  /*     * ***********************Methode static*************************** */
 public static function cron() {
    $eqLogics = self::byType('edf_tempo', true);
    $heure   = (int) date('G');
    $minutes = (int) date('i');

    // Bloc minuit : rotation des couleurs sans appel API
    if ($heure === 0 && $minutes === 0) {
      foreach ($eqLogics as $edf_tempo) {
        try {
          $cmdTomorrow = $edf_tempo->getCmd(null, 'edf_tomorrow');
          $couleurDemain = is_object($cmdTomorrow) ? $cmdTomorrow->execCmd() : 'NA';

          if ($couleurDemain && $couleurDemain !== 'NA' && $couleurDemain !== 'NON_DEFINI') {
            $edf_tempo->checkAndUpdateCmd('edf_today', $couleurDemain);
            log::add('edf_tempo', 'info', "Minuit : rotation couleur demain ($couleurDemain) → aujourd'hui");
          } else {
            log::add('edf_tempo', 'warning', "Minuit : couleur demain indisponible ($couleurDemain), aujourd'hui non modifié");
          }

          $edf_tempo->checkAndUpdateCmd('edf_tomorrow', 'NA');
          $edf_tempo->checkAndUpdateCmd('edf_status', 'NOK');
        } catch (Exception $e) {
          log::add('edf_tempo', 'error', 'Erreur rotation minuit : ' . $e->getMessage());
        }
      }
      return;
    }

    // Bloc 11h-12h05 : appels API pour récupérer les couleurs
    // Retry chaque minute ; erreur si toujours NOK après 30 min
    if ($heure >= 11 && ($heure < 12 || ($heure === 12 && $minutes <= 5))) {
      foreach ($eqLogics as $edf_tempo) {
        try {
          $cmd = $edf_tempo->getCmd(null, 'edf_status');
          if (is_object($cmd) && $cmd->execCmd() !== 'OK') {
            self::updateEDFTempoInfos($edf_tempo);
            // Après 30 min de retries infructueux, loguer en erreur
            if ($heure === 11 && $minutes === 30) {
              $cmdCheck = $edf_tempo->getCmd(null, 'edf_status');
              if (is_object($cmdCheck) && $cmdCheck->execCmd() !== 'OK') {
                log::add('edf_tempo', 'error', "Couleur de demain toujours indisponible après 30 min de retries.");
              }
            }
          }
        } catch (Exception $e) {
          log::add('edf_tempo', 'error', $e->getMessage());
        }
      }
    }
  }




  /*
  * Fonction exécutée automatiquement toutes les minutes par Jeedom
  public static function cron() {}
  */

  /*
  * Fonction exécutée automatiquement toutes les 5 minutes par Jeedom
  public static function cron5() {}
  */

  /*
  * Fonction exécutée automatiquement toutes les 10 minutes par Jeedom
  public static function cron10() {}
  */

  /*
  * Fonction exécutée automatiquement toutes les 15 minutes par Jeedom
  public static function cron15() {}
  */

  /*
  * Fonction exécutée automatiquement toutes les 30 minutes par Jeedom
  public static function cron30() {}
  */

  /*
  * Fonction exécutée automatiquement toutes les heures par Jeedom
  public static function cronHourly() {}
  */

  /*
  * Fonction exécutée automatiquement tous les jours par Jeedom
  */
  public static function cronDaily() {
    // Force la MAJ du nombre de jours bleu le 1er septembre
    if (date('m-d') == '09-01') {
      self::updateMaxJrBleu();
    }
  }

  /*     * *********************Méthodes d'instance************************* */

  // Fonction exécutée automatiquement avant la création de l'équipement
  public function preInsert() {
  }

  // Fonction exécutée automatiquement après la création de l'équipement
  public function postInsert() {
    log::add('edf_tempo', 'info', "Mise à jour de l'autorefresh de l'équipement.");
    // $this->setConfiguration('autorefresh', '6 11 * * *');
    $this->setIsEnable(1);
    $this->setIsVisible(1);
    $this->save();
  }

  // Fonction exécutée automatiquement avant la mise à jour de l'équipement
  public function preUpdate() {
  }

  // Fonction exécutée automatiquement après la mise à jour de l'équipement
  public function postUpdate() {

    log::add('edf_tempo', 'debug', 'postUpdate a été déclenché.');
    $this->updateMaxJrBleu();

  }

  // Fonction exécutée automatiquement avant la sauvegarde (création ou mise à jour) de l'équipement
  public function preSave() {
  }

  // Fonction exécutée automatiquement après la sauvegarde (création ou mise à jour) de l'équipement
  public function postSave() {

    $info = $this->getCmd(null, 'edf_today');
    if (!is_object($info)) {
      $info = new edf_tempoCmd();
      $info->setName(__("Aujourd'hui", __FILE__));
    }
    $info->setLogicalId('edf_today');
    $info->setEqLogic_id($this->getId());
    $info->setType('info');
    $info->setSubType('string');
    $info->setIsVisible(0);
    $info->setOrder(3);
    $info->save();

    $info = $this->getCmd(null, 'edf_tomorrow');
    if (!is_object($info)) {
      $info = new edf_tempoCmd();
      $info->setName(__("Demain", __FILE__));
    }
    $info->setLogicalId('edf_tomorrow');
    $info->setEqLogic_id($this->getId());
    $info->setType('info');
    $info->setSubType('string');
    $info->setIsVisible(0);
    $info->setOrder(4);
    $info->save();

    $info = $this->getCmd(null, 'edf_nb_bleu');
    if (!is_object($info)) {
      $info = new edf_tempoCmd();
      $info->setName(__("Bleu", __FILE__));
    }
    $info->setLogicalId('edf_nb_bleu');
    $info->setEqLogic_id($this->getId());
    $info->setType('info');
    $info->setSubType('string');
    $info->setIsVisible(0);
    $info->setOrder(5);
    $info->save();


    $info = $this->getCmd(null, 'edf_nb_blanc');
    if (!is_object($info)) {
      $info = new edf_tempoCmd();
      $info->setName(__("Blanc", __FILE__));
    }
    $info->setLogicalId('edf_nb_blanc');
    $info->setEqLogic_id($this->getId());
    $info->setType('info');
    $info->setSubType('string');
    $info->setIsVisible(0);
    $info->setOrder(6);
    $info->save();


    $info = $this->getCmd(null, 'edf_nb_rouge');
    if (!is_object($info)) {
      $info = new edf_tempoCmd();
      $info->setName(__("Rouge", __FILE__));
    }
    $info->setLogicalId('edf_nb_rouge');
    $info->setEqLogic_id($this->getId());
    $info->setType('info');
    $info->setSubType('string');
    $info->setIsVisible(0);
    $info->setOrder(7);
    $info->save();

    $info = $this->getCmd(null, 'edf_lastupdate');
    if (!is_object($info)) {
      $info = new edf_tempoCmd();
      $info->setName(__("Mis à jour", __FILE__));
    }
    $info->setLogicalId('edf_lastupdate');
    $info->setEqLogic_id($this->getId());
    $info->setType('info');
    $info->setSubType('string');
    $info->setIsVisible(0);
    $info->setOrder(2);
    $info->save();

    $refresh = $this->getCmd(null, 'refresh');
    if (!is_object($refresh)) {
      $refresh = new edf_tempoCmd();
      $refresh->setName(__('Rafraichir', __FILE__));
    }
    $refresh->setEqLogic_id($this->getId());
    $refresh->setLogicalId('refresh');
    $refresh->setType('action');
    $refresh->setSubType('other');
    $refresh->setOrder(1);
    $refresh->save();


    $info = $this->getCmd(null, 'edf_status');
    if (!is_object($info)) {
      $info = new edf_tempoCmd();
      $info->setName(__("Etat de la synchronisation", __FILE__));
    }
    $info->setLogicalId('edf_status');
    $info->setEqLogic_id($this->getId());
    $info->setType('info');
    $info->setSubType('string');
    $info->setIsVisible(0);
    $info->setOrder(8);
    $info->save();

    $this->updateEDFTempoInfos($this); // mets à jour la tuile
  }

  // Fonction exécutée automatiquement avant la suppression de l'équipement
  public function preRemove() {
  }

  // Fonction exécutée automatiquement après la suppression de l'équipement
  public function postRemove() {
  }

  /*
  * Permet de crypter/décrypter automatiquement des champs de configuration des équipements
  * Exemple avec le champ "Mot de passe" (password)
  public function decrypt() {
    $this->setConfiguration('password', utils::decrypt($this->getConfiguration('password')));
  }
  public function encrypt() {
    $this->setConfiguration('password', utils::encrypt($this->getConfiguration('password')));
  }
  */

  /*
  * Permet de modifier l'affichage du widget (également utilisable par les commandes)
  public function toHtml($_version = 'dashboard') {}
  */

  /*
  * Permet de déclencher une action avant modification d'une variable de configuration du plugin
  * Exemple avec la variable "param3"
  public static function preConfig_param3( $value ) {
    // do some checks or modify on $value
    return $value;
  }
  */

  /*
  * Permet de déclencher une action après modification d'une variable de configuration du plugin
  * Exemple avec la variable "param3"
  public static function postConfig_param3($value) {
    // no return value
  }
  */

  /*     * **********************Getteur Setteur*************************** */

  /**
   * Appel authentifié vers l'API proxy (ai-beauvaisis.fr)
   * @param string $endpoint  Ex: 'colors', 'remaining', 'tarifs'
   * @param array  $params    Paramètres GET supplémentaires
   * @return array|false      Réponse JSON décodée ou false en cas d'erreur
   */
  public static function callApi(string $endpoint, array $params = []) {
    $baseUrl   = config::byKey('global_api_url', 'edf_tempo');
    $hmacSecret = config::byKey('global_api_hmac_secret', 'edf_tempo');
    $pluginId  = 'edf_tempo';
    $timestamp = (string)time();
    $installId = config::byKey('global_install_id', 'edf_tempo');

    $signature = hash_hmac('sha256', $pluginId . $timestamp . $installId, $hmacSecret);

    $url = rtrim($baseUrl, '/') . '/' . $endpoint;
    if (!empty($params)) {
      $url .= '?' . http_build_query($params);
    }

    $ch = curl_init($url);
    curl_setopt_array($ch, [
      CURLOPT_RETURNTRANSFER => true,
      CURLOPT_TIMEOUT => 15,
      CURLOPT_CONNECTTIMEOUT => 10,
      CURLOPT_FOLLOWLOCATION => true,
      CURLOPT_ENCODING => '',
      CURLOPT_HTTPHEADER => [
        'Accept: application/json',
        'X-Plugin-Id: ' . $pluginId,
        'X-Timestamp: ' . $timestamp,
        'X-Install-Id: ' . $installId,
        'X-Signature: ' . $signature,
      ],
    ]);

    $response = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    $error = curl_error($ch);
    curl_close($ch);

    if ($response === false || $httpCode !== 200) {
      if ($httpCode === 404 && $endpoint === 'colors') {
        log::add('edf_tempo', 'debug', "API colors : donnée non disponible (HTTP 404)");
      } else {
        log::add('edf_tempo', 'error', "API callApi($endpoint) erreur HTTP $httpCode : " . ($error ?: substr($response, 0, 200)));
      }
      return false;
    }

    $data = json_decode($response, true);
    if ($data === null) {
      log::add('edf_tempo', 'error', "API callApi($endpoint) réponse JSON invalide");
      return false;
    }

    if (isset($data['error'])) {
      log::add('edf_tempo', 'debug', "API callApi($endpoint) : " . $data['error']);
      return false;
    }

    return $data;
  }

  public static function updateEDFTempoInfos($eqlogic, $force = false) {
    $d = new DateTime();
    $today    = $d->format('Y-m-d');
    $tomorrow = $d->modify('+1 day')->format('Y-m-d');

    // Lire les valeurs actuelles
    $cmdToday = $eqlogic->getCmd(null, 'edf_today');
    $currentToday = is_object($cmdToday) ? $cmdToday->execCmd() : 'NA';
    $cmdTomorrow = $eqlogic->getCmd(null, 'edf_tomorrow');
    $currentTomorrow = is_object($cmdTomorrow) ? $cmdTomorrow->execCmd() : 'NA';

    // Couleur aujourd'hui : ne requêter que si inconnue ou forcé
    $couleurJ = $currentToday;
    if ($force || !$currentToday || $currentToday === 'NA' || $currentToday === 'NON_DEFINI') {
      $colorToday = self::callApi('colors', ['date' => $today]);
      $couleurJ = ($colorToday && isset($colorToday['color'])) ? $colorToday['color'] : 'NA';
      $eqlogic->checkAndUpdateCmd('edf_today', $couleurJ);
    }

    // Couleur demain : ne requêter que si inconnue ou forcé
    $couleurJ1 = $currentTomorrow;
    if ($force || !$currentTomorrow || $currentTomorrow === 'NA' || $currentTomorrow === 'NON_DEFINI') {
      $colorTomorrow = self::callApi('colors', ['date' => $tomorrow]);
      $couleurJ1 = ($colorTomorrow && isset($colorTomorrow['color'])) ? $colorTomorrow['color'] : 'NA';
      $eqlogic->checkAndUpdateCmd('edf_tomorrow', $couleurJ1);
    }

    // Jours restants + tarifs : uniquement quand demain est connu ou en mode forcé
    if ($force || ($couleurJ1 !== 'NA' && $couleurJ1 !== 'NON_DEFINI')) {
      $remaining = self::callApi('remaining');
      if ($remaining && isset($remaining['remaining'])) {
        $eqlogic->checkAndUpdateCmd('edf_nb_bleu', $remaining['remaining']['TEMPO_BLEU'] ?? 'NA');
        $eqlogic->checkAndUpdateCmd('edf_nb_blanc', $remaining['remaining']['TEMPO_BLANC'] ?? 'NA');
        $eqlogic->checkAndUpdateCmd('edf_nb_rouge', $remaining['remaining']['TEMPO_ROUGE'] ?? 'NA');
      }

      $tarifs = self::callApi('tarifs');
      if ($tarifs && isset($tarifs['tarifs'])) {
        config::save('global_tempo_bleu_hc', $tarifs['tarifs']['bleu_hc'], 'edf_tempo');
        config::save('global_tempo_bleu_hp', $tarifs['tarifs']['bleu_hp'], 'edf_tempo');
        config::save('global_tempo_blanc_hc', $tarifs['tarifs']['blanc_hc'], 'edf_tempo');
        config::save('global_tempo_blanc_hp', $tarifs['tarifs']['blanc_hp'], 'edf_tempo');
        config::save('global_tempo_rouge_hc', $tarifs['tarifs']['rouge_hc'], 'edf_tempo');
        config::save('global_tempo_rouge_hp', $tarifs['tarifs']['rouge_hp'], 'edf_tempo');
        config::save('global_tarifs_version', $tarifs['version'] ?? '', 'edf_tempo');
        log::add('edf_tempo', 'info', "Tarifs synchronisés : " . ($tarifs['label'] ?? $tarifs['version'] ?? ''));
      }

      $eqlogic->checkAndUpdateCmd('edf_lastupdate', date("d-m-Y à H:i"));
      $eqlogic->checkAndUpdateCmd('edf_status', 'OK');
      log::add('edf_tempo', 'info', "Mise à jour EDF Tempo : today=$couleurJ, demain=$couleurJ1");
    } else {
      $eqlogic->checkAndUpdateCmd('edf_status', 'NOK');
      log::add('edf_tempo', 'debug', "Couleur demain pas encore disponible (today=$couleurJ)");
    }
  }

  public function updateMaxJrBleu(){

    $month  = date('n');
    $year   = date('Y');
    if ($month < 9) {
        $date1 = new DateTime(($year - 1) . '-09-01');
        $date2 = new DateTime($year . '-08-31');
    } else {
        $date1 = new DateTime($year . '-09-01');
        $date2 = new DateTime(($year + 1) . '-08-31');
    }

    $interval = $date1->diff($date2);
    $nbJr= $interval->days + 1;
    $nbBleu = 300;
    if ($nbJr > 365){
        $nbBleu = 301;
    }

    config::save('global_max_tempo_bleu', $nbBleu, 'edf_tempo');
    log::add('edf_tempo', 'debug', 'global_max_tempo_bleu a été enregistré avec la valeur ' . config::byKey('global_max_tempo_bleu', 'edf_tempo') . ' jours.');
  }

  public function toHtml($_version = 'dashboard') {
    $texte="";
    $replace = $this->preToHtml($_version);
    if (!is_array($replace)) {
      return $replace;
    }
    $version = jeedom::versionAlias($_version);

    // Liste des commandes à récupérer et remplacer
    $commandsToReplace = array(
      'edf_today',
      'edf_tomorrow',
      'edf_nb_bleu',
      'edf_nb_blanc',
      'edf_nb_rouge',
    );

    // Parcourir les commandes à remplacer
    foreach ($commandsToReplace as $commandName) {
      $cmd = $this->getCmd(null, $commandName);
      if (is_object($cmd) && $cmd->getType() == 'info') {
        $commandValue = $cmd->execCmd();
        $replace['#' . $commandName . '#'] = $commandValue;
      } else {
        $replace['#' . $commandName . '#'] = 'Valeur indisponible';
      }
    }

    $replace['#global_tempo_bleu_hc#']    = config::byKey('global_tempo_bleu_hc', 'edf_tempo');
    $replace['#global_tempo_bleu_hp#']    = config::byKey('global_tempo_bleu_hp', 'edf_tempo');
    $replace['#global_tempo_blanc_hc#']   = config::byKey('global_tempo_blanc_hc', 'edf_tempo');
    $replace['#global_tempo_blanc_hp#']   = config::byKey('global_tempo_blanc_hp', 'edf_tempo');
    $replace['#global_tempo_rouge_hc#']   = config::byKey('global_tempo_rouge_hc', 'edf_tempo');
    $replace['#global_tempo_rouge_hp#']   = config::byKey('global_tempo_rouge_hp', 'edf_tempo');
    $replace['#global_max_tempo_bleu#']   = config::byKey('global_max_tempo_bleu', 'edf_tempo');
    $replace['#global_max_tempo_blanc#']  = config::byKey('global_max_tempo_blanc', 'edf_tempo');
    $replace['#global_max_tempo_rouge#']  = config::byKey('global_max_tempo_rouge', 'edf_tempo');

    return $this->postToHtml($_version, template_replace($replace, getTemplate('core', $version, 'tile_edf_tempo', 'edf_tempo')));
  }

}

class edf_tempoCmd extends cmd {
  /*     * *************************Attributs****************************** */

  /*
  public static $_widgetPossibility = array();
  */

  /*     * ***********************Methode static*************************** */


  /*     * *********************Methode d'instance************************* */

  /*
  * Permet d'empêcher la suppression des commandes même si elles ne sont pas dans la nouvelle configuration de l'équipement envoyé en JS
  public function dontRemoveCmd() {
    return true;
  }
  */

  // Exécution d'une commande
  public function execute($_options = array()) {
      $eqlogic = $this->getEqLogic();
      switch ($this->getLogicalId()) {
        case 'refresh':
          log::add('edf_tempo', 'info', "Mise à jour forcée le " . date("d-m-Y à H:i"));
          $eqlogic->updateEDFTempoInfos($eqlogic, true);
          // $eqlogic->checkAndUpdateCmd('edf_lastupdate', "Forcée le ".date("m-d-Y à H:i"));
          $eqlogic->refreshWidget();
        break;
      }
  }


  /*     * **********************Getteur Setteur*************************** */

}
