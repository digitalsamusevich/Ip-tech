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
    <li class="active"><a href="#tab-general" data-toggle="tab"><?php echo $_language->get('tab_general'); ?></a></li>
    <?php for ($i=1; $i <= $address_number; $i++) { ?>
    <li><a href="#tab-address-<?php echo $i; ?>" data-toggle="tab"><?php echo $_language->get('tab_address') . ' ' . $i; ?></a></li>
    <?php } ?>
    <li class="pull-right"><a href="#tab-functions" data-toggle="tab"><?php echo $_language->get('tab_functions'); ?></a></li>
  </ul>
  
  <div class="tab-content alternateColors">
    <div class="tab-pane active" id="tab-general">
      <?php if(isset($profile['item_identifier']) && $profile['item_identifier'] == $type.'_id') dataField($type.'_id', $_language->get('entry_'.$type.'_id'), $columns, $profile, $_language); ?>
      <?php dataField('customer_group_id', $_language->get('entry_customer_group'), $columns, $profile, $_language, 'select', $customer_groups); ?>
      
      <?php foreach ($custom_fields as $custom_field) { ?>
        <?php if ($custom_field['location'] == 'account') { ?>
          <?php dataField('custom_field]['.$custom_field['custom_field_id'], $custom_field['name'], $columns, $profile, $_language); ?>
        <?php } ?>
      <?php } ?>
      
      <?php dataField('firstname', $_language->get('entry_firstname'), $columns, $profile, $_language); ?>
      <?php dataField('lastname', $_language->get('entry_lastname'), $columns, $profile, $_language); ?>
      <?php dataField('email', $_language->get('entry_email'), $columns, $profile, $_language); ?>
      <?php dataField('telephone', $_language->get('entry_telephone'), $columns, $profile, $_language); ?>
      <?php dataField('fax', $_language->get('entry_fax'), $columns, $profile, $_language); ?>
      <?php /* dataField('custom_field', $_language->get('entry_custom_field'), $columns, $profile, $_language); */ ?>
      <?php /* dataField('password', $_language->get('entry_password'), $columns, $profile, $_language); */ ?>
      <div class="form-group" style="margin-bottom:0">
        <label class="col-sm-2 control-label"><?php echo $_language->get('entry_password'); ?></label>
        <div class="col-md-4"><?php dataField('password', null, $columns, $profile, $_language); ?></div>
        <label class="col-sm-2 control-label"><span data-toggle="tooltip" title="<?php echo $_language->get('entry_pwd_hash_i'); ?>"><?php echo $_language->get('entry_pwd_hash'); ?></span></label>
        <div class="col-md-4">
          <input type="radio" class="switch" name="pwd_hash" onclick="javascript:$('#pwd_salt').slideUp();"   value="0" data-label="<?php echo $_language->get('text_pwd_clear'); ?>" <?php if (empty($profile['pwd_hash'])) echo 'checked'; ?>/>
          <input type="radio" class="switch" name="pwd_hash" onclick="javascript:$('#pwd_salt').slideDown();" value="1" data-label="<?php echo $_language->get('text_pwd_hash'); ?>" <?php if (!empty($profile['pwd_hash'])) echo 'checked'; ?>/>
        </div>
      </div>
      <hr class="dotted"/>
      <div id="pwd_salt"<?php if (empty($profile['pwd_hash'])) echo ' style="display:none"';?>><?php dataField('salt', $_language->get('entry_salt'), $columns, $profile, $_language); ?></div>
      <?php dataField('newsletter', $_language->get('entry_newsletter'), $columns, $profile, $_language, 'checkbox'); ?>
      <?php dataField('status', $_language->get('entry_status'), $columns, $profile, $_language, 'enabled'); ?>
      <?php dataField('approved', $_language->get('entry_approved'), $columns, $profile, $_language, 'radio'); ?>
      <?php dataField('safe', $_language->get('entry_safe'), $columns, $profile, $_language, 'radio'); ?>
      <?php dataField('ip', 'IP', $columns, $profile, $_language); ?>
    </div>
    
    <?php for ($i=1; $i <= $address_number; $i++) { ?>
      <div class="tab-pane" id="tab-address-<?php echo $i; ?>">
        <?php foreach ($custom_fields as $custom_field) { ?>
          <?php if ($custom_field['location'] == 'address') { ?>
            <?php dataField('address]['.$i.'][custom_field]['.$custom_field['custom_field_id'], $custom_field['name'], $columns, $profile, $_language); ?>
          <?php } ?>
        <?php } ?>
        <?php dataField('address]['.$i.'][firstname', $_language->get('entry_firstname'), $columns, $profile, $_language); ?>
        <?php dataField('address]['.$i.'][lastname', $_language->get('entry_lastname'), $columns, $profile, $_language); ?>
        <?php dataField('address]['.$i.'][company', $_language->get('entry_company'), $columns, $profile, $_language); ?>
        <?php dataField('address]['.$i.'][address_1', $_language->get('entry_address_1'), $columns, $profile, $_language); ?>
        <?php dataField('address]['.$i.'][address_2', $_language->get('entry_address_2'), $columns, $profile, $_language); ?>
        <?php dataField('address]['.$i.'][city', $_language->get('entry_city'), $columns, $profile, $_language); ?>
        <?php dataField('address]['.$i.'][postcode', $_language->get('entry_postcode'), $columns, $profile, $_language); ?>
        <?php dataField('address]['.$i.'][country_id', $_language->get('entry_country'), $columns, $profile, $_language); ?>
        <?php dataField('address]['.$i.'][zone_id', $_language->get('entry_zone'), $columns, $profile, $_language); ?>
        <?php dataField('address]['.$i.'][default', $_language->get('entry_default'), $columns, $profile, $_language, 'radio'); ?>
    </div>
    <?php } ?>
    
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