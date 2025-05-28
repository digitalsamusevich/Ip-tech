<?php function rowSatusIcon($status) {
  switch ($status) {
    case 'inserted': $icon = 'plus'; break;
    case 'updated': $icon = 'pencil'; break;
    case 'deleted': $icon = 'times'; break;
    case 'disabled': $icon = 'eraser'; break;
    case 'skipped': $icon = 'chevron-right'; break;
    case 'error': $icon = 'exclamation-triangle'; break;
  }
  echo '<i class="fa fa-'.$icon.' '.$status.'"></i>';
} ?>
<div class="spacer"></div>


<div class="summaryShow">
    <?php if (!empty($alert_info)) { ?>
      <div class="alert alert-info"><i class="fa fa-info-circle"></i> <?php echo $alert_info; ?></div>
    <?php } ?>
    
    <?php if (!empty($errors)) { ?>
    <div class="alert alert-danger"> <i class="fa fa-exclamation-triangle"></i> Errors:
      <ul>
      <?php foreach ($errors as $err_msg) { ?>
      <li><?php echo $err_msg; ?></li>
      <?php } ?>
      </ul>
    </div>
  <?php } ?>
  
  <hr class="dotted"/>
</div>
  
<p>
  <b class="summaryShow"><?php echo $_language->get('text_simu_summary'); ?></b>
  <b class="simulationShow" style="display:none"><?php echo $_language->get('text_full_simu_summary'); ?></b>
  <?php unset($processed['processed']); foreach($processed as $status => $val) { ?>
    <span style="padding:0 60px;"><?php echo $_language->get('text_simu_'.$status); ?> <span class="badge <?php echo $status; ?> simu-stat-<?php echo $status; ?>"><?php echo $val; ?></span></span>
  <?php } ?>
</p>

<hr class="dotted"/>

