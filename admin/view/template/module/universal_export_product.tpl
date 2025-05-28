  <?php include 'universal_export_functions.tpl'; ?>
  
  <fieldset class="filters"><legend><div class="pull-right" style="font-size:13px; color:#666"><?php echo $_language->get('total_export_items'); ?> <span class="export_number badge clearblue"></span></div><?php echo $_language->get('export_filters'); ?></legend>
  
    <div class="row">
      <div class="col-sm-4">
        <div class="form-group">
          <?php fieldLabel('filter_language', $_language); ?>
          <div class="input-group">
          <span class="input-group-addon"><i class="fa fa-fw fa-flag"></i></span>
          <select class="form-control" name="filter_language">
            <option value=""><?php echo $_language->get('export_all'); ?></option>
            <?php foreach ($languages as $language) { ?>
              <option value="<?php echo $language['language_id']; ?>" <?php if (isset($profile['filter_language']) && $profile['filter_language'] !== '' && $profile['filter_language'] == $language['language_id']) echo 'selected'; ?>><?php echo $language['name']; ?></option>
            <?php } ?>
          </select>
          </div>
        </div>
      </div>
      <div class="col-sm-4">
        <div class="form-group">
          <?php fieldLabel('filter_store', $_language); ?>
          <div class="input-group">
          <span class="input-group-addon"><i class="fa fa-fw fa-home"></i></span>
          <select class="form-control" name="filter_store">
            <option value=""><?php echo $_language->get('export_all'); ?></option>
            <?php foreach ($stores as $store) { ?>
              <option value="<?php echo $store['store_id']; ?>" <?php if (isset($profile['filter_store']) && $profile['filter_store'] !== '' && $profile['filter_store'] == $store['store_id']) echo 'selected'; ?>><?php echo $store['name']; ?></option>
            <?php } ?>
          </select>
          </div>
        </div>
      </div>
      <div class="col-sm-4">
        <div class="form-group">
          <?php fieldLabel('filter_limit', $_language); ?>
          <div class="input-group">
          <span class="input-group-addon"><?php echo $_language->get('filter_limit_start'); ?></span>
          <input type="text" class="form-control" name="filter-start" value="<?php echo isset($profile['filter-start']) ? $profile['filter-start'] : ''; ?>" placeholder="" />
          <span class="input-group-addon"><?php echo $_language->get('filter_limit_limit'); ?></span>
          <input type="text" class="form-control" name="filter-limit" value="<?php echo isset($profile['filter-limit']) ? $profile['filter-limit'] : ''; ?>" placeholder=""/>
          </div>
        </div>
      </div>
    </div>
    
    <hr class="dotted" style="margin:0"/>
    
    <div class="row">
      <div class="col-sm-4">
        <div class="form-group">
          <?php fieldLabel('filter_status', $_language); ?>
          <div class="input-group">
          <span class="input-group-addon"><i class="fa fa-fw fa-check"></i></span>
          <select class="form-control" name="filter_status">
            <option value=""><?php echo $_language->get('export_all'); ?></option>
            <option value="1" <?php if (isset($profile['filter_status']) && $profile['filter_status'] !== '' && $profile['filter_status'] == 1) echo 'selected'; ?>><?php echo $_language->get('text_enabled'); ?></option>
            <option value="0" <?php if (isset($profile['filter_status']) && $profile['filter_status'] !== '' && $profile['filter_status'] == 0) echo 'selected'; ?>><?php echo $_language->get('text_disabled'); ?></option>
          </select>
          </div>
        </div>
      </div>
      <?php /*
      <div class="col-sm-4">
        <div class="form-group">
          <?php fieldLabel('filter_quantity', $_language); ?>
          <div class="input-group">
          <span class="input-group-addon"><i class="fa fa-fw fa-archive"></i></span>
            <input type="text" class="form-control" name="filter_quantity" value="<?php echo isset($profile['filter_quantity']) ? $profile['filter_quantity'] : ''; ?>" placeholder="1"/>
          </div>
        </div>
      </div>
      */ ?>
      <div class="col-sm-4">
        <div class="form-group">
          <?php fieldLabel('filter_quantity', $_language); ?>
          <div class="input-group">
          <span class="input-group-addon"><?php echo $_language->get('filter_min'); ?></span>
          <input type="text" class="form-control" name="filter_quantity" value="<?php echo isset($profile['filter_quantity']) ? $profile['filter_quantity'] : ''; ?>" placeholder="" />
          <span class="input-group-addon"><?php echo $_language->get('filter_max'); ?></span>
          <input type="text" class="form-control" name="filter_quantity_max" value="<?php echo isset($profile['filter_quantity_max']) ? $profile['filter_quantity_max'] : ''; ?>" placeholder=""/>
          </div>
        </div>
      </div>
      <div class="col-sm-4">
        <div class="form-group">
          <?php fieldLabel('filter_price', $_language); ?>
          <div class="input-group">
          <span class="input-group-addon"><?php echo $_language->get('filter_min'); ?></span>
          <input type="text" class="form-control" name="filter_price_min" value="<?php echo isset($profile['filter_price_min']) ? $profile['filter_price_min'] : ''; ?>" placeholder="" />
          <span class="input-group-addon"><?php echo $_language->get('filter_max'); ?></span>
          <input type="text" class="form-control" name="filter_price_max" value="<?php echo isset($profile['filter_price_max']) ? $profile['filter_price_max'] : ''; ?>" placeholder=""/>
          </div>
        </div>
      </div>
    </div>
    
    <hr class="dotted" style="margin:0"/>
    
    <div class="row">
      <div class="col-sm-4">
        <div class="form-group">
          <?php fieldLabel('filter_category', $_language); ?>
          <div class="input-group">
          <span class="input-group-addon"><i class="fa fa-fw fa-folder-open-o"></i></span>
          <select class="selectize_category" name="filter_category[]" multiple="multiple">
            <option value=""><?php echo $_language->get('export_all'); ?></option>
            <?php foreach ($categories as $category) { ?>
              <option value="<?php echo $category['category_id']; ?>" <?php if (isset($profile['filter_category']) && in_array($category['category_id'], $profile['filter_category'])) echo 'selected'; ?>><?php echo $category['name']; ?></option>
            <?php } ?>
          </select>
          </div>
        </div>
      </div>
      <div class="col-sm-4">
        <div class="form-group">
          <?php fieldLabel('filter_manufacturer', $_language); ?>
          <div class="input-group">
          <span class="input-group-addon"><i class="fa fa-fw fa-industry"></i></span>
          <select class="selectize_category" name="filter_manufacturer[]" multiple="multiple">
            <option value=""><?php echo $_language->get('export_all'); ?></option>
            <?php foreach ($manufacturers as $manufacturer) { ?>
              <option value="<?php echo $manufacturer['manufacturer_id']; ?>" <?php if (isset($profile['filter_manufacturer']) && in_array($manufacturer['manufacturer_id'], $profile['filter_manufacturer'])) echo 'selected'; ?>><?php echo $manufacturer['name']; ?></option>
            <?php } ?>
          </select>
          </div>
        </div>
      </div>
      <div class="col-sm-4">
        <div class="form-group">
          <?php fieldLabel('filter_label', $_language); ?>
          <div class="input-group">
          <span class="input-group-addon"><i class="fa fa-fw fa-tag"></i></span>
          <select class="selectize_category" name="filter_import_batch[]" multiple="multiple">
            <option value=""><?php echo $_language->get('export_all'); ?></option>
            <?php foreach ($labels as $label) { ?>
              <option value="<?php echo $label; ?>" <?php if (isset($profile['filter_import_batch']) && in_array($label, $profile['filter_import_batch'])) echo 'selected'; ?>><?php echo $label; ?></option>
            <?php } ?>
          </select>
          </div>
        </div>
      </div>
    </div>
    
    <hr class="dotted" style="margin:0"/>
    
      <!--div class="col-sm-4">
        <div class="form-group">
          <?php fieldLabel('filter_name', $_language); ?>
          <div class="input-group">
          <span class="input-group-addon"><i class="fa fa-fw fa-folder-open-o"></i></span>
          <input type="text" class="form-control" name="filter_name" placeholder=""/>
          </div>
        </div>
      </div-->
    <div class="row">
      <div class="col-sm-4">
        <div class="form-group">
          <?php fieldLabel('filter_model', $_language); ?>
          <div class="input-group">
          <span class="input-group-addon"><i class="fa fa-fw fa-asterisk"></i></span>
          <input type="text" class="form-control" name="filter_model" value="<?php echo isset($profile['filter_model']) ? $profile['filter_model'] : ''; ?>" placeholder=""/>
          </div>
        </div>
      </div>
      <div class="col-sm-4">
        <div class="form-group">
          <?php fieldLabel('filter_tag', $_language); ?>
          <div class="input-group">
          <span class="input-group-addon"><i class="fa fa-fw fa-tags"></i></span>
          <input type="text" class="form-control" name="filter_tag" value="<?php echo isset($profile['filter_tag']) ? $profile['filter_tag'] : ''; ?>" placeholder=""/>
          </div>
        </div>
      </div>
    </div>
  
  </fieldset>
  
  <fieldset><legend><?php echo $_language->get('export_options'); ?></legend>
    <div class="row">
      <div class="col-sm-4">
        <div class="form-group">
          <?php fieldLabel('param_price_multiplier', $_language); ?>
          <div class="input-group">
          <span class="input-group-addon"><i class="fa fa-fw fa-dollar"></i></span>
            <input type="text" class="form-control" name="price_multiplier" value="<?php echo isset($profile['price_multiplier']) ? $profile['price_multiplier'] : ''; ?>" placeholder="1.2"/>
          </div>
        </div>
      </div>
      <div class="col-sm-4">
        <div class="form-group">
          <?php fieldLabel('param_image_path', $_language); ?>
          <div class="input-group">
          <span class="input-group-addon"><i class="fa fa-fw fa-image"></i></span>
          <select class="form-control" name="param_image_path">
            <option value="" <?php if (empty($profile['param_image_path'])) echo 'selected'; ?>><?php echo $_language->get('image_path_absolute'); ?></option>
            <option value="1" <?php if (!empty($profile['param_image_path'])) echo 'selected'; ?>><?php echo $_language->get('image_path_relative'); ?></option>
          </select>
          </div>
        </div>
      </div>
      <div class="col-sm-4">
        <div class="form-group">
          <?php fieldLabel('param_image_inline', $_language); ?>
          <div class="input-group">
          <span class="input-group-addon"><i class="fa fa-fw fa-image"></i></span>
          <select class="form-control" name="param_image_inline">
            <option value="" <?php if (empty($profile['param_image_inline'])) echo 'selected'; ?>><?php echo $_language->get('text_image_inline'); ?></option>
            <option value="1" <?php if (!empty($profile['param_image_inline'])) echo 'selected'; ?>><?php echo $_language->get('text_image_inline_cols'); ?></option>
          </select>
          </div>
        </div>
      </div>
    </div>
    <div class="row">
      <div class="col-sm-4">
        <div class="form-group">
          <?php fieldLabel('param_option_row', $_language); ?>
          <div class="input-group">
          <span class="input-group-addon"><i class="fa fa-fw fa-list"></i></span>
          <select class="form-control" name="param_option_row">
            <option value="" <?php if (empty($profile['param_option_row'])) echo 'selected'; ?>><?php echo $_language->get('text_option_row_default'); ?></option>
            <option value="1" <?php if (!empty($profile['param_option_row'])) echo 'selected'; ?>><?php echo $_language->get('text_option_row_option'); ?></option>
          </select>
          </div>
        </div>
      </div>
    </div>
    <div class="row">
      <div class="col-sm-12">
        <div class="form-group">
          <?php fieldLabel('export_fields', $_language); ?>
          <div class="input-group">
          <span class="input-group-addon"><i class="fa fa-fw fa-list"></i></span>
          <select class="selectize_category" name="export_fields[]" multiple="multiple">
            <option value=""><?php echo $_language->get('export_all'); ?></option>
            <?php if (!empty($profile['export_fields'])) foreach ($profile['export_fields'] as $field) { ?>
              <option value="<?php echo $field; ?>" selected><?php echo $field; ?></option>
            <?php } ?>
            <?php foreach ($exportFields as $field) { 
              if (!isset($profile['export_fields']) || (isset($profile['export_fields']) && !in_array($field, $profile['export_fields']))) {
            ?>
              <option value="<?php echo $field; ?>"><?php echo $field; ?></option>
            <?php }} ?>
          </select>
          </div>
        </div>
      </div>
    </div>
  </fieldset>

<div class="spacer"></div>

  <?php include 'universal_export_common.tpl'; ?>
  
<script type="text/javascript">
var $selectize_category = $('.selectize_category').selectize({
  maxItems: null
});

$('select[name=filter_language]').change(function(){
	if ($(this).val()) {
    $('input[name=filter_tag]').removeAttr('disabled');
  } else {
    $('input[name=filter_tag]').attr('disabled', 'disabled');
  }
});
$('select[name=filter_language]').trigger('change');
getTotalExportCount();
</script>