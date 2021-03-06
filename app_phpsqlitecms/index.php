<?php
/**
 * phpSQLiteCMS - a simple and lightweight PHP web content management system
 * based on PHP and SQLite
 *
 * @author Mark Alexander Hoschek <alex at phpsqlitecms dot net>
 * @copyright 2006-2010 Mark Alexander Hoschek
 * @version 2.0.4
 * @link http://phpsqlitecms.net/
 * @package phpSQLiteCMS
 *
 * This program is free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program.  If not, see <http://www.gnu.org/licenses/>.
 */

session_start();

define('CACHE_DIR', 'cms/cache/');

// get query string passed by mod_rewrite:
if(isset($_GET['qs']))
 {
  if(get_magic_quotes_gpc()) $_GET['qs'] = stripslashes($_GET['qs']);
  $qs = $_GET['qs'];
 }
else
 {
  $qs = '';
 }

// check if requested page is cached and if so displays it:
if(empty($_POST) && file_exists('./'.CACHE_DIR.'settings.php'))
 {
  include('./'.CACHE_DIR.'settings.php');
  if(empty($_SESSION[$settings['session_prefix'].'user_id']))
   {
    if($qs=='') $cache_file = rawurlencode(strtolower($settings['index_page'])).'.cache';
    else $cache_file = rawurlencode(strtolower($qs)).'.cache';
    if(file_exists('./'.CACHE_DIR.$cache_file))
     {
      include('./'.CACHE_DIR.$cache_file);
      exit; // that's it if cached page is available.
     }
   }
 }

define('IN_INDEX', TRUE);

try
 {
  #throw new Exception('Error message...');
  #require('./cms/config/db_settings.conf.php');
  require('./cms/includes/functions.inc.php');

  // load replacement functions for the multibyte string functions
  // if they are not available:
  if(!defined('MB_CASE_LOWER')) require('./cms/includes/functions.mb_replacements.inc.php');

  require('./cms/includes/classes/Database.class.php');
  $database = new Database();

  $settings = get_settings();

  // access permission check for not registered users:
  if($settings['check_access_permission']==1 && !isset($_SESSION[$settings['session_prefix'].'user_id']))
   {
    if(is_access_denied()) raise_error('403');
   }

  // set timezone:
  if($settings['time_zone']) date_default_timezone_set($settings['time_zone']);

  define('BASE_URL', get_base_url());
  define('BASE_PATH', get_base_path());
  define('MEDIA_DIR', 'media/');
  define('SMILIES_DIR', 'media/smilies/');
  define('IMAGE_IDENTIFIER', 'photo');
  define('CATEGORY_IDENTIFIER', 'category:');
  define('AMPERSAND_REPLACEMENT', ':AMP:');

  define('WYSIWYG_EDITOR', 'cms/modules/tiny_mce/tiny_mce.js');
  define('WYSIWYG_EDITOR_INIT', 'js/wysiwyg_init_frontend.js');

  if($settings['content_functions']==1) require(BASE_PATH.'cms/includes/functions.content.inc.php');

  require('./cms/includes/classes/Template.class.php');
  $template = new Template();
  #$template->set_settings($settings);

  if($settings['caching'])
   {
    $cache = new Cache(BASE_PATH.CACHE_DIR, $settings);
    if(!empty($_POST) || isset($_SESSION[$settings['session_prefix'].'user_id']))
     {
      $cache->doCaching = false;
     }
   }

  if(isset($_SESSION[$settings['session_prefix'].'user_id']))
   {
    $template->assign('admin', true);
   }
  else
   {
    $template->assign('admin', false);
   }

  $template->assign('settings', $settings);

  $template->assign('BASE_URL', BASE_URL);

  $qsp = explode(',',$qs);
  if($qsp[0] == '')
   {
    define('PAGE', strtolower($settings['index_page']));
   }
  else
   {
    define('PAGE',strtolower($qsp[0]));
   }

  // append comma separated parameters to $_GET ($_GET['get_1'], $_GET['get_2'] etc.):
  if(isset($qsp[1]))
   {
    $items = count($qsp);
    for($i=1;$i<$items;++$i)
     {
      $_GET['get_'.$i] = $qsp[$i];
     }
   }

  if(isset($_GET['get_1']) && $_GET['get_1']==IMAGE_IDENTIFIER && isset($_GET['get_2']))
   {
    // photo:
    include(BASE_PATH.'cms/includes/photo.inc.php');
   }
  else
   {
    // regular content:
    include(BASE_PATH.'cms/includes/content.inc.php');
   }

  // display template:
  if(isset($template_file))
   {
    $template->assign('lang', Localization::$lang);
    $template->assign('content_type', $content_type);
    $template->assign('charset', Localization::$lang['charset']);
    header('Content-Type: '.$content_type.'; charset='.Localization::$lang['charset']);
    $template->display(BASE_PATH.'templates/'.$template_file);
    // create cache file:
    if(isset($cache))
     {
      if($cache->cacheId && $cache->doCaching)
       {
        $cache_content = $cache->createCacheContent($template->fetch(BASE_PATH.'templates/'.$template_file), $content_type, CHARSET);
        $cache->createChacheFile($cache_content);
       }
     }
   }
 } // end try
catch(Exception $exception)
 {
  include('./cms/includes/exception.inc.php');
 }
?>
