

var cart = {
	'add': function(product_id, quantity, that) {
			var el = $(that);
			var that = $(that).closest('.cart-trigger-item').find('.cart-trigger-item-img');
			var cart = $(".cart");
			var w = that.width();

			that.clone()
				.css({'width' : w,
				'position' : 'absolute',
				'z-index' : '9999',
				top: that.offset().top,
				left:that.offset().left})
				.appendTo("body")
				.animate({opacity: 0.05,
					left: cart.offset()['left'],
					top: cart.offset()['top'],
					width: 20}, 1000, function() {
						$(this).remove();
			});


		$.ajax({
			url: 'index.php?route=checkout/cart/add',
			type: 'post',
			data: 'product_id=' + product_id + '&quantity=' + (typeof(quantity) != 'undefined' ? quantity : 1),
			dataType: 'json',
			beforeSend: function() {
			},
			complete: function() {
			},
			success: function(json) {
				console.log(json);
				cart.each(function(){
					var cart = $(this);

					var cartNum = cart.find('.cart-icon-num');
					var cartSum = cart.find('.cart-info-sum');

					cartSum.text(json.total_summ);
					cartNum.text(json.total_items);

					setTimeout(function(){
						if(parseInt(json.total_summ) > 0){
							cart.addClass('added');
						}else {
							cart.removeClass('added');
						}
					}, 1000);

				});
				/*
				$('.alert-dismissible, .text-danger').remove();

				if (json['redirect']) {
					location = json['redirect'];
				}

				if (json['success']) {
					$('#content').parent().before('<div class="alert alert-success alert-dismissible"><i class="fa fa-check-circle"></i> ' + json['success'] + ' <button type="button" class="close" data-dismiss="alert">&times;</button></div>');


					setTimeout(function () {
						$('#cart > button').html('<span id="cart-total"><i class="fa fa-shopping-cart"></i> ' + json['total'] + '</span>');
					}, 100);

					$('html, body').animate({ scrollTop: 0 }, 'slow');

					$('#cart > ul').load('index.php?route=common/cart/info ul li');
				}*/
			},
			error: function(xhr, ajaxOptions, thrownError) {
				alert(thrownError + "\r\n" + xhr.statusText + "\r\n" + xhr.responseText);
			}
		});

	},
	'update': function(key, quantity) {
		var cartC = $(".cart");
		$.ajax({
			url: 'index.php?route=checkout/cart/edit',
			type: 'post',
			data: 'key=' + key + '&quantity=' + (typeof(quantity) != 'undefined' ? quantity : 1),
			dataType: 'json',
			beforeSend: function() {
			},
			complete: function() {
			},
			success: function(json) {
				$('.cart-total-summ-product-' + key + ' b').text(json.total_summ_product.total);
				cart.order(key, 'checkout');
				cartC.each(function(){
					var cart = $(this);

					var cartNum = cart.find('.cart-icon-num');
					var cartSum = cart.find('.cart-info-sum');

					cartSum.text(json.total_summ);
					cartNum.text(json.total_items);

					setTimeout(function(){
						if(parseInt(json.total_summ) > 0){
							cart.addClass('added');
						}else {
							cart.removeClass('added');
						}
					}, 100);

				});
				/*
				// Need to set timeout otherwise it wont update the total
				setTimeout(function () {
					$('#cart > button').html('<span id="cart-total"><i class="fa fa-shopping-cart"></i> ' + json['total'] + '</span>');
				}, 100);

				if (getURLVar('route') == 'checkout/cart' || getURLVar('route') == 'checkout/checkout') {
					location = 'index.php?route=checkout/cart';
				} else {
					$('#cart > ul').load('index.php?route=common/cart/info ul li');
				}*/
			},
			error: function(xhr, ajaxOptions, thrownError) {
				alert(thrownError + "\r\n" + xhr.statusText + "\r\n" + xhr.responseText);
			}
		});
	},
	'order': function(key, type = false) {
		$.ajax({
			url: 'index.php?route=checkout/cart/order',
			type: 'post',
			data: 'key=' + key + '&type=' + type,
			dataType: 'html',
			beforeSend: function() {
			},
			complete: function() {
			},
			success: function(html) {
				$('.checkout-order').html(html);
			},
			error: function(xhr, ajaxOptions, thrownError) {
				alert(thrownError + "\r\n" + xhr.statusText + "\r\n" + xhr.responseText);
			}
		});
	},
	'remove': function(key) {
		$.ajax({
			url: 'index.php?route=checkout/cart/remove',
			type: 'post',
			data: 'key=' + key,
			dataType: 'json',
			beforeSend: function() {
			},
			complete: function() {
			},
			success: function(json) {
				console.log(json);
				cart.order(key);
				if(json.total_items == 0){
					location.reload();
				}
				$(".cart").each(function(){
					var cart = $(this);

					var cartNum = cart.find('.cart-icon-num');
					var cartSum = cart.find('.cart-info-sum');

					cartSum.text(json.total_summ);
					cartNum.text(json.total_items);

					setTimeout(function(){
						if(parseInt(json.total_summ) > 0){
							cart.addClass('added');
						}else {
							cart.removeClass('added');
						}
					}, 100);

				});
			},
			error: function(xhr, ajaxOptions, thrownError) {
				alert(thrownError + "\r\n" + xhr.statusText + "\r\n" + xhr.responseText);
			}
		});
	}
}

