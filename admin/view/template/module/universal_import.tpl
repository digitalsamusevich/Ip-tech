<?php function imageRepeat($id, $_language, $val) { $id.= '_repeat'; ?>
  <select name="univimport_theme[<?php echo $id; ?>]" class="form-control changeReload">
    <option value="" <?php if($val[$id] == '') echo 'selected="selected"'; ?>><?php echo $_language->get('text_repeat'); ?></option>
    <option value="repeat-x" <?php if($val[$id] == 'repeat-x') echo 'selected="selected"'; ?>><?php echo $_language->get('text_repeat-x'); ?></option>
    <option value="repeat-y" <?php if($val[$id] == 'repeat-y') echo 'selected="selected"'; ?>><?php echo $_language->get('text_repeat-y'); ?></option>
    <option value="no-repeat" <?php if($val[$id] == 'no-repeat') echo 'selected="selected"'; ?>><?php echo $_language->get('text_no-repeat'); ?></option>
    <option value="top center no-repeat" <?php if($val[$id] == 'top center no-repeat') echo 'selected="selected"'; ?>><?php echo $_language->get('text_no-repeat_center'); ?></option>
  </select>
<?php } ?>
<?php $gkhtab = $gkhdiv = 0; ?>
<?php echo $header; ?><?php echo $column_left; ?>
<div id="content">
<?php if(!empty($style_v15)) { ?><style scoped><?php echo $style_v15; ?></style><?php } ?>
<input type="hidden" name="no-image" value="0" />
<div id="modal-alert" class="modal fade bs-example-modal-lg" tabindex="-1" role="dialog" aria-labelledby=""><div class="modal-dialog modal-lg"><div class="modal-content"><div class="modal-header"> <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">×</span></button> <h4 class="modal-title"></h4> </div><div class="modal-body"></div></div></div></div>
<div id="modal-info" class="modal <?php if (version_compare(VERSION, '2', '>=')) echo ' fade'; ?>" tabindex="-1" role="dialog" aria-hidden="true"></div>
<div class="modal fade" id="modal-profile" tabindex="-1" role="dialog" aria-hidden="true">
  <div class="modal-dialog">
    <div class="modal-content">
      <div class="modal-header">
        <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
        <h4 class="modal-title" id="myModalLabel"><?php echo $_language->get('text_save_profile'); ?></h4>
      </div>
      <div class="modal-body form-horizontal">
        <form id="profile-form">
          <div class="alert alert-success" style="display:none"><i class="fa fa-check-circle"></i> <span></span></div>
          <div class="alert alert-warning" style="display:none"><i class="fa fa-exclamation-circle"></i> <span></span></div>
          <p><?php echo $_language->get('text_save_profile_i'); ?></p>
          <div class="form-group">
            <div class="col-sm-12">
              <select class="form-control" name="save_profile">
                <option value=""><?php echo $_language->get('text_new_profile'); ?></option>
                <?php foreach ($profiles as $item) { ?>
                  <option value="<?php echo $item['name']; ?>" class="type-hide show-<?php echo $item['type']; ?>"><?php echo $item['name']; ?></option>
                <?php } ?>
              </select>
            </div>
          </div>
          <div class="form-group">
            <div class="col-sm-12">
              <input name="profile_name" class="form-control" placeholder="<?php echo $_language->get('text_profile_name'); ?>"/>
            </div>
          </div>
        </form>
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-default" data-dismiss="modal"><?php echo $_language->get('text_close'); ?></button>
        <button type="button" class="btn btn-primary" id="saveProfile"><?php echo $_language->get('button_save'); ?></button>
      </div>
    </div>
  </div>
</div>
<div class="modal fade" id="modal-profile-export" tabindex="-1" role="dialog" aria-hidden="true">
  <div class="modal-dialog">
    <div class="modal-content">
      <div class="modal-header">
        <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
        <h4 class="modal-title" id="myModalLabel"><?php echo $_language->get('text_save_profile'); ?></h4>
      </div>
      <div class="modal-body form-horizontal">
        <form id="export-profile-form">
          <div class="alert alert-success" style="display:none"><i class="fa fa-check-circle"></i> <span></span></div>
          <div class="alert alert-warning" style="display:none"><i class="fa fa-exclamation-circle"></i> <span></span></div>
          <p><?php echo $_language->get('text_save_profile_i'); ?></p>
          <div class="form-group">
            <div class="col-sm-12">
              <select class="form-control" name="save_profile">
                <option value=""><?php echo $_language->get('text_new_profile'); ?></option>
                <?php foreach ($export_profiles as $item) { ?>
                  <option value="<?php echo $item['name']; ?>" class="export-type-hide export-type-show-<?php echo $item['type']; ?>"><?php echo $item['name']; ?></option>
                <?php } ?>
              </select>
            </div>
          </div>
          <div class="form-group">
            <div class="col-sm-12">
              <input name="profile_name" class="form-control" placeholder="<?php echo $_language->get('text_profile_name'); ?>"/>
            </div>
          </div>
        </form>
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-default" data-dismiss="modal"><?php echo $_language->get('text_close'); ?></button>
        <button type="button" class="btn btn-primary" id="saveExportProfile"><?php echo $_language->get('button_save'); ?></button>
      </div>
    </div>
  </div>
</div>

			<ul class="breadcrumb">
				<?php foreach ($breadcrumbs as $breadcrumb) { ?>
					<li><a href="<?php echo $breadcrumb['href']; ?>"><?php echo $breadcrumb['text']; ?></a></li>
				<?php } ?>
			</ul>

  <div class="<?php if(version_compare(VERSION, '2', '>=')) echo 'container-fluid'; ?>">
	<?php if (isset($success) && $success) { ?><div class="alert alert-success success"><i class="fa fa-check-circle"></i> <?php echo $success; ?> <button type="button" class="close" data-dismiss="alert">&times;</button></div><script type="text/javascript">setTimeout("$('.alert-success').slideUp();",5000);</script><?php } ?>
	<?php if (isset($info) && $info) { ?><div class="alert alert-info"><i class="fa fa-info-circle"></i> <?php echo $info; ?> <button type="button" class="close" data-dismiss="alert">&times;</button></div><?php } ?>
	<?php if (isset($error) && $error) { ?><div class="alert alert-danger"><i class="fa fa-exclamation-circle"></i> <?php echo $error; ?> <button type="button" class="close" data-dismiss="alert">&times;</button></div><?php } ?>
    <?php if (isset($warning) && $warning) { ?><div class="alert alert-danger"><i class="fa fa-exclamation-circle"></i> <?php echo $warning; ?> <button type="button" class="close" data-dismiss="alert">&times;</button></div><?php } ?>
