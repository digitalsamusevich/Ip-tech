<?php 

if($_SESSION['total_refresh'] < 100){

?>
    <div class="info-wrap">
            <?php 
            if($_SESSION['current_key'] >= 0){
                $_SESSION['total'] = $_SESSION['total'] + 1;
                $_SESSION['total_refresh'] = $_SESSION['total_refresh'] + 1;
                if(count($_SESSION['info']) > 28){ 
                    array_shift($_SESSION['info']);
                }
                    $current_key = $_SESSION['current_key'];
        
                    echo "<div class='stroke' id='" . $product_id . "'><span id='product'>product_id: " . $product_id . "&nbsp;&nbsp;&nbsp;&nbsp;" . html_entity_decode($name) . "</span></div>";
               
                } 
            ?>
    </div>
    

    
    <div id="pagewrap">
    
    <script src="/catalog/view/javascript/service/html-cleaner.js" type="text/javascript"></script>

            <?php 
            if($current_key >= 1 && $current_key != 'undefined'){
  
             
            ?>
            <form method="post">
                <div style="display:none" id="result"></div>
                <input type="hidden" name="current_key" value="<?php echo $_SESSION['current_key']; ?>">
                <input type="hidden" name="product_id" value="<?php echo $product_id; ?>">
                <input type="hidden" id="title" name="title" value="<?php echo $name; ?>">
                <input type="hidden" id="title" name="model" value="<?php echo $model; ?>">
                <input type="hidden" id="dots" name="dots" value="">
                <textarea name="description" hidden="hidden" rows="25" id="elm1" name="elm1">
                    <?php echo $description;?>
                </textarea>
            </form>
            <?
            }
            ?>
        </div>
        
<div id="bottom">
    <br>
        <span id="total">
            <?php if(isset($_SESSION['total'])) { ?>
                Total: <?php echo $_SESSION['total']; ?>&nbsp;&nbsp;[0-<? echo $_SESSION['total']-1; ?>]&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
            <? } ?>
        </span>
           
</div>
<?php
    echo $current.'</br>';
    echo $info_last_id[1].'</br>';
   
    } else {
        $_SESSION['current_key'] = $_SESSION['current_key'] + 1;
        unset($_SESSION['total_refresh']);
        if(!isset($_SESSION['refresh'])){
            $_SESSION['refresh'] = 1;
        } else {
            $_SESSION['refresh'] = $_SESSION['refresh'] + 1;
        }
        echo "refresh";    
    } 
?>