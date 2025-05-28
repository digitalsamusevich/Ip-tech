<?php $gkhtab = $gkhdiv = 0; ?>
<?php function inputField($name, $profile, $_language, $type = 'text', $values = null, $default = null, $disabled = false, $extra_class = '') { ?>
  <?php if ($_language->get('entry_'.$name.'_i') != 'entry_'.$name.'_i') { ?>
  <label class="col-sm-2 control-label <?php echo $extra_class; ?>"><span data-toggle="tooltip" title="<?php echo $_language->get('entry_'.$name.'_i'); ?>"><?php echo $_language->get('entry_'.$name); ?></span></label>
  <?php } else { ?>
  <label class="col-sm-2 control-label <?php echo $extra_class; ?>"><?php echo $_language->get('entry_'.$name); ?></label>
  <?php } ?>
  <div class="col-sm-4 <?php echo $extra_class; ?>">
    <?php if ($type == 'text' && $name == 'xml_node') { ?>
    <div class="input-group">
      <span class="input-group-addon">&lt;</span>
      <input type="text" class="form-control" name="<?php echo $name; ?>" value="<?php if (isset($profile[$name])) echo $profile[$name]; else if($default) echo $default; ?>"<?php if ($_language->get('entry_'.$name.'_ph') != 'entry_'.$name.'_ph') echo ' placeholder="'.$_language->get('entry_'.$name.'_ph').'"'; ?>/>
      <span class="input-group-addon">&gt;</span>
    </div>
    <?php } else if ($type == 'text') { ?>
    <input type="text" class="form-control" name="<?php echo $name; ?>" value="<?php if (isset($profile[$name])) echo $profile[$name]; else if($default) echo $default; ?>"<?php if ($_language->get('entry_'.$name.'_ph') != 'entry_'.$name.'_ph') echo ' placeholder="'.$_language->get('entry_'.$name.'_ph').'"'; ?>/>
    <?php } else if ($type == 'select') { ?>
    <select class="form-control" name="<?php echo $name; ?>">
      <?php foreach ($values as $val => $title) { ?>
        <option value="<?php echo $val; ?>" <?php if (isset($profile[$name]) && $profile[$name] == $val) echo 'selected'; ?>><?php echo $title; ?></option>
      <?php } ?>
    </select>
    <?php } else if ($type == 'checkbox') { ?>
    <input class="switch" type="checkbox" name="<?php echo $name; ?>" id="defaults_<?php echo $name; ?>" value="1" <?php echo $disabled ? 'disabled' : ''; ?>/>
    <?php } else if ($type == 'radio') { ?>
    <input type="radio" class="switch" name="<?php echo $name; ?>" value="1" data-label="<?php echo $_language->get('text_yes'); ?>" <?php if (!empty($profile[$name]) || (!isset($profile[$name])) && !empty($default)) echo 'checked'; ?> <?php echo $disabled ? 'disabled' : ''; ?>/>
    <input type="radio" class="switch" name="<?php echo $name; ?>" value="0" data-label="<?php echo $_language->get('text_no'); ?>" <?php if ((isset($profile[$name]) && empty($profile[$name])) || (!isset($profile[$name])) && empty($default)) echo 'checked'; ?> <?php echo $disabled ? 'disabled' : ''; ?>/>
    <?php } else if ($type == 'enabled') { ?>
    <input type="radio" class="switch" name="<?php echo $name; ?>" value="1" data-label="<?php echo $_language->get('text_enabled'); ?>" <?php if (!empty($profile[$name])) echo 'checked'; ?> <?php echo $disabled ? 'disabled' : ''; ?>/>
    <input type="radio" class="switch" name="<?php echo $name; ?>" value="0" data-label="<?php echo $_language->get('text_disabled'); ?>" <?php if (empty($profile[$name])) echo 'checked'; ?> <?php echo $disabled ? 'disabled' : ''; ?>/>
    <?php } ?>
  </div>

<?php } ?>
<div class="spacer"></div>

<input type="hidden" name="import_filetype" value="<?php echo $filetype; ?>"/>
<input type="hidden" name="import_compression" value="<?php echo $compression; ?>"/>
    