<div class="panel panel-default">
	<div class="panel-heading module-heading">
    <div class="pull-right saveBtns" style="display:none">
      <button type="submit" form="form" data-toggle="tooltip" title="<?php echo $button_save; ?>" class="btn btn-primary"><i class="fa fa-save"></i> <?php echo $button_save; ?></button>
      <a href="<?php echo $cancel; ?>" data-toggle="tooltip" title="<?php echo $button_cancel; ?>" class="btn btn-default"><i class="fa fa-reply"></i> <?php echo $button_cancel; ?></a>
    </div>
		<h3 class="panel-title"><img src="<?php echo $_img_path; ?>icon_big.png" alt="" style="vertical-align:top;"/> <?php echo $heading_title; ?></h3>
	</div>
	<div class="content panel-body">
  <!--<div id="stores" class="form-inline" <?php if (version_compare(VERSION, '2', '>=') && 0) echo 'class="v2"'; ?>>
		<?php echo $_language->get('text_store_select'); ?>
		<select name="store" class="form-control input-sm">
			<?php foreach ($stores as $store) { ?>
			<?php if ($store_id == $store['store_id']) { ?>
			<option value="<?php echo $store['store_id']; ?>" selected="selected"><?php echo $store['name']; ?></option>
			<?php } else { ?>
			<option value="<?php echo $store['store_id']; ?>"><?php echo $store['name']; ?></option>
			<?php } ?>
			<?php } ?>
		</select>
	</div>-->
		<ul class="nav nav-tabs mainMenu">
    	<li class="active"><a href="#tab-0" data-toggle="tab"><i class="fa fa-download"></i><?php echo $_language->get('text_tab_0'); ?></a></li>
			<li><a href="#tab-1" data-toggle="tab"><i class="fa fa-upload"></i><?php echo $_language->get('text_tab_1'); ?></a></li>
			<li><a href="#tab-2" data-toggle="tab"><i class="fa fa-gear"></i><?php echo $_language->get('text_tab_2'); ?></a></li>
			<!--<li><a href="#tab-3" data-toggle="tab"><i class="fa fa-pencil-square-o"></i><?php echo $_language->get('text_tab_3'); ?></a></li>-->
      <li><a href="#tab-cron" data-toggle="pill"><i class="fa fa-terminal"></i> <?php echo $_language->get('tab_option_2'); ?></a></li>
			<li><a href="#tab-about" data-toggle="tab"><i class="fa fa-info"></i><?php echo $_language->get('text_tab_about'); ?></a></li>
		</ul>
    
		<div class="tab-content container-fluid">
      <div class="tab-pane active" id="tab-0">
        <form id="import-form" class="form-horizontal">
          <div id="importAccordion" role="tablist" aria-multiselectable="true">
            <div class="panel-heading" role="tab" data-toggle="collapse" data-parent="#importAccordion" href="#importStep1" aria-expanded="true">
              <h4 class="panel-title"><?php echo $_language->get('text_import_step1'); ?></h4>
            </div>
            <div id="importStep1" class="collapse in clearfix" role="tabpanel">
              <div class="spacer"></div>
              <div class="col-md-offset-2">
                <div class="gkdwidget gkdwidget-color-blueLight clearfix">
                  <header role="heading">
                    <i class="icon fa fa-info-circle fa-2x pull-left" title="<?php echo $_language->get('info_help'); ?>"></i>
                    <ul class="nav nav-tabs pull-left in">
                      <li><a data-toggle="tab" href="#gkhelp<?php echo $gkhtab++; ?>"><span class="hidden-mobile hidden-tablet"><?php echo $_language->get('info_data_source_title'); ?></span></a></li>
                      <li><a data-toggle="tab" href="#gkhelp<?php echo $gkhtab++; ?>"><span class="hidden-mobile hidden-tablet"><?php echo $_language->get('info_api_title'); ?></span></a></li>
                      <li><a data-toggle="tab" href="#gkhelp<?php echo $gkhtab++; ?>"><span class="hidden-mobile hidden-tablet"><?php echo $_language->get('info_profiles_title'); ?></span></a></li>
                    </ul>
                  </header>
                  <div class="gkdwidget-container" style="display:none">
                    <div class="tab-content">
                      <div class="tab-pane" id="gkhelp<?php echo $gkhdiv++; ?>"><?php echo $_language->get('info_data_source'); ?></div>
                      <div class="tab-pane" id="gkhelp<?php echo $gkhdiv++; ?>"><?php echo $_language->get('info_api'); ?></div>
                      <div class="tab-pane" id="gkhelp<?php echo $gkhdiv++; ?>"><?php echo $_language->get('info_profiles'); ?></div>
                    </div>
                  </div>
                </div>
              </div>
              <div class="form-group">
                <label class="col-sm-2 control-label"><span data-toggle="tooltip" title="<?php echo $_language->get('entry_type_i'); ?>"><?php echo $_language->get('entry_type'); ?></span></label>
                <div class="col-sm-4">
                  <select name="import_type" class="form-control big-select">
                   <?php $currentType = '';
                      foreach ($import_types as $import_type) {
                        if ($currentType == (strstr($import_type, '_', true) ? strstr($import_type, '_', true) : $import_type)) { ?>
                          <option value="<?php echo $import_type; ?>"><?php echo $_language->get('text_type_'.$import_type); ?></option>
                          <?php } else { ?>
                            <option value="<?php echo $import_type; ?>"><?php echo $_language->get('text_type_'.$import_type); ?></option>
                          <?php }
                          $currentType = strstr($import_type, '_', true) ? strstr($import_type, '_', true) : $import_type;
                        }
                    ?>
                   <?php /*
                    <?php $currentType = '';
                      foreach ($import_types as $import_type) {
                        if ($currentType == (strstr($import_type, '_', true) ? strstr($import_type, '_', true) : $import_type)) { ?>
                          <option value="<?php echo $import_type; ?>"><?php echo $_language->get('text_type_'.$import_type); ?></option>
                          <?php } else { ?>
                          <?php if ($currentType) { ?></optgroup><?php } ?>
                          <optgroup label="<?php echo $_language->get('text_type_'.$import_type); ?>">
                            <option value="<?php echo $import_type; ?>"><?php echo $_language->get('text_type_'.$import_type); ?></option>
                          <?php }
                          $currentType = strstr($import_type, '_', true) ? strstr($import_type, '_', true) : $import_type;
                        }
                    ?>
                    </optgroup>
                    */ ?>
                  </select>
                </div>
                <label class="col-sm-2 control-label"><span data-toggle="tooltip" title="<?php echo $_language->get('entry_profile_i'); ?>"><?php echo $_language->get('entry_profile'); ?></span></label>
                <div class="col-sm-4" style="padding-right:57px">
                  <button type="button" data-toggle="tooltip" class="btn btn-danger deleteProfile" style="position:absolute; right:15px;" title="<?php echo $_language->get('text_delete_profile'); ?> "disabled="disabled"><i class="fa fa-minus-circle"></i></button>
                  <select class="form-control" name="profile">
                    <option value=""><?php echo $_language->get('text_select_profile'); ?></option>
                    <?php foreach ($profiles as $item) { ?>
                      <option value="<?php echo $item['name']; ?>" class="type-hide show-<?php echo $item['type']; ?>"><?php echo $item['name']; ?></option>
                    <?php } ?>
                  </select>
                </div>
              </div>
              
              <hr class="dotted"/>
              
              <div class="form-group">
                <label class="col-sm-2 control-label"><span data-toggle="tooltip" title="<?php echo $_language->get('entry_file_i'); ?>"><?php echo $_language->get('entry_file'); ?></span></label>
                <div class="col-sm-6">
                  <select name="import_source" class="form-control">
                    <?php foreach (array('upload', 'url', 'api', 'ftp', 'path') as $file_source) { ?>
                    <option value="<?php echo $file_source; ?>"><?php echo $_language->get('text_source_'.$file_source); ?></option>
                    <?php } ?>
                  </select>
                </div>
                <div class="col-sm-2">
                  <select name="import_compression" class="form-control">
                    <option value=""><?php echo $_language->get('text_compression_auto'); ?></option>
                    <?php foreach (array('gz', 'zip', 'bz2') as $file_compression) { ?>
                    <option value="<?php echo $file_compression; ?>"><?php echo strtoupper($file_compression); ?></option>
                    <?php } ?>
                  </select>
                </div>
                <div class="col-sm-2">
                  <select name="import_extension" class="form-control">
                    <option value=""><?php echo $_language->get('text_extension_auto'); ?></option>
                    <?php foreach ($import_extensions as $file_extension) { ?>
                    <option value="<?php echo $file_extension; ?>"><?php echo strtoupper($file_extension); ?></option>
                    <?php } ?>
                    <option value="sql" style="display:none">SQL</option>
                  </select>
                </div>
              </div>
              <?php if ($demo_mode) { ?>
              <div class="alert alert-warning col-md-offset-2"><i class="fa fa-exclamation-circle"></i> <?php echo $_language->get('warning_demo_file_limitation'); ?></div>
              <?php } ?>
              <div id="alertFileUpload" class="alert alert-danger col-md-offset-2" style="display:none"><i class="fa fa-exclamation-circle"></i> <span></span></div>
              <div id="importSource"></div>
              
              <?php if ($demo_mode) { ?>
              <div class="form-group" style="background:#c0d7d7; padding:10px 0;">
                <label class="col-sm-2 control-label"><span data-toggle="tooltip" title="<?php echo $_language->get('entry_demo_file_i'); ?>"><?php echo $_language->get('entry_demo_file'); ?></span></label>
                <div class="col-sm-10">
                  <select name="demo_file" class="form-control big-select">
                    <option></option>
                    <?php foreach (array('products','categories','informations','manufacturers','customers') as $demo_type) { ?>
                    <option value="<?php echo $demo_type.'.csv'; ?>"><?php echo $demo_type.'.csv'; ?></option>
                    <?php } ?>
                  </select>
                </div>
              </div>
              <?php } ?>
              
              
              <?php if (!empty($import_transformers)) { ?>
              <div class="form-group" style="border:0">
                <label class="col-sm-2 control-label"><span data-toggle="tooltip" title="<?php echo $_language->get('entry_preprocessor_i'); ?>"><?php echo $_language->get('entry_preprocessor'); ?></span></label>
                <div class="col-sm-10">
                  <select name="import_transformer" class="form-control">
                    <option value=""><?php echo $_language->get('text_none'); ?></option>
                    <?php foreach ($import_transformers as $import_transformer) { ?>
                    <option value="<?php echo $import_transformer; ?>"><?php echo $import_transformer; ?></option>
                    <?php } ?>
                  </select>
                </div>
              </div>
              <?php } ?>
              
              <hr class="dotted"/>
              
              <div class="pull-right">
                <button type="button" class="btn btn-success submit" disabled="disabled" data-step="1"><i class="fa fa-check"></i> <?php echo $_language->get('text_next_step'); ?></button>
              </div>
              <div class="spacer"></div>
            </div>
            
            <div class="panel-heading" role="tab" data-toggle="collapse" data-parent="#importAccordion" href="#importStep2">
              <h4 class="panel-title"><?php echo $_language->get('text_import_step2'); ?></h4>
            </div>
            <div id="importStep2" class="collapse" role="tabpanel"></div>
            
            <div class="panel-heading" role="tab" data-toggle="collapse" data-parent="#importAccordion" href="#importStep3">
              <h4 class="panel-title"><?php echo $_language->get('text_import_step3'); ?></h4>
            </div>
            <div id="importStep3" class="collapse" role="tabpanel"></div>
            
            <div class="panel-heading" role="tab" data-toggle="collapse" data-parent="#importAccordion" href="#importStep4">
              <h4 class="panel-title"><?php echo $_language->get('text_import_step4'); ?></h4>
            </div>
            <div id="importStep4" class="collapse" role="tabpanel"></div>
            
            <div class="panel-heading" role="tab" data-toggle="collapse" data-parent="#importAccordion" href="#importStep5">
              <h4 class="panel-title"><?php echo $_language->get('text_import_step5'); ?></h4>
            </div>
            <div id="importStep5" class="collapse" role="tabpanel"></div>
            
          </div>
          </form>
        </div>
        <div class="tab-pane clearfix" id="tab-1">
          <form id="export-form">
            <div class=" well well-blue clearfix">
              <div class="row">
                <div class="form-group form-horizontal">
              <label class="col-sm-2 control-label"><span data-toggle="tooltip" title="<?php echo $_language->get('entry_export_type_i'); ?>"><?php echo $_language->get('entry_export_type'); ?></span></label>
              <div class="col-sm-4">
                <select name="export_type" class="form-control big-select">
                  <?php $i=1; foreach ($export_types as $export_type) { ?>
                  <option value="<?php echo $export_type; ?>"><?php echo $_language->get('text_type_'.$export_type); ?></option>
                  <?php } ?>
                </select>
              </div>
                  <label class="col-sm-2 control-label"><span data-toggle="tooltip" title="<?php echo $_language->get('entry_profile_export_i'); ?>"><?php echo $_language->get('entry_profile'); ?></span></label>
                  <div class="col-sm-4" style="padding-right:57px">
                    <button type="button" data-toggle="tooltip" class="btn btn-danger deleteProfileExport" style="position:absolute; right:15px;" title="<?php echo $_language->get('text_delete_profile'); ?> "disabled="disabled"><i class="fa fa-minus-circle"></i></button>
                    <select class="form-control" name="export_profile">
                      <option value=""><?php echo $_language->get('text_select_profile'); ?></option>
                      <?php foreach ($export_profiles as $item) { ?>
                        <option value="<?php echo $item['name']; ?>" class="export-type-hide export-type-show-<?php echo $item['type']; ?>"><?php echo $item['name']; ?></option>
                      <?php } ?>
                    </select>
                  </div>
                </div>
              </div>
              <div class="row">
                <div class="form-group form-horizontal">
              <label class="col-sm-2 control-label"><span data-toggle="tooltip" title="<?php echo $_language->get('entry_export_format_i'); ?>"><?php echo $_language->get('entry_export_format'); ?></span></label>
              <div class="col-sm-4">
                <select name="export_format" class="form-control big-select">
                  <?php foreach ($export_extensions as $file_format) { ?>
                  <option value="<?php echo $file_format; ?>"><?php echo $_language->get('text_format_'.$file_format); ?></option>
                  <?php } ?>
                  <option value="sql" style="display:none">SQL</option>
                </select>
              </div>
                  <div class="col-sm-2"></div>
                  <div class="col-sm-4">
                    <button type="button" id="save_preset_export" class="btn btn-primary btn-block" data-toggle="modal" data-target="#modal-profile-export"><i class="fa fa-save"></i> <?php echo $_language->get('text_save_profile'); ?></button>
                  </div>
                </div>
              </div>
            </div>
            
            <hr class="dotted"/>
    
            <div id="exportContainer"></div>
            
            <div>
              <button type="button" class="startExportProcess btn btn-success btn-lg btn-block"><i class="fa fa-play"></i> <?php echo $_language->get('text_start_export'); ?></button>
              <button type="button" class="pauseExportProcess btn btn-default btn-lg btn-block" style="display:none"><i class="fa fa-gear fa-spin"></i> <?php echo $_language->get('text_pause_export'); ?></button>
            </div>

            <div class="spacer"></div>
            
            <div id="exportProgress" class="progress" style="display:none">
              <div class="progress-bar progress-bar-striped progress-bar-success active"></div>
            </div>
            
          </form>
        </div>
    <div class="tab-pane" id="tab-2">
      <form action="<?php echo $action; ?>" method="post" enctype="multipart/form-data" id="form" class="form-horizontal">
        <ul class="nav nav-pills nav-stacked col-md-2">
          <li class="active"><a href="#tab-option-3" data-toggle="pill"><i class="fa fa-cog"></i> <?php echo $_language->get('tab_option_3'); ?></a></li>
          <li><a href="#tab-option-1" data-toggle="pill"><i class="fa fa-bolt"></i> <?php echo $_language->get('tab_option_1'); ?></a></li>
          <!--li><a href="#tab-option-2" data-toggle="pill"><i class="fa fa-terminal"></i> <?php echo $_language->get('tab_option_2'); ?></a></li-->
        </ul>
        <div class="tab-content col-md-10">
          <div class="tab-pane" id="tab-option-1">
            <div class="well">
              <?php echo $_language->get('batch_import_i'); ?>
            </div>
            <div class="form-group">
              <label class="col-sm-2 control-label"><?php echo $_language->get('entry_batch_import'); ?></label>
              <div class="col-sm-10">
                <input type="text" class="form-control" name="<?php echo $prefix; ?>batch_imp" value="<?php echo ${$prefix.'batch_imp'}; ?>" placeholder="200"/>
              </div>
            </div>
            <div class="form-group">
              <label class="col-sm-2 control-label"><?php echo $_language->get('entry_batch_export'); ?></label>
              <div class="col-sm-10">
                <input type="text" class="form-control" name="<?php echo $prefix; ?>batch_exp" value="<?php echo ${$prefix.'batch_exp'}; ?>" placeholder="200"/>
              </div>
            </div>
            <div class="form-group">
              <label class="col-sm-2 control-label"><?php echo $_language->get('entry_sleep'); ?></label>
              <div class="col-sm-10">
                <input type="text" class="form-control" name="<?php echo $prefix; ?>sleep" value="<?php echo ${$prefix.'sleep'}; ?>" placeholder="0"/>
              </div>
            </div>
            <div class="spacer"></div>
          </div>
          <div class="tab-pane active" id="tab-option-3">
            <div class="well">
              <h4><?php echo $_language->get('tab_option_3'); ?></h4>
              <p><?php echo $_language->get('default_label_i'); ?></p>
            </div>
            <div class="form-group">
              <label class="col-sm-2 control-label"><?php echo $_language->get('entry_default_import_label'); ?></label>
              <div class="col-sm-10">
                <input type="text" class="form-control" name="<?php echo $prefix; ?>default_label" value="<?php echo ${$prefix.'default_label'}; ?>" placeholder="[profile]-[year]-[month]-[day]"/>
              </div>
            </div>
            <div class="spacer"></div>
          </div>
        </div>
      </form>
		</div>
    <div class="tab-pane" id="tab-cron">
      <form id="formCron">
      <div class="well">
        <h4><?php echo $_language->get('tab_option_2'); ?></h4>
        <p><?php echo $_language->get('cron_jobs_i'); ?></p>
      </div>
      <ul class="nav nav-tabs">
        <li class="active"><a href="#tab-cron-2" data-toggle="pill"><i class="fa fa-download"></i> <?php echo $_language->get('text_tab_cron_import'); ?></a></li>
        <li><a href="#tab-cron-3" data-toggle="pill"><i class="fa fa-upload"></i> <?php echo $_language->get('text_tab_cron_export'); ?></a></li>
        <li><a href="#tab-cron-1" data-toggle="pill"><i class="fa fa-cogs"></i> <?php echo $_language->get('text_tab_cron_config'); ?></a></li>
        <li><a href="#tab-cron-4" data-toggle="pill"><i class="fa fa-file-text-o"></i> <?php echo $_language->get('text_tab_cron_report'); ?></a></li>
      </ul>
      <div class="tab-content form-horizontal">
        <div class="tab-pane" id="tab-cron-1">
          <div class="form-group">
            <label class="col-sm-2 control-label"><span data-toggle="tooltip" title="<?php echo $_language->get('entry_cron_key_i'); ?>"><?php echo $_language->get('entry_cron_key'); ?></span></label>
            <div class="col-sm-10">
              <input class="form-control" type="text" name="<?php echo $prefix; ?>cron_key" value="<?php echo (!empty(${$prefix.'cron_key'})) ? ${$prefix.'cron_key'} : 'cron_secure_key'; ?>"/>
            </div>
          </div>
          <div class="form-group">
            <label class="col-sm-2 control-label"><span data-toggle="tooltip" title="<?php echo $_language->get('entry_cron_log_i'); ?>"><?php echo $_language->get('entry_cron_log'); ?></span></label>
            <div class="col-sm-10">
              <select name="<?php echo $prefix; ?>cron_log" id="<?php echo $prefix; ?>cron_log" class="form-control">
                <?php foreach (array('all', 'error', 'off') as $log_type) { ?>
                <option value="<?php echo $log_type; ?>"<?php echo (!empty(${$prefix.'cron_log'}) && ${$prefix.'cron_log'} == $log_type) ? ' selected="selected"' : ''; ?>><?php echo $_language->get('text_log_'.$log_type); ?></option>
                <?php } ?>
              </select>
            </div>
          </div>
          <div class="form-group">
            <label class="col-sm-2 control-label"><span data-toggle="tooltip" title="<?php echo $_language->get('entry_report_email_i'); ?>"><?php echo $_language->get('entry_report_email'); ?></span></label>
            <div class="col-sm-10">
              <input class="switch" type="radio" name="<?php echo $prefix; ?>report_email" value="1" data-label="<?php echo $_language->get('text_yes'); ?>" <?php echo (!empty(${$prefix.'report_email'})) ? ' checked' : ''; ?>/>
              <input class="switch" type="radio" name="<?php echo $prefix; ?>report_email" value="0" data-label="<?php echo $_language->get('text_no'); ?>"<?php echo (empty(${$prefix.'report_email'})) ? ' checked' : ''; ?>/>
            </div>
          </div>
        </div>
        <div class="tab-pane active" id="tab-cron-2">
          <div class="form-group">
            <label class="col-sm-2 control-label"><?php echo $_language->get('entry_type'); ?></label>
            <div class="col-sm-4">
              <select class="importCronTypeSelect form-control big-select">
                <?php foreach ($import_types as $import_type) { ?>
                <option value="<?php echo $import_type; ?>"><?php echo $_language->get('text_type_'.$import_type); ?></option>
                <?php } ?>
              </select>
            </div>
            <label class="col-sm-2 control-label"><?php echo $_language->get('entry_profile'); ?></label>
            <div class="col-sm-4" style="padding-right:57px">
              <select class="importCronProfileSelect form-control">
                <option value=""><?php echo $_language->get('text_select_profile'); ?></option>
                <?php foreach ($profiles as $item) { ?>
                  <option value="<?php echo $item['name']; ?>" class="importCronTypeHide importCronTypeShow-<?php echo $item['type']; ?>"><?php echo $item['name']; ?></option>
                <?php } ?>
              </select>
            </div>
          </div>
          <div class="form-group">
            <label class="col-sm-2 control-label"><span data-toggle="tooltip" title="<?php echo $_language->get('entry_cron_command_i'); ?>"><?php echo $_language->get('entry_cron_command'); ?></span></label>
            <div class="col-sm-10">
              <div class="alert alert-info importCronQuoteAlert" <?php echo (!empty(${$prefix.'cron_key'}) && strpos(${$prefix.'cron_key'}, ' ') !== false) ? '' : 'style="display:none"'; ?>><i class="fa fa-exclamation-circle"></i> <span><?php echo $_language->get('text_cron_command_format'); ?></span></div>
              <div class="gkdCode gkdCodeBig"><?php echo !$demo_mode ? PHP_BINDIR . '/php ' . str_replace('system/', '', DIR_SYSTEM) : 'php /[path_to_your_www]/'; ?>univ_import_cron.php <?php echo (!empty(${$prefix.'cron_key'}) && strpos(${$prefix.'cron_key'}, ' ') !== false) ? '"' : ''; ?>k=<b><?php echo (!empty(${$prefix.'cron_key'})) ? ${$prefix.'cron_key'} : 'cron_secure_key'; ?></b><?php echo (!empty(${$prefix.'cron_key'}) && strpos(${$prefix.'cron_key'}, ' ') !== false) ? '"' : ''; ?> type=<b class="importCronType">product</b> <span class="importProfileQuote">"</span>profile=<b class="importCronProfile">Profile_Name</b><span class="importProfileQuote">"</span></div>
            </div>
          </div>
          <div class="form-group">
            <label class="col-sm-2 control-label"><span data-toggle="tooltip" title="<?php echo $_language->get('entry_cron_params_i'); ?>"><?php echo $_language->get('entry_cron_params'); ?></span></label>
            <div class="col-sm-10">
              <?php echo $_language->get('text_cron_label'); ?>
              <div class="gkdCode">label=<b>cron_import</b></div>
              <?php echo $_language->get('text_cron_import_email'); ?>
              <div class="gkdCode">email=<b>mail@yourwebsite.com</b></div>
            </div>
          </div>
        </div>
        <div class="tab-pane" id="tab-cron-3">
          <div class="form-group">
            <label class="col-sm-2 control-label"><?php echo $_language->get('entry_export_type'); ?></label>
            <div class="col-sm-4">
              <select class="exportCronTypeSelect form-control big-select">
                <?php foreach ($export_types as $export_type) { ?>
                <option value="<?php echo $export_type; ?>"><?php echo $_language->get('text_type_'.$export_type); ?></option>
                <?php } ?>
              </select>
            </div>
            <label class="col-sm-2 control-label"><?php echo $_language->get('entry_profile'); ?></label>
            <div class="col-sm-4" style="padding-right:57px">
              <select class="exportCronProfileSelect form-control">
                <option value=""><?php echo $_language->get('text_select_profile'); ?></option>
                <?php foreach ($export_profiles as $item) { ?>
                  <option value="<?php echo $item['name']; ?>" class="exportCronTypeHide exportCronTypeShow-<?php echo $item['type']; ?>"><?php echo $item['name']; ?></option>
                <?php } ?>
              </select>
            </div>
          </div>
          <div class="form-group">
            <label class="col-sm-2 control-label"><span data-toggle="tooltip" title="<?php echo $_language->get('entry_cron_command_i'); ?>"><?php echo $_language->get('entry_cron_command'); ?></span></label>
            <div class="col-sm-10">
              <div class="alert alert-info exportCronQuoteAlert" <?php echo (!empty(${$prefix.'cron_key'}) && strpos(${$prefix.'cron_key'}, ' ') !== false) ? '' : 'style="display:none"'; ?>><i class="fa fa-exclamation-circle"></i> <span><?php echo $_language->get('text_cron_command_format'); ?></span></div>
              <div class="gkdCode gkdCodeBig"><?php echo !$demo_mode ? PHP_BINDIR . '/php ' . str_replace('system/', '', DIR_SYSTEM) : 'php /[path_to_your_www]/'; ?>univ_import_cron.php <?php echo (!empty(${$prefix.'cron_key'}) && strpos(${$prefix.'cron_key'}, ' ') !== false) ? '"' : ''; ?>k=<b><?php echo (!empty(${$prefix.'cron_key'})) ? ${$prefix.'cron_key'} : 'cron_secure_key'; ?></b><?php echo (!empty(${$prefix.'cron_key'}) && strpos(${$prefix.'cron_key'}, ' ') !== false) ? '"' : ''; ?> export=<b>1</b> type=<b class="exportCronType">product</b> <span class="exportProfileQuote">"</span>profile=<b class="exportCronProfile">Profile_Name</b><span class="exportProfileQuote">"</span></div>
            </div>
          </div>
          <div class="form-group">
            <label class="col-sm-2 control-label"><span data-toggle="tooltip" title="<?php echo $_language->get('entry_cron_params_i'); ?>"><?php echo $_language->get('entry_cron_params'); ?></span></label>
            <div class="col-sm-10">
              <?php echo $_language->get('text_cron_export_filename'); ?>
              <div class="gkdCode">filename=<b>my_export.csv</b></div>
              Folder (choose the export folder name, will be at root of your website):
              <div class="gkdCode">folder=<b>my_export</b></div>
              <?php echo $_language->get('text_cron_export_email'); ?>
              <div class="gkdCode">email=<b>mail@yourwebsite.com</b></div>
              <!--
              <?php echo $_language->get('filter_interval'); ?>
              <div class="gkdCode">interval=<b>-1 hour</b> (-1 day, -1 month, last monday, ...)</div>
              <?php echo $_language->get('entry_export_format'); ?>
              <div class="gkdCode">export_format=<b>xml</b></div>
              <?php echo $_language->get('filter_language'); ?>
              <div class="gkdCode">filter_language=<b>XXX</b> (ID)</div>
              <?php echo $_language->get('filter_store'); ?>
              <div class="gkdCode">filter_store=<b>XXX</b> (ID)</div>
              <?php echo $_language->get('filter_status'); ?>
              <div class="gkdCode">filter_status=<b>1</b></div>
              <?php echo $_language->get('filter_quantity'); ?>
              <div class="gkdCode">filter_quantity=<b>1</b></div>
              <?php echo $_language->get('filter_price'); ?>
              <div class="gkdCode">filter_price_min=<b>XX</b> filter_price_max=<b>XX</b></div>
              <?php echo $_language->get('filter_category'); ?>
              <div class="gkdCode">filter_category[]=<b>XXX</b> (ID)</div>
              <?php echo $_language->get('filter_manufacturer'); ?>
              <div class="gkdCode">filter_manufacturer[]=<b>XXX</b> (ID)</div>
              <?php echo $_language->get('export_fields'); ?>
              <div class="gkdCode">export_fields[]=<b>model</b> export_fields[]=<b>quantity</b> export_fields[]=<b>price</b></div>
              <?php echo $_language->get('param_option_row') . ' - ' . $_language->get('text_option_row_option'); ?>
              <div class="gkdCode">param_option_row=<b>1</b></div>
              <?php echo $_language->get('filter_label'); ?>
              <div class="gkdCode">filter_import_batch[]=<b>label</b></div>
              -->
            </div>
          </div>
        </div>
        <div class="tab-pane" id="tab-cron-4">
          <p>Show latest 10.000 lines of log file, to see full log click on Save log file</p>
          <textarea wrap="off" rows="30" readonly class="form-control"><?php echo $cli_log; ?></textarea>
          <div class="text-center" style="margin-top:20px">
            <a class="btn btn-success" <?php if(!$cli_log_link || !$cli_log) { echo 'disabled'; } else { echo 'href="'.$cli_log_link.'"';} ?>><i class="fa fa-download"></i> <?php echo $_language->get('text_cli_log_save'); ?><?php echo isset($cli_log_size) ? ' ('.$cli_log_size.')' : ''; ?></a>
            <a <?php if(!$cli_log_link || !$cli_log) { echo 'disabled not'; } ?>onclick="confirm('<?php echo $_language->get('text_confirm'); ?>') ? location.href='<?php echo $action.'&clear_cli_logs=1'; ?>' : false;" class="btn btn-danger"><i class="fa fa-eraser"></i> <?php echo $_language->get('text_cli_clear_logs'); ?></a>
          </div>
        </div>
      </div>
      </form>
    </div>
		<div class="tab-pane" id="tab-about">
      <div class="row">
        <div class="col-md-4">
          <h3><i class="fa fa-info-circle"></i> Extension details</h3>
          <div class="table-responsive">
            <table class="table"><tbody>
              <tr>
                <td>Module</td>
                <td><?php echo strip_tags($heading_title); ?></td>
              </tr>
              <tr>
                <td>Version</td>
                <td><?php echo $module_version; ?> - <?php echo $module_type; ?></td>
              </tr>
            <?php if (!empty($license_info)) { ?>
              <tr>
                <td colspan="2" style="padding:0;">
                  <h3 style="padding-top:40px;margin-bottom:10px;"><i class="fa fa-check-square"></i> Your License</h3>
                </td>
              </tr>
              <tr>
                <td>License holder</td>
                <td><?php echo $license_info['email']; ?></td>
              </tr>
              <tr>
                <td>Registered domain</td>
                <td><?php echo $license_info['website']; ?></td>
              </tr>
              <tr>
                <td>Support expires on</td>
                <td><?php echo date('F d, Y', strtotime($license_info['support_date'])); ?></td>
              </tr>
              <tr>
                <td>Support status</td>
                <td style="vertical-align:middle">
                  <?php if (time() > strtotime($license_info['support_date'])) { ?>
                    <span class="label label-danger" style="font-size:12px">Expired</span>
                  <?php } else { ?>
                    <span class="label label-success" style="font-size:12px"><i class="fa fa-check"></i> Valid</span>
                  <?php } ?>
                </td>
              </tr>
              <tr>
                <td colspan="2" style="padding:20px 0;">
                <?php if (time() > strtotime($license_info['support_date'])) { ?>
                  <a target="_blank" href="http://geekodev.com/renewal?module=<?php echo $module; ?>&license=<?php echo $license_info['license']; ?>" class="btn btn-success btn-block" style="font-size:14px;"><i class="fa fa-refresh"></i> Renew my license</a>
                  <?php } else { ?>
                  <a target="_blank" href="http://geekodev.com/login" class="btn btn-grey btn-block" style="font-size:14px;"><i class="fa fa-cog"></i> Manage my license</a>
                <?php } ?>
                </td>
              </tr>
            <?php } else { ?>
              <tr><td colspan="2"></td></tr>
            <?php } ?>
            </tbody></table>
          </div>
        </div>
        <div class="col-md-8">
          <h3><i class="fa fa-external-link"></i> Connect with us</h3>
          <div class="row">
            <div class="col-md-12">
              <div class="well text-center">
                <div style="height:80px"><img src="data:image/png;base64,iVBORw0KGgoAAAANSUhEUgAAADwAAAA8CAYAAAA6/NlyAAAACXBIWXMAAAsTAAALEwEAmpwYAAA5z2lUWHRYTUw6Y29tLmFkb2JlLnhtcAAAAAAAPD94cGFja2V0IGJlZ2luPSLvu78iIGlkPSJXNU0wTXBDZWhpSHpyZVN6TlRjemtjOWQiPz4KPHg6eG1wbWV0YSB4bWxuczp4PSJhZG9iZTpuczptZXRhLyIgeDp4bXB0az0iQWRvYmUgWE1QIENvcmUgNS41LWMwMTQgNzkuMTUxNDgxLCAyMDEzLzAzLzEzLTEyOjA5OjE1ICAgICAgICAiPgogICA8cmRmOlJERiB4bWxuczpyZGY9Imh0dHA6Ly93d3cudzMub3JnLzE5OTkvMDIvMjItcmRmLXN5bnRheC1ucyMiPgogICAgICA8cmRmOkRlc2NyaXB0aW9uIHJkZjphYm91dD0iIgogICAgICAgICAgICB4bWxuczp4bXA9Imh0dHA6Ly9ucy5hZG9iZS5jb20veGFwLzEuMC8iCiAgICAgICAgICAgIHhtbG5zOnhtcE1NPSJodHRwOi8vbnMuYWRvYmUuY29tL3hhcC8xLjAvbW0vIgogICAgICAgICAgICB4bWxuczpzdEV2dD0iaHR0cDovL25zLmFkb2JlLmNvbS94YXAvMS4wL3NUeXBlL1Jlc291cmNlRXZlbnQjIgogICAgICAgICAgICB4bWxuczpkYz0iaHR0cDovL3B1cmwub3JnL2RjL2VsZW1lbnRzLzEuMS8iCiAgICAgICAgICAgIHhtbG5zOnBob3Rvc2hvcD0iaHR0cDovL25zLmFkb2JlLmNvbS9waG90b3Nob3AvMS4wLyIKICAgICAgICAgICAgeG1sbnM6dGlmZj0iaHR0cDovL25zLmFkb2JlLmNvbS90aWZmLzEuMC8iCiAgICAgICAgICAgIHhtbG5zOmV4aWY9Imh0dHA6Ly9ucy5hZG9iZS5jb20vZXhpZi8xLjAvIj4KICAgICAgICAgPHhtcDpDcmVhdG9yVG9vbD5BZG9iZSBQaG90b3Nob3AgQ0MgKFdpbmRvd3MpPC94bXA6Q3JlYXRvclRvb2w+CiAgICAgICAgIDx4bXA6Q3JlYXRlRGF0ZT4yMDE2LTA2LTEzVDE4OjMyOjA2KzAyOjAwPC94bXA6Q3JlYXRlRGF0ZT4KICAgICAgICAgPHhtcDpNZXRhZGF0YURhdGU+MjAxNi0wNi0xM1QxODozMjowNiswMjowMDwveG1wOk1ldGFkYXRhRGF0ZT4KICAgICAgICAgPHhtcDpNb2RpZnlEYXRlPjIwMTYtMDYtMTNUMTg6MzI6MDYrMDI6MDA8L3htcDpNb2RpZnlEYXRlPgogICAgICAgICA8eG1wTU06SW5zdGFuY2VJRD54bXAuaWlkOjRlN2U2MGY0LTdiYzYtMDY0Ni04ZTJhLThhNWRhNWRlMjEyYjwveG1wTU06SW5zdGFuY2VJRD4KICAgICAgICAgPHhtcE1NOkRvY3VtZW50SUQ+eG1wLmRpZDowMjgzNzFmNC00MzJlLTY5NDctYjk4ZS1hZGJmNTBiZWViMDk8L3htcE1NOkRvY3VtZW50SUQ+CiAgICAgICAgIDx4bXBNTTpPcmlnaW5hbERvY3VtZW50SUQ+eG1wLmRpZDowMjgzNzFmNC00MzJlLTY5NDctYjk4ZS1hZGJmNTBiZWViMDk8L3htcE1NOk9yaWdpbmFsRG9jdW1lbnRJRD4KICAgICAgICAgPHhtcE1NOkhpc3Rvcnk+CiAgICAgICAgICAgIDxyZGY6U2VxPgogICAgICAgICAgICAgICA8cmRmOmxpIHJkZjpwYXJzZVR5cGU9IlJlc291cmNlIj4KICAgICAgICAgICAgICAgICAgPHN0RXZ0OmFjdGlvbj5jcmVhdGVkPC9zdEV2dDphY3Rpb24+CiAgICAgICAgICAgICAgICAgIDxzdEV2dDppbnN0YW5jZUlEPnhtcC5paWQ6MDI4MzcxZjQtNDMyZS02OTQ3LWI5OGUtYWRiZjUwYmVlYjA5PC9zdEV2dDppbnN0YW5jZUlEPgogICAgICAgICAgICAgICAgICA8c3RFdnQ6d2hlbj4yMDE2LTA2LTEzVDE4OjMyOjA2KzAyOjAwPC9zdEV2dDp3aGVuPgogICAgICAgICAgICAgICAgICA8c3RFdnQ6c29mdHdhcmVBZ2VudD5BZG9iZSBQaG90b3Nob3AgQ0MgKFdpbmRvd3MpPC9zdEV2dDpzb2Z0d2FyZUFnZW50PgogICAgICAgICAgICAgICA8L3JkZjpsaT4KICAgICAgICAgICAgICAgPHJkZjpsaSByZGY6cGFyc2VUeXBlPSJSZXNvdXJjZSI+CiAgICAgICAgICAgICAgICAgIDxzdEV2dDphY3Rpb24+c2F2ZWQ8L3N0RXZ0OmFjdGlvbj4KICAgICAgICAgICAgICAgICAgPHN0RXZ0Omluc3RhbmNlSUQ+eG1wLmlpZDo0ZTdlNjBmNC03YmM2LTA2NDYtOGUyYS04YTVkYTVkZTIxMmI8L3N0RXZ0Omluc3RhbmNlSUQ+CiAgICAgICAgICAgICAgICAgIDxzdEV2dDp3aGVuPjIwMTYtMDYtMTNUMTg6MzI6MDYrMDI6MDA8L3N0RXZ0OndoZW4+CiAgICAgICAgICAgICAgICAgIDxzdEV2dDpzb2Z0d2FyZUFnZW50PkFkb2JlIFBob3Rvc2hvcCBDQyAoV2luZG93cyk8L3N0RXZ0OnNvZnR3YXJlQWdlbnQ+CiAgICAgICAgICAgICAgICAgIDxzdEV2dDpjaGFuZ2VkPi88L3N0RXZ0OmNoYW5nZWQ+CiAgICAgICAgICAgICAgIDwvcmRmOmxpPgogICAgICAgICAgICA8L3JkZjpTZXE+CiAgICAgICAgIDwveG1wTU06SGlzdG9yeT4KICAgICAgICAgPGRjOmZvcm1hdD5pbWFnZS9wbmc8L2RjOmZvcm1hdD4KICAgICAgICAgPHBob3Rvc2hvcDpDb2xvck1vZGU+MzwvcGhvdG9zaG9wOkNvbG9yTW9kZT4KICAgICAgICAgPHRpZmY6T3JpZW50YXRpb24+MTwvdGlmZjpPcmllbnRhdGlvbj4KICAgICAgICAgPHRpZmY6WFJlc29sdXRpb24+NzIwMDAwLzEwMDAwPC90aWZmOlhSZXNvbHV0aW9uPgogICAgICAgICA8dGlmZjpZUmVzb2x1dGlvbj43MjAwMDAvMTAwMDA8L3RpZmY6WVJlc29sdXRpb24+CiAgICAgICAgIDx0aWZmOlJlc29sdXRpb25Vbml0PjI8L3RpZmY6UmVzb2x1dGlvblVuaXQ+CiAgICAgICAgIDxleGlmOkNvbG9yU3BhY2U+NjU1MzU8L2V4aWY6Q29sb3JTcGFjZT4KICAgICAgICAgPGV4aWY6UGl4ZWxYRGltZW5zaW9uPjYwPC9leGlmOlBpeGVsWERpbWVuc2lvbj4KICAgICAgICAgPGV4aWY6UGl4ZWxZRGltZW5zaW9uPjYwPC9leGlmOlBpeGVsWURpbWVuc2lvbj4KICAgICAgPC9yZGY6RGVzY3JpcHRpb24+CiAgIDwvcmRmOlJERj4KPC94OnhtcG1ldGE+CiAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAKICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgIAogICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgCiAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAKICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgIAogICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgCiAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAKICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgIAogICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgCiAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAKICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgIAogICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgCiAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAKICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgIAogICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgCiAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAKICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgIAogICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgCiAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAKICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgIAogICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgCiAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAKICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgIAogICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgCiAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAKICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgIAogICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgCiAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAKICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgIAogICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgCiAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAKICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgIAogICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgCiAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAKICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgIAogICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgCiAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAKICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgIAogICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgCiAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAKICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgIAogICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgCiAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAKICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgIAogICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgCiAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAKICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgIAogICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgCiAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAKICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgIAogICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgCiAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAKICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgIAogICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgCiAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAKICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgIAogICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgCiAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAKICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgIAogICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgCiAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAKICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgIAogICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgCiAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAKICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgIAogICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgCiAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAKICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgIAogICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgCiAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAKICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgIAogICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgCiAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAKICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgIAogICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgCiAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAKICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgIAogICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgCiAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAKICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgIAogICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgCiAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAKICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgIAogICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgCiAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAKICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgIAogICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgCiAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAKICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgIAogICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgCiAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAKICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgIAogICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgCiAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAKICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgIAogICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgCiAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAKICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgIAogICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgCiAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAKICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgIAogICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgCiAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAKICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgIAogICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgCiAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAKICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgIAogICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgCiAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAKICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgIAogICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgCiAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAKICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgIAogICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgCiAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAKICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgIAogICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgCiAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAKICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgIAogICAgICAgICAgICAgICAgICAgICAgICAgICAgCjw/eHBhY2tldCBlbmQ9InciPz75HNQxAAAAIGNIUk0AAHolAACAgwAA+f8AAIDpAAB1MAAA6mAAADqYAAAXb5JfxUYAAAeeSURBVHja5Jt7cBXVHcc/NyEIhA1gJO2oPAQhsUAplFbrM8cOKBWoLzoWaEdbbXOmlbZOp04ftrVlSuuMWgZ1o44tVTRTUSOotRlHFrFIW0FrgEKFxIRHjGBQcqCBhJj+sb9LN8t97I13N+v4/Yfd88r97u9xfr/fOSR6enpIIpFI0F+wHasA+DKwADgPOANIAAeAfwF/BdZoZXblunYvjnEgbDvWSGA18IUAwzcADwI1WpnOjyrhx4H5OU7bA9wJ2NmIx4qw7VjFwLvAoD4usRO4RSvzbBDCBfQ/5n8IsgATgGdsx3rYdqzh2Qb3q4RtxxoEbAHOztOSTcA8rcyW2EnYdqxC4P48kgUYC7xiO9aloUtYpHUuMA2oECIjgE6gWb5+I7AfKAVulO0nDHQAc7Qya/PutGzHOgu4GfgmUEJ8cBiYrpXZmRfCtmNNAX4FzIuJ80uFN4AZVZXtx5MNA/qourcBP+rL/HwikShk9KkzGVFcQUfnAXa31dHR9a53yFTg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" alt="" style="position:relative;top:6px;"/></div>
                <h3>Made by GeekoDev</h3>
                <p>Developed with love and skill.<br/>Check our other great extensions on our website.</p>
                <a href="https://geekodev.com" target="_blank" class="btn btn-lg btn-grey">Visit our website</a>
              </div>
            </div>
          
          </div>
          <div class="row">
            <div class="col-md-6">
              <div class="well text-center">
                <div style="height:80px"><img src="data:image/png;base64,iVBORw0KGgoAAAANSUhEUgAAAD8AAABGCAYAAAB7ckzWAAAACXBIWXMAAAsTAAALEwEAmpwYAABCt2lUWHRYTUw6Y29tLmFkb2JlLnhtcAAAAAAAPD94cGFja2V0IGJlZ2luPSLvu78iIGlkPSJXNU0wTXBDZWhpSHpyZVN6TlRjemtjOWQiPz4KPHg6eG1wbWV0YSB4bWxuczp4PSJhZG9iZTpuczptZXRhLyIgeDp4bXB0az0iQWRvYmUgWE1QIENvcmUgNS41LWMwMTQgNzkuMTUxNDgxLCAyMDEzLzAzLzEzLTEyOjA5OjE1ICAgICAgICAiPgogICA8cmRmOlJERiB4bWxuczpyZGY9Imh0dHA6Ly93d3cudzMub3JnLzE5OTkvMDIvMjItcmRmLXN5bnRheC1ucyMiPgogICAgICA8cmRmOkRlc2NyaXB0aW9uIHJkZjphYm91dD0iIgogICAgICAgICAgICB4bWxuczp4bXA9Imh0dHA6Ly9ucy5hZG9iZS5jb20veGFwLzEuMC8iCiAgICAgICAgICAgIHhtbG5zOmRjPSJodHRwOi8vcHVybC5vcmcvZGMvZWxlbWVudHMvMS4xLyIKICAgICAgICAgICAgeG1sbnM6eG1wTU09Imh0dHA6Ly9ucy5hZG9iZS5jb20veGFwLzEuMC9tbS8iCiAgICAgICAgICAgIHhtbG5zOnN0RXZ0PSJodHRwOi8vbnMuYWRvYmUuY29tL3hhcC8xLjAvc1R5cGUvUmVzb3VyY2VFdmVudCMiCiAgICAgICAgICAgIHhtbG5zOnN0UmVmPSJodHRwOi8vbnMuYWRvYmUuY29tL3hhcC8xLjAvc1R5cGUvUmVzb3VyY2VSZWYjIgogICAgICAgICAgICB4bWxuczpwaG90b3Nob3A9Imh0dHA6Ly9ucy5hZG9iZS5jb20vcGhvdG9zaG9wLzEuMC8iCiAgICAgICAgICAgIHhtbG5zOnRpZmY9Imh0dHA6Ly9ucy5hZG9iZS5jb20vdGlmZi8xLjAvIgogICAgICAgICAgICB4bWxuczpleGlmPSJodHRwOi8vbnMuYWRvYmUuY29tL2V4aWYvMS4wLyI+CiAgICAgICAgIDx4bXA6Q3JlYXRvclRvb2w+QWRvYmUgUGhvdG9zaG9wIENDIChXaW5kb3dzKTwveG1wOkNyZWF0b3JUb29sPgogICAgICAgICA8eG1wOkNyZWF0ZURhdGU+MjAxOC0xMi0xNFQxNDoyNjowMyswMTowMDwveG1wOkNyZWF0ZURhdGU+CiAgICAgICAgIDx4bXA6TWV0YWRhdGFEYXRlPjIwMTktMDUtMjFUMTE6NTA6MTMrMDI6MDA8L3htcDpNZXRhZGF0YURhdGU+CiAgICAgICAgIDx4bXA6TW9kaWZ5RGF0ZT4yMDE5LTA1LTIxVDExOjUwOjEzKzAyOjAwPC94bXA6TW9kaWZ5RGF0ZT4KICAgICAgICAgPGRjOmZvcm1hdD5pbWFnZS9wbmc8L2RjOmZvcm1hdD4KICAgICAgICAgPHhtcE1NOkluc3RhbmNlSUQ+eG1wLmlpZDphMmU2OTE0Yy01MGNkLTU5NDItYWE5OS1lNDhlYWM5YmU4MDU8L3htcE1NOkluc3RhbmNlSUQ+CiAgICAgICAgIDx4bXBNTTpEb2N1bWVudElEPmFkb2JlOmRvY2lkOnBob3Rvc2hvcDpmY2E3ZDJkYy0wNzNkLTRiNDgtOTc3Zi0zNzJiZWJiMzRmYjk8L3htcE1NOkRvY3VtZW50SUQ+CiAgICAgICAgIDx4bXBNTTpPcmlnaW5hbERvY3VtZW50SUQ+eG1wLmRpZDpmZGE2ZjBiMC0xZWM4LTM5NDYtYjZiMy1lZjViNzgwNjNlZjk8L3htcE1NOk9yaWdpbmFsRG9jdW1lbnRJRD4KICAgICAgICAgPHhtcE1NOkhpc3Rvcnk+CiAgICAgICAgICAgIDxyZGY6U2VxPgogICAgICAgICAgICAgICA8cmRmOmxpIHJkZjpwYXJzZVR5cGU9IlJlc291cmNlIj4KICAgICAgICAgICAgICAgICAgPHN0RXZ0OmFjdGlvbj5jcmVhdGVkPC9zdEV2dDphY3Rpb24+CiAgICAgICAgICAgICAgICAgIDxzdEV2dDppbnN0YW5jZUlEPnhtcC5paWQ6ZmRhNmYwYjAtMWVjOC0zOTQ2LWI2YjMtZWY1Yjc4MDYzZWY5PC9zdEV2dDppbnN0YW5jZUlEPgogICAgICAgICAgICAgICAgICA8c3RFdnQ6d2hlbj4yMDE4LTEyLTE0VDE0OjI2OjAzKzAxOjAwPC9zdEV2dDp3aGVuPgogICAgICAgICAgICAgICAgICA8c3RFdnQ6c29mdHdhcmVBZ2VudD5BZG9iZSBQaG90b3Nob3AgQ0MgMjAxOSAoV2luZG93cyk8L3N0RXZ0OnNvZnR3YXJlQWdlbnQ+CiAgICAgICAgICAgICAgIDwvcmRmOmxpPgogICAgICAgICAgICAgICA8cmRmOmxpIHJkZjpwYXJzZVR5cGU9IlJlc291cmNlIj4KICAgICAgICAgICAgICAgICAgPHN0RXZ0OmFjdGlvbj5zYXZlZDwvc3RFdnQ6YWN0aW9uPgogICAgICAgICAgICAgICAgICA8c3RFdnQ6aW5zdGFuY2VJRD54bXAuaWlkOjhmNDYyYTFiLTljZWYtNDI0Zi1iMGQ2LTE1OTZhYTJiMGZjYjwvc3RFdnQ6aW5zdGFuY2VJRD4KICAgICAgICAgICAgICAgICAgPHN0RXZ0OndoZW4+MjAxOC0xMi0xNFQxNToxOTozOSswMTowMDwvc3RFdnQ6d2hlbj4KICAgICAgICAgICAgICAgICAgPHN0RXZ0OnNvZnR3YXJlQWdlbnQ+QWRvYmUgUGhvdG9zaG9wIENDIDIwMTkgKFdpbmRvd3MpPC9zdEV2dDpzb2Z0d2FyZUFnZW50PgogICAgICAgICAgICAgICAgICA8c3RFdnQ6Y2hhbmdlZD4vPC9zdEV2dDpjaGFuZ2VkPgogICAgICAgICAgICAgICA8L3JkZjpsaT4KICAgICAgICAgICAgICAgPHJkZjpsaSByZGY6cGFyc2VUeXBlPSJSZXNvdXJjZSI+CiAgICAgICAgICAgICAgICAgIDxzdEV2dDphY3Rpb24+c2F2ZWQ8L3N0RXZ0OmFjdGlvbj4KICAgICAgICAgICAgICAgICAgPHN0RXZ0Omluc3RhbmNlSUQ+eG1wLmlpZDo4NDY1OTc0ZS1iODJmLWZjNGEtODMyNy01Y2Q1YjQ3ODM1MjY8L3N0RXZ0Omluc3RhbmNlSUQ+CiAgICAgICAgICAgICAgICAgIDxzdEV2dDp3aGVuPjIwMTgtMTItMTRUMTU6MjE6NDYrMDE6MDA8L3N0RXZ0OndoZW4+CiAgICAgICAgICAgICAgICAgIDxzdEV2dDpzb2Z0d2FyZUFnZW50PkFkb2JlIFBob3Rvc2hvcCBDQyAyMDE5IChXaW5kb3dzKTwvc3RFdnQ6c29mdHdhcmVBZ2VudD4KICAgICAgICAgICAgICAgICAgPHN0RXZ0OmNoYW5nZWQ+Lzwvc3RFdnQ6Y2hhbmdlZD4KICAgICAgICAgICAgICAgPC9yZGY6bGk+CiAgICAgICAgICAgICAgIDxyZGY6bGkgcmRmOnBhcnNlVHlwZT0iUmVzb3VyY2UiPgogICAgICAgICAgICAgICAgICA8c3RFdnQ6YWN0aW9uPmNvbnZlcnRlZDwvc3RFdnQ6YWN0aW9uPgogICAgICAgICAgICAgICAgICA8c3RFdnQ6cGFyYW1ldGVycz5mcm9tIGFwcGxpY2F0aW9uL3ZuZC5hZG9iZS5waG90b3Nob3AgdG8gaW1hZ2UvcG5nPC9zdEV2dDpwYXJhbWV0ZXJzPgogICAgICAgICAgICAgICA8L3JkZjpsaT4KICAgICAgICAgICAgICAgPHJkZjpsaSByZGY6cGFyc2VUeXBlPSJSZXNvdXJjZSI+CiAgICAgICAgICAgICAgICAgIDxzdEV2dDphY3Rpb24+ZGVyaXZlZDwvc3RFdnQ6YWN0aW9uPgogICAgICAgICAgICAgICAgICA8c3RFdnQ6cGFyYW1ldGVycz5jb252ZXJ0ZWQgZnJvbSBhcHBsaWNhdGlvbi92bmQuYWRvYmUucGhvdG9zaG9wIHRvIGltYWdlL3BuZzwvc3RFdnQ6cGFyYW1ldGVycz4KICAgICAgICAgICAgICAgPC9yZGY6bGk+CiAgICAgICAgICAgICAgIDxyZGY6bGkgcmRmOnBhcnNlVHlwZT0iUmVzb3VyY2UiPgogICAgICAgICAgICAgICAgICA8c3RFdnQ6YWN0aW9uPnNhdmVkPC9zdEV2dDphY3Rpb24+CiAgICAgICAgICAgICAgICAgIDxzdEV2dDppbnN0YW5jZUlEPnhtcC5paWQ6ZjU3NWU5YTYtOGM3YS1iYjQwLWE5ZGUtYThjMjIzODQ4NTU3PC9zdEV2dDppbnN0YW5jZUlEPgogICAgICAgICAgICAgICAgICA8c3RFdnQ6d2hlbj4yMDE4LTEyLTE0VDE1OjIxOjQ2KzAxOjAwPC9zdEV2dDp3aGVuPgogICAgICAgICAgICAgICAgICA8c3RFdnQ6c29mdHdhcmVBZ2VudD5BZG9iZSBQaG90b3Nob3AgQ0MgMjAxOSAoV2luZG93cyk8L3N0RXZ0OnNvZnR3YXJlQWdlbnQ+CiAgICAgICAgICAgICAgICAgIDxzdEV2dDpjaGFuZ2VkPi88L3N0RXZ0OmNoYW5nZWQ+CiAgICAgICAgICAgICAgIDwvcmRmOmxpPgogICAgICAgICAgICAgICA8cmRmOmxpIHJkZjpwYXJzZVR5cGU9IlJlc291cmNlIj4KICAgICAgICAgICAgICAgICAgPHN0RXZ0OmFjdGlvbj5zYXZlZDwvc3RFdnQ6YWN0aW9uPgogICAgICAgICAgICAgICAgICA8c3RFdnQ6aW5zdGFuY2VJRD54bXAuaWlkOmEyZTY5MTRjLTUwY2QtNTk0Mi1hYTk5LWU0OGVhYzliZTgwNTwvc3RFdnQ6aW5zdGFuY2VJRD4KICAgICAgICAgICAgICAgICAgPHN0RXZ0OndoZW4+MjAxOS0wNS0yMVQxMTo1MDoxMyswMjowMDwvc3RFdnQ6d2hlbj4KICAgICAgICAgICAgICAgICAgPHN0RXZ0OnNvZnR3YXJlQWdlbnQ+QWRvYmUgUGhvdG9zaG9wIENDIChXaW5kb3dzKTwvc3RFdnQ6c29mdHdhcmVBZ2VudD4KICAgICAgICAgICAgICAgICAgPHN0RXZ0OmNoYW5nZWQ+Lzwvc3RFdnQ6Y2hhbmdlZD4KICAgICAgICAgICAgICAgPC9yZGY6bGk+CiAgICAgICAgICAgIDwvcmRmOlNlcT4KICAgICAgICAgPC94bXBNTTpIaXN0b3J5PgogICAgICAgICA8eG1wTU06RGVyaXZlZEZyb20gcmRmOnBhcnNlVHlwZT0iUmVzb3VyY2UiPgogICAgICAgICAgICA8c3RSZWY6aW5zdGFuY2VJRD54bXAuaWlkOjg0NjU5NzRlLWI4MmYtZmM0YS04MzI3LTVjZDViNDc4MzUyNjwvc3RSZWY6aW5zdGFuY2VJRD4KICAgICAgICAgICAgPHN0UmVmOmRvY3VtZW50SUQ+eG1wLmRpZDpmZGE2ZjBiMC0xZWM4LTM5NDYtYjZiMy1lZjViNzgwNjNlZjk8L3N0UmVmOmRvY3VtZW50SUQ+CiAgICAgICAgICAgIDxzdFJlZjpvcmlnaW5hbERvY3VtZW50SUQ+eG1wLmRpZDpmZGE2ZjBiMC0xZWM4LTM5NDYtYjZiMy1lZjViNzgwNjNlZjk8L3N0UmVmOm9yaWdpbmFsRG9jdW1lbnRJRD4KICAgICAgICAgPC94bXBNTTpEZXJpdmVkRnJvbT4KICAgICAgICAgPHBob3Rvc2hvcDpDb2xvck1vZGU+MzwvcGhvdG9zaG9wOkNvbG9yTW9kZT4KICAgICAgICAgPHRpZmY6T3JpZW50YXRpb24+MTwvdGlmZjpPcmllbnRhdGlvbj4KICAgICAgICAgPHRpZmY6WFJlc29sdXRpb24+NzIwMDAwLzEwMDAwPC90aWZmOlhSZXNvbHV0aW9uPgogICAgICAgICA8dGlmZjpZUmVzb2x1dGlvbj43MjAwMDAvMTAwMDA8L3RpZmY6WVJlc29sdXRpb24+CiAgICAgICAgIDx0aWZmOlJlc29sdXRpb25Vbml0PjI8L3RpZmY6UmVzb2x1dGlvblVuaXQ+CiAgICAgICAgIDxleGlmOkNvbG9yU3BhY2U+NjU1MzU8L2V4aWY6Q29sb3JTcGFjZT4KICAgICAgICAgPGV4aWY6UGl4ZWxYRGltZW5zaW9uPjYzPC9leGlmOlBpeGVsWERpbWVuc2lvbj4KICAgICAgICAgPGV4aWY6UGl4ZWxZRGltZW5zaW9uPjcwPC9leGlmOlBpeGVsWURpbWVuc2lvbj4KICAgICAgPC9yZGY6RGVzY3JpcHRpb24+CiAgIDwvcmRmOlJERj4KPC94OnhtcG1ldGE+CiAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAKICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgIAogICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgCiAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAKICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgIAogICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgCiAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAKICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgIAogICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgCiAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAKICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgIAogICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgCiAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAKICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgIAogICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgCiAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAKICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgIAogICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgCiAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAKICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgIAogICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgCiAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAKICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgIAogICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgCiAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAKICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgIAogICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgCiAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAKICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgIAogICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgCiAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAKICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgIAogICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgCiAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAKICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgIAogICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgCiAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAKICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgIAogICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgCiAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAKICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgIAogICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgCiAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAKICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgIAogICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgCiAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAKICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgIAogICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgCiAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAKICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgIAogICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgCiAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAKICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgIAogICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgCiAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAKICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgIAogICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgCiAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAKICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgIAogICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgCiAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAKICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgIAogICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgCiAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAKICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgIAogICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgCiAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAKICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgIAogICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgCiAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAKICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgIAogICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgCiAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAKICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgIAogICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgCiAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAKICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgIAogICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgCiAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAKICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgIAogICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgCiAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAKICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgIAogICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgCiAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAKICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgIAogICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgCiAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAKICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgIAogICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgCiAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAKICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgIAogICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgCiAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAKICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgIAogICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgCiAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAKICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgIAogICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgCiAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAKICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgIAogICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgCiAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAKICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgIAogICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgCiAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAKICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgIAogICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgCiAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAKICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgIAogICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgCiAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAKICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgIAogICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgCiAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAKICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgIAogICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgCiAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAKICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgIAogICAgICAg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" alt=""/></div>
                <h3>Get support</h3>
                <p>You have an issue or need help about module usage?<br/>You have some idea or feature request?</p>
                <a href="https://support.geekodev.com" target="_blank" class="btn btn-lg btn-grey">Open a ticket</a>
              </div>
            </div>
            <div class="col-md-6">
              <div class="well text-center">
                <div style="height:80px"><img src="data:image/png;base64,iVBORw0KGgoAAAANSUhEUgAAAEYAAABLCAYAAADNsPFaAAAACXBIWXMAAAsTAAALEwEAmpwYAABERGlUWHRYTUw6Y29tLmFkb2JlLnhtcAAAAAAAPD94cGFja2V0IGJlZ2luPSLvu78iIGlkPSJXNU0wTXBDZWhpSHpyZVN6TlRjemtjOWQiPz4KPHg6eG1wbWV0YSB4bWxuczp4PSJhZG9iZTpuczptZXRhLyIgeDp4bXB0az0iQWRvYmUgWE1QIENvcmUgNS41LWMwMTQgNzkuMTUxNDgxLCAyMDEzLzAzLzEzLTEyOjA5OjE1ICAgICAgICAiPgogICA8cmRmOlJERiB4bWxuczpyZGY9Imh0dHA6Ly93d3cudzMub3JnLzE5OTkvMDIvMjItcmRmLXN5bnRheC1ucyMiPgogICAgICA8cmRmOkRlc2NyaXB0aW9uIHJkZjphYm91dD0iIgogICAgICAgICAgICB4bWxuczp4bXA9Imh0dHA6Ly9ucy5hZG9iZS5jb20veGFwLzEuMC8iCiAgICAgICAgICAgIHhtbG5zOmRjPSJodHRwOi8vcHVybC5vcmcvZGMvZWxlbWVudHMvMS4xLyIKICAgICAgICAgICAgeG1sbnM6cGhvdG9zaG9wPSJodHRwOi8vbnMuYWRvYmUuY29tL3Bob3Rvc2hvcC8xLjAvIgogICAgICAgICAgICB4bWxuczp4bXBNTT0iaHR0cDovL25zLmFkb2JlLmNvbS94YXAvMS4wL21tLyIKICAgICAgICAgICAgeG1sbnM6c3RFdnQ9Imh0dHA6Ly9ucy5hZG9iZS5jb20veGFwLzEuMC9zVHlwZS9SZXNvdXJjZUV2ZW50IyIKICAgICAgICAgICAgeG1sbnM6c3RSZWY9Imh0dHA6Ly9ucy5hZG9iZS5jb20veGFwLzEuMC9zVHlwZS9SZXNvdXJjZVJlZiMiCiAgICAgICAgICAgIHhtbG5zOnRpZmY9Imh0dHA6Ly9ucy5hZG9iZS5jb20vdGlmZi8xLjAvIgogICAgICAgICAgICB4bWxuczpleGlmPSJodHRwOi8vbnMuYWRvYmUuY29tL2V4aWYvMS4wLyI+CiAgICAgICAgIDx4bXA6Q3JlYXRvclRvb2w+QWRvYmUgUGhvdG9zaG9wIENDIChXaW5kb3dzKTwveG1wOkNyZWF0b3JUb29sPgogICAgICAgICA8eG1wOkNyZWF0ZURhdGU+MjAxNi0xMi0yMVQyMTowNSswMTowMDwveG1wOkNyZWF0ZURhdGU+CiAgICAgICAgIDx4bXA6TW9kaWZ5RGF0ZT4yMDE5LTA1LTE4VDIxOjA2OjA4KzAyOjAwPC94bXA6TW9kaWZ5RGF0ZT4KICAgICAgICAgPHhtcDpNZXRhZGF0YURhdGU+MjAxOS0wNS0xOFQyMTowNjowOCswMjowMDwveG1wOk1ldGFkYXRhRGF0ZT4KICAgICAgICAgPGRjOmZvcm1hdD5pbWFnZS9wbmc8L2RjOmZvcm1hdD4KICAgICAgICAgPHBob3Rvc2hvcDpDb2xvck1vZGU+MzwvcGhvdG9zaG9wOkNvbG9yTW9kZT4KICAgICAgICAgPHBob3Rvc2hvcDpEb2N1bWVudEFuY2VzdG9ycz4KICAgICAgICAgICAgPHJkZjpCYWc+CiAgICAgICAgICAgICAgIDxyZGY6bGk+eG1wLmRpZDo2NDI2N2M4Mi0wN2JmLWYwNDMtOTQyMS0yMzk3NzliODZjNTU8L3JkZjpsaT4KICAgICAgICAgICAgPC9yZGY6QmFnPgogICAgICAgICA8L3Bob3Rvc2hvcDpEb2N1bWVudEFuY2VzdG9ycz4KICAgICAgICAgPHhtcE1NOkluc3RhbmNlSUQ+eG1wLmlpZDpiNmNjZWQzYS1hZDQxLTY4NDMtODhhOC0xNjQyZWY2ZTljMTE8L3htcE1NOkluc3RhbmNlSUQ+CiAgICAgICAgIDx4bXBNTTpEb2N1bWVudElEPnhtcC5kaWQ6NjQyNjdjODItMDdiZi1mMDQzLTk0MjEtMjM5Nzc5Yjg2YzU1PC94bXBNTTpEb2N1bWVudElEPgogICAgICAgICA8eG1wTU06T3JpZ2luYWxEb2N1bWVudElEPnhtcC5kaWQ6NjQyNjdjODItMDdiZi1mMDQzLTk0MjEtMjM5Nzc5Yjg2YzU1PC94bXBNTTpPcmlnaW5hbERvY3VtZW50SUQ+CiAgICAgICAgIDx4bXBNTTpIaXN0b3J5PgogICAgICAgICAgICA8cmRmOlNlcT4KICAgICAgICAgICAgICAgPHJkZjpsaSByZGY6cGFyc2VUeXBlPSJSZXNvdXJjZSI+CiAgICAgICAgICAgICAgICAgIDxzdEV2dDphY3Rpb24+Y3JlYXRlZDwvc3RFdnQ6YWN0aW9uPgogICAgICAgICAgICAgICAgICA8c3RFdnQ6aW5zdGFuY2VJRD54bXAuaWlkOjY0MjY3YzgyLTA3YmYtZjA0My05NDIxLTIzOTc3OWI4NmM1NTwvc3RFdnQ6aW5zdGFuY2VJRD4KICAgICAgICAgICAgICAgICAgPHN0RXZ0OndoZW4+MjAxNi0xMi0yMVQyMTowNSswMTowMDwvc3RFdnQ6d2hlbj4KICAgICAgICAgICAgICAgICAgPHN0RXZ0OnNvZnR3YXJlQWdlbnQ+QWRvYmUgUGhvdG9zaG9wIENDIChXaW5kb3dzKTwvc3RFdnQ6c29mdHdhcmVBZ2VudD4KICAgICAgICAgICAgICAgPC9yZGY6bGk+CiAgICAgICAgICAgICAgIDxyZGY6bGkgcmRmOnBhcnNlVHlwZT0iUmVzb3VyY2UiPgogICAgICAgICAgICAgICAgICA8c3RFdnQ6YWN0aW9uPmNvbnZlcnRlZDwvc3RFdnQ6YWN0aW9uPgogICAgICAgICAgICAgICAgICA8c3RFdnQ6cGFyYW1ldGVycz5mcm9tIGltYWdlL3BuZyB0byBhcHBsaWNhdGlvbi92bmQuYWRvYmUucGhvdG9zaG9wPC9zdEV2dDpwYXJhbWV0ZXJzPgogICAgICAgICAgICAgICA8L3JkZjpsaT4KICAgICAgICAgICAgICAgPHJkZjpsaSByZGY6cGFyc2VUeXBlPSJSZXNvdXJjZSI+CiAgICAgICAgICAgICAgICAgIDxzdEV2dDphY3Rpb24+c2F2ZWQ8L3N0RXZ0OmFjdGlvbj4KICAgICAgICAgICAgICAgICAgPHN0RXZ0Omluc3RhbmNlSUQ+eG1wLmlpZDo3YzgwNzlhZi04Zjc5LTkzNGQtYjkwZi05ZWI1NjYwZTk5YjA8L3N0RXZ0Omluc3RhbmNlSUQ+CiAgICAgICAgICAgICAgICAgIDxzdEV2dDp3aGVuPjIwMTYtMTItMjJUMDk6NDg6NTgrMDE6MDA8L3N0RXZ0OndoZW4+CiAgICAgICAgICAgICAgICAgIDxzdEV2dDpzb2Z0d2FyZUFnZW50PkFkb2JlIFBob3Rvc2hvcCBDQyAoV2luZG93cyk8L3N0RXZ0OnNvZnR3YXJlQWdlbnQ+CiAgICAgICAgICAgICAgICAgIDxzdEV2dDpjaGFuZ2VkPi88L3N0RXZ0OmNoYW5nZWQ+CiAgICAgICAgICAgICAgIDwvcmRmOmxpPgogICAgICAgICAgICAgICA8cmRmOmxpIHJkZjpwYXJzZVR5cGU9IlJlc291cmNlIj4KICAgICAgICAgICAgICAgICAgPHN0RXZ0OmFjdGlvbj5zYXZlZDwvc3RFdnQ6YWN0aW9uPgogICAgICAgICAgICAgICAgICA8c3RFdnQ6aW5zdGFuY2VJRD54bXAuaWlkOmQ4YTVkOGQ2LTc4ODctNDk0OC1hZTkzLWU5ZTc4YjI0Yzk2ODwvc3RFdnQ6aW5zdGFuY2VJRD4KICAgICAgICAgICAgICAgICAgPHN0RXZ0OndoZW4+MjAxOS0wNC0yM1QxNTo0NDo0MiswMjowMDwvc3RFdnQ6d2hlbj4KICAgICAgICAgICAgICAgICAgPHN0RXZ0OnNvZnR3YXJlQWdlbnQ+QWRvYmUgUGhvdG9zaG9wIENDIChXaW5kb3dzKTwvc3RFdnQ6c29mdHdhcmVBZ2VudD4KICAgICAgICAgICAgICAgICAgPHN0RXZ0OmNoYW5nZWQ+Lzwvc3RFdnQ6Y2hhbmdlZD4KICAgICAgICAgICAgICAgPC9yZGY6bGk+CiAgICAgICAgICAgICAgIDxyZGY6bGkgcmRmOnBhcnNlVHlwZT0iUmVzb3VyY2UiPgogICAgICAgICAgICAgICAgICA8c3RFdnQ6YWN0aW9uPmNvbnZlcnRlZDwvc3RFdnQ6YWN0aW9uPgogICAgICAgICAgICAgICAgICA8c3RFdnQ6cGFyYW1ldGVycz5mcm9tIGFwcGxpY2F0aW9uL3ZuZC5hZG9iZS5waG90b3Nob3AgdG8gaW1hZ2UvcG5nPC9zdEV2dDpwYXJhbWV0ZXJzPgogICAgICAgICAgICAgICA8L3JkZjpsaT4KICAgICAgICAgICAgICAgPHJkZjpsaSByZGY6cGFyc2VUeXBlPSJSZXNvdXJjZSI+CiAgICAgICAgICAgICAgICAgIDxzdEV2dDphY3Rpb24+ZGVyaXZlZDwvc3RFdnQ6YWN0aW9uPgogICAgICAgICAgICAgICAgICA8c3RFdnQ6cGFyYW1ldGVycz5jb252ZXJ0ZWQgZnJvbSBhcHBsaWNhdGlvbi92bmQuYWRvYmUucGhvdG9zaG9wIHRvIGltYWdlL3BuZzwvc3RFdnQ6cGFyYW1ldGVycz4KICAgICAgICAgICAgICAgPC9yZGY6bGk+CiAgICAgICAgICAgICAgIDxyZGY6bGkgcmRmOnBhcnNlVHlwZT0iUmVzb3VyY2UiPgogICAgICAgICAgICAgICAgICA8c3RFdnQ6YWN0aW9uPnNhdmVkPC9zdEV2dDphY3Rpb24+CiAgICAgICAgICAgICAgICAgIDxzdEV2dDppbnN0YW5jZUlEPnhtcC5paWQ6ZjFhMTdkOTUtODQ1NC1lNTQwLThmMTItMDY4ZDBkNTE5NWRiPC9zdEV2dDppbnN0YW5jZUlEPgogICAgICAgICAgICAgICAgICA8c3RFdnQ6d2hlbj4yMDE5LTA0LTIzVDE1OjQ0OjQyKzAyOjAwPC9zdEV2dDp3aGVuPgogICAgICAgICAgICAgICAgICA8c3RFdnQ6c29mdHdhcmVBZ2VudD5BZG9iZSBQaG90b3Nob3AgQ0MgKFdpbmRvd3MpPC9zdEV2dDpzb2Z0d2FyZUFnZW50PgogICAgICAgICAgICAgICAgICA8c3RFdnQ6Y2hhbmdlZD4vPC9zdEV2dDpjaGFuZ2VkPgogICAgICAgICAgICAgICA8L3JkZjpsaT4KICAgICAgICAgICAgICAgPHJkZjpsaSByZGY6cGFyc2VUeXBlPSJSZXNvdXJjZSI+CiAgICAgICAgICAgICAgICAgIDxzdEV2dDphY3Rpb24+c2F2ZWQ8L3N0RXZ0OmFjdGlvbj4KICAgICAgICAgICAgICAgICAgPHN0RXZ0Omluc3RhbmNlSUQ+eG1wLmlpZDpiNmNjZWQzYS1hZDQxLTY4NDMtODhhOC0xNjQyZWY2ZTljMTE8L3N0RXZ0Omluc3RhbmNlSUQ+CiAgICAgICAgICAgICAgICAgIDxzdEV2dDp3aGVuPjIwMTktMDUtMThUMjE6MDY6MDgrMDI6MDA8L3N0RXZ0OndoZW4+CiAgICAgICAgICAgICAgICAgIDxzdEV2dDpzb2Z0d2FyZUFnZW50PkFkb2JlIFBob3Rvc2hvcCBDQyAoV2luZG93cyk8L3N0RXZ0OnNvZnR3YXJlQWdlbnQ+CiAgICAgICAgICAgICAgICAgIDxzdEV2dDpjaGFuZ2VkPi88L3N0RXZ0OmNoYW5nZWQ+CiAgICAgICAgICAgICAgIDwvcmRmOmxpPgogICAgICAgICAgICA8L3JkZjpTZXE+CiAgICAgICAgIDwveG1wTU06SGlzdG9yeT4KICAgICAgICAgPHhtcE1NOkRlcml2ZWRGcm9tIHJkZjpwYXJzZVR5cGU9IlJlc291cmNlIj4KICAgICAgICAgICAgPHN0UmVmOmluc3RhbmNlSUQ+eG1wLmlpZDpkOGE1ZDhkNi03ODg3LTQ5NDgtYWU5My1lOWU3OGIyNGM5Njg8L3N0UmVmOmluc3RhbmNlSUQ+CiAgICAgICAgICAgIDxzdFJlZjpkb2N1bWVudElEPnhtcC5kaWQ6NjQyNjdjODItMDdiZi1mMDQzLTk0MjEtMjM5Nzc5Yjg2YzU1PC9zdFJlZjpkb2N1bWVudElEPgogICAgICAgICAgICA8c3RSZWY6b3JpZ2luYWxEb2N1bWVudElEPnhtcC5kaWQ6NjQyNjdjODItMDdiZi1mMDQzLTk0MjEtMjM5Nzc5Yjg2YzU1PC9zdFJlZjpvcmlnaW5hbERvY3VtZW50SUQ+CiAgICAgICAgIDwveG1wTU06RGVyaXZlZEZyb20+CiAgICAgICAgIDx0aWZmOk9yaWVudGF0aW9uPjE8L3RpZmY6T3JpZW50YXRpb24+CiAgICAgICAgIDx0aWZmOlhSZXNvbHV0aW9uPjcyMDAwMC8xMDAwMDwvdGlmZjpYUmVzb2x1dGlvbj4KICAgICAgICAgPHRpZmY6WVJlc29sdXRpb24+NzIwMDAwLzEwMDAwPC90aWZmOllSZXNvbHV0aW9uPgogICAgICAgICA8dGlmZjpSZXNvbHV0aW9uVW5pdD4yPC90aWZmOlJlc29sdXRpb25Vbml0PgogICAgICAgICA8ZXhpZjpDb2xvclNwYWNlPjY1NTM1PC9leGlmOkNvbG9yU3BhY2U+CiAgICAgICAgIDxleGlmOlBpeGVsWERpbWVuc2lvbj43MDwvZXhpZjpQaXhlbFhEaW1lbnNpb24+CiAgICAgICAgIDxleGlmOlBpeGVsWURpbWVuc2lvbj43NTwvZXhpZjpQaXhlbFlEaW1lbnNpb24+CiAgICAgIDwvcmRmOkRlc2NyaXB0aW9uPgogICA8L3JkZjpSREY+CjwveDp4bXBtZXRhPgogICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgCiAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAKICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgIAogICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgCiAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAKICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgIAogICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgCiAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAKICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgIAogICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgCiAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAKICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgIAogICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgCiAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAKICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgIAogICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgCiAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAKICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgIAogICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgCiAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAKICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgIAogICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgCiAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAKICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgIAogICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgCiAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAKICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgIAogICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgCiAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAKICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgIAogICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgCiAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAKICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgIAogICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgCiAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAKICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgIAogICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgCiAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAKICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgIAogICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgCiAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAKICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgIAogICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgCiAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAKICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgIAogICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgCiAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAKICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgIAogICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgCiAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAKICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgIAogICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgCiAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAKICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgIAogICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgCiAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAKICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgIAogICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgCiAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAKICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgIAogICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgCiAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAKICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgIAogICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgCiAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAKICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgIAogICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgCiAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAKICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgIAogICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgCiAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAKICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgIAogICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgCiAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAKICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgIAogICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgCiAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAKICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgIAogICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgCiAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAKICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgIAogICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgCiAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAKICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgIAogICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgCiAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAKICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgIAogICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgCiAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAKICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgIAogICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgCiAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAKICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgIAogICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgCiAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAKICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgIAogICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgCiAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAKICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgIAogICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgCiAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAKICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgIAogICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgCiAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAKICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgIAogICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgCiAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAKICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgIAogICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgCiAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAKICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgIAogICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgCiAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAKICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgIAogICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgCiAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAKICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgIAogICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgCiAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAKICAgICAgICAgICAgICAgICAgICAgICAgICAgIAo8P3hwYWNrZXQgZW5kPSJ3Ij8+F5l64gAAACBjSFJNAAB6JQAAgIMAAPn/AACA6QAAdTAAAOpgAAA6mAAAF2+SX8VGAAAF5ElEQVR42uyba4hUVRzAf/fOax/q7K6PXR8rhj0RMbUtUyos7YMEmkUiERTRh0KLIgqiKCEhEIkkI0wi6ENE0AvrQ7uiBj7SzKSXEkGpWGrqXDVdZ2fu6cP5j3ud5525O87seP5wmLlnzj337O/+z/9xzllLKYWRXLENAgPGgDFgDBgDxoAxYAyYxpLw7B3OWOCEQZGrMeuBGw2KXDAK+A242eC4HMzDwDlgH/CSQTIIJgXMEs1ZDRwEVgLtxivB70APcB64HlgngNYDdwPW1QbGmrU94b2+CdgMjM9qdxjoA7YD3wKHgItXExiATmAjcF+R+44C3wF/imb9Ii7/BHCqUcFk5FngRQHlV04KnGMyLR3gtHyeAdJ5pqWSKd0BhAHX57NCwAXp3xuoWtLHKflMe17WWRnLSak7VwkYgFHAU8CTwOQGmin9npd2QDzyfmCTgCwJxvt2lgDLgLuAcQ1qWg4Ba4C3/YLxykhgHjBfPNm0BgS1wpq5PRHUF7cD14qbnw50A1OkjBO7MdzkYDhkQdrVVsrKsogWYJemdhrYI8UrUaBNAHWLvWoWjWuRz3QZg1UCud2TyijPy4lIfZdcj5TnV7KCkAiHbAjbELUHn5Jh0Z+CpAuWVVGElwSOS9ldg7ceB8aKNs8AHgRu8XnvBqtnV4KoDSNCg2AycBRwZgAupHxrT73LcuBd0d5C8j3QEwZQCtIqFwxAaxgiFpxP6TbDHM5H4qY3FUhz/gYWUWr+KcBVEA1BPAaxEKSUrhvGsq1A/TFgrgSouR6jb3a8YI8L9jpEbG17BkR7hqECrcijLfuBxcBf2dl1SSiZ35tCMDIKLWKT0sNLe+YBb2TVbQHmeKEAWD27Euy+LV72ExbudUi6gT3XlZRpwF4g5qn7FHgg73pMJVDI2B4b4lFojQzapDqVbmBrFpT3C0EBsIKcj5m728FCa0syDSm3LrWmDfgBuMZTtwZ4oeiyQ9CDQ7N3Olr1hIhSutSJRCUume6pex5YW+rGwHlMdlxjWbrUCaDeLCiPAR/4ubFqCZ7l0aAayefAnZ7rJcAXfm8ODCbtXh4Q5oNTA7uzUeISJNJdBOwop4PAYNwsILYXRG20ZTXwuHw/AixEr9KVp/FBjO/MHc6lv92mLjzSSvTWD8DPwL2S/5RvO4OMIiVUQvUBZbkHyhbg1kqhBNKYqdscwrJcYdU+uJsD7JTvnwAPBfa2QbSl2YaQVXMoY9CbgADvDAWUiozv5K0OKRdawhCzB6dTjcRGrw5GgFeA14eq47LBDLg6NmkN1UVuZANvicbsG9I4rLMvwT/3lE4kOzc7WBZcTEM8Al0xvSbTqGKnFIzpc4pP4j4HV0F/GsIWdET976MOVwlnXvpoD5yTC+K09TqWpd2wssTYphSMb9G25aLb2GdD8tqYjl4HF1TI44HSCiY1Q1tEFqYGmz8CLBXv0A98BbwH/BdwbLdL0neDBNh7ZA3lwJUAY3X0JnIqldI7YbaE9SkFE5v1FEq6l4zuGPQK2B15+v0DuB/4qcJxrQWeK/DbM55ArqpWvagkXehqgtExPX089ravABSAqRJwdVUwpleLQEG80NKagbGApNIeqCOmV+i4fPrMKNF3K/BymeMZD7zmo906qnx4u2DnaaU9UGeThpS1ybzMZ/+LJJXyK4t9tpsouVD1wXh3yJVMoc4YNIdzjC1lTJGx6A18vzKpjLbdVfdKrhjczBmtlEBpj+gplMctH/PZ/7/oI2d+5UgZbQ9XVWPSCkZHYUqL9jyTmmFKq55CbuGs+WOf/X9NeUc9vvTZ7ihVPkFhK3TANiKs859MUcX3qD8EfizR91lgVZnjOerT+K6gysG3DRpAKquUSIMU+mB0oXXUQ+iF6OMVjGkV8GaR5z4NfFb1AG/UNwkmNGm3PFDZO3gCfSZ4gtiULegzKGcCjm0+8ChwHTAg2fMG4NcrEvma/9SvMPI1YIwYMAaMAWPAGDAGjAFjwBgwBowBY8SAMWAMmCGV/wcAW96kU8ix6wIAAAAASUVORK5CYII=" alt=""/></div>
                <h3>Feedback</h3>
                <p>If you like this module, please consider to leave a comment on module page, or <a href="https://www.opencart.com/index.php?route=account/rating" target="_blank">a rating</a> from your account.</p>
                <a href="https://www.opencart.com/index.php?route=extension/extension/info&extension_id=<?php echo $OCID; ?>" target="_blank" class="btn btn-lg btn-grey">OC module page</a>
              </div>
            </div>
          </div>
        </div>
      </div>
      <style type="text/css">
        #tab-about .well p{font-size:13px; min-height:55px}
        #tab-about table td{font-size:13px; padding: 13px 5px;}
        #tab-about table tr td:first-child{font-weight:bold;}
        #tab-about .btn-grey{background: #818181; color: white; border-color: #6b6b6b;}
        #tab-about .btn-grey:hover{background: #646464; color: white; border-color: #727272;}
      </style>
		</div>
		</div>
	  </div>
	  </div>
  </div>
