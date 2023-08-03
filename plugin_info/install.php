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
	$defaultUrlRestant 		= 'https://particulier.edf.fr/services/rest/referentiel/getNbTempoDays?TypeAlerte=TEMPO';
	$defaultUrlColor 		= 'https://particulier.edf.fr/services/rest/referentiel/searchTempoStore?dateRelevant=';
	$defaultTempoBleuHC 	= '0.1056€';
	$defaultTempoBleuHP 	= '0.1369€';
	$defaultTempoBlancHC 	= '0.1246€';
	$defaultTempoBlancHP 	= '0.1654€';
	$defaultTempoRougeHC 	= '0.1328€';
	$defaultTempoRougeHP 	= '0.7324€';

	config::save('global_url_edf_restant', $defaultUrlRestant, 'edf_tempo');
	config::save('global_url_edf_color', $defaultUrlColor, 'edf_tempo');
	config::save('global_tempo_bleu_hc', $defaultTempoBleuHC, 'edf_tempo');
	config::save('global_tempo_bleu_hp', $defaultTempoBleuHP, 'edf_tempo');
	config::save('global_tempo_blanc_hc', $defaultTempoBlancHC, 'edf_tempo');
	config::save('global_tempo_blanc_hp', $defaultTempoBlancHP, 'edf_tempo');
	config::save('global_tempo_rouge_hc', $defaultTempoRougeHC, 'edf_tempo');
	config::save('global_tempo_rouge_hp', $defaultTempoRougeHP, 'edf_tempo');
	log::add('edf_tempo', 'info', "Installation des valeurs par défaut.");
}


// Fonction exécutée automatiquement après la mise à jour du plugin
function edf_tempo_update() {
}

// Fonction exécutée automatiquement après la suppression du plugin
function edf_tempo_remove() {
}
