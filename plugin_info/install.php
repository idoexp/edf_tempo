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

require_once dirname(__FILE__) . '/../../../core/php/core.inc.php';

// Fonction exécutée automatiquement après l'installation du plugin
function edf_tempo_install() {
	$defaultUrlRestant 		= 'https://api-commerce.edf.fr/commerce/activet/v1/saisons/search?option=TEMPO';
	$defaultUrlColor 		= 'https://api-commerce.edf.fr/commerce/activet/v1/calendrier-jours-effacement?option=TEMPO&dateApplicationBorneInf=';
	/* **** Tarif 1er Février 2026 **** */
	$defaultTempoBleuHC 	= '0.1325€';
	$defaultTempoBleuHP 	= '0.1612€';
	$defaultTempoBlancHC 	= '0.1499€';
	$defaultTempoBlancHP 	= '0.1871€';
	$defaultTempoRougeHC 	= '0.1575€';
	$defaultTempoRougeHP 	= '0.7060€';

	config::save('global_url_edf_restant', $defaultUrlRestant, 'edf_tempo');
	config::save('global_url_edf_color', $defaultUrlColor, 'edf_tempo');
	config::save('global_tempo_bleu_hc', $defaultTempoBleuHC, 'edf_tempo');
	config::save('global_tempo_bleu_hp', $defaultTempoBleuHP, 'edf_tempo');
	config::save('global_tempo_blanc_hc', $defaultTempoBlancHC, 'edf_tempo');
	config::save('global_tempo_blanc_hp', $defaultTempoBlancHP, 'edf_tempo');
	config::save('global_tempo_rouge_hc', $defaultTempoRougeHC, 'edf_tempo');
	config::save('global_tempo_rouge_hp', $defaultTempoRougeHP, 'edf_tempo');
	config::save('global_max_tempo_blanc', 43, 'edf_tempo');
	config::save('global_max_tempo_rouge', 22, 'edf_tempo');
	config::save('global_url_tarifs_json', 'https://raw.githubusercontent.com/idoexp/edf_tempo/main/tarifs.json', 'edf_tempo');
	config::save('global_tarifs_version', '2026-02-01', 'edf_tempo');
	config::save('global_tarifs_dismissed_version', '', 'edf_tempo');
	config::save('global_tarifs_update_date', date('d-m-Y'), 'edf_tempo');
	config::save('global_tarifs_update_source', 'installation', 'edf_tempo');
	log::add('edf_tempo', 'info', "Installation des valeurs par défaut.");
}


// Fonction exécutée automatiquement après la mise à jour du plugin
function edf_tempo_update() {
	config::save('global_url_edf_restant', 'https://api-commerce.edf.fr/commerce/activet/v1/saisons/search?option=TEMPO', 'edf_tempo');
	config::save('global_url_edf_color', 'https://api-commerce.edf.fr/commerce/activet/v1/calendrier-jours-effacement?option=TEMPO&dateApplicationBorneInf=', 'edf_tempo');
	config::save('global_max_tempo_blanc', 43, 'edf_tempo');
	config::save('global_max_tempo_rouge', 22, 'edf_tempo');
	config::save('global_url_tarifs_json', 'https://raw.githubusercontent.com/idoexp/edf_tempo/main/tarifs.json', 'edf_tempo');
	// Initialiser la version des tarifs si elle n'existe pas (migration depuis une ancienne version)
	if (config::byKey('global_tarifs_version', 'edf_tempo') == '') {
		config::save('global_tarifs_version', '', 'edf_tempo');
		config::save('global_tarifs_dismissed_version', '', 'edf_tempo');
	}
	// TODO: Retirer ce bloc à la prochaine version — écrasement exceptionnel des tarifs (1er Février 2026)
	config::save('global_tempo_bleu_hc', '0.1325€', 'edf_tempo');
	config::save('global_tempo_bleu_hp', '0.1612€', 'edf_tempo');
	config::save('global_tempo_blanc_hc', '0.1499€', 'edf_tempo');
	config::save('global_tempo_blanc_hp', '0.1871€', 'edf_tempo');
	config::save('global_tempo_rouge_hc', '0.1575€', 'edf_tempo');
	config::save('global_tempo_rouge_hp', '0.7060€', 'edf_tempo');
	config::save('global_tarifs_version', '2026-02-01', 'edf_tempo');
	config::save('global_tarifs_update_date', date('d-m-Y à H:i'), 'edf_tempo');
	config::save('global_tarifs_update_source', 'mise à jour du plugin', 'edf_tempo');
	config::save('global_tarifs_dismissed_version', '', 'edf_tempo');
	log::add('edf_tempo', 'info', "Mise à jour du plugin effectuée. Tarifs mis à jour vers la version 2026-02-01.");
	// Vérifier si de nouveaux tarifs sont disponibles
	edf_tempo::checkRemoteTarifs();
}

// Fonction exécutée automatiquement après la suppression du plugin
function edf_tempo_remove() {
}
