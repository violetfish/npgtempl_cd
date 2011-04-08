<?php
/*
 *
 * @license    GPL 2 (http://www.gnu.org/licenses/gpl.html)
 * @author     Jason Grout <jason-doku@creativetrax.com>>
 * 
 * Modifications by Sergio (1 Apr 2007), an unidentified author, 
 * and  Niko Paltzer (15 Jan 2010).
 *
 *  brought up-to-date with currnet Dokuwiki Event changes
 *  and event handling by Myron Turner (April 7 2011)
 *  turnermm02@shaw.ca     
 */
 
// must be run within Dokuwiki
if(!defined('DOKU_INC')) die();
 
if(!defined('DOKU_PLUGIN')) define('DOKU_PLUGIN',DOKU_INC.'lib/plugins/');
require_once(DOKU_PLUGIN.'action.php');
 
class action_plugin_newpagetemplate extends DokuWiki_Action_Plugin {
   var $done = false;
  /**
   * return some info
   */
  function getInfo(){
    return array(
      'author' => 'Jason Grout',
      'email'  => 'jason-doku@creativetrax.com',
      'date'   => '2007-02-24',
      'name'   => 'newpagetemplate',
      'desc'   => 'Loads into the new page creation box a template specified in the $_REQUEST "newpagetemplate" parameter (i.e., can be passed in the URL or as a form value).',
      'url'    => '',
    );
  }
 
  /**
   * register the eventhandlers
   *  Modified by 
   *  @author Myron Turner
   *  turnermm02@shaw.ca     
   */
  function register(&$contr){

    $contr->register_hook('COMMON_PAGE_FROMTEMPLATE', 'BEFORE', $this, 'pagefromtemplate', array());
    $contr->register_hook('COMMON_PAGETPL_LOAD', 'BEFORE', $this, 'pagefromtemplate', array());

  }

  /**
   *  pagefromtemplate
   *  Modified by 
   *  @author Myron Turner
   *  turnermm02@shaw.ca     
   */
 
  function pagefromtemplate(&$event, $param) {  
    if($this->done) return;
    $this->done=true;

    if(strlen(trim($_REQUEST['newpagetemplate']))>0) {
      global $conf;
      global $INFO;
      global $ID;
 
      $tpl = io_readFile(wikiFN($_REQUEST['newpagetemplate']));
 
      if($this->getConf('userreplace')) {
        $stringvars =
          array_map(create_function('$v', 'return explode(",",$v,2);'),
                    explode(';',$_REQUEST['newpagevars']));
        foreach($stringvars as $value) {
          $tpl = str_replace(trim($value[0]),trim($value[1]),$tpl);
	}
      }
 
      if($this->getConf('standardreplace')) {
        // replace placeholders
        $file = noNS($ID);
        $page = strtr($file,'_',' ');
 
        $tpl = str_replace(array(
                              '@ID@',
                              '@NS@',
                              '@FILE@',
                              '@!FILE@',
                              '@!FILE!@',
                              '@PAGE@',
                              '@!PAGE@',
                              '@!!PAGE@',
                              '@!PAGE!@',
                              '@USER@',
                              '@NAME@',
                              '@MAIL@',
                              '@DATE@',
                           ),
                           array(
                              $ID,
                              getNS($ID),
                              $file,
                              utf8_ucfirst($file),
                              utf8_strtoupper($file),
                              $page,
                              utf8_ucfirst($page),
                              utf8_ucwords($page),
                              utf8_strtoupper($page),
                              $_SERVER['REMOTE_USER'],
                              $INFO['userinfo']['name'],
                              $INFO['userinfo']['mail'],
                              $conf['dformat'],
                           ), $tpl);
 
        // we need the callback to work around strftime's char limit
        $tpl = preg_replace_callback('/%./',create_function('$m','return strftime($m[0]);'),$tpl);
      }
      $event->data['tpl'] = $tpl;

      $event->preventDefault(); 
    }
  }

}