<?php
	if (basename($_SERVER['SCRIPT_NAME']) == basename(__FILE__)) exit('Please do not load this page directly');
	
	$wpp_settings_def = array(
		'stats' => array(
			'order_by' => 'comments',
			'limit' => 10
		),
		'tools' => array(
			'ajax' => false,
			'css' => true,
			'stylesheet' => true,
			'thumbnail' => array(
				'source' => 'featured',
				'field' => ''
			)
		)
	);
	
	$ops = get_option('wpp_settings_config');
	
	if (!$ops) {
		add_option('wpp_settings_config', $wpp_settings_def);
		$ops = $wpp_settings_def;
	}
	
	if ( isset($_POST['section']) ) {
		if ($_POST['section'] == "stats") {
			$ops['stats']['order_by'] = $_POST['stats_order'];
			$ops['stats']['limit'] = (is_numeric($_POST['stats_limit']) && $_POST['stats_limit'] > 0) ? $_POST['stats_limit'] : 10;
			
			update_option('wpp_settings_config', $ops);			
			echo "<div class=\"updated\"><p><strong>" . __('Settings saved.', 'wordpress-popular-posts-reloaded' ) . "</strong></p></div>";
			
		} else if ($_POST['section'] == "tools") {
			
			if ($_POST['thumb_source'] == "custom_field" && (!isset($_POST['thumb_field']) || empty($_POST['thumb_field']))) {
				echo '<div id="wpp-message" class="error fade"><p>'.__('Please provide the name of your custom field.', 'wordpress-popular-posts-reloaded').'</p></div>';
			} else {				
				$ops['tools']['thumbnail']['source'] = $_POST['thumb_source'];
				$ops['tools']['thumbnail']['field'] = $_POST['thumb_field'];
				
				update_option('wpp_settings_config', $ops);				
				echo "<div class=\"updated\"><p><strong>" . __('Settings saved.', 'wordpress-popular-posts-reloaded' ) . "</strong></p></div>";
			}
		} else if  ($_POST['section'] == "ajax") {
			$ops['tools']['ajax'] = $_POST['ajax'];
			
			update_option('wpp_settings_config', $ops);				
			echo "<div class=\"updated\"><p><strong>" . __('Settings saved.', 'wordpress-popular-posts-reloaded' ) . "</strong></p></div>";
		} else if  ($_POST['section'] == "css") {									
			$ops['tools']['css'] = $_POST['css'];
			
			//print_r($ops);
			
			update_option('wpp_settings_config', $ops);				
			echo "<div class=\"updated\"><p><strong>" . __('Settings saved.', 'wordpress-popular-posts-reloaded' ) . "</strong></p></div>";
		}
	}
	
	$rand = md5(uniqid(rand(), true));	
	$wpp_rand = get_option("wpp_rand");	
	if (empty($wpp_rand)) {
		add_option("wpp_rand", $rand);
	} else {
		update_option("wpp_rand", $rand);
	}
	
?>


<style>
	#wmpp-title {
		color:#666;
		font-family:Georgia, "Times New Roman", Times, serif;
		font-weight:100;		
		font-size:24px;
		font-style:italic;
	}
	
	.wmpp-subtitle {
		margin:8px 0 15px 0;
		color:#666;
		font-family:Georgia, "Times New Roman", Times, serif;
		font-size:16px;
		font-weight:100;
	}
	
	.wpp_boxes {
		display:none;
		overflow:hidden;
		width:100%;
	}
	
	#wpp-options {
		width:100%;
	}
	
		#wpp-options fieldset {
			margin:0 0 15px 0;
			width:99%;				
		}
		
			#wpp-options fieldset legend { font-weight:bold; }
	
			#wpp-options fieldset .lbl_wpp_stats {
				display:block;
				margin:0 0 8px 0;
			}
	
	#wpp-stats-tabs {
		padding:2px 0;
	}
		
	#wpp-stats-canvas {
		overflow:hidden;
		padding:2px 0;
		width:100%;
	}
	
		.wpp-stats {
			display:none;
			width:96%px;
			padding:1% 0;
			font-size:8px;
			background:#fff;
			border:#999 3px solid;
		}
		
		.wpp-stats-active {
			display:block;
		}
		
			.wpp-stats ol {
				margin:0;
				padding:0;
			}
			
				.wpp-stats ol li {
					overflow:hidden;
					margin:0 8px 10px 8px!important;
					padding:0 0 2px 0!important;
					font-size:12px;
					line-height:12px;
					color:#999;
					border-bottom:#eee 1px solid;
				}
				
					.wpp-post-title {
						/*display:block;*/
						display:inline;
						float:left;
						font-weight:bold;
					}
					
					.post-stats {
						display:inline;
						float:right;
						font-size:0.9em!important;
						text-align:right;
						color:#999;
					}
				
			.wpp-stats-unique-item, .wpp-stats-last-item {
				margin:0!important;
				padding:0!important;
				border:none!important;
			}
			/**/
			.wpp-stats p {
				margin:0;
				padding:0 8px;
				font-size:12px;
			}
			
	.wp-list-table h4 {
		margin:0 0 0 0;
	}
	
	.wpp-ans {
		display:none;
		width:100%;
	}
	
		.wpp-ans p {
			margin:0 0 0 0;
			padding:0;
		}
	
</style>

