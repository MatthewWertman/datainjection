<?php
/*
 * @version $Id$
 -------------------------------------------------------------------------
 GLPI - Gestionnaire Libre de Parc Informatique
 Copyright (C) 2003-2010 by the INDEPNET Development Team.

 http://indepnet.net/   http://glpi-project.org
 -------------------------------------------------------------------------

 LICENSE

 This file is part of GLPI.

 GLPI is free software; you can redistribute it and/or modify
 it under the terms of the GNU General Public License as published by
 the Free Software Foundation; either version 2 of the License, or
 (at your option) any later version.

 GLPI is distributed in the hope that it will be useful,
 but WITHOUT ANY WARRANTY; without even the implied warranty of
 MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 GNU General Public License for more details.

 You should have received a copy of the GNU General Public License
 along with GLPI; if not, write to the Free Software
 Foundation, Inc., 59 Temple Place, Suite 330, Boston, MA  02111-1307  USA
 --------------------------------------------------------------------------
 */

// ----------------------------------------------------------------------
// Original Author of file: Remi Collet
// Purpose of file:
// ----------------------------------------------------------------------

if (!defined('GLPI_ROOT')){
   die("Sorry. You can't access directly to this file");
}

/// Location class
class PluginDatainjectionSoftwareLicenseInjection extends SoftwareLicense
   implements PluginDatainjectionInjectionInterface {

   function __construct() {
      $this->table = getTableForItemType('SoftwareLicense');
   }

   function isPrimaryType() {
      return true;
   }

   function connectedTo() {
      return array('Software');
   }

   function getOptions($primary_type = '') {
      global $LANG;
      if ($primary_type == 'SoftwareLicense') {
         $tab = parent::getSearchOptions();

         $blacklist = PluginDatainjectionCommonInjectionLib::getBlacklistedOptions();
         //Remove some options because some fields cannot be imported
         $notimportable = array();
         $ignore_fields = array_merge($blacklist,$notimportable);

         //Add linkfield for theses fields : no massive action is allowed in the core, but they can be
         //imported using the commonlib
         $add_linkfield = array('comment' => 'comment', 'notepad' => 'notepad');
         foreach ($tab as $id => $tmp) {
            if (!is_array($tmp) || in_array($id,$ignore_fields)) {
               unset($tab[$id]);
            }
            else {
               if (in_array($tmp['field'],$add_linkfield)) {
                  $tab[$id]['linkfield'] = $add_linkfield[$tmp['field']];
               }
               if (!in_array($id,$ignore_fields)) {
                  if (!isset($tmp['linkfield'])) {
                     $tab[$id]['injectable'] = PluginDatainjectionCommonInjectionLib::FIELD_VIRTUAL;
                  }
                  else {
                     $tab[$id]['injectable'] = PluginDatainjectionCommonInjectionLib::FIELD_INJECTABLE;
                  }

                  if (isset($tmp['linkfield']) && !isset($tmp['displaytype'])) {
                     $tab[$id]['displaytype'] = 'text';
                  }
                  if (isset($tmp['linkfield']) && !isset($tmp['checktype'])) {
                     $tab[$id]['checktype'] = 'text';
                  }
               }
            }
         }

         //Add displaytype value
         $dropdown = array("dropdown"       => array(5, 6, 7),
                           "date"          => array(8),
                           "computer"      => array(9),
                           "multiline_text" => array(16));
         foreach ($dropdown as $type => $tabsID) {
            foreach ($tabsID as $tabID) {
               $tab[$tabID]['displaytype'] = $type;
            }
         }
      }
      else {
         $tab[100]['name']          = $LANG['help'][31];
         $tab[100]['field']         = 'name';
         $tab[100]['table']         = getTableForItemType('Software');
         $tab[100]['linkfield']     = getForeignKeyFieldForTable($tab[100]['table']);
         $tab[100]['displaytype']   = 'text';
         $tab[100]['injectable']   = true;
         $tab[100]['storevaluein'] = $tab[100]['linkfield'];
      }

      return $tab;
   }


   function showAdditionalInformation($info = array(),$option = array()) {
      $name = "info[".$option['linkfield']."]";
      switch ($option['displaytype']) {
         case 'computer':
            Dropdown::show('Computer',array('name'=>$name,'comment'=>true,
                                            'entity'=>$_SESSION['glpiactive_entity']));
            break;
         default:
            break;
      }
   }

   /**
    * Standard method to add an object into glpi
    * WILL BE INTEGRATED INTO THE CORE IN 0.80
    * @param values fields to add into glpi
    * @param options options used during creation
    * @return an array of IDs of newly created objects : for example array(Computer=>1, Networkport=>10)
    */
   function addOrUpdateObject($values=array(), $options=array()) {
      global $LANG;
      $lib = new PluginDatainjectionCommonInjectionLib($this,$values,$options);
      $lib->processAddOrUpdate();
      return $lib->getInjectionResults();
   }

   function checkMandatoryMappings() {
      return array('softwares_id');
   }
}

?>