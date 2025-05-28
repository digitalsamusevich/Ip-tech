<?php include 'universal_export_functions.tpl'; ?>
<fieldset class="filters"><legend><div class="pull-right" style="font-size:13px; color:#666"><?php echo $_language->get('total_export_items'); ?> <span class="export_number badge clearblue"></span></div><?php echo $_language->get('export_filters'); ?></legend>

  <div class="row">
    <div class="col-sm-4">
      <div class="form-group">
        <?php fieldLabel('mode', $_language); ?>
        <div class="input-group">
        <span class="input-group-addon"><i class="fa fa-fw fa-flag"></i></span>
        <select class="form-control" name="mode">
          <option value="product" <?php if (isset($profile['mode']) && $profile['mode'] == 'product') echo 'selected'; ?>><?php echo $_language->get('export_product_filters'); ?></option>
          <option value="all" <?php if (isset($profile['mode']) && $profile['mode'] == 'all') echo 'selected'; ?>><?php echo $_language->get('export_all_filters'); ?></option>
        </select>
        </div>
      </div>
    </div>
      
    <div class="col-sm-4">
    </div>
    
    <div class="col-sm-4">
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

<div class="spacer"></div>

<?php include 'universal_export_common.tpl'; ?>

<script type="text/javascript">
getTotalExportCount();
</script>