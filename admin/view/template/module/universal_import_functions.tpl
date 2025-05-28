<?php function inputField($name, $profile, $_language, $type = 'text', $values = null, $default = null, $disabled = false) { ?>
  <?php if ($_language->get('entry_'.$name.'_i') != 'entry_'.$name.'_i') { ?>
  <label class="col-sm-2 control-label"><span data-toggle="tooltip" title="<?php echo $_language->get('entry_'.$name.'_i'); ?>"><?php echo $_language->get('entry_'.$name); ?></span></label>
  <?php } else { ?>
  <label class="col-sm-2 control-label"><?php echo $_language->get('entry_'.$name); ?></label>
  <?php } ?>
  <div class="col-sm-4">
    <?php if ($type == 'text' && $name == 'xml_node') { ?>
    <div class="input-group">
      <span class="input-group-addon">&lt;</span>
      <input type="text" class="form-control" name="<?php echo $name; ?>" value="<?php if (isset($profile[$name])) echo $profile[$name]; else if($default) echo $default; ?>" />
      <span class="input-group-addon">&gt;</span>
    </div>
    <?php } else if ($type == 'text') { ?>
    <input type="text" class="form-control" name="<?php echo $name; ?>" value="<?php if (isset($profile[$name])) echo $profile[$name]; else if($default) echo $default; ?>" />
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
<?php function dataField($name, $label, $columns, $profile, $_language, $default = false, $select = array(), $multiple = false, $no_close = false, $iteration = 0) {
$is_extra = false;
//if (($name == '_extra_') || !empty($profile['extra']) && in_array($name, $profile['extra'])) {
if (($name == '_extra_') || $label == $_language->get('entry_extra')) {
  $is_extra = true;
}
?>
  <?php if ($label) { ?>
<div class="form-group <?php if ($is_extra) { echo ' extraField'; } ?>" <?php if ($name == '_extra_') {echo 'style="display:none;"';} ?>>
  <?php if ($is_extra) { ?>
    <label class="col-sm-2 control-label"><?php echo $label; ?></label>
    <div class="col-sm-3">
      <input class="form-control extraFieldName" type="text" name="extra[]" value="<?php echo $name != '_extra_' ? $name : ''; ?>" placeholder="<?php echo $_language->get('placeholder_extra_col'); ?>" <?php echo $name == '_extra_' ? 'disabled="disabled"' : ''; ?>/>
    </div>
  <?php } elseif ($_language->get('help_field_'.$name.'_'.$profile['filetype']) != 'help_field_'.$name.'_'.$profile['filetype']) { ?>
    <label class="col-sm-2 control-label"><span data-toggle="tooltip" title="<?php echo $_language->get('help_field_'.$name.'_'.$profile['filetype']); ?>"><?php echo $label; ?></span></label>
  <?php } elseif ($_language->get('help_field_'.$name) != 'help_field_'.$name) { ?>
    <label class="col-sm-2 control-label"><span data-toggle="tooltip" title="<?php echo $_language->get('help_field_'.$name); ?>"><?php echo $label; ?></span></label>
  <?php } elseif ($_language->get('entry_'.$name.'_i') != 'entry_'.$name.'_i') { ?>
    <label class="col-sm-2 control-label"><span data-toggle="tooltip" title="<?php echo $_language->get('entry_'.$name.'_i'); ?>"><?php echo $label; ?></span></label>
  <?php } else { ?>
    <label class="col-sm-2 control-label"><?php echo $label; ?></label>
  <?php } ?>
  <div class="col-sm-4">
  <?php } else { ?>
  <div>
  <?php } ?>
    <?php
      if (isset($profile['columns'][$name])) {
        $profile_values = (array) $profile['columns'][$name];
      } else if (strpos($name, '][')) {
        $profile_values = $profile['columns'];
        $keys = explode('][', $name);
        while (($key = array_shift($keys)) !== null) {
          if (isset($profile_values[$key])) {
            $profile_values = (array) $profile_values[$key]; 
          } else {
            $profile_values = array('');
          }
        }
      } else {
        $profile_values = array('');
      }
      
      if (isset($profile['defaults'][$name])) {
        $profile_defaults = (array) $profile['defaults'][$name];
      } else if (strpos($name, '][')) {
        $profile_defaults = isset($profile['defaults']) ? $profile['defaults'] : array();
        $keys = explode('][', $name);
        while (($key = array_shift($keys)) !== null) {
          if (isset($profile_defaults[$key])) {
            $profile_defaults = (array) $profile_defaults[$key]; 
          } else {
            $profile_defaults = array('');
          }
        }
      } else {
        $profile_defaults = array('');
      }
      
      if (!empty($profile['extra_fields'])) {
        $extra_fields = explode(',', $profile['extra_fields']);
        foreach ($extra_fields as $i => $extra_field_name) {
          $columns['__extra_field_'.$i] = $extra_field_name;
        }
      }
      
      if ($default) {
        //$columns['__apply_default_value'] = $_language->get('text_col_set_to_default');
        $columns = array('__apply_default_value' => $_language->get('text_col_set_to_default')) + $columns;
      }
      
      if ($multiple) {
        foreach ($profile_values as $fieldKey => $profile_value) {
    ?>
    <div class="multipleElement">
      <select class="form-control<?php if ($is_extra) { echo ' extraFieldColumn'; } ?>" name="<?php if ($name != '_extra_') { ?>columns[<?php echo $name; ?>]<?php if ($multiple) echo '[]'; ?><?php } ?>" <?php echo $name == '_extra_' ? 'disabled="disabled"' : ''; ?>>
        <option value=""><?php echo $_language->get('text_ignore'); ?></option>
        <?php foreach ($columns as $key => $row) { ?>
          <option value="<?php echo $key; ?>" <?php if ($profile_value !== '' && (($profile_value === '__apply_default_value' && $profile_value === $key) || ($profile_value !== '__apply_default_value' && $profile_value == $key))) echo 'selected'; ?>><?php echo $row; ?></option>
        <?php } ?>
        <?php if (!in_array($profile_value, array_keys($columns))) { ?>
          <option value="<?php echo $profile_value; ?>" selected><?php echo $profile_value; ?></option>
        <?php } ?>
      </select>
    </div>
    
    <?php  if ($multiple && !empty($profile['columns']['sub_'.$name][$fieldKey])) {  foreach ($profile['columns']['sub_'.$name][$fieldKey] as $profile_value) { ?>
    <div class="multipleElement subitem">
      <select class="subitem form-control<?php if ($is_extra) { echo ' extraFieldColumn'; } ?>" name="columns[sub_<?php echo $name; ?>][<?php echo $fieldKey; ?>][]">
        <option value=""><?php echo $_language->get('text_ignore'); ?></option>
        <?php foreach ($columns as $key => $row) { ?>
          <option value="<?php echo $key; ?>" <?php if ($profile_value !== '' && (($profile_value === '__apply_default_value' && $profile_value === $key) || ($profile_value !== '__apply_default_value' && $profile_value == $key))) echo 'selected'; ?>><?php echo $row; ?></option>
        <?php } ?>
        <?php if (!in_array($profile_value, array_keys($columns))) { ?>
          <option value="<?php echo $profile_value; ?>" selected><?php echo $profile_value; ?></option>
        <?php } ?>
      </select>
    </div>
    <?php }} ?>
    <?php }} else { 
      $profile_value = $profile_values[0];
    ?>
    <div class="singleElement">
      <select class="form-control<?php if ($is_extra) { echo ' extraFieldColumn'; } ?>" name="<?php if ($name != '_extra_') { ?>columns[<?php echo $name; ?>]<?php if ($multiple) echo '[]'; ?><?php } ?>" <?php echo $name == '_extra_' ? 'disabled="disabled"' : ''; ?>>
        <option value=""><?php echo $_language->get('text_ignore'); ?></option>
        <?php foreach ($columns as $key => $row) { ?>
          <option value="<?php echo $key; ?>" <?php if ($profile_value !== '' && (($profile_value === '__apply_default_value' && $profile_value === $key) || ($profile_value !== '__apply_default_value' && $profile_value == $key))) echo 'selected'; ?>><?php echo $row; ?></option>
        <?php } ?>
        <?php if (!in_array($profile_value, array_keys($columns))) { ?>
          <option value="<?php echo $profile_value; ?>" selected><?php echo $profile_value; ?></option>
        <?php } ?>
      </select>
    </div>
    <?php } ?>
  </div>
  <?php if ($multiple) { ?>
  <?php if ($multiple === 'option_') { ?>
    <div class="col-md-2">
      <select name="option_mode[]" class="form-control">
        <?php foreach (array('', 'string', 'advanced') as $opt_mode) { ?>
        <option value="<?php echo $opt_mode; ?>" <?php if (isset($profile['option_mode'][$iteration]) && $profile['option_mode'][$iteration] == $opt_mode) echo 'selected="selected"'; ?>><?php echo $_language->get('entry_option_mode_'.$opt_mode); ?></option>
        <?php } ?>
      </select>
    </div>
  <?php } ?>
  <div class="col-sm-1">
    <?php if ($multiple === 'category') { ?>
      <div class="btn-group">
        <button type="button" class="btn btn-success dropdown-toggle" data-toggle="dropdown"><i class="fa fa-plus"></i> <?php echo $_language->get('text_add_sub'); ?> <span class="caret"></span></button>
        <ul class="dropdown-menu">
          <li><a class="add-column" href="#"><?php echo $_language->get('text_add_cat'); ?></a></li>
          <li role="separator" class="divider"></li>
          <li><a class="add-column is-subcat" href="#"><?php echo $_language->get('text_add_subcat'); ?></a></li>
        </ul>
      </div>
    <?php } else { ?>
    <button type="button" class="btn btn-success add-column"><i class="fa fa-plus"></i> <?php echo $_language->get('text_add_column'); ?></button>
    <?php } ?>
    <?php array_pop($profile_values); foreach ($profile_values as $profile_value) { ?>
    <button title="<?php echo $_language->get('text_remove_column'); ?>" type="button" data-toggle="tooltip" class="btn btn-danger remove-column"><i class="fa fa-minus-circle"></i></button>
    <?php } ?>
    <?php  if (!empty($profile['columns']['sub_'.$name])) { foreach ($profile['columns']['sub_'.$name] as $subitems) { foreach ($subitems as $subitem) { ?>
      <button title="<?php echo $_language->get('text_remove_column'); ?>" type="button" data-toggle="tooltip" class="btn btn-danger remove-column"><i class="fa fa-minus-circle"></i></button>
    <?php }}} ?>
  </div>
  <?php } ?>
  <?php if ($label && $default) { ?>
  <label class="col-sm-<?php echo $multiple ? 1 : 2; ?> control-label"><span data-toggle="tooltip" title="<?php echo $_language->get('entry_default_i'); ?>"><?php echo $_language->get('import_default_value'); ?></span></label>
  <div class="col-sm-4">
    <?php if ($default == 'text') { ?>
    <input type="text" class="form-control" name="defaults[<?php echo $name; ?>]" value="<?php if (isset($profile_defaults[0])) echo $profile_defaults[0]; ?>" />
    <?php } else if ($default == 'checkbox') { ?>
    <input class="switch" type="checkbox" name="defaults[<?php echo $name; ?>]" id="defaults_<?php echo $name; ?>" value="1" data-label="" <?php if (!empty($profile_defaults[0])) echo 'checked'; ?>/>
    <?php } else if ($default == 'radio') { ?>
    <input type="radio" class="switch" name="defaults[<?php echo $name; ?>]" value="1" data-label="<?php echo $_language->get('text_yes'); ?>" <?php if (!empty($profile_defaults[0])) echo 'checked'; ?>/>
    <input type="radio" class="switch" name="defaults[<?php echo $name; ?>]" value="0" data-label="<?php echo $_language->get('text_no'); ?>" <?php if (empty($profile_defaults[0])) echo 'checked'; ?>/>
    <?php } else if ($default == 'select') { ?>
      <select class="form-control" class="selectize" name="defaults[<?php echo $name; ?>]">
        <?php foreach ($select as $key => $row) { ?>
          <option value="<?php echo $key; ?>" <?php if (isset($profile_defaults[0]) && $profile_defaults[0] !== '' && $profile_defaults[0] == $key) echo 'selected'; ?>><?php echo $row; ?></option>
        <?php } ?>
      </select>
    <?php } else if ($default == 'store') { ?>
      <select id="selectize-<?php echo $name; ?>" class="selectize" name="defaults[<?php echo $name; ?>][]" multiple="multiple">
        <?php foreach ($select as $key => $row) { ?>
          <option value="<?php echo $key; ?>" <?php if (in_array($key, $profile_defaults)) echo 'selected'; ?>><?php echo $row; ?></option>
        <?php } ?>
      </select>
      <script type="text/javascript">
      $('#selectize-<?php echo $name; ?>').selectize({plugins: ['remove_button']});
      </script>
    <?php } else if ($default == 'selectize') { ?>
      <select id="selectize-<?php echo str_replace(array('[', ']'), '-', $name); ?>" class="selectize" name="defaults[<?php echo $name; ?>][]" multiple="multiple">
        <?php foreach ($select as $key => $row) { ?>
          <option value="<?php echo $key; ?>" <?php if (in_array($key, $profile_defaults)) echo 'selected'; ?>><?php echo $row; ?></option>
        <?php } ?>
      </select>
      <script type="text/javascript">
      $('#selectize-<?php echo str_replace(array('[', ']'), '-', $name); ?>').selectize({plugins: ['remove_button']});
      </script>
     <?php } else if ($default == 'selectize_single') { ?>
      <select id="selectize-<?php echo str_replace(array('[', ']'), '-', $name); ?>" class="selectize" name="defaults[<?php echo $name; ?>]">
        <?php foreach ($select as $key => $row) { ?>
          <option value="<?php echo $key; ?>" <?php if (isset($profile_defaults[0]) && $profile_defaults[0] == $key) echo 'selected'; ?>><?php echo $row; ?></option>
        <?php } ?>
      </select>
      <script type="text/javascript">
      $('#selectize-<?php echo str_replace(array('[', ']'), '-', $name); ?>').selectize();
      </script>
    <?php } else if ($default == 'enabled') { ?>
    <input type="radio" class="switch" name="defaults[<?php echo $name; ?>]" value="1" data-label="<?php echo $_language->get('text_enabled'); ?>" <?php if (!empty($profile_defaults[0])) echo 'checked'; ?>/>
    <input type="radio" class="switch" name="defaults[<?php echo $name; ?>]" value="0" data-label="<?php echo $_language->get('text_disabled'); ?>" <?php if (empty($profile_defaults[0])) echo 'checked'; ?>/>
    <?php } ?>
  </div>
  <?php } ?>
  <?php if ($label) { ?>
<?php if ($is_extra) { ?>
  <?php if (in_array($profile['import_type'], array('product', 'category', 'information')) /*&& isset($profile['item_exists']) && $profile['item_exists'] != 'soft_update'*/) { ?>
  <div class="col-sm-2">
    <input class="extraFieldForced <?php echo $name != '_extra_' ? 'switch' : ''; ?>" type="checkbox" data-label="<?php echo $_language->get('text_extra_forced'); ?>" name="extra_forced[]" id="extra_forced_<?php echo $name; ?>" value="<?php echo $name; ?>" <?php echo (!empty($profile['extra_forced']) && in_array($name, $profile['extra_forced'])) ? 'checked="checked"' : ''; ?> <?php echo $name == '_extra_' ? 'disabled="disabled"' : ''; ?>/>
  </div>
  <?php } ?>
  <div class="col-sm-1"><button title="<?php echo $_language->get('text_remove_extra_col'); ?>" type="button" data-toggle="tooltip" class="btn btn-danger remove-extra-column"><i class="fa fa-minus-circle"></i></button></div>
<?php } ?>
<?php if (!$no_close) { ?>
</div>
  <?php if (!$is_extra) { ?>
  <hr class="dotted"/>
  <?php } ?>
<?php } ?>
  <?php } ?>
<?php }

function attributeField($name, $label, $columns, $profile, $_language, $languages, $type, $store_id = null) { ?>
  <div class="row attributeField" <?php if ($name == '_extra_') {echo 'style="display:none;"';} ?>>
    <div class="col-sm-11">
    <table id="attributeFields" class="table table-bordered">
      <thead>
        <tr>
          <th style="width:20%;"><?php echo $_language->get('text_optbinding_name'); ?></th>
          <th><?php echo $_language->get('text_optbinding_bind_to'); ?></th>
          <td><?php echo $_language->get('entry_default'); ?></td>
        </tr>
      </thead>
      <tbody>
      <?php foreach (array('group', 'name', 'value') as $attribute_type) { ?>
      <tr>
        <td><?php echo $_language->get('text_attribute_'.$attribute_type); ?></td>
        <td>
          <?php foreach ($languages as $lang) { ?>
            <div class="input-group">
              <span class="input-group-addon"><img src="<?php echo $lang['image']; ?>" title="<?php echo $lang['name']; ?>"></span>
              <?php if (false) { ?>
              <input type="text" name="attribute_fields[<?php echo $name; ?>][<?php echo $attribute_type; ?>][<?php echo $lang['language_id']; ?>]" value="<?php echo isset($profile['attribute_fields'][$name][$attribute_type][$lang['language_id']]) ? $profile['attribute_fields'][$name][$attribute_type][$lang['language_id']] : ''; ?>" class="form-control" <?php echo $name == '_extra_' ? 'disabled="disabled"' : ''; ?>/>
              <?php } else { ?>
              <select class="form-control" name="attribute_fields[<?php echo $name; ?>][<?php echo $attribute_type; ?>][<?php echo $lang['language_id']; ?>]" <?php echo $name == '_extra_' ? 'disabled="disabled"' : ''; ?>>
                <option value=""><?php echo $_language->get('text_ignore'); ?></option>
                <?php foreach ($columns as $key => $row) { ?>
                  <option value="<?php echo $key; ?>" <?php if (isset($profile['attribute_fields'][$name][$attribute_type][$lang['language_id']]) && $profile['attribute_fields'][$name][$attribute_type][$lang['language_id']] !== '' && $profile['attribute_fields'][$name][$attribute_type][$lang['language_id']] == $key) echo 'selected="selected"'; ?>><?php echo $row; ?></option>
                <?php } ?>
                <?php if (!empty($profile['attribute_fields'][$name][$attribute_type][$lang['language_id']]) && !in_array($profile['attribute_fields'][$name][$attribute_type][$lang['language_id']], array_keys($columns))) { ?>
                  <option value="<?php echo $profile['attribute_fields'][$name][$attribute_type][$lang['language_id']]; ?>" selected><?php echo $profile['attribute_fields'][$name][$attribute_type][$lang['language_id']]; ?></option>
                <?php } ?>
              </select>
              <?php } ?>
              </span>
            </div>
          <?php } ?>
        </td>
        <td>
          <?php foreach ($languages as $lang) { ?>
            <div class="input-group">
              <span class="input-group-addon"><img src="<?php echo $lang['image']; ?>" title="<?php echo $lang['name']; ?>"></span>
              <input type="text" name="attribute_fields_default[<?php echo $name; ?>][<?php echo $attribute_type; ?>][<?php echo $lang['language_id']; ?>]" value="<?php echo isset($profile['attribute_fields_default'][$name][$attribute_type][$lang['language_id']]) ? $profile['attribute_fields_default'][$name][$attribute_type][$lang['language_id']] : ''; ?>" class="form-control" placeholder="<?php echo ($attribute_type == 'group') ? 'Default' : ''; ?>" <?php echo $name == '_extra_' ? 'disabled="disabled"' : ''; ?>/>
              </span>
            </div>
          <?php } ?>
        </td>
      </tr>
      <?php } ?>
      </tbody>
    </table>
    </div>
    <div class="col-sm-1"><button title="<?php echo $_language->get('text_remove_extra_col'); ?>" type="button" data-toggle="tooltip" class="btn btn-danger remove-attribute-field"><i class="fa fa-minus-circle"></i></button></div>
  </div>
  <?php
}

function dataFieldML($name, $label, $columns, $profile, $_language, $languages, $type, $store_id = null) {
if (!empty($profile['extra_fields'])) {
  $extra_fields = explode(',', $profile['extra_fields']);
  foreach ($extra_fields as $i => $extra_field_name) {
    $columns['__extra_field_'.$i] = $extra_field_name;
  }
}
      
$is_extra = false;
//if (($name == '_extra_') || !empty($profile['extraml']) && in_array($name, $profile['extraml'])) {
if (($name == '_extra_') || $label == $_language->get('entry_extra_ml')) {
  $is_extra = true;
}
?>
<div class="form-group <?php if ($is_extra) { echo ' extraFieldMl'; } ?>" <?php if ($name == '_extra_') {echo 'style="display:none;"';} ?>>
  <label class="col-sm-2 control-label"><?php echo $label; ?></label>
  <?php if ($is_extra) { ?>
  <div class="col-sm-3">
    <input class="form-control extraFieldNameMl" type="text" name="extraml[]" value="<?php echo $name != '_extra_' ? $name : ''; ?>" placeholder="<?php echo $_language->get('placeholder_extra_col'); ?>" <?php echo $name == '_extra_' ? 'disabled="disabled"' : ''; ?>/>
  </div>
  <?php } ?>
  <div class="col-sm-4">
    <?php foreach ($languages as $language) { ?>
    <div class="input-group">
      <span class="input-group-addon"><img src="<?php echo $language['image']; ?>" title="<?php echo $language['name']; ?>"></span>
      <?php if (isset($store_id)) { ?>
      <select class="form-control<?php if ($is_extra) { echo ' extraFieldColumnMl'; } ?>" <?php if ($name == '_extra_') { echo 'field'; } ?>name="columns[seo_<?php echo $type; ?>_description][<?php echo $store_id; ?>][<?php echo $language['language_id']; ?>][<?php echo $name; ?>]" <?php echo $name == '_extra_' ? 'disabled="disabled"' : ''; ?>>
        <option value=""><?php echo $_language->get('text_ignore'); ?></option>
        <?php foreach ($columns as $key => $row) { ?>
          <option value="<?php echo $key; ?>" <?php if (isset($profile['columns']['seo_'.$type.'_description'][$store_id][$language['language_id']][$name]) && $profile['columns']['seo_'.$type.'_description'][$store_id][$language['language_id']][$name] !== '' && $profile['columns']['seo_'.$type.'_description'][$store_id][$language['language_id']][$name] == $key) echo 'selected="selected"'; ?>><?php echo $row; ?></option>
        <?php } ?>
        <?php if (!empty($profile['columns']['seo_'.$type.'_description'][$store_id][$language['language_id']][$name]) && !in_array($profile['columns']['seo_'.$type.'_description'][$store_id][$language['language_id']][$name], array_keys($columns))) { ?>
          <option value="<?php echo $profile['columns']['seo_'.$type.'_description'][$store_id][$language['language_id']][$name]; ?>" selected><?php echo $profile['columns']['seo_'.$type.'_description'][$store_id][$language['language_id']][$name]; ?></option>
        <?php } ?>
      </select>
      <?php } else { ?>
      <select class="form-control<?php if ($is_extra) { echo ' extraFieldColumnMl'; } ?>" <?php if ($name == '_extra_') { echo 'field'; } ?>name="columns[<?php echo $type; ?>_description][<?php echo $language['language_id']; ?>][<?php echo $name; ?>]" <?php echo $name == '_extra_' ? 'disabled="disabled"' : ''; ?>>
        <option value=""><?php echo $_language->get('text_ignore'); ?></option>
        <?php foreach ($columns as $key => $row) { ?>
          <option value="<?php echo $key; ?>" <?php if (isset($profile['columns'][$type.'_description'][$language['language_id']][$name]) && $profile['columns'][$type.'_description'][$language['language_id']][$name] !== '' && $profile['columns'][$type.'_description'][$language['language_id']][$name] == $key) echo 'selected="selected"'; ?>><?php echo $row; ?></option>
        <?php } ?>
        <?php if (!empty($profile['columns'][$type.'_description'][$language['language_id']][$name]) && !in_array($profile['columns'][$type.'_description'][$language['language_id']][$name], array_keys($columns))) { ?>
          <option value="<?php echo $profile['columns'][$type.'_description'][$language['language_id']][$name]; ?>" selected><?php echo $profile['columns'][$type.'_description'][$language['language_id']][$name]; ?></option>
        <?php } ?>
      </select>
      <?php } ?>
    </div>
    <?php } ?>
  </div>
<?php if ($is_extra) { ?>
  <?php if (in_array($profile['import_type'], array('product', 'category', 'information')) /*&& isset($profile['item_exists']) && $profile['item_exists'] == 'update'*/) { ?>
  <div class="col-sm-2">
    <input class="extraFieldForcedMl <?php echo $name != '_extra_' ? 'switch' : ''; ?>" type="checkbox" data-label="<?php echo $_language->get('text_extra_forced'); ?>" name="extraml_forced[]" id="extraml_forced_<?php echo $name; ?>" value="<?php echo $name; ?>" <?php echo (!empty($profile['extraml_forced']) && in_array($name, $profile['extraml_forced'])) ? 'checked="checked"' : ''; ?> <?php echo $name == '_extra_' ? 'disabled="disabled"' : ''; ?>/>
  </div>
  <?php } ?>
  <div class="col-sm-1"><button title="<?php echo $_language->get('text_remove_extra_col'); ?>" type="button" data-toggle="tooltip" class="btn btn-danger remove-extra-column-ml"><i class="fa fa-minus-circle"></i></button></div>
<?php } ?>
</div>
<?php if (!$is_extra) { ?>
<hr class="dotted"/>
<?php } ?>
<?php }

function dataFieldMSML($name, $label, $columns, $profile, $_language, $languages, $type, $stores) {
if (!empty($profile['extra_fields'])) {
  $extra_fields = explode(',', $profile['extra_fields']);
  foreach ($extra_fields as $i => $extra_field_name) {
    $columns['__extra_field_'.$i] = $extra_field_name;
  }
}
$is_extra = false;
//if (($name == '_extra_') || !empty($profile['extraml']) && in_array($name, $profile['extraml'])) {
if (($name == '_extra_') || $label == $_language->get('entry_extra_ml')) {
  $is_extra = true;
}
foreach ($stores as $store_id => $store_name) {
?>
<div class="form-group <?php if ($is_extra) { echo ' extraFieldMsMl'; } ?>" <?php if ($name == '_extra_') {echo 'style="display:none;"';} ?>>
  <label class="col-sm-2 control-label">(<?php echo $store_name; ?>) <?php echo $label; ?></label>
  <?php if ($is_extra) { ?>
  <div class="col-sm-3">
    <input class="form-control extraFieldNameMl" type="text" name="extraml[]" value="<?php echo $name != '_extra_' ? $name : ''; ?>" placeholder="<?php echo $_language->get('placeholder_extra_col'); ?>" <?php echo $name == '_extra_' ? 'disabled="disabled"' : ''; ?>/>
  </div>
  <?php } ?>
  <div class="col-sm-4">
    <?php foreach ($languages as $language) { ?>
    <div class="input-group">
      <span class="input-group-addon"><img src="<?php echo $language['image']; ?>" title="<?php echo $language['name']; ?>"></span>
      <select class="form-control<?php if ($is_extra) { echo ' extraFieldColumnMl'; } ?>" <?php if ($name == '_extra_') { echo 'field'; } ?>name="columns[<?php echo $name; ?>][<?php echo $store_id; ?>][<?php echo $language['language_id']; ?>]" <?php echo $name == '_extra_' ? 'disabled="disabled"' : ''; ?>>
        <option value=""><?php echo $_language->get('text_ignore'); ?></option>
        <?php foreach ($columns as $key => $row) { ?>
          <option value="<?php echo $key; ?>" <?php if (isset($profile['columns'][$name][$store_id][$language['language_id']]) && $profile['columns'][$name][$store_id][$language['language_id']] !== '' && $profile['columns'][$name][$store_id][$language['language_id']] == $key) echo 'selected="selected"'; ?>><?php echo $row; ?></option>
        <?php } ?>
        <?php if (!empty($profile['columns'][$name][$store_id][$language['language_id']]) && !in_array($profile['columns'][$name][$store_id][$language['language_id']], array_keys($columns))) { ?>
          <option value="<?php echo $profile['columns'][$name][$store_id][$language['language_id']]; ?>" selected><?php echo $profile['columns'][$name][$store_id][$language['language_id']]; ?></option>
        <?php } ?>
      </select>
    </div>
    <?php } ?>
  </div>
<?php if ($is_extra) { ?>
  <div class="col-sm-1"><button title="<?php echo $_language->get('text_remove_extra_col'); ?>" type="button" data-toggle="tooltip" class="btn btn-danger remove-extra-column-ml"><i class="fa fa-minus-circle"></i></button></div>
<?php } ?>
</div>
<?php } ?>
<?php if (!$is_extra) { ?>
<hr class="dotted"/>
<?php } ?>
<?php }

function getXfn($type, $values, $count, $columns, $profile, $_language, $languages, $vars) {
  $xfn = '';
  
  $xfn  = '<tr class="xfn-'.$type.' form-horizontal">';
  $xfn  .= '<td style="font-family:FontAwesome, sans-serif;color:#c3c3c3;font-size:15px">&nbsp;&#xf07d;&nbsp;</td>';
  
  if ($_language->get('xfn_'.$type.'_i') != 'xfn_'.$type.'_i') {
    $xfn .= '  <td><span data-toggle="tooltip" title="'.$_language->get('xfn_'.$type.'_i').'">'.$_language->get('xfn_'.$type).'</span></td>';
  } else {
    $xfn .= '  <td>'.(($_language->get('xfn_'.$type) != 'xfn_'.$type) ? $_language->get('xfn_'.$type) : ucfirst(str_replace('_', ' ', $type))).'</td>';
  }
  
  if ($_language->get('xfn_'.$type.'_set') !== 'xfn_'.$type.'_set') {
    $set = $_language->get('xfn_'.$type.'_set');
  } else {
    //$set = trim(strip_tags($_language->get('xfn_'.$type)));
    $set = trim(str_replace('<i class="', '<i style="display:none" class="', $_language->get('xfn_'.$type)));
  }
  
  $xfn .= '  <td>';
  $xfn .= '  <table style="width:100%;border-collapse: separate;border-spacing:8px"><tr><td style="width:30%">';
  
  #custom_xfn#
  
  if ($type == 'remote_api') {
    $xfn .= '<span data-toggle="tooltip" title="'.$_language->get('source_api_url_i').'">'.$_language->get('source_api_url').'</span>';
    $xfn .= '  </td><td>';
    $xfn .= '    <input type="text" name="extra_func['.$count.']['.$type.'][url]" value="'.(isset($values['url']) ? $values['url'] : '').'" disabled="disabled" class="form-control" placeholder="https://api.example.com/v1/product/{id}/resources"/>';
    $xfn .= '  </td></tr><tr><td>';
    
    $xfn .= '<span data-toggle="tooltip" title="'.$_language->get('source_api_auth_i').'">'.$_language->get('source_api_auth').'</span>';
    $xfn .= '  </td><td>';
    $xfn .= '    <input type="text" name="extra_func['.$count.']['.$type.'][auth]" value="'.(isset($values['auth']) ? $values['auth'] : '').'" disabled="disabled" class="form-control" placeholder="username:password"/>';
    $xfn .= '  </td></tr><tr><td>';
    
    $xfn .= '<span data-toggle="tooltip" title="'.$_language->get('source_api_field_i').'">'.$_language->get('source_api_field').'</span>';
    $xfn .= '  </td><td>';
    $xfn .= '    <input type="text" name="extra_func['.$count.']['.$type.'][path]" value="'.(isset($values['path']) ? $values['path'] : '').'" disabled="disabled" class="form-control" placeholder="resources/images/*"/>';
    $xfn .= '  </td></tr><tr><td>';
    if (!empty($profile['import_transformers'])) {
      $xfn .= '<span data-toggle="tooltip" title="'.$_language->get('entry_preprocessor_i').'">'.$_language->get('entry_preprocessor').'</span>';
      $xfn .= '  </td><td>';
      $xfn .= '  <select name="extra_func['.$count.']['.$type.'][transformer]" disabled="disabled" class="form-control">';
      $xfn .= '        <option value="">'. $_language->get('text_none').'</option>';
      foreach ($profile['import_transformers'] as $import_transformer) {
        $xfn .= '        <option value="'.$import_transformer.'" '. ((isset($values['transformer']) && $values['transformer'] == $import_transformer) ? 'selected' : '') .'>'.$import_transformer.'</option>';
      }
      $xfn .= '      </select>';
      $xfn .= '  </td></tr><tr><td>';
    }
  }
  
  if ($type == 'oc_currency') {
    $xfn .= $_language->get('xfn_currency_from');
    $xfn .= '  </td><td>';
    
    $xfn .= '    <select class="form-control" name="extra_func['.$count.']['.$type.'][from]" disabled="disabled">';
    if (!empty($vars['currencies'])) {
      foreach ($vars['currencies'] as $curCode => $curName) {
        $xfn .= '          <option value="'.$curCode.'" '. ((isset($values['from']) && $values['from'] == $curCode) ? 'selected' : '') .'>'. $curName .'</option>';
      }
    }
    $xfn .= '    </select>';
    
    $xfn .= '  </td></tr><tr><td>';
    
    $xfn .= $_language->get('xfn_to');
    $xfn .= '  </td><td>';
    $xfn .= $vars['_config']->get('config_currency');
    /*
    $xfn .= '    <select class="form-control" name="extra_func['.$count.']['.$type.'][to]" disabled="disabled">';
    foreach ($vars['currencies'] as $curCode => $curName) {
      $xfn .= '          <option value="'.$curCode.'" '. ((isset($values['to']) && $values['to'] == $curCode) ? 'selected' : '') .'>'. $curName .'</option>';
    }
    $xfn .= '    </select>';
    */
    $xfn .= '  </td></tr><tr><td>';
    
    $xfn .= $_language->get('xfn_target_field');
    $xfn .= '  </td><td>';
    $xfn .= '    <select class="'.($vars['filetype'] == 'xml' ? 'selectize' : 'form-control').'" name="extra_func['.$count.']['.$type.'][field]" disabled="disabled">';
    $xfn .= '      <option value=""></option>';
    foreach ($columns as $key => $row) {
      $xfn .= '          <option value="'.$key.'" '. ((isset($values['field']) && $values['field'] == $key) ? 'selected' : '') .'>'. $row .'</option>';
    }
    if (!empty($values['field']) && !in_array($values['field'], array_keys($columns)) && substr($values['field'], 0, 14) != '__extra_field_') {
      $xfn .= '          <option value="'.$values['field'].'" selected>'.$values['field'].'</option>';
    }
    if (!empty($profile['extra_fields'])) {
      foreach (explode(',', $profile['extra_fields']) as $i => $extra_field_name) {
        $xfn .= '        <option value="__extra_field_'.$i.'" '. ((isset($values['field']) && $values['field'] == '__extra_field_'.$i) ? 'selected' : '') .'>'. trim($extra_field_name) .'</option>';
      }
    }
    $xfn .= '    </select>';
    
    $xfn .= '  <tr><td>';
    $xfn .= $_language->get('text_round');
    $xfn .= '  </td><td>';
    $xfn .= '   <select style="width:100%" class="form-control" name="extra_func['.$count.']['.$type.'][round]" disabled="disabled">';
    foreach (array('', 0, 1, 2, 3, 4) as $val) {
      $xfn .= '          <option value="'.$val.'" '. ((isset($values['round']) && $values['round'] !== '' && $values['round'] == $val) ? 'selected' : '') .'>'. $_language->get('xfn_precision_'.$val) .'</option>';
    }
    $xfn .= '    </select>';
  }
  
  if (in_array($type, array('if_table', 'wholesale'))) {
      $xfn .= $_language->get('xfn_source_field');
      $xfn .= '  </td><td>';
        $xfn .= '    <select style="width:100%" class="'.($vars['filetype'] == 'xml' ? 'selectize' : 'form-control').'" name="extra_func['.$count.']['.$type.'][field]" disabled="disabled">';
        foreach ($columns as $key => $row) {
          $xfn .= '          <option value="'.$key.'" '. ((isset($values['field']) && $values['field'] == $key) ? 'selected' : '') .'>'. $row .'</option>';
        }
        if (!empty($values['field']) && !in_array($values['field'], array_keys($columns)) && substr($values['field'], 0, 14) != '__extra_field_') {
          $xfn .= '          <option value="'.$values['field'].'" selected>'.$values['field'].'</option>';
        }
        if (!empty($profile['extra_fields'])) {
          foreach (explode(',', $profile['extra_fields']) as $i => $extra_field_name) {
            $xfn .= '        <option value="__extra_field_'.$i.'" '. ((isset($values['field']) && $values['field'] == '__extra_field_'.$i) ? 'selected' : '') .'>'. trim($extra_field_name) .'</option>';
          }
        }
        $xfn .= '    </select>';
      $xfn .= '  </td></tr><tr><td>';
      $xfn .= $_language->get('text_'.$type.'_table');
      $xfn .= '  </td><td>';
      $xfn .= '    <textarea style="width:100%;height:'. ($type == 'if_table' ? 210 : 150) .'px;font-size:12px;" name="extra_func['.$count.']['.$type.'][value]" disabled="disabled" class="form-control" placeholder="'.$_language->get('placeholder_xfn_'.$type).'">'.(isset($values['value']) ? $values['value'] : '').'</textarea>';
      $xfn .= '  </td></tr>';
      if ($type == 'wholesale') {
        $xfn .= '  <tr><td>';
        $xfn .= $_language->get('text_round');
        $xfn .= '  </td><td>';
        $xfn .= '   <select style="width:100%" class="form-control" name="extra_func['.$count.']['.$type.'][round]" disabled="disabled">';
        foreach (array('', 0, 1, 2, 3, 4) as $val) {
          $xfn .= '          <option value="'.$val.'" '. ((isset($values['round']) && $values['round'] !== '' && $values['round'] == $val) ? 'selected' : '') .'>'. $_language->get('xfn_precision_'.$val) .'</option>';
        }
        $xfn .= '    </select>';
      }
  // Target field only
  } else if (in_array($type, array('nl2br', 'strip_tags', 'strlen', 'html_encode', 'html_decode', 'uppercase', 'lowercase', 'ucfirst', 'ucwords', 'remote_content', 'to_float', 'urlencode', 'urldecode'))) {
    //$xfn .= '  <tr><td>';
    if ($type == 'remote_content') {
      $xfn .= $set;
    } else {
      $xfn .= $_language->get('xfn_target_field');
    }
    $xfn .= '  </td><td>';
    $xfn .= '    <select class="'.($vars['filetype'] == 'xml' ? 'selectize' : 'form-control').'" name="extra_func['.$count.']['.$type.'][field]" disabled="disabled">';
    $xfn .= '      <option value=""></option>';
    foreach ($columns as $key => $row) {
      $xfn .= '          <option value="'.$key.'" '. ((isset($values['field']) && $values['field'] == $key) ? 'selected' : '') .'>'. $row .'</option>';
    }
    if (!empty($values['field']) && !in_array($values['field'], array_keys($columns)) && substr($values['field'], 0, 14) != '__extra_field_') {
      $xfn .= '          <option value="'.$values['field'].'" selected>'.$values['field'].'</option>';
    }
    if (!empty($profile['extra_fields'])) {
      foreach (explode(',', $profile['extra_fields']) as $i => $extra_field_name) {
        $xfn .= '        <option value="__extra_field_'.$i.'" '. ((isset($values['field']) && $values['field'] == '__extra_field_'.$i) ? 'selected' : '') .'>'. trim($extra_field_name) .'</option>';
      }
    }
    $xfn .= '    </select>';
    //$xfn .= '  </td></tr>';
  } else if (in_array($type, array('multiple_separator', 'append', 'prepend', 'combine', 'replace', 'random', 'remove', 'tag', 'add', 'percentage', 'subtract', 'regex', 'regex_replace', 'regex_remove', 'substr'))) {
    // xfn_[type]_set [field or value] xfn_to [field]
    $for = $_language->get('xfn_to');
    $by = $_language->get('xfn_by');
    
    switch ($type) {
      case 'uppercase':
      case 'lowercase':
      case 'ucfirst':
      case 'ucwords': $for = $_language->get('xfn_for'); break;
      case 'append': $for = $_language->get('xfn_after'); break;
      case 'prepend': $for = $_language->get('xfn_before'); break;
      case 'combine': $for = $_language->get('text_combine_fields'); $set = $_language->get('entry_separator'); break;
      case 'random': $set = $_language->get('filter_min'); $by = $_language->get('filter_max'); break;
      case 'tag':
      case 'remove':
      case 'replace': $for = $_language->get('xfn_in'); break;
      case 'nl2br': $for = $_language->get('xfn_in'); break;
      case 'substr': $for = $_language->get('xfn_substr_of'); break;
      case 'remote_content': 
      case 'regex': 
      case 'regex_remove':
      case 'subtract': $for = $_language->get('xfn_from'); break;
      case 'regex_replace':
      case 'strip_tags': $for = $_language->get('xfn_in'); break;
      case 'multiple_separator': $for = $_language->get('xfn_for_column'); break;
    }
    
    
    
    //$xfn .= $set.'&nbsp;';
      $xfn .= $set;
      $xfn .= '  </td><td>';
      
    if ($type == 'percentage') {
      $xfn .= ' <div class="input-group" style="border-spacing:0">';
      $xfn .= '    <input type="text" name="extra_func['.$count.']['.$type.'][value]" value="'.(isset($values['value']) ? $values['value'] : '').'" disabled="disabled" class="form-control" '.(isset($values['fieldval']) && $values['fieldval'] !== '' ? 'style="display:none"' : '').'/>';
      $xfn .= '<span class="input-group-addon"><i class="fa fa-percent"></i></span>';
      $xfn .= '</div>';
    }
    
    if (in_array($type, array('append', 'prepend', 'add', 'subtract', 'replace'))) {
      $xfn .= '    <select class="form-control xfnFieldVal" name="extra_func['.$count.']['.$type.'][fieldval]" disabled="disabled">';
      $xfn .= '          <option value="">'. $_language->get('xfn_manual_value') .'</option>';
      foreach ($columns as $key => $row) {
        $xfn .= '          <option value="'.$key.'" '. ((isset($values['fieldval']) && $values['fieldval'] == $key && $values['fieldval'] !== '') ? 'selected' : '') .'>'. $row .'</option>';
      }
      if (!empty($values['field']) && !in_array($values['field'], array_keys($columns)) && substr($values['field'], 0, 14) != '__extra_field_') {
        $xfn .= '          <option value="'.$values['field'].'" selected>'.$values['field'].'</option>';
      }
      if (!empty($profile['extra_fields'])) {
        foreach (explode(',', $profile['extra_fields']) as $i => $extra_field_name) {
          $xfn .= '        <option value="__extra_field_'.$i.'" '. ((isset($values['fieldval']) && $values['fieldval'] == '__extra_field_'.$i) ? 'selected' : '') .'>'. trim($extra_field_name) .'</option>';
        }
      }
      $xfn .= '    </select>';
    }
    
    if (in_array($type, array('multiple_separator', 'append', 'prepend', 'combine', 'replace', 'random', 'remove', 'tag', 'add', 'subtract', 'regex', 'regex_remove', 'regex_replace', 'substr'))) {
      $xfn .= '    <input type="text" name="extra_func['.$count.']['.$type.'][value]" value="'.(isset($values['value']) ? $values['value'] : '').'" disabled="disabled" class="form-control" '.(isset($values['fieldval']) && $values['fieldval'] !== '' ? 'style="display:none"' : '').'/>';
    }
    
    $xfn .= '  </td></tr>';
    
    if (in_array($type, array('replace_', 'random', 'regex_replace'))) {
      $xfn .= '  <tr><td>';
      $xfn .= $by;
      $xfn .= '  </td><td>';
      $xfn .= '    <input type="text" name="extra_func['.$count.']['.$type.'][value2]" value="'.(isset($values['value2']) ? $values['value2'] : '').'" disabled="disabled" class="form-control"/>';
      $xfn .= '  </td></tr>';
    }
    
     // extra column field
    if (in_array($type, array('replace'))) {
      $xfn .= '  <tr><td>';
      $xfn .= $by;
      $xfn .= '  </td><td>';
      
      $xfn .= '    <select class="form-control xfnFieldVal" name="extra_func['.$count.']['.$type.'][field2]" disabled="disabled">';
      $xfn .= '          <option value="">'. $_language->get('xfn_manual_value') .'</option>';
        foreach ($columns as $key => $row) {
          $xfn .= '          <option value="'.$key.'" '. ((isset($values['field2']) && $values['field2'] == $key && $values['field2'] !== '') ? 'selected' : '') .'>'. $row .'</option>';
        }
        if (!empty($values['field']) && !in_array($values['field'], array_keys($columns)) && substr($values['field'], 0, 14) != '__extra_field_') {
          $xfn .= '          <option value="'.$values['field'].'" selected>'.$values['field'].'</option>';
        }
        if (!empty($profile['extra_fields'])) {
          foreach (explode(',', $profile['extra_fields']) as $i => $extra_field_name) {
            $xfn .= '        <option value="__extra_field_'.$i.'" '. ((isset($values['field2']) && $values['field2'] == '__extra_field_'.$i) ? 'selected' : '') .'>'. trim($extra_field_name) .'</option>';
          }
        }
      $xfn .= '    </select>';
      $xfn .= '    <input type="text" name="extra_func['.$count.']['.$type.'][field2_val]" value="'.(isset($values['field2_val']) ? $values['field2_val'] : (isset($values['value2']) ? $values['value2'] : '')).'" disabled="disabled" class="form-control" '.(isset($values['field2']) && $values['field2'] !== '' ? 'style="display:none"' : '').'/>';
      $xfn .= '  </td></tr>';
    }
    
    // to
    if (!in_array($type, array('random'))) {
      $xfn .= '  <tr><td>';
      $xfn .= $for;
      $xfn .= '  </td><td>';
      if (in_array($type, array('combine'))) {
        $extraFieldsArray = array();
        if (!empty($profile['extra_fields'])) {
          foreach (explode(',', $profile['extra_fields']) as $i => $extra_field_name) {
            $extraFieldsArray['__extra_field_'.$i] = $extra_field_name;
          }
        }
     
        // multiple selector
        $xfn .= '    <select class="selectize" name="extra_func['.$count.']['.$type.'][fields][]" disabled="disabled" multiple="multiple">';
        if (isset($values['fields'])) {
          foreach ($values['fields'] as $fieldKey) {
            if (isset($columns[$fieldKey])) {
              $xfn .= '          <option value="'.$fieldKey.'" selected>'. $columns[$fieldKey] .'</option>';
            } else if (isset($extraFieldsArray[$fieldKey])) {
              $xfn .= '          <option value="'.$fieldKey.'" selected>'. $extraFieldsArray[$fieldKey] .'</option>';
            }
          }
        }
        
        foreach ($columns as $key => $row) {
          //$xfn .= '          <option value="'.$key.'" '. (isset($values['fields']) && in_array((string) $key, $values['fields'], true) ? 'selected' : '') .'>'. $row .'</option>';
          $xfn .= '          <option value="'.$key.'">'. $row .'</option>';
        }
        if (!empty($profile['extra_fields'])) {
          foreach (explode(',', $profile['extra_fields']) as $i => $extra_field_name) {
            //$xfn .= '        <option value="__extra_field_'.$i.'" '. ((isset($values['fields']) && in_array('__extra_field_'.$i, $values['fields'])) ? 'selected' : '') .'>'. trim($extra_field_name) .'</option>';
            $xfn .= '        <option value="__extra_field_'.$i.'">'. trim($extra_field_name) .'</option>';
          }
        }
        if (!empty($values['fields'])) {
          foreach ($values['fields'] as $field) {
            if (!in_array($field, array_keys($columns))) {
              $xfn .= '       <option value="'.$field.'" selected>'.$field.'</option>';
            }
          }
        }
        $xfn .= '    </select>';
      } else {
        // single selector
        $xfn .= '    <select class="'.($vars['filetype'] == 'xml' ? 'selectize' : 'form-control').'" name="extra_func['.$count.']['.$type.'][field]" disabled="disabled">';
        $xfn .= '      <option value=""></option>';
        foreach ($columns as $key => $row) {
          $xfn .= '          <option value="'.$key.'" '. ((isset($values['field']) && $values['field'] == $key) ? 'selected' : '') .'>'. $row .'</option>';
        }
        if (!empty($values['field']) && !in_array($values['field'], array_keys($columns)) && substr($values['field'], 0, 14) != '__extra_field_') {
          $xfn .= '          <option value="'.$values['field'].'" selected>'.$values['field'].'</option>';
        }
        if (!empty($profile['extra_fields'])) {
          foreach (explode(',', $profile['extra_fields']) as $i => $extra_field_name) {
            $xfn .= '        <option value="__extra_field_'.$i.'" '. ((isset($values['field']) && $values['field'] == '__extra_field_'.$i) ? 'selected' : '') .'>'. trim($extra_field_name) .'</option>';
          }
        }
        $xfn .= '    </select>';
      }
    }
    
    if (in_array($type, array('tag'))) {
      $xfn .= '  <tr><td>';
      $xfn .= 'Available tags';
      $xfn .= '  </td><td>';
      $xfn .= $_language->get('text_xfn_tag_available');
      $xfn .= '  </td></tr>';
    }
    
    $xfn .= '  </td></tr>';
  } else if (in_array($type, array('multiply', 'divide', 'delete_item', 'skip', 'option', 'skip_db', 'round', 'urlify', 'date_convert', 'field_replace'))) {
    $for = $_language->get('xfn_to');
    
    switch ($type) {
      case 'multiply': $for = $_language->get('xfn_by'); break;
      case 'divide': $for = $_language->get('xfn_by'); break;
      case 'skip': $for = ''; break;
      case 'delete_item': $for = ''; $set = $_language->get('xfn_if'); break;
      case 'skip_db': $for = ''; break;
      case 'option': $for = ''; break;
      case 'round': $for = $_language->get('xfn_precision'); break;
      case 'urlify': $for = $_language->get('xfn_mode'); break;
      case 'date_convert': $for = $_language->get('text_convert_from'); break;
      case 'field_replace': $for = ''; break;
    }
    
    switch ($type) {
      case 'add': $operator = '+'; break;
      case 'subtract': $operator = '-'; break;
      case 'multiply': $operator = '*'; break;
      case 'divide': $operator = '/'; break;
      default: $operator = $_language->get('xfn_to');
    }
    
    if ($type == 'delete_item') {
      if (in_array($profile['import_type'], array('product', 'product_update'))) {
        $delete_actions = array('delete', 'disable', 'zero');
      } else if (in_array($profile['import_type'], array('category', 'information'))) {
        $delete_actions = array('delete', 'disable');
      } else {
        $delete_actions = array('delete');
      }
      
      $xfn .= $_language->get('entry_delete_action').'</td><td>';
      $xfn .= '    <select class="form-control" name="extra_func['.$count.']['.$type.'][action]" disabled="disabled">';
      foreach ($delete_actions as $action) {
        $xfn .= '          <option value="'.$action.'" '. ((isset($values['action']) && $values['action'] == $action) ? 'selected' : '') .'>'. $_language->get('text_delete_'.$action) .'</option>';
      }
      $xfn .= '    </select>';
      $xfn .= '  </td></tr><tr><td>';
    }
    
    $xfn .= $set;
    $xfn .= '  </td><td>';
    if ($type == 'skip_full__') {
      $xfn .= '    <select class="'.($vars['filetype'] == 'xml' ? 'selectize' : 'form-control').'" name="extra_func['.$count.']['.$type.'][field]" disabled="disabled">';
      $xfn .= '      <option value=""></option>';
      $xfn .= '      <optgroup label="'. $_language->get('text_import_value') .'">';
      foreach ($columns as $key => $row) {
        $xfn .= '          <option value="'.$key.'" '. ((isset($values['field']) && $values['field'] == $key) ? 'selected' : '') .'>'. $row .'</option>';
      }
      if (!empty($values['field']) && !in_array($values['field'], array_keys($columns)) && substr($values['field'], 0, 14) != '__extra_field_') {
        $xfn .= '          <option value="'.$values['field'].'" selected>'.$values['field'].'</option>';
      }
      if (!empty($profile['extra_fields'])) {
        foreach (explode(',', $profile['extra_fields']) as $i => $extra_field_name) {
          $xfn .= '        <option value="__extra_field_'.$i.'" '. ((isset($values['field']) && $values['field'] == '__extra_field_'.$i) ? 'selected' : '') .'>'. trim($extra_field_name) .'</option>';
        }
      }
      $xfn .= '      </optgroup>';
      $xfn .= '      <optgroup label="'. $_language->get('text_db_value') .'">';
      foreach (array('model', 'sku', 'upc', 'ean', 'jan', 'isbn', 'mpn', 'quantity', 'subtract') as $val) {
        $xfn .= '          <option value="#db#'.$val.'" '. ((isset($values['field']) && $values['field'] == '#db#'.$val) ? 'selected' : '') .'>'. $_language->get('entry_'.$val) .'</option>';
      }
      $xfn .= '      </optgroup>';
      $xfn .= '    </select>';
    } else if ($type == 'skip_db') {
      $xfn .= '    <select class="form-control" name="extra_func['.$count.']['.$type.'][db_field]" disabled="disabled">';
      
      if (in_array($profile['import_type'], array('product', 'product_update'))) {
        $dbFieldsTypes = array('name', 'description', 'model', 'sku', 'upc', 'ean', 'jan', 'isbn', 'mpn', 'quantity', 'subtract', 'price', 'status', 'manufacturer_id', 'date_added', 'date_modified');
      } else if (in_array($profile['import_type'], array('order', 'order_status_update'))) {
        $dbFieldsTypes = array('total', 'payment_code', 'shipping_code', 'order_status_id');
      }
      
      foreach ($dbFieldsTypes as $val) {
        $xfn .= '          <option value="'.$val.'" '. ((isset($values['db_field']) && $values['db_field'] == $val && $values['db_field'] !== '') ? 'selected' : '') .'>'. (($_language->get('entry_'.$val) != 'entry_'.$val) ? $_language->get('entry_'.$val) : ucwords(str_replace('_', ' ', $val))) .'</option>';
      }
      $xfn .= '    </select>';
    } else {
      $xfn .= '    <select class="'.($vars['filetype'] == 'xml' ? 'selectize' : 'form-control').'" name="extra_func['.$count.']['.$type.'][field]" disabled="disabled">';
      $xfn .= '      <option value=""></option>';
      
      foreach ($columns as $key => $row) {
        $xfn .= '          <option value="'.$key.'" '. ((isset($values['field']) && $values['field'] == $key) ? 'selected' : '') .'>'. $row .'</option>';
      }
      
      if (!empty($values['field']) && !in_array($values['field'], array_keys($columns)) && substr($values['field'], 0, 14) != '__extra_field_') {
        $xfn .= '          <option value="'.$values['field'].'" selected>'.$values['field'].'</option>';
      }
      if (!empty($profile['extra_fields'])) {
        foreach (explode(',', $profile['extra_fields']) as $i => $extra_field_name) {
          $xfn .= '        <option value="__extra_field_'.$i.'" '. ((isset($values['field']) && $values['field'] == '__extra_field_'.$i) ? 'selected' : '') .'>'. trim($extra_field_name) .'</option>';
        }
      }
      $xfn .= '    </select>';
    }
    $xfn .= '  </td></tr><tr><td>';
    $xfn .= $for;
    $xfn .= '  </td><td>';
    
    
    // comparator
    if (in_array($type, array('delete_item', 'skip', 'option', 'skip_db', 'field_replace'))) {
      $xfn .= '    <select class="form-control" name="extra_func['.$count.']['.$type.'][comparator]" disabled="disabled">';
      foreach (array('is_equal', 'is_equal_list', 'is_not_equal', 'is_not_equal_list', 'is_greater', 'is_lower', 'contain', 'not_contain') as $comparator) {
        $xfn .= '          <option value="'.$comparator.'" '. ((isset($values['comparator']) && $values['comparator'] == $comparator) ? 'selected' : '') .'>'. $_language->get('xfn_'.$comparator) .'</option>';
      }
      $xfn .= '    </select>';
    }
    
    if (in_array($type, array('round'))) {
      $xfn .= '    <select class="form-control" name="extra_func['.$count.']['.$type.'][value]" disabled="disabled">';
      foreach (array(0, 1, 2, 3, 4) as $val) {
        $xfn .= '          <option value="'.$val.'" '. ((isset($values['value']) && $values['value'] == $val) ? 'selected' : '') .'>'. $_language->get('xfn_precision_'.$val) .'</option>';
      }
      $xfn .= '    </select>';
    }
    
    if (in_array($type, array('date_convert'))) {
      $xfn .= '    <select class="form-control" name="extra_func['.$count.']['.$type.'][format]" disabled="disabled">';
      $xfn .= '      <option value="eu" '. ((isset($values['format']) && $values['format'] == 'eu') ? 'selected' : '') .'>'. $_language->get('text_date_format_eu') .'</option>';
      $xfn .= '      <option value="us" '. ((isset($values['format']) && $values['format'] == 'us') ? 'selected' : '') .'>'. $_language->get('text_date_format_us') .'</option>';
      $xfn .= '      <option value="sql" '. ((isset($values['format']) && $values['format'] == 'sql') ? 'selected' : '') .'>'. $_language->get('text_date_format_sql') .'</option>';
      $xfn .= '      <option value="xls" '. ((isset($values['format']) && $values['format'] == 'xls') ? 'selected' : '') .'>'. $_language->get('text_date_format_xls') .'</option>';
      $xfn .= '    </select>';
      
      $xfn .= '  </td></tr><tr><td>';
      $xfn .= '    <span data-toggle="tooltip" title="'.$_language->get('text_convert_to_i').'">'.$_language->get('text_convert_to').'</span>';
      $xfn .= '  </td><td>';
      
      $xfn .= '    <input type="text" name="extra_func['.$count.']['.$type.'][to]" value="'.(isset($values['to']) ? $values['to'] : '').'" disabled="disabled" placeholder="Let empty for SQL format" class="form-control" '.(isset($values['fieldval']) && $values['fieldval'] !== '' ? 'style="display:none"' : '').'/>';
    }
    
    if (in_array($type, array('urlify'))) {
      $xfn .= '    <select class="form-control" name="extra_func['.$count.']['.$type.'][ascii]" disabled="disabled">';
      $xfn .= '        <optgroup label="'. $_language->get('text_urlify_basic') .'">';
      $xfn .= '          <option value="">'. $_language->get('text_urlify_default') .'</option>';
      $xfn .= '        </optgroup>';
      $xfn .= '        <optgroup label="'. $_language->get('text_urlify_ascii') .'">';
      foreach ($languages as $lang) {
      $xfn .= '          <option value="'.substr($lang['code'], 0, 2).'" '. ((isset($values['ascii']) && $values['ascii'] == substr($lang['code'], 0, 2)) ? 'selected' : '') .'>'. $_language->get('text_translit') .' '. $lang['name'] .'</option>';
      }
      $xfn .= '        </optgroup>';
      $xfn .= '    </select>';
    }
    
    // manual value or column
    if (in_array($type, array('multiply', 'divide', 'delete_item', 'skip', 'option', 'skip_db', 'field_replace'))) {
      $xfn .= '    <select class="form-control xfnFieldVal" name="extra_func['.$count.']['.$type.'][fieldval]" disabled="disabled">';
      $xfn .= '          <option value="">'. $_language->get('xfn_manual_value') .'</option>';
      foreach ($columns as $key => $row) {
        $xfn .= '          <option value="'.$key.'" '. ((isset($values['fieldval']) && $values['fieldval'] == $key && $values['fieldval'] !== '') ? 'selected' : '') .'>'. $row .'</option>';
      }
      if (!empty($values['field']) && !in_array($values['field'], array_keys($columns)) && substr($values['field'], 0, 14) != '__extra_field_') {
        $xfn .= '          <option value="'.$values['field'].'" selected>'.$values['field'].'</option>';
      }
      if (!empty($profile['extra_fields'])) {
        foreach (explode(',', $profile['extra_fields']) as $i => $extra_field_name) {
          $xfn .= '        <option value="__extra_field_'.$i.'" '. ((isset($values['fieldval']) && $values['fieldval'] == '__extra_field_'.$i) ? 'selected' : '') .'>'. trim($extra_field_name) .'</option>';
        }
      }
      $xfn .= '    </select>';
      $xfn .= '    <input type="text" name="extra_func['.$count.']['.$type.'][value]" value="'.(isset($values['value']) ? $values['value'] : '').'" disabled="disabled" class="form-control" '.(isset($values['fieldval']) && $values['fieldval'] !== '' ? 'style="display:none"' : '').'/>';
    }
    
    // extra column field
    if (in_array($type, array('field_replace'))) {
      $xfn .= '  </td></tr><tr><td>';
      $xfn .= $_language->get('text_replace_by');
      $xfn .= '  </td><td>';
      
      $xfn .= '    <select class="form-control xfnFieldVal" name="extra_func['.$count.']['.$type.'][field2]" disabled="disabled">';
      $xfn .= '          <option value="">'. $_language->get('xfn_manual_value') .'</option>';
      foreach ($columns as $key => $row) {
        $xfn .= '          <option value="'.$key.'" '. ((isset($values['field2']) && $values['field2'] == $key && $values['field2'] !== '') ? 'selected' : '') .'>'. $row .'</option>';
      }
      if (!empty($values['field']) && !in_array($values['field'], array_keys($columns)) && substr($values['field'], 0, 14) != '__extra_field_') {
        $xfn .= '          <option value="'.$values['field'].'" selected>'.$values['field'].'</option>';
      }
      if (!empty($profile['extra_fields'])) {
        foreach (explode(',', $profile['extra_fields']) as $i => $extra_field_name) {
          $xfn .= '        <option value="__extra_field_'.$i.'" '. ((isset($values['field2']) && $values['field2'] == '__extra_field_'.$i) ? 'selected' : '') .'>'. trim($extra_field_name) .'</option>';
        }
      }
      $xfn .= '    </select>';
      $xfn .= '    <input type="text" name="extra_func['.$count.']['.$type.'][field2_val]" value="'.(isset($values['field2_val']) ? $values['field2_val'] : '').'" disabled="disabled" class="form-control" '.(isset($values['field2']) && $values['field2'] !== '' ? 'style="display:none"' : '').'/>';
    }
  }
  $xfn .= '  </td></tr></table>';
  $xfn .= '  </td>';
  
  $xfn .= '  <td>';
  if (!in_array($type, array('delete_item', 'delete_item', 'skip', 'option', 'skip_db', 'multiple_separator'))) {
    $autonomousXfn = array('random', 'remote_api');
    
    if (in_array($type, $autonomousXfn) && empty($profile['extra_fields'])) {
      $xfn .= '<div class="alert alert-warning">You must define an extra field in order to use this function,<br/>please go to step 2 > Extra field to create one.</div>';
    } else {
      $xfn .= '    <select style="width:100%" class="form-control xfnFieldVal" name="extra_func['.$count.']['.$type.'][target]" disabled="disabled">';
      
      if (!in_array($type, $autonomousXfn)) {
      $xfn .= '        <optgroup label="'. $_language->get('text_function_same_field') .'">';
      $xfn .= '          <option value="" '. (empty($values['target']) ? 'selected' : '') .'>'. $_language->get('text_function_same_field') .'</option>';
      $xfn .= '        </optgroup>';
      }
      
      $xfn .= '        <optgroup label="'. $_language->get('text_extra_fields') .'">';
      //for ($i=1; $i <= 20; $i++) {
      if (!empty($profile['extra_fields'])) {
        foreach (explode(',', $profile['extra_fields']) as $i => $extra_field_name) {
          $xfn .= '          <option value="__extra_field_'.$i.'" '. ((isset($values['target']) && $values['target'] == '__extra_field_'.$i) ? 'selected' : '') .'>'. trim($extra_field_name) .'</option>';
        }
      }
      $xfn .= '        </optgroup>';
      
      $xfn .= '        <optgroup label="'. $_language->get('text_existing_fields') .'">';
      foreach ($columns as $key => $row) {
        $xfn .= '          <option value="'.$key.'" '. ((isset($values['target']) && $values['target'] == $key && $values['target'] !== '' && substr($values['target'], 0, 14) != '__extra_field_') ? 'selected' : '') .'>'. $row .'</option>';
      }
      $xfn .= '        </optgroup>';
      
      $xfn .= '    </select>';
    }
  }
  $xfn .= '  </td>';
  $xfn .= '  <td><button title="'. $_language->get('text_remove_function') .'" type="button" data-toggle="tooltip" class="btn btn-danger remove-function"><i class="fa fa-minus-circle"></i></button></td>';
  $xfn .= '</tr>';
  
  if ($count !== '') {
    $xfn = str_replace('disabled="disabled"', '', $xfn);
  }
  
  return $xfn;
}

function extraDisableConfig($columns, $profile, $_language, $languages, $vars) {
  $gkhtab = $gkhdiv = 0;
?>
  <div class="gkdwidget gkdwidget-color-blueLight clearfix">
    <header role="heading">
      <i class="icon fa fa-info-circle fa-2x pull-left" title="<?php echo $_language->get('info_help'); ?>"></i>
      <ul class="nav nav-tabs pull-left in">
        <li><a data-toggle="tab" href="#gkhelpxfndc<?php echo $gkhtab++; ?>"><span class="hidden-mobile hidden-tablet"><?php echo $_language->get('tab_disable_cfg'); ?></span></a></li>
      </ul>
    </header>
    <div class="gkdwidget-container" style="display:none">
      <div class="tab-content">
        <div class="tab-pane" id="gkhelpxfndc<?php echo $gkhdiv++; ?>"><?php echo $_language->get('info_disable_cfg'); ?></div>
      </div>
    </div>
  </div>
  
  <div class="row">
    <label class="col-sm-2 control-label"><?php echo $_language->get('entry_disable_config'); ?></label>
    <div class="col-md-8">
      <input class="form-control" type="text" name="disable_cfg" value="<?php echo isset($profile['disable_cfg']) ? $profile['disable_cfg'] : ''; ?>" placeholder="<?php echo $_language->get('placeholder_disable_config'); ?>"/>
    </div>
  </div>
<?php
}

function extraCustomFields($columns, $profile, $_language, $languages, $vars) {
  $gkhtab = $gkhdiv = 0;
?>
  <div class="gkdwidget gkdwidget-color-blueLight clearfix">
    <header role="heading">
      <i class="icon fa fa-info-circle fa-2x pull-left" title="<?php echo $_language->get('info_help'); ?>"></i>
      <ul class="nav nav-tabs pull-left in">
        <li><a data-toggle="tab" href="#gkhelpxfncs<?php echo $gkhtab++; ?>"><span class="hidden-mobile hidden-tablet"><?php echo $_language->get('tab_extra'); ?></span></a></li>
      </ul>
    </header>
    <div class="gkdwidget-container" style="display:none">
      <div class="tab-content">
        <div class="tab-pane" id="gkhelpxfncs<?php echo $gkhdiv++; ?>"><?php echo $_language->get('info_extra_field'); ?></div>
      </div>
    </div>
  </div>
  
  <?php dataField('_extra_', $_language->get('entry_extra'), $columns, $profile, $_language); ?>
  <?php if (!empty($profile['extra'])) { foreach ($profile['extra'] as $extra) { ?>
    <?php dataField($extra, $_language->get('entry_extra'), $columns, $profile, $_language); ?>
  <?php }} ?>
  <div class="row">
    <div class="col-md-offset-2 col-md-7">
      <button type="button" class="btn btn-success btn-block add-extra"><i class="fa fa-plus"></i> <?php echo $_language->get('text_add_extra_field'); ?></button>
    </div>
  </div>
  
  <hr class="dotted"/>
  
  <?php dataFieldML('_extra_', $_language->get('entry_extra_ml'), $columns, $profile, $_language, $languages, $vars['type']); ?>
  <?php if (!empty($profile['extraml'])) { foreach ($profile['extraml'] as $extra) { ?>
    <?php dataFieldML($extra, $_language->get('entry_extra_ml'), $columns, $profile, $_language, $languages, $vars['type']); ?>
  <?php }} ?>
  <div class="row">
    <div class="col-md-offset-2 col-md-7">
      <button type="button" class="btn btn-success btn-block add-extra-ml"><i class="fa fa-plus"></i> <?php echo $_language->get('text_add_extra_field_ml'); ?></button>
    </div>
  </div>
<?php
}

function extraImportFunctions($columns, $profile, $_language, $languages, $vars) {
$gkhtab = $gkhdiv = 0;

$xfn_names = array(
  'string' => array('uppercase', 'lowercase', 'ucfirst', 'ucwords', 'prepend', 'append', 'tag', 'combine', 'replace', 'remove', 'field_replace', 'if_table', 'substr', 'strlen', 'urlify'),
  'regex' => array('regex', 'regex_replace', 'regex_remove'),
  'number' => array('add', 'subtract', 'multiply', 'divide', 'percentage', 'round', 'random', 'wholesale', 'to_float'),
  'currency' => array('oc_currency'),
  'html' => array('strip_tags', 'nl2br', 'html_encode', 'html_decode', 'urlencode', 'urldecode'),
  'web' => array('remote_api', 'remote_content'),
  #custom_xfn_name#
);
if (in_array($profile['import_type'], array('product', 'product_update'))) {
  $xfn_names['product'] = array('option');
}
if (in_array($profile['import_type'], array('product', 'product_update', 'order_status_update'))) {
  $xfn_names['other'] = array('date_convert', 'skip', 'skip_db', 'delete_item', 'multiple_separator');
} else {
  $xfn_names['other'] = array('date_convert', 'skip', 'delete_item', 'multiple_separator');
}
?>

<div class="gkdwidget gkdwidget-color-blueLight clearfix">
  <header role="heading">
    <i class="icon fa fa-info-circle fa-2x pull-left" title="<?php echo $_language->get('info_help'); ?>"></i>
    <ul class="nav nav-tabs pull-left in">
      <li><a data-toggle="tab" href="#gkhelpxfn<?php echo $gkhtab++; ?>"><span class="hidden-mobile hidden-tablet"><?php echo $_language->get('tab_functions'); ?></span></a></li>
    </ul>
  </header>
  <div class="gkdwidget-container" style="display:none">
    <div class="tab-content">
      <div class="tab-pane" id="gkhelpxfn<?php echo $gkhdiv++; ?>"><?php echo $_language->get('info_extra_functions'); ?></div>
    </div>
  </div>
</div>
          
<table id="extraFuncs" class="table table-bordered">
  <thead>
    <tr class="nodrag">
      <th style="width:1%;"></th>
      <th style="width:200px;"><?php echo $_language->get('text_function_type'); ?></th>
      <th><?php echo $_language->get('text_function_action'); ?></th>
      <th><?php echo $_language->get('text_function_target'); ?></th>
      <th style="width:55px;"></th>
    </tr>
  </thead>
  <tbody>
      <?php $i=1; if(!empty($profile['extra_func'])){ foreach ($profile['extra_func'] as $extra_funcs) {
        foreach ($extra_funcs as $funcName => $values) {
          echo getXfn($funcName, $values, $i++, $columns, $profile, $_language, $languages, $vars);
        }
      }} ?>
    <tr class="nodrag nodrop">
      <td colspan="4" style="text-align:center" class="form-inline">
        <select class="form-control_ extra_func_select" id="extra_func_select" style="">
          <?php foreach ($xfn_names as $groupName => $group) { ?>
          <optgroup label="<?php echo ($_language->get('xfn_group_'.$groupName) != 'xfn_group_'.$groupName) ? $_language->get('xfn_group_'.$groupName) : $groupName; ?>">
            <?php foreach ($group as $funcName) { ?>
              <option value="<?php echo $funcName; ?>"><?php echo ($_language->get('xfn_'.$funcName) != 'xfn_'.$funcName) ? $_language->get('xfn_'.$funcName) : ucfirst(str_replace('_', ' ', $funcName)); ?></option>
            <?php } ?>
          <?php } ?>
          </optgroup>
        </select>
        <button type="button" class="btn btn-success add-function"><i class="fa fa-plus"></i> <?php echo $_language->get('text_add_function'); ?></button>
      </td>
    </tr>
  </tbody>
</table>
<table id="extraFuncsSrc" style="display:none">
  <?php foreach ($xfn_names as $groupName => $group) {
    foreach ($group as $funcName) {
      echo getXfn($funcName, '', '', $columns, $profile, $_language, $languages, $vars);
    }
  } ?>
</table>

<?php } ?>

<div class="spacer"></div>

<h4><?php echo $_language->get('text_data_preview'); ?></h4>

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
        <?php foreach ($columns as $key => $col) { ?>
        <?php
        if ($filetype == 'xml') {
          $key = $col;
        }
        
        if (isset($row[$key]) && is_array($row[$key])) {
          echo '<td class="limitHeight"><pre>'.print_r($row[$key], 1).'</pre></td>'; /*echo '[Array of values]';/*var_export($key);*/
        } else if (isset($row[$key]) && strlen($row[$key]) > 100) {
          echo '<td class="limitHeight"><div>'.$row[$key].'</div></td>';
        } else if (isset($row[$key])) {
          echo '<td>'.$row[$key].'</td>';
        } else {
          echo '<td></td>';
        }
        ?></td>
        <?php } ?>
      </tr>
      <?php } ?>
    </tbody>
  </table>
</div>

<hr />

<input type="hidden" name="column_headers" value="<?php echo base64_encode(json_encode($columns)); ?>" />
<style>
.data-array pre{border:0; background:transparent; color:inherit; padding:0; margin:0; line-height:inherit;}
.extra_func_select{font-family: FontAwesome, sans-serif; text-align:left;width: 300px; display: inline-block; vertical-align: middle;}
.extra_func_select .selectize-input{height: 36px; margin-top: 5px;}
.extra_func_select .selectize-dropdown-content{max-height: 400px;}
.tDnD_whileDrag{background-color:#eef8f7;}
</style>
<script>
$(document).ready(function() {
	$("#extraFuncs").tableDnD();
});
$('#extra_func_select').selectize();
$('#extraFuncs .selectize').selectize();
</script>