</div>
<!-- jQuery File Upload Dependencies -->
<?php /*
<script src="<?php echo $_asset_path; ?>file-upload/jquery.ui.widget.js"></script>
<script src="<?php echo $_asset_path; ?>file-upload/jquery.iframe-transport.js"></script>
<script src="<?php echo $_asset_path; ?>file-upload/jquery.fileupload.js"></script>
*/ ?>

<script type="text/javascript"><!--
var uploadUrl = 'index.php?route=module/universal_import/import_file&<?php echo $token; ?>';

$(document).on('submit','#form',function(){
  $('#form :input[isacopy]').remove();
  
  $('#formCron :input').not(':submit').clone().hide().attr('isacopy','y').appendTo('#form');
  
  // cloned selects does not get selected value
  $($('#formCron').find('select')).each(function(i) {
    if ($(this).attr('name')) {
      $('#form [name='+$(this).attr('name')+']').val($(this).val());
    }
  });
  return true;
});

// info wells
$('body').on('click', '.infowell h4', function (e) {
  $(this).parent().find('div').slideToggle();
  $(this).find('i').toggleClass('fa-caret-right').toggleClass('fa-caret-down');
});

// category bindings
var categoriesOptions = [
<?php $f=false; foreach ($categories as $id => $cat) {
  echo $f ? ',' : ''; $f = true;
  echo '{id:\''.$id.'\', title:'.json_encode(html_entity_decode($cat, ENT_QUOTES, 'UTF-8')).'}';
  /*
  if ($f) {
    echo '{id:\''.$id.'\', title:'.json_encode(html_entity_decode($cat)).'}';
  } else {
    echo '{id:\'\', title:'.json_encode(html_entity_decode($_language->get('text_no_binding'))).'}';
    $f=true;
  }
  */
} ?>
];