<script type="text/javascript">
	jQuery(document).ready(function(){
		
		// TABS
		jQuery(".subsubsub li a").click(function(e){
			var tab = jQuery(this);
			tab.addClass("current").parent().siblings().children("a").removeClass("current");
			
			jQuery(".wpp_boxes:visible").hide();
			jQuery("#" + tab.attr("rel")).fadeIn();
			
			e.preventDefault();
		});
		
		// STATISTICS TABS		
		jQuery("#wpp-stats-tabs a").click(function(){
			var activeTab = jQuery(this).attr("rel");
			jQuery(this).removeClass("button-secondary").addClass("button-primary").siblings().removeClass("button-primary").addClass("button-secondary");
			jQuery(".wpp-stats:visible").fadeOut("fast", function(){
				jQuery("#"+activeTab).slideDown("fast");
			});
			
			return false;
		});
			
		jQuery(".wpp-stats").each(function(){
			if (jQuery("li", this).length == 1) {
				jQuery("li", this).addClass("wpp-stats-last-item");
			} else {
				jQuery("li:last", this).addClass("wpp-stats-last-item");
			}
		});
		
		// FAQ
		jQuery(".wp-list-table a").click(function(e){
			var ans = jQuery(this).attr("rel");
			
			jQuery(".wpp-ans:visible").hide();			
			jQuery("#"+ans).slideToggle();
			
			e.preventDefault();
		});
		
		// TOOLS
		jQuery("#thumb_source").change(function() {
			if (jQuery(this).val() == "custom_field") {
				jQuery("#lbl_field, #thumb_field").show();
			} else {
				jQuery("#lbl_field, #thumb_field").hide();
			}
		});
	});
	
	// TOOLS
	function confirm_reset_cache() {
		if (confirm("<?php _e("This operation will delete all entries from Wordpress Popular Posts Reloaded' cache table and cannot be undone.", "wordpress-popular-posts-reloaded"); ?> \n" + "<?php _e("Do you want to continue?", "wordpress-popular-posts-reloaded"); ?>")) {
			jQuery.post(ajaxurl, {action: 'wpp_clear_cache', token: '<?php echo get_option("wpp_rand"); ?>', clear: 'cache'}, function(data){
				alert(data);
			});
		}
	}
	
	function confirm_reset_all() {
		if (confirm("<?php _e("This operation will delete all stored info from Wordpress Popular Posts Reloaded' data tables and cannot be undone.", "wordpress-popular-posts-reloaded"); ?> \n" + "<?php _e("Do you want to continue?", "wordpress-popular-posts-reloaded"); ?>")) {
			jQuery.post(ajaxurl, {action: 'wpp_clear_all', token: '<?php echo get_option("wpp_rand"); ?>', clear: 'all'}, function(data){
				alert(data);
			});
		}
	}
	
</script>

<div class="wrap">
    <div id="icon-options-general" class="icon32"><br /></div>
    <h2 id="wmpp-title">Wordpress Popular Posts Reloaded</h2>
    
    <ul class="subsubsub">
    	<li id="btn_stats"><a href="#" <?php if (!isset($_POST['section']) || (isset($_POST['section']) && $_POST['section'] == "stats") ) {?>class="current"<?php } ?> rel="wpp_stats"><?php _e("Stats", "wordpress-popular-posts-reloaded"); ?></a> |</li>
        <li id="btn_faq"><a href="#" rel="wpp_faq"><?php _e("FAQ", "wordpress-popular-posts-reloaded"); ?></a> |</li>
        <li id="btn_tools"><a href="#" rel="wpp_tools"<?php if (isset($_POST['section']) && ($_POST['section'] == "tools" || $_POST['section'] == "ajax" || $_POST['section'] == "css") ) {?> class="current"<?php } ?>><?php _e("Tools", "wordpress-popular-posts-reloaded"); ?></a></li>
    </ul>
    <!-- Start stats -->
    <div id="wpp_stats" class="wpp_boxes"<?php if (!isset($_POST['section']) || (isset($_POST['section']) && $_POST['section'] == "stats") ) {?> style="display:block;"<?php } ?>>
    	<p><?php _e("Click on each tab to see what are the most popular entries on your blog today, this week, last 30 days or all time since Wordpress Popular Posts Reloaded was installed.", "wordpress-popular-posts-reloaded"); ?></p>
        
        <div class="tablenav top">
            <div class="alignleft actions">
                <form action="" method="post" id="wpp_stats_options" name="wpp_stats_options">
                    <select name="stats_order">
                        <option <?php if ($ops['stats']['order_by'] == "comments") {?>selected="selected"<?php } ?> value="comments"><?php _e("Order by comments", "wordpress-popular-posts-reloaded"); ?></option>
                        <option <?php if ($ops['stats']['order_by'] == "views") {?>selected="selected"<?php } ?> value="views"><?php _e("Order by views", "wordpress-popular-posts-reloaded"); ?></option>
                        <option <?php if ($ops['stats']['order_by'] == "avg") {?>selected="selected"<?php } ?> value="avg"><?php _e("Order by avg. daily views", "wordpress-popular-posts-reloaded"); ?></option>
                    </select>
                    <label for="stats_limits"><?php _e("Limit", "wordpress-popular-posts-reloaded"); ?>:</label> <input type="text" name="stats_limit" value="<?php echo $ops['stats']['limit']; ?>" size="5" />
                    <input type="hidden" name="section" value="stats" />
                    <input type="submit" class="button-secondary action" value="<?php _e("Apply", "wordpress-popular-posts-reloaded"); ?>" name="" />
                </form>
            </div>
        </div>
        <br />
        <div id="wpp-stats-tabs">            
            <a href="#" class="button-primary" rel="wpp-daily"><?php _e("Last 24 hours", "wordpress-popular-posts-reloaded"); ?></a>
            <a href="#" class="button-secondary" rel="wpp-weekly"><?php _e("Last 7 days", "wordpress-popular-posts-reloaded"); ?></a>
            <a href="#" class="button-secondary" rel="wpp-monthly"><?php _e("Last 30 days", "wordpress-popular-posts-reloaded"); ?></a>
            <a href="#" class="button-secondary" rel="wpp-all"><?php _e("All-time", "wordpress-popular-posts-reloaded"); ?></a>
        </div>
        <div id="wpp-stats-canvas">            
            <div class="wpp-stats wpp-stats-active" id="wpp-daily">            	
                <?php echo do_shortcode("[wpp range='daily' stats_comments=1 stats_views=1 order_by='".$ops['stats']['order_by']."' wpp_start='<ol>' wpp_end='</ol>' do_pattern=1 pattern_form='{title} <span class=\"post-stats\">{stats}</span>' limit=".$ops['stats']['limit']."]"); ?>
            </div>
            <div class="wpp-stats" id="wpp-weekly">
                <?php echo do_shortcode("[wpp range='weekly' stats_comments=1 stats_views=1 order_by='".$ops['stats']['order_by']."' wpp_start='<ol>' wpp_end='</ol>' do_pattern=1 pattern_form='{title} <span class=\"post-stats\">{stats}</span>' limit=".$ops['stats']['limit']."]"); ?>
            </div>
            <div class="wpp-stats" id="wpp-monthly">
                <?php echo do_shortcode("[wpp range='monthly' stats_comments=1 stats_views=1 order_by='".$ops['stats']['order_by']."' wpp_start='<ol>' wpp_end='</ol>' do_pattern=1 pattern_form='{title} <span class=\"post-stats\">{stats}</span>' limit=".$ops['stats']['limit']."]"); ?>
            </div>
            <div class="wpp-stats" id="wpp-all">
                <?php echo do_shortcode("[wpp range='all' stats_views=1 order_by='".$ops['stats']['order_by']."' wpp_start='<ol>' wpp_end='</ol>' do_pattern=1 pattern_form='{title} <span class=\"post-stats\">{stats}</span>' limit=".$ops['stats']['limit']."]"); ?>
            </div>
        </div>
    </div>
    <!-- End stats -->
    
    <!-- Start faq -->
    <div id="wpp_faq" class="wpp_boxes">
    	<h3 class="wmpp-subtitle"><?php _e("Frequently Asked Questions", "wordpress-popular-posts-reloaded"); ?></h3>
    	<table cellspacing="0" class="wp-list-table widefat fixed posts">
            <tr>
                <td valign="top"><!-- help area -->
                	<h4>&raquo; <a href="#" rel="q-1"><?php _e('What does "Title" do?', 'wordpress-popular-posts-reloaded'); ?></a></h4>
                    <div class="wpp-ans" id="q-1">
                        <p><?php _e('It allows you to show a heading for your most popular posts listing. If left empty, no heading will be displayed at all.', 'wordpress-popular-posts-reloaded'); ?></p>
                    </div>
                    
                	<h4>&raquo; <a href="#" rel="q-2"><?php _e('What is Time Range for?', 'wordpress-popular-posts-reloaded'); ?></a></h4>
                    <div class="wpp-ans" id="q-2">
                        <p><?php _e('It will tell Wordpress Popular Posts Reloaded to retrieve all posts with most views / comments within the selected time range.', 'wordpress-popular-posts-reloaded'); ?></p>
                    </div>
                    
                    <h4>&raquo; <a href="#" rel="q-3"><?php _e('What is "Sort post by" for?', 'wordpress-popular-posts-reloaded'); ?></a></h4>
                    <div class="wpp-ans" id="q-3">
                        <p><?php _e('It allows you to decide whether to order your popular posts listing by total views, comments, or average views per day.', 'wordpress-popular-posts-reloaded'); ?></p>
                    </div>                    
                    
                    <h4>&raquo; <a href="#" rel="q-4"><?php _e('What does "Display post rating" do?', 'wordpress-popular-posts-reloaded'); ?></a></h4>
                    <div class="wpp-ans" id="q-4">
                        <p><?php _e('If checked, Wordpress Popular Posts Reloaded will show how your readers are rating your most popular posts. This feature requires having WP-PostRatings plugin installed and enabled on your blog for it to work.', 'wordpress-popular-posts-reloaded'); ?></p>
                    </div>
                    
                    <h4>&raquo; <a href="#" rel="q-5"><?php _e('What does "Shorten title" do?', 'wordpress-popular-posts-reloaded'); ?></a></h4>
                    <div class="wpp-ans" id="q-5">
                        <p><?php _e('If checked, all posts titles will be shortened to "n" characters. A new "Shorten title to" option will appear so you can set it to whatever you like.', 'wordpress-popular-posts-reloaded'); ?></p>
                    </div>
                    
                    <h4>&raquo; <a href="#" rel="q-6"><?php _e('What does "Display post excerpt" do?', 'wordpress-popular-posts-reloaded'); ?></a></h4>
                    <div class="wpp-ans" id="q-6">
                        <p><?php _e('If checked, Wordpress Popular Posts Reloaded will also include a small extract of your posts in the list. Similarly to the previous option, you will be able to decide how long the post excerpt should be.', 'wordpress-popular-posts-reloaded'); ?></p>
                    </div>
                    
                    <h4>&raquo; <a href="#" rel="q-7"><?php _e('What does "Keep text format and links" do?', 'wordpress-popular-posts-reloaded'); ?></a></h4>
                    <div class="wpp-ans" id="q-7">
                        <p><?php _e('If checked, and if the Post Excerpt feature is enabled, Wordpress Popular Posts Reloaded will keep the styling tags (eg. bold, italic, etc) that were found in the excerpt. Hyperlinks will remain intact, too.', 'wordpress-popular-posts-reloaded'); ?></p>
                    </div>
                    
                    <h4>&raquo; <a href="#" rel="q-7"><?php _e('What is "Post type" for?', 'wordpress-popular-posts-reloaded'); ?></a></h4>
                    <div class="wpp-ans" id="q-7">
                        <p><?php _e('This filter allows you to decide which post types to show on the listing. By default, it will retrieve only posts and pages (which should be fine for most cases).', 'wordpress-popular-posts-reloaded'); ?></p>
                    </div>
                    
                    <h4>&raquo; <a href="#" rel="q-8"><?php _e('What is "Category(ies) ID(s)" for?', 'wordpress-popular-posts-reloaded'); ?></a></h4>
                    <div class="wpp-ans" id="q-8">
                        <p><?php _e('This filter allows you to select which categories should be included or excluded from the listing. A negative sign in front of the category ID number will exclude posts belonging to it from the list, for example. You can specify more than one ID with a comma separated list.', 'wordpress-popular-posts-reloaded'); ?></p>
                    </div>
                    
                    <h4>&raquo; <a href="#" rel="q-9"><?php _e('What is "Author(s) ID(s)" for?', 'wordpress-popular-posts-reloaded'); ?></a></h4>
                    <div class="wpp-ans" id="q-9">
                        <p><?php _e('Just like the Category filter, this one lets you filter posts by author ID. You can specify more than one ID with a comma separated list.', 'wordpress-popular-posts-reloaded'); ?></p>
                    </div>
                    
                    <h4>&raquo; <a href="#" rel="q-10"><?php _e('What does "Display post thumbnail" do?', 'wordpress-popular-posts-reloaded'); ?></a></h4>
                    <div class="wpp-ans" id="q-10">
                        <p><?php _e('If checked, Wordpress Popular Posts Reloaded will attempt to retrieve the thumbnail of each post. You can set up the source of the thumbnail via Settings - Wordpress Popular Posts Reloaded - Tools.', 'wordpress-popular-posts-reloaded'); ?></p>
                    </div>
                    
                    <h4>&raquo; <a href="#" rel="q-11"><?php _e('What does "Display comment count" do?', 'wordpress-popular-posts-reloaded'); ?></a></h4>
                    <div class="wpp-ans" id="q-11">
                        <p><?php _e('If checked, Wordpress Popular Posts Reloaded will display how many comments each popular post has got in the selected Time Range.', 'wordpress-popular-posts-reloaded'); ?></p>
                    </div>
                    
                    <h4>&raquo; <a href="#" rel="q-12"><?php _e('What does "Display views" do?', 'wordpress-popular-posts-reloaded'); ?></a></h4>
                    <div class="wpp-ans" id="q-12">
                        <p><?php _e('If checked, Wordpress Popular Posts Reloaded will show how many pageviews a single post has gotten in the selected Time Range.', 'wordpress-popular-posts-reloaded'); ?></p>
                    </div>
                    
                    <h4>&raquo; <a href="#" rel="q-13"><?php _e('What does "Display author" do?', 'wordpress-popular-posts-reloaded'); ?></a></h4>
                    <div class="wpp-ans" id="q-13">
                        <p><?php _e('If checked, Wordpress Popular Posts Reloaded will display the name of the author of each entry listed.', 'wordpress-popular-posts-reloaded'); ?></p>
                    </div>
                    
                    <h4>&raquo; <a href="#" rel="q-14"><?php _e('What does "Display date" do?', 'wordpress-popular-posts-reloaded'); ?></a></h4>
                    <div class="wpp-ans" id="q-14">
                        <p><?php _e('If checked, Wordpress Popular Posts Reloaded will display the date when each popular posts was published.', 'wordpress-popular-posts-reloaded'); ?></p>
                    </div>
                    
                    <h4>&raquo; <a href="#" rel="q-15"><?php _e('What does "Use custom HTML Markup" do?', 'wordpress-popular-posts-reloaded'); ?></a></h4>
                    <div class="wpp-ans" id="q-15">
                        <p><?php _e('If checked, you will be able to customize the HTML markup of your popular posts listing. For example, you can decide whether to wrap your posts in an unordered list, an ordered list, a div, etc. If you know xHTML/CSS, this is for you!', 'wordpress-popular-posts-reloaded'); ?></p>
                    </div>
                    
                    <h4>&raquo; <a href="#" rel="q-16"><?php _e('What does "Use content formatting tags" do?', 'wordpress-popular-posts-reloaded'); ?></a></h4>
                    <div class="wpp-ans" id="q-16">
                        <p><?php _e('If checked, you can decide the order of the items displayed on each entry. For example, setting it to "{title}: {summary}" (without the quotes) would display "Post title: excerpt of the post here". Available tags: {image}, {title}, {summary}, {stats} and {rating}.', 'wordpress-popular-posts-reloaded'); ?></p>
                    </div>
                    
                    <h4>&raquo; <a href="#" rel="q-17"><?php _e('What are "Template Tags"?', 'wordpress-popular-posts-reloaded'); ?></a></h4>
                    <div class="wpp-ans" id="q-17">
                        <p><?php _e('Template Tags are simply php functions that allow you to perform certain actions. For example, Wordpress Popular Posts Reloaded currently supports two different template tags: wpp_get_mostpopular() and wpp_get_views().', 'wordpress-popular-posts-reloaded'); ?></p>
                    </div>
                    
                    <h4>&raquo; <a href="#" rel="q-18"><?php _e('What are the template tags that Wordpress Popular Posts Reloaded supports?', 'wordpress-popular-posts-reloaded'); ?></a></h4>
                    <div class="wpp-ans" id="q-18">
                        <p><?php _e('The following are the template tags supported by Wordpress Popular Posts Reloaded', 'wordpress-popular-posts-reloaded'); ?>:</p>
                        <table cellspacing="0" class="wp-list-table widefat fixed posts">
                        	<thead>
                                <tr>
                                    <th class="manage-column column-title"><?php _e('Template tag', 'wordpress-popular-posts-reloaded'); ?></th>
                                    <th class="manage-column column-title"><?php _e('What it does ', 'wordpress-popular-posts-reloaded'); ?></th>
                                    <th class="manage-column column-title"><?php _e('Parameters', 'wordpress-popular-posts-reloaded'); ?></th>
                                    <th class="manage-column column-title"><?php _e('Example', 'wordpress-popular-posts-reloaded'); ?></th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr>
                                    <td class="post type-post status-draft format-standard hentry category-js alternate iedit"><strong>wpp_get_mostpopular()</strong></td>
                                    <td class="post type-post status-draft format-standard hentry category-js iedit"><?php _e('Similar to the widget functionality, this tag retrieves the most popular posts on your blog. This function also accepts parameters so you can customize your popular listing, but these are not required.', 'wordpress-popular-posts-reloaded'); ?></td>
                                    <td class="post type-post status-draft format-standard hentry category-js alternate iedit"><?php _e('Please refer to "List of parameters accepted by wpp_get_mostpopular() and the [wpp] shortcode".', 'wordpress-popular-posts-reloaded'); ?></td>
                                    <td class="post type-post status-draft format-standard hentry category-js iedit">&lt;?php wpp_get_mostpopular(); ?&gt;<br />&lt;?php wpp_get_mostpopular("range=weekly&amp;limit=7"); ?&gt;</td>
                                </tr>
                                <tr>
                                    <td><strong>wpp_get_views()</strong></td>
                                    <td><?php _e('Displays the number of views of a single post. Post ID is required or it will return false.', 'wordpress-popular-posts-reloaded'); ?></td>
                                    <td><?php _e('Post ID', 'wordpress-popular-posts-reloaded'); ?></td>
                                    <td>&lt;?php echo wpp_get_views($post->ID); ?&gt;<br />&lt;?php echo wpp_get_views(15); ?&gt;</td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                    
                    <h4>&raquo; <a href="#" rel="q-19"><?php _e('What are "shortcodes"?', 'wordpress-popular-posts-reloaded'); ?></a></h4>
                    <div class="wpp-ans" id="q-19">
                        <p><?php _e('Shortcodes are similar to BB Codes, these allow us to call a php function by simply typing something like [shortcode]. With Wordpress Popular Posts Reloaded, the shortcode [wpp] will let you insert a list of the most popular posts in posts content and pages too! For more information about shortcodes, please visit', 'wordpress-popular-posts-reloaded', 'wordpress-popular-posts-reloaded'); ?> <a href="http://codex.wordpress.org/Shortcode_API" target="_blank">Wordpress Shortcode API</a>.</p>
                    </div>
                    <h4>&raquo; <a href="#" rel="q-20"><?php _e('List of parameters accepted by wpp_get_mostpopular() and the [wpp] shortcode', 'wordpress-popular-posts-reloaded'); ?></a></h4>
                    <div class="wpp-ans" id="q-20" style="display:block;">
                        <p><?php _e('These parameters can be used by both the template tag wpp_get_most_popular() and the shortcode [wpp].', 'wordpress-popular-posts-reloaded'); ?>:</p>
                        <table cellspacing="0" class="wp-list-table widefat fixed posts">
                        	<thead>
                                <tr>
                                    <th class="manage-column column-title"><?php _e('Parameter', 'wordpress-popular-posts-reloaded'); ?></th>
                                    <th class="manage-column column-title"><?php _e('What it does ', 'wordpress-popular-posts-reloaded'); ?></th>
                                    <th class="manage-column column-title"><?php _e('Possible values', 'wordpress-popular-posts-reloaded'); ?></th>
                                    <th class="manage-column column-title"><?php _e('Defaults to', 'wordpress-popular-posts-reloaded'); ?></th>
                                    <th class="manage-column column-title"><?php _e('Example', 'wordpress-popular-posts-reloaded'); ?></th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr>
                                    <td><strong>header</strong></td>
                                    <td><?php _e('Sets a heading for the list', 'wordpress-popular-posts-reloaded'); ?></td>
                                    <td><?php _e('Text string', 'wordpress-popular-posts-reloaded'); ?></td>
                                    <td><?php _e('Popular Posts', 'wordpress-popular-posts-reloaded'); ?></td>
                                    <td>header="Popular Posts"</td>
                                </tr>
                                <tr>
                                    <td><strong>header_start</strong></td>
                                    <td><?php _e('Set the opening tag for the heading of the list', 'wordpress-popular-posts-reloaded'); ?></td>
                                    <td><?php _e('Text string', 'wordpress-popular-posts-reloaded'); ?></td>
                                    <td>&lt;h2&gt;</td>
                                    <td>header_start="&lt;h2&gt;"</td>
                                </tr>
                                <tr>
                                    <td><strong>header_end</strong></td>
                                    <td><?php _e('Set the closing tag for the heading of the list', 'wordpress-popular-posts-reloaded'); ?></td>
                                    <td><?php _e('Text string', 'wordpress-popular-posts-reloaded'); ?></td>
                                    <td>&lt;/h2&gt;</td>
                                    <td>header_end="&lt;/h2&gt;"</td>
                                </tr>
                                <tr>
                                    <td><strong>limit</strong></td>
                                    <td><?php _e('Sets the maximum number of popular posts to be shown on the listing', 'wordpress-popular-posts-reloaded'); ?></td>
                                    <td><?php _e('Positive integer', 'wordpress-popular-posts-reloaded'); ?></td>
                                    <td>10</td>
                                    <td>limit=10</td>
                                </tr>
                                <tr>
                                    <td><strong>range</strong></td>
                                    <td><?php _e('Tells Wordpress Popular Posts Reloaded to retrieve the most popular entries within the time range specified by you', 'wordpress-popular-posts-reloaded'); ?></td>
                                    <td>"daily", "weekly", "monthly", "all"</td>
                                    <td>daily</td>
                                    <td>range="daily"</td>
                                </tr>
                                <tr>
                                    <td><strong>order_by</strong></td>
                                    <td><?php _e('Sets the sorting option of the popular posts', 'wordpress-popular-posts-reloaded'); ?></td>
                                    <td>"comments", "views", "avg" <?php _e('(for average views per day)', 'wordpress-popular-posts-reloaded'); ?></td>
                                    <td>comments</td>
                                    <td>order_by="comments"</td>
                                </tr>
                                <tr>
                                    <td><strong>post_type</strong></td>
                                    <td><?php _e('Defines the type of posts to show on the listing', 'wordpress-popular-posts-reloaded'); ?></td>
                                    <td><?php _e('Text string', 'wordpress-popular-posts-reloaded'); ?></td>
                                    <td>post,page</td>
                                    <td>post_type=post,page,your-custom-post-type</td>
                                </tr>
                                <tr>
                                    <td><strong>cat</strong></td>
                                    <td><?php _e('If set, Wordpress Popular Posts Reloaded will retrieve all entries that belong to the specified category(ies) ID(s). If a minus sign is used, the category(ies) will be excluded instead.', 'wordpress-popular-posts-reloaded'); ?></td>
                                    <td><?php _e('Text string', 'wordpress-popular-posts-reloaded'); ?></td>
                                    <td><?php _e('None', 'wordpress-popular-posts-reloaded'); ?></td>
                                    <td>cat="1,55,-74"</td>
                                </tr>
                                <tr>
                                    <td><strong>author</strong></td>
                                    <td><?php _e('If set, Wordpress Popular Posts Reloaded will retrieve all entries created by specified author(s) ID(s).', 'wordpress-popular-posts-reloaded'); ?></td>
                                    <td><?php _e('Text string', 'wordpress-popular-posts-reloaded'); ?></td>
                                    <td><?php _e('None', 'wordpress-popular-posts-reloaded'); ?></td>
                                    <td>author="75,8,120"</td>
                                </tr>
                                <tr>
                                    <td><strong>title_length</strong></td>
                                    <td><?php _e('If set, Wordpress Popular Posts Reloaded will shorten each post title to "n" characters whenever possible', 'wordpress-popular-posts-reloaded'); ?></td>
                                    <td><?php _e('Positive integer', 'wordpress-popular-posts-reloaded'); ?></td>
                                    <td>25</td>
                                    <td>title_length=25</td>
                                </tr>
                                <tr>
                                    <td><strong>excerpt_length</strong></td>
                                    <td><?php _e('If set, Wordpress Popular Posts Reloaded will build and include an excerpt of "n" characters long from the content of each post listed as popular', 'wordpress-popular-posts-reloaded'); ?></td>
                                    <td><?php _e('Positive integer', 'wordpress-popular-posts-reloaded'); ?></td>
                                    <td>55</td>
                                    <td>excerpt_length=55</td>
                                </tr>
                                <tr>
                                    <td><strong>excerpt_format</strong></td>
                                    <td><?php _e('If set, Wordpress Popular Posts Reloaded will maintaing all styling tags (strong, italic, etc) and hyperlinks found in the excerpt', 'wordpress-popular-posts-reloaded'); ?></td>
                                    <td>1 (true), (0) false</td>
                                    <td>0</td>
                                    <td>excerpt_format=1</td>
                                </tr>                                
                                <tr>
                                    <td><strong>thumbnail_width</strong></td>
                                    <td><?php _e('If set, and if your current server configuration allows it, you will be able to display thumbnails of your posts. This attribute sets the width for thumbnails', 'wordpress-popular-posts-reloaded'); ?></td>
                                    <td><?php _e('Positive integer', 'wordpress-popular-posts-reloaded'); ?></td>
                                    <td>15</td>
                                    <td>thumbnail_width=30</td>
                                </tr>
                                <tr>
                                    <td><strong>thumbnail_height</strong></td>
                                    <td><?php _e('If set, and if your current server configuration allows it, you will be able to display thumbnails of your posts. This attribute sets the height for thumbnails', 'wordpress-popular-posts-reloaded'); ?></td>
                                    <td><?php _e('Positive integer', 'wordpress-popular-posts-reloaded'); ?></td>
                                    <td>15</td>
                                    <td>thumbnail_height=30</td>
                                </tr>
                                <tr>
                                    <td><strong>rating</strong></td>
                                    <td><?php _e('If set, and if the WP-PostRatings plugin is installed and enabled on your blog, Wordpress Popular Posts Reloaded will show how your visitors are rating your entries', 'wordpress-popular-posts-reloaded'); ?></td>
                                    <td>1 (true), (0) false</td>
                                    <td>0</td>
                                    <td>rating=1</td>
                                </tr>
                                <tr>
                                    <td><strong>stats_comments</strong></td>
                                    <td><?php _e('If set, Wordpress Popular Posts Reloaded will show how many comments each popular post has got until now', 'wordpress-popular-posts-reloaded'); ?></td>
                                    <td>1 (true), 0 (false)</td>
                                    <td>1</td>
                                    <td>stats_comments=1</td>
                                </tr>
                                <tr>
                                    <td><strong>stats_views</strong></td>
                                    <td><?php _e('If set, Wordpress Popular Posts Reloaded will show how many views each popular post has got since it was installed', 'wordpress-popular-posts-reloaded'); ?></td>
                                    <td>1 (true), (0) false</td>
                                    <td>0</td>
                                    <td>stats_views=1</td>
                                </tr>
                                <tr>
                                    <td><strong>stats_author</strong></td>
                                    <td><?php _e('If set, Wordpress Popular Posts Reloaded will show who published each popular post on the list', 'wordpress-popular-posts-reloaded'); ?></td>
                                    <td>1 (true), (0) false</td>
                                    <td>0</td>
                                    <td>stats_author=1</td>
                                </tr>
                                <tr>
                                    <td><strong>stats_date</strong></td>
                                    <td><?php _e('If set, Wordpress Popular Posts Reloaded will display the date when each popular post on the list was published', 'wordpress-popular-posts-reloaded'); ?></td>
                                    <td>1 (true), (0) false</td>
                                    <td>0</td>
                                    <td>stats_date=1</td>
                                </tr>
                                <tr>
                                    <td><strong>stats_date_format</strong></td>
                                    <td><?php _e('Sets the date format', 'wordpress-popular-posts-reloaded'); ?></td>
                                    <td><?php _e('Text string', 'wordpress-popular-posts-reloaded'); ?></td>
                                    <td>0</td>
                                    <td>stats_date_format='F j, Y'</td>
                                </tr>
                                <tr>
                                    <td><strong>wpp_start</strong></td>
                                    <td><?php _e('Sets the opening tag for the listing', 'wordpress-popular-posts-reloaded'); ?></td>
                                    <td><?php _e('Text string', 'wordpress-popular-posts-reloaded'); ?></td>
                                    <td>&lt;ul&gt;</td>
                                    <td>wpp_start="&lt;ul&gt;"</td>
                                </tr>
                                <tr>
                                    <td><strong>wpp_end</strong></td>
                                    <td><?php _e('Sets the closing tag for the listing', 'wordpress-popular-posts-reloaded'); ?></td>
                                    <td><?php _e('Text string', 'wordpress-popular-posts-reloaded'); ?></td>
                                    <td>&lt;/ul&gt;</td>
                                    <td>wpp_end="&lt;/ul&gt;"</td>
                                </tr>
                                <tr>
                                    <td><strong>post_start</strong></td>
                                    <td><?php _e('Sets the opening tag for each item on the list', 'wordpress-popular-posts-reloaded'); ?></td>
                                    <td><?php _e('Text string', 'wordpress-popular-posts-reloaded'); ?></td>
                                    <td>&lt;li&gt;</td>
                                    <td>post_start="&lt;li&gt;"</td>
                                </tr>
                                <tr>
                                    <td><strong>post_end</strong></td>
                                    <td><?php _e('Sets the closing tag for each item on the list', 'wordpress-popular-posts-reloaded'); ?></td>
                                    <td><?php _e('Text string', 'wordpress-popular-posts-reloaded'); ?></td>
                                    <td>&lt;/li&gt;</td>
                                    <td>post_end="&lt;/li&gt;"</td>
                                </tr>                        
                                <tr>
                                    <td><strong>do_pattern</strong></td>
                                    <td><?php _e('If set, this option will allow you to decide the order of the contents within each item on the list.', 'wordpress-popular-posts-reloaded'); ?></td>
                                    <td>1 (true), (0) false</td>
                                    <td>0</td>
                                    <td>do_pattern=1</td>
                                </tr>
                                <tr>
                                    <td><strong>pattern_form</strong></td>
                                    <td><?php _e('If set, you can decide the order of each content inside a single item on the list. For example, setting it to "{title}: {summary}" would output something like "Your Post Title: summary here". This attribute requires do_pattern to be true.', 'wordpress-popular-posts-reloaded'); ?></td>
                                    <td><?php _e('Available tags', 'wordpress-popular-posts-reloaded'); ?>: {image}, {title}, {summary}, {stats}, {rating}</td>
                                    <td>{image} {title}: {summary} {stats}</td>
                                    <td>pattern_form="{image} {title}: {summary} {stats}"</td>
                                </tr>
							</tbody>
                        </table>
                    </div>
                </td>
            </tr>
        </table>
    </div>
    <!-- End faq -->
    
    <!-- Start tools -->
    <div id="wpp_tools" class="wpp_boxes"<?php if (isset($_POST['section']) && ($_POST['section'] == "tools" || $_POST['section'] == "ajax" || $_POST['section'] == "css") ) {?> style="display:block;"<?php } ?>>
    	<p><?php _e("Here you will find a handy group of options to tweak Wordpress Popular Posts Reloaded.", "wordpress-popular-posts-reloaded"); ?></p><br />
                       
        <h3 class="wmpp-subtitle"><?php _e("Thumbnail source", "wordpress-popular-posts-reloaded"); ?></h3>
        <p><?php _e("Tell Wordpress Popular Posts Reloaded where it should get thumbnails from", "wordpress-popular-posts-reloaded"); ?>:</p>
        <div class="tablenav top">
        	<div class="alignleft actions">
                <form action="" method="post" id="wpp_thumbnail_options" name="wpp_thumbnail_options">
                    <select name="thumb_source" id="thumb_source">
                        <option <?php if ($ops['tools']['thumbnail']['source'] == "featured") {?>selected="selected"<?php } ?> value="featured"><?php _e("Featured image", "wordpress-popular-posts-reloaded"); ?></option>
                        <option <?php if ($ops['tools']['thumbnail']['source'] == "first_image") {?>selected="selected"<?php } ?> value="first_image"><?php _e("First image on post", "wordpress-popular-posts-reloaded"); ?></option>
                        <option <?php if ($ops['tools']['thumbnail']['source'] == "custom_field") {?>selected="selected"<?php } ?> value="custom_field"><?php _e("Custom field", "wordpress-popular-posts-reloaded"); ?></option>
                    </select>

                    <label for="thumb_field" id="lbl_field" <?php if ($ops['tools']['thumbnail']['source'] != "custom_field") {?>style="display:none;"<?php } ?>><?php _e("Custom field name", "wordpress-popular-posts-reloaded"); ?>:</label>
                    <input type="text" id="thumb_field" name="thumb_field" value="<?php echo $ops['tools']['thumbnail']['field']; ?>" size="10" <?php if ($ops['tools']['thumbnail']['source'] != "custom_field") {?>style="display:none;"<?php } ?> />
                    <input type="hidden" name="section" value="tools" />
                    <input type="submit" class="button-secondary action" id="btn_th_ops" value="<?php _e("Apply", "wordpress-popular-posts-reloaded"); ?>" name="" />
                </form>                
            </div>
        </div>
        <br />
        
        <h3 class="wmpp-subtitle"><?php _e("Wordpress Popular Posts Reloaded Stylesheet", "wordpress-popular-posts-reloaded"); ?></h3>
        <p><?php _e("By default, the plugin includes a stylesheet called wpp.css which you can use to style your popular posts listing. If you wish to use your own stylesheet or do not want it to have it included in the header section of your site, use this.", "wordpress-popular-posts-reloaded"); ?></p>
        <div class="tablenav top">
        	<div class="alignleft actions">
                <form action="" method="post" id="wpp_css_options" name="wpp_css_options">
                    <select name="css" id="css">
                        <option <?php if ($ops['tools']['css']) {?>selected="selected"<?php } ?> value="1"><?php _e("Enabled", "wordpress-popular-posts-reloaded"); ?></option>
                        <option <?php if (!$ops['tools']['css']) {?>selected="selected"<?php } ?> value="0"><?php _e("Disabled", "wordpress-popular-posts-reloaded"); ?></option>
                    </select>
                    <input type="hidden" name="section" value="css" />
                    <input type="submit" class="button-secondary action" id="btn_css_ops" value="<?php _e("Apply", "wordpress-popular-posts-reloaded"); ?>" name="" />
                </form>                
            </div>
        </div>
        <br />
        
        <h3 class="wmpp-subtitle"><?php _e("Data tools", "wordpress-popular-posts-reloaded"); ?></h3>
                
        <p><?php _e("AJAX update. If you are using a caching plugin such as WP Super Cache, enabling this feature will keep the popular list from being cached.", "wordpress-popular-posts-reloaded"); ?> (NOT AVAILABLE)</p>
        <div class="tablenav top">
        	<div class="alignleft actions">
                <form action="" method="post" id="wpp_ajax_options" name="wpp_ajax_options">
                    <select name="ajax" id="ajax" disabled="disabled">
                        <option <?php if ($ops['tools']['ajax']) {?>selected="selected"<?php } ?> value="1"><?php _e("Enabled", "wordpress-popular-posts-reloaded"); ?></option>
                        <option <?php if (!$ops['tools']['ajax']) {?>selected="selected"<?php } ?> value="0"><?php _e("Disabled", "wordpress-popular-posts-reloaded"); ?></option>
                    </select>
                    <input type="hidden" name="section" value="ajax" />
                    <input type="submit" class="button-secondary action" id="btn_ajax_ops" value="<?php _e("Apply", "wordpress-popular-posts-reloaded"); ?>" name="" />
                </form>                
            </div>
        </div>
        <br />
        
        <p><?php _e('Wordpress Popular Posts Reloaded keeps historical data of your most popular entries for up to 30 days. If for some reason you need to clear the cache table, or even both historical and cache tables, please use the buttons below to do so.', 'wordpress-popular-posts-reloaded') ?></p>
        <p><input type="button" name="wpp-reset-cache" id="wpp-reset-cache" class="button-secondary" value="<?php _e("Empty cache", "wordpress-popular-posts-reloaded"); ?>" onclick="confirm_reset_cache()" /> <label for="wpp-reset-cache"><small><?php _e('Use this button to manually clear entries from WPP cache only', 'wordpress-popular-posts-reloaded'); ?></small></label></p>
        <p><input type="button" name="wpp-reset-all" id="wpp-reset-all" class="button-secondary" value="<?php _e("Clear all data", "wordpress-popular-posts-reloaded"); ?>" onclick="confirm_reset_all()" /> <label for="wpp-reset-all"><small><?php _e('Use this button to manually clear entries from all WPP data tables', 'wordpress-popular-posts-reloaded'); ?></small></label></p>
    </div>
    <!-- End tools -->
    
    <br />
    <hr />
<?php /*?>    <p><?php _e('Do you like this plugin?', 'wordpress-popular-posts-reloaded'); ?> <a title="<?php _e('Rate Wordpress Popular Posts Reloaded!', 'wordpress-popular-posts-reloaded'); ?>" href="http://wordpress.org/extend/plugins/wordpress-popular-posts-reloaded/#rate-response" target="_blank"><strong><?php _e('Rate it', 'wordpress-popular-posts-reloaded'); ?></strong></a> <?php _e('on the official Plugin Directory!', 'wordpress-popular-posts-reloaded'); ?></p><?php */?>
    <p><?php _e('Do you love this plugin?', 'wordpress-popular-posts-reloaded'); ?> <a title="<?php _e('Buy me a beer!', 'wordpress-popular-posts-reloaded'); ?>" href="https://www.paypal.com/cgi-bin/webscr?cmd=_donations&business=QXZZJ83F6PBDQ&lc=SE&item_name=WordPress%20Popular%20Posts%20Reloaded&item_number=wppr&currency_code=USD&bn=PP%2dDonationsBF%3abtn_donateCC_LG%2egif%3aNonHosted" target="_blank"><strong><?php _e('Buy me a beer!', 'wordpress-popular-posts-reloaded'); ?></strong></a>. <?php _e('Each donation motivates me to keep releasing free stuff for the Wordpress community!', 'wordpress-popular-posts-reloaded'); ?></p>
</div>