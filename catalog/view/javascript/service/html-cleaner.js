function removeTagAttributes() {
    hossz = text.length;
    for (var e = text.split(""), t = new Array(""), o = 1, i = 0, a = 0; i < hossz;) "<" == e[i] && (o = 2, "!" == e[i + 1] && "-" == e[i + 2] && "-" == e[i + 3] && (o = 1), "a" == e[i + 1] && " " == e[i + 2] && (o = 4), "i" == e[i + 1] && "m" == e[i + 2] && "g" == e[i + 3] && " " == e[i + 4] && (o = 14)), " " == e[i] && (2 == o && (o = 3), (4 == o || 5 == o) && ("h" == e[i + 1] && "r" == e[i + 2] && "e" == e[i + 3] && "f" == e[i + 4] && (o = 6), "d" == e[i + 1] && "o" == e[i + 2] && "w" == e[i + 3] && "n" == e[i + 4] && "l" == e[i + 5] && "o" == e[i + 6] && "a" == e[i + 7] && "d" == e[i + 8] && (o = 6)), (14 == o || 15 == o) && "s" == e[i + 1] && "r" == e[i + 2] && "c" == e[i + 3] && (o = 16), 4 == o && (o = 5), 8 == o && (o = 3), 14 == o && (o = 15), 18 == o && (o = 3)), '"' == e[i] && "7" == o && (o = 8), '"' == e[i] && "6" == o && (o = 7), '"' == e[i] && "17" == o && (o = 18), '"' == e[i] && "16" == o && (o = 17), (">" == e[i] || "/" == e[i] && ">" == e[i + 1]) && (o = 1), (1 == o || 2 == o || 4 == o || 6 == o || 7 == o || 8 == o || 14 == o || 16 == o || 17 == o || 18 == o) && (t[a] = e[i], a++), i++;
    text = t.join("")
}


function csakEnteresTagotTorul() {
    hossz = text.length;
    for (var e = text.split(""), t = new Array(""), o = 0, i = 0, a = 0, n = 0, l = 0; o < hossz;) {
        if (0 == a && "<" == e[o] && "/" != e[o + 1] && (a = 1, n = o), 2 == a && ">" == e[o]) {
            for (l = 0; o - n >= l; l++) t[l + n] = "";
            e[o] = "", a = 0
        }
        1 == a && ">" == e[o] && (a = "/" != e[o - 2] && "/" != e[o - 1] && "\n" == e[o + 1] && "<" == e[o + 2] && "/" == e[o + 3] ? 2 : 0), t[i] = e[o], i++, o++
    }
    text = t.join("")
}

function torolTagbanKettoKozt(e, t) {
    hossz = text.length;
    for (var o = e.length, i = t.length, a = text.split(""), n = e.split(""), l = t.split(""), r = 0, s = new Array(""), u = 1, c = 0, p = 0, d = 0, m = 0, h = 0; c < hossz;) {
        if ("<" == a[c] && (r = 1), ">" == a[c] && (r = 0), 1 == r) {
            for (m = 1, d = 0; o > d; d++) n[d] != a[c + d] && (m = 0);
            if (1 == m)
                for (h++, u = -999, c += o, d = 0; o > d; d++) s[p] = n[d], p++
                    }
        for (m = 1, d = 0; i > d; d++) l[d] != a[c + d] && (m = 0);
        1 == m && (u = 0), -999 != u && u++, u > 0 && (s[p] = a[c], p++), c++
    }
    return text = s.join(""), h
}

function hash(str){
    var h = 0;
    for (i = 0; i < str.length; i++) {
        char = str.charCodeAt(i);
        h += char;
    }
    return h;
}


function helyettesit(e, t) {
    var o = text,
        i = 0,
        a = 0;
    do i = 0, o = text.replace(e, t), o === text ? i = 1 : a++, text = o; while (0 == i);
    return a;
}


function reilyttesit(e, t) {
    text = text.replace(e, t);
}