$('body').on('click', '.gkdwidget .icon', function(){
  $(this).closest('.gkdwidget').find('.gkdwidget-container').slideUp(function(){
    $(this).closest('.gkdwidget').find('.active').removeClass('active');
  });
});

$('body').on('click', '.gkdwidget a[data-toggle="tab"]', function(){
  $(this).closest('.gkdwidget').find('.gkdwidget-container').fadeIn();
  $(this).closest('.gkdwidget').find('header').addClass('active');
});

$('body').tooltip({selector: '[data-toggle=tooltip]'});

$('body').on('click', 'button.get-bindings', function(){
  $('#categoryBinding tbody').html('<tr><td colspan="3" style="text-align:center"><img src="view/universal_import/img/loader.gif" alt=""/></td></tr>');
  
  $.post('index.php?route=module/universal_import/get_bindings&<?php echo $token; ?>', $('#import-form').serialize(), function(data) {
    $('#categoryBinding tbody').html(data);
    $('.catBindSelect').selectize({valueField: 'id', labelField: 'title', searchField: 'title', options: categoriesOptions});
  });
});

$('body').on('click', 'button.get-option-fields', function(){
  $('#optionFields tbody').html('<tr><td colspan="3" style="text-align:center"><img src="view/universal_import/img/loader.gif" alt=""/></td></tr>');
  
  $.post('index.php?route=module/universal_import/get_option_fields&<?php echo $token; ?>', $('#import-form').serialize(), function(data) {
    $('#optionFields tbody').html(data);
  });
});
      
