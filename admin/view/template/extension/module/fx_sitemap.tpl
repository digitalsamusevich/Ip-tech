<?php echo $header; ?>
<?php if((float)VERSION < 2) { ?>
<script   src="https://code.jquery.com/jquery-1.9.1.min.js"   integrity="sha256-wS9gmOZBqsqWxgIVgA8Y9WcQOa7PgSIX+rPA0VL2rbQ="   crossorigin="anonymous"></script>
<link href="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.6/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-1q8mTJOASx8j1Au+a5WDVnPi2lkFfwwEAa8hDDdjZlpLegxhjVME1fgjWPGmkzs7" crossorigin="anonymous">
<script src="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.6/js/bootstrap.min.js" integrity="sha384-0mSbJDEHialfmuBBQP6A4Qrprq5OVfW37PRR3j5ELqxss1yVqOtnepnHVP9aJ7xS" crossorigin="anonymous"></script>
<link href="https://maxcdn.bootstrapcdn.com/font-awesome/4.6.3/css/font-awesome.min.css" rel="stylesheet" integrity="sha384-T8Gy5hrqNKT+hzMclPo118YTQO6cYprQmhrYwIiQ/3axmI1hQomh7Ud2hPOy8SP1" crossorigin="anonymous">
<?php }  ?>

        <div class="name">
            <h2>#FX <p>Sitemap</p></h2><sup>6 beta</sup></h2> 
        </div>

<div id="content">

    <div class="page-header">
        <div class="container-fluid">
            <div class="pull-right nopaddingtop">
                <button type="submit" form="form-fx_sitemap" class="btn btn-primary" id="save"><i class="fa fa-check"></i></button>
                <a href="<?php echo $cancel; ?>" class="cancel btn btn-default"><i class="fa fa-times"></i></a>
            </div>
			
            <ul class="breadcrumb">
                <?php foreach ($breadcrumbs as $breadcrumb) { ?>
                <li><a href="<?php echo $breadcrumb['href']; ?>"><?php echo $breadcrumb['text']; ?></a></li>
                <?php } ?>
            </ul>
        </div>
    </div>

  