$('.search-form input').keyup(function(){
	var v = $(this).val();
	if((v != '') &&(v.length > 2)){
		$.ajax({
			url: 'index.php?route=product/live_search',
			type: 'get',
			data: 'filter_name=' + v + '&limit=7',
			dataType: 'json',
			beforeSend: function() {
			},
			complete: function() {
			},
			success: function(json) {
				/*console.log(json.products.length);*/
				$('.search-form-hidden').html('');
				if(json.products.length > 0){
					for(var i in json.products){
						$('.search-form-hidden').append('<a href="' + json.products[i].url + '">' + json.products[i].name + '</a>');
					}
				}
				$('.search-form-hidden').css('display', 'block');
			},
			error: function(xhr, ajaxOptions, thrownError) {
/*				console.log(thrownError);
				console.log(xhr.statusText);
				console.log(xhr.responseText);*/
			}
		});
	}
});

$("#search-form").submit(function(event){
	event.preventDefault();
	var v = $('.search-form input').val();
	window.location.href = '/index.php?route=product/search&search=' + v;
});


var common = {
	init: function() {
		common.main();
		common.carousel();
		common.submit();
	},
	main: function(){


		// menu-trigger

		$('.menu-trigger-open').click(function(event){
			event.preventDefault();
			$('.header').addClass('open');
			$('body').addClass('hidden');
		});

		$('.catalog-filter-trigger').click(function(event){
			event.preventDefault();
			$(this).closest('.catalog-filter').toggleClass('open');
		});

		$('.menu-trigger-close').click(function(event){
			event.preventDefault();
			$('.header').removeClass('open');
			$('body').removeClass('hidden');
		});

		$('.catalog-menu-trigger').click(function(event){
			event.preventDefault();

			$(document).mouseup(function (e){ // событие клика по веб-документу
                var div = $(".header-categories-items"); // тут указываем ID элемента
                if (!div.is(e.target) // если клик был не по нашему блоку
                    && div.has(e.target).length === 0) { // и не по его дочерним элементам

                    if ($(window).width() > 1200) {
                        $('.header').removeClass('open-cat');
                    }

                }
            });

            $('.header').addClass('open-cat');
			window.scrollTo({ top: 0, behavior: 'smooth' });
		});

		$(".header-categories-item").mouseenter(function (e) {
			$(".header-categories-item:hover .header-categories-item-hidden").css('visibility', 'visible');
			$(".header-categories-item:hover .header-categories-item-hidden").css('opacity', '1');
			$(".header-categories-item").mouseenter(function (e) {
				$(".header-categories-item .header-categories-item-hidden").css('visibility', 'hidden');
				$(".header-categories-item .header-categories-item-hidden").css('opacity', '0');
				$(".header-categories-item:hover .header-categories-item-hidden").css('visibility', 'visible');
				$(".header-categories-item:hover .header-categories-item-hidden").css('opacity', '1');
			})
		})


		$('.header-categories-close').click(function(event){
			event.preventDefault();
			$('.header').removeClass('open-cat');
		});


		$('.checkbox-item-trigger').click(function(){
			if($(this).find('input').prop('checked')){
				$(this).addClass('active');
			}else {
				$(this).removeClass('active');
			}
		});

		$('.card-top-slider-item').click(function(event){
			event.preventDefault();
			var src = $(this).find('img').attr('src');
			$('.card-top-slider-item').removeClass('active');
			$(this).addClass('active');
			$(this).closest('.card-top-slider').find('.card-top-slider-right').attr('href', src);
			$(this).closest('.card-top-slider').find('.card-top-slider-right').find('img').attr('src', src);
		});

		$('.select-block-trigger').click(function(event){
			event.preventDefault();
			$(this).closest('.select-block').toggleClass('open');
		});

		$('.select-block-hidden .select-block-hidden-item:not(.active)').click(function(){
			var text = $(this).text();
			var activeEl = $(this).closest('.select-block').find('.select-block-hidden-item');
			var textLayout = $(this).closest('.select-block').find('.select-block-trigger-text-2');

			textLayout.text(text);
			activeEl.removeClass('active');
			$(this).addClass('active');

			$(this).closest('.select-block').removeClass('open');
		});

		$('.header-categories-item > a').click(function(event){
			if ($(window).width() < 1200) {
				event.preventDefault();
				$(this).parent().toggleClass('active');
			}
		});


		$('.delete-cart-item').click(function(e){
			e.preventDefault();
			var key = $(this).attr('data-product-key');
			cart.remove(key);
			var list = $(this).closest('.cart-list');
			$(this).closest('.cart-item').remove();
			if($('.cart-list .cart-item').length == 0) {
				list.addClass('no-cnt');
			}
		});


		$('.cart-quantity button').click(function(e){
			e.preventDefault();
			var activeQuantity = $(this).closest('.quantity-row').find('input').val();
			var cartId = $(this).attr('data-product-key');
			if($(this).hasClass('quantity-plus')) {
				var count = $(this).closest('.quantity-row').find('input').val(Number(activeQuantity) + 1).val()
			}
			if($(this).hasClass('quantity-minus')) {
				if(activeQuantity !== '1') {
					var count = $(this).closest('.quantity-row').find('input').val(Number(activeQuantity) - 1).val()
				}
			}
			cart.update(cartId, count);
			console.log(count);
			console.log(cartId);
		});

		// b-lazy

		var bLazy = new Blazy({});


        // $(".header-categories-items").mouseleave(function(){
        //     if ($(window).width() > 1200) {
        //         $('.header').removeClass('open-cat');
        //     }
        // });


		$('.popup-close').click(function(){
			$(this).closest('.popup-wrapper').fadeOut('fast');
			$('body').removeClass('hidden');
		});

        $('.social-links-btn').click(function (event) {
            if (!$('.social-links-menu').is(':visible')) {
                $('.social-links-menu').addClass('active-menu');
                $('.social-links-menu').show(300);
            }
        });
        setInterval(function(){
            $(".social-links-btn").toggleClass("animat");
        }, 5000);

        $('.social-links-btn').hover(function(){
            $('.social-links-btn').toggleClass("animat-inf");
        }, function(){
            $('.social-links-btn').toggleClass("animat-inf");
        });

        $(document).mouseup(function (e) { // СЃРѕР±С‹С‚РёРµ РєР»РёРєР° РїРѕ РІРµР±-РґРѕРєСѓРјРµРЅС‚Сѓ
            var div = $(".social-links-menu"); // С‚СѓС‚ СѓРєР°Р·С‹РІР°РµРј ID СЌР»РµРјРµРЅС‚Р°
            if (!div.is(e.target) // РµСЃР»Рё РєР»РёРє Р±С‹Р» РЅРµ РїРѕ РЅР°С€РµРјСѓ Р±Р»РѕРєСѓ
                && div.has(e.target).length === 0) { // Рё РЅРµ РїРѕ РµРіРѕ РґРѕС‡РµСЂРЅРёРј СЌР»РµРјРµРЅС‚Р°Рј
                div.hide(300); // СЃРєСЂС‹РІР°РµРј РµРіРѕ
                $('.social-links-menu').removeClass('active-menu');
            }

        });

		// phone mask
		$('.tel-trigger').mask("+380 (99) 999-99-99");
		/*
		$('.cart-trigger').on('click', function(e){
			e.preventDefault();
			var el = $(this);
			var that = $(this).closest('.cart-trigger-item').find('.cart-trigger-item-img');
			var cart = $(".cart");
			var w = that.width();

			that.clone()
				.css({'width' : w,
				'position' : 'absolute',
				'z-index' : '9999',
				top: that.offset().top,
				left:that.offset().left})
				.appendTo("body")
				.animate({opacity: 0.05,
					left: cart.offset()['left'],
					top: cart.offset()['top'],
					width: 20}, 1000, function() {
						$(this).remove();
			});



			cart.each(function(){
				var cart = $(this);

				var cartNum = cart.find('.cart-icon-num');
				var cartNumCount = Number(cartNum.text());
				var cartSum = cart.find('.cart-info-sum');
				var cartSumNum = Number(cartSum.text());
				var itemPrice = Number(el.closest('.cart-trigger-item').find('.cart-trigger-item-price').text().replace(/\s+/g, ''));


				cartSum.text(cartSumNum + itemPrice);

				cartNumCount++;

				cartNum.text(cartNumCount);

				setTimeout(function(){
					if(cartNumCount > 0){
						cart.addClass('added');
					}else {
						cart.removeClass('added');
					}
				}, 1000);




			});


		});*/

	},
	carousel: function(){



	},
	submit: function(){
		$("#datas-form").submit(function(event){
			event.preventDefault();
			formGroup = $(this).find('.form-group');
			var that = this;
			setTimeout(function(){
				if(!formGroup.hasClass('has-error')){

					var fields = $(that).serialize();
					$.ajax({
						url: 'index.php?route=checkout/simplecheckout',
						type: 'post',
						data: fields,
						dataType: 'json',
						beforeSend: function() {
							$('.popup-wrapper').removeClass('active');
							$('#thanksPopup').addClass('active');
							$('body').addClass('hidden');
							var bLazy = new Blazy({});
						},
						complete: function() {
						},
						success: function() {
						},
						error: function(xhr, ajaxOptions, thrownError) {
							console.log(thrownError);
							console.log(xhr.statusText);
							console.log(xhr.responseText);
						}
					});

				}
			}, 100)

		});
	},
};

(function() {
	common.init();
}());