$('.mainMenu').on('click', 'li > a', function(){
  if ($(this).attr('href') == '#tab-2' || $(this).attr('href') == '#tab-cron') {
    $('.saveBtns').show();
  } else {
    $('.saveBtns').hide();
  }
});

$('body').on('change', 'select[name=option_identifier]', function(){
  if ($(this).val() == '') {
    $('.hideForOptionUpdate').fadeIn();
  } else {
    $('.hideForOptionUpdate').fadeOut();
  }
});

$('body').on('change', 'input[name=image_download]', function(){
  if ($(this).val() == 1) {
    $('.imgDownload').fadeIn();
  } else {
    $('.imgDownload').fadeOut();
  }
});

$('body').on('change', 'select[name=import_source]', function(){
  //$('#importSource').load('index.php?route=module/universal_import/import_step1&<?php echo $token; ?>&file='+$('#import_file').val());
  
  $.post('index.php?route=module/universal_import/import_step1&<?php echo $token; ?>', $('#import-form').serialize(), function(data) {
    $('#importSource').html(data);
  });
  
  /*
  $('div[class^=source_]').fadeOut(100);
  $('div.source_'+$(this).val()).delay(100).fadeIn();
  */
  <?php if (!$demo_mode) { ?>
  if ($(this).val() == 'upload') {
    $('#importStep1 button.submit').attr('disabled', true);
  } else {
    $('#importStep1 button.submit').attr('disabled', false);
  }
  <?php } ?>
});
$('select[name=import_source]').change();

