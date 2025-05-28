<div id="items-wrap">
    <div id="items-group">
        <label>Items that are missing&nbsp; &nbsp; <? if(!empty($items)){ echo count($items); } ?></label>
    <?php if(!empty($items)){
     foreach ($items as $key => $item) { ?>
    <div id="stroke">
        <div><? echo $item[$column]; ?></div>
    </div>
    <?  }} else { ?>
    <div id="stroke">empty</div>
    <? } ?>
    </div>
    <div id="items-group">
    <label>Models that are missing</label>
    <?php if(!empty($items)){
        foreach ($items as $key => $item) { ?>
    <div id="stroke">
        <div><? if(!empty($item['model'])){ echo $item['model']; } else { echo 'NOT FOUND'; } ?></div>
        <!--<div>INSERT INTO `iptech`.`url_alias` (`url_alias_id`, `query`, `keyword`) VALUES (NULL, '<? echo $item[$column]; ?>', '<? if(!empty($item['model'])){ echo $item['model']; } else { echo 'NOT FOUND'; } ?>');</div>-->
    </div>
    <?  }}else { ?>
    <div id="stroke">empty</div>
    <? } ?>

</div>

   

    