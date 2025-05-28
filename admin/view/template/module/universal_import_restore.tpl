<div class="spacer"></div>

<div class="spacer"></div>


  <div class="pull-right">
    <button type="button" class="btn btn-default cancel" data-step="3"><i class="fa fa-reply"></i> <?php echo $_language->get('text_previous_step'); ?></button>
    <button type="button" class="btn btn-success submit" data-step="3"><i class="fa fa-check"></i> <?php echo $_language->get('text_next_step'); ?></button>
  </div>
<div class="spacer"></div>

<script type="text/javascript">
$('button[data-step="3"]').trigger('click');
</script>