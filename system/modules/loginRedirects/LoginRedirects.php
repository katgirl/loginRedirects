<?php if (!defined('TL_ROOT')) die('You can not access this file directly!');

/**
 * Contao Open Source CMS
 * Copyright (C) 2005-2010 Leo Feyer
 *
 * Formerly known as TYPOlight Open Source CMS.
 *
 * This program is free software: you can redistribute it and/or
 * modify it under the terms of the GNU Lesser General Public
 * License as published by the Free Software Foundation, either
 * version 3 of the License, or (at your option) any later version.
 * 
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the GNU
 * Lesser General Public License for more details.
 * 
 * You should have received a copy of the GNU Lesser General Public
 * License along with this program. If not, please visit the Free
 * Software Foundation website at <http://www.gnu.org/licenses/>.
 *
 * PHP version 5
 * @copyright  MEN AT WORK 2011
 * @package    loginRedirects 
 * @license    GNU/LGPL 
 * @filesource
 */

class LoginRedirects extends ContentElement
{

    /**
     * Tempate var
     * 
     * @var string 
     */ 
    protected $strTemplate = "ce_loginRedirects";

    /**
     * Backend
     * 
     * @return string 
     */
    public function generate()
    {
        // If backendmode shows widlcard.
        if (TL_MODE == 'BE')
        {
            $arrRedirect = deserialize($this->lr_choose_redirect);
            
            $arrWildcard = array();
            $i = 0;
            
            $arrWildcard[] = '### LOGIN REDIRECTS ###';
            $arrWildcard[] = '<br /><br />';
            $arrWildcard[] = '<table>';
            $arrWildcard[] = '<colgroup>';
            $arrWildcard[] = '<col width="175" />';
            $arrWildcard[] = '<col width="400" />';
            $arrWildcard[] = '</colgroup>';
            foreach ($arrRedirect as $key => $value)
            {
                $arrWildcard[] = '<tr>';

                $arrWildcard[] = '<td>';
                $arrWildcard[] = "(" . ++$i . ") " .  $this->lookUpName($value["lr_id"]);
                $arrWildcard[] = '</td>';
                
                $arrPage = $this->lookUpPage($value["lr_redirecturl"]);

                $arrWildcard[] = '<td>';
                if ($arrPage["link"] != "")
                {
                    $arrWildcard[] = '<a ' . LINK_NEW_WINDOW . ' href="' . $arrPage["link"] . '">';
                    $arrWildcard[] = $arrPage["title"];
                    $arrWildcard[] = '</a>';
                }
                else
                {
                    $arrWildcard[] = $arrPage["title"];
                }
                $arrWildcard[] = '</td>';

                $arrWildcard[] = '</tr>';
            }

            $arrWildcard[] = '</table>';

            $objTemplate = new BackendTemplate('be_wildcard');

            $objTemplate->wildcard = implode("\n", $arrWildcard);
            $objTemplate->title = $this->headline;
            $objTemplate->id = $this->id;
            $objTemplate->link = $this->name;

            return $objTemplate->parse();
        }

        return parent::generate();
    }    

