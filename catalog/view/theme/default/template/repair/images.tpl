<html lang="en">

    <head>
        <script src="/catalog/view/javascript/jquery/jquery-3.1.1.min.js" type="text/javascript"></script>
        <script src="/catalog/view/javascript/jquery/jquery-migrate-3.0.0.min.js" type="text/javascript"></script>
        <script src="/catalog/view/javascript/jquery/mag/jquery.magnific-popup.min.js"></script>
        <link href="/catalog/view/javascript/jquery/mag/magnific-popup.css" rel="stylesheet" media="screen" />
    </head>
    <style>
     
        #panel{
            display: block;
            float: left;
            width: 100%;
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
            width: 100%;
            height: auto;
            margin: 0 15px;
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
        
        #panel-left #select-options{
            display: block;
            float: left;
            margin: 0 5px 0 0;
        }
        
        #panel-left #button-start{
            display: block;
            float: left;
            width: 390px;
            margin: 0 0 0 5px;
        }
        
        #panel-left #button-start #items_count{
            display: block;
            float: right;
            width: 150px;
        }
        
        #panel-left #button-start #button{
            display: block;
            float: left;
            width: 250px;
            height: 13px;
            padding: 9px 0;
            font-size: 14px;
            text-align: center;
            background: #ccc;
            cursor: pointer;
        }
        
        
        #panel-tables{
            display: block;
            float: left;
            width: calc(70% - 15px);
            margin: 0 15px 0 0;
        }
        
        #panel-tables #tables{
            display: block;
            float: left;
            width: calc(100% - 30px);
            padding: 15px;
            background: #ccc;
        }
        
        #placeholder{
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
        #content{
            width: calc(100% - 30px);
            margin: 0 15px;
        }
        .info{
            display: block;
            float: left;
            width: 30%;
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
            margin: 0 10px 15px 0px;
            background: #ccc;
            padding: 15px 0;
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

        
        .main_option:last{
            padding: 0 0 10px 0;
        }

    </style>

    <body>

     
    <div id="pagewrap-firts">
        <div id="panel">
           <form id="panel-info">
            <div id="panel-left">
                <div id="select-options">
                    <label for="select_column_name">Select Options:</label>
                    <select name="column_name" id="select_column_name">
                        <option data-options='0'>Get all images for products</option>
                        <option data-options='1'>Get all pictures in desc</option>
                        <option data-options='2'>Download pictures in desc</option>
                        <option data-options='3'>Repair img for products</option>
                        <option data-options='4'>Repair pictures in desc</option>
                        <option data-options='5'>Find issue img</option>
                        <option data-options='6'>Show all img</option>
                        <option data-options='7' style="font-weight: bold;">Optimize all img</option>
                    </select>
                </div>
                <div id="button-start">
                    <label class="start">Total items will be processed:&nbsp;<span id="items_count"></span></label>
                    <div id="button" data-button="button" onclick="">START</div>
                </div>  
                <script>
                    $('.main_option').each(function() {
                        $('#select_column_name').prepend(this);
                    });
                </script>
              
            </div>
            </form>
        </div>
        <div id="content">
        <form id="panel-tables" name="tables">
            <div id="panel-right">
                <label for="select">Found images:</label>
                <div id="tables">
                    <div class="html-code grid-of-images">
                        <div class="popup-gallery">
                            <div id="placeholder">Doesn't show</div>
                        </div>
                    </div>
                </div>
            </div>
        </form>
        <div class="info">
            <label for="frame">Info:</label>
            <div id="frame">
                <div id="placeholder">Empty</div>
            </div>
            
        </div>
        </div>
        <div id="bottom">
            <div id="time"></div>
            </br>
        </div>
        <script>
            var on = true;
            getImages(count = true, 0);
          
            var select_column_name = document.getElementById("select_column_name");
            select_column_name.addEventListener("change", function() {
                
                var x = $(this.options[this.selectedIndex]).attr('data-options');
                
                switch(x) {
                    case '0': 
                        getImages(count = true, 0);
                        break;
                    case '1': 
                        getPicturesInDesc(count = true, 0);
                        break;
                    case '2': 

                        break;
                    case '3': 

                        break;
                    case '4': 

                        break;
                    case '5': 

                        break;
                    case '6': 

                        break;
                    default:
                        break;
                }
           

            });
            
            function loading(){
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
                $('#items_count').html(loader);
            }
            

            function getImages(count, page){
                $.ajax({
                    type: 'POST',
                    url: '/index.php?route=repair/images/getImages',
                    dataType: 'json',
                    data: {'page':page},
                    beforeSend: function(){
                        loading();
                    },
                    success: function (json) {
                        clearInterval(dots_loader);
                        page += 1;
                        $('[data-button]').attr('onclick', 'getImages(count = false, ' + page + ')');
                        if (json['count']) {
                            $('#items_count').html(json['count']);
                        }
                        if(count != true){
                            $('.popup-gallery').empty();
                            
                            for (var i = 0; i <= json['image'].length - 1; i++) {
                                var img = document.createElement('a');
                                $(img).attr('href', "image/" + json['image'][i]);
                                var figure = document.createElement('figure');
                                $(figure).addClass('slider__itemImage');
                                $(figure).attr('style', "background-image:url(image/" + escape(json['image'][i]) + ")");
                                $(img).append(figure);
                                $('.popup-gallery').append(img);
                                

                                if(json['image2'][i] && json['image2'][i] != null){
                                    var img = document.createElement('a');
                                    $(img).attr('href', "image/" + json['image2'][i]);
                                    var figure = document.createElement('figure');
                                    $(figure).addClass('slider__itemImage');
                                    $(figure).attr('style', "background-image:url(image/" + escape(json['image2'][i]) + ")");
                                    $(img).append(figure);
                                    $('.popup-gallery').append(img);
                                }

                                if(json['image3'][i] && json['image3'][i] != null){
                                    var img = document.createElement('a');
                                    $(img).attr('href', "image/" + json['image3'][i]);
                                    var figure = document.createElement('figure');
                                    $(figure).addClass('slider__itemImage');
                                    $(figure).attr('style', "background-image:url(image/" + escape(json['image3'][i]) + ")");
                                    $(img).append(figure);
                                    $('.popup-gallery').append(img);
                                }
                            }
                            update_popup();
                        }
                    },
                    error: function (xhr, ajaxOptions, thrownError) {
                        location.reload();
                    }
                });
            }
    
            
            function getPicturesInDesc(count, page){
                    $.ajax({
                        type: 'POST',
                        url: '/index.php?route=repair/images/getPicturesInDesc',
                        dataType: 'json',
                        data: {'page':page},
                        beforeSend: function(){
                            loading();
                        },
                        success: function (json) {
                            clearInterval(dots_loader);
                            page += 1;
                            $('[data-button]').attr('onclick', 'getPicturesInDesc(count = false, ' + page + ')');
                            
                            if (json['count']) {
                                $('#items_count').html(json['count']);
                            }
                            
                            if(count != true){
                                $('.popup-gallery').empty();

                                for (var i = 0; i <= json['picture'].length - 1; i++) {
                                    var img = document.createElement('a');
                                    $(img).attr('href', "image/" + json['picture'][i]);
                                    var figure = document.createElement('figure');
                                    $(figure).addClass('slider__itemImage');
                                    $(figure).attr('style', "background-image:url(image/" + escape(json['picture'][i]) + ")");
                                    $(img).append(figure);
                                    $('.popup-gallery').append(img);
                                }

                                update_popup();

                            }
                        },
                        error: function (xhr, ajaxOptions, thrownError) {
                            //location.reload();
                        }
                    });
            }
        
          
            function update_popup() {
                $('.popup-gallery').magnificPopup({
                    delegate: 'a',
                    type: 'image',
                    tLoading: 'Loading...',
                    mainClass: 'mfp-img-mobile',
                    gallery: {
                        enabled: true,
                        navigateByImgClick: true,
                        preload: [0,1]
                    },
                    image: {
                        tError: 'Сould not be loaded.',
                        titleSrc: function(item) {
                            return item.el.attr('title') + '<small>123</small>';
                        }
                    }
                });
            };
           
            
        </script>

    </div>
</body>

</html>