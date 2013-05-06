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
 * Class LoginRedirectsCallback
 *
 * Callback Class for Backend
 *
 * @copyright  Kirsten Roschanski (C) 2013
 * @copyright  MEN AT WORK (C) 2011
 * @author     Kirsten Roschanski <kirsten@kat-webdesign.de>
 * @author     Andreas Isaak <cms@men-at-work.de> 
 * @author     David Maack <cms@men-at-work.de>
 * @author     Stefan Heimes <cms@men-at-work.de>
 */
 
class LoginRedirectsCallback extends \Backend implements \executable
{
  
    /**
     * Return all active members/fe32760
     * groups with id and name.
     * 
     * @return array 
     */
    public function getSelection()
    {
        $arrReturn = array();
        $arrReturn["all"] = $GLOBALS['TL_LANG']['tl_content']['lr_all'];
        $arrReturn["allmembers"] = $GLOBALS['TL_LANG']['tl_content']['lr_allmembers'];
        $arrReturn["guestsonly"] = $GLOBALS['TL_LANG']['tl_content']['lr_guestsonly'];
        

        // Groups
        
        $arrMemberGroups = $this->Database->prepare("SELECT * FROM tl_member_group WHERE disable != 1 ORDER BY name")->execute()->fetchAllAssoc();

        foreach ($arrMemberGroups as $key => $value)
        {
            $arrReturn[$GLOBALS['TL_LANG']['tl_content']['lr_groups']]["G::" . $value["id"]] = $value["name"];
        }

        // Members
        
        $arrMember = $this->Database->prepare("SELECT * FROM tl_member WHERE locked != 1 ORDER BY firstname, lastname")->execute()->fetchAllAssoc();

        foreach ($arrMember as $key => $value)
        {
            if (strlen($value["firstname"]) != 0 && strlen($value["lastname"]) != 0)
            {
                $arrReturn[$GLOBALS['TL_LANG']['tl_content']['lr_members']]["M::" . $value["id"]] = $value["firstname"] . " " . $value["lastname"];
            }
            else
            {
                $arrReturn[$GLOBALS['TL_LANG']['tl_content']['lr_members']]["M::" . $value["id"]] = $value["username"];
            }
        }

        return $arrReturn;
    }

    /**
     * Check if a member or group is chosen twice.
     * 
     * @param string $varVal
     * @param DataContainer $dc
     * @return string 
     */
    public function checkSelection($varVal, DataContainer $dc)
    {
        $arrValue = deserialize($varVal);
        $arrValueFound = array();
        
        // Check duplicates
        foreach ($arrValue as $key => $value)
        {
            if (in_array($value["lr_id"], $arrValueFound))
            {
                $_SESSION["TL_ERROR"][] = $GLOBALS['TL_LANG']['ERR']['lr_duplicate'];
            }
            else
            {
                $arrValueFound[] = $value["lr_id"];
            }
        }
        
        return serialize($arrValue);
    }

}
