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
include_file('core', 'authentification', 'php');
if (!isConnect()) {
  include_file('desktop', '404', 'php');
  die();
}
?>
<form class="form-horizontal">
  <fieldset>
    <div class="form-group">
      <label class="col-md-4 control-label">{{URL jours restant}}
        <sup><i class="fas fa-question-circle tooltips" title="{{Indiquez le lien direct vers la page des jours restant EDF}}"></i></sup>
      </label>
      <div class="col-md-4">
        <input class="configKey form-control"  data-l1key="global_url_edf_restant" value="https://particulier.edf.fr/services/rest/referentiel/getNbTempoDays?TypeAlerte=TEMPO"/>
      </div>
    </div>

    <div class="form-group">
      <label class="col-md-4 control-label">{{URL couleur du jour}}
        <sup><i class="fas fa-question-circle tooltips" title="{{Indiquez le lien direct vers la page indiquant la couleur du jour EDF}}"></i></sup>
      </label>
      <div class="col-md-4">
        <input class="configKey form-control"  data-l1key="global_url_edf_color" value="https://particulier.edf.fr/services/rest/referentiel/searchTempoStore?dateRelevant="/>
      </div>
    </div>

    <div class="form-group">
      <label class="col-md-4 control-label">{{Tarif Bleu HC}}
        <sup><i class="fas fa-question-circle tooltips" title="{{Indiquez le prix en euros par kilowattheure}}"></i></sup>
      </label>
      <div class="col-md-4">
        <input class="configKey form-control"  data-l1key="global_tempo_bleu_hc" value="0.1296"/>
      </div>
    </div>

    <div class="form-group">
      <label class="col-md-4 control-label">{{Tarif Bleu HP}}
        <sup><i class="fas fa-question-circle tooltips" title="{{Indiquez le prix en euros par kilowattheure}}"></i></sup>
      </label>
      <div class="col-md-4">
        <input class="configKey form-control"  data-l1key="global_tempo_bleu_hp" value="0.1609"/>
      </div>
    </div>

    <div class="form-group">
      <label class="col-md-4 control-label">{{Tarif Blanc HC}}
        <sup><i class="fas fa-question-circle tooltips" title="{{Indiquez le prix en euros par kilowattheure}}"></i></sup>
      </label>
      <div class="col-md-4">
        <input class="configKey form-control"  data-l1key="global_tempo_blanc_hc" value="0.1486"/>
      </div>
    </div>
    <div class="form-group">
      <label class="col-md-4 control-label">{{Tarif Blanc HP}}
        <sup><i class="fas fa-question-circle tooltips" title="{{Indiquez le prix en euros par kilowattheure}}"></i></sup>
      </label>
      <div class="col-md-4">
        <input class="configKey form-control"  data-l1key="global_tempo_blanc_hp" value="0.1894"/>
      </div>
    </div>

    <div class="form-group">
      <label class="col-md-4 control-label">{{Tarif Rouge HC}}
        <sup><i class="fas fa-question-circle tooltips" title="{{Indiquez le prix en euros par kilowattheure}}"></i></sup>
      </label>
      <div class="col-md-4">
        <input class="configKey form-control"  data-l1key="global_tempo_rouge_hc" value="0.1568"/>
      </div>
    </div>
    <div class="form-group">
      <label class="col-md-4 control-label">{{Tarif Rouge HP}}
        <sup><i class="fas fa-question-circle tooltips" title="{{Indiquez le prix en euros par kilowattheure}}"></i></sup>
      </label>
      <div class="col-md-4">
        <input class="configKey form-control"  data-l1key="global_tempo_rouge_hp" value="0.7562"/>
      </div>
    </div>

    <div class="form-group">
      <label class="col-md-4 control-label">{{Nombres max de jours bleus }}
        <sup><i class="fas fa-question-circle tooltips" title="{{301 en cas d'année bisextile}}"></i></sup>
      </label>
      <div class="col-md-4">
        <input class="configKey form-control"  data-l1key="global_max_tempo_bleu" value="0"/>
      </div>
    </div>


  </fieldset>
</form>
