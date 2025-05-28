<html lang="en">

    <head>
        <script src="/catalog/view/javascript/jquery/jquery-3.1.1.min.js" type="text/javascript"></script>
        <script src="/catalog/view/javascript/jquery/jquery-migrate-3.0.0.min.js" type="text/javascript"></script>
    </head>
    <style>
     
        #panel{
            display: block;
            float: left;
            width: 500px;
            min-height: 600px;
        }
        
        label{
            display: block;
            float: left;
            width: calc(100% - 35px);
            height: auto;
            margin: 0 0 5px 0; 
        }
        
        #panel-left{
            display: block;
            float: left;
            width: 210px;
            height: auto;
            margin: 0 10px;
        }
        
        #panel-left select{
            display: block;
            float: left;
            width: 100%;
            height: 30px;
            margin: 0 0 15px 0; 
            background: #ccc;
            border: none;
            outline: none;
            cursor: pointer;
        }
        
        
        #panel-right{
            display: block;
            float: left;
            width: 250px;
            margin: 0 10px;
        }
        
        #panel-right #tables{
            display: block;
            float: left;
            width: calc(100% - 30px);
            padding: 15px;
            background: #ccc;
        }
        
        #panel-right #placeholder{
            display: block;
            float: left;
            width: 100%;
            text-align: center;
        }
        
        #panel-right #stroke-table{
            display: block;
            float: left;
            width: 100%;
            height: auto;
        }
        
        #panel-right input{
            display: block;
            float: left;
            width: 20px;
            height: auto;
        }
        form{
            display: block;
            float: left;
        }
        
        .info{
            display: block;
            float: left;
            width: 700px;
            margin: 0 10px;
            max-height: 650px;
            overflow-x: hidden;
        }
        
        .info .stroke{
            padding-left: 15px;
        }
        
        .info .stroke-finish{
            padding: 20px 0 0 15px;
        }
        
        #bottom{
            display: block;
            float: left;
            width: 700px;
            margin: 0 10px;
        }
        
        #bottom #time{
            padding: 20px 0 0 15px;
        }
        
        #frame{
            display: block;
            float: left;
            width: 100%;
            margin: 0 10px 15px 10px;
        }
        

        #frame #items-group{
            display: block;
            float: left;
            width: 50%;
        }

        #frame #stroke{
            display: block;
            float: left;
            width: 100%;
            padding: 0 20px;
        }
        

        #frame label{
            display: block;
            float: left;
            width: 100%;
            padding: 0 20px;
        }
        
        #frame .loader{
            display: block;
            float: left;
            width: 100%;
            padding: 0 20px;
        }

        
        #frame #button{
            display: block;
            float: left;
            width: 150px;
            height: 18px;
            padding: 15px 0;
            text-align: center;
            background: #ccc;
            cursor: pointer;
        }
        
        .main_option:last{
            padding: 0 0 10px 0;
        }

    </style>

    <body>

     
    <div id="pagewrap-firts">
        <div id="panel">
           <form id="panel-info">
            <div id="panel-left">
                <label for="select_column_name">Select Column ID:</label>
                <select name="column_name" id="select_column_name">
                    <?php foreach ($columns_name as $key => $column) {   ?>
                         <?php if ($column['column_name'] == 'product_id'){ ?> 
                                <option class="main_option" style="font-weight: bold;" selected><? echo $column['column_name'] ?></option>
                         <? } else { ?>
                         <?php if ($column['column_name'] == 'category_id' || $column['column_name'] == 'manufacturer_id' || $column['column_name'] == 'information_id' || $column['column_name'] == 'url_alias_id'){ ?>
                                <option class="main_option" style="font-weight: bold;"><? echo $column['column_name'] ?></option>
                         <? } else { ?>
                                <option><? echo $column['column_name'] ?></option>
                         <? } ?>
                        <? } ?>
                        <? } ?>
                </select>
                <script>
                    $('.main_option').each(function() {
                        $('#select_column_name').prepend(this);
                    });
                </script>
                <div id="table-main">
                    <label for="select">Select MAIN table:</label>
                    <select disabled="disabled" name="table_main" id="table_main_select"></select>
                </div>
               <div id="table-compare">
                  
                   <label for="select">Select COMPARE table:</label>
                   <select disabled="disabled" name="table_compare" id="table_compare_select"></select>
               </div>
            </div>
            </form>
            <form id="panel-tables" name="tables">
                <div id="panel-right">
                    <label for="select">Tables in which this Column</label>
                    <div id="tables">
                        <div id="placeholder">Not Found</div>
                    </div>
                </div>
            </form>

        </div>
        <div class="info">
            <div id="frame">

            </div>
            
        </div>
        <div id="bottom">
            <div id="time"></div>
            </br>
        </div>
        <script>
            var on = true;
            getTables();
            
            var select_column_name = document.getElementById("select_column_name");
            select_column_name.addEventListener("change", function() {
                getTables();
            });
            
            var table_main_select = document.getElementById("table_main_select");
            table_main_select.addEventListener("change", function() {
                if($(table_compare_select).length > 0){
                    getItems();
                    var input = document.getElementsByName('table_name[]');

                    $("#table_main_select option").each(function() {
                        if(this.selected){
                            var selected_value = $(this).html();

                            for(var i=0;i<input.length;i++){

                                if ($(input[i]).hasClass('main_checkbox')){
                                    input[i].disabled=true;
                                    $(input[i]).prop( "checked", false );
                                } 
                                
                                if(input[i].value == selected_value){
                                    $(input[i]).addClass('main_checkbox');
                                    input[i].disabled=false;
                                    $(input[i]).prop( "checked", true );
                                }
                                
                            }

                            $(this).prop( "selected", true );

                        } else {
                            $(this).prop( "selected", false );
                        }


                    });
                };
            });
            
            var table_compare_select = document.getElementById("table_compare_select");
            table_compare_select.addEventListener("change", function() {
                
                if($(table_main_select).length > 0){
                    getItems();
                    var input = document.getElementsByName('table_name[]');
                    
                    $("#table_compare_select option").each(function() {
                        if(this.selected){
                            var selected_value = $(this).html();
                            
                                for(var i=0;i<input.length;i++){
                                    
                                    if (!$(input[i]).hasClass('main_checkbox')){
                                        input[i].disabled=true;
                                        $(input[i]).prop( "checked", false );
                                    } 

                                    if(input[i].value == selected_value){
                                        input[i].disabled=false;
                                        $(input[i]).prop( "checked", true );
                                    }
                                }
                                
                            $(this).prop( "selected", true );
                            
                        } else {
                            $(this).prop( "selected", false );
                        }
                      
                           
                    });
                }
            });
            

            (s=document).onclick=function checkselect(evt) 
            { 
                evt=evt||event; 
                if((checkObj=s.all?evt.srcElement:evt.target).tagName!='INPUT')return;   
                if(checkObj.name!='table_name[]')return; 
                else{ 
                    var checkedValue = null;
                    for(var i=0,check=s.getElementsByName(checkObj.name),checksum=0;i<check.length;i++){
                        if(check[i].checked){
                            checksum+=1;
                        } else {
                            check[i].disabled=false; 
                        }
                    }
                    
                    if(checksum=='0'){
                        $('#table_main_select option').each(function(){
                            if($(this).hasClass('first')){
                                $(this).addClass('main_checkbox');
                                $(this).prop( "selected", true );
                            } else {
                                $(this).prop( "selected", false );
                            }
                        });
                    }
                    
                    if(checksum=='1')for(var i=0;i<check.length;i++){
                        if(check[i].checked){
                            $('#table_main_select option').each(function(){
                                $(this).removeClass('main_checkbox');
                                if($(this).html() == check[i].value){
                                    $(this).addClass('main_checkbox');
                                    $(this).prop( "selected", true );
                                } else {
                                    $(this).prop( "selected", false );
                                }
                            });
                            
                            $('#table_compare_select option').each(function(){
                                if($(this).hasClass('first')){
                                    $(this).prop( "selected", true );
                                } else {
                                    $(this).prop( "selected", false );
                                }
                            });
                       
                            
                        }
                    }
                    
                    if(checksum=='2')for(var i=0;i<check.length;i++){
                        if(!check[i].checked){
                            check[i].disabled=true;
                        }

                        if(check[i].checked){
                            var check_value = check[i].value;

                            $('#table_compare_select option').each(function(){
                                if($(this).html() == check[i].value && !$(check[i]).hasClass('main_checkbox')){
                                    $(this).prop( "selected", true );
                                } else {
                                    $(this).prop( "selected", false );
                                }
                                
                            });
                        }
                    }
                    getItems();
                } 
            } 


            function getTables(){
                    var msg = $('form#panel-info').serialize();
                    $.ajax({
                        type: 'POST',
                        url: '/index.php?route=repair/compare_tables/getTables',
                        data: msg,
                        success: function (data) {
                            var tables = $(data).filter('#tables-wrap').html(),
                                table_main = $(data).filter('#table-main').children('#table_main_select').html(),
                                table_compare = $(data).filter('#table-main').children('#table_compare_select').html();
                            $('#tables').html(tables);
                            $('#table_main_select').html(table_main);
                            $('#table_compare_select').html(table_compare);
                            $('#table_main_select').removeAttr("disabled");
                            $('#table_compare_select').removeAttr("disabled");
                            $('#table_main_select .main_option').each(function() {
                                $('#table_main_select').prepend(this);
                            });
                            $('#table_main_select .url_alias').each(function() {
                                $('#table_main_select').append(this);
                            });
                        
                            $('#table_compare_select .url_alias').each(function() {
                                $('#table_compare_select').append(this);
                            });
                            
                            $('#tables .url_alias').each(function() {
                                $('#tables').append(this);
                            });
                            getItems();
                        },
                        error: function (xhr, ajaxOptions, thrownError) {
                            location.reload();
                        }
                    });
            }
            
            function getItems(){
                    var msg = $('form#panel-info').serialize();
                    $.ajax({
                        type: 'POST',
                        url: '/index.php?route=repair/compare_tables/getItems',
                        data: msg,
                        beforeSend: function(){
                            
                            window.loader = document.createElement('div');
                            $(loader).addClass('loader');
                            $(loader).text('Загрузка')
                            var span = document.createElement('span');
                            $(loader).append(span);
                            var k = 0;
                            window.dots_loader = setInterval(function () {
                                if (k <= 10) {
                                    dots = '.';
                                    k++;
                                    $(span).append(dots);
                                } else {
                                    $(span).empty();
                                    k = 0;
                                }
                            }, 50)
                            $('#frame').html(loader);
                        },
                        success: function (data) {
                            clearInterval(dots_loader);
                            $(loader).remove();
                            var items = $(data).filter('#items-wrap').html();
                            $('#frame').html(items);
                        },
                        error: function (xhr, ajaxOptions, thrownError) {
                            location.reload();
                        }
                    });
            }
            
            function animation() {
                if(on){
                    if ($('.info .stroke').length > 0) {
                        //document.getElementById('bottom').scrollIntoView(true);
                        var dots = '';
                        var s = document.querySelector('.info .stroke:last-child #product');

                        var w = Math.round(Math.round(s.offsetWidth) / 5),
                            k = 0;
                        window.dots_generator = setInterval(function () {
                            if (k <= 100 - w) {
                                if ((100 - w) - k >= 40) {
                                    dots = '<span>.</span><span>.</span><span>.</span><span>.</span><span>.</span><span>.</span><span>.</span><span>.</span><span>.</span><span>.</span>';
                                    k = k + 10;
                                } else if ((100 - w) - k > 10 && (100 - w) - k < 40) {
                                    dots = '<span>.</span><span>.</span><span>.</span><span>.</span><span>.</span>';
                                    k = k + 5;
                                } else {
                                    dots = '<span>.</span>';
                                    k++;
                                }

                                window.d = $(s).parent().append(dots);

                                /*if ($('form').length > 0) {
                                    document.querySelector('input#dots').value += dots;
                                }*/

                            } else {
                                if ($('form').length > 0) {

                                    defrag();
                                } else {
                                    $(d).append('SUCCESS');
                                    clearInterval(dots_generator);
                                    defrag();
                                }
                            }
                        }, 1)
                    } else {
                        $('.info').html("<div class='stroke-start'><span id='start'>start</span></div>");
                        $('#bottom').append("<div id='button-stop' onclick='stop()'>STOP</div>");
                        defrag();
                    }
                }
            }
            
            function defrag() {
                if(on){
                    if ($('.info .stroke').length > 0) {
                        clearInterval(dots_generator);
                    }

                    var msg = $('form').serialize();

                    $.ajax({
                        type: 'POST',
                        url: '/index.php?route=repair/defragmentation/defrag',
                        data: msg,
                        success: function (data) {
                            var stroke = $(data).filter('.info-wrap').html(),
                                 total = $(data).filter('#bottom').find('#total').text();
 

                            if ($('.info .stroke').length == 0) {
                                var stop = $(data).filter('#bottom').find('#frame-wrap').html();
                                $('#frame').html(stop);
                            }
                            
                            if ($('.info .stroke').length > 0) {
                                $(d).append('SUCCESS');
                            }
                            
                            $('.info').append(stroke);

                            
                            $('#bottom #total').html(total);
                            
                            if(document.querySelector('.info').childNodes.length > 40){

                                if ($('.stroke-start').length > 0) {
                                    $('.info').find('.stroke-start').remove();
                                } else {
                                    $('.info').find('.stroke:first').remove();
                                }
                            }
                            
                            if ($(stroke).length > 0) {
                                if(!$('#button').hasClass('stop')){
                                    animation();
                                };
                            } else if(data == 'refresh'){
                                $('#refresh').html('Refresh');
                                location.reload();
                            } else {
                                stop();
                            }
                        },
                        error: function (xhr, ajaxOptions, thrownError) {
                            location.reload();
                        }
                    });
                }
            }
   
            function refresh() {
                if(on){
                    var msg = $('form').serialize();

                    $.ajax({
                        type: 'POST',
                        url: '/index.php?route=repair/defragmentation/clear',
                        data: msg,
                        success: function (data) {
                            var wrap = $(data).filter('#pagewrap').html(),
                                total = $(data).filter('#bottom').find('#total').text(),
                                stroke = $(data).filter('.info-wrap').find('.stroke:last');
                            
                            $('#frame').html(wrap);
                            $('#bottom #total').html(total);


                            $('.info').append(stroke);

                            if(!$('#button').hasClass('stop')){
                                animation();
                            };


                        },
                        error: function (xhr, ajaxOptions, thrownError) {
                            location.reload();
                        }
                    });
                }
            }


            function stop() {

                on = false;

                $('#button').text("CLEAR");
                $('#button').attr("onclick","location.reload();");

                $.ajax({
                    type: 'POST',
                    url: '/index.php?route=repair/defragmentation/stop',
                    success: function (data) {
                        var time = $(data).filter('#time').html();
                        $('#bottom #time').html(time)
                        $('.info').append("<div class='stroke-finish'><span id='finish'>finish</span></div>");
                    },
                    error: function (xhr, ajaxOptions, thrownError) {
                        location.reload();
                    }
                });

            }

        </script>

    </div>

<?php
/*if(!empty($_SESSION['info'])){
    echo '<script>refresh();</script>';
}*/
?>
</body>

</html>