$('body').on('change', 'select[name=delete]', function(){
  if ($(this).val()) {
    if ($('select[name=import_type]').val() == 'product' || $('select[name=import_type]').val() == 'product_update') {
      $('.delete_batch').slideDown();
    }
    $('.delete_action').fadeIn();
  } else {
    $('.delete_batch').slideUp();
    $('.delete_action').fadeOut();
  }
});

$('body').on('change', 'select[name="delete_condition[db_field]"]', function(){
  if ($(this).val()) {
    $('.delete_condition_show').slideDown();
  } else {
    $('.delete_condition_show').slideUp();
  }
});

<?php if ($demo_mode) { ?>
$('body').on('change', 'select[name=demo_file]', function(){
  if ($(this).val()) {
    $('#importStep1 button.submit').attr('disabled', false);
    $('#import_file').val($(this).val());
    
    $('#files').hide().html('<?php echo $_language->get('text_file_loaded'); ?> <i class="fa fa-file"></i> <b>' + $(this).val() + '</b>').fadeIn();
  } else {
    $('#import_file').val('');
    $('#importStep1 button.submit').attr('disabled', true);
    $('#files').fadeOut('fast');
  }
});

$('select[name=demo_file]').trigger('change');
<?php } ?>

// handle extra functions
$('body').on('change', '.xfnFieldVal', function(){
  if ($(this).val()) {
    $(this).next().hide();
  } else {
    $(this).next().show();
  }
  /*
  $(this).parent().parent().find('.extraFieldColumnMl').each(function() {
    name = $(this).attr('fieldname');
    name = name.replace('[_extra_]', '['+$(this).val()+']');
    $(this).attr('name', name);
  });
  */
});