<div class="summaryShow">

  <?php /*if (($last_row - $start_row) > 10) {*/ ?>
  <?php if (1) { ?>
  <div class="text-center form-inline">
    <button type="button" class="btn btn-default browsePreview" data-start="<?php echo $first_row; ?>" <?php echo ($start_row <= $first_row) ? 'disabled' : 'data-toggle="tooltip" title="'.$_language->get('text_nav_start').'"'; ?>><i class="fa fa-angle-double-left"></i></button>
    <button type="button" class="btn btn-default browsePreview" data-start="<?php echo (($start_row - 10) > $first_row) ? $start_row - 10 : $first_row; ?>" <?php echo ($start_row <= $first_row) ? 'disabled' : 'data-toggle="tooltip" title="'.$_language->get('text_nav_prev').'"'; ?>><i class="fa fa-angle-left"></i></button>
    <input type="text" class="form-control browsePreviewInput" name="browsePreviewInput" value="<?php echo $start_row; ?>" style="text-align:center"  data-toggle="tooltip" title="<?php echo $_language->get('text_nav_custom'); ?>"/>
    <button type="button" class="btn btn-default browsePreview" data-start="<?php echo $start_row + 10; ?>" <?php echo ($start_row >= $last_row - 9) ? 'disabled' : 'data-toggle="tooltip" title="'.$_language->get('text_nav_next').'"'; ?>><i class="fa fa-angle-right"></i></button>
    <button type="button" class="btn btn-default browsePreview" data-start="<?php echo $last_row - 9; ?>" <?php echo ($start_row >= $last_row - 9) ? 'disabled' : 'data-toggle="tooltip" title="'.$_language->get('text_nav_end').'"'; ?>><i class="fa fa-angle-double-right"></i></button>
  </div>

  <hr class="dotted"/>
  <?php } ?>

  <div role="tabpanel">
    <ul class="nav nav-tabs">
    <?php $f=1; foreach ($simulate as $row => &$simu) { ?>
      <li <?php if($f) {echo 'class="active"'; $f=0;}; ?>><a href="#tab-check-<?php echo $row; ?>" data-toggle="tab"><?php echo (($filetype == 'xml') ? $_language->get('text_row_xml') : $_language->get('text_row')) . ' ' . ($row); ?> <?php rowSatusIcon($simu['row_status']); ?></a></li>
    <?php } ?>
    </ul>
    <div class="tab-content">
      <?php $f=1; foreach ($simulate as $row => &$simu) { ?>
      <?php
       // unset not required data
       unset($simu['uiep_filter_to_category'], $simu['gkd_extra_fields'], $simu['gkd_extra_desc_fields']);
      ?>
      <div class="tab-pane<?php if($f) {echo ' active'; $f=0;}; ?>" id="tab-check-<?php echo $row; ?>">
        <table class="table table-bordered table-striped">
        <?php if (isset($simu['row_msg']) && !in_array($simu['row_status'], array('disabled', 'deleted', 'skipped'))) unset($simu['row_msg']); ?>
        <?php if (isset($simu['row_status'])) { ?>
          <thead>
          <tr>
            <th style="width:250px;"><?php echo $_language->get('entry_row_status'); ?></th>
            <th><?php rowSatusIcon($simu['row_status']); ?>&nbsp;&nbsp;<?php echo $_language->get('text_simu_'.$simu['row_status']); ?></th>
          </tr>
          </thead>
          <tbody>
             <?php if (!empty($preview_errors[$row])) { ?>
              <tr>
                <td><?php echo $_language->get('text_simu_error'); ?></td>
                <td>
                  <div class="alert alert-danger" style="margin-bottom:0">
                    <?php foreach ($preview_errors[$row] as $err_msg) { ?>
                    <i class="fa fa-exclamation-triangle"></i> <?php echo $err_msg; ?><br/>
                    <?php } ?>
                  </div>
                </td>
              </tr>
            <?php } ?>
            
            <?php if (in_array($simu['row_status'], array('deleted', 'skipped'))) { ?>
            <?php if (!empty($simu[$_POST['item_identifier']])) { ?>
            <tr>
              <td><b><?php echo $_language->get('entry_'.$_POST['item_identifier']); ?></b></td>
              <td><?php echo $simu[$_POST['item_identifier']]; ?></td>
            </tr>
            <?php } ?>
            <?php if (!empty($simu['row_msg'])) { ?>
            <tr>
              <td><b><?php echo $_language->get('text_message'); ?></b></td>
              <td><?php echo $simu['row_msg']; ?></td>
            </tr>
            <?php } ?>
            </tbody></table></div>
            <?php continue; }
            unset($simu['row_status']);
            ?>
        <?php } ?>
        <?php if (isset($simu['item_to_update'])) { ?>
        <tr>
          <td><b><?php echo $_language->get('text_item_to_update'); ?></b></td>
          <td><?php echo $simu['item_to_update']; ?></td>
        </tr>
        <?php unset($simu['item_to_update']); } ?>
        
        <?php foreach (array($type.'_seo_url') as $ms_ml_field) { ?>
        <?php if (isset($simu[$ms_ml_field])) { ?>
          <tr>
            <td><?php
              if (strpos($_language->get('entry_'.$ms_ml_field), 'entry_') === false) {
                echo $_language->get('entry_'.$ms_ml_field);
              } else {
                echo ucfirst(str_replace(array('entry_','_'), array('', ' '), $ms_ml_field));
              }
              ?>
            </td><td>
            <?php foreach ($simu[$ms_ml_field] as $store_id => $ml_values) { ?>
              <table style="float:left; margin-right: 50px">
              <tr><td><b><?php echo 'Store ' . $store_id; ?></b></td></tr>
              <?php foreach ($languages as $language) {
                if (isset($simu[$ms_ml_field][$store_id][$language['language_id']])) { ?>
                <tr><td><img src="<?php echo $language['image']; ?>" alt=""/>&nbsp;&nbsp;<?php echo $simu[$ms_ml_field][$store_id][$language['language_id']]; ?></td></tr>
              <?php }} ?>
              </table>
            <?php } ?>
            </td>
          </tr>
          <?php unset($simu[$ms_ml_field]); }} ?>
          
        <?php if (isset($simu[$type.'_description'])) { ?>
        <?php foreach ($simu[$type.'_description'] as $desc) { ?>
          <?php foreach ($desc as $key => $value) {
            $display = false;
            foreach ($languages as $language) {
              if (!empty($simu[$type.'_description'][$language['language_id']][$key])) $display = true;
            }
            if (!$display) continue;
            ?>
            <tr>
              <td><?php $label = str_replace('seo_keyword', 'keyword', $key);
                if (strpos($_language->get('entry_'.$label), 'entry_') === false) {
                  echo $_language->get('entry_'.$label);
                } else {
                  echo ucfirst(str_replace(array('entry_p_','entry_','_'), '', $label));
                }
                ?></td>
              <td><?php 
                  foreach ($languages as $language) {
                    if (!empty($simu[$type.'_description'][$language['language_id']][$key])) {
                      if (is_scalar($simu[$type.'_description'][$language['language_id']][$key])) {
                        $dots = (mb_strlen($simu[$type.'_description'][$language['language_id']][$key]) > 200) ? '...' : '';
                        echo '<img src="' . $language['image'] . '" alt=""/>&nbsp;&nbsp;' . mb_substr(strip_tags($simu[$type.'_description'][$language['language_id']][$key]), 0, 200) . $dots . '<br/>';
                      } else if (is_array($simu[$type.'_description'][$language['language_id']][$key])) {
                        echo '<img src="' . $language['image'] . '" alt=""/>&nbsp;&nbsp;' . implode(', ', $simu[$type.'_description'][$language['language_id']][$key]) . '<br/>';
                      }
                    }
                  }
                ?></td>
            </tr>
          <?php }break;} unset($simu[$type.'_description']);} ?>
          
        <?php if (isset($simu['seo_'.$type.'_description'])) { ?>
        <?php foreach ($simu['seo_'.$type.'_description'] as $storeId => $langDesc) {
          foreach ($langDesc as $desc) {
            foreach ($desc as $key => $value) {
            $display = false;
            foreach ($languages as $language) {
              if (!empty($simu['seo_'.$type.'_description'][$storeId][$language['language_id']][$key])) $display = true;
            }
            if (!$display) continue;
            ?>
            <tr>
              <td><?php $label = str_replace('seo_keyword', 'keyword', $key);
                if (strpos($_language->get('entry_'.$label), 'entry_') === false) {
                  echo $_language->get('entry_'.$label) . ' ('.$stores[$storeId].')';
                } else {
                  echo ucfirst(str_replace(array('entry_p_','entry_','_'), '', $label));
                }
                ?></td>
              <td><?php 
                  foreach ($languages as $language) {
                    if (!empty($simu['seo_'.$type.'_description'][$storeId][$language['language_id']][$key])) {
                      $dots = (mb_strlen($simu['seo_'.$type.'_description'][$storeId][$language['language_id']][$key]) > 200) ? '...' : '';
                      echo '<img src="' . $language['image'] . '" alt=""/>&nbsp;&nbsp;' . mb_substr(strip_tags($simu['seo_'.$type.'_description'][$storeId][$language['language_id']][$key]), 0, 200) . $dots . '<br/>';
                    }
                  }
                ?></td>
            </tr>
          <?php }break;}break;} unset($simu['seo_'.$type.'_description']);} ?>
          
          <?php foreach ($simu as $key => $value) { ?>
            <tr>
              <td><?php $label = str_replace(array('parent_id', '_p_', 'product_', 'class_id', 'status_id'), array('parent', '', '', 'class', 'status'), $key);
                //echo $_language->get('entry_'.$label);
                if (strpos($_language->get('entry_'.$label), 'entry_') === false) {
                  echo $_language->get('entry_'.$label);
                } else if (strpos($_language->get('entry_'.(str_replace('_id', '', $label))), 'entry_') === false) {
                  echo $_language->get('entry_'.(str_replace('_id', '', $label)));
                } else {
                  echo ucwords(str_replace('_', ' ', $label));
                }
              ?></td>
              <td><?php
                if (in_array($key, array('status', 'subtract', 'shipping'))) {
                  echo $value ? $_language->get('text_on') : $_language->get('text_off');
                } else if (in_array($key, array('image', 'product_image', 'product_group_image'))) {
                  foreach((array) $value as $val) {
                    if (strpos($val, 'http') === 0) {
                      echo '<img src="'.$val.'" alt="'.$val.'" title="'.$val.'" style="height:70px" class="img-thumbnail"/>&nbsp;&nbsp;';
                    } else if (strpos($val, 'ftp:') === 0) {
                      echo '<img src="'.$val.'" alt="Images from ftp cannot be previewed" title="Images from ftp cannot be previewed" style="height:70px" class="img-thumbnail"/>&nbsp;&nbsp;';
                    } else {
                      echo '<img src="'. HTTP_CATALOG . 'image/' . $val.'" alt="'.$val.'" title="'.$val.'" style="height:70px" class="img-thumbnail"/>&nbsp;&nbsp;';
                    }
                  }
                } else if (is_array($value)) {
                  foreach($value as $val) {
                    if (is_array($val)) {
                      foreach($val as $subval) {
                        if (is_array($subval)) {
                          foreach($subval as $subsubval) {
                            if (is_array($subsubval)) {
                              foreach($subsubval as $subsubsubval) {
                                echo $subsubsubval ? $subsubsubval . '<br/>' : '';
                              }
                            } else {
                              echo $subsubval ? $subsubval . '<br/>' : '';
                            }
                          }
                        } else {
                          echo $subval ? $subval . '<br/>' : '';
                        }
                      }
                    } else {
                      echo $val . '<br/>';
                    }
                  }
                } else {
                  echo $value;
                }
                ?></td>
            </tr>
          <?php } ?>
          </tbody>
          </table>
        </div>
      <?php } ?>
    </div>
  </div>

  <hr class="dotted"/>

