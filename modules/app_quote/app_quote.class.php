<?php
/**
 * Приложение: Цитаты
 *
 * @package app_quote
 * @author Serge J. <jey@tut.by>
 * @copyright http://www.atmatic.eu/ (c)
 * @author L.D.V <palacex@gmail.com>
 * @version 0.2
 */
class app_quote extends module
{
   /**
    * app_quote
    *
    * Module class constructor
    *
    * @access private
    */
   function app_quote() {
      $this->name="app_quote";
      $this->title="<#LANG_APP_QUOTE#>";
      $this->module_category="<#LANG_SECTION_APPLICATIONS#>";
      $this->checkInstalled();
   }
   
   /**
    * Saving module parameters
    * @param mixed $data 
    * @return string
    */
   public function saveParams($data = 0)
   {
      $p = array();
      if (isset($this->id))
         $p["id"] = $this->id;
      
      if (isset($this->view_mode))
         $p["view_mode"] = $this->view_mode;
      
      if (isset($this->edit_mode))
         $p["edit_mode"] = $this->edit_mode;
      
      if (isset($this->tab))
         $p["tab"] = $this->tab;
      
      return parent::saveParams($p);
   }
   
   /**
    * Getting module parameters from query string
    */
   public function getParams()
   {
      global $id, $mode, $mode, $view_mode, $edit_mode, $tab;
      
      if (isset($id))
         $this->id = $id;
      
      if (isset($mode))
         $this->mode = $mode;
      
      if (isset($view_mode))
         $this->view_mode = $view_mode;
      
      if (isset($edit_mode))
         $this->edit_mode = $edit_mode;
      
      if (isset($tab))
         $this->tab = $tab;
   }
   
   /**
    * Summary of run
    */
   public function run()
   {
      global $session;
      $out = array();
      
      if ($this->action == 'admin') 
         $this->admin($out);
      else
         $this->usual($out);
      
      if (isset($this->owner->action)) 
         $out['PARENT_ACTION'] = $this->owner->action;
      
      if (isset($this->owner->name))
         $out['PARENT_NAME'] = $this->owner->name;
      
      $out['VIEW_MODE'] = $this->view_mode;
      $out['EDIT_MODE'] = $this->edit_mode;
      $out['MODE'] = $this->mode;
      $out['ACTION'] = $this->action;
      $out['TAB'] = $this->tab;
      
      if ($this->single_rec)
         $out['SINGLE_REC'] = 1;
      
      $this->data = $out;
      $p = new parser(DIR_TEMPLATES . $this->name . "/" . $this->name . ".html", $this->data, $this);
      $this->result = $p->result;
   }
   
   /**
    * Module backend
    * @param mixed $out 
    */
   public function admin(&$out) 
   {
      if (isset($this->data_source) && !$_GET['data_source'] && !$_POST['data_source'])
      {
         $out['SET_DATASOURCE'] = 1;
      }
      
      if ($this->data_source == 'app_quote' || $this->data_source == '')
      {
         if ($this->view_mode == '' || $this->view_mode == 'search_app_quote')
         {
            $this->search_app_quote($out);
         }
         
         if ($this->view_mode == 'edit_app_quote') 
            $this->edit_app_quote($out, $this->id);
         
         if ($this->view_mode == 'delete_app_quote')
         {
            $this->delete_app_quote($this->id);
            $this->redirect("?");
         }
         
         if ($this->view_mode == 'import_app_quote')
            $this->import_app_quote($out);
         
         if ($this->view_mode == 'multiple_app_quote')
         {
            global $ids;
            if (is_array($ids))
            {
               $total_selected = count($ids);
               global $delete;
               global $export;
               
               if ($export)
                  $this->export_app_quote($ids);
               
               for($i=0; $i < $total_selected; $i++)
               {
                  $id=$ids[$i];
                  
                  // operation: DELETE
                  if ($delete) 
                     $this->delete_app_quote($id);
               }
            }
            
            $this->redirect("?");
         }
      }
   }
   
   /**
    * Module frontend
    * @param mixed $out 
    */
   public function usual(&$out)
   {
      global $session;
      
      $orderBy = ($this->order) ? 'QUOTE_ID desc' : 'RAND()';
      
      if (!$session->data['SEEN_QUOTES'])
         $session->data['SEEN_QUOTES'] = '0';
      
      $res = $this->GetQuoteByIds($session->data['SEEN_QUOTES'], $orderBy);
      
      if (!isset($res['QUOTE_ID'])) 
      {
         $session->data['SEEN_QUOTES'] = '0';
         $res = $this->GetQuoteByIds($session->data['SEEN_QUOTES'], $orderBy);
      }
      
      if (isset($res['QUOTE_ID']))
         $session->data['SEEN_QUOTES'] .= ',' . $res['QUOTE_ID'];
      
      $session->save();

      if ($res['QUOTE_ID'])
         $out['QUOTE'] = $res['QUOTE']; 
   }
   
