<?php
//-----------------------------------------------------------------------------
/*
Plugin Name: PostLists-Extension Repeat
Version: 1.0.1
Plugin URI: http://www.rene-ade.de/inhalte/wordpress-plugin-postlists-extension-repeat.html
Description: PostLists Extension Repeat can be used to repeat PostLists-lists for each tag, category, author or parent
Author: Ren&eacute; Ade
Author URI: http://www.rene-ade.de
*/
//-----------------------------------------------------------------------------
?>
<?php

//-----------------------------------------------------------------------------

// is repeat list  
function ple_repeat_isrepeatlist( $args ) {

  // check if is repeate list
  if( array_key_exists('ple_repeat_repeat',$args) && !empty($args['ple_repeat_repeat']) )
    return true;

  return false;
}

//-----------------------------------------------------------------------------

// native getlist
function ple_repeat_getlist( $retval, $args ) {

  // check if is repeat list  
  if( ple_repeat_isrepeatlist($args) )
    return false; // no native getlist
    
  return $retval;
}

// get lists
function ple_repeat_list( $output, $args ) {
  
  // if is repeat list
  if( ple_repeat_isrepeatlist($args) && !$output ) {
  
    // create output
    $output = array();
    
    // what to repeat
    $repeat = $args['ple_repeat_repeat'];
    
    // get conditions list
    $conditions = ple_repeat_conditions( $repeat );
    
    // get sublists
    $unique = 0;
    foreach( $conditions as $condition=>$value ) {
  
      // args
      $subargs = $args;
    
      // remove repeate for sublists args
      $subargs['ple_repeat_repeat'] = null;
      $subargs['ple_repeat_repeated'] = $repeat;
      
      // add value for placeholders
      $subargs['ple_repeat_value'] = $value;
      
      // add condition
      if( !empty($subargs['where']) )
        $condition.= ' AND ('.$subargs['where'].')';
      $subargs['where'] = $condition;
      
      // load tables if needed
      if(   strpos($condition,'ple_repeat_term_relationships')!==false 
         || strpos($condition,'ple_repeat_term_taxonomy')!==false ) {
        global $wpdb;
        $load = "$wpdb->term_relationships as ple_repeat_term_relationships, "
               ."$wpdb->term_taxonomy as ple_repeat_term_taxonomy";
        if( !empty($subargs['load']) )
          $load.= ', '.$subargs['load'];
        $subargs['load'] = $load;
      }
      
      // catch posts if orderby
      if( array_key_exists('ple_repeat_orderby',$args) && strlen($args['ple_repeat_orderby'])>0 ) {
        global $ple_repeat_list_posts_posts; 
          $ple_repeat_list_posts_posts = array();
        global $ple_repeat_list_posts_args; 
          $ple_repeat_list_posts_args = $subargs;
        if( !function_exists('ple_repeat_list_posts') ) {
          function ple_repeat_list_posts( $posts, $args ) {
            global $ple_repeat_list_posts_posts;   
              $ple_repeat_list_posts_posts = array_merge( $ple_repeat_list_posts_posts, $posts );
            global $ple_repeat_list_posts_args;    
              $ple_repeat_list_posts_args = array_merge( $ple_repeat_list_posts_args, $args );                            
            return $posts;
          }
        }
        add_filter( 'ple_posts', 'ple_repeat_list_posts', 9, 2 );
      }
      
      // get sub list 
      $list = pl_getlist( $subargs );

      // order by
      $key = $unique;
      if( array_key_exists('ple_repeat_orderby',$args) && strlen($args['ple_repeat_orderby'])>0 ) {
        global $ple_repeat_list_posts_posts;   
        global $ple_repeat_list_posts_args;             
        remove_filter( 'ple_posts', 'ple_repeat_list_posts' );
        $key = pl_getplaceholdervalue( $args['ple_repeat_orderby'], $ple_repeat_list_posts_args, $ple_repeat_list_posts_posts, null ).$unique;
      }
      
      // add to output
      $output[ $key ] = $list;
      
      // unique
      $unique++;
    }
    
    // order
    if( array_key_exists('ple_repeat_orderby',$args) && strlen($args['ple_repeat_orderby'])>0 )
      ksort( $output );
    if( array_key_exists('ple_repeat_order',$args) && strlen($args['ple_repeat_order'])>0 ) {
      if( $args['ple_repeat_order']=='DESC' )
        $output = array_reverse( $output );
    }
    
    // return output array as string
    return implode( $output );
  }
  
  // return output
  return $output;
}

//-----------------------------------------------------------------------------