<ul class="nav nav-tabs">
  <li class="active"><a href="#tab-setting-common" data-toggle="tab"><?php echo $_language->get('text_common_settings'); ?></a></li>
  <?php if (in_array($type, array('product', 'category', 'manufacturer')) && !$update) { ?>
  <li><a href="#tab-setting-image" data-toggle="tab"><?php echo $_language->get('text_image_settings'); ?></a></li>
  <?php } ?>
  <?php if (in_array($type, array('product', 'category'))) { ?>
  <li><a href="#tab-setting-category" data-toggle="tab"><?php echo $_language->get('text_category_settings'); ?></a></li>
  <?php } ?>
  <?php if (in_array($type, array('product'))) { ?>
  <li><a href="#tab-setting-product" data-toggle="tab"><?php echo $_language->get('text_product_settings'); ?></a></li>
  <li><a href="#tab-setting-manufacturer" data-toggle="tab"><?php echo $_language->get('text_manufacturer_settings'); ?></a></li>
  <?php } ?>
  <?php if (!in_array($type, array('order_status', 'order'))) { ?>
  <li><a href="#tab-setting-remove" data-toggle="tab"><?php echo $_language->get('text_delete_settings'); ?> (<?php echo $_language->get('text_'.$type); ?>)</a></li>
  <?php } ?>
  <?php /*<li><a href="#tab-setting-limit" data-toggle="tab"><?php echo $_language->get('text_limit_settings'); ?></a></li>*/ ?>
  <li><a href="#tab-setting-extrafields" data-toggle="tab"><?php echo $_language->get('text_extra_fields'); ?></a></li>
  <?php if ($filetype == 'xml') { ?>
  <li><a href="#tab-setting-xpath" data-toggle="tab"><?php echo $_language->get('text_extra_xpath'); ?></a></li>
  <?php } ?>
</ul>

