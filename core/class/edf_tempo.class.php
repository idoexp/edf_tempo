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

    $heureStart = 11;
    $heureEnd   = 12;

    $heure    = date("G");
    $minutes  = date("i");

    if ($heure >= $heureStart && $heure <= $heureEnd && $minutes <= 5 || $heure == 0 && $minutes <= 2){
      foreach ($eqLogics as $edf_tempo) {
        try {
            if ($heure == 0 && $minutes = 2){
              $edf_tempo->checkAndUpdateCmd('edf_status', "NOK");
            }

            $cmd = $edf_tempo->getCmd(null,'edf_status');
            if(is_object($cmd)){
              $edf_status = $cmd->execCmd();
              if ($edf_status != "OK"){
                self::updateEDFTempoInfos($edf_tempo);
              }
              if ($heure == $heureEnd && $edf_status !="OK"){
                log::add('edf_tempo', 'error', "Impossible de récupérer la couleur des jours depuis 11h du matin.");
              }
            }

          // $cronExpression = $edf_tempo->getConfiguration('autorefresh');
          // if (self::isCronTimeToRun($cronExpression)) {
          // }
        } catch (Exception $e) {
          log::add('edf_tempo', 'info', $e->getMessage());
        }
      }
    }

  }


  public static function isCronTimeToRun($cronExpression) {
    // Convertir la valeur du cron en heure et minute au format 'HH:mm'
    list($minute, $hour) = explode(' ', $cronExpression);
    $cronTime = sprintf('%02d:%02d', $hour, $minute);
    
    // Vérifier si le cron correspond à l'heure actuelle
    return $cronTime == date('H:i');
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
    $eqLogics = self::byType('edf_tempo', true);

    foreach ($eqLogics as $edf_tempo) {
      try {
        $edf_tempo->checkAndUpdateCmd('edf_status', "NOK");
        $cronExpression = $edf_tempo->getConfiguration('autorefresh');
        if (self::isCronTimeToRun($cronExpression)) {
          self::updateEDFTempoInfos($edf_tempo);
        }
      } catch (Exception $e) {
        log::add('edf_tempo', 'info', $e->getMessage());
      }
    }
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
    $info->setOrder(1);
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
  public static function updateEDFTempoInfos($eqlogic) {
    // log::add('edf_tempo', 'info', "Récupération des données sur le site d'EDF");
    $colors   = $eqlogic->getEDFColors();
    $restant  = $eqlogic->getEDFRestant();
    $eqlogic->checkAndUpdateCmd('edf_today', $colors->couleurJourJ);
    $eqlogic->checkAndUpdateCmd('edf_tomorrow', $colors->couleurJourJ1);
    $eqlogic->checkAndUpdateCmd('edf_nb_bleu', $restant->PARAM_NB_J_BLEU);
    $eqlogic->checkAndUpdateCmd('edf_nb_blanc', $restant->PARAM_NB_J_BLANC);          
    $eqlogic->checkAndUpdateCmd('edf_nb_rouge', $restant->PARAM_NB_J_ROUGE);     

    log::add('edf_tempo', 'info', "couleur today ". $colors->couleurJourJ);     

    if ($colors->couleurJourJ1 == "NA" || $colors->couleurJourJ1 == "NON_DEFINI" || !isset($colors->couleurJourJ1)){
      $eqlogic->checkAndUpdateCmd('edf_status', "NOK");
      log::add('edf_tempo', 'info', "Erreur de récupération des informations, je test un peu plus tard.");
    }else{
      $eqlogic->checkAndUpdateCmd('edf_lastupdate', date("d-m-Y à H:i"));          
      $eqlogic->checkAndUpdateCmd('edf_status', "OK");
      log::add('edf_tempo', 'info', "Mise à jour des informations d'EDF Tempo le ".date("d-m-Y à H:i"));
    }

  }

  public function getEDFColors(){
    $urlColors = config::byKey('global_url_edf_color', 'edf_tempo').date("Y-m-d");

    $d = new DateTime();
    $today = $d->format('Y-m-d');
    $tomorrow = $d->modify('+1 day')->format('Y-m-d');

    $urlColors ="https://api-commerce.edf.fr/commerce/activet/v1/calendrier-jours-effacement?option=TEMPO&dateApplicationBorneInf=".$today."&dateApplicationBorneSup=".$tomorrow."&identifiantConsommateur=src";

    $colors = $this->getJson($urlColors);

    log::add('edf_tempo', 'info', "Récupération des couleurs : " .$colors['content']['dateApplicationBorneInf']) ;
    if(!$colors){
      $colors = json_decode('{"couleurJourJ":"NA","couleurJourJ1":"NA"}');
      log::add('edf_tempo', 'info', "Erreur de récupération de la couleur des jours, je test un peu plus tard.");
      return $colors;
    }

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
    $r->couleurJourJ  = $couleurJourJ;
    $r->couleurJourJ1 = $couleurJourJ1;

    return $r;
  }

  public function getEDFRestant(){
    // $urlRestant = config::byKey('global_url_edf_restant', 'edf_tempo');
    
    $urlRestant = "https://api-commerce.edf.fr/commerce/activet/v1/saisons/search?option=TEMPO&dateReference=".date("Y-m-d");

    $restant = $this->getJson($urlRestant);
    $sortedData = [];
    if (!$restant){
      $restant = json_decode('{"PARAM_NB_J_BLANC":"NA","PARAM_NB_J_ROUGE":"NA","PARAM_NB_J_BLEU":"NA"}');
      log::add('edf_tempo', 'info', "Erreur de récupération du nombres de jours restant, je test un peu plus tard.");
      return $restant;
    }
    
    foreach ($restant['content'] as $item) {
        $typeJourEff = $item['typeJourEff'];
        if (!isset($sortedData[$typeJourEff])) {
            $sortedData[$typeJourEff] = [];
        }
        $sortedData[$typeJourEff][] = $item;

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
    $response = curl_exec($ch);
    
    // Vérifier les erreurs
    if (curl_errno($ch)) {
        // echo 'Erreur cURL: ' . curl_error($ch);
        return false;
    } else {
        // Traiter la réponse JSON
        $data = json_decode($response, true);
        return $data;
        
    }
    
  }
  public function getJson_old2($url){
    if (function_exists('curl_init')) {
      log::add('edf_tempo', 'debug', "fonction getJson via -> cUrl");
      $curl = curl_init();
      curl_setopt($curl, CURLOPT_URL, $url);
      curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
      curl_setopt($curl, CURLOPT_TIMEOUT, 15);
      curl_setopt($curl, CURLOPT_CONNECTTIMEOUT, 15);
      curl_setopt($curl, CURLOPT_FOLLOWLOCATION, true); // Suivre les redirections
      curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, false); // Ne pas vérifier le certificat SSL
      curl_setopt($curl, CURLOPT_HTTPHEADER, array(
        "User-Agent: Wget/1.20.3 (linux-gnu)",
        "Accept: text/html,application/xhtml+xml,application/xml;q=0.9,image/webp,*/*;q=0.8",
        "Content-Type: application/json"
      ));
      $data = curl_exec($curl);
      $httpRespCode = curl_getinfo($curl, CURLINFO_HTTP_CODE);
      curl_close($curl);
  
      log::add('edf_tempo', 'debug', "Réponse HTTP : ". $httpRespCode);
      if ($httpRespCode == 0) {
        log::add('edf_tempo', 'error', "Impossible de récupérer les données : ". curl_error($curl));
        return false;
      }
    } else {
      log::add('edf_tempo', 'error', "cURL n'est pas disponible sur ce serveur.");
      return false;
    }
  
    log::add('edf_tempo', 'debug', "Données récupérées : ". $data);
    $retour = json_decode($data);
    log::add('edf_tempo', 'debug', "BB : ". $retour->PARAM_NB_J_BLEU);
    return $retour;    
  }

  public function getJson_old($url){
    if (function_exists('curl_init')) {
      log::add('edf_tempo', 'debug', "fonction getJson via -> cUrl");
      // Utiliser cURL pour récupérer les données
      $curl           = curl_init();
      curl_setopt($curl, CURLOPT_URL, $url);
      curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
      curl_setopt($curl, CURLOPT_TIMEOUT,        15);
      curl_setopt($curl, CURLOPT_CONNECTTIMEOUT, 15);
      $data           = curl_exec($curl);
      $httpRespCode   = curl_getinfo($curl, CURLINFO_HTTP_CODE);
      curl_close($curl);


      log::add('edf_tempo', 'debug', "Réponse HHTP : ". $httpRespCode);
      if ($httpRespCode == 0) {
        log::add('edf_tempo', 'error', "Impossible de récupérer les données : ". curl_error($curl));
        return false;
      }
  
    } else {
  
      // Utilise file_get_contents pour récupérer les données
      log::add('edf_tempo', 'debug', "fonction getJson via -> file_get_contents");
      $opts = array(
        'http'=>array(
          'method'=>"GET",
          'header'=>array( "User-Agent: Wget/1.20.3 (linux-gnu)",
              "Accept: text/html,application/xhtml+xml,application/xml;q=0.9,image/webp,*/*;q=0.8",
              "Content-Type: application/json"
          )
        )
      );
      $context  = stream_context_create($opts);
      $data     = file_get_contents($url, false, $context);
      if ($data === false) {
          log::add('edf_tempo', 'error', "Impossible de récupérer les données : ". error_get_last()['message']);
          return false;
      }    
    }

    log::add('edf_tempo', 'debug', "Données récupérées : ". $data);
    $retour   = json_decode($data);
    return $retour;    
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
          log::add('edf_tempo', 'info', "Mise à jour forcée le ".date("m-d-Y à H:i"));
          $eqlogic->updateEDFTempoInfos($eqlogic);
          // $eqlogic->checkAndUpdateCmd('edf_lastupdate', "Forcée le ".date("m-d-Y à H:i")); 
          $eqlogic->refreshWidget();      
        break;
      }
  }


  /*     * **********************Getteur Setteur*************************** */

}
