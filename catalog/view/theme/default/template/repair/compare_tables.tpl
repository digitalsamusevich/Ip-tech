
<div id="tables-wrap">
   
    
    <?php foreach ($tables as $key => $table) { ?>
    <div id="stroke-table">
        <?php if($table['table_name'] == $primary['table_name']){ ?> 
        <input type="checkbox" id="table_name_<? echo $key; ?>" name="table_name[]" class="main_checkbox first" checked value="<? echo $table['table_name']; ?>"/>
        <label for="table_name_<? echo $key; ?>"><? echo $table['table_name']; ?></label>
        <?php if($table['table_name'] == 'product' || $table['table_name'] == 'category' || $table['table_name'] == 'manufacturer' || $table['table_name'] == 'information'){ ?>
                <div id="stroke-table" class="url_alias">
                    <input type="checkbox" id="table_name_url_alias" name="table_name[]"  value="url_alias"/>
                    <label for="table_name_url_alias">url_alias</label>
                </div>
        <? } ?>
        <? } else { ?>
        <input type="checkbox" id="table_name_<? echo $key; ?>" name="table_name[]"  value="<? echo $table['table_name']; ?>"/>
        <label for="table_name_<? echo $key; ?>"><? echo $table['table_name']; ?></label>
         
        <? } ?>
        
        <?php if($table['table_name'] == 'url_alias'){ ?>
        <input type="checkbox" id="table_name_2" name="table_name[]"  value="product"/>
        <label for="table_name_2">product</label>
        <input type="checkbox" id="table_name_3" name="table_name[]"  value="category"/>
        <label for="table_name_3">category</label>
        <input type="checkbox" id="table_name_4" name="table_name[]"  value="manufacturer"/>
        <label for="table_name_4">manufacturer</label>
        <input type="checkbox" id="table_name_5" name="table_name[]"  value="information"/>
        <label for="table_name_5">information</label>
     
        <? } ?>
        
    </div>
    <?  } ?>
</div>

<div id="table-main">
    <select id="table_main_select">
    <?php foreach ($tables as $key => $table) { ?>
        <?php if($table['table_name'] == $primary['table_name']){ ?> 
            <option class="main_option" style="font-weight: bold;" selected><? echo $table['table_name']; ?></option>

        <? } else { ?>  
           
            <option><? echo $table['table_name']; ?></option>   
        <? } ?>
    <? } ?>     
    </select>
    
    <select id="table_compare_select">
        <option class="first" selected>Select Table to Compare</option>   
        <?php foreach ($tables as $key => $table) { ?>
  
        <option><? echo $table['table_name']; ?></option>   
        
        <?php if($table['table_name'] == 'product' || $table['table_name'] == 'category' || $table['table_name'] == 'manufacturer' || $table['table_name'] == 'information'){ ?>
            <option class="url_alias">url_alias</option>   
        <? } ?>
        
        <?php if($table['table_name'] == 'url_alias'){ ?>
            <option>product</option>
            <option>category</option>   
            <option>manufacturer</option>     
            <option>information</option>    
        <? } ?> 
       
        <? } ?>   
    </select>
</div>


   

    