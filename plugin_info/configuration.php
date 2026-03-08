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
$tarifsVersion = config::byKey('global_tarifs_version', 'edf_tempo');
$tarifsDate = config::byKey('global_tarifs_update_date', 'edf_tempo');
$tarifsSource = config::byKey('global_tarifs_update_source', 'edf_tempo');
$tarifsDisplay = '';
if ($tarifsVersion) {
  $versionDate = DateTime::createFromFormat('Y-m-d', $tarifsVersion);
  $tarifsDisplay = $versionDate ? $versionDate->format('d/m/Y') : $tarifsVersion;
  if ($tarifsDate) {
    $tarifsDisplay .= ' — mis à jour le ' . $tarifsDate;
  }
  if ($tarifsSource) {
    $tarifsDisplay .= ' (' . $tarifsSource . ')';
  }
} else {
  $tarifsDisplay = 'Non défini';
}
?>
<form class="form-horizontal">
  <fieldset>

    <legend><i class="fas fa-sync-alt"></i> {{Mise à jour des tarifs}}</legend>
    <div class="form-group">
      <label class="col-md-4 control-label">{{Tarifs appliqués}}
      </label>
      <div class="col-md-4">
        <span class="label label-info" id="tarifsVersionLabel"><?php echo $tarifsDisplay; ?></span>
      </div>
    </div>
    <div class="form-group">
      <label class="col-md-4 control-label">{{URL fichier tarifs}}
        <sup><i class="fas fa-question-circle tooltips" title="{{URL du fichier JSON contenant les tarifs à jour}}"></i></sup>
      </label>
      <div class="col-md-4">
        <input class="configKey form-control" data-l1key="global_url_tarifs_json" value="https://raw.githubusercontent.com/idoexp/edf_tempo/main/tarifs.json"/>
      </div>
    </div>
    <div class="form-group">
      <div class="col-md-offset-4 col-md-4">
        <a class="btn btn-success" id="bt_checkTarifs"><i class="fas fa-search"></i> {{Vérifier les tarifs}}</a>
        <a class="btn btn-warning" id="bt_dismissTarifs"><i class="fas fa-bell-slash"></i> {{Ignorer cette version}}</a>
      </div>
    </div>
    <div id="tarifsCompareContainer" style="display:none;">
      <div class="form-group">
        <div class="col-md-offset-2 col-md-8">
          <div id="tarifsCompareContent"></div>
          <div style="margin-top:10px;">
            <a class="btn btn-success" id="bt_applyTarifs" style="display:none;"><i class="fas fa-check"></i> {{Appliquer ces tarifs}}</a>
          </div>
        </div>
      </div>
    </div>

    <legend><i class="fas fa-link"></i> {{URLs API EDF}}</legend>
    <div class="form-group">
      <label class="col-md-4 control-label">{{URL jours restant}}
        <sup><i class="fas fa-question-circle tooltips" title="{{Indiquez le lien direct vers la page des jours restant EDF}}"></i></sup>
      </label>
      <div class="col-md-4">
        <input class="configKey form-control"  data-l1key="global_url_edf_restant" value="https://api-commerce.edf.fr/commerce/activet/v1/saisons/search?option=TEMPO"/>
      </div>
    </div>

    <div class="form-group">
      <label class="col-md-4 control-label">{{URL couleur du jour}}
        <sup><i class="fas fa-question-circle tooltips" title="{{Indiquez le lien direct vers la page indiquant la couleur du jour EDF}}"></i></sup>
      </label>
      <div class="col-md-4">
        <input class="configKey form-control"  data-l1key="global_url_edf_color" value="https://api-commerce.edf.fr/commerce/activet/v1/calendrier-jours-effacement?option=TEMPO&amp;dateApplicationBorneInf="/>
      </div>
    </div>

    <legend><i class="fas fa-euro-sign"></i> {{Tarifs}}</legend>
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

    <legend><i class="fas fa-calendar-alt"></i> {{Jours maximum}}</legend>
    <div class="form-group">
      <label class="col-md-4 control-label">{{Nombres max de jours bleus }}
        <sup><i class="fas fa-question-circle tooltips" title="{{301 en cas d'année bisextile}}"></i></sup>
      </label>
      <div class="col-md-4">
        <input class="configKey form-control"  data-l1key="global_max_tempo_bleu" value="0"/>
      </div>
    </div>

    <div class="form-group">
      <label class="col-md-4 control-label">{{Nombres max de jours blancs }}
      </label>
      <div class="col-md-4">
        <input class="configKey form-control"  data-l1key="global_max_tempo_blanc" value="43"/>
      </div>
    </div>

    <div class="form-group">
      <label class="col-md-4 control-label">{{Nombres max de jours rouges }}
      </label>
      <div class="col-md-4">
        <input class="configKey form-control"  data-l1key="global_max_tempo_rouge" value="22"/>
      </div>
    </div>

  </fieldset>
</form>

<script>
var _remoteTarifsData = null;

