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
    <?php if (version_compare(VERSION, '3', '>=') || (in_array('complete_seo', $installed_modules) && $_config->get('mlseo_enabled'))) { ?>
      <li><a href="#tab-seo" data-toggle="tab"><?php echo $_language->get('tab_seo'); ?></a></li>
    <?php } ?>
    <li class="pull-right"><a href="#tab-functions" data-toggle="tab"><?php echo $_language->get('tab_functions'); ?></a></li>
  </ul>
  <div class="tab-content alternateColors">
    <div class="tab-pane active" id="tab-general">
      <?php if(isset($profile['item_identifier']) && $profile['item_identifier'] == $type.'_id') dataField($type.'_id', $_language->get('entry_'.$type.'_id'), $columns, $profile, $_language); ?>
      <?php dataField('name', $_language->get('entry_name'), $columns, $profile, $_language); ?>
      <?php dataField('manufacturer_store', $_language->get('entry_store'), $columns, $profile, $_language, 'store', $stores); ?>
      <?php if (in_array('complete_seo', $installed_modules) && $_config->get('mlseo_enabled')) { ?>
      <?php } else if (version_compare(VERSION, '3', '>=')) { ?>
        <?php dataFieldMSML('manufacturer_seo_url', $_language->get('entry_keyword'), $columns, $profile, $_language, $languages, 'manufacturer', $stores); ?>
      <?php } else { ?>
      <?php dataField('keyword', $_language->get('entry_keyword'), $columns, $profile, $_language); ?>
      <?php } ?>
      <?php dataField('image', $_language->get('entry_image'), $columns, $profile, $_language); ?>
      <?php dataField('sort_order', $_language->get('entry_sort_order'), $columns, $profile, $_language, 'text'); ?>
      <?php /*if (in_array('complete_seo', $installed_modules)) { ?>
        <?php dataFieldML('seo_h1', $_language->get('entry_seo_h1'), $columns, $profile, $_language, $languages, $type); ?>
        <?php dataFieldML('meta_title', $_language->get('entry_meta_title'), $columns, $profile, $_language, $languages, $type); ?>
        <?php dataFieldML('meta_description', $_language->get('entry_meta_description'), $columns, $profile, $_language, $languages, $type); ?>
        <?php dataFieldML('meta_keyword', $_language->get('entry_meta_keyword'), $columns, $profile, $_language, $languages, $type); ?>
      <?php }*/ ?>
    </div>
    
    <?php if (in_array('complete_seo', $installed_modules) && $_config->get('mlseo_enabled')) { ?>
    <div class="tab-pane" id="tab-seo">
      <ul class="nav nav-pills nav-stacked col-md-2">
      <?php $first=0; foreach ($stores as $storeId => $storeName) { ?>
        <li<?php if(!$first) { echo ' class="active"'; $first=1;} ?>><a href="#gkd-tab-store-<?php echo $storeId; ?>" data-toggle="pill"><?php echo $storeName; ?></a></li>
      <?php } ?>
      </ul>
      <div class="tab-content col-md-10 clearfix">
        <?php $first=0; foreach ($stores as $storeId => $storeName) { ?>
        <div id="gkd-tab-store-<?php echo $storeId; ?>" class="tab-pane<?php if(!$first) { echo ' active'; $first=1;} ?>">
          <?php dataFieldML('name', $_language->get('entry_name'), $columns, $profile, $_language, $languages, 'manufacturer', $storeId); ?>
          <?php dataFieldML('description', $_language->get('entry_description'), $columns, $profile, $_language, $languages, 'manufacturer', $storeId); ?>
          <?php dataFieldML('seo_keyword', $_language->get('entry_keyword'), $columns, $profile, $_language, $languages, 'manufacturer', $storeId); ?>
          <?php dataFieldML('seo_h1', $_language->get('entry_seo_h1'), $columns, $profile, $_language, $languages, 'manufacturer', $storeId); ?>
          <?php dataFieldML('seo_h2', $_language->get('entry_seo_h2'), $columns, $profile, $_language, $languages, 'manufacturer', $storeId); ?>
          <?php dataFieldML('seo_h3', $_language->get('entry_seo_h3'), $columns, $profile, $_language, $languages, 'manufacturer', $storeId); ?>
          <?php dataFieldML('meta_title', $_language->get('entry_meta_title'), $columns, $profile, $_language, $languages, 'manufacturer', $storeId); ?>
          <?php dataFieldML('meta_description', $_language->get('entry_meta_description'), $columns, $profile, $_language, $languages, 'manufacturer', $storeId); ?>
          <?php dataFieldML('meta_keyword', $_language->get('entry_meta_keyword'), $columns, $profile, $_language, $languages, 'manufacturer', $storeId); ?>
        </div>
      <?php } ?>
      </div>
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