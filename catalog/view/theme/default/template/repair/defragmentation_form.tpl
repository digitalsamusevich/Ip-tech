<div class="info-wrap">
<?php 
if($_SESSION['current_key'] < count($_SESSION['items'])){
$_SESSION['total'] = $_SESSION['total'] + 1;

$current_key = $_SESSION['current_key'];
echo "<div class='stroke' id='" . $items . "'><span id='product'>" . $column . ": " . $items . "&nbsp; change ID to '" . $_SESSION['total'] . "'&nbsp;&nbsp;&nbsp;</span></div>";
} 
?>
</div>



<div id="pagewrap">
    <?php 
    if($current_key < count($_SESSION['items']) && $current_key != 'undefined'){
    ?>
    <form method="post">
        <input type="hidden" name="current_key" value="<?php echo $_SESSION['current_key']; ?>">
        <input type="hidden" name="items" value="<?php echo $items; ?>">
        <input type="hidden" id="title" name="title" value="<?php echo $name; ?>">
        <input type="hidden" id="dots" name="dots" value="">
    </form>
    <? } ?>
</div>

<div id="bottom">
    </br>
    
    <span id="total">
       <?php  if(isset($_SESSION['total'])) { ?>
        Total: <?php echo $_SESSION['total']; ?>&nbsp;&nbsp;[0-<? echo $_SESSION['total']-1; ?>]&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
        <? } ?>
    </span>
    
    <div id="frame-wrap">
        <div id="button-stop">
            <label class="start">Total items will be processed:&nbsp;<? echo count($_SESSION['items']); ?></label>
            <div id="button" onclick="stop()">STOP</div>
        </div>
    </div>


</div>