<div class="container-fluid">

    <?php if ($error_warning) { ?>

    <div class="alert alert-danger"><i class="fa fa-exclamation-circle"></i> <?php echo $error_warning; ?>

      <button type="button" class="close" data-dismiss="alert">&times;</button>

    </div>

    <?php } ?>

    <div class="panel panel-default">

		<div class="panel-body">

			<form action="<?php echo $action; ?>" method="post" enctype="multipart/form-data" id="form-fx_sitemap" class="form-horizontal">

		         <ul class="nav nav-tabs">
                        <li class="active"><a data-toggle="tab" href="#main">Main</a></li>
						<?php //if ($kley) { ?>
                        <li><a data-toggle="tab" href="#products"><?=$produsts?></a></li>
						
                        <li><a data-toggle="tab" href="#categories"><?=$categories?></a></li>
                        <li><a data-toggle="tab" href="#brands"><?=$brands?></a></li>
                        <li><a data-toggle="tab" href="#other"><?=$other?></a></li>
                        <li><a data-toggle="tab" href="#service"><?=$service?></a></li>
						<?php // } ?>
                 </ul>
				 
				 
                <div class="tab-content">
					<div id="main" class="fade out tab-pane in active">
					
						<div class="form-group">
							<label class="col-sm-2 control-label">Sitemap <?=$status?></label>
							<div class="col-sm-10 btn-group">
								<input class="tgl tgl-skewed" id="sitemap_on" name="sitemap_on" type="checkbox" value="<?php echo $sitemap_on ? 1 : 0; ?>">
								<label class="tgl-btn" data-tg-off="" data-tg-on="✓" for="sitemap_on"></label>	
							</div>								
						</div>

						<div class="form-group">
							<label class="col-sm-2 control-label" for="fx_text_defalt"><?php echo $text_defalt; ?></label>
							<div class="col-sm-10">
								<textarea name="default" id="default" class="form-control"><?php echo $default; ?></textarea>
							</div>
						</div>

						<div class="form-group">
							<label class="col-sm-2 control-label"><?php echo $text_key; ?></label>
							<div class="col-sm-10">
								<input type="text" name="key" value="<?php echo $key; ?>" id="key" class="form-control" />
							</div>
						</div>
			
						<div class="form-group">
							<label class="col-sm-2 control-label">MultiSitemap</label>
							<div class="col-sm-2 btn-group">
								<input class="tgl tgl-skewed" id="multi" name="multi" type="checkbox" value="<?php echo $multi ? 1 : 0; ?>">
								<label class="tgl-btn" data-tg-off="" data-tg-on="✓" for="multi"></label>	
							</div>
							
							<label class="col-sm-2 control-label">limit</label>
							<div class="col-sm-2">
								<input type="text" name="limit" value="<?php echo $limit; ?>" id="limit" class="form-control" />
							</div>
							
						</div>
						
						<div class="form-group">
							<label class="col-sm-2 control-label"><?=$text_only_seo_url?></label>
							<div class="col-sm-10 btn-group">
								<input class="tgl tgl-skewed" id="only_seo_url" name="only_seo_url" type="checkbox" value="<?php echo $only_seo_url ? 1 : 0; ?>">
								<label class="tgl-btn" data-tg-off="" data-tg-on="✓" for="only_seo_url"></label>	
							</div>								
						</div>
						
						<div class="form-group">
							<label class="col-sm-2 control-label"><?=$text_sort?></label>
							<div class="col-sm-10 btn-group">
								<input class="tgl tgl-skewed" id="sort" name="sort" type="checkbox" value="<?php echo $sort ? 1 : 0; ?>">
								<label class="tgl-btn" data-tg-off="" data-tg-on="✓" for="sort"></label>	
							</div>								
						</div>
					</div>
				<!--------->
					<div id="products" class="fade out tab-pane">

						<div class="form-group">
							<label class="col-sm-2 control-label"><?=$status?></label>
							<div class="col-sm-10 btn-group">
								<input class="tgl tgl-skewed" id="products_on" name="products_on" type="checkbox" value="<?php echo $products_on ? 1 : 0; ?>">
								<label class="tgl-btn" data-tg-off="" data-tg-on="✓" for="products_on"></label>
							</div>								
						</div>

						<div class="form-group">
							
							<label class="col-sm-2 control-label"><?=$text_postfix?></label>
							<div class="col-sm-1">
								<input type="text" name="postfix" value="<?php echo $postfix; ?>" class="form-control" />
							</div>
						</div>

						<div class="form-group">
							<label class="col-sm-2 control-label">lastmod</label>
							<div class="col-sm-10 btn-group">
								<input class="tgl tgl-skewed" id="product_lastmod" name="product_lastmod" type="checkbox" value="<?php echo $product_lastmod ? 1 : 0; ?>">
								<label class="tgl-btn" data-tg-off="" data-tg-on="✓" for="product_lastmod"></label>
							</div>								
						</div>
					
						<div class="form-group">
							<label class="col-sm-2 control-label">changefreq</label>
							<div class="col-sm-10 btn-group">								
								<select name="product_changefreq" class="form-control">
								  <option value="off">---</option>
								  <?php foreach ($changefreqs as $changefreq) { ?>
								  <option value="<?php echo $changefreq; ?>"  <?php if ($product_changefreq == $changefreq) echo 'selected="selected" ';  echo '>' . $changefreq; ?></option>
								  <?php } ?>
								</select>
							</div>
						</div>

						<div class="form-group">
							<label class="col-sm-2 control-label">priority</label>
							<div class="col-sm-10">
								<input type="text" name="product_priority" value="<?php echo $product_priority; ?>" class="form-control" />
							</div>
						</div>
						
						<div class="form-group">						
							<label class="col-sm-2 control-label express">Express(short address)</label>
							<div class="col-sm-10 btn-group express">
								<input class="tgl tgl-skewed" id="express" name="express" type="checkbox" value="<?php echo $express ? 1 : 0; ?>">
								<label class="tgl-btn" data-tg-off="" data-tg-on="✓" for="express"></label>
							</div>
						</div>
						
					</div>
				<!--------->
					
					<div id="categories" class="fade out tab-pane">
					
						<div class="form-group">
							<label class="col-sm-2 control-label"><?=$status?></label>
							<div class="col-sm-10 btn-group">
								<input class="tgl tgl-skewed" id="categories_on" name="categories_on" type="checkbox" value="<?php echo $categories_on ? 1 : 0; ?>">
								<label class="tgl-btn" data-tg-off="" data-tg-on="✓" for="categories_on"></label>	
							</div>								
						</div>
						
						<div class="form-group">
							<label class="col-sm-2 control-label">lastmod</label>
							<div class="col-sm-10 btn-group">
								<input class="tgl tgl-skewed" name="category_lastmod" id="category_lastmod" type="checkbox" value="<?php echo $category_lastmod ? 1 : 0; ?>">
								<label class="tgl-btn" data-tg-off="" data-tg-on="✓" for="category_lastmod"></label>
							</div>								
						</div>
					
						<div class="form-group">
							<label class="col-sm-2 control-label">changefreq</label>
							<div class="col-sm-10 btn-group">								
								<select name="category_changefreq" class="form-control">
								  <option value="off">---</option>
								  <?php foreach ($changefreqs as $changefreq) { ?>
								  <option value="<?php echo $changefreq; ?>"  <?php if ($category_changefreq == $changefreq) echo 'selected="selected" ';  echo '>' . $changefreq; ?></option>
								  <?php } ?>
								</select>
							</div>
						</div>

						<div class="form-group">
							<label class="col-sm-2 control-label">priority</label>
							<div class="col-sm-10">
								<input type="text" name="category_priority" value="<?php echo $category_priority; ?>" class="form-control" />
							</div>
						</div>
						
						<div class="form-group">						
							<label class="col-sm-2 control-label express">Express(short address)</label>
							<div class="col-sm-10 btn-group express">
								<input class="tgl tgl-skewed" id="express_cat" name="express_cat" type="checkbox" value="<?php echo $express_cat ? 1 : 0; ?>">
								<label class="tgl-btn" data-tg-off="" data-tg-on="✓" for="express_cat"></label>
							</div>
						</div>
					
					</div>
				<!--------->
					
					<div id="brands" class="fade out tab-pane">
					
						<div class="form-group">
							<label class="col-sm-2 control-label"><?=$status?></label>
							<div class="col-sm-10 btn-group">
								<input class="tgl tgl-skewed" id="brands_on" name="brands_on" type="checkbox" value="<?php echo $brands_on ? 1 : 0; ?>">
								<label class="tgl-btn" data-tg-off="" data-tg-on="✓" for="brands_on"></label>	
							</div>								
						</div>
						
						<!--div class="form-group">
							<label class="col-sm-2 control-label">lastmod</label>
							<div class="col-sm-10 btn-group">
								<input class="tgl tgl-skewed" name="brands_lastmod" id="brands_lastmod" type="checkbox" value="<?php echo $brands_lastmod ? 1 : 0; ?>">
								<label class="tgl-btn" data-tg-off="" data-tg-on="✓" for="brands_lastmod"></label>
							</div>								
						</div-->
					
						<div class="form-group">
							<label class="col-sm-2 control-label">changefreq</label>
							<div class="col-sm-10 btn-group">								
								<select name="brands_changefreq" class="form-control">
								  <option value="off">---</option>
								  <?php foreach ($changefreqs as $changefreq) { ?>
								  <option value="<?php echo $changefreq; ?>"  <?php if ($brands_changefreq == $changefreq) echo 'selected="selected" ';  echo '>' . $changefreq; ?></option>
								  <?php } ?>
								</select>
							</div>
						</div>

						<div class="form-group">
							<label class="col-sm-2 control-label">priority</label>
							<div class="col-sm-10">
								<input type="text" name="brands_priority" value="<?php echo $brands_priority; ?>" class="form-control" />
							</div>
						</div>						
					
					</div>
				<!--------->
					
					<div id="other" class="fade out tab-pane">
					
						<div class="form-group">
							<label class="col-sm-2 control-label"><?=$information?></label>
							<div class="col-sm-1 btn-group">
								<input class="tgl tgl-skewed" id="informations_on" name="informations_on" type="checkbox" value="<?php echo $informations_on ? 1 : 0; ?>">
								<label class="tgl-btn" data-tg-off="" data-tg-on="✓" for="informations_on"></label>	
							</div>
						</div>
					
						<div class="form-group">
							<label class="col-sm-2 control-label">Blog</label>
							<div class="col-sm-1 btn-group">
								<input class="tgl tgl-skewed" id="blog_on" name="blog_on" type="checkbox" value="<?php echo $blog_on ? 1 : 0; ?>">
								<label class="tgl-btn" data-tg-off="" data-tg-on="✓" for="blog_on"></label>	
							</div>
							
							<label class="col-sm-1 control-label">route</label>
							<div class="col-sm-6">
							  <input type="text" name="blog_route" value="<?php echo $blog_route; ?>" id="blog_route" class="form-control" />
							</div>
						</div>
					
						<div class="form-group">
							<label class="col-sm-2 control-label">Article</label>
							<div class="col-sm-1 btn-group">
								<input class="tgl tgl-skewed" id="article_on" name="article_on" type="checkbox" value="<?php echo $article_on ? 1 : 0; ?>">
								<label class="tgl-btn" data-tg-off="" data-tg-on="✓" for="article_on"></label>	
							</div>
							
							<label class="col-sm-1 control-label">route</label>
							<div class="col-sm-6">
							  <input type="text" name="article_route" value="<?php echo $article_route; ?>" id="article_route" class="form-control" />
							</div>
						</div>
					
						<div class="form-group">
							<label class="col-sm-2 control-label">News</label>
							<div class="col-sm-1 btn-group">
								<input class="tgl tgl-skewed" id="news_on" name="news_on" type="checkbox" value="<?php echo $news_on ? 1 : 0; ?>">
								<label class="tgl-btn" data-tg-off="" data-tg-on="✓" for="news_on"></label>	
							</div>
							
							<label class="col-sm-1 control-label">route</label>
							<div class="col-sm-6">
							  <input type="text" name="news_route" value="<?php echo $news_route; ?>" id="news_route" class="form-control" />
							</div>
						</div>
					
						<div class="form-group">
							<label class="col-sm-2 control-label">SeoCms</label>
							<div class="col-sm-1 btn-group">
								<input class="tgl tgl-skewed" id="records_on" name="records_on" type="checkbox" value="<?php echo $records_on ? 1 : 0; ?>">
								<label class="tgl-btn" data-tg-off="" data-tg-on="✓" for="records_on"></label>	
							</div>
						</div>
					
						<div class="col-sm-4 form-group">
							<div class="form-group">
								<label class="col-sm-6 control-label">OcFilter</label>
								<div class="col-sm-6 btn-group">
									<input class="tgl tgl-skewed" id="ocfilter_on" name="ocfilter_on" type="checkbox" value="<?php echo $ocfilter_on ? 1 : 0; ?>">
									<label class="tgl-btn" data-tg-off="" data-tg-on="✓" for="ocfilter_on"></label>	
								</div>
							</div>
						
							<div class="form-group">
								<label class="col-sm-6 control-label">MegaFilter Pro</label>
								<div class="col-sm-6 btn-group">
									<input class="tgl tgl-skewed" id="mfp_on" name="mfp_on" type="checkbox" value="<?php echo $mfp_on ? 1 : 0; ?>">
									<label class="tgl-btn" data-tg-off="" data-tg-on="✓" for="mfp_on"></label>	
								</div>
							</div>
						</div>
						
						
						<div class="col-sm-8 form-group">
							<br>
							<br>
							<label class="col-sm-2 control-label"> + "/" </label>
							<div class="col-sm-2 btn-group">
								<input class="tgl tgl-skewed" id="slash" name="slash" type="checkbox" value="<?php echo $slash ? 1 : 0; ?>">
								<label class="tgl-btn" data-tg-off="" data-tg-on="✓" for="slash"></label>	
							</div>
						</div>
						
						<div class="col-sm-12  form-group"></div>
					
						<div class="form-group">
							<label class="col-sm-2 control-label">FilterPro</label>
							<div class="col-sm-10 btn-group">
								<input class="tgl tgl-skewed" id="filterpro_on" name="filterpro_on" type="checkbox" value="<?php echo $filterpro_on ? 1 : 0; ?>">
								<label class="tgl-btn" data-tg-off="" data-tg-on="✓" for="filterpro_on"></label>
							</div>
						</div>
					
						<div class="form-group">
							<label class="col-sm-2 control-label">FilterVier</label>
							<div class="col-sm-10 btn-group">
								<input class="tgl tgl-skewed" id="vier_on" name="vier_on" type="checkbox" value="<?php echo $vier_on ? 1 : 0; ?>">
								<label class="tgl-btn" data-tg-off="" data-tg-on="✓" for="vier_on"></label>
							</div>
						</div>
					
						<div class="form-group">
							<label class="col-sm-2 control-label">Home</label>
							<div class="col-sm-10 btn-group">
								<input class="tgl tgl-skewed" id="home_on" name="home_on" type="checkbox" value="<?php echo $home_on ? 1 : 0; ?>">
								<label class="tgl-btn" data-tg-off="" data-tg-on="✓" for="home_on"></label>
							</div>
						</div>
					
						<div class="form-group">
							<label class="col-sm-2 control-label">Special</label>
							<div class="col-sm-10 btn-group">
								<input class="tgl tgl-skewed" id="special_on" name="special_on" type="checkbox" value="<?php echo $special_on ? 1 : 0; ?>">
								<label class="tgl-btn" data-tg-off="" data-tg-on="✓" for="special_on"></label>
							</div>
						</div>
					
						<div class="form-group">
							<label class="col-sm-2 control-label">Contact</label>
							<div class="col-sm-10 btn-group">
								<input class="tgl tgl-skewed" id="contact_on" name="contact_on" type="checkbox" value="<?php echo $contact_on ? 1 : 0; ?>">
								<label class="tgl-btn" data-tg-off="" data-tg-on="✓" for="contact_on"></label>
							</div>
						</div>
						
						<div class="form-group">
							<label class="col-sm-2 control-label">#FX Brands</label>
							<div class="col-sm-10 btn-group">
								<input class="tgl tgl-skewed" id="brands_category_on" name="brands_category_on" type="checkbox" value="<?php echo $brands_category_on ? 1 : 0; ?>">
								<label class="tgl-btn" data-tg-off="" data-tg-on="✓" for="brands_category_on"></label>
							</div>
						</div>
					
					</div>
					
				<!--------->
					
					<div id="service" class="fade out tab-pane">
			
						<div class="form-group">
						
							<label class="col-sm-2 control-label">Ultra</label>
							<div class="col-sm-10 btn-group">
								<input class="tgl tgl-skewed" id="ultra" name="ultra" type="checkbox" value="<?php echo $ultra ? 1 : 0; ?>">
								<label class="tgl-btn" data-tg-off="" data-tg-on="✓" for="ultra"></label>
							</div>
							
							<label class="col-sm-2 control-label">Categories Super Cache</label>
							<div class="col-sm-10 btn-group">
								<input class="tgl tgl-skewed" id="categories_from_db" name="categories_from_db" type="checkbox" value="<?php echo $categories_from_db ? 1 : 0; ?>">
								<label class="tgl-btn" data-tg-off="" data-tg-on="✓" for="categories_from_db"></label>	
							</div>
							
							<div class="col-sm-2"></div>
							<div class="generate col-sm-8"><div id="generate" class="btn btn-primary center-block"><i class="fa fa-play"></i> Generate</div></div>
							<div class="col-sm-12 msg"> </div>
						</div>
					
						<div class="form-group">
							<label class="col-sm-2 control-label">MultiStore</label>
							<div class="col-sm-10 btn-group">
								<input class="tgl tgl-skewed" id="multistore" name="multistore" type="checkbox" value="<?php echo $multistore ? 1 : 0; ?>">
								<label class="tgl-btn" data-tg-off="" data-tg-on="✓" for="multistore"></label>
							</div>
						</div>
			
						<div class="form-group">
							<label class="col-sm-2 control-label">Log file</label>
							<div class="col-sm-9 btn-group">
								<input class="tgl tgl-skewed" id="log" name="log" type="checkbox" value="<?php echo $log ? 1 : 0; ?>">
								<label class="tgl-btn" data-tg-off="" data-tg-on="✓" for="log"></label>	
							</div>
							<div class="col-sm-1 btn-group">
								<a class="btn btn-danger clear pull-right "><i class="fa fa-eraser"></i></a>
							</div>
							
							<label class="col-sm-2 control-label"></label>
							<div class="col-sm-10">
								<textarea readonly="readonly" id="log_file" rows="15" class="form-control"><?php echo $log_file; ?></textarea>
							</div>
							
						</div>
			
						<div class="form-group">
							<label class="col-sm-2 control-label">Add URLs</label>
							<div class="col-sm-10 btn-group">
								<input class="tgl tgl-skewed" id="add_file_on" name="add_file_on" type="checkbox" value="<?php echo $add_file_on ? 1 : 0; ?>">
								<label class="tgl-btn" data-tg-off="" data-tg-on="✓" for="add_file_on"></label>
							</div>
							
							<label class="col-sm-2 control-label"></label>
							<div class="col-sm-10">
								<textarea id="add_file" name="add_file" rows="12" class="form-control"><?php echo $add_file; ?></textarea>
							</div>
						</div>						
						
						<div class="form-group">
							<label class="col-sm-2 control-label">Exclude URLs</label>
							<div class="col-sm-10 btn-group">
								<input class="tgl tgl-skewed" id="exclude_file_on" name="exclude_file_on" type="checkbox" value="<?php echo $exclude_file_on ? 1 : 0; ?>">
								<label class="tgl-btn" data-tg-off="" data-tg-on="✓" for="exclude_file_on"></label>
							</div>
							
							<label class="col-sm-2 control-label"></label>
							<div class="col-sm-10">
								<textarea id="exclude_file" name="exclude_file" rows="12" class="form-control"><?php echo $exclude_file; ?></textarea>
							</div>
						</div>
			
						<div class="form-group">
							<label class="col-sm-2 control-label">Add to the Multisitemap</label>
							<div class="col-sm-10 btn-group">
								<input class="tgl tgl-skewed" id="add_to_multi_on" name="add_to_multi_on" type="checkbox" value="<?php echo $add_to_multi_on ? 1 : 0; ?>">
								<label class="tgl-btn" data-tg-off="" data-tg-on="✓" for="add_to_multi_on"></label>
							</div>
							
							<label class="col-sm-2 control-label"></label>
							<div class="col-sm-10">
								<textarea id="add_to_multi" name="add_to_multi" rows="12" class="form-control"><?php echo $add_to_multi; ?></textarea>
							</div>
						</div>	
					
						<div class="form-group">
							<label class="col-sm-2 control-label">MultiLang Simple Smap (short URLs)</label>
							<div class="col-sm-10 btn-group">
								<input class="tgl tgl-skewed" id="multilang" name="multilang" type="checkbox" value="<?php echo $multilang ? 1 : 0; ?>">
								<label class="tgl-btn" data-tg-off="" data-tg-on="✓" for="multilang"></label>
								<span class="help-block">Only for Opencart 3.0 with Standart SEO_URL Controller</span>
							</div>
						</div>
					
						<div class="form-group">
							<label class="col-sm-2 control-label">HTTP header</label>
							<div class="col-sm-10">
							  <input type="text" name="http_header" value="<?php echo $http_header; ?>" id="http_header" class="form-control" />
							</div>
						</div>
						
						
					</div>
					
				</div>

			</form>
				<div class="powered"><span>Powered by <a href="http://www.dev-opencart.com/"><p>DEV-OPENCART.COM</p></a></span></div>
        </div>
    </div>
	
	<div id = "sitemaps">
		
	</div>
	<br>
	<button class="btn" id="generate-urls">generate seo url</button>
