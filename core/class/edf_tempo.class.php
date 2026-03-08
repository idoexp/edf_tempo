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
  /*     * ***********************Methode static*************************** */
 public static function cron() {
    $eqLogics = self::byType('edf_tempo', true);

    $heure   = (int) date("G");
    $minutes = (int) date("i");

    // Tentative initiale entre 11h00 et 11h05
    if ($heure == 11 && $minutes <= 5) {
      foreach ($eqLogics as $edf_tempo) {
        try {
          $cmd = $edf_tempo->getCmd(null, 'edf_status');
          if (is_object($cmd) && $cmd->execCmd() != "OK") {
            log::add('edf_tempo', 'info', "Tentative de synchronisation...");
            self::updateEDFTempoInfos($edf_tempo);

            // Si toujours NOK après la tentative, initialiser le compteur de retry
            if ($cmd->execCmd() != "OK") {
              config::save('edf_retry_count', 0, 'edf_tempo');
              config::save('edf_retry_timestamp', time(), 'edf_tempo');
            }
          }
        } catch (Exception $e) {
          log::add('edf_tempo', 'warning', "Erreur cron : " . $e->getMessage());
        }
      }
      return;
    }

    // Système de retry : 3 tentatives à +3min, +6min, +9min après l'échec initial
    $retryCount = (int) config::byKey('edf_retry_count', 'edf_tempo', -1);
    $retryTimestamp = (int) config::byKey('edf_retry_timestamp', 'edf_tempo', 0);

    if ($retryCount < 0 || $retryCount >= 3 || $retryTimestamp == 0) {
      return;
    }

    $delais = array(3 * 60, 6 * 60, 9 * 60); // 3min, 6min, 9min
    $elapsed = time() - $retryTimestamp;

    if ($elapsed < $delais[$retryCount]) {
      return;
    }

    $attempt = $retryCount + 1;
    log::add('edf_tempo', 'info', "Retry " . $attempt . "/3 — nouvelle tentative de synchronisation...");

    foreach ($eqLogics as $edf_tempo) {
      try {
        $cmd = $edf_tempo->getCmd(null, 'edf_status');
        if (is_object($cmd) && $cmd->execCmd() != "OK") {
          self::updateEDFTempoInfos($edf_tempo);

          if ($cmd->execCmd() == "OK") {
            // Succès : on arrête les retries
            log::add('edf_tempo', 'info', "Synchronisation réussie au retry " . $attempt . "/3.");
            config::save('edf_retry_count', -1, 'edf_tempo');
            config::save('edf_retry_timestamp', 0, 'edf_tempo');
            return;
          }
        }
      } catch (Exception $e) {
        log::add('edf_tempo', 'warning', "Erreur retry " . $attempt . "/3 : " . $e->getMessage());
      }
    }

    config::save('edf_retry_count', $attempt, 'edf_tempo');

    // Après 3 échecs, log d'erreur final
    if ($attempt >= 3) {
      log::add('edf_tempo', 'error', "Impossible de récupérer les données EDF Tempo malgré 3 tentatives. Vous pouvez essayer manuellement via le bouton Rafraîchir sur l'équipement.");
      config::save('edf_retry_count', -1, 'edf_tempo');
      config::save('edf_retry_timestamp', 0, 'edf_tempo');
    }
  }


  // Fonction exécutée automatiquement tous les jours par Jeedom
  public static function cronDaily() {
    $eqLogics = self::byType('edf_tempo', true);

    foreach ($eqLogics as $edf_tempo) {
      try {
        $edf_tempo->checkAndUpdateCmd('edf_status', "NOK");
      } catch (Exception $e) {
        log::add('edf_tempo', 'warning', "Erreur cronDaily : " . $e->getMessage());
      }
    }
    // Force la MAJ du nombre de jours bleu le 1er septembre
    if (date('m-d') == '09-01') {
      self::updateMaxJrBleu();
    }

    // Vérifie si de nouveaux tarifs sont disponibles
    self::checkRemoteTarifs();
  }

  private static function getRemoteJson($url) {
    $ch = curl_init($url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
    curl_setopt($ch, CURLOPT_TIMEOUT, 10);
    curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 10);
    curl_setopt($ch, CURLOPT_ENCODING, '');
    $response = curl_exec($ch);

    if (curl_errno($ch)) {
      $error = curl_error($ch);
      curl_close($ch);
      throw new Exception("Impossible de récupérer les données distantes : " . $error);
    }
    curl_close($ch);

    $data = json_decode($response, true);
    if (!$data) {
      throw new Exception("Réponse JSON distante invalide.");
    }

    return $data;
  }

  public static function checkRemoteTarifs() {
    $url = config::byKey('global_url_tarifs_json', 'edf_tempo');
    if (empty($url)) {
      return;
    }

    try {
      $remote = self::getRemoteJson($url);
    } catch (Exception $e) {
      log::add('edf_tempo', 'warning', "Impossible de vérifier les tarifs distants : " . $e->getMessage());
      return;
    }

    if (!isset($remote['version'])) {
      log::add('edf_tempo', 'warning', "Fichier tarifs distant invalide (version manquante).");
      return;
    }

    $currentVersion = config::byKey('global_tarifs_version', 'edf_tempo');
    $dismissedVersion = config::byKey('global_tarifs_dismissed_version', 'edf_tempo');

    if ($remote['version'] != $currentVersion && $remote['version'] != $dismissedVersion) {
      $label = isset($remote['label']) ? $remote['label'] : $remote['version'];
      log::add('edf_tempo', 'warning', "Nouveaux tarifs EDF Tempo disponibles (" . $label . "). Rendez-vous dans la configuration du plugin pour les appliquer.");
    }
  }

  public static function fetchRemoteTarifs() {
    $url = config::byKey('global_url_tarifs_json', 'edf_tempo');
    if (empty($url)) {
      throw new Exception("URL des tarifs distants non configurée.");
    }

    $remote = self::getRemoteJson($url);

    if (!isset($remote['version']) || !isset($remote['tarifs'])) {
      throw new Exception("Fichier tarifs distant invalide.");
    }

    // Ajouter les tarifs actuels pour comparaison
    $remote['current'] = array(
      'bleu_hc'  => config::byKey('global_tempo_bleu_hc', 'edf_tempo'),
      'bleu_hp'  => config::byKey('global_tempo_bleu_hp', 'edf_tempo'),
      'blanc_hc' => config::byKey('global_tempo_blanc_hc', 'edf_tempo'),
      'blanc_hp' => config::byKey('global_tempo_blanc_hp', 'edf_tempo'),
      'rouge_hc' => config::byKey('global_tempo_rouge_hc', 'edf_tempo'),
      'rouge_hp' => config::byKey('global_tempo_rouge_hp', 'edf_tempo'),
    );

    return $remote;
  }

  public static function applyRemoteTarifs() {
    $remote = self::fetchRemoteTarifs();

    $tarifs = $remote['tarifs'];
    config::save('global_tempo_bleu_hc', $tarifs['bleu_hc'], 'edf_tempo');
    config::save('global_tempo_bleu_hp', $tarifs['bleu_hp'], 'edf_tempo');
    config::save('global_tempo_blanc_hc', $tarifs['blanc_hc'], 'edf_tempo');
    config::save('global_tempo_blanc_hp', $tarifs['blanc_hp'], 'edf_tempo');
    config::save('global_tempo_rouge_hc', $tarifs['rouge_hc'], 'edf_tempo');
    config::save('global_tempo_rouge_hp', $tarifs['rouge_hp'], 'edf_tempo');
    config::save('global_tarifs_version', $remote['version'], 'edf_tempo');
    config::save('global_tarifs_dismissed_version', '', 'edf_tempo');
    config::save('global_tarifs_update_date', date('d-m-Y à H:i'), 'edf_tempo');
    config::save('global_tarifs_update_source', 'synchronisé', 'edf_tempo');

    $label = isset($remote['label']) ? $remote['label'] : $remote['version'];
    log::add('edf_tempo', 'info', "Tarifs mis à jour : " . $label);

    return $remote;
  }

  public static function dismissRemoteTarifs() {
    try {
      $remote = self::fetchRemoteTarifs();
    } catch (Exception $e) {
      log::add('edf_tempo', 'warning', "Impossible d'ignorer les tarifs : " . $e->getMessage());
      return;
    }

    config::save('global_tarifs_dismissed_version', $remote['version'], 'edf_tempo');
    log::add('edf_tempo', 'info', "Notification de mise à jour des tarifs ignorée pour la version " . $remote['version']);
  }

  /*     * *********************Méthodes d'instance************************* */

  // Fonction exécutée automatiquement avant la création de l'équipement
  public function preInsert() {
  }

  // Fonction exécutée automatiquement après la création de l'équipement
  public function postInsert() {
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

    self::updateEDFTempoInfos($this); // mets à jour la tuile
  }

  // Fonction exécutée automatiquement avant la suppression de l'équipement
  public function preRemove() {
  }

  // Fonction exécutée automatiquement après la suppression de l'équipement
  public function postRemove() {
  }

  /*     * **********************Getteur Setteur*************************** */
  public static function updateEDFTempoInfos($eqlogic) {
    // log::add('edf_tempo', 'info', "Récupération des données sur le site d'EDF");
    $colors   = $eqlogic->getEDFColors();
    $restant  = $eqlogic->getEDFRestant();
    $eqlogic->checkAndUpdateCmd('edf_today', $colors->couleurJourJ);
    $eqlogic->checkAndUpdateCmd('edf_tomorrow', $colors->couleurJourJ1);
    $eqlogic->checkAndUpdateCmd('edf_nb_bleu', $restant->PARAM_NB_J_BLEU);
    $eqlogic->checkAndUpdateCmd('edf_nb_blanc', $restant->PARAM_NB_J_BLANC);          
    $eqlogic->checkAndUpdateCmd('edf_nb_rouge', $restant->PARAM_NB_J_ROUGE);     

    log::add('edf_tempo', 'debug', "Couleur du jour : " . $colors->couleurJourJ . " — Demain : " . (isset($colors->couleurJourJ1) ? $colors->couleurJourJ1 : 'N/A'));

    if (!isset($colors->couleurJourJ1) || $colors->couleurJourJ1 == "NA" || $colors->couleurJourJ1 == "NON_DEFINI"){
      $eqlogic->checkAndUpdateCmd('edf_status', "NOK");
      log::add('edf_tempo', 'warning', "Couleur de demain indisponible, nouvelle tentative ultérieure.");
    }else{
      $eqlogic->checkAndUpdateCmd('edf_lastupdate', date("d-m-Y à H:i"));
      $eqlogic->checkAndUpdateCmd('edf_status', "OK");
      log::add('edf_tempo', 'info', "Synchronisation EDF Tempo réussie le " . date("d-m-Y à H:i"));
    }

  }

  public function getEDFColors(){
    $d = new DateTime();
    $today = $d->format('Y-m-d');
    $tomorrow = $d->modify('+1 day')->format('Y-m-d');

    $baseUrl = config::byKey('global_url_edf_color', 'edf_tempo');
    $urlColors = $baseUrl . $today . "&dateApplicationBorneSup=" . $tomorrow . "&identifiantConsommateur=src";

    $colors = $this->getJson($urlColors);

    if(!$colors){
      $colors = json_decode('{"couleurJourJ":"NA","couleurJourJ1":"NA"}');
      log::add('edf_tempo', 'warning', "Impossible de récupérer la couleur des jours, nouvelle tentative ultérieure.");
      return $colors;
    }

    log::add('edf_tempo', 'debug', "Couleurs récupérées depuis : " . $colors['content']['dateApplicationBorneInf']);

    $couleurJourJ   = "NA";
    $couleurJourJ1  = "NA";

    foreach ($colors['content']['options'][0]['calendrier'] as $item) {
      if ($item['dateApplication'] == $today) {
          $couleurJourJ = $item['statut'];
      }
      if ($item['dateApplication'] == $tomorrow) {
          $couleurJourJ1 = $item['statut'];
      }
    }
	
	$r = new stdClass();
    $r->couleurJourJ  = $couleurJourJ;
    $r->couleurJourJ1 = $couleurJourJ1;

    return $r;
  }

  public function getEDFRestant(){
    $urlRestant = config::byKey('global_url_edf_restant', 'edf_tempo') . "&dateReference=" . date("Y-m-d");

    $restant = $this->getJson($urlRestant);
    if (!$restant){
      $restant = json_decode('{"PARAM_NB_J_BLANC":"NA","PARAM_NB_J_ROUGE":"NA","PARAM_NB_J_BLEU":"NA"}');
      log::add('edf_tempo', 'warning', "Impossible de récupérer le nombre de jours restants, nouvelle tentative ultérieure.");
      return $restant;
    }
    
	$r = new stdClass();
    foreach ($restant['content'] as $item) {
        $typeJourEff = $item['typeJourEff'];

        if ($typeJourEff == 'TEMPO_BLEU') {
            $r->PARAM_NB_J_BLEU = $item['nombreJours'] - $item['nombreJoursTires'];
        }

        if ($typeJourEff == 'TEMPO_BLANC') {
            $r->PARAM_NB_J_BLANC = $item['nombreJours'] - $item['nombreJoursTires'];
        }

        if ($typeJourEff == 'TEMPO_ROUGE') {
            $r->PARAM_NB_J_ROUGE = $item['nombreJours'] - $item['nombreJoursTires'];
        }

    }
    $restant = $r;
    return  $restant;
  }


  public function getJson($url){

    // Initialiser cURL
    $ch = curl_init($url);
    
    // Définir les options cURL
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
    curl_setopt($ch, CURLOPT_TIMEOUT, 15);
    curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 15);
    
    // Ajouter cette ligne pour gérer la décompression automatique
    curl_setopt($ch, CURLOPT_ENCODING, '');
    
    // Si l'API nécessite des en-têtes spécifiques, ajoutez-les ici
    $headers = [
        'Accept: application/json, text/plain, */*',
        'Accept-Encoding: gzip, deflate, br, zstd',
        'Accept-Language: fr-FR,fr;q=0.9,en-US;q=0.8,en;q=0.7',
        'Application-Origine-Controlee: site_RC',
        'Content-Type: application/json',
        'Origin: https://particulier.edf.fr',
        'Referer: https://particulier.edf.fr/',
        'Sec-CH-UA: "Chromium";v="128", "Not;A=Brand";v="24", "Google Chrome";v="128"',
        'Sec-CH-UA-Mobile: ?0',
        'Sec-CH-UA-Platform: "Windows"',
        'Sec-Fetch-Dest: empty',
        'Sec-Fetch-Mode: cors',
        'Sec-Fetch-Site: same-site',
        'Situation-Usage: saison',
        'User-Agent: Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/128.0.0.0 Safari/537.36',
        'X-Request-ID: 666'
    ];
    
    curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
    
    // Exécuter la requête cURL
    log::add('edf_tempo', 'debug', "cURL requête vers : " . $url);
    $response = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);

    // Vérifier les erreurs
    if (curl_errno($ch)) {
        $error = curl_error($ch);
        curl_close($ch);
        log::add('edf_tempo', 'debug', "cURL erreur : " . $error);
        return false;
    }

    curl_close($ch);
    log::add('edf_tempo', 'debug', "cURL réponse HTTP " . $httpCode . " — taille : " . strlen($response) . " octets");

    $data = json_decode($response, true);
    if ($data === null && json_last_error() !== JSON_ERROR_NONE) {
        log::add('edf_tempo', 'debug', "JSON decode erreur : " . json_last_error_msg() . " — réponse brute (500 premiers chars) : " . substr($response, 0, 500));
        return false;
    }

    return $data;
  }
  public static function updateMaxJrBleu(){

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

  // Exécution d'une commande
  public function execute($_options = array()) {
      $eqlogic = $this->getEqLogic();
      switch ($this->getLogicalId()) {
        case 'refresh': 
          log::add('edf_tempo', 'info', "Mise à jour manuelle le " . date("d-m-Y à H:i"));
          edf_tempo::updateEDFTempoInfos($eqlogic);
          // $eqlogic->checkAndUpdateCmd('edf_lastupdate', "Forcée le ".date("m-d-Y à H:i")); 
          $eqlogic->refreshWidget();      
        break;
      }
  }


  /*     * **********************Getteur Setteur*************************** */

}
