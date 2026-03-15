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
?>
<form class="form-horizontal">
  <fieldset>

    <legend><i class="fas fa-euro-sign"></i> {{Tarifs}}</legend>
    <div class="form-group">
      <label class="col-md-4 control-label">{{Version des tarifs}}</label>
      <div class="col-md-4">
        <?php if ($tarifsVersion) {
          $versionDate = DateTime::createFromFormat('Y-m-d', $tarifsVersion);
          $versionLabel = $versionDate ? $versionDate->format('d/m/Y') : $tarifsVersion;
        ?>
          <span class="label label-primary" style="font-size:13px;"><?= $versionLabel ?></span>
          <?php if ($tarifsDate) {
            $dt = DateTime::createFromFormat('d-m-Y à H:i', $tarifsDate);
            $formattedDate = $dt ? $dt->format('d/m/Y à H:i') : $tarifsDate;
          ?>
            <span class="label label-default" style="font-size:11px; margin-left:5px;">maj <?= $formattedDate ?></span>
          <?php } ?>
          <?php if ($tarifsSource) { ?>
            <span class="label <?= (strpos($tarifsSource, 'API') !== false) ? 'label-info' : 'label-warning' ?>" style="font-size:11px; margin-left:5px;"><?= $tarifsSource ?></span>
          <?php } ?>
        <?php } else { ?>
          <span class="label label-default" style="font-size:13px;">Non synchronisé</span>
        <?php } ?>
        <br><a class="btn btn-success btn-sm" id="bt_syncTarifs" style="margin-top:8px; margin-bottom:8px;"><i class="fas fa-sync-alt"></i> {{Synchroniser les prix depuis l'API}}</a>
      </div>
    </div>

    <div class="form-group">
      <label class="col-md-4 control-label">{{Tarif Bleu HC}}
        <sup><i class="fas fa-question-circle tooltips" title="{{Prix en euros par kWh}}"></i></sup>
      </label>
      <div class="col-md-4">
        <input class="configKey form-control" data-l1key="global_tempo_bleu_hc"/>
      </div>
    </div>
    <div class="form-group">
      <label class="col-md-4 control-label">{{Tarif Bleu HP}}</label>
      <div class="col-md-4">
        <input class="configKey form-control" data-l1key="global_tempo_bleu_hp"/>
      </div>
    </div>
    <div class="form-group">
      <label class="col-md-4 control-label">{{Tarif Blanc HC}}</label>
      <div class="col-md-4">
        <input class="configKey form-control" data-l1key="global_tempo_blanc_hc"/>
      </div>
    </div>
    <div class="form-group">
      <label class="col-md-4 control-label">{{Tarif Blanc HP}}</label>
      <div class="col-md-4">
        <input class="configKey form-control" data-l1key="global_tempo_blanc_hp"/>
      </div>
    </div>
    <div class="form-group">
      <label class="col-md-4 control-label">{{Tarif Rouge HC}}</label>
      <div class="col-md-4">
        <input class="configKey form-control" data-l1key="global_tempo_rouge_hc"/>
      </div>
    </div>
    <div class="form-group">
      <label class="col-md-4 control-label">{{Tarif Rouge HP}}</label>
      <div class="col-md-4">
        <input class="configKey form-control" data-l1key="global_tempo_rouge_hp"/>
      </div>
    </div>

    <legend><i class="fas fa-calendar-alt"></i> {{Jours maximum}}</legend>
    <div class="form-group">
      <label class="col-md-4 control-label">{{Jours bleus max}}
        <sup><i class="fas fa-question-circle tooltips" title="{{301 en cas d'année bisextile — calculé automatiquement}}"></i></sup>
      </label>
      <div class="col-md-4">
        <input class="configKey form-control" data-l1key="global_max_tempo_bleu"/>
      </div>
    </div>
    <div class="form-group">
      <label class="col-md-4 control-label">{{Jours blancs max}}</label>
      <div class="col-md-4">
        <input class="configKey form-control" data-l1key="global_max_tempo_blanc" value="43"/>
      </div>
    </div>
    <div class="form-group">
      <label class="col-md-4 control-label">{{Jours rouges max}}</label>
      <div class="col-md-4">
        <input class="configKey form-control" data-l1key="global_max_tempo_rouge" value="22"/>
      </div>
    </div>

  </fieldset>
</form>

<div class="modal fade" id="md_tarifsCompare" tabindex="-1" role="dialog">
  <div class="modal-dialog" role="document">
    <div class="modal-content">
      <div class="modal-header">
        <button type="button" class="close" data-dismiss="modal"><span>&times;</span></button>
        <h4 class="modal-title"><i class="fas fa-exchange-alt"></i> {{Comparaison des tarifs}}</h4>
      </div>
      <div class="modal-body" id="tarifsCompareContent"></div>
      <div class="modal-footer">
        <button type="button" class="btn btn-default" data-dismiss="modal"><i class="fas fa-times"></i> {{Annuler}}</button>
        <button type="button" class="btn btn-success" id="bt_applyTarifs" style="display:none;"><i class="fas fa-check"></i> {{Appliquer ces tarifs}}</button>
      </div>
    </div>
  </div>
</div>

<script>
var tarifsLabels = {
  'bleu_hc': 'Bleu HC', 'bleu_hp': 'Bleu HP',
  'blanc_hc': 'Blanc HC', 'blanc_hp': 'Blanc HP',
  'rouge_hc': 'Rouge HC', 'rouge_hp': 'Rouge HP'
};

$('#bt_syncTarifs').on('click', function() {
  var btn = $(this);
  btn.prop('disabled', true).find('i').addClass('fa-spin');
  $('#bt_applyTarifs').hide();

  $.ajax({
    type: 'POST',
    url: 'plugins/edf_tempo/core/ajax/edf_tempo.ajax.php',
    data: { action: 'fetchTarifs', ajax: 1 },
    dataType: 'json',
    global: false,
    success: function(data) {
      btn.prop('disabled', false).find('i').removeClass('fa-spin');
      if (data.state != 'ok') {
        $('#div_alert').showAlert({message: data.result, level: 'danger'});
        return;
      }

      var remote = data.result.tarifs;
      var current = data.result.current;
      var label = data.result.label || data.result.version;

      var html = '<h4>' + label + '</h4>';
      html += '<table class="table table-bordered table-striped">';
      html += '<thead><tr><th>{{Créneau}}</th><th>{{Actuel}}</th><th></th><th>{{Nouveau}}</th></tr></thead>';
      html += '<tbody>';

      var hasChange = false;
      for (var key in tarifsLabels) {
        var avant = current[key] || '—';
        var apres = remote[key] || '—';
        var changed = (avant !== apres);
        if (changed) hasChange = true;
        var rowStyle = changed ? ' style="font-weight:bold;"' : '';
        var arrow;
        if (!changed) {
          arrow = '<i class="fas fa-equals" style="color:#95a5a6;"></i>';
        } else {
          var avantNum = parseFloat(String(avant).replace(/[^0-9.,]/g, '').replace(',', '.'));
          var apresNum = parseFloat(String(apres).replace(/[^0-9.,]/g, '').replace(',', '.'));
          if (apresNum > avantNum) {
            arrow = '<i class="fas fa-arrow-up" style="color:#e74c3c;"></i>';
          } else {
            arrow = '<i class="fas fa-arrow-down" style="color:#27ae60;"></i>';
          }
        }
        html += '<tr' + rowStyle + '>';
        html += '<td>' + tarifsLabels[key] + '</td>';
        html += '<td>' + avant + '</td>';
        html += '<td style="text-align:center;">' + arrow + '</td>';
        html += '<td>' + apres + '</td>';
        html += '</tr>';
      }
      html += '</tbody></table>';

      if (!hasChange) {
        html += '<div class="alert alert-info"><i class="fas fa-info-circle"></i> {{Les tarifs sont déjà à jour.}}</div>';
      }

      $('#tarifsCompareContent').html(html);
      if (hasChange) {
        $('#bt_applyTarifs').show();
      }
      $('#md_tarifsCompare').modal('show');
    },
    error: function() {
      btn.prop('disabled', false).find('i').removeClass('fa-spin');
      $('#div_alert').showAlert({message: '{{Impossible de récupérer les tarifs distants.}}', level: 'danger'});
    }
  });
});

$('#bt_applyTarifs').on('click', function() {
  var btn = $(this);
  btn.prop('disabled', true);
  $.ajax({
    type: 'POST',
    url: 'plugins/edf_tempo/core/ajax/edf_tempo.ajax.php',
    data: { action: 'applyTarifs', ajax: 1 },
    dataType: 'json',
    global: false,
    success: function(data) {
      if (data.state != 'ok') {
        btn.prop('disabled', false);
        $('#div_alert').showAlert({message: data.result, level: 'danger'});
        return;
      }
      $('#md_tarifsCompare').modal('hide');
      $('#div_alert').showAlert({message: '{{Tarifs synchronisés avec succès.}}', level: 'success'});
      setTimeout(function() { location.reload(); }, 1500);
    },
    error: function() {
      btn.prop('disabled', false);
      $('#div_alert').showAlert({message: '{{Erreur lors de l\'application des tarifs.}}', level: 'danger'});
    }
  });
});

// Appelé automatiquement par Jeedom après sauvegarde manuelle de la config
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
</script>