</div>
 




<style>

form {
    padding-top: 0!important;
}

#generate{
	padding: 30px;
	font-size: 2em;
}

#container {
    background: #f6f6f6
}

.powered{
  text-align: center;
  font-size: 1.1em;
  padding: .9em;  
}

<?php if((float)VERSION < 2) { ?>
.page-header{margin-top:0}
.pull-right{padding-top: 15px}

<?php } ?>
.powered a{color:#777; border-bottom: 1px dotted;}


.powered a:hover{text-decoration: none; border-bottom: 1px solid;}

h1 p, h2 p, .powered a p {color:#29D!important; display:inline}
h1 span, h2 span, .powered a span{color:#FB5151}
h1, h2 {display: inline}
#redirect_list_edit{font-size:2em; cursor: pointer;}
.go{font-size:1.8em; text-align:center}
.alert-danger {
  background-color: #adf;
  border: none;
  font-size: 1.25em;
  color: #FFF; }
/*.form-group{border: none!important}
/*.btn-group > label{padding: 5px 10px;}
.btn-group > label:not(.active){opacity:.5!important;padding: 5px 10px;}*/
.col-sm-7 > .help-block {margin-bottom: 20px;}
.form-group + .form-group { border-top: 1px solid #ededed; }
.yes:hover{cursor: no-drop}
.form-group {
    padding-top: 15px;
    padding-bottom: 15px;
    margin-bottom: 0;
}
.buttons .btn {margin-bottom:5px}
.panel-body {
  padding: 0; 
  padding-top: 0;
background: #444
  }

.breadcrumb li:last-child a {
    color: #1e91cf;
}
.breadcrumb li a {
    color: #999999;
    font-size: 11px;
    padding: 0px;
    margin: 0px;
}


  .tab-content {
	background: #fff;
	padding: 10px;
    border: 1px solid #444;
  }
  .nav-tabs {
	margin-bottom: 0;
	background: #444;	
    border-bottom: none;
	background-image: url('data:image/png;base64,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');
    background-repeat: no-repeat;
}  
.nav {
	padding-left: 39px;
	
}

.panel-default {
	border-top: 0
}
.panel-left .nav span {
	padding-top: 5px;
	color: #0dca24;
	padding-bottom: 1px;
	border: 1px solid #ddd;
	background: #e244a7;
}

#header, #column-left, #footer, #menu {
  display: none;

}

.panel-body, .panel, .panel-default{
	filter: none!important;
}


.btn-group label.btn { 
  min-width: 43px;
}

  .form-horizontal .form-group {
margin-left: -4px;
margin-right: -4px;
    }
	
.noindex_addon .btn-group{
	min-height: 39px;
}

#import_info{
display: block;
margin: 0;
padding:8px 0;
}

#import{
display: block;
clear: both;
margin: 8px 0;
}

.import{
	min-height: 68px;
	padding-bottom:0;
}

.progress{
    background-color: #f38733;
	margin-bottom: 0;
}

input.war{
	background: #f99;
}

.war label.control-label{
	color: #f55;
}

.breadcrumb {
    background: none;
    padding-left: 0!important;
	display: block;
	margin-top: 5px;
	margin-bottom: 15px;
}

.page-header h1{
	margin-bottom: 0;	
}

input {	
    padding-left: 10px!important;
}
div.btn {
	padding-top: 6px;
}

.page-header {
    padding-bottom: 0;
    margin: 0;
    border-bottom: none;
}

#save{
	background: #283;
	border-color: #a4c5a6;
}


#save:focus, #save:hover{
	background: #76c983;
	border-color: #94b596;
}

