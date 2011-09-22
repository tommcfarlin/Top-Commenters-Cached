<?php if(strlen(trim($widget_title)) > 0) { ?>
	<h3 class="widget-title">
		<?php echo $widget_title; ?>
	</h3>
<?php
	} // end if
		
	global $wpdb;
	$comment_list = '<ol>';
	foreach($commenters as $commenter) {
		
		$comment_list .= '<li>';
			
			// actually print the commenter's name and the number of comments
			$comment_list .= $commenter->comment_author;
			$comment_list .= ' (' . $commenter->comments_count . ')';

		$comment_list .= '</li>';
		
	} // end foreach
	$comment_list .= '</ol>';
	
	echo $comment_list;
?>