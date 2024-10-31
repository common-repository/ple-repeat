<?php
//-----------------------------------------------------------------------------
/*
  this file will get included by ple_repeat.php
    it contains the examples that can be added 
  this file belongs to ple repeat version 1
*/   
//-----------------------------------------------------------------------------
?>
<?php

//-----------------------------------------------------------------------------

function ple_repeat_examples_examples( $examples ) {

  // add examples
  $examples['MYLATESTPOSTOFEACHCATEGORY'] = array(
    'before' => 'Latest post in category "<a href="%categoryurl%">%category%</a>": <br>',
    'after' => '<br><br>',
    'entry' => '&raquo; <a href="%posturl%">%posttitle%</a>',
    'noposts' => '',
    'numberposts'=>1, 
    'minnumberposts'=>1, 
    'orderby'=>'post_date', 
    'order'=>'DESC', 
    'post_type'=>'post', 
    'post_status'=>'publish',
    'ple_repeat_repeat'=>'categories',
    'ple_repeat_order'=>'ASC',    
    'ple_repeat_orderby'=>'category'
  );
  $examples['MYATTACHMENTS'] = array(
    'before' => '<li><a href="%parenturl%">%parent%</a><ul>',
    'after'=>'</ul>',
    'entry'=>'<li><a href="%guid%">%title%</a></li>',
    'noposts'=>'',
    'numberposts'=>'', 
    'minnumberposts'=>1, 
    'orderby'=>'post_date', 
    'order'=>'DESC', 
    'post_type'=>'attachment',
    'post_status'=>'',
    'post_parent'=>'THIS',
    'ple_repeat_repeat'=>'parents',
    'ple_repeat_order'=>'DESC',    
    'ple_repeat_orderby'=>'parentdate'
  );
  $examples['MYLATESTPOSTOFEACHAUTHOR'] = array(
    'before' => 'Latest post of "<a href="%authorurl%">%author%</a>": <br>',
    'after' => '<br><br>',
    'entry' => '&raquo; <a href="%posturl%">%posttitle%</a>',
    'noposts' => '',
    'numberposts'=>1, 
    'minnumberposts'=>1, 
    'orderby'=>'post_date', 
    'order'=>'DESC', 
    'post_type'=>'post', 
    'post_status'=>'publish', 
    'ple_repeat_repeat'=>'authors',
    'ple_repeat_order'=>'ASC', 
    'ple_repeat_orderby'=>'author'    
  );
  $examples['MYTAGINDEX'] = array(
    'before' => '<li><a href="%tagurl%">%tag%</a> (%count%)<ul>',
    'after' => '</ul></li>',
    'entry' => '<li><a href="%posturl%">%posttitle%</a></li>',
    'noposts' => '',
    'numberposts'=>'', 
    'minnumberposts'=>1, 
    'orderby'=>'post_title', 
    'order'=>'ASC', 
    'post_type'=>'post', 
    'post_status'=>'publish', 
    'ple_repeat_repeat'=>'tags',
    'ple_repeat_order'=>'ASC',
    'ple_repeat_orderby'=>'tag'  
  );

  return $examples;
}

//-----------------------------------------------------------------------------

?>