.btn:focus {
    outline: none;
}

.not_saved{
	background: #e77!important;
	border-color: #d66!important;
}


#column-left + #content {
    margin: 0px;
}
#content {
    padding-top: 20px;
    transition: all 0.3s;
}

.tab-pane{
	transition: all .2s ease;
}

.table thead td span[data-toggle="tooltip"]:after, label.control-label span:after {
    color: #607D8B;
}

.form-control {
  background-color: #f5f5f5;
  border: 1px solid #f7f1f1;
  box-shadow: none;
}

.form-control:hover {
  background-color: #eee;
  border: 1px solid #f7f1f1;
  box-shadow: none;
}.menu4{	color:#0dca24;
	padding-bottom: 1px;	}


.1tgl-btn:hover{
    opacity: .75;
}

.tgl-skewed:checked + .tgl-btn:hover {
    background: #76b983;
}
.tgl-skewed + .tgl-btn:hover {
    background: #777;
}

#save:hover, .not_saved:hover {
    background: #ec5!important;
  border: 1px solid #db4 !important;
}

.nav-tabs > li > a {
color: #fff;
margin-right: 0;
border: none!important;
border-radius: 0;	
padding: 9px 15px 12px;	
border-top: 1px solid #444!important;
}

.nav-tabs > li > a:hover {
border-top: 1px solid #fb9c37!important;
transition: border-top 999ms;
}

