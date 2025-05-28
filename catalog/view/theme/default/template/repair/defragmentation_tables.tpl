
<div id="tables-wrap">
    <?php foreach ($tables as $key => $table) { ?>
    <div id="stroke-table">
        <input type="checkbox" id="table_name_<? echo $key; ?>" name="table_name[]" checked value="<? echo $table['table_name']; ?>"/>
        <label for="table_name_<? echo $key; ?>"><? echo $table['table_name']; ?></label>
    </div>
    <?  } ?>
</div>

<div id="table-main">
    <select id="table_main_select">
    <?php foreach ($tables as $key => $table) { ?>
        <?php if($table['table_name'] == $primary['table_name']){ ?> 
            <option selected><? echo $table['table_name']; ?></option>            
        <? } else { ?>
            <option><? echo $table['table_name']; ?></option>   
        <? } ?>
    <? } ?>     
    </select>
</div>
   
   

    