</div>

<div class="simu-log simulationShow" style="display:none">
  <table class="table table-bordered">
    <thead>
      <tr>
        <th><?php echo $_language->get('text_row'); ?></th>
        <th><?php echo $_language->get('text_status'); ?></th>
        <th><?php echo $_language->get('text_message'); ?></th>
      </tr>
    </thead>
    <tbody>
    </tbody>
  </table>
</div>

<div class="spacer"></div>

<div>
  <button type="button" class="startSimuProcess btn btn-success btn-lg btn-block"><i class="fa fa-play"></i> <?php echo $_language->get('text_start_simu_process'); ?></button>
  <button type="button" class="pauseSimuProcess btn btn-default btn-lg btn-block" style="display:none"><i class="fa fa-refresh fa-pulse fa-spin"></i> <?php echo $_language->get('text_pause_simu_process'); ?></button>
</div>

<div class="spacer"></div>

<div id="simuProgress" class="progress">
  <div class="progress-bar progress-bar-striped progress-bar-success active"></div>
</div>

<div class="spacer"></div>

<div class="alert alert-danger obui-errors" style="display:none"></div>

<div class="spacer"></div>

<script type="text/javascript"><!--
/* Step 4 */
var pauseSimuProcess = 1;
function processSimuQueue(postproc) {
  if (postproc) {
    proc_url = 'index.php?route=module/universal_import/postproc&simu=1&<?php echo $token; ?>';
  } else {
    proc_url = 'index.php?route=module/universal_import/process&simu=1&<?php echo $token; ?>';
  }
  
  $.ajax({
		url: proc_url,
    type: 'POST',
		data: {},
		dataType: 'json',
		success: function(data) {
      if (data.success) {
        $('#simuProgress .progress-bar').css('width',data.progress + '%').html(data.progress + ' %');
        
        $.each(data.processed, function(key, value) {
          $('.simu-stat-'+key).html(value);
        });
        
        if (!pauseSimuProcess && !data.finished) {
          processSimuQueue(data.postproc);
        } else {
          $('#simuProgress .progress-bar').removeClass('active');
          $('.startSimuProcess').removeClass('btn-warning').addClass('btn-success').removeAttr('disabled');
          $('.startSimuProcess').html('<i class="fa fa-play"></i> <?php echo $_language->get('text_resume_process'); ?>');
          
          if (data.finished) {
            $('.pauseSimuProcess,.startSimuProcess').hide();
          }
        }
        
        if (data.log.length) {
          $(data.log).each(function(i, item) {
            $('.simu-log tbody').append('<tr class="row_'+item.status+'"><td>'+item.row+'</td><td class="status bg_'+item.status+'">'+item.title+'</td><td>'+item.msg+'</td></tr>');
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
        
      } else if(data.error) {
        $('#profile-form .alert-warning span').html(data.error);
        $('#profile-form .alert-warning').fadeIn();
      }
		},
    error: function(data, e, error) {
      $('.startSimuProcess').html('<i class="fa fa-play"></i> <?php echo $_language->get('text_resume_process'); ?>');
      $('#simuProgress .progress-bar').removeClass('active');
      $('.pauseSimuProcess').hide();
      $('.startSimuProcess').show();
      $('#modal-alert').find('.modal-title').text('Error');
      $('#modal-alert').find('.modal-body').html(data.responseText);
      $('#modal-alert').modal('show');
		}
	});
}

$(document).ready(function() {
  var simu_first_run = true;
  
  $('.startSimuProcess').click(function() {
    $('.summaryShow').hide();
    $('.simulationShow').show();
    $('.startSimuProcess').hide();
    $('.pauseSimuProcess').show();
    $('#simuProgress .progress-bar').addClass('active');
    
    if (simu_first_run) {
      simu_first_run = false;
      $('#simuProgress .progress-bar').css('min-width', '2em').html('0 %');
    }
    
    pauseSimuProcess = 0;
    processSimuQueue(false);
  });
  $('.pauseSimuProcess').click(function() {
    $('.pauseSimuProcess').hide();
    $('.startSimuProcess').show();
    $('.startSimuProcess').removeClass('btn-success').addClass('btn-warning').attr('disabled','disabled');
    $('.startSimuProcess').html('<i class="fa fa-gear fa-spin"></i> <?php echo $_language->get('text_pausing_process'); ?>');
    pauseSimuProcess = 1;
  });
});
--></script>

  <div class="pull-left" style="width:50%">
    <?php if ($type == 'product' || $type == 'product_update') { ?>
    <div class="row form-inline">
      <label class="col-sm-2 control-label"><span data-toggle="tooltip" title="<?php echo $_language->get('text_import_label_i'); ?>"><?php echo $_language->get('text_import_label'); ?></span></label>
      <div class="col-md-10">
        <input class="form-control" type="text" name="import_label" value="<?php echo isset($profile['import_label']) ? $profile['import_label'] : ''; ?>"/>
      </div>
    </div>
    <?php } ?>
  </div>
<div class="pull-right">
  <button type="button" class="btn btn-default cancel" data-step="4"><i class="fa fa-reply"></i> <?php echo $_language->get('text_previous_step'); ?></button>
  <button type="button" id="save_preset" class="btn btn-primary" data-toggle="modal" data-target="#modal-profile"><i class="fa fa-save"></i> <?php echo $_language->get('text_save_profile'); ?></button>
  <button type="button" class="btn btn-success submit" data-step="4"><i class="fa fa-check"></i> <?php echo $_language->get('text_next_step'); ?></button>
</div>

<div class="spacer"></div>