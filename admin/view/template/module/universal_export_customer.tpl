<?php include 'universal_export_functions.tpl'; ?>
<fieldset class="filters"><legend><div class="pull-right" style="font-size:13px; color:#666"><?php echo $_language->get('total_export_items'); ?> <span class="export_number badge clearblue"></span></div><?php echo $_language->get('export_filters'); ?></legend>

  <div class="row">
    <div class="col-sm-6">
      <div class="form-group">
        <label class="control-label"><?php echo $_language->get('filter_language'); ?></label>
        <div class="input-group">
        <span class="input-group-addon"><i class="fa fa-flag"></i></span>
        <select class="form-control" name="filter_language">
          <?php foreach ($languages as $language) { ?>
            <option value="<?php echo $language['language_id']; ?>" <?php if (isset($profile['filter_language']) && $profile['filter_language'] !== '' && $profile['filter_language'] == $language['language_id']) echo 'selected'; ?>><?php echo $language['name']; ?></option>
          <?php } ?>
        </select>
        </div>
      </div>
    </div>
    
    <div class="col-sm-6">
      <div class="form-group">
        <label class="control-label"><span data-toggle="tooltip" title="<?php echo $_language->get('filter_limit_i'); ?>"><?php echo $_language->get('filter_limit'); ?></span></label>
        <div class="input-group">
        <input type="text" class="form-control" name="filter-start" value="<?php echo isset($profile['filter-start']) ? $profile['filter-start'] : ''; ?>" placeholder="<?php echo $_language->get('filter_limit_start'); ?>" />
        <span class="input-group-addon">-</span>
        <input type="text" class="form-control" name="filter-limit" value="<?php echo isset($profile['filter-limit']) ? $profile['filter-limit'] : ''; ?>" placeholder="<?php echo $_language->get('filter_limit_limit'); ?>"/>
        </div>
      </div>
    </div>
  </div>

</fieldset>

<fieldset><legend><?php echo $_language->get('export_options'); ?></legend>
 <div class="row">
    <div class="col-sm-4">
      <div class="form-group">
        <?php fieldLabel('param_date_format', $_language); ?>
        <div class="input-group">
        <span class="input-group-addon"><i class="fa fa-fw fa-calendar"></i></span>
          <input type="text" class="form-control" name="date_format" value="<?php echo isset($profile['date_format']) ? $profile['date_format'] : ''; ?>" placeholder="d/m/Y H:i"/>
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
getTotalExportCount();

var $selectize_category = $('.selectize_category').selectize({
  maxItems: null
});
</script>