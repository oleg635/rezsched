<?php
require($_SERVER['DOCUMENT_ROOT'] . '/wp-config.php');
$success = TRUE;

$log_filename = $_SERVER['DOCUMENT_ROOT'] . '/wp-content/themes/greenshift-child/delete_bi_weekly.txt';

function delete_bi_weekly( $parent_post_id ) {
	$posts_to_delete = array();
    // Fetch the parent post's start date
    $start_date = get_post_meta( $parent_post_id, 'etn_start_date', true );
    $end_date = get_post_meta( $parent_post_id, 'etn_end_date', true );
	
    // Find the first Sunday after the start date (by adding days to get to the next Sunday)
    $day_of_week = date( 'w', strtotime( $start_date ) ); // 0 (for Sunday) through 6 (for Saturday)
    $days_to_sunday = (7 - $day_of_week) % 7;
    $first_sunday_date = date( 'Y-m-d', strtotime( "+$days_to_sunday days", strtotime( $start_date ) ) );
	
    // Retrieve all child posts
    $child_posts = get_posts( array(
        'post_type'   => 'etn',
        'post_parent' => $parent_post_id,
        'post_status' => 'publish',
        'posts_per_page' => -1, // Get all child posts
        'fields' => 'ids',
		'orderby'    => 'ID',
		'sort_order' => 'DESC',
    ));
	$i = 0;
	$non_legal_week_start = $first_sunday_date;
	
	vd($non_legal_week_start, '$non_legal_week_start');
	vd($end_date, '$end_date');
	//return FALSE;
    // Iterate through non-legal week timeframes and delete posts
    while($non_legal_week_start < $end_date)
	{
        // Define the non-legal week timeframe (Sunday to Saturday)
        $non_legal_week_start = $first_sunday_date;
        $non_legal_week_end = date( 'Y-m-d', strtotime( '+6 days', strtotime( $non_legal_week_start ) ) );
		$i++;
		echo '$i ' . $i  . ' $non_legal_week_start <strong>' . $non_legal_week_start  . '</strong> $non_legal_week_end <strong>' . $non_legal_week_end . '</strong><br>';
        // Iterate over child posts and delete those within the non-legal week
        foreach ( $child_posts as $key => $child_post_id ) {
            $child_start_date = get_post_meta( $child_post_id, 'etn_start_date', true );
			
			echo '<br>' . $child_post_id . ' <strong>' . $child_start_date . '</strong><br>';
			
            if ( $child_start_date >= $non_legal_week_start && $child_start_date <= $non_legal_week_end ) {
                //wp_delete_post( $child_post_id, true );
				$posts_to_delete[] = $child_post_id;
				unset( $child_posts[ $key ] );
            }
        }
		
        // Move to the next non-legal week (add two weeks to the Sunday date)
        $first_sunday_date = date( 'Y-m-d', strtotime( '+2 weeks', strtotime( $first_sunday_date ) ) );
		
		if($i > 100)
		{
			echo '<br>endless loop!<br>';
			break;
		}
    }
	
	//vd($child_posts, '$child_posts');
	echo '<strong>$child_posts</strong><br>';
	foreach($child_posts as $child_post)
	{
	    $start_date = get_post_meta( $child_post, 'etn_start_date', true );
	    $end_date = get_post_meta( $child_post, 'etn_end_date', true );
		echo $child_post . ' <strong>' . $start_date . '</strong> ' . ' <strong>' . $end_date . '</strong><br>';
	}
	
	echo '<strong>$posts_to_delete</strong><br>';
	foreach($posts_to_delete as $child_post)
	{
	    $start_date = get_post_meta( $child_post, 'etn_start_date', true );
	    $end_date = get_post_meta( $child_post, 'etn_end_date', true );
		echo $child_post . ' <strong>' . $start_date . '</strong> ' . ' <strong>' . $end_date . '</strong><br>';
	}
	
	return $posts_to_delete;
}

$posts_to_delete = delete_bi_weekly( 3605 );
vd($posts_to_delete, '$posts_to_delete');
?>