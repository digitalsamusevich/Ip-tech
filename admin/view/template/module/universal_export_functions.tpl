<?php function fieldLabel($label, $_language) { ?>
  <?php if ($_language->get($label.'_i') != $label.'_i') { ?>
    <label class="control-label"><span data-toggle="tooltip" title="<?php echo $_language->get($label.'_i'); ?>"><?php echo $_language->get($label); ?></span></label>
  <?php } else { ?>
    <label class="control-label"><?php echo $_language->get($label); ?></label>
  <?php } ?>
<?php } ?>