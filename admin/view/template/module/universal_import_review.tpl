<?php
if (isset($vqmod)) {
  if (function_exists('modification')) {
    include($vqmod->modCheck(modification(DIR_TEMPLATE.'module/universal_import_functions.tpl')));
  } else {
    include($vqmod->modCheck(DIR_TEMPLATE.'module/universal_import_functions.tpl'));
  }
} else if (class_exists('VQMod')) {
  if (function_exists('modification')) {
    include(VQMod::modCheck(modification(DIR_TEMPLATE.'module/universal_import_functions.tpl')));
  } else {
    include(VQMod::modCheck(DIR_TEMPLATE.'module/universal_import_functions.tpl'));
  }
} else {
  if (function_exists('modification')) {
    include(modification(DIR_TEMPLATE.'module/universal_import_functions.tpl'));
  } else {
    include(DIR_TEMPLATE.'module/universal_import_functions.tpl');
  }
}
?>
  <select class="form-control" name="source_columns" disabled="disabled" style="display:none">
    <option value=""><?php echo $_language->get('text_ignore'); ?></option>
    <?php foreach ($columns as $key => $row) { ?>
      <option value="<?php echo $key; ?>"><?php echo $row; ?></option>
    <?php } ?>
    <?php if (!empty($profile['extra_fields'])) { ?>
      <?php foreach (explode(',', $profile['extra_fields']) as $i => $extra_field_name) { ?>
        <option value="__extra_field_<?php echo $i; ?>"><?php echo trim($extra_field_name); ?></option>
      <?php } ?>
    <?php } ?>
  </select>
  
  <ul class="nav nav-tabs">
    <li class="active"><a href="#tab-general" data-toggle="tab"><?php echo $_language->get('text_type_review'); ?></a></li>
    <li class="pull-right"><a href="#tab-functions" data-toggle="tab"><?php echo $_language->get('tab_functions'); ?></a></li>
  </ul>
  <div class="tab-content alternateColors">
    <div class="tab-pane active" id="tab-general">
      <?php if (isset($profile['item_identifier']) && !in_array($profile['item_identifier'], array('review_id'))) { ?>
      <div class="well quickUpdateWell">
        <h4><?php echo $_language->get('import_product_review'); ?></h4>
        <p><?php echo $_language->get('text_import_product_review'); ?></p>
        <?php
          if (!isset($profile['item_identifier'])) {
            echo 'Please select an item identifier in step 2';
          } else {
            if ($profile['item_identifier'] == $type.'_id') {
              dataField($type.'_id', $_language->get('entry_'.$type.'_id'), $columns, $profile, $_language);
            } else {
              dataField($profile['item_identifier'], $_language->get('entry_'.$profile['item_identifier']), $columns, $profile, $_language);
            }
          }
        ?>
        <?php /*
        <div class="form-group">
          <?php inputField('delete_reviews', $profile, $_language, 'radio', null, 0); ?>
        </div>
        */ ?>
      </div>
      <?php } ?>
      
      <?php if (isset($profile['item_identifier']) && $profile['item_identifier'] == $type.'_id') dataField($type.'_id', $_language->get('entry_'.$type.'_id'), $columns, $profile, $_language); ?>

      <?php dataField('customer_id', $_language->get('entry_customer'), $columns, $profile, $_language); ?>
      <?php dataField('author', $_language->get('entry_author'), $columns, $profile, $_language); ?>
      <?php dataField('text', $_language->get('entry_text'), $columns, $profile, $_language); ?>
      <?php dataField('rating', $_language->get('entry_rating'), $columns, $profile, $_language); ?>
      <?php dataField('date_added', $_language->get('entry_date_added'), $columns, $profile, $_language); ?>
      <?php dataField('status', $_language->get('entry_date_added'), $columns, $profile, $_language, 'enabled'); ?>
    
    </div>
    
    <div class="tab-pane" id="tab-functions">
    
      <ul class="nav nav-pills nav-stacked col-md-2">
        <li class="active"><a href="#tab-extra-func-1" data-toggle="pill"><?php echo $_language->get('tab_functions'); ?></a></li>
        <li><a href="#tab-extra-func-2" data-toggle="pill"><?php echo $_language->get('tab_extra'); ?></a></li>
        <li><a href="#tab-extra-func-4" data-toggle="pill"><?php echo $_language->get('tab_disable_cfg'); ?></a></li>
      </ul>
      <div class="tab-content col-md-10" style="min-height:400px;padding-bottom:120px">
        <div class="tab-pane active" id="tab-extra-func-1">
          <?php extraImportFunctions($columns, $profile, $_language, $languages, get_defined_vars()); ?>
        </div>
        <div class="tab-pane" id="tab-extra-func-2"><?php extraCustomFields($columns, $profile, $_language, $languages, get_defined_vars()); ?></div>
        <div class="tab-pane" id="tab-extra-func-4"><?php extraDisableConfig($columns, $profile, $_language, $languages, get_defined_vars()); ?></div>
      </div>
    </div>
    
  </div>
  
  <hr />

  <div class="pull-right">
    <button type="button" class="btn btn-default cancel" data-step="3"><i class="fa fa-reply"></i> <?php echo $_language->get('text_previous_step'); ?></button>
    <button type="button" class="btn btn-success submit" data-step="3"><i class="fa fa-check"></i> <?php echo $_language->get('text_next_step'); ?></button>
  </div>

<div class="spacer"></div>

<script type="text/javascript">
$('select[name=option_identifier]').trigger('change');
</script>