    /**
     * Frontend
     */
    protected function compile()
    {
        // Import frontenduser
        $this->import("FrontendUser");

        // Get the redirect array
        if (strlen($this->lr_choose_redirect) == 0 || $this->FrontendUser->login == 0)
        {
            return;
        }

        // Get settings
        $arrRedirect = deserialize($this->lr_choose_redirect);

        // Get usergroups
        $arrCurrentGroups = $this->FrontendUser->groups;

        // Build group and members array
        foreach ($arrRedirect as $key => $value)
        {
            if (strpos($value['lr_id'], "M") !== FALSE)
            {
                $value['lr_id'] = str_replace("M::", "", $value['lr_id']);
                                
                if ($this->FrontendUser->id == $value["lr_id"])
                {
                    // Get ID for page
                    $intPage = str_replace(array("{{link_url::", "}}"), array("", ""), $value["lr_redirecturl"]);                    
                    // Load Page
                    $arrPage = $this->Database->prepare("SELECT * FROM tl_page WHERE id=?")->execute((int) $intPage)->fetchAllAssoc();

                    //Check if we have a page
                    if (count($arrPage) == 0)
                    {
                        $this->log("Try to redirect, but the necessary page cannot be found in the database.", __FUNCTION__ . " | " . __CLASS__, TL_ERROR);
                    }
                    else
                    {
                        $this->redirect($this->generateFrontendUrl($arrPage[0]));
                    }
                }
            }
            else if (strpos($value['lr_id'], "G") !== FALSE)
            {
                $value['lr_id'] = str_replace("G::", "", $value['lr_id']);
                
                if (in_array($value["lr_id"], $arrCurrentGroups))
                {
                    // Get ID for page
                    $intPage = str_replace(array("{{link_url::", "}}"), array("", ""), $value["lr_redirecturl"]);
                    // Load Page
                    $arrPage = $this->Database->prepare("SELECT * FROM tl_page WHERE id=?")->execute((int) $intPage)->fetchAllAssoc();

                    //Check if we have a page
                    if (count($arrPage) == 0)
                    {
                        $this->log("Try to redirect, but the necessary page cannot be found in the database.", __FUNCTION__ . " | " . __CLASS__, TL_ERROR);
                    }
                    else
                    {
                        $this->redirect($this->generateFrontendUrl($arrPage[0]));
                    }
                }
            }   
            else if ($value["lr_id"] == "all")
            {
                // Get ID for page
                $intPage = str_replace(array("{{link_url::", "}}"), array("", ""), $value["lr_redirecturl"]);
                // Load Page
                $arrPage = $this->Database->prepare("SELECT * FROM tl_page WHERE id=?")->execute((int) $intPage)->fetchAllAssoc();

                //Check if we have a page
                if (count($arrPage) == 0)
                {
                    $this->log("Try to redirect, but the necessary page cannot be found in the database.", __FUNCTION__ . " | " . __CLASS__, TL_ERROR);
                }
                else
                {
                    $this->redirect($this->generateFrontendUrl($arrPage[0]));
                }
            }
            else
            {
                $this->log("Try to redirect, but the type of user/group is unknown: " . $value['lr_id'], __FUNCTION__ . " | " . __CLASS__, TL_ERROR);
            }
        }

        return;
    }
    
    /** ------------------------------------------------------------------------
     * Helper
     */
    
    /**
     * Look up a member name or group name
     * @param string $strID
     * @return string 
     */
    private function lookUpName($strID)
    {
        if (strpos($strID, "M") !== FALSE)
        {
            $strID = str_replace("M::", "", $strID);
            
            $objUser = $this->Database->prepare("SELECT * FROM tl_member WHERE id=?")->execute($strID);
            
            if($objUser->numRows == 0)
            {
                return $GLOBALS['TL_LANG']['ERR']['lr_error_unknownMember'];
            }
            else
            {
                if (strlen($objUser->firstname) != 0 && strlen($objUser->lastname) != 0)
                {
                    return $objUser->firstname . " " . $objUser->lastname;
                }
                else
                {
                    return $objUser->username;
                }
            }
        }
        else if (strpos($strID, "G") !== FALSE)
        {
            $strID = str_replace("G::", "", $strID);
            
            $objGroup = $this->Database->prepare("SELECT * FROM tl_member_group WHERE id=?")->execute($strID);
            
            if($objGroup->numRows == 0)
            {
                return $GLOBALS['TL_LANG']['ERR']['lr_error_unknownGroup'];
            }
            else
            {
                return $objGroup->name;
            }            
        }
        else if ($strID == "all")
        {
            return $GLOBALS['TL_LANG']['tl_content']['lr_all'];
        }
        else
        {
            return $GLOBALS['TL_LANG']['ERR']['lr_error_unknownType'];
        }
    }
    
    /**
     * Look up a page title
     * 
     * @param string $strID
     * @return string 
     */
    private function lookUpPage($strID)
    {
        $strID = str_replace(array("{{link_url::", "}}"), array("", ""), $strID);
        $arrPage = $this->Database->prepare("SELECT * FROM tl_page WHERE id=?")->execute((int) $strID)->fetchAllAssoc();

        if (count($arrPage) == 0)
        {
            return array(
                "title" => $GLOBALS['TL_LANG']['ERR']['lr_error_unknownPage'],
                "link" => ""
            );
        }
        else
        {
            return array(
                "title" => $arrPage[0]["title"] . " - " . $arrPage[0]["pageTitle"],
                "link" => $this->generateFrontendUrl($arrPage[0])
            );
        }
    }

}

?>