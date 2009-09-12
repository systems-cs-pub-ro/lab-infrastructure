<?php
/**
 * Wiki Lab Extension: Solution Plugin
 *
 * Syntax:     <solution> ... </solution>
 *
 * Acknowledgements:
 *  Derived from Box plugin (http://www.dokuwiki.org/plugin:box)
 * 
 * @license    GPL 2 (http://www.gnu.org/licenses/gpl.html)
 * @author     Mircea Bardac <mircea@bardac.net>  
 */

if(!defined('DOKU_INC')) define('DOKU_INC',realpath(dirname(__FILE__).'/../../').'/');
if(!defined('DOKU_PLUGIN')) define('DOKU_PLUGIN',DOKU_INC.'lib/plugins/');
require_once(DOKU_PLUGIN.'syntax.php');

/**
 * All DokuWiki plugins to extend the parser/rendering mechanism
 * need to inherit from this class
 */
class syntax_plugin_labsolution extends DokuWiki_Syntax_Plugin {

    /**
     * return some info
     */
    function getInfo(){
      return array(
        'author' => 'Mircea Bardac',
        'email'  => 'mircea@bardac.net',
        'date'   => '2009-09-12',
        'name'   => 'Wiki Lab Extension: Solution Plugin',
        'desc'   => 'Plugin for showing up solutions in lab.',
        'url'    => '',
      );
    }

    function getType(){ return 'protected';}
    function getAllowedTypes() {
            return array('container','substition','protected','disabled','formatting','paragraphs');
    }
    function getPType(){ return 'block';}

    // must return a number lower than returned by native 'code' mode (200)
    function getSort(){ return 195; }

    // override default accepts() method to allow nesting 
    // - ie, to get the plugin accepts its own entry syntax
    //function accepts($mode) {
    //    if ($mode == substr(get_class($this), 7)) return true;
    //    return parent::accepts($mode);
    //}

    /**
     * Connect pattern to lexer
     */
    function connectTo($mode) {       
            $this->Lexer->addEntryPattern('<solution>(?=.*?</solution>)',$mode,'plugin_labsolution');
    }

    function postConnect() {
            $this->Lexer->addExitPattern('</solution>', 'plugin_labsolution');
            #$this->Lexer->addPattern('.*?', 'plugin_labsolution');
    }

    /**
     * Handle the match
     */
    function handle($match, $state, $pos, &$handler){

      $last_sol_lab = $this->getConf('last_sol_lab');
      $this_lab_no = 100;
      $r = $_SERVER['REQUEST_URI'];
      if (preg_match("/\/lab\/\d\d\/[^\/]*/",$r)) {    
          $p = explode("/",$r);
          $this_lab_no=(int)$p[count($p)-2];
      }

       switch ($state) {
            case DOKU_LEXER_ENTER:
                #$handler->_addCall('nocache',array(),$pos);
                $data = array();
                return array('labsolution_open',array());
                return false;

            case DOKU_LEXER_MATCHED:
                return array('labsolution_data', $match);

            case DOKU_LEXER_UNMATCHED:                
                 return array('labsolution_data', $match);
                 #$handler->_addCall('cdata',array($match), $pos);
                 #return false;

            case DOKU_LEXER_EXIT:
                return array('labsolution_close', '');

        }       
        return false;
    }

    /**
     * Create output
     */
    function render($mode, &$renderer, $indata) {

      if (empty($indata)) return false;
      list($instr, $data) = $indata;

      $last_sol_lab = $this->getConf('last_sol_lab');
      $this_lab_no = 100;
      $r = $_SERVER['REQUEST_URI'];
      if (preg_match("/\/lab\/\d\d\/[^\/]*/",$r)) {    
          $p = explode("/",$r);
          $this_lab_no=(int)$p[count($p)-2];
      }

      if($mode == 'xhtml'){
          switch ($instr) {

          case 'labsolution_open' :   
            if ($this_lab_no > $last_sol_lab) break;      
            $renderer->doc .= '<div class="solution"><div class="solution_title">Rezolvare</div><div class="solution_contents">';
            break;

          case 'labsolution_data' :      
            if ($this_lab_no > $last_sol_lab) break;
            $renderer->doc .= $renderer->_xmlEntities($data); 
            break;

          case 'labsolution_close' :
            if ($this_lab_no > $last_sol_lab) break;      
            
            #$r = $_SERVER['REQUEST_URI'];
            #$p = explode("/",$r);
            #$q = "not ok";
            #if (preg_match("/\/lab\/\d\d\/[^\/]*/",$r)) $q=(int)$p[count($p)-2];
            #$renderer->doc .= $q."<br/>".$r."<br/>".date(DATE_RFC822);
            
            $renderer->doc .= "</div></div>\n";
            break;
        }

        return true;
      }
      return false;
    }

}

//Setup VIM: ex: et ts=4 enc=utf-8 :
