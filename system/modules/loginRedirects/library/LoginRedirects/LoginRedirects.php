<?php

/**
 * loginRedirects for Contao Open Source CMS
 *
 * Copyright (C) 2013 Kirsten Roschanski
 * Copyright (C) 2011 MEN AT WORK <http://www.men-at-work.de/> 
 *
 * @package    loginRedirects
 * @license    http://opensource.org/licenses/lgpl-3.0.html LGPL
 */

namespace MENATWORK\LoginRedirects;


/**
 * Class LoginRedirects
 *
 * Content Element for Contao 
 *
 * @copyright  Kirsten Roschanski (C) 2013
 * @copyright  MEN AT WORK (C) 2011
 * @author     Kirsten Roschanski <kirsten@kat-webdesign.de>
 * @author     Andreas Isaak <cms@men-at-work.de> 
 * @author     David Maack <cms@men-at-work.de>
 * @author     Stefan Heimes <cms@men-at-work.de>
 */

class LoginRedirects extends \ContentElement
{

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
            if (count($arrRedirect) > 0)
            {
                foreach ($arrRedirect as $key => $value)
                {
                    $arrWildcard[] = '<tr>';

                    $arrWildcard[] = '<td>';
                    $arrWildcard[] = ++$i . ". " .  $this->lookUpName($value["lr_id"]);
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
            }
            else
            {
                $arrWildcard[] = '<tr><td>'.$GLOBALS['TL_LANG']['tl_content']['lr_noentries'] .'</td></tr>';
            }

            $arrWildcard[] = '</table>';

            $objTemplate = new \BackendTemplate('be_wildcard');

            $objTemplate->wildcard = implode("\n", $arrWildcard);
            $objTemplate->title = $this->headline;
            $objTemplate->id = $this->id;
            $objTemplate->link = $this->name;
            $objTemplate->href  = 'contao/main.php?do=themes&amp;table=tl_module&amp;act=edit&amp;id=' . $this->id;

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
        $this->import('FrontendUser', 'User');

        // Get settings
        $arrRedirect = deserialize($this->lr_choose_redirect, true);

        //return if the array is empty
        if (count($arrRedirect) == 0) return;

        // Get usergroups
        $arrCurrentGroups = (is_array($this->User->groups))? $this->User->groups : array();

        // Build group and members array
        foreach ($arrRedirect as $key => $value)
        {
            $redirect = false;
            $arrId = explode("::", $value['lr_id']);

            switch ($arrId[0])
            {
                case 'G':
                    //redirect if the user is in the correct group
                    if (in_array($arrId[1], $arrCurrentGroups)) $redirect = true;
                    break;
                case 'M':
                    //redirect if the FE-User id is found
                    if ($this->User->id == $arrId[1]) $redirect = true;
                    break;
                case 'allmembers':
                    //redirect if we have a valid FE-User
                    if ($this->User->id != '') $redirect = true;
                    break;
                case 'guestsonly':
                    //skip loop if we have a user-id
                    if ($this->User->id == '') $redirect = true;
                    break;
                case 'all':
                    //no test, just redirect:)
                    $redirect = true;
                    break;
            }

            if ($redirect)
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
                    $arrCurrentPage = $GLOBALS['objPage']->fetchAllAssoc();                                        
                    if($arrCurrentPage[0]['id'] != $arrPage[0]['id'])
                    {
                         $this->redirect($this->generateFrontendUrl($arrPage[0]));
                    }
                }
            }
        }
        
        return;
    }
    
    /**
     * Helper
     */
    
    /**
     * Look up a member name or group name
     * @param string $strID
     * @return string 
     */
    private function lookUpName($strID)
    {
        switch ($strID){
            case 'all':
            case 'allmembers':
            case 'guestsonly':
                    return $GLOBALS['TL_LANG']['tl_content']['lr_'.$strID];
                break;
            default:
                $strID = explode("::", $strID);
                if ($strID[0] == "M")
                {
                    $strID = $strID[1];

                    $objUser = $this->Database->prepare("SELECT * FROM tl_member WHERE id=?")->limit(1)->execute($strID);

                    if($objUser->numRows == 0)
                    {
                        return $GLOBALS['TL_LANG']['ERR']['lr_unknownMember'];
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
                else if ($strID[0] == "G")
                {
                    $strID = $strID = $strID[1];

                    $objGroup = $this->Database->prepare("SELECT * FROM tl_member_group WHERE id=?")->limit(1)->execute($strID);

                    if($objGroup->numRows == 0)
                    {
                        return $GLOBALS['TL_LANG']['ERR']['lr_unknownGroup'];
                    }
                    else
                    {
                        return $objGroup->name;
                    }            
                }
                break;
        }
        return $GLOBALS['TL_LANG']['ERR']['lr_unknownType'];
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
                "title" => $GLOBALS['TL_LANG']['ERR']['lr_unknownPage'],
                "link" => ""
            );
        }
        else
        {
            return array(
                "title" => $arrPage[0]["title"] . ((strlen($arrPage[0]["pageTitle"]) != 0) ? " - " . $arrPage[0]["pageTitle"] : ""),
                "link" => $this->generateFrontendUrl($arrPage[0])
            );
        }
    }

}
