<html lang="en">

<head>
    <script src="/catalog/view/javascript/jquery/jquery-3.1.1.min.js" type="text/javascript"></script>
    <script src="/catalog/view/javascript/jquery/jquery-migrate-3.0.0.min.js" type="text/javascript"></script>
</head>
<style>
    #panel {
        display: block;
        float: left;
        width: 500px;
        min-height: 600px;
    }
    
    label {
        display: block;
        float: left;
        width: calc(100% - 35px);
        height: auto;
        margin: 0 0 5px 0;
    }
    
    #panel-left {
        display: block;
        float: left;
        width: 210px;
        height: auto;
        margin: 0 10px;
    }
    
    #panel-left select {
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
    
    #panel-right {
        display: block;
        float: left;
        width: 250px;
        margin: 0 10px;
    }
    
    #panel-right #tables {
        display: block;
        float: left;
        width: calc(100% - 30px);
        padding: 15px;
        background: #ccc;
    }
    
    #panel-right #placeholder {
        display: block;
        float: left;
        width: 100%;
        text-align: center;
    }
    
    #panel-right #stroke-table {
        display: block;
        float: left;
        width: 100%;
        height: auto;
    }
    
    #panel-right input {
        display: block;
        float: left;
        width: 20px;
        height: auto;
    }
    
    .info {
        display: block;
        float: left;
        width: 700px;
        margin: 0 10px;
    }
    
    .info .stroke-finish {
        padding: 20px 0 0 0;
    }
    
    #bottom {
        display: block;
        float: left;
        width: 700px;
        margin: 0 10px;
    }
    
    #bottom #time, #bottom #total{
        float: left;
        padding: 10px 0 0 10px;
    }
    
    #frame {
        display: block;
        float: left;
        width: 100%;
        margin: 0 10px 15px 10px;
    }
    
    #frame #button-start{
        display: block;
        float: left;
        width: 100%;
        height: 85px;
    }
    
    #frame #button {
        display: block;
        float: left;
        width: 150px;
        height: 18px;
        padding: 15px 0;
        text-align: center;
        background: #ccc;
        cursor: pointer;
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
                                <option class="main_option" style="font-weight: bold;" selected>
                                    <? echo $column['column_name'] ?>
                                </option>
                             <? } else { ?>
                                <?php if ($column['column_name'] == 'category_id' || $column['column_name'] == 'manufacturer_id' || $column['column_name'] == 'information_id' || $column['column_name'] == 'url_alias_id'){ ?>
                                    <option class="main_option" style="font-weight: bold;">
                                        <? echo $column['column_name'] ?>
                                    </option>
                                <? } else { ?>
                                    <option>
                                        <? echo $column['column_name'] ?>
                                    </option>
                                <? } ?>
                                <? } ?>
                         <? } ?>
                    </select>

                    <script>
                        $('.main_option').each(function () {
                            $('#select_column_name').prepend(this);
                        });
                    </script>
                    <div id="table-main">
                        <label for="select">Select MAIN table:</label>
                        <select disabled="disabled" name="table_main" id="table_main_select">
                        </select>
                    </div>
                </div>
                <div id="panel-right">
                    <label for="select">Tables in which this Column</label>
                    <div id="tables">
                        <div id="placeholder">Not Found</div>
                    </div>
                </div>
            </form>

        </div>
        <div class="info">
            <div id='frame'>
            </div>
        </div>
        <div id="bottom">
            <div id="total"></div>
            <div id="time"></div>
            </br>
        </div>
        <script>
            window.on = true;
            window.dots_generator = true;
            getTables();

            function stop() {
                on = false;
                if(dots_generator != true){
                    clearInterval(dots_generator);
                };
                $.ajax({
                    dataType: 'json',
                    url: '/index.php?route=repair/defragmentation/stop',
                    success: function (json) {
                        if ($('#frame .stroke').length > 0) {
                            $('#button').text("CLEAR");
                            $('#button').attr("onclick", "location.reload();");
                            
                            var stroke = document.createElement('div');
                            $(stroke).addClass('stroke-finish');      
                            var span = document.createElement('span');
                            $(span).attr('id', 'finish');   
                            $(span).text('finish');
                            $(stroke).append(span);
                            $('#frame').append(stroke);

                            $('#bottom #time').html(json['time']);
                        }
                    },
                    error: function (xhr, ajaxOptions, thrownError) {
                        location.reload();
                    }
                });
                
               

            }
            
            var select_column_name = document.getElementById("select_column_name");
            select_column_name.addEventListener("change", function () {
                on = false;
                stop();
                getTables();
                
            });

            var table_main_select = document.getElementById("table_main_select");
            table_main_select.addEventListener("change", function () {
                on = false;
                stop();
                getTables();
            });

    

            function loading() {
                window.loader = document.createElement('div');
                $(loader).addClass('loader');
                $(loader).text('Loading')
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
            }



            function getTables() {
               
                var msg = $('form#panel-info').serialize();
                $.ajax({
                    type: 'POST',
                    url: '/index.php?route=repair/defragmentation/getTables',
                    data: msg,
                    dataType: 'json',
                    success: function (json) {
                        
                        $('.frame').empty();
                        $('#tables').empty();
                        $('#bottom #total').empty();
                        $('#bottom #time').empty();
                        
                        for (var i = 0; i <= json['tables'].length - 1; i++) {
                            var st = document.createElement('div');
                            $(st).attr('id', 'stroke-table');
                            var input = document.createElement('input');
                            $(input).attr('type', 'checkbox');
                            $(input).attr('id', 'table_name_' + i);
                            $(input).attr('name', 'table_name[]');
                            $(input).attr('checked', 'checked');
                            $(input).attr('value', json['tables'][i]['table_name']);
                            $(st).append(input);
                            var label = document.createElement('label');
                            $(label).attr('for', 'table_name_' + i);
                            $(label).text(json['tables'][i]['table_name']);
                            $(st).append(label);
                            $('#tables').append(st);
                        }

                        for (var i = 0; i <= json['tables'].length - 1; i++) {

                            if (json['tables'][i]['table_name'] == json['primary']['table_name']) {
                                var option = document.createElement('option');
                                $(option).attr('selected', 'selected');
                                $(option).text(json['tables'][i]['table_name']);
                                $('#table_main_select').append(option);
                            } else {
                                var option = document.createElement('option');
                                $(option).text(json['tables'][i]['table_name']);
                                $('#table_main_select').append(option);
                            }
                        }

                        $('#table_main_select').removeAttr("disabled");
                        getItems();
                    },
                    error: function (xhr, ajaxOptions, thrownError) {
                        location.reload();
                    }
                });
            }

            function getItems() {

                var msg = $('form#panel-info').serialize();
                $.ajax({
                    type: 'POST',
                    url: '/index.php?route=repair/defragmentation/getItems',
                    data: msg,
                    dataType: 'json',
                    beforeSend: function () {
                            loading();
                    },
                    success: function (json) {
                        if(on == false){
                            on = true;
                        };
                        
                        window.items = [];

                        for (var item, i = 0; item = json['items'][i++];) {
                            items.push(item);
                        };

                        window.total_items = json['total_items'];


                        $('#frame').empty();

                        var bs = document.createElement('div');
                        $(bs).attr('id', 'button-start');
                        var label = document.createElement('label');
                        $(label).addClass('start');
                        $(label).text('Total items will be processed: ' + json['total_items']);
                        $(bs).append(label);
                        var button = document.createElement('div');
                        $(button).attr('id', 'button');
                        $(button).attr('onclick', "defrag()");
                        $(button).text('START');
                        $(bs).append(button);

                        $('#frame').append(bs);
                       
                       
                    },
                    error: function (xhr, ajaxOptions, thrownError) {
                        location.reload();
                    }
                });
            }

            function animation(current_key, item, total) {
                    if(on){
                    if ($('#frame .stroke').length > 0) {
                        
                        var dots = '';
                        var s = document.querySelector('#frame .stroke:last-child #product');

                        var w = Math.round(Math.round(s.offsetWidth) / 5),
                            k = 0;
                        dots_generator = setInterval(function () {
                            if (k <= 100 - w) {
                                
                                if ((100 - w) - k > 15) {
                                    dots = '<span>.</span><span>.</span><span>.</span><span>.</span><span>.</span><span>.</span><span>.</span><span>.</span><span>.</span><span>.</span>';
                                    k = k + 10;
                                } else {
                                    dots = '<span>.</span>';
                                    k++;
                                }

                                window.d = $(s).parent().append(dots);


                            } else {
                                if(total < total_items){
                                        if ($('form').length > 0) {

                                            defrag(current_key, item, total);
                                        } else {
                                            $(d).append('SUCCESS');
                                            clearInterval(dots_generator);
                                            defrag(current_key, item, total);
                                        }
                                } else {
                                    $(d).append('SUCCESS');
                                    clearInterval(dots_generator);
                                    stop();
                                }
                            }
                        }, 1)
                    } else {
                        
                        defrag();
                    }
                }
            }


            function defrag(current_key, item, total) {
                    if(on){
                        
                    if ($('#frame .stroke').length > 0) {
                        clearInterval(dots_generator);
                    }
                    
                    if(total == null){
                        total = 0; 
                    }
                    
                    if(current_key == null){
                        current_key = 0; 
                    }

                    if(item == null){
                        item = items[0];
                    }
                    
                    var column = $('#select_column_name').val(),
                        table_main = $('#table_main_select').val();
                    

                    var msg = $('form').serialize();
                        
                    var table_name = $("[name='table_name[]']").serializeArray();

                    $.ajax({
                        type: 'POST',
                        url: '/index.php?route=repair/defragmentation/defrag',
                        data: {current_key: current_key, column:column, item:item, table_main: table_main, total: total, table_name: table_name, total_items: total_items},
                        dataType: 'json',
                        success: function (json) {

                                var current_key = json['current_key'];
                                var item = items[json['current_key']];
                                var total = json['total'];

                                var stroke = document.createElement('div');
                                $(stroke).addClass('stroke');    
                                $(stroke).attr('id', 'stroke-table');   
                                var span = document.createElement('span');
                                $(span).attr('id', 'product');   
                                $(span).text(json['column'] + ': ' + json['item'] + '   change ID  to ' + json['total']);
                                $(stroke).append(span);


                                if ($('#frame .stroke').length == 0 && $('#frame .stroke-start').length == 0) {
                                    
                                    $('#button').attr('onclick', 'stop()');
                                    $('#button').text('STOP');
                                    
                                    var stroke = document.createElement('div');
                                    $(stroke).addClass('stroke-start');    
                                    var span = document.createElement('span');
                                    $(span).attr('id', 'start');   
                                    $(span).text('start');
                                    $(stroke).append(span);
                                    $('#frame').append(stroke);
                                }

                                if ($('#frame .stroke').length > 0) {
                                    $(d).append('SUCCESS');
                                }

                                $('#frame').append(stroke);

                                $('#bottom #total').html('Total: ' + json['total'] + '  [0-' + Math.floor(json['total']-1) + ']        ');

                                if (document.querySelector('#frame').childNodes.length > 20) {
                                    if ($('.stroke-start').length > 0) {
                                        $('#frame').find('.stroke-start').remove();
                                    } else {
                                        $('#frame').find('.stroke:first').remove();
                                    }
                                }

                                if (total > 0) {
                                    if (!$('#button').hasClass('stop')) {
                                        animation(current_key, item, total);
                                    };
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
        
        </script>

    </div>

</body>

</html>