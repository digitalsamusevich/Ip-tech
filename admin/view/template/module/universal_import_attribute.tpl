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
    <li class="active"><a href="#tab-general" data-toggle="tab"><?php echo $_language->get('text_type_attribute'); ?></a></li>
    <li class="pull-right"><a href="#tab-functions" data-toggle="tab"><?php echo $_language->get('tab_functions'); ?></a></li>
  </ul>
  <div class="tab-content alternateColors">
    <div class="tab-pane active" id="tab-general">
      <?php if (isset($profile['item_identifier']) && !in_array($profile['item_identifier'], array('attribute_id', 'attribute_name'))) { ?>
      <div class="well quickUpdateWell">
        <h4><?php echo $_language->get('import_product_attribute'); ?></h4>
        <p><?php echo $_language->get('text_import_product_attributes'); ?></p>
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
          <?php inputField('delete_attributes', $profile, $_language, 'radio', null, 0); ?>
        </div>
        */ ?>
        <div class="form-group" style="margin-bottom:0">
          <label class="col-sm-2 control-label"><span data-toggle="tooltip" title="<?php echo $_language->get('entry_insert_method_i'); ?>"><?php echo $_language->get('entry_insert_method'); ?></span></label>
          <div class="col-md-4">
            <select name="attr_insert_type" class="form-control">
              <?php foreach (array('', 'update', 'add', 'rm_add') as $insert_type) { ?>
              <option value="<?php echo $insert_type; ?>" <?php if (isset($profile['option_insert_type']) && $profile['option_insert_type'] == $insert_type) echo 'selected="selected"'; ?>><?php echo $_language->get('entry_insert_method_'.$insert_type); ?></option>
              <?php } ?>
            </select>
          </div>
        </div>
      </div>
      <?php } ?>
      
      <?php
        if (isset($profile['item_identifier']) && !in_array($profile['item_identifier'], array('attribute_id', 'attribute_name'))) { 
          dataField($type.'_id', $_language->get('entry_'.$type.'_id'), $columns, $profile, $_language);
        } else if (isset($profile['item_identifier']) && $profile['item_identifier'] == $type.'_id') {
          dataField($type.'_id', $_language->get('entry_'.$type.'_id'), $columns, $profile, $_language);
          dataField($type.'_group_id', $_language->get('entry_'.$type.'_group_id'), $columns, $profile, $_language);
        }
      ?>
      <?php dataFieldML('group', $_language->get('text_attribute_group'), $columns, $profile, $_language, $languages, 'attribute'); ?>
      <?php dataFieldML('name', $_language->get('text_attribute_name'), $columns, $profile, $_language, $languages, 'attribute'); ?>
      
      <?php if (isset($profile['item_identifier']) && !in_array($profile['item_identifier'], array('attribute_id', 'attribute_name'))) { ?>
      <?php dataFieldML('value', $_language->get('text_attribute_value'), $columns, $profile, $_language, $languages, 'attribute'); ?>
      <?php } ?>
      
      <?php dataField('sort_order', $_language->get('entry_sort_order'), $columns, $profile, $_language, 'text'); ?>
    
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

<?php if (in_array($filetype, array('xml', 'json'))) { ?>
$('select[name^="columns"]').removeClass('form-control');
$('select[name^="columns"]').selectize({create: function(input) {
  return {
    value: input,
    text: input
  }
}});
<?php } ?>
</script>