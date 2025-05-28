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
    <li class="active"><a href="#tab-general" data-toggle="tab"><?php echo $_language->get('tab_quick_update'); ?></a></li>
    <li class="pull-right"><a href="#tab-functions" data-toggle="tab"><?php echo $_language->get('tab_functions'); ?></a></li>
  </ul>
  <div class="tab-content alternateColors">
    <div class="tab-pane active" id="tab-general">
      <div class="well quickUpdateWell">
        <h4><?php echo $_language->get('tab_quick_update'); ?></h4>
        <p><?php echo $_language->get('text_quick_update_identifier'); ?></p>
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
        <?php if (!empty($product_options)) { ?>
        <p><?php echo $_language->get('text_quick_update_option_identifier'); ?></p>
        <div class="form-group">
          <label class="col-sm-2 control-label" style="color:#666"><span data-toggle="tooltip" title="<?php echo $_language->get('entry_option_identifier_i'); ?>"><?php echo $_language->get('entry_option_identifier'); ?></span></label>
          <div class="col-sm-4">
            <select class="form-control" name="option_identifier">
              <option value=""></option>
              <optgroup label="<?php echo $_language->get('text_identify_option_type'); ?>">
              <?php foreach ($product_options as $prod_option) { ?>
              <option value="<?php echo $prod_option['option_id']; ?>" <?php if (isset($profile['option_identifier']) && $profile['option_identifier'] == $prod_option['option_id']) echo 'selected'; ?>><?php echo $prod_option['name']; ?></option>
              <?php } ?>
              </optgroup>
              <optgroup label="<?php echo $_language->get('text_identify_option_field'); ?>">
                <?php foreach ($extra_option_identifiers as $prod_option) { ?>
                <option value="_<?php echo $prod_option; ?>" <?php if (isset($profile['option_identifier']) && $profile['option_identifier'] == '_'.$prod_option) echo 'selected'; ?>><?php echo $prod_option; ?></option>
                <?php } ?>
              </optgroup>
            </select>
          </div>
          <label class="col-sm-2 control-label" style="color:#666"><?php echo $_language->get('entry_option_identifier_value'); ?></label>
          <div class="col-sm-4">
            <?php dataField('option_identifier_value', false, $columns, $profile, $_language); ?>
          </div>
        </div>
        <?php } ?>
      </div>
      
        <?php dataField('price', $_language->get('entry_price'), $columns, $profile, $_language); ?>
        
      <div class="hideForOptionUpdate">
        <?php dataField('product_discount', $_language->get('tab_discount'), $columns, $profile, $_language, false, false, true, true); ?>
        <div class="col-sm-5">
          <div class="row" style="padding-bottom:15px">
            <label class="col-sm-3 control-label"><span data-toggle="tooltip" title="<?php echo $_language->get('entry_insert_method_i'); ?>"><?php echo $_language->get('entry_insert_method'); ?></span></label>
            <div class="col-md-9">
              <select name="discount_insert_type" class="form-control">
                <?php foreach (array('', 'update', 'add', 'rm_add') as $insert_type) { ?>
                <option value="<?php echo $insert_type; ?>" <?php if (isset($profile['special_insert_type']) && $profile['special_insert_type'] == $insert_type) echo 'selected="selected"'; ?>><?php echo $_language->get('entry_insert_method_'.$insert_type); ?></option>
                <?php } ?>
              </select>
            </div>
          </div>
        </div>
      </div>
        
        <div class="form-group">
          <label class="col-sm-2 control-label"><span data-toggle="tooltip" title="<?php echo $_language->get('entry_advanced_discount_i'); ?>"><?php echo $_language->get('entry_advanced_discount'); ?></span></label>
          <div class="col-sm-10">
            <table id="attributeFields" class="table table-bordered condensedFieldsTable">
              <thead>
                <tr>
                  <th style="width:20%;"><?php echo $_language->get('text_customer_group'); ?></th>
                  <th><?php echo $_language->get('text_optbinding_bind_to'); ?></th>
                </tr>
              </thead>
              <tbody>
              <?php foreach($customer_groups as $group_id => $group_name) { ?>
                <tr>
                  <td><?php echo $group_name; ?></td>
                  <td>
                    <?php foreach (array('price', 'quantity', 'priority', 'date_start', 'date_end') as $attribute_type) { ?>
                      <?php dataField('discountByCustomerGroup]['.$group_id.']['.$attribute_type, $_language->get('entry_'.$attribute_type), $columns, $profile, $_language, 'text', false, false, false); ?>
                    <?php } ?>
                  </td>
                </tr>
              <?php } ?>
              </tbody>
            </table>
          </div>
        </div>
        
        <?php dataField('product_special', $_language->get('tab_special'), $columns, $profile, $_language, false, false, true, true); ?>
        <div class="col-sm-5">
          <div class="row" style="padding-bottom:15px">
            <label class="col-sm-3 control-label"><span data-toggle="tooltip" title="<?php echo $_language->get('entry_insert_method_i'); ?>"><?php echo $_language->get('entry_insert_method'); ?></span></label>
            <div class="col-md-9">
              <select name="special_insert_type" class="form-control">
                <?php foreach (array('', /*'update',*/'add', 'rm_add') as $insert_type) { ?>
                <option value="<?php echo $insert_type; ?>" <?php if (isset($profile['special_insert_type']) && $profile['special_insert_type'] == $insert_type) echo 'selected="selected"'; ?>><?php echo $_language->get('entry_insert_method_'.$insert_type); ?></option>
                <?php } ?>
              </select>
            </div>
          </div>
          <?php /*
          <table id="attributeFields" class="table table-bordered">
            <thead>
              <tr>
                <th style="width:20%;"><?php echo $_language->get('text_optbinding_name'); ?></th>
                <th><?php echo $_language->get('text_default'); ?></th>
              </tr>
            </thead>
            <tbody>
            <tr>
              <td><?php echo $_language->get('entry_date_start'); ?></td>
              <td>
                <input type="text" name="special_date_start" value="<?php echo isset($profile['special_date_start']) ? $profile['special_date_start'] : ''; ?>" class="form-control"/>
              </td>
            </tr>
            <tr>
              <td><?php echo $_language->get('entry_date_end'); ?></td>
              <td>
                <input type="text" name="special_date_end" value="<?php echo isset($profile['special_date_end']) ? $profile['special_date_end'] : ''; ?>" class="form-control"/>
              </td>
            </tr>
            </tbody>
          </table>
          */ ?>
          
        </div>
        
        <div class="form-group">
          <label class="col-sm-2 control-label"><span data-toggle="tooltip" title="<?php echo $_language->get('entry_advanced_special_i'); ?>"><?php echo $_language->get('entry_advanced_special'); ?></span></label>
          <div class="col-sm-10">
            <table id="attributeFields" class="table table-bordered condensedFieldsTable">
              <thead>
                <tr>
                  <th style="width:20%;"><?php echo $_language->get('text_customer_group'); ?></th>
                  <th><?php echo $_language->get('text_optbinding_bind_to'); ?></th>
                </tr>
              </thead>
              <tbody>
              <?php foreach($customer_groups as $group_id => $group_name) { ?>
                <tr>
                  <td><?php echo $group_name; ?></td>
                  <td>
                    <?php foreach (array('price', 'priority', 'date_start', 'date_end') as $attribute_type) { ?>
                      <?php dataField('specialByCustomerGroup]['.$group_id.']['.$attribute_type, $_language->get('entry_'.$attribute_type), $columns, $profile, $_language, 'text', false, false, false); ?>
                    <?php } ?>
                  </td>
                </tr>
              <?php } ?>
              </tbody>
            </table>
          </div>
        </div>
        
      </div>
        
      <hr class="dotted"/>
        
      </div>
      
      <?php /* dataField('quantity', $_language->get('entry_quantity'), $columns, $profile, $_language, 'text'); */ ?>
       
      <div class="row">
        <label class="col-sm-2 control-label"><?php echo $_language->get('entry_quantity'); ?></label>
        <div class="col-sm-4">
          <?php dataField('quantity', false, $columns, $profile, $_language); ?>
        </div>
        <label class="col-sm-1 control-label"><span data-toggle="tooltip" title="<?php echo $_language->get('entry_value_modifier_i'); ?>"><?php echo $_language->get('entry_value_modifier'); ?></span></label>
        <div class="col-sm-2">
          <select class="form-control" name="quantity_modifier">
            <option value="">Replace</option>
            <option value="+" <?php if (isset($profile['quantity_modifier']) && $profile['quantity_modifier'] == '+') echo 'selected'; ?>>Add</option>
            <option value="-" <?php if (isset($profile['quantity_modifier']) && $profile['quantity_modifier'] == '-') echo 'selected'; ?>>Subtract</option>
          </select>
        </div>
        <label class="col-sm-1 control-label"><span data-toggle="tooltip" title="<?php echo $_language->get('entry_default_i'); ?>"><?php echo $_language->get('import_default_value'); ?></span></label>
        <div class="col-sm-2">
          <input type="text" class="form-control" name="defaults[quantity]" value="<?php if (isset($profile['defaults']['quantity'])) echo $profile['defaults']['quantity']; ?>" />
        </div>
      </div>
      
      <hr class="dotted"/>
      
      <div class="form-group" style="margin-bottom:0">
        <label class="col-sm-2 control-label"><?php echo $_language->get('entry_stock_status'); ?></label>
        <div class="col-md-4"><span data-toggle="tooltip" title="<?php echo $_language->get('help_field_stock_status_id'); ?>"><?php dataField('stock_status_id', null, $columns, $profile, $_language); ?></span></div>
        <label class="col-sm-2 control-label"><span data-toggle="tooltip" title="<?php echo $_language->get('entry_default_i'); ?>"><?php echo $_language->get('entry_default'); ?></span></label>
        <div class="col-md-4">
          <select name="defaults[stock_status_id]" class="form-control">
            <option value="" <?php if (isset($profile['defaults']['stock_status_id']) && $profile['defaults']['stock_status_id'] == '') echo 'selected="selected"'; ?>><?php echo $_language->get('text_no_change'); ?></option>
            <?php foreach ($stock_statuses as $stock_status) { ?>
            <option value="<?php echo $stock_status['stock_status_id']; ?>" <?php if (isset($profile['defaults']['stock_status_id']) && $profile['defaults']['stock_status_id'] == $stock_status['stock_status_id']) echo 'selected="selected"'; ?>><?php echo $stock_status['name']; ?></option>
            <?php } ?>
          </select>
        </div>
      </div>
      
      <hr class="dotted"/>
      
      <div class="hideForOptionUpdate">
      <?php dataField('status', $_language->get('entry_status'), $columns, $profile, $_language, 'select',
        array(
          '' => $_language->get('text_no_change'),
          '1' => $_language->get('text_enabled'),
          '0' => $_language->get('text_disabled'),
        ));
      ?>
      </div>
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