.nav-tabs > li:hover, .nav-tabs > li a:hover {
  color: #444;
}

.nav-tabs > li.active:hover, .nav-tabs > li.active a:hover {
  background: #fff!important;
  color: #444;
 transition: 999ms;
}

.nav-tabs > li.active a {
	border-top: 1px solid #fb9c37;
}

#column-left + #content {
    margin-left: 0 !important
}


.tgl {
  display: none;
}
 
.tgl + .tgl-btn {
  outline: 0;
  display: block;
  width: 2em;
  height: 2em;
  position: relative;
  cursor: pointer;
  -webkit-user-select: none;
     -moz-user-select: none;
      -ms-user-select: none;
          user-select: none;
}

.tgl + .tgl-btn6 {
  width: 6em;
}

.tgl + .tgl-btn:after, .tgl + .tgl-btn:before {
  position: relative;
  display: block;
  content: "";
  width: 50%;
  height: 100%;
}
.tgl + .tgl-btn:after {
  left: 0;
}
.tgl + .tgl-btn:before {
  display: none;
}
.tgl:checked + .tgl-btn:after {
  left: 50%;
}

.tgl-skewed + .tgl-btn {
  overflow: hidden;
  -webkit-backface-visibility: hidden;
          backface-visibility: hidden;
  /*-webkit-transition: all .2s ease;
  transition: all .2s ease;*/
  font-family: sans-serif;
  background: #888;
  border-radius: 2px;
}
.tgl-skewed + .tgl-btn:after, .tgl-skewed + .tgl-btn:before {
  display: inline-block;
  /*
  -webkit-transition: all .2s ease;
  transition: all .2s ease;*/
  width: 100%;
  text-align: center;
  position: absolute;
  line-height: 2em;
  font-weight: bold;
  color: #fff;
}
.tgl-skewed + .tgl-btn:after {
  left: 100%;
  content: attr(data-tg-on);
}
.tgl-skewed + .tgl-btn:before {
  left: 0;
  content: attr(data-tg-off);
}
.tgl-skewed + .tgl-btn:active {
  background: #888;
}
.tgl-skewed + .tgl-btn:active:before {
  left: -10%;
}
.tgl-skewed:checked + .tgl-btn {
  background: #fb913f;
}
.tgl-skewed:checked + .tgl-btn:before {
  left: -100%;
}
.tgl-skewed:checked + .tgl-btn:after {
  left: 0;
}
.tgl-skewed:checked + .tgl-btn:active:after {
  left: 10%;
}

