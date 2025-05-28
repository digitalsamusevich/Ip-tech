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
    <li class="active"><a href="#tab-data" data-toggle="tab"><?php echo $_language->get('tab_order'); ?></a></li>
    <li><a href="#tab-customer" data-toggle="tab"><?php echo $_language->get('tab_customer'); ?></a></li>
    <li><a href="#tab-payment" data-toggle="tab"><?php echo $_language->get('tab_payment'); ?></a></li>
    <li><a href="#tab-shipping" data-toggle="tab"><?php echo $_language->get('tab_shipping'); ?></a></li>
    <li><a href="#tab-info" data-toggle="tab"><?php echo $_language->get('tab_info'); ?></a></li>
    
    <li class="pull-right"><a href="#tab-functions" data-toggle="tab"><?php echo $_language->get('tab_functions'); ?></a></li>
  </ul>
  <div class="tab-content alternateColors">
    
    <div class="tab-pane active" id="tab-data">
      <?php if(isset($profile['item_identifier']) && $profile['item_identifier'] == $type.'_id') dataField($type.'_id', $_language->get('entry_'.$type.'_id'), $columns, $profile, $_language); ?>
      
      <?php if ($_config->get('ordIdMan_rand_ord_num')) {
        dataField('order_id_user', 'Random Order ID', $columns, $profile, $_language);
      } ?>
      <?php dataField('invoice_no', $_language->get('entry_invoice_no'), $columns, $profile, $_language); ?>
      <?php dataField('invoice_prefix', $_language->get('entry_invoice_prefix'), $columns, $profile, $_language, 'text'); ?>
      <?php dataField('store_id', $_language->get('entry_store'), $columns, $profile, $_language, 'select', $stores); ?>
      <?php dataField('comment', $_language->get('entry_comment'), $columns, $profile, $_language); ?>
      <?php dataField('total', $_language->get('entry_total'), $columns, $profile, $_language); ?>
      <?php dataField('currency_code', $_language->get('entry_currency'), $columns, $profile, $_language, 'select', $currencies); ?>
      <?php dataField('order_status_id', $_language->get('entry_order_status'), $columns, $profile, $_language, 'select', $order_statuses); ?>
      <?php dataField('date_added', $_language->get('entry_date_added'), $columns, $profile, $_language); ?>
      <?php dataField('date_modified', $_language->get('entry_date_modified'), $columns, $profile, $_language); ?>

    </div>
    
    <div class="tab-pane" id="tab-customer">
      <?php dataField('customer_id', $_language->get('entry_customer_id'), $columns, $profile, $_language); ?>
      <?php dataField('email', $_language->get('entry_email'), $columns, $profile, $_language); ?>
      
      <?php dataField('customer_group_id', $_language->get('entry_customer_group'), $columns, $profile, $_language, 'select', $customer_groups); ?>
      
      <?php foreach ($custom_fields as $custom_field) { ?>
        <?php if ($custom_field['location'] == 'account') { ?>
          <?php dataField('custom_field]['.$custom_field['custom_field_id'], $custom_field['name'], $columns, $profile, $_language); ?>
        <?php } ?>
      <?php } ?>
      
      <?php dataField('firstname', $_language->get('entry_firstname'), $columns, $profile, $_language); ?>
      <?php dataField('lastname', $_language->get('entry_lastname'), $columns, $profile, $_language); ?>
      <?php dataField('telephone', $_language->get('entry_telephone'), $columns, $profile, $_language); ?>
      <?php dataField('fax', $_language->get('entry_fax'), $columns, $profile, $_language); ?>
    </div>
    
    <?php foreach (array('payment', 'shipping') as $i) { ?>
      <div class="tab-pane" id="tab-<?php echo $i; ?>">
        <?php dataField($i.'_method', $_language->get('entry_'.$i.'_method_name'), $columns, $profile, $_language); ?>
        <?php dataField($i.'_code', $_language->get('entry_'.$i.'_method_code'), $columns, $profile, $_language); ?>
        <?php foreach ($custom_fields as $custom_field) { ?>
          <?php if ($custom_field['location'] == 'address') { ?>
            <?php dataField($i.'_custom_field]['.$custom_field['custom_field_id'].'', $custom_field['name'], $columns, $profile, $_language); ?>
          <?php } ?>
        <?php } ?>
        <?php dataField($i.'_firstname', $_language->get('entry_firstname'), $columns, $profile, $_language); ?>
        <?php dataField($i.'_lastname', $_language->get('entry_lastname'), $columns, $profile, $_language); ?>
        <?php dataField($i.'_company', $_language->get('entry_company'), $columns, $profile, $_language); ?>
        <?php dataField($i.'_address_1', $_language->get('entry_address_1'), $columns, $profile, $_language); ?>
        <?php dataField($i.'_address_2', $_language->get('entry_address_2'), $columns, $profile, $_language); ?>
        <?php dataField($i.'_city', $_language->get('entry_city'), $columns, $profile, $_language); ?>
        <?php dataField($i.'_postcode', $_language->get('entry_postcode'), $columns, $profile, $_language); ?>
        <?php dataField($i.'_country', $_language->get('entry_country'), $columns, $profile, $_language); ?>
        <?php dataField($i.'_zone', $_language->get('entry_zone'), $columns, $profile, $_language); ?>
    </div>
    <?php } ?>
    
    <div class="tab-pane" id="tab-info">
      <?php dataField('user_agent', $_language->get('entry_user_agent'), $columns, $profile, $_language); ?>
      <?php dataField('ip', $_language->get('entry_ip'), $columns, $profile, $_language); ?>
      <?php dataField('accept_language', $_language->get('entry_accept_language'), $columns, $profile, $_language); ?>
      
      <div class="form-group">
        <label class="col-sm-2 control-label"><?php echo $_language->get('entry_create_order_total'); ?></label>
        <div class="col-sm-4">
          <input type="radio" class="switch" name="create_order_total" value="1" data-label="<?php echo $_language->get('text_yes'); ?>" <?php if (!empty($profile['create_order_total'])) echo 'checked'; ?>/>
          <input type="radio" class="switch" name="create_order_total" value="0" data-label="<?php echo $_language->get('text_no'); ?>" <?php if (empty($profile['create_order_total'])) echo 'checked'; ?>/>
        </div>
      </div>
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