// handle extra columns
$('body').on('change', '.extraFieldName', function(){
  $(this).parent().parent().find('.extraFieldColumn').attr('name', 'columns['+$(this).val()+']');
  $(this).parent().parent().find('.extraFieldForced').attr('value', $(this).val());
});

$('body').on('change', '.extraFieldNameMl', function(){
  //name = name.replace(/(columns\[.+\]\[.+\]\[).+\]/, '$1'+$(this).val()+']');
  //$(this).parent().parent().find('.extraFieldColumnMl').attr('name', name);
  var nameReplace = $(this).val();
  $(this).parent().parent().find('.extraFieldColumnMl').each(function(i, el) {
    name = $(el).attr('fieldname').replace('[_extra_]', '['+nameReplace+']');
    $(el).attr('name', name)
  });
  $(this).parent().parent().find('.extraFieldForcedMl').attr('value', nameReplace);
});

$('body').on('click', '.add-extra', function (e) {
  var element = $('.extraField');
  var extra = element.first().clone();
  extra.find('[disabled]').removeAttr('disabled');
  extra.insertAfter(element.last());
  if (extra.find('input[type=checkbox]').length) {
   extra.find('input[type=checkbox]').prettyCheckable();
  }
  $('.extraField').last().slideDown();
});

$('body').on('click', '.add-extra-ml', function (e) {
  var element = $('.extraFieldMl');
  var extra = element.first().clone();
  extra.find('[disabled]').removeAttr('disabled');
  extra.insertAfter(element.last());
  if (extra.find('input[type=checkbox]').length) {
    extra.find('input[type=checkbox]').prettyCheckable();
  }
  $('.extraFieldMl').last().slideDown();
});

var attrRow = 0;
$('body').on('click', '.add-attribute-field', function (e) {
  var element = $('.attributeField');
  
  if (attrRow > 0) {
    attrRow++;
  } else {
    attrRow = $('.attributeField').length;
  }
  
  var extra = element.first().clone();
  
  extra.insertAfter(element.last());
  if (extra.find('input[type=checkbox]').length) {
    extra.find('input[type=checkbox]').prettyCheckable();
  }
  //var xfnRow = $('#extraFuncs tr[class^="xfn-"]').length;
  extra.find('[disabled]').each(function(i) {
    $(this).attr('name', $(this).attr('name').replace('attribute_fields[]', 'attribute_fields['+attrRow+']'));
    $(this).attr('name', $(this).attr('name').replace('attribute_fields_default[]', 'attribute_fields_default['+attrRow+']'));
  });
  
  extra.find('[disabled]').removeAttr('disabled');
  
  $('.attributeField').last().slideDown();
});

$('body').on('click', '.remove-extra-column', function (e) {
  $(this).closest('.extraField').slideUp(400, function(){$(this).closest('.extraField').remove()});
});

$('body').on('click', '.remove-extra-column-ml', function (e) {
  $(this).closest('.extraFieldMl').slideUp(400, function(){$(this).closest('.extraFieldMl').remove()});
});

$('body').on('click', '.remove-attribute-field', function (e) {
  $(this).closest('.attributeField').slideUp(400, function(){$(this).closest('.attributeField').remove()});
});

$('#import-form').on('click', 'button.browsePreview',  function(){
  $('button.submit[data-step=3]').attr('data-start', $(this).attr('data-start')).trigger('click');
});

$('#import-form').on('keyup', '.browsePreviewInput',  function(e){
  if(e.keyCode == 13) {
    $('button.submit[data-step=3]').attr('data-start', $(this).val()).trigger('click');
  }
});

$('textarea').keyup(function(e){
    if(e.keyCode == 13)
    {
        $(this).trigger("enterKey");
    }
});

$('#import-form').on('click', 'button.submit', function(){
  var current_step = $(this).attr('data-step');
  var next_step = parseInt($(this).attr('data-step')) + 1;
  var extra_params = '';

  if (current_step == 2 && $('select[name=import_type]').val() == 'restore') {
    next_step++;
  }
  
  if (current_step == 3 && $(this).attr('data-start') !== undefined) {
    extra_params = '&start=' + ($(this).attr('data-start'));
  }
  
  $('#importStep' + next_step).html('<div style="text-align:center"><img src="view/universal_import/img/loader.gif" alt=""/></div>');
  $('#importStep' + current_step).collapse('hide');
  $('#importStep' + next_step).collapse('show');
  
  $.ajax({
    type: 'POST',
    url: 'index.php?route=module/universal_import/import_step' + next_step + extra_params + '&<?php echo $token; ?>',
    data: $('#import-form').serialize(),
    success: function(data) {
      if (data.file_error) {
        $('#alertFileUpload span').html(data.file_error);
        $('#alertFileUpload').show();
        $('#importStep2').html('').collapse('hide');
        $('#importStep1').collapse('show');
      } else {
        $('#alertFileUpload').hide();
        $('#importStep' + next_step).html(data);
        $.each( $('#importStep' + next_step + ' input.switch'), function(){ $(this).prettyCheckable(); });
      }
    },
    error: function(data, e, error) {
      $('#importStep' + next_step).html('<div class="well clearfix" style="margin: 60px"><h4>Error: '+error+'</h4><div>'+data.responseText+'</div><div class="pull-right"><button type="button" class="btn btn-default cancel" data-step="2"><i class="fa fa-reply"></i> <?php echo $_language->get('text_previous_step'); ?></button></div></div>');
      // $('#modal-alert').find('.modal-title').text('Error: ' + error);
      // $('#modal-alert').find('.modal-body').html(data.responseText);
      // $('#modal-alert').modal('show');
    }
  });
/*
  $.post('index.php?route=module/universal_import/import_step' + next_step + '&<?php echo $token; ?>', $('#import-form').serialize(), function(data) {
    if (data == 'file_not_found') {
      $('#alertFileNotFound').show();
      $('#importStep2').html('').collapse('hide');
      $('#importStep1').collapse('show');
    } else {
      $('#alertFileNotFound').hide();
      $('#importStep' + next_step).html(data);
      $.each( $('#importStep' + next_step + ' input.switch'), function(){ $(this).prettyCheckable(); });
    }
    //$('#importStep' + next_step + ' input.switch').prettyCheckable();
  });
  */
//  $('#importStep2').load('index.php?route=module/universal_import/import_step1&<?php echo $token; ?>&file='+$('#import_file').val());
});

$('#import-form').on('click', 'button.cancel', function(){
  var current_step = $(this).attr('data-step');
  var previous_step = parseInt($(this).attr('data-step')) - 1;

  if (current_step == 4 && $('select[name=import_type]').val() == 'restore') {
    previous_step--;
  }
  
  $('#importStep' + current_step).collapse('hide');
  $('#importStep' + previous_step).collapse('show');
});

//$('input.switch').prettyCheckable();
$.each( $('input.switch'), function(){ $(this).prettyCheckable(); });

// display specific field
$('body').on('change', 'input[name="pwd_hash"]', function(){
  if ($(this).attr('checked')) {
    $('#pwd_salt').slideUp();
  } else {
    $('#pwd_salt').slideDown();
  }
});

$('body').on('change', 'select[name=import_type]', function(){
  $('.type-hide').hide();
  $('.show-'+$(this).val()).show();
  $('select[name=profile]').val('');
  
  if ($(this).val() == 'restore') {
    $('div[href="#importStep3"] h4').css('color', '#bbb');
    $('select[name=import_extension] option').hide();
    $('select[name=import_extension] option[value=sql]').show();
    $('select[name=import_extension]').val('sql');
  } else {
    $('div[href="#importStep3"] h4').css('color', '#595959');
    $('select[name=import_extension] option').show();
    $('select[name=import_extension] option[value=sql]').hide();
    $('select[name=import_extension]').val('');
  }
});

$('body').on('change', 'select.importCronTypeSelect', function(){
  $('.importCronType').hide();
  $('.importCronType').html($(this).val());
  $('.importCronType').fadeIn();
  $('.importCronTypeHide').hide();
  $('.importCronTypeShow-'+$(this).val()).show();
  $('select.importCronProfileSelect').val('').trigger('change');
});

