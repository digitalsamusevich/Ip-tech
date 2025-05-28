<div class="show_export_csv show_export_txt" <?php if ((isset($_POST['export_format']) && !in_array($_POST['export_format'], array('csv', 'txt', 'tsv'))) || (isset($profile['export_format']) && !in_array($profile['export_format'], array('csv', 'txt', 'tsv')))) echo 'style="display:none"'; ?>>
  <fieldset class="filters"><legend><?php echo $_language->get('text_csv_settings'); ?></legend>
    <div class="row">
      <div class="col-sm-12">
        <div class="form-group">
          <label class="control-label"><?php echo $_language->get('entry_csv_separator'); ?></label>
          <div class="input-group">
          <span class="input-group-addon"><i class="fa fa-fw fa-code"></i></span>
          <select name="export_separator" class="form-control" style="font-size:14px">
            <?php foreach ($separators as $val => $name) { ?>
            <option value="<?php echo $val; ?>" <?php if (isset($profile['export_separator']) && $profile['export_separator'] == $val) echo 'selected'; ?>><?php echo $name; ?></option>
            <?php } ?>
          </select>
          </div>
        </div>
      </div>
    </div>
  </fieldset>
</div>

<hr class="dotted"/>