// get conditions
function ple_repeat_conditions( $repeat ) {
  global $wpdb;

  // conditions
  $conditions = array();

  // get conditions
  switch( $repeat ) {
    case 'tags':
      $tags = get_tags();
      foreach( $tags as $tag ) {
        $condition = "$wpdb->posts.ID=ple_repeat_term_relationships.object_id "
                    ."AND ple_repeat_term_relationships.term_taxonomy_id=ple_repeat_term_taxonomy.term_taxonomy_id "
                    ."AND ple_repeat_term_taxonomy.term_id=$tag->term_id";
        $conditions[ $condition ] = $tag->term_id;
      }
      break;
    case 'categories':
      $categories = get_categories();
      foreach( $categories as $category ) {
        $condition = "$wpdb->posts.ID=ple_repeat_term_relationships.object_id "
                    ."AND ple_repeat_term_relationships.term_taxonomy_id=ple_repeat_term_taxonomy.term_taxonomy_id "
                    ."AND ple_repeat_term_taxonomy.term_id=$category->term_id";
        $conditions[ $condition ] = $category->term_id;
      }
      break;
    case 'authors':
      $authorids = $wpdb->get_col( "SELECT post_author FROM $wpdb->posts GROUP BY post_author" ); // all known authors
      foreach( $authorids as $authorid ) {
        if( $authorid==null )
          continue;
        $condition = "$wpdb->posts.post_author=$authorid";
        $conditions[ $condition ] = $authorid;
      }
      break;
    case 'parents':
      $parents = $wpdb->get_col( "SELECT post_parent FROM $wpdb->posts GROUP BY post_parent" ); // all known parents
      foreach( $parents as $parent ) {
        if( $parent==null || $parent==0 )
          continue;
        $condition = "$wpdb->posts.post_parent='$parent'";
        $conditions[ $condition ] = $parent;
      }
      break;
  }
  
  // unknown
  return $conditions;
}

//-----------------------------------------------------------------------------

// correct placeholders
function ple_repeat_placeholdervalue( $value, $name, $args, $posts, $post ) {

  // if not repeatlist or value not set nothing to correct
  if( !array_key_exists('ple_repeat_value',$args) || empty($args['ple_repeat_value']) )
    return $value;

  // fix placeholders
  switch( $args['ple_repeat_repeated'] ) {
    case 'categories':
      $args['category'] = $args['ple_repeat_value'];
      break;
    case 'tags':
      $args['tag'] = $args['ple_repeat_value'];
      break;
    case 'authors':
      $args['author'] = $args['ple_repeat_value'];
      break;
    case 'parents':
      $args['post_parent'] = $args['ple_repeat_value'];
      break;
  }
  
  // remove repeat value for retry
  $args['ple_repeat_value'] = null;
  
  // retry with value
  return pl_getplaceholdervalue( $name, $args, $posts, $post ); 
}

//-----------------------------------------------------------------------------

function ple_repeat_fields( $fields ) {

  // placeholder type
  $placeholders = pl_getsupportedplaceholders( false, false, false );
  $placeholders_type = array( ''=>'' );
  foreach( $placeholders as $placeholder )
    $placeholders_type[ '%'.$placeholder.'%' ] = $placeholder;

  // add admin fields
  $fields['ple_repeat_repeat'] = array(
    'description'=>'Repeat this list for each',
    'type'=>array( ''=>'',
      'Category'=>'categories', 
      'Tag'=>'tags',
      'Known Author'=>'authors',       
      'Known Post-Parent'=>'parents'            
    ),
    'expert'=>false
  );
  // add adminfields order
  $fields['ple_repeat_order'] = array(
    'description'=>'Order list repetition',
    'type'=>array( ''=>'',
      'Ascending'=>'ASC', 'Descending'=>'DESC' ),
    'expert'=>false
  );
  $fields['ple_repeat_orderby'] = array(
    'description'=>'Order list repetition by placeholder',
    'type'=>$placeholders_type,
    'expert'=>false
  );

  // return fields
  return $fields;
}

//-----------------------------------------------------------------------------

// add the examples
function ple_repeat_examples( $examples ) {
  include_once( dirname(__FILE__).'/includes/examples.php' );
  return ple_repeat_examples_examples( $examples );
}

//-----------------------------------------------------------------------------

// filters
add_filter( 'ple_getlist',          'ple_repeat_getlist',          0, 2 );
add_filter( 'ple_list',             'ple_repeat_list',             0, 2 );
add_filter( 'ple_placeholdervalue', 'ple_repeat_placeholdervalue', 0, 5 );
add_filter( 'ple_fields',           'ple_repeat_fields',           0, 1 );
add_filter( 'ple_examples',         'ple_repeat_examples',         0, 1 );

//-----------------------------------------------------------------------------

?>