   /**
    * app_quote search
    */
   public function search_app_quote(&$out)
   {
      global $session;
      global $save_qry;
      
      if ($this->owner->name == 'panel')
         $out['CONTROLPANEL'] = 1;
      
      if (!$save_qry) 
         $session->data['app_quotes_qry'] = 1;
      
      $qry = $session->data['app_quotes_qry'];
      
      if (!$qry) $qry="1";
      
      global $sortby_app_quotes;
      if (!$sortby_app_quotes)
      {
         $sortby_app_quotes = $session->data['app_quotes_sort'];
      } 
      else 
      {
         if ($session->data['app_quotes_sort'] == $sortby_app_quotes) 
         {
            if (is_integer(strpos($sortby_app_quotes, ' DESC')))
            {
               $sortby_app_quotes = str_replace(' DESC', '', $sortby_app_quotes);
            }
            else
            {
               $sortby_app_quotes = $sortby_app_quotes . " DESC";
            }
         }
         
         $session->data['app_quotes_sort'] = $sortby_app_quotes;
      }
      
      if (!$sortby_app_quotes)
         $sortby_app_quotes = "QUOTE_ID DESC";
      
      $out['SORTBY'] = $sortby_app_quotes;
      
      // SEARCH RESULTS
      $res = SQLSelect("select QUOTE_ID, QUOTE, LM_DATE 
                          from APP_QUOTE 
                         where " . $qry . " 
                         order by " . $sortby_app_quotes);
      
      if ($res[0]['QUOTE_ID'])
      {
         paging($res, 50, $out); // search result paging
         $total = count($res);
         
         for($i = 0; $i < $total; $i++) 
         {
            // some action for every record if required
            $res[$i]['QUOTE'] = htmlspecialchars($res[$i]['QUOTE']);
         }
         $out['RESULT'] = $res;
      }
   }
   
   /**
    * app_quote edit/add
    */
   public function edit_app_quote(&$out, $id)
   {
      if ($this->owner->name == 'panel')
         $out['CONTROLPANEL'] = 1;
      
      
      if (is_numeric($id))
         $rec = $this->SelectQuoteByID($id);
      
      if ($this->mode == 'update')
      {
         global $body;
         
         $result = false;
         if (isset($rec['QUOTE_ID']))
         {
            if (!$this->IsQuoteExist($body))
               $result = $this->UpdateQuote($rec['QUOTE_ID'], $body);
         }
         else
         {
            if (!$this->IsQuoteExist($body))
            {
               $rec['QUOTE_ID'] = $this->SetQuote($body);
               if ($rec['QUOTE_ID'] != 0) 
                  $result = true;
            }
         }
         
         $out['OK'] = $result;
         
      }
      
      foreach($rec as $k=>$v)
      {
         if (!is_array($v)) 
            $rec[$k] = htmlspecialchars($v);
      }
      
      outHash($rec, $out);
      
   }
   
   /**
    * app_quote data import
    */
   public function import_app_quote(&$out) 
   {
      if ($this->owner->name == 'panel')
         $out['CONTROLPANEL'] = 1;
      
      if ($this->mode == 'update')
      {
         global $file;
         if (file_exists($file)) 
         {
            $tmp = LoadFile($file);
            $lines = mb_split(PHP_EOL, $tmp);
            $total_lines = count($lines);
            
            for($i = 0; $i < $total_lines; $i++) 
            {
               $quote = $lines[$i];
               if ($quote == '' || $this->IsQuoteExist($quote)) continue;
               
               $this->SetQuote($quote);
               $out["TOTAL"]++;
            }
         }
      }
   }
   
   /**
    * app_quote data import
    */
   public function export_app_quote($ids) 
   {
      if (count($ids)) 
         $tmp = $this->SelectQuotesByIds($ids);
      else 
         $tmp = $this->SelectQuotes();
      
      $total = count($tmp);
      
      if ($total) 
      {
         $res = '';
         
         for($i = 0; $i < $total; $i++) 
         {
            $line = array();
            foreach($tmp[$i] as $k=>$v)
            {
               if ($k != 'QUOTE_ID' && (!isset($this->{strtolower($k)}) || $k == 'TITLE')) 
                  $line[] = trim($v);
            }
            
            $res .= implode("\t", $line) . PHP_EOL;
         }
         
         $filename = "app_quote_export.txt";
         header("Content-type: application/octet-stream");
         header("Content-Disposition: attachment; filename=" . $filename);
         header("Pragma: no-cache");
         header("Expires: 0");
         echo $res;
         exit;
      }
   }
   
   /**
    * app_quote delete record
    */
   public function delete_app_quote($id)
   {
      $rec = $this->SelectQuoteByID($id);
      
      if (isset($rec["QUOTE_ID"]))
         $this->DeleteQuoteByID($rec["QUOTE_ID"]);
   }
   
   /**
    * Module installation routine
    */
   public function install($data = '')
   {
      $val = SQLSelectOne("select count(*)+2 CNT from information_schema.tables where table_schema = '" . DB_NAME . "' and table_name = 'APP_QUOTE'");
      $val = $val["CNT"] == 2 ? FALSE : TRUE;
      
      if (!file_exists(DIR_MODULES . $this->name . "/installed") && $val == FALSE) 
      {
         $this->DeleteAppQuoteData();
         $this->CreateAppQuoteDataStructure();
      }
      
      parent::install();
   }
   
   /**
    * Module uninstall routine
    */
   public function uninstall()
   {
      $this->DeleteAppQuoteData();
      parent::uninstall();
   }
   
   /**
    * Database installation routine
    */
   public function dbInstall($data)
   {
   
   }
   
   /**
    * Return quote by ID
    * @param mixed $quoteID  Quote ID
    * @return array
    */
   private function SelectQuoteByID($quoteID)
   {
      $rec = SQLSelectOne("select QUOTE_ID, QUOTE, LM_DATE 
                             from APP_QUOTE 
                            where QUOTE_ID = " . $quoteID);
      
      
      return $rec;
   }
   
   /**
    * Check for exist quote
    * @param mixed $quote Quote
    * @return bool
    */
   private function IsQuoteExist($quote)
   {
      $query = "select count(*) CNT
                  from APP_QUOTE 
                 where QUOTE_HASH = '" . md5($quote) . "'";
      
      $result = SQLSelectOne($query);
      
      return $result['CNT'] > 0;
   }
   
   /**
    * Return Quote by Ids
    * @param mixed $quoteIds 
    * @param mixed $orderBy Order param
    * @return array
    */
   private function SelectQuoteByIds($quoteIds, $orderBy)
   {
      $result = SQLSelectOne("select QUOTE_ID, QUOTE, LM_DATE
                             from APP_QUOTE
                            where QUOTE_ID not in (" . $quoteIds . ")
                            order by " . $orderBy ."
                            limit 1");
      
      return $result;
   }
   
   /**
    * Return selected quotes
    * @param mixed $quoteIdArray Array of selected quotes
    * @return array
    */
   private function SelectQuotesByIds($quoteIdArray)
   {
      
      $quoteIds = implode(',', $quoteIdArray);
      $result = SQLSelect("select QUOTE_ID, QUOTE, LM_DATE
                             from APP_QUOTE
                            where QUOTE_ID in (" . $quoteIds . ")");
      
      return $result;
   }
   
   /**
    * Return all quotes as array
    * @return array
    */
   private function SelectQuotes()
   {
      $result = SQLSelect("select QUOTE_ID, QUOTE, LM_DATE
                             from APP_QUOTE");
      
      return $result;
   }
   
   /**
    * DeleteQuote by quote ID
    * @param mixed $quoteID  Quote ID
    */
   private function DeleteQuoteByID($quoteID)
   {
      // some action for related tables
      SQLExec("delete
                 from APP_QUOTE
                where QUOTE_ID = " . $quoteID);
   }
   
   /**
    * Update quote by ID
    * @param mixed $quoteID Quote ID
    * @param mixed $quote   Quote content
    * @return bool
    */
   private function UpdateQuote($quoteID, $quote)
   {
      if ($quoteID == null) return false;  // quoteID not found
      if ($quote == '') return false;
      
      $requestDate =  date('Y-m-d H:i:s');
      $rec = array();
      $rec["QUOTE"]      = $quote;
      $rec["QUOTE_HASH"] = md5($quote);
      $rec["LM_DATE"]    = $requestDate;
      $rec["QUOTE_ID"]   = $quoteID;
      
      $result = SQLUpdate("APP_QUOTE", $rec, "QUOTE_ID");
      
      return $result == 1;
   }
   
   /**
    * Add quote to database
    * @param mixed $quote Quote
    * @return bool|int
    */
   private function SetQuote($quote)
   {
      if ($quote == '') return false;
      
      $requestDate =  date('Y-m-d H:i:s');
      $rec = array();
      $rec["QUOTE"]      = $quote;
      $rec["QUOTE_HASH"] = md5($quote);
      $rec["LM_DATE"]    = $requestDate;
      
      $res = SQLInsert("APP_QUOTE", $rec);
      
      return $res;
   }
   
   /**
    * Remove application data from database
    */
   private function DeleteAppQuoteData()
   {
      SQLExec('drop table if exists APP_QUOTE');
   }
   
   /**
    * Application Quote DataStructure creation
    */
   private function CreateAppQuoteDataStructure()
   {
      $query = "create table APP_QUOTE(";
      $query .= "  QUOTE_ID             INT(10) not null auto_increment,";
      $query .= "  QUOTE                TEXT not null,";
      $query .= "  QUOTE_HASH           VARCHAR(32) not null,";
      $query .= "  LM_DATE              DATETIME not null,";
      $query .= "  primary key (QUOTE_ID),";
      $query .= "  unique key AK_APP_QUOTE_HASH (QUOTE_HASH)";
      $query .= "  ) ENGINE=InnoDB CHARACTER SET=utf8;";
      
      SQLExec($query);
   }
   
}

?>