$('body').on('change', 'select.importCronProfileSelect', function(){
  $('.importCronProfile').hide();
  //$('.importCronProfile').html(encodeURI($(this).val()));
  if ($(this).val()) {
  $('.importCronProfile').html($(this).val());
  } else {
    $('.importCronProfile').html('Profile_Name');
  }
  
  if ($(this).val().includes(' ')) {
    $('.importProfileQuote,.importCronQuoteAlert').show();
  } else {
    $('.importProfileQuote,.importCronQuoteAlert').hide();
  }
  
  $('.importCronProfile').fadeIn();
});

$('body').on('change', 'select.exportCronTypeSelect', function(){
  $('.exportCronType').hide();
  $('.exportCronType').html($(this).val());
  $('.exportCronType').fadeIn();
  $('.exportCronTypeHide').hide();
  $('.exportCronTypeShow-'+$(this).val()).show();
  $('select.exportCronProfileSelect').val('').trigger('change');
});

$('body').on('change', 'select.exportCronProfileSelect', function(){
  $('.exportCronProfile').hide();
  //$('.importCronProfile').html(encodeURI($(this).val()));
  if ($(this).val()) {
    $('.exportCronProfile').html($(this).val());
  } else {
    $('.exportCronProfile').html('Profile_Name');
  }
  
  if ($(this).val().includes(' ')) {
    $('.exportProfileQuote,.exportCronQuoteAlert').show();
  } else {
    $('.exportProfileQuote,.exportCronQuoteAlert').hide();
  }
  
  $('.exportCronProfile').fadeIn();
});

$('select.importCronTypeSelect,select.exportCronTypeSelect').trigger('change');

/* Export conditions */
$('body').on('click', '.add-condition, .add-or-condition', function(){
  var fieldName = 'conditions';
  
  if ($(this).attr('class').includes('or-condition')) {
    var fieldName = 'conditions_or';
  }
  
  var feed_row = $(this).attr('data-feed-row');
  var condition_key = $(this).parent().find('.input-group').length;
  
  while ($('[name="univfeed_feeds['+ feed_row +'][conditions]['+ condition_key +'][field]"]').length) {
    condition_key++;
  }
  
  html  = '<div class="input-group" style="margin-bottom:15px">';
  html  += '  <span class="input-group-addon"><i class="fa fa-question-circle"></i></span>';
  html  += '  <input type="text" name="'+fieldName+'['+ condition_key +'][db_field]" value="" class="form-control" placeholder="DB field (product_id)"/>';
  html  += '  <select name="'+fieldName+'['+ condition_key +'][comparator]" class="form-control">';
  <?php foreach (array('is_equal', 'is_equal_list', 'is_not_equal', 'is_not_equal_list', 'is_greater', 'is_lower', 'contain', 'not_contain') as $comparator) { ?>
  html  += '    <option value="<?php echo $comparator; ?>"><?php echo addslashes($_language->get('xfn_'.$comparator)); ?></option>';
  <?php } ?>
  html  += '  </select>';
  html  += '  <input type="text" name="'+fieldName+'['+ condition_key +'][value]" value="" class="form-control" placeholder="123"/>';
  html  += '  <span class="input-group-addon"><button type="button" class="btn btn-danger btn-xs remove-condition" ><i class="fa fa-remove"></i></button></span>';
  html  += '</div>';
  
  $(this).before(html);
});

$('body').on('click', '.remove-condition', function(){
  $(this).parent().parent().remove();
});

/* Profiles */
$('body').on('change', 'select[name=profile]', function(){
  $.post('index.php?route=module/universal_import/get_profile_source&<?php echo $token; ?>', $('#import-form').serialize(), function(data) {
    if (!($('select[name=import_source]').val() == 'upload' && data.source == 'upload')) {
     $('select[name=import_source]').val(data.source);
     $('select[name=import_source]').change();
    }
    
    if (data.extension) {
      $('select[name=import_extension]').val(data.extension);
    } else {
      $('select[name=import_extension]').val('');
    }
    
    if (data.compression) {
      $('select[name=import_compression]').val(data.compression);
    } else {
      $('select[name=import_compression]').val('');
    }
    
    if (data.import_transformer) {
      $('select[name=import_transformer]').val(data.import_transformer);
    } else {
      $('select[name=import_transformer]').val('');
    }
  });
  $('select[name=save_profile]').val($(this).val()).trigger('change');
  if ($(this).val()) {
    $('.deleteProfile').attr('disabled', false);
  } else {
    $('.deleteProfile').attr('disabled', true);
  }
});

$('body').on('click', '.deleteProfile', function(){
  if (!$('select[name=profile]').val()) return;
  
  if (window.confirm('<?php echo $_language->get('text_really_delete'); ?>')) {
    $.post('index.php?route=module/universal_import/delete_profile&<?php echo $token; ?>', $('#import-form').serialize(), function(data) {
      if(data.success) {
        $('select[name=profile] option:selected').remove();
      } else {
        alert('Error');
      }
    }, 'json');
  }
});

$('body').on('change', 'select[name=export_profile]', function(){
  $.post('index.php?route=module/universal_import/get_profile_format&<?php echo $token; ?>', $('#export-form').serialize(), function(data) {
    if (data.export_format) {
      $('select[name=export_format]').val(data.export_format);
    } else {
      $('select[name=export_format]').val('csv');
    }
  });
  
  resetExportForm();
  
  $('select[name=save_profile]').val($(this).val()).trigger('change');
  
  if ($(this).val()) {
    $('.deleteProfileExport').attr('disabled', false);
  } else {
    $('.deleteProfileExport').attr('disabled', true);
  }
});

$('body').on('click', '.deleteProfileExport', function(){
  if (!$('select[name=export_profile]').val()) return;
  
  if (window.confirm('<?php echo $_language->get('text_really_delete'); ?>')) {
    $.post('index.php?route=module/universal_import/delete_profile&<?php echo $token; ?>', $('#export-form').serialize(), function(data) {
      if(data.success) {
        $('select[name=export_profile] option:selected').remove();
      } else {
        alert('Error');
      }
    }, 'json');
  }
});

$('select[name=import_type]').trigger('change');

$('body').on('change', 'select[name=save_profile]', function(){
  if ($(this).val() == '') {
    $('input[name=profile_name]').show();
  } else {
    $('input[name=profile_name]').hide();
  }
});

$('#modal-profile').on('shown.bs.modal', function () {
  $('#modal-profile .alert').hide();
});

$('#modal-profile-export').on('shown.bs.modal', function () {
  $('#modal-profile-export .alert').hide();
});

$('#saveProfile').on('click', function (e) {
  e.preventDefault();

  $.ajax({
		url: 'index.php?route=module/universal_import/save_profile&<?php echo $token; ?>',
    type:'POST',
		data: $('#import-form, #profile-form').serialize(),
		dataType: 'json',
		success: function(data){
      if (data.success) {
        $('#profile-form .alert-success span').html(data.success);
        $('#profile-form .alert-success').fadeIn();
        //$('#saveProfile').hide();
      } else if(data.error) {
        $('#profile-form .alert-warning span').html(data.error);
        $('#profile-form .alert-warning').fadeIn();
      }
		}
	});
});

$('#saveExportProfile').on('click', function (e) {
  e.preventDefault();

  $.ajax({
		url: 'index.php?route=module/universal_import/save_profile&<?php echo $token; ?>',
    type:'POST',
		data: $('#export-form, #export-profile-form').serialize(),
		dataType: 'json',
		success: function(data){
      if(data.success) {
        $('#export-profile-form .alert-success span').html(data.success);
        $('#export-profile-form .alert-success').fadeIn();
        //$('#saveProfile').hide();
      } else if(data.error) {
        $('#export-profile-form .alert-warning span').html(data.error);
        $('#export-profile-form .alert-warning').fadeIn();
      }
		}
	});
});

$('body').on('click', '.add-column', function (e) {
  e.preventDefault();
  var element = $(this).closest('.form-group').find('select[name^=column]:not(.subitem)');
  var elCopy = $('select[name="source_columns"]').clone();
  
  //if (element.attr('name') == 'columns[product_option][]') {}
  
  var elContainer = document.createElement('div');
  $(elContainer).addClass('multipleElement');
  
  if ($('input[name="import_filetype"]').val() == 'xml') {
    elCopy.removeAttr('disabled').removeAttr('class').removeAttr('style').attr('name', element.attr('name'));
    
    if ($(this).hasClass('is-subcat')) {
      elCopy.addClass('subitem');
      elCopy.attr('name', element.attr('name').replace('columns[', 'columns[sub_').replace('[]', '[' + (element.length-1) + '][]'));
      $(elContainer).addClass('subitem');
    }
    
    $(elContainer).html(elCopy);
    $(elContainer).appendTo(element.closest('.multipleElement').parent());
    
    $(elContainer).find('select').selectize({create: function(input) {
      return {
        value: input,
        text: input
      }
    }});
  } else {
    elCopy.removeAttr('disabled').removeAttr('style').attr('name', element.attr('name'));
    
    if ($(this).hasClass('is-subcat')) {
      elCopy.addClass('subitem');
      elCopy.attr('name', element.attr('name').replace('columns[', 'columns[sub_').replace('[]', '[' + (element.length-1) + '][]'));
      $(elContainer).addClass('subitem');
    }
    
    $(elContainer).html(elCopy);
    $(elContainer).appendTo(element.closest('.multipleElement').parent());
  }
  
  $(this).closest('.col-sm-1').append('<button title="<?php echo $_language->get('text_remove_column'); ?>" type="button" data-toggle="tooltip" class="btn btn-danger remove-column"><i class="fa fa-minus-circle"></i></button>');
  
  return false;
});

$('body').on('click', '.remove-column', function (e) {
  //if ($('input[name="import_filetype"]').val() == 'xml') {
  //  $(this).parent().parent().find('div.selectize-control').eq($(this).index()).remove();
  //}
  
  $(this).parent().parent().find('div.multipleElement').eq($(this).index()).remove();

  var currentFieldIteration = -1;
  
  $(this).parent().parent().find('select').each(function(i, el) {
    if ($(el).hasClass('subitem')) {
      $(el).attr('name', 'columns[sub_product_category]['+currentFieldIteration+'][]');
    } else {
      currentFieldIteration++;
    }
  });
  
  $(this).remove();
  $('.tooltip').remove();
});

$('select[name=store]').change(function(){
	document.location = 'index.php?route=module/universal_import&<?php echo $token; ?>&store_id='+$(this).val();
});

/* extra functions handler */
$('body').on('click', '.add-function', function (e) {
  var element = $('#extraFuncsSrc .xfn-'+$('#extra_func_select').val());
  element.clone().insertBefore($(this).parent().parent());
  $('#extraFuncs [disabled]').removeAttr('disabled');
  var xfnRow = $('#extraFuncs tr[class^="xfn-"]').length;
  $('#extraFuncs [name^="extra_func[]"]').each(function(i) {
    $(this).attr('name', $(this).attr('name').replace('extra_func[]', 'extra_func['+xfnRow+']'));
  });
  
  $('#extraFuncs tr[class^="xfn-"]:last .selectize').selectize({create: function(input) {
  return {
    value: input,
    text: input
  }
  }});
  
  $("#extraFuncs").tableDnDUpdate();
});

$('body').on('click', '.remove-function', function (e) {
  $(this).parent().parent().remove();
  $('.tooltip').remove();
});

// Export
//$('#exportContainer').tooltip({selector: '[data-toggle="tooltip"]'});
function resetExportForm() {
  if ($('select[name=export_type]').val() == 'backup') {
    $('select[name=export_format] option').hide();
    $('select[name=export_format] option[value=sql]').show();
    $('select[name=export_format]').val('sql');
  } else {
    $('select[name=export_format] option').show();
    $('select[name=export_format] option[value=sql]').hide();
    $('select[name=export_format]').val('csv');
  }
  
  $('#exportProgress').hide();
  $('#exportContainer').html('<div style="text-align:center"><img src="view/universal_import/img/loader.gif" alt=""/></div>');
  
  $.post('index.php?route=module/universal_import/export_form&<?php echo $token; ?>', $('#export-form').serialize(), function(data) {
    //$('#exportContainer').hide().html(data).slideDown();
    $('#exportContainer').fadeOut('fast', function(){$('#exportContainer').html(data).slideDown();});
    $.each( $('#exportContainer input.switch'), function(){ $(this).prettyCheckable(); });
    //$.each( $('#exportContainer [data-toggle="tooltip"]'), function(){ $(this).tooltip(); });
  });
  
  $('.startExportProcess').attr('disabled', false);
};

$('body').on('change', 'select[name=export_type]', function(){
  $('.export-type-hide').hide();
  $('.export-type-show-'+$(this).val()).show();
  $('select[name=export_profile]').val('');
  
  resetExportForm();
});

$('select[name=export_type]').trigger('change');

$('body').on('change', 'select[name=export_format]', function(){
  $('*[class^="show_export_"]').hide();
  $('.show_export_'+$(this).val()).show();
});

$('select[name=export_format]').trigger('change');

$('body').on('change keyup', 'input[name=filter_interval]', function(){
  $('.intervalValue').load('index.php?route=module/universal_import/get_time_interval&<?php echo $token; ?>', {filter_interval: $(this).val()});
});

function getTotalExportCount() {
  $('.export_number').html('<i class="fa fa-refresh fa-spin"></i>');
  
  $.post('index.php?route=module/universal_import/export_count&<?php echo $token; ?>', $('#export-form').serialize(), function(data) {
    $('.export_number').html(data);
    if (data == 0) {
      $('.startExportProcess').attr('disabled', true);
    } else {
      $('.startExportProcess').attr('disabled', false);
    }
  });
}
  
$('body').on('change', '.filters select', function(){getTotalExportCount();});
$('body').on('keyup', '.filters input', function(){getTotalExportCount();});

var pauseExportProcess = 1;
function processExportQueue(start) {
  $.ajax({
		url: 'index.php?route=module/universal_import/process_export&start='+start+'&<?php echo $token; ?>',
    type: 'POST',
		data: $('#export-form').serialize(),
		dataType: 'json',
		success: function(data){
      if(data.success) {
        $('#exportProgress .progress-bar').css('width',data.progress + '%').html(data.progress + ' %');
        
        if (!pauseExportProcess && !data.finished) {
          processExportQueue(data.processed);
        } else {
          $('#exportProgress .progress-bar').removeClass('active');
          
          if (data.finished) {
            $('.pauseExportProcess').hide();
            document.location = 'index.php?route=module/universal_import/get_export&file='+data.file+'&<?php echo $token; ?>'
          }
        }
      }
		},
    error: function(xhr) {
      alert(xhr.responseText);
    }
	});
}


$('.startExportProcess').click(function() {
  $('#exportProgress').show();
  $('#exportProgress .progress-bar').addClass('active');
  
  $('#exportProgress .progress-bar').css('min-width', '2em').html('0 %');
  
  pauseExportProcess = 0;
  processExportQueue('init');
});

$('.pauseExportProcess').click(function() {
  $('.pauseExportProcess').hide();
  $('.startExportProcess').show();
  pauseExportProcess = 1;
});
--></script>
<style>
.selectize-input{padding: 7px 12px;}
.selectize-control + .selectized + .selectize-control{margin-top:1px;}
</style>
<?php echo $footer; ?>