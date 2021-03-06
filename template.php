<?php

/**
 * Override or insert variables into the html template.
 *
 * @param $vars
 *   An array of variables to pass to the theme template.
 */
function jbase_preprocess_html(&$vars) {
  // give <body> tag a unique class depending on PATHs
  $path_alias = strtolower(drupal_clean_css_identifier(drupal_get_path_alias($_GET['q'])));
  if ($path_alias == 'node') {
    $vars['classes_array'][] = '';
  }
  else {
    $vars['classes_array'][] = 'path-'. $path_alias;
  }
  
  // Add to the array of body classes
  // layout classes
  $vars['classes_array'][] = 'layout-'. (!empty($vars['page']['sidebar_first']) ? 'first-main' : 'main') . (!empty($vars['page']['sidebar_second']) ? '-second' : '');
  // headers classes
  if (!empty($vars['page']['header_first']) || !empty($vars['page']['header_second']) || !empty($vars['page']['header_third'])) {
    $header_regions = 'header';
    $header_regions .= (!empty($vars['page']['header_first'])) ? '-first' : '';
    $header_regions .= (!empty($vars['page']['header_second'])) ? '-second' : '';
    $header_regions .= (!empty($vars['page']['header_third'])) ? '-third' : '';
    $vars['classes_array'][] = $header_regions;
  }
  // preface classes
  if (!empty($vars['page']['preface_first']) || !empty($vars['page']['preface_second']) || !empty($vars['page']['preface_third'])) {
    $preface_regions = 'preface';
    $preface_regions .= (!empty($vars['page']['preface_first'])) ? '-first' : '';
    $preface_regions .= (!empty($vars['page']['preface_second'])) ? '-second' : '';
    $preface_regions .= (!empty($vars['page']['preface_third'])) ? '-third' : '';
    $vars['classes_array'][] = $preface_regions;
  }
  // postscripts classes
  if (!empty($vars['page']['postscript_first']) || !empty($vars['page']['postscript_second']) || !empty($vars['page']['postscript_third'])) {
    $postscript_regions = 'postscript';
    $postscript_regions .= (!empty($vars['page']['postscript_first'])) ? '-first' : '';
    $postscript_regions .= (!empty($vars['page']['postscript_second'])) ? '-second' : '';
    $postscript_regions .= (!empty($vars['page']['postscript_third'])) ? '-third' : '';
    $vars['classes_array'][] = $postscript_regions;
  }
  // footers classes
  if (!empty($vars['page']['footer_first']) || !empty($vars['page']['footer_second']) || !empty($vars['page']['footer_third'])) {
    $footer_regions = 'footers';
    $footer_regions .= (!empty($vars['page']['footer_first'])) ? '-first' : '';
    $footer_regions .= (!empty($vars['page']['footer_second'])) ? '-second' : '';
    $footer_regions .= (!empty($vars['page']['footer_third'])) ? '-third' : '';
    $vars['classes_array'][] = $footer_regions;
  }
  // Panels classes
  $vars['classes_array'][] = (module_exists('panels') && (panels_get_current_page_display())) ? 'panels' : '';
  if (module_exists('panels') && (panels_get_current_page_display())) {
    $panels_display = panels_get_current_page_display();
    $vars['classes_array'][] = 'panels-'. strtolower(preg_replace('/[^a-zA-Z0-9-]+/', '-', $panels_display->layout));
  }
  
  $vars['classes_array'] = array_filter($vars['classes_array']);
  
  // IE stylesheets
  drupal_add_css(path_to_theme() . '/css/ie7-fixes.css', array('group' => CSS_THEME, 'browsers' => array('IE' => 'IE 7', '!IE' => FALSE), 'preprocess' => FALSE));
  drupal_add_css(path_to_theme() . '/css/ie8-fixes.css', array('group' => CSS_THEME, 'browsers' => array('IE' => 'IE 8', '!IE' => FALSE), 'preprocess' => FALSE));
}


/**
 * Override or insert variables into the page template.
 *
 * @param $vars
 *   An array of variables to pass to the theme template.
 */
function jbase_preprocess_page(&$vars) {
  // Add preface, postscript, & footers classes with number of active sub-regions
  $region_list = array(
    'prefaces' => array('preface_first', 'preface_second', 'preface_third'), 
    'postscripts' => array('postscript_first', 'postscript_second', 'postscript_third'),
    'footers' => array('footer_first', 'footer_second', 'footer_third')
  );
  foreach ($region_list as $sub_region_key => $sub_region_list) {
    $active_regions = array();
    foreach ($sub_region_list as $region_item) {
      if (!empty($vars['page'][$region_item])) {
        $active_regions[] = $vars['page'][$region_item];
      }
    }
    $vars[$sub_region_key] = $sub_region_key .'-'. strval(count($active_regions));
  }
  
  // Render menu tree from main-menu
  $menu_tree = menu_tree('main-menu');
  $vars['main_menu_tree'] = render($menu_tree);
}


/**
 * Override or insert variables into the node templates.
 *
 * @param $vars
 *   An array of variables to pass to the theme template.
 */
function jbase_preprocess_node(&$vars) {
  $vars['classes_array'][] = $vars['zebra'];
  if ($vars['view_mode'] == 'full') {
    $vars['classes_array'][] = 'node-full';
  }
  
  // Add node-type-page template suggestion
  if ($vars['page']) {
    $vars['theme_hook_suggestions'][] = 'node__'. $vars['node']->type .'_page';
    $vars['theme_hook_suggestions'][] = 'node__'. $vars['node']->type .'-'. $vars['node']->nid .'_page';
  }
  else {
    $vars['theme_hook_suggestions'][] = 'node__'. $vars['node']->type .'_teaser';
    $vars['theme_hook_suggestions'][] = 'node__'. $vars['node']->nid;
  }
}


/**
 * Preprocess variables for region.tpl.php
 *
 * Prepare the values passed to the theme_region function to be passed into a
 * pluggable template engine. Uses the region name to generate a template file
 * suggestions. If none are found, the default region.tpl.php is used.
 *
 * @see region.tpl.php
 */
function jbase_preprocess_region(&$vars) {
  // Sidebar region template suggestion.
  if (strpos($vars['region'], 'sidebar_') === 0) {
    $vars['theme_hook_suggestions'][] = 'region__sidebar';
    $vars['theme_hook_suggestions'][] = 'region__' . $vars['region'];
  }
}


/**
 * Override or insert variables into the block templates.
 *
 * @param $vars
 *   An array of variables to pass to the theme template.
 */
function jbase_preprocess_block(&$vars) {
  $block = $vars['block'];
  // First/last block position
  $vars['position'] = ($vars['block_id'] == 1) ? 'first' : '';
  if ($vars['block_id'] == count(block_list($block->region))) {
    $vars['position'] = ($vars['position']) ? '' : 'last';
  }
}


/**
 * Override or insert variables into the comment templates.
 *
 * @param $vars
 *   An array of variables to pass to the theme template.
 */
function jbase_preprocess_comment(&$vars) {
  // Add odd/even classes to comments classes_array 
  static $comment_odd = TRUE;
  $vars['classes_array'][] = $comment_odd ? 'odd' : 'even';
  $comment_odd = !$comment_odd;
}