#container {
    background: #f9f9f9
}

pre{
	margin: 0;
	background: #cde
}

.ramka{
	background: #ffc;
	margin-bottom: 30px
}
.close {
    opacity: 0.5;
	color: #fff
}

.panel-body label {
	white-space: nowrap;
	overflow: hidden
}

iframe {
	width: 650px;
	border: none;
	min-height: 450px;
}

.info{
    text-align: center;
}

.breadcrumb li:before {
    padding-left: 1px!important;
}
.breadcrumb li+li:before {
	content: '🞄';
}

.cancel{
    background: #444;
	color: #fff
}

.powered a:hover{text-decoration: none; border-bottom: 1px solid;}

.name{
	background: #444;
	padding: 15px;
	padding-bottom: 10px;
	color: #fff; 
    border-radius: 2px;
}

h1 p, h2 p, .powered a p {
    color: #fb9c37!important;
    display: inline;
    font-weight: 600;
}

sup{
    top: -1em;    font-size: medium;
}

.breadcrumb li:last-child a {
    color: #32bd48!important;
}

.nopaddingtop .btn, a.btn-danger{
	border-radius: 0
}

</style>

<script>
/*
$("form#form-fx_sitemap").submit(function(e) {
    var url = '<?php echo str_replace('&amp;', '&', $action); ?>';
    $.ajax({
		type: "POST",
		url: url,
		data: $("form#form-seopage").serialize(),
		success: function(data){
		},
		beforeSend: function() {
			$('#save').html('<i class="fa fa-spinner" aria-hidden="true"></i>');
		},
		complete: function() {
			setTimeout(function () {
				$('#save').html('<i class="fa fa-check"></i>');
			$('#save').removeClass("not_saved");
			}, 2000);			
		},
	});
	e.preventDefault();
});
*/
$('input').change(function() {
  $('#save').addClass("not_saved");
});