var tarifsLabels = {
  'bleu_hc': 'Bleu HC',
  'bleu_hp': 'Bleu HP',
  'blanc_hc': 'Blanc HC',
  'blanc_hp': 'Blanc HP',
  'rouge_hc': 'Rouge HC',
  'rouge_hp': 'Rouge HP'
};

$('#bt_checkTarifs').on('click', function() {
  _remoteTarifsData = null;
  $('#bt_applyTarifs').hide();
  $('#tarifsCompareContainer').hide();
  $('#tarifsCompareContent').empty();

  $.ajax({
    type: 'POST',
    url: 'plugins/edf_tempo/core/ajax/edf_tempo.ajax.php',
    data: {
      action: 'fetchTarifs',
      ajax: 1
    },
    dataType: 'json',
    global: false,
    success: function(data) {
      if (data.state != 'ok') {
        $('#tarifsCompareContent').html('<div class="alert alert-danger"><i class="fas fa-exclamation-triangle"></i> ' + data.result + '</div>');
        $('#tarifsCompareContainer').show();
        return;
      }

      _remoteTarifsData = data.result;
      var remote = data.result.tarifs;
      var current = data.result.current;
      var label = data.result.label || data.result.version;

      var html = '<h4><i class="fas fa-exchange-alt"></i> Comparaison des tarifs — ' + label + '</h4>';
      html += '<table class="table table-bordered table-striped" style="max-width:600px;">';
      html += '<thead><tr><th>Créneau</th><th>Avant</th><th></th><th>Après</th></tr></thead>';
      html += '<tbody>';

      var hasChange = false;
      for (var key in tarifsLabels) {
        var avant = current[key] || '—';
        var apres = remote[key] || '—';
        var changed = (avant !== apres);
        if (changed) hasChange = true;
        var rowClass = changed ? ' style="font-weight:bold;"' : '';
        var arrow = changed ? '<i class="fas fa-arrow-right" style="color:#e67e22;"></i>' : '<i class="fas fa-equals" style="color:#95a5a6;"></i>';
        html += '<tr' + rowClass + '>';
        html += '<td>' + tarifsLabels[key] + '</td>';
        html += '<td>' + avant + '</td>';
        html += '<td style="text-align:center;">' + arrow + '</td>';
        html += '<td>' + apres + '</td>';
        html += '</tr>';
      }

      html += '</tbody></table>';

      if (!hasChange) {
        html += '<div class="alert alert-info"><i class="fas fa-info-circle"></i> Les tarifs sont déjà à jour.</div>';
      }

      $('#tarifsCompareContent').html(html);
      $('#tarifsCompareContainer').show();

      if (hasChange) {
        $('#bt_applyTarifs').show();
      }
    },
    error: function() {
      $('#tarifsCompareContent').html('<div class="alert alert-danger"><i class="fas fa-exclamation-triangle"></i> Impossible de récupérer les tarifs distants.</div>');
      $('#tarifsCompareContainer').show();
    }
  });
});

$('#bt_applyTarifs').on('click', function() {
  if (!_remoteTarifsData) return;
  var label = _remoteTarifsData.label || _remoteTarifsData.version;

  $.ajax({
    type: 'POST',
    url: 'plugins/edf_tempo/core/ajax/edf_tempo.ajax.php',
    data: {
      action: 'updateTarifs',
      ajax: 1
    },
    dataType: 'json',
    global: false,
    success: function(data) {
      if (data.state != 'ok') {
        $('#div_alert').showAlert({message: data.result, level: 'danger'});
        return;
      }
      $('#div_alert').showAlert({message: 'Tarifs mis à jour avec succès (' + label + ')', level: 'success'});
      setTimeout(function() { location.reload(); }, 1500);
    },
    error: function() {
      $('#div_alert').showAlert({message: 'Erreur lors de l\'application des tarifs.', level: 'danger'});
    }
  });
});

// Fonction appelée automatiquement par Jeedom après la sauvegarde de la config plugin
function edf_tempo_postSaveConfiguration() {
  $.ajax({
    type: 'POST',
    url: 'plugins/edf_tempo/core/ajax/edf_tempo.ajax.php',
    data: { action: 'markTarifsManual', ajax: 1 },
    dataType: 'json',
    global: false,
    async: false
  });
}

$('#bt_dismissTarifs').on('click', function() {
  $.ajax({
    type: 'POST',
    url: 'plugins/edf_tempo/core/ajax/edf_tempo.ajax.php',
    data: {
      action: 'dismissTarifs',
      ajax: 1
    },
    dataType: 'json',
    global: false,
    success: function(data) {
      if (data.state != 'ok') {
        $('#div_alert').showAlert({message: data.result, level: 'danger'});
        return;
      }
      $('#div_alert').showAlert({message: 'Notification de mise à jour ignorée pour cette version.', level: 'success'});
    },
    error: function() {
      $('#div_alert').showAlert({message: 'Erreur lors de l\'opération.', level: 'danger'});
    }
  });
});
</script>
