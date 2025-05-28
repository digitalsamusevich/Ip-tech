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
    <li class="active"><a href="#tab-data" data-toggle="tab"><?php echo $_language->get('tab_total'); ?></a></li>
    <li><a href="#tab-product" data-toggle="tab"><?php echo $_language->get('tab_product'); ?></a></li>
    <li class="pull-right"><a href="#tab-functions" data-toggle="tab"><?php echo $_language->get('tab_functions'); ?></a></li>
  </ul>
  
  <div class="tab-content alternateColors">
    
    <div class="tab-pane active" id="tab-data">
      <div class="well quickUpdateWell">
        <h4><?php echo $_language->get('info_identifier_title'); ?></h4>
        <p><?php echo $_language->get('info_identifier'); ?></p>
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
      </div>
      
      <?php dataField('title', $_language->get('entry_title'), $columns, $profile, $_language); ?>
      <?php dataField('code', $_language->get('entry_total_code'), $columns, $profile, $_language); ?>
      <?php dataField('value', $_language->get('entry_total'), $columns, $profile, $_language); ?>

    </div>
    
    <div class="tab-pane" id="tab-product">
    
      <?php dataField('name', $_language->get('entry_product_name'), $columns, $profile, $_language); ?>
      <?php dataField('model', $_language->get('entry_model'), $columns, $profile, $_language); ?>
      <?php dataField('quantity', $_language->get('entry_quantity'), $columns, $profile, $_language, 'text'); ?>
      <?php dataField('price', $_language->get('entry_price'), $columns, $profile, $_language); ?>
      <?php dataField('tax', $_language->get('entry_tax'), $columns, $profile, $_language, 'text'); ?>
      <?php dataField('total', $_language->get('entry_total'), $columns, $profile, $_language); ?>
      <?php dataField('reward', $_language->get('entry_reward'), $columns, $profile, $_language); ?>

    </div>
    
    <div class="tab-pane" id="tab-functions">
    
      <ul class="nav nav-pills nav-stacked col-md-2">
        <li class="active"><a href="#tab-extra-func-1" data-toggle="pill"><?php echo $_language->get('tab_functions'); ?></a></li>
        <li><a href="#tab-extra-func-2" data-toggle="pill"><?php echo $_language->get('tab_extra'); ?></a></li>
        <li><a href="#tab-extra-func-3" data-toggle="pill"><?php echo $_language->get('tab_cat_binding'); ?></a></li>
        <li><a href="#tab-extra-func-4" data-toggle="pill"><?php echo $_language->get('tab_disable_cfg'); ?></a></li>
      </ul>
      <div class="tab-content col-md-10" style="min-height:400px;padding-bottom:120px">
        <div class="tab-pane active" id="tab-extra-func-1">
          <?php extraImportFunctions($columns, $profile, $_language, $languages, get_defined_vars()); ?>
        </div>
        <div class="tab-pane" id="tab-extra-func-2"><?php extraCustomFields($columns, $profile, $_language, $languages, get_defined_vars()); ?></div>
        <div class="tab-pane" id="tab-extra-func-3">
          <div class="well infowell">
            <h4><i class="fa fa-fw fa-caret-right"></i> <?php echo $_language->get('tab_cat_binding'); ?></h4>
            <div style="display:none"><?php echo $_language->get('info_cat_binding'); ?></div>
          </div>
          
          <div class="form-group">
            <label class="col-sm-2 control-label"><?php echo $_language->get('entry_cat_binding_mode'); ?></label>
            <div class="col-md-10">
              <select name="col_binding_mode" class="form-control">
                <option value="" <?php if (empty($profile['col_binding_mode'])) echo 'selected="selected"'; ?>><?php echo $_language->get('text_cat_binding_default'); ?></option>
                <option value="1" <?php if (isset($profile['col_binding_mode']) && $profile['col_binding_mode'] == '1') echo 'selected="selected"'; ?>><?php echo $_language->get('text_cat_binding_exclusive'); ?></option>
                <option value="2" <?php if (isset($profile['col_binding_mode']) && $profile['col_binding_mode'] == '2') echo 'selected="selected"'; ?>><?php echo $_language->get('text_cat_binding_skip'); ?></option>
              </select>
            </div>
          </div>
          
          <table id="categoryBinding" class="table table-bordered">
            <thead>
              <tr>
                <th style="width:45%;"><?php echo $_language->get('text_catbinding_name'); ?></th>
                <th><?php echo $_language->get('text_catbinding_bind_to'); ?></th>
                <th style="width:55px;"></th>
              </tr>
            </thead>
            <tbody>
              <?php if(!empty($profile['col_binding'])){ foreach ($profile['col_binding'] as $col_from => $col_to) { ?>
                <tr>
                  <td><input type="hidden" name="col_binding_names[<?php echo $col_from; ?>]" value="<?php echo isset($profile['col_binding_names'][$col_from]) ? $profile['col_binding_names'][$col_from] : ''; ?>"/><?php echo isset($profile['col_binding_names'][$col_from]) ? $profile['col_binding_names'][$col_from] : ''; ?></td>
                  <td>
                    <select name="col_binding[<?php echo $col_from; ?>][]" class="catBindSelect" multiple>
                      <?php foreach((array) $col_to as $val) { ?>
                      <option value="<?php echo $val; ?>" selected></option>
                      <?php } ?>
                    </select>
                  </td>
                  <td><button title="<?php echo $_language->get('text_remove_function'); ?>" type="button" data-toggle="tooltip" class="btn btn-danger remove-function"><i class="fa fa-minus-circle"></i></button></td>
                </tr>
              <?php }} else { ?>
                <tr><td colspan="3" class="text-center"><?php echo $_language->get('text_no_bindings'); ?></td></tr>
              <?php } ?>
              <tr>
                <td colspan="3" style="text-align:center" class="form-inline">
                  <button type="button" class="btn btn-success get-bindings"><i class="fa fa-refresh"></i> <?php echo $_language->get('text_get_bindings'); ?></button>
                </td>
              </tr>
            </tbody>
            
          </table>
        </div>
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

<script type="text/javascript"><!--
<?php if ($filetype == 'xml') { ?>
$('select[name^="columns"]').removeClass('form-control');
$('select[name^="columns"]').selectize({create: function(input) {
  return {
    value: input,
    text: input
  }
}});
<?php } ?>
--></script>