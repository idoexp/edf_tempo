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
	// API proxy
	config::save('global_api_url', 'https://ai-beauvaisis.fr/api/edf_tempo', 'edf_tempo');
	config::save('global_api_hmac_secret', 'da1029121617019c3f464e8bea4f8ae196ff3b0e7b27cc961eefd1fa0a1523ce', 'edf_tempo');
	config::save('global_max_tempo_blanc', 43, 'edf_tempo');
	config::save('global_max_tempo_rouge', 22, 'edf_tempo');
	log::add('edf_tempo', 'info', "Installation du plugin. Les tarifs seront synchronisés au premier rafraîchissement.");
}


// Fonction exécutée automatiquement après la mise à jour du plugin
function edf_tempo_update() {
	// S'assurer que l'URL API et le secret HMAC sont présents
	if (config::byKey('global_api_url', 'edf_tempo') == '') {
		config::save('global_api_url', 'https://ai-beauvaisis.fr/api/edf_tempo', 'edf_tempo');
	}
	if (config::byKey('global_api_hmac_secret', 'edf_tempo') == '') {
		config::save('global_api_hmac_secret', 'da1029121617019c3f464e8bea4f8ae196ff3b0e7b27cc961eefd1fa0a1523ce', 'edf_tempo');
	}
	log::add('edf_tempo', 'info', "Mise à jour du plugin edf_tempo.");
}

// Fonction exécutée automatiquement après la suppression du plugin
function edf_tempo_remove() {
}
