<?php

/**
 * Contao Open Source CMS
 * Copyright (C) 2005-2013 Leo Feyer
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
 * @license    LGPL 
 * @filesource
 */
 
/**
 * Table tl_content
 */
// Palettes
$GLOBALS['TL_DCA']['tl_content']['palettes']['loginRedirects'] = '{type_legend},type;{lr_legend},lr_choose_redirect;{protected_legend:hide},protected;{expert_legend:hide},guests,cssID,space;';

// Fields
$GLOBALS['TL_DCA']['tl_content']['fields']['lr_choose_redirect'] = array
(
    'label'                         => $GLOBALS['TL_LANG']['tl_content']['lr_choose_redirect'],
    'exclude'                       => true,
    'inputType'                     => 'multiColumnWizard',
    'save_callback' => array(
        array("LoginRedirects\LoginRedirectsCallback", "checkSelection")
    ),
    'eval' => array(
        'style'                     => 'width:100%;',
        'columnFields' => array(
            'lr_id' => array(
                'label'             => $GLOBALS['TL_LANG']['tl_content']['lr_id'],
                'inputType'         => 'select',
                'options_callback'  => array("LoginRedirects\LoginRedirectsCallback", "getSelection"),
                'eval'              => array('mandatory' => true, 'style' => 'width:210px;', 'includeBlankOption' => true, 'chosen' => true),
            ),
            'lr_redirecturl' => array(
                'label'             => $GLOBALS['TL_LANG']['tl_content']['lr_redirecturl'],
                'exclude'           => true,
                'search'            => true,
                'inputType'         => 'text',
                'eval'              => array('mandatory' => true, 'rgxp' => 'url', 'decodeEntities' => true, 'maxlength' => 255, 'tl_class' => 'w50 wizard', 'style' => 'width:370px;'),
                'wizard' => array(
                    array('tl_content', 'pagePicker')
                )
            )
        )
    ),
    'sql' => "blob NULL"
);