$('#content').on('click', '#reload', function() {
	document.location.reload(true);
});
	
$("input.tgl").on('change', function() {
  if ($(this).is(':checked')) {
    $(this).attr('value', '1');
  } else {
    $(this).attr('value', '0');
  }
  
  $('#checkbox-value').text($('#checkbox1').val());
});

$('input.tgl[value="1"]').attr( 'checked', true );

$('#generate').click(function() {
	
	$('#generate').html('<i class="fa fa-spinner fa-spin" aria-hidden="true"></i>');
	$('.msg').html('...');
	
	var link = '/<?=$link?>';
	
	go(link + '/cron');
	
	$('#generate').html('<i class="fa fa-check" aria-hidden="true"></i>');
});

$('.clear').click(function() {
	
	$('#log_file').val('');
	
	var link = '/<?=$link?>';
	
	go(link + '/clearlog');
	
});

function go(address) {
	$.ajax({
		url: address,
		datatype: 'text',		
		beforesend: function() {
			
		},
		success: function(json) {
			$('.msg').html(json);
		},
		complete: function(json) {
		   
		},		
		error: function(xhr, ajaxOptions, thrownError){
			var link = '/<?=$link?>';
			go(link.replace('extension/feed/', 'feed/'));
		}
	});
}


function sitemaps() {
	$.ajax({
		url: '/<?=$link?>/getSitemaps',
		datatype: 'text',
		
		success: function(data) {
			$('#sitemaps').html(data);
		},
	});
}

sitemaps();

$('#generate-urls').click(function() {
	
	$('#generate-urls').html('<i class="fa fa-spinner fa-spin" aria-hidden="true"></i>');
	$('#sitemaps').html('...');
	
	$.ajax({
		url: '/<?=$link?>/generateSitemapUrl',
		datatype: 'text',
		
		success: function(data) {
			sitemaps();
			$('#generate-urls').hide(400);
		},
	});
});
	
</script>



 <?php echo $footer; ?>