<div class="tab-content">
  <div class="tab-pane active" id="tab-setting-common">
    <div class="col-md-offset-2">
      <div class="gkdwidget gkdwidget-color-blueLight clearfix">
        <header role="heading">
          <i class="icon fa fa-info-circle fa-2x pull-left" title="<?php echo $_language->get('info_help'); ?>"></i>
          <ul class="nav nav-tabs pull-left in">
            <li><a data-toggle="tab" href="#gkhelps2<?php echo $gkhtab++; ?>"><span class="hidden-mobile hidden-tablet"><?php echo $_language->get('info_identifier_title'); ?></span></a></li>
            <?php if (in_array($filetype, array('xls', 'xlsx'))) { ?>
            <li><a data-toggle="tab" href="#gkhelps2<?php echo $gkhtab++; ?>"><span class="hidden-mobile hidden-tablet"><?php echo $_language->get('info_xls_settings_title'); ?></span></a></li>
            <?php } ?>
            <?php if (in_array($filetype, array('csv', 'txt', 'tsv'))) { ?>
            <li><a data-toggle="tab" href="#gkhelps2<?php echo $gkhtab++; ?>"><span class="hidden-mobile hidden-tablet"><?php echo $_language->get('info_csv_settings_title'); ?></span></a></li>
            <?php } ?>
            <?php if ($filetype == 'xml') { ?>
            <li><a data-toggle="tab" href="#gkhelps2<?php echo $gkhtab++; ?>"><span class="hidden-mobile hidden-tablet"><?php echo $_language->get('info_xml_settings_title'); ?></span></a></li>
            <?php } ?>
          </ul>
        </header>
        <div class="gkdwidget-container" style="display:none">
          <div class="tab-content">
            <div class="tab-pane" id="gkhelps2<?php echo $gkhdiv++; ?>"><?php echo $_language->get('info_identifier'); ?></div>
            <?php if (in_array($filetype, array('xls', 'xlsx'))) { ?>
            <div class="tab-pane" id="gkhelps2<?php echo $gkhdiv++; ?>"><?php echo $_language->get('info_xls_settings'); ?></div>
            <?php } ?>
            <?php if (in_array($filetype, array('csv', 'txt', 'tsv'))) { ?>
            <div class="tab-pane" id="gkhelps2<?php echo $gkhdiv++; ?>"><?php echo $_language->get('info_csv_settings'); ?></div>
            <?php } ?>
            <?php if ($filetype == 'xml') { ?>
            <div class="tab-pane" id="gkhelps2<?php echo $gkhdiv++; ?>"><?php echo $_language->get('info_xml_settings'); ?></div>
            <?php } ?>
          </div>
        </div>
      </div>
    </div>
    <?php if ($import_subtypes) { ?>
    <div class="form-group">
      <label class="col-sm-2 control-label"><?php echo $_language->get('entry_subtype'); ?></label>
      <div class="col-sm-4">
        <select name="import_subtype" class="form-control">
          <?php foreach ($import_subtypes as $import_subtype) { ?>
          <option value="<?php echo $import_subtype; ?>" <?php if (isset($profile['import_subtype']) && $profile['import_subtype'] == $import_subtype) echo 'selected'; ?>><?php echo $_language->get('text_subtype_'.$type.'_'.$import_subtype); ?></option>
          <?php } ?>
        </select>
      </div>
    </div>

    <hr class="dotted"/>
    <?php } ?>
    
    <?php if (in_array($filetype, array('csv', 'txt', 'tsv'))) { ?>
    <div class="form-group">
      <?php if (in_array($filetype, array('csv', 'txt'))) { ?>
        <?php inputField('csv_separator', $profile, $_language, 'select', $separators, '"'); ?>
      <?php } ?>
      <?php if (in_array($filetype, array('csv', 'txt', 'tsv'))) { ?>
        <?php inputField('csv_enclosure', $profile, $_language, 'select', array(
            '"' => '"',
            "'" => "'",
          ), '"'); ?>
      <?php } ?>
    </div>

    <hr class="dotted"/>
    <?php } ?>

    <?php if (1) { ?>
    <div class="form-group">
    <?php if (in_array($filetype, array('csv', 'txt', 'tsv', 'xml'))) { ?>
      <label class="col-sm-2 control-label"><span data-toggle="tooltip" title="<?php echo $_language->get('entry_encoding_i'); ?>"><?php echo $_language->get('entry_encoding'); ?></span></label>
      <div class="col-sm-4">
        <select name="encoding" class="form-control">
          <optgroup label="Common encodings">
            <option value="" <?php if (empty($profile['encoding'])) echo 'selected'; ?>>UTF-8 (default)</option>
            <option value="ISO-8859-1" <?php if (isset($profile['encoding']) && $profile['encoding'] == 'ISO-8859-1') echo 'selected'; ?>>ISO-8859-1</option>
            <option value="Windows-1251" <?php if (isset($profile['encoding']) && $profile['encoding'] == 'ISO-8859-1') echo 'selected'; ?>>Windows-1251</option>
            <option value="pass" <?php if (isset($profile['encoding']) && $profile['encoding'] == 'pass') echo 'selected'; ?>>Auto-detect</option>
          </optgroup>
          <optgroup label="All encodings">
          <?php foreach (mb_list_encodings() as $encoding) {
          if (in_array($encoding, array('pass', 'auto'))) continue; ?>
          <option value="<?php echo $encoding; ?>" <?php if (isset($profile['encoding']) && $profile['encoding'] == $encoding) echo 'selected'; ?>><?php echo $encoding; ?></option>
          <?php } ?>
          </optgroup>
        </select>
      </div>
      <?php } ?>
      <?php if ($filetype != 'xml') { ?>
        <?php inputField('multiple_separator', $profile, $_language, 'text', null, '|'); ?>
      <?php } ?>
    </div>
    
    <hr class="dotted"/>
    <?php } ?>

    <?php if ($filetype == 'json') { ?>
    <div class="form-group" style="border:0">
      <label class="col-sm-2 control-label"><span data-toggle="tooltip" title="<?php echo $_language->get('source_api_field_i'); ?>"><?php echo $_language->get('source_api_field'); ?></span></label>
      <div class="col-sm-4">
        <input type="text" name="import_api_field" class="form-control" value="<?php if (isset($profile['import_api_field'])) echo $profile['import_api_field']; ?>" placeholder="products/*"/>
      </div>
    </div>
    
    <hr class="dotted"/>
    <?php } ?>
    
    <?php if ($filetype == 'xml') { ?>
    <div class="form-group">
      <?php inputField('xml_node', $profile, $_language, 'text', null, $xml_node); ?>
      <label class="col-sm-2 control-label"><span data-toggle="tooltip" title="<?php echo $_language->get('entry_xml_processor_i'); ?>"><?php echo $_language->get('entry_xml_processor'); ?></span></label>
      <div class="col-sm-4">
        <select name="xml_proc" class="form-control">
          <?php foreach (array('', 'xri') as $xml_proc) { ?>
          <option value="<?php echo $xml_proc; ?>" <?php if (isset($profile['xml_proc']) && $profile['xml_proc'] == $xml_proc) echo 'selected'; ?>><?php echo $_language->get('text_xml_processor_'.$xml_proc); ?></option>
          <?php } ?>
        </select>
      </div>
    </div>

    <hr class="dotted"/>
    <?php } else if ($filetype != 'json'){ ?>
    <div class="form-group">
      <?php inputField('csv_header', $profile, $_language, 'radio', null, 1); ?>
    </div>

    <hr class="dotted"/>
    <?php } ?>

    <?php if (!empty($sheets)) { ?>

    <div class="form-group">
      <label class="col-sm-2 control-label"><span data-toggle="tooltip" title="<?php echo $_language->get('entry_xls_sheet_i'); ?>"><?php echo $_language->get('entry_xls_sheet'); ?></span></label>
      <div class="col-sm-4">
        <select name="sheet" class="form-control">
          <?php foreach ($sheets as $sheet_key => $sheet_name) { ?>
          <option value="<?php echo $sheet_key; ?>" <?php if (isset($profile['sheet']) && $profile['sheet'] == $sheet_key) echo 'selected'; ?>><?php echo $sheet_name; ?></option>
          <?php } ?>
        </select>
      </div>
    </div>

    <hr class="dotted"/>
    
    <?php } ?>
    
    <?php if (!empty($identifiers)) { ?>

    <div class="form-group">
      <label class="col-sm-2 control-label"><span data-toggle="tooltip" title="<?php echo $_language->get('entry_item_identifier_i'); ?>"><?php echo $_language->get('entry_item_identifier'); ?></span></label>
      <div class="col-sm-4">
        <select name="item_identifier" class="form-control">
          <?php if (!empty($identifiers_product)) { ?><optgroup label="<?php echo $_language->get('import_all_'.$orig_type); ?>"><?php } ?>
          <?php foreach ($identifiers as $val) { ?>
          <option value="<?php echo $val; ?>" <?php if (isset($profile['item_identifier']) && $profile['item_identifier'] == $val) echo 'selected'; ?>><?php echo $_language->get('entry_'.$val) != 'entry_'.$val ? $_language->get('entry_'.$val) : $val; ?></option>
          <?php } ?>
          <?php if (!empty($identifiers_product)) { ?></optgroup><?php } ?>
          <?php if (!empty($identifiers_product)) { ?>
            <optgroup label="<?php echo $_language->get('import_product_'.$orig_type); ?>">
            <?php foreach ($identifiers_product as $val) { ?>
              <option value="<?php echo $val; ?>" <?php if (isset($profile['item_identifier']) && $profile['item_identifier'] == $val) echo 'selected'; ?>><?php echo $_language->get('entry_'.$val); ?></option>
            <?php } ?>
            </optgroup>
          <?php } ?>
          <?php if (!empty($identifiers_category)) { ?>
            <optgroup label="<?php echo $_language->get('import_category_'.$orig_type); ?>">
            <?php foreach ($identifiers_category as $val) { ?>
              <option value="@cat_<?php echo $val; ?>" <?php if (isset($profile['item_identifier']) && $profile['item_identifier'] == '@cat_'.$val) echo 'selected'; ?>><?php echo $_language->get('entry_'.$val); ?></option>
            <?php } ?>
            </optgroup>
          <?php } ?>
        </select>
      </div>
      
      <label class="col-sm-2 control-label"><span data-toggle="tooltip" title="<?php echo $_language->get('entry_identifier_mode_i'); ?>"><?php echo $_language->get('entry_identifier_mode'); ?></span></label>
      <div class="col-sm-4">
        <select name="identifier_mode" class="form-control">
          <?php foreach (array('', '%') as $val) { ?>
          <option value="<?php echo $val; ?>" <?php if (isset($profile['identifier_mode']) && $profile['identifier_mode'] == $val) echo 'selected'; ?>><?php echo $_language->get('text_identifier_mode_'.$val); ?></option>
          <?php } ?>
        </select>
      </div>
    </div>

    <hr class="dotted"/>

    <?php if (!$update) { ?>
      <div class="form-group">
        <label class="col-sm-2 control-label"><?php echo $_language->get('entry_item_not_exists'); ?></label>
        <div class="col-sm-4">
          <select name="item_not_exists" class="form-control">
            <?php foreach (array('insert', 'skip') as $val) { ?>
            <option value="<?php echo $val; ?>" <?php if (isset($profile['item_not_exists']) && $profile['item_not_exists'] == $val) echo 'selected'; ?>><?php echo $_language->get('text_action_'.$val); ?></option>
            <?php } ?>
          </select>
        </div>
        <label class="col-sm-2 control-label"><span data-toggle="tooltip" title="<?php echo $_language->get('entry_item_exists_i'); ?>"><?php echo $_language->get('entry_item_exists'); ?></span></label>
        <div class="col-sm-4">
          <select name="item_exists" class="form-control">
            <?php
              $update_modes = array('update', 'skip');
              
              if (in_array($type, array('product', 'category', 'customer'))) {
                $update_modes = array('soft_update', 'update', 'skip');
              }
              
              if ($type == 'attribute') {
                $update_modes = array('update', 'soft_update', 'skip');
              }
              
              if ($type == 'product') {
                $update_modes = array('soft_update', 'update', 'skip', 'option', 'update_option');
              }
            ?>
            <?php foreach ($update_modes as $val) { ?>
            <option value="<?php echo $val; ?>" <?php if (isset($profile['item_exists']) && $profile['item_exists'] == $val) echo 'selected'; ?>><?php echo $_language->get('text_action_'.$val); ?></option>
            <?php } ?>
          </select>
        </div>
      </div>

      <hr class="dotted"/>
    <?php } ?>

    <?php } ?>

    <div class="form-group">
      <label class="col-sm-2 control-label"><span data-toggle="tooltip" title="<?php echo $_language->get('entry_limit_i'); ?>"><?php echo $_language->get('entry_limit'); ?></span></label>
      <div class="col-sm-4">
        <div class="input-group">
          <span class="input-group-addon"><?php echo $_language->get('filter_start'); ?></span>
          <input type="text" class="form-control" name="row_start" placeholder="" value="<?php echo isset($profile['row_start']) ? $profile['row_start'] : ''; ?>"/>
          <span class="input-group-addon"><?php echo $_language->get('filter_end'); ?></span>
          <input type="text" class="form-control" name="row_end" placeholder="" value="<?php echo isset($profile['row_end']) ? $profile['row_end'] : ''; ?>"/>
        </div>
      </div>
        <?php /*
      <div class="col-sm-4 input-group">
        <span class="input-group-addon"><?php echo $_language->get('filter_limit_start'); ?></span>
        <input type="text" class="form-control" name="row_start" value="<?php echo isset($profile['row_start']) ? $profile['row_start'] : ''; ?>" placeholder="<?php echo $_language->get('entry_limit_start'); ?>" />
        <span class="input-group-addon"><?php echo $_language->get('filter_limit_limit'); ?></span>
        <input type="text" class="form-control" name="max_items" value="<?php echo isset($profile['max_items']) ? $profile['max_items'] : ''; ?>" placeholder="<?php echo $_language->get('entry_limit_max'); ?>"/>
      </div>
      */ ?>
    </div>
  </div>

  <?php if (in_array($type, array('product', 'category', 'manufacturer')) && !$update) { ?>
    <div class="tab-pane" id="tab-setting-image">
      <?php if(false && !ini_get('allow_url_fopen')) { ?>
        <div class="alert alert-danger col-md-offset-2"><i class="fa fa-exclamation-circle"></i> <b>Warning:</b> "allow_url_fopen disabled", in order to be able to download remote images you need to have <b>allow_url_fopen</b> PHP parameter to be enabled in your php.ini</div>
      <?php } ?>

      <?php if(false && ini_get('open_basedir')) { ?>
        <div class="alert alert-danger col-md-offset-2"><i class="fa fa-exclamation-circle"></i> <b>Warning:</b> "open_basedir restriction", in order to be able to download remote images you need set <b>open_basedir</b> PHP parameter to none in your php.ini</div>
      <?php } ?>
      
      <div class="form-group">
        <?php /* inputField('image_download', $profile, $_language, 'radio', null, ini_get('allow_url_fopen') && !ini_get('open_basedir'), !ini_get('allow_url_fopen') || ini_get('open_basedir')); */ ?>
        <?php inputField('image_download', $profile, $_language, 'radio', null, 1); ?>
      </div>
      
      <hr class="dotted"/>
      
      <div class="form-group">
        <?php inputField('image_exists', $profile, $_language, 'select',
          array(
            'keep' => $_language->get('text_img_action_keep'),
            'rename' => $_language->get('text_img_action_rename'),
            'overwrite' => $_language->get('text_img_action_overwrite'),
          )); ?>
          
          <?php inputField('image_not_exists', $profile, $_language, 'select',
          array(
            '' => $_language->get('text_img_action_continue'),
            'skip' => $_language->get('text_img_action_skip'),
          )); ?>
      </div>
      
      <hr class="dotted"/>

      <div class="form-group">
        <?php inputField('image_location', $profile, $_language, 'text'); ?>
        <?php inputField('image_keep_path', $profile, $_language, 'radio', null, 1, null, 'imgDownload'); ?>
      </div>
      
      <div class="form-group imgDownload">
        <?php inputField('image_http_auth', $profile, $_language, 'text'); ?>
        <?php inputField('image_name_param', $profile, $_language, 'text'); ?>
      </div>
      
      <div class="form-group imgDownload">
        <?php inputField('image_sanitize', $profile, $_language, 'select',
          array(
            '' => $_language->get('text_img_sanitize_default'),
            'safe' => $_language->get('text_img_sanitize_safe'),
            'lcase' => $_language->get('text_img_sanitize_lcase'),
            'off' => $_language->get('text_img_sanitize_off'),
          )); ?>
          
        <?php inputField('image_remove_path', $profile, $_language, 'text'); ?>
      </div>

      <hr class="dotted imgDownload"/>
    </div>
  <?php } ?>

  <?php if (in_array($type, array('product', 'category'))) { ?>
    <div class="tab-pane" id="tab-setting-category">
      <div class="form-group">
        <?php inputField('category_create', $profile, $_language, 'radio', null, 1); ?>
      </div>

      <hr class="dotted"/>
      
      <div class="form-group">
        <?php inputField('category_separator', $profile, $_language, 'text', null, '|'); ?>
        <label class="col-sm-2 control-label"><span data-toggle="tooltip" title="<?php echo $_language->get('entry_subcat_separator_i'); ?>"><?php echo $_language->get('entry_subcat_separator'); ?></span></label>
        <div class="col-sm-4">
          <input type="text" class="form-control" name="subcategory_separator" value="<?php if (isset($profile['subcategory_separator'])) echo $profile['subcategory_separator']; else echo '>'; ?>" />
        </div>
      </div>
      
      <hr class="dotted"/>
      
      <?php if (in_array($type, array('product'))) { ?>
      <div class="form-group">
        <?php inputField('include_subcat', $profile, $_language, 'select',
          array(
            '' => $_language->get('text_include_subcat_none'),
            'parent' => $_language->get('text_include_subcat_parent'),
            'all' => $_language->get('text_include_subcat_all'),
          )); ?>
      </div>
        
        <hr class="dotted"/>
          
        <div class="form-group">
          <?php inputField('filter_to_category', $profile, $_language, 'radio', null, 0); ?>
        </div>
      
        <hr class="dotted"/>
      <?php } ?>
    </div>
  <?php } ?>
  <?php if (in_array($type, array('product'))) { ?>
    <div class="tab-pane" id="tab-setting-product">
      <div class="form-group">
        <label class="col-sm-2 control-label"><span data-toggle="tooltip" title="<?php echo $_language->get('entry_auto_qty_i'); ?>"><?php echo $_language->get('entry_auto_qty'); ?></span></label>
        <div class="col-sm-10">
          <select name="auto_qty" class="form-control">
            <?php foreach (array('', '1', '2') as $optMode) { ?>
            <option value="<?php echo $optMode; ?>" <?php if (isset($profile['auto_qty']) && $profile['auto_qty'] == $optMode) echo 'selected'; ?>><?php echo $_language->get('entry_auto_qty_'.$optMode); ?></option>
            <?php } ?>
          </select>
        </div>
      </div>
      <div class="form-group">
        <?php inputField('preserve_attribute', $profile, $_language, 'radio', null, 0); ?>
      </div>
      <div class="form-group">
        <label class="col-sm-2 control-label"><span data-toggle="tooltip" title="<?php echo $_language->get('entry_filter_generate_i'); ?>"><?php echo $_language->get('entry_filter_generate'); ?></span></label>
        <div class="col-sm-4">
          <input class="switch" type="checkbox" name="filters_from_attributes" id="defaults_filters_from_attributes" data-label="<?php echo $_language->get('entry_attribute'); ?>" value="1" <?php if (!empty($profile['filters_from_attributes']) || (!isset($profile['filters_from_attributes'])) && !empty($default)) echo 'checked'; ?>/>
          <input class="switch" type="checkbox" name="filters_from_options" id="defaults_filters_from_options" data-label="<?php echo $_language->get('entry_option'); ?>" value="1" <?php if (!empty($profile['filters_from_options']) || (!isset($profile['filters_from_options'])) && !empty($default)) echo 'checked'; ?>/>
        </div>
      </div>
      <div class="form-group">
        <label class="col-sm-2 control-label"><span data-toggle="tooltip" title="<?php echo $_language->get('entry_filter_identifier_i'); ?>"><?php echo $_language->get('entry_filter_identifier'); ?></span></label>
        <div class="col-sm-10">
          <select name="filter_identifier" class="form-control">
            <?php foreach (array('', '1') as $optMode) { ?>
            <option value="<?php echo $optMode; ?>" <?php if (isset($profile['filter_identifier']) && $profile['filter_identifier'] == $optMode) echo 'selected'; ?>><?php echo $_language->get('entry_filter_identifier_'.$optMode); ?></option>
            <?php } ?>
          </select>
        </div>
      </div>
    </div>
    <div class="tab-pane" id="tab-setting-manufacturer">
      <div class="form-group">
        <?php inputField('manufacturer_create', $profile, $_language, 'radio', null, 1); ?>
      </div>
    </div>
  <?php } ?>
  <?php if (!in_array($type, array('order_status'))) { ?>
    <div class="tab-pane" id="tab-setting-remove">
      <div class="form-group">
      <?php
       $delete_types = array(
          '' => $_language->get('text_delete_nothing'),
          'all' => $_language->get('text_delete_all'),
          'missing' => $_language->get('text_delete_missing'),
        );
        
        if ($type == 'product') {
          $delete_types['missing_brand'] = $_language->get('text_delete_missing_brand');
        }
        
        $delete_modes = array(
          'delete' => $_language->get('text_delete_delete'),
        );
        
        if (in_array($type, array('product', 'category', 'information'))) {
          $delete_modes['disable'] = $_language->get('text_delete_disable');
        }
        
        if ($type == 'product') {
          $delete_modes['zero'] = $_language->get('text_delete_zero');
        }
        
      //inputField('delete', $profile, $_language, 'select', $delete_types);
      //inputField('delete_action', $profile, $_language, 'select', $delete_modes);
      ?>
      <label class="col-sm-2 control-label"><?php echo $_language->get('entry_delete'); ?></label>
      <div class="col-sm-4">
        <select class="form-control" name="delete">
          <?php foreach ($delete_types as $val => $title) { ?>
            <option value="<?php echo $val; ?>" <?php if (isset($profile['delete']) && $profile['delete'] == $val) echo 'selected'; ?> <?php if ($val == 'batch' && !count($importLabels)) echo 'disabled style="color:#aaa"'; ?>><?php echo $title; ?></option>
          <?php } ?>
        </select>
      </div>
      <div class="col-sm-6">
        <div class="delete_batch">
          <label class="col-sm-3 control-label"><?php echo $_language->get('entry_delete_batch'); ?></label>
          <div class="col-sm-9" style="margin-bottom:7px">
            <select class="form-control" name="delete_batch">
              <optgroup label="<?php echo $_language->get('batch_group_general'); ?>">
              <option value="" <?php if (isset($profile['delete_batch']) && $profile['delete_batch'] == '') echo 'selected'; ?>><?php echo $_language->get('batch_delete_everything'); ?></option>
              <option value="defined" <?php if (isset($profile['delete_batch']) && $profile['delete_batch'] == 'defined') echo 'selected'; ?>><?php echo $_language->get('batch_delete_defined'); ?></option>
              <option value="empty" <?php if (isset($profile['delete_batch']) && $profile['delete_batch'] == 'empty') echo 'selected'; ?>><?php echo $_language->get('batch_delete_empty'); ?></option>
              </optgroup>
              <optgroup label="<?php echo $_language->get('batch_group_specific'); ?>">
              <?php foreach ($importLabels as $val) { ?>
                <option value="<?php echo $val; ?>" <?php if (isset($profile['delete_batch']) && $profile['delete_batch'] == $val) echo 'selected'; ?>><?php echo $val; ?></option>
              <?php } ?>
              </optgroup>
            </select>
          </div>
        </div>
        <div class="delete_batch">
          <label class="col-sm-3 control-label"><?php echo $_language->get('entry_no_delete_skipped'); ?></label>
          <div class="col-sm-9" style="margin-bottom:7px">
            <select class="form-control" name="no_delete_skipped">
              <option value="" <?php if (empty($profile['no_delete_skipped'])) echo 'selected'; ?>><?php echo $_language->get('text_no_delete_skipped_off'); ?></option>
              <option value="1" <?php if (!empty($profile['no_delete_skipped'])) echo 'selected'; ?>><?php echo $_language->get('text_no_delete_skipped_on'); ?></option>
            </select>
          </div>
        </div>
        <?php if (in_array($type, array('product', 'product_update'))) {
          $dbFieldsTypes = array('', 'name', 'description', 'model', 'sku', 'upc', 'ean', 'jan', 'isbn', 'mpn', 'quantity', 'subtract', 'price', 'status', 'manufacturer_id', 'date_added', 'date_modified');
        } 
        
        if (!empty($dbFieldsTypes)) {
        ?>
        <div class="delete_batch">
          <label class="col-sm-3 control-label"><?php echo $_language->get('entry_delete_condition'); ?></label>
          <div class="col-sm-9" style="margin-bottom:7px">
            <select class="form-control" name="delete_condition[db_field]">
            <?php foreach ($dbFieldsTypes as $val) { ?>
              <option value="<?php echo $val; ?>" <?php if (isset($profile['delete_condition']['db_field']) && $profile['delete_condition']['db_field'] == $val) echo 'selected'; ?>><?php echo (!$val ? $_language->get('text_no_condition') : (($_language->get('entry_'.$val) != 'entry_'.$val) ? $_language->get('entry_'.$val) : ucwords(str_replace('_', ' ', $val)))); ?></option>
            <?php } ?>
            </select>
            <div class="delete_condition_show">
              <select class="form-control" name="delete_condition[comparator]">
              <?php
              foreach (array('is_equal', 'is_equal_list', 'is_not_equal', 'is_not_equal_list', 'is_greater', 'is_lower', 'contain', 'not_contain') as $comparator) {
                echo '          <option value="'.$comparator.'" '. ((isset($profile['delete_condition']['comparator']) && $profile['delete_condition']['comparator'] == $comparator) ? 'selected' : '') .'>'. $_language->get('xfn_'.$comparator) .'</option>';
              } ?>
              </select>
              <input type="text" name="delete_condition[value]" value="<?php if (isset($profile['delete_condition']['value'])) echo $profile['delete_condition']['value']; ?>" class="form-control"/>
            </div>
          </div>
        </div>
        <?php } ?>
        <div class="delete_action">
          <label class="col-sm-3 control-label"><?php echo $_language->get('entry_delete_action'); ?></label>
          <div class="col-sm-9">
            <select class="form-control" name="delete_action">
              <?php foreach ($delete_modes as $val => $title) { ?>
                <option value="<?php echo $val; ?>" <?php if (isset($profile['delete_action']) && $profile['delete_action'] == $val) echo 'selected'; ?>><?php echo $title; ?></option>
              <?php } ?>
            </select>
          </div>
        </div>
      </div>
    </div>
  </div>
  <?php } ?>
  <?php /*
  <div class="tab-pane" id="tab-setting-limit">
    <div class="form-group">
      <?php inputField('limit_batch_status', $profile, $_language, 'radio', null, 0); ?>
      <div class="col-sm-6">
        <div class="limit_batch">
          <label class="col-sm-3 control-label"><?php echo $_language->get('entry_delete_batch'); ?></label>
          <div class="col-sm-9" style="margin-bottom:7px">
            <select class="form-control" name="limit_batch">
              <optgroup label="<?php echo $_language->get('batch_group_general'); ?>">
              <option value="" <?php if (isset($profile['delete_batch']) && $profile['delete_batch'] == '') echo 'selected'; ?>><?php echo $_language->get('batch_delete_everything'); ?></option>
              <option value="defined" <?php if (isset($profile['delete_batch']) && $profile['delete_batch'] == 'defined') echo 'selected'; ?>><?php echo $_language->get('batch_delete_defined'); ?></option>
              <option value="empty" <?php if (isset($profile['delete_batch']) && $profile['delete_batch'] == 'empty') echo 'selected'; ?>><?php echo $_language->get('batch_delete_empty'); ?></option>
              </optgroup>
              <optgroup label="<?php echo $_language->get('batch_group_specific'); ?>">
              <?php foreach ($importLabels as $val) { ?>
                <option value="<?php echo $val; ?>" <?php if (isset($profile['delete_batch']) && $profile['delete_batch'] == $val) echo 'selected'; ?>><?php echo $val; ?></option>
              <?php } ?>
              </optgroup>
            </select>
          </div>
        </div>
      </div>
    </div>
  
    <div class="form-group">
      <?php inputField('limit_field_status', $profile, $_language, 'radio', null, 0); ?>
      <div class="col-sm-6">
        <div class="limit_field">
          <label class="col-sm-3 control-label"><?php echo $_language->get('entry_limit_field'); ?></label>
          <div class="col-sm-9" style="margin-bottom:7px">
            <select class="form-control" name="limit_field[0][field]">
              <?php foreach (array('model', 'sku', 'upc', 'ean', 'jan', 'isbn', 'mpn', 'quantity', 'subtract') as $val) { ?>
                <option value="<?php echo $val; ?>" <?php if (isset($profile['delete_batch']) && $profile['delete_batch'] == $val) echo 'selected'; ?>><?php echo $_language->get('entry_'.$val); ?></option>
              <?php } ?>
            </select>
            <select class="form-control" name="limit_field[0][comparator]">
              <?php foreach (array('is_equal', 'is_not_equal') as $comparator) { ?>
                <option value="<?php echo $comparator; ?>" <?php echo ((isset($values['comparator']) && $values['comparator'] == $comparator) ? 'selected' : ''); ?>><?php echo $_language->get('xfn_'.$comparator); ?></option>
              <?php } ?>
            </select>
            <input type="text" class="form-control" name="limit_field[0][value]"/>
          </div>
        </div>
      </div>
    </div>
  </div>
  */ ?>
  <div class="tab-pane" id="tab-setting-extrafields">
    <div class="form-group">
      <label class="col-sm-2 control-label"><span data-toggle="tooltip" title="<?php echo $_language->get('entry_extra_fields_i'); ?>"><?php echo $_language->get('entry_extra_fields'); ?></span></label>
      <div class="col-sm-10">
      <input type="text" class="form-control" name="extra_fields" value="<?php if (isset($profile['extra_fields'])) echo $profile['extra_fields']; ?>" placeholder="Extra field 1, Custom price, Uppercase title"/>
      </div>
    </div>
  </div>
  
  <div class="tab-pane" id="tab-setting-xpath">
    <div class="well infowell">
      <h4><i class="fa fa-fw fa-caret-right"></i> <?php echo $_language->get('info_extra_xpath_title'); ?></h4>
      <div style="display:none"><?php echo $_language->get('info_extra_xpath'); ?></div>
    </div>
    <div class="form-group">
      <label class="col-sm-2 control-label"><span data-toggle="tooltip" title="<?php echo $_language->get('entry_extra_xpath_i'); ?>"><?php echo $_language->get('entry_extra_xpath'); ?></span>
      <?php /*<div style="color:#888;font-size:11px"><?php echo $_language->get('entry_extra_xpath_eg'); ?></div> */ ?>
      </label>
      <div class="col-sm-10">
      <textarea class="form-control" name="extra_xpath" placeholder="<?php echo $_language->get('entry_extra_xpath_ph'); ?>" rows="12"><?php if (isset($profile['extra_xpath'])) echo $profile['extra_xpath']; ?></textarea>
      </div>
    </div>
  </div>
  
</div>

<?php /*
<div class="data-array">
  <table class="table table-bordered">
    <thead>
      <tr>
      <?php foreach ($columns as $row) { ?>
        <th><?php echo $row; ?></th>
      <?php } ?>
      </tr>
    </thead>
    <tbody>
      <?php foreach ($rows as $row) { ?>
      <tr>
        <?php foreach ($row as $col) { ?>
        <td><?php echo $col; ?></td>
        <?php } ?>
      </tr>
      <?php } ?>
    </tbody>
  </table>
</div>
*/ ?>
<div class="pull-right">
  <button type="button" class="btn btn-default cancel" data-step="2"><i class="fa fa-reply"></i> <?php echo $_language->get('text_previous_step'); ?></button>
  <button type="button" class="btn btn-success submit" data-step="2"><i class="fa fa-check"></i> <?php echo $_language->get('text_next_step'); ?></button>
</div>

<div class="spacer"></div>

<script type="text/javascript">
$('select[name=delete]').trigger('change');
$('select[name="delete_condition[db_field]"]').trigger('change');
</script>