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
<div class="spacer"></div>

<input type="hidden" name="import_filetype" value="<?php echo $filetype; ?>"/>
<input type="hidden" name="import_compression" value="<?php echo $compression; ?>"/>

<ul class="nav nav-tabs">
  <li class="active"><a href="#tab-setting-common" data-toggle="tab"><?php echo $_language->get('text_common_settings'); ?></a></li>
</ul>

<div class="tab-content">
  <div class="tab-pane active" id="tab-setting-common">
  
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
  
</div>

<div class="pull-right">
  <button type="button" class="btn btn-default cancel" data-step="2"><i class="fa fa-reply"></i> <?php echo $_language->get('text_previous_step'); ?></button>
  <button type="button" class="btn btn-success submit" data-step="2"><i class="fa fa-check"></i> <?php echo $_language->get('text_next_step'); ?></button>
</div>

<div class="spacer"></div>

<script type="text/javascript">
$('select[name=delete]').trigger('change');
</script>