function get_regexp(o) {

    var search = new Array(
        '\\\\',
        '\\/',
        '\\|',
        '\\+',
        '\\(',
        '\\)',
        '\\[',
        '\\]',
        '\\{',
        '\\}',
        '\\?',
        '\\.',
        '\\*',
        '\\^',
        '\\$',
        '\\@',
    );

    var replaceTo = new Array(
        '\\\\',
        '\\/',
        '\\|',
        '\\+',
        '\\(',
        '\\)',
        '\\[',
        '\\]',
        '\\{',
        '\\}',
        '\\?',
        '\\.',
        '\\*',
        '\\^',
        '\\$',
        '\\@',
    );

    for (i = 0; i < search.length; i++) {
        var e = search[i],
            t = replaceTo[i],
            o = o.replace(new RegExp(e, 'gim'), t);
    }

    o = new RegExp(o, 'gim');
    
    return o;
}

function prf_regexp(o, l, m) {
    var a =  o.replace(new RegExp(l, 'gim'), m);
    return a;
}

function convertText(product, lang) {
    var shell = document.createElement('shell');
    text = jQuery.htmlPrefilter($(shell).html(product['description'][lang]).get(0).outerHTML);

    for (var e = 0; 54 >= e; e++) opt[e] = 0;
    
    for (opt[1] = 1, opt[2] = 1, opt[3] = 1, opt[4] = 1, opt[5] = 1, opt[6] = 1, opt[7] = 1, opt[8] = 1, opt[10] = 1,  opt[12] = 1,  opt[13] = 1, opt[15] = 1, opt[16] = 1, ij = 0; ij <= 20; ij++);
    
    var e = 0;
    helyettesit("	", ""), helyettesit("  ", " "), helyettesit("&nbsp;&nbsp;", " "), helyettesit(" &nbsp;", " "),  helyettesit("&nbsp; ", " "), helyettesit(" \n", "\n"), helyettesit("	\n", "\n"), helyettesit("\n\n", "\n"), helyettesit("  ", " ");
    var t = 0,
        o = 0;
    torolTagbanKettoKozt("<script", "</script>"), torolTagbanKettoKozt("<style", "</style>"), helyettesit("<style</style>", "") > 0 && t++, helyettesit("<script</script>", "") > 0 && o++;
        
    //1 == opt[2] && (helyettesit("&nbsp;&nbsp;", " "), helyettesit("&nbsp; ", " "), helyettesit(" &nbsp;", " "), helyettesit("&nbsp;</", "</"), helyettesit(">&nbsp;", ">"), helyettesit(" </", "</"), helyettesit("> ", ">"), helyettesit(">&#13", ">"), helyettesit("&#13</", "</"), helyettesit(">&#10", ">"), helyettesit("&#10</", "</"), helyettesit(">/n", ">"), helyettesit("/n</", "</"), helyettesit(">/r", ">"), helyettesit("/r</", "</")),    
    //1 == opt[4] && (torolTagbanKettoKozt("<i ", ">"), torolTagbanKettoKozt("<b ", ">"), helyettesit("<i>", "<em>"), helyettesit("<i >", "<em>"), helyettesit("</i>", "</em>"), helyettesit("<b>", "<strong>"), helyettesit("<b >", "<strong>"), helyettesit("</b>", "</strong>")),   
    //1 == opt[3] && (helyettesit(" class = ", " class="), helyettesit(" class= ", " class="), helyettesit(" class =", " class="), torolTagbanKettoKozt(' class="', '"'), helyettesit(' class=""', ""), helyettesit(" id = ", " id="), helyettesit(" id= ", " id="), helyettesit(" id =", " id="), torolTagbanKettoKozt(' id="', '"'), helyettesit(' id=""', "")),     
   
    var search = new Array(
        //убираем ↵
        '[\\r\\n]',
        //убираем атрибут style
        'style\=\".*?\"',
        'bgcolor\=\".*?\"',
        //'height\=\".*?\"',
        //убираем классы и id
        'class\=\".*?\"',
        'id\=\".*?\"',
        'width\=\".*?\"',
        ' align\=\".*?\"',
        ' valign\=\".*?\"',
        //убираем ' ' пере закрытием тега
        '\\s{1,}>',
        '\\s{1,}/>',
        '\\s{2,}',
        '(<br[^>]*>){3,}',
        //оттачиваем colspan и rowspan
        'colspan=\"(.*?)\"',
        'rowspan=\"(.*?)\"',
        //заменям тире на -
        '&ndash;',
        '&bull;',
        '&quot',
        //добавляем tbody
        '<table[^>]*>',
        '<table[^>]*>[\\s\\S]?<tr[^>]*>',
        '<\/tr[^>]*>[\\s\\S]?<\/table[^>]*>',
        //убираем теги <p> между <td></td>
        '(<td[^>]*?>)<p[^>]*>(.*?)<\/p[^>]*>(<\/td>)',
        '(<th[^>]*?>)<p[^>]*>(.*?)<\/p[^>]*>(<\/th>)'

    );

    var replaceTo = new Array(
        //убираем ↵
        '',
        //убираем атрибут style
        '',
        '',
        //'',
        //убираем классы и id
        '',
        '',
        '',
        '',
        '',
        //убираем ' ' пере закрытием тега
        '>',
        '>',
        ' ',
        '<br><br>',
        //оттачиваем colspan и rowspan
        'colspan="$1"',
        'rowspan="$1"',
        //заменям тире на -
        '-',
        '•',
        '"',
        //добавляем tbody
        '<table>',
        '<table><tbody><tr>',
        '</tr></tbody></table>',
        
        //убираем теги <p> между <td></td>
        '$1$2$3',
        '$1$2$3'
    );

    for (i = 0; i < search.length; i++) {
        1 == opt[12] && (helyettesit(new RegExp(search[i], 'g'), replaceTo[i]));
    }
  
    // убираем пустые таблицы
    jQuery(text).find('tr:first-child').each(function() {
        var rs = false;
        var tr = jQuery(this).closest('table').find('tr');
        var td = jQuery(this).closest('table').find('td');
        var th = jQuery(this).closest('table').find('th');
        var img = jQuery(this).closest('table').find('img');
        var ul = jQuery(this).closest('table').find('ul');

        var count_tr = tr.length;
        var count_td = td.length;
        var count_th = th.length;
        var count_img = img.length;
        var count_ul = ul.length;
    
        
        if(jQuery(this).closest('table').text().trim().length == 0 && count_img == 0){
            var table = get_regexp($(this).closest('table').get(0).outerHTML);
            1 == opt[12] && (helyettesit(table, ""));
        } else if (count_tr == 1 && count_td == 1 && count_img == 1){
            // выносим изображение если ради него создана таблица
            img.each(function() {
                var y = get_regexp(jQuery(this).closest('table').get(0).outerHTML);
                1 == opt[12] && (helyettesit(y, '<p>' + jQuery(this).get(0).outerHTML + '</p>'));
            });  
        } else {
            
            /*
            var x = get_regexp(jQuery(this).get(0).outerHTML);
            var n = prf_regexp(jQuery(this).get(0).outerHTML, '<table>(.*?)<\/table>', '<table data-cols="2" data-rows="2"><tr >$1</tr></tbody>');
            1 == opt[12] && (helyettesit(x, n));
            */
            
            if(jQuery(this).closest('table').find('td > div').first().length > 2){
                var to = jQuery(this).closest('table').get(0).outerHTML;
                var tb = document.createElement('table');

                jQuery(this).find('td > div').each(function(i) {

                        var tr = document.createElement('tr');
                        var z = prf_regexp(jQuery(this).get(0).outerHTML, '<[^>]*>.*?<\/[^>]*>', '');
                    
                        jQuery(this).children().each(function(i) {
                         
                            jQuery(this).replaceWith(function(index, m){
                                var td = $("<td>").html(m);
                                if(i == 0){
                                    $(td).append(z);
                                }
                                jQuery(tr).append(td);
                            });
                            
                        });
                        
                        jQuery(tb).append(tr);
                });

                var t = get_regexp(to);
                var r = jQuery(tb).get(0).outerHTML;

                1 == opt[12] && (helyettesit(t, r));
            }
            
            // выносим список если ради него создана таблица
            if(count_ul > 0 && count_img == 0){
                var u = prf_regexp(jQuery(this).closest('table').get(0).outerHTML, '<ul[^>]*>.*?<\/ul[^>]*>', '');
                var l = jQuery(u).text().trim().length;
                if(l == 0){
                    var y = get_regexp(jQuery(this).closest('table').get(0).outerHTML);
                    var p = document.createElement('p');
                    ul.each(function() {
                        jQuery(p).append(jQuery(this));
                    });
                    
                    1 == opt[12] && (helyettesit(y, jQuery(p).get(0).outerHTML));
                };
            };
            
            
            //если есть rowspan то предварительно чистим таблицу от tbody
            if(jQuery(this).closest('table').find('[rowspan]').length != 0){
                rs = true;
                var g = jQuery(this).closest('table').get(0).outerHTML;
                var a = get_regexp(g);
                var z = prf_regexp(g, '<\/?tbody>', '');
                1 == opt[12] && (helyettesit(a, z));
                
            }
          
            //работаем с каждой строкой таблицы
            jQuery(this).closest('table').find('tr').each(function(i) {
                var count_img = jQuery(this).find('img').length;
                
                //убираем убираем пустые строки если они есть
                if(jQuery(this).text().trim().length == 0 && count_img == 0){
                    var e = get_regexp(jQuery(this).get(0).outerHTML);
                    1 == opt[12] && (helyettesit(e, ""));
                } else {
                    
                    if(jQuery(this).find('th').length != 0){
                        //добавляем перед строкой tbody
                        var d = get_regexp(jQuery(this).get(0).outerHTML);
                        
                        var s = prf_regexp(jQuery(this).get(0).outerHTML, '<tr>(.*?)<\/tr>', '<tbody data-thead="true"><tr >$1</tr></tbody>');
                        1 == opt[12] && (helyettesit(d, s));
                    }
                    
                    if(rs){
                        //если в строке есть rowspan то работаем с ней
                        if(jQuery(this).find('[rowspan]').length != 0){
                            //берем значение rowspan
                            var k = parseInt(jQuery(this).find('[rowspan]').first().attr('rowspan'));
                            var m = i + k;

                            //добавляем перед строкой tbody
                            var u = get_regexp(jQuery(this).get(0).outerHTML);
                            var c = prf_regexp(jQuery(this).get(0).outerHTML, '<tr', "<tbody><tr ");

                            1 == opt[12] && (helyettesit(u, c));

                            //закрываем tbody
                            
                            if(m <= tr.length){
                                var q = tr[m - 1];
                                var h = get_regexp(q.outerHTML);
                                var v = prf_regexp(q.outerHTML, '<\/tr>', '</tr ></tbody>');

                                1 == opt[12] && (helyettesit(h, v));
                            }
                        } 
                        //удаляем " " 
                        1 == opt[12] && (helyettesit(new RegExp('\\s{1,}>', 'gim'), '>'));
                        1 == opt[12] && (helyettesit(new RegExp('<tr\\s{1,}', 'gim'), '<tr '));
                    }
                }
            });
        }
    });
    

    // работаем с параграфами
    1 == opt[12] && (torolTagbanKettoKozt("<p ", ">"), helyettesit("<p >", "<div>"), helyettesit("<p>", "<p>"), helyettesit("</p>", "</div>"));

    
    jQuery(text).find("div").each(function() {
        // убираем пустые параграфы
        if(jQuery(this).text().trim().length == 0 && jQuery(this).find('img').length == 0){
            var p = get_regexp(jQuery(this).get(0).outerHTML);
            1 == opt[12] && (helyettesit(p, ""));
        } else{
            // убираем параграфы внутри параграфов
            jQuery(this).find('div').each(function(){
                var u = get_regexp(jQuery(this).get(0).outerHTML);
                var s = prf_regexp(jQuery(this).get(0).outerHTML, '<div>(.*?)<\/div>', '</div><div >$1</div><div>');
                1 == opt[12] && (helyettesit(u, s));
            });
        }
    });
    1 == opt[12] && (torolTagbanKettoKozt("<div ", ">"), helyettesit("<div >", "<p>"), helyettesit("<div>", "<p>"), helyettesit("</div>", "</p>"));
    


    //работаем со списками
    jQuery(text).find('li').each(function() {
        // убираем • так как есть li
        if($(text).find('li').find(':contains("•")').length != 0){
            $(text).find('li').find(':contains("•")').each(function() {
                if(jQuery(this).text().trim().length == 1){
                   var l = get_regexp(jQuery(this).get(0).outerHTML);
                    1 == opt[12] && (helyettesit(l, ""));
                }
            });
        }
        
        if($(text).find('li').find('strong').length != 0){
            $(text).find('li').find('strong').each(function() {
                var y = get_regexp(jQuery(this).get(0).outerHTML);
                jQuery(this).replaceWith(function(index, z){
                    1 == opt[12] && (helyettesit(y, z));
                });
            });
        }
        
        
    });
    
    jQuery(text).find('a').each(function() {
        var $this = jQuery(this);
        
        if($this.attr('href')){
            if($this.html().replace(/[0-9a-zA-ZА-ЯЁа-яё]{1,2}|\.|\,/, '').length == 0) {
                1 == opt[12] && (helyettesit($this.get(0).outerHTML, $this.html()));
            }
            if($this.attr('href').replace(/javascript/g, '').length == 0) {
                1 == opt[12] && (helyettesit($this.get(0).outerHTML, $this.html()));
            }
        } else {
            1 == opt[12] && (helyettesit($this.get(0).outerHTML, $this.html()));
        }
        
    });

    //убераем комментарии
    1 == opt[13] && (torolTagbanKettoKozt("<!--", "-->"), helyettesit("<!---->", ""));

    1 == opt[5] && (helyettesit("> <", "><"), helyettesit("> \n", ">\n"), csakEnteresTagotTorul());
    1 == opt[6] && (helyettesit("> &nbsp;<", "><"), helyettesit(">&nbsp; <", "><"), helyettesit(">&nbsp;<", "><"));
    1 == opt[7] && (torolTagbanKettoKozt("<font ", ">"), helyettesit("<font >", ""), helyettesit("<font>", ""), helyettesit("</font>", ""),
                    torolTagbanKettoKozt("<u ", ">"), helyettesit("<u >", ""), helyettesit("<u>", ""), helyettesit("</u>", ""), 
                    torolTagbanKettoKozt("<em ", ">"), helyettesit("<em >", ""), helyettesit("<em>", ""), helyettesit("</em>", ""), 
                    torolTagbanKettoKozt("<ol ", ">"), helyettesit("<ol >", ""), helyettesit("<ol>", ""), helyettesit("</ol>", ""),
                    torolTagbanKettoKozt("<o:p ", ">"), helyettesit("<o:p >", ""), helyettesit("<o:p>", ""), helyettesit("</o:p>", ""),
                    torolTagbanKettoKozt("<u1:p ", ">"), helyettesit("<u1:p >", ""), helyettesit("<u1:p>", ""), helyettesit("</u1:p>", ""));

    1 == opt[8] && (helyettesit("http://www.ip-tech.com.ua/", "/"));
    1 == opt[8] && (helyettesit("ip-tech.com.ua/", "/"));
    1 == opt[8] && (helyettesit("www.ip-tech.com.ua/", "/"));
    1 == opt[8] && (helyettesit("http://voipcard.com.ua/", "/"));
    1 == opt[8] && (helyettesit("http://voipcard.com.ua/", "/")); 
    1 == opt[8] && (helyettesit("voipcard.com.ua/", "/"));
    
    1 == opt[9] && (torolTagbanKettoKozt("<a ", ">"), helyettesit("<a >", ""), helyettesit("<a>", ""), helyettesit("</a>", ""));
    1 == opt[10] && (torolTagbanKettoKozt("<span ", ">"), helyettesit("<span >", ""), helyettesit("<span>", ""), helyettesit("</span>", ""));
    
    1 == opt[12] && (helyettesit("<h5>", "<h4>"), helyettesit("</h5>", "</h4>"));
    1 == opt[12] && (helyettesit("<h1>", "<h3>"), helyettesit("</h1>", "</h3>"));
    1 == opt[12] && (helyettesit("<h2>", "<h3>"), helyettesit("</h2>", "</h3>"));

    if(jQuery(text).find(':contains("·")').length > 0){
        //alert('Внимание! Проблемный товар.');
        var u = document.createElement('ul');
        var temp = document.createElement('temp');
        
        var c = jQuery(text).find(':contains("·")').nextUntil(':not(:contains("·"))').andSelf();
        

        jQuery(c).each(function(i){
                if(jQuery(this).next().text().indexOf('·') == 0 && jQuery(this).next().length > 0){
                    
                    jQuery(temp).append(jQuery(this).get(0).outerHTML);
                    
                    var l = document.createElement('li');
                    var x = prf_regexp(jQuery(this).html(), '·', '');
                    jQuery(l).html(x);
                    jQuery(u).append(l);
                    
                } else {
                    jQuery(temp).append(jQuery(this).get(0).outerHTML);
                    var l = document.createElement('li');
                    var x = prf_regexp(jQuery(this).html(), '·', '');
                    jQuery(l).html(x);
                    jQuery(u).append(l);
                    
                    jQuery(temp).filter('temp').replaceWith(function(i, ul){
                        var y = get_regexp(ul);
                        
                        1 == opt[12] && (helyettesit(y, jQuery(u).get(0).outerHTML));
                        jQuery(u).html('');
                        jQuery(temp).html('');
                    });
                }
        });

    }
    
    if(jQuery(text).find('h5, h4, h3, h2, h1').length > 0){
        if(jQuery(text).find('h5, h4, h3, h2, h1').text().length > 60){
            jQuery(text).find('h5, h4, h3, h2, h1').each(function() {
                var y = get_regexp(jQuery(this).get(0).outerHTML);
                jQuery(this).replaceWith(function(index, z){
                    1 == opt[12] && (helyettesit(y, z));
                });
            });
        }
    }
    
   
    if(jQuery(text).find(':contains("Снят с производства")').length > 0){
        jQuery(text).find(':contains("Снят с производства")').first().each(function(){
            var u = prf_regexp(jQuery(this).get(0).outerHTML, 'Снят с производства\.?', '');
            var l = jQuery(u).text().trim().length;
            if(l == 0){
                var y = get_regexp(jQuery(this).get(0).outerHTML);
                var p = document.createElement('p');
                jQuery(p).attr('data-discntnd', 'true');
                jQuery(p).append('Снят с производства.');
                1 == opt[12] && (helyettesit(y, jQuery(p).get(0).outerHTML));
            } else {
                var y = get_regexp(jQuery(this).get(0).outerHTML);
                var p = jQuery(this).attr('data-discntnd', 'true');
                1 == opt[12] && (helyettesit(y, jQuery(p).get(0).outerHTML));
            }
        });
    }
 

    jQuery(text).filter('shell').replaceWith(function(i, sh){
        text = sh;
    }); 
    
    product['description'][lang] = text;
    
    return product;
}


var hanyadikclean = 0,
    opt = new Array,
    text = "",
    replaceaktiv = new Array,
    replaceaktivalva = 1,
    wysiwygActive = 0,
    sourceFontSize = 12;