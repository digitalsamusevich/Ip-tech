<div class="spacer"></div>

<?php if ($demo_mode) { ?>
<div class="alert alert-warning text-center"><i class="fa fa-exclamation-circle"></i> DEMO - No data will be saved <button type="button" class="close" data-dismiss="alert">&times;</button></div>
<?php } ?>
<?php if (!empty($warning_message)) { ?>
<div class="alert alert-danger"><i class="fa fa-exclamation-circle"></i> <?php echo $warning_message; ?> <button type="button" class="close" data-dismiss="alert">&times;</button></div>
<?php } ?>

<div class="row">
  <div class="col-md-6">
    <div class="panel panel-default">
      <div class="panel-heading">
        <h3 class="panel-title"><i class="fa fa-file-text-o"></i> <?php echo $_language->get('text_process_summary'); ?></h3>
      </div>
      <ul class="list-group">
        <li class="list-group-item bold">
          <?php echo $_language->get('text_rows_csv'); ?>
          <span class="pull-right badge"><?php echo $summary['total_rows']; ?></span>
        </li>
        <!--
        <li class="list-group-item">
          <?php echo $_language->get('text_rows_process'); ?>
          <span class="pull-right badge">XX</span>
        </li>
        <li class="list-group-item">
          <?php echo $_language->get('text_rows_insert'); ?>
          <span class="pull-right badge green">XX</span>
        </li>
        <li class="list-group-item">
          <?php echo $_language->get('text_rows_update'); ?>
          <span class="pull-right badge clearblue">XX</span>
        </li>
        -->
      </ul>
    </div>
  </div>
  <div class="col-md-6">
    <div class="panel panel-default">
      <div class="panel-heading">
        <h3 class="panel-title"><i class="fa fa-area-chart"></i> <?php echo $_language->get('text_process_done'); ?></h3>
      </div>
      <ul class="list-group">
        <li class="list-group-item bold">
          <?php echo $_language->get('text_rows_processed'); ?>
          <span class="pull-right badge stat-processed">0</span>
        </li>
        <li class="list-group-item">
          <?php echo $_language->get('text_rows_inserted'); ?>
          <span class="pull-right badge inserted stat-inserted">0</span>
        </li>
        <li class="list-group-item">
          <?php echo $_language->get('text_rows_updated'); ?>
          <span class="pull-right badge updated stat-updated">0</span>
        </li>
        <li class="list-group-item">
          <?php echo $_language->get('text_rows_disabled'); ?>
          <span class="pull-right badge disabled stat-disabled">0</span>
        </li>
        <li class="list-group-item">
          <?php echo $_language->get('text_rows_skipped'); ?>
          <span class="pull-right badge skipped stat-skipped">0</span>
        </li>
        <li class="list-group-item">
          <?php echo $_language->get('text_rows_deleted'); ?>
          <span class="pull-right badge deleted stat-deleted">0</span>
        </li>
        <li class="list-group-item">
          <?php echo $_language->get('text_rows_error'); ?>
          <span class="pull-right badge error stat-error">0</span>
        </li>
        
      </ul>
    </div>
  </div>
  
</div>

<div>
  <button type="button" class="startProcess btn btn-success btn-lg btn-block"><i class="fa fa-play"></i> <?php echo $_language->get('text_start_process'); ?></button>
  <button type="button" class="pauseProcess btn btn-default btn-lg btn-block" style="display:none"><i class="fa fa-refresh fa-pulse fa-spin"></i> <?php echo $_language->get('text_pause_process'); ?></button>
</div>

<div class="spacer"></div>

<div id="importProgress" class="progress">
  <div class="progress-bar progress-bar-striped progress-bar-success active"></div>
</div>

<div class="spacer"></div>

<div class="process-log" style="display:none">
  <table class="table table-bordered">
    <thead>
      <tr>
        <th style="width:5%">Row</th>
        <th style="width:20%">Status</th>
        <th>Info</th>
      </tr>
    </thead>
    <tbody>
    </tbody>
  </table>
</div>

<div class="spacer"></div>

<div class="alert alert-danger obui-errors" style="display:none"></div>

<div class="spacer"></div>

<script type="text/javascript"><!--
/* Step 5 */
var pauseProcess = 1;
function processQueue(postproc) {
  if (postproc) {
    proc_url = 'index.php?route=module/universal_import/postproc&<?php echo $token; ?>';
  } else {
    proc_url = 'index.php?route=module/universal_import/process&<?php echo $token; ?>';
  }
  
  $.ajax({
		url: proc_url,
    type: 'POST',
		data: {import_label: $('input[name=import_label]').val()},
		dataType: 'json',
		success: function(data){
      if (data.success) {
        $('#importProgress .progress-bar').css('width',data.progress + '%').html(data.progress + ' %');
        
        $.each(data.processed, function(key, value) {
          $('.stat-'+key).html(value);
        });
        
        if (!pauseProcess && !data.finished) {
          processQueue(data.postproc);
        } else {
          $('#importProgress .progress-bar').removeClass('active');
          $('.startProcess').removeClass('btn-warning').addClass('btn-success').removeAttr('disabled');
          $('.startProcess').html('<i class="fa fa-play"></i> <?php echo $_language->get('text_resume_process'); ?>');
          
          if (data.finished) {
            $('.pauseProcess,.startProcess').hide();
          }
        }
        
        if (data.log.length) {
          $(data.log).each(function(i, item) {
            $('.process-log tbody').append('<tr class="row_'+item.status+'"><td>'+item.row+'</td><td class="status bg_'+item.status+'">'+item.title+'</td><td>'+item.msg+'</td></tr>');
          });
        }
        
        if (data.errors.length) {
          var ul = $('<ul>').appendTo('.obui-errors');
          $(data.errors).each(function(i, item) {
              ul.append(
                $(document.createElement('li')).text(item)
              );
          });
          
          $('.obui-errors').fadeIn();
        }
        
      } else if (data.error) {
        $('#profile-form .alert-warning span').html(data.error);
        $('#profile-form .alert-warning').fadeIn();
      }
		},
    error: function(data, e, error) {
      $('.startProcess').html('<i class="fa fa-play"></i> <?php echo $_language->get('text_resume_process'); ?>');
      $('#importProgress .progress-bar').removeClass('active');
      $('.pauseProcess').hide();
      $('.startProcess').show();
      $('#modal-alert').find('.modal-title').text('Error');
      $('#modal-alert').find('.modal-body').html(data.responseText);
      $('#modal-alert').modal('show');
		}
	});
}

$(document).ready(function() {
  var first_run = true;
  
  $('.startProcess').click(function() {
    $('.startProcess').hide();
    $('.process-log').show();
    $('.pauseProcess').show();
    $('#importProgress .progress-bar').addClass('active');
    
    if (first_run) {
      first_run = false;
      $('#importProgress .progress-bar').css('min-width', '2em').html('0 %');
    }
    
    pauseProcess = 0;
    processQueue(false);
  });
  
  $('.pauseProcess').click(function() {
    $('.pauseProcess').hide();
    $('.startProcess').show();
    $('.startProcess').removeClass('btn-success').addClass('btn-warning').attr('disabled','disabled');
    $('.startProcess').html('<i class="fa fa-gear fa-spin"></i> <?php echo $_language->get('text_pausing_process'); ?>');
    pauseProcess = 1;
  });
});
--></script>