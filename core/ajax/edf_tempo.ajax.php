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

try {
    require_once dirname(__FILE__) . '/../../../../core/php/core.inc.php';
    include_file('core', 'authentification', 'php');

    if (!isConnect('admin')) {
        throw new Exception(__('401 - Accès non autorisé', __FILE__));
    }

    ajax::init();

    if (init('action') == 'fetchTarifs') {
      $tarifs = edf_tempo::callApi('tarifs');
      if (!$tarifs || !isset($tarifs['tarifs'])) {
        throw new Exception(__('Impossible de récupérer les tarifs depuis l\'API.', __FILE__));
      }
      $tarifs['current'] = array(
        'bleu_hc'  => config::byKey('global_tempo_bleu_hc', 'edf_tempo'),
        'bleu_hp'  => config::byKey('global_tempo_bleu_hp', 'edf_tempo'),
        'blanc_hc' => config::byKey('global_tempo_blanc_hc', 'edf_tempo'),
        'blanc_hp' => config::byKey('global_tempo_blanc_hp', 'edf_tempo'),
        'rouge_hc' => config::byKey('global_tempo_rouge_hc', 'edf_tempo'),
        'rouge_hp' => config::byKey('global_tempo_rouge_hp', 'edf_tempo'),
      );
      ajax::success($tarifs);
    }

    if (init('action') == 'applyTarifs') {
      $tarifs = edf_tempo::callApi('tarifs');
      if (!$tarifs || !isset($tarifs['tarifs'])) {
        throw new Exception(__('Impossible de récupérer les tarifs depuis l\'API.', __FILE__));
      }
      config::save('global_tempo_bleu_hc', $tarifs['tarifs']['bleu_hc'], 'edf_tempo');
      config::save('global_tempo_bleu_hp', $tarifs['tarifs']['bleu_hp'], 'edf_tempo');
      config::save('global_tempo_blanc_hc', $tarifs['tarifs']['blanc_hc'], 'edf_tempo');
      config::save('global_tempo_blanc_hp', $tarifs['tarifs']['blanc_hp'], 'edf_tempo');
      config::save('global_tempo_rouge_hc', $tarifs['tarifs']['rouge_hc'], 'edf_tempo');
      config::save('global_tempo_rouge_hp', $tarifs['tarifs']['rouge_hp'], 'edf_tempo');
      config::save('global_tarifs_version', $tarifs['version'] ?? '', 'edf_tempo');
      config::save('global_tarifs_update_date', date('d-m-Y à H:i'), 'edf_tempo');
      config::save('global_tarifs_update_source', 'Synchronisé depuis l\'API', 'edf_tempo');
      log::add('edf_tempo', 'info', "Tarifs appliqués : " . ($tarifs['label'] ?? $tarifs['version'] ?? ''));
      ajax::success();
    }

    if (init('action') == 'getCalendar') {
      $calendar = edf_tempo::callApi('calendar');
      if (!$calendar || !isset($calendar['colors'])) {
        throw new Exception(__('Impossible de récupérer le calendrier depuis l\'API.', __FILE__));
      }
      ajax::success($calendar);
    }

    if (init('action') == 'markTarifsManual') {
      config::save('global_tarifs_update_date', date('d-m-Y à H:i'), 'edf_tempo');
      config::save('global_tarifs_update_source', 'Modifié manuellement', 'edf_tempo');
      log::add('edf_tempo', 'info', "Tarifs modifiés manuellement le " . date('d-m-Y à H:i'));
      ajax::success();
    }

    throw new Exception(__('Aucune méthode correspondante à', __FILE__) . ' : ' . init('action'));
    /*     * *********Catch exeption*************** */
}
catch (Exception $e) {
    ajax::error(displayException($e), $e->getCode());
}
