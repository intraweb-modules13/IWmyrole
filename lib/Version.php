<?php
 /**
 * Load the module version information
 *
 * @author		Albert Pérez Monfort (aperezm@xtec.cat)
 * @return		The version information
 */
$dom = ZLanguage::getModuleDomain('IWmyrole');
$modversion['name'] = 'IWmyrole';
$modversion['version'] = '2.0';
$modversion['description'] = __('Allow users to change their roles or groups.', $dom);
$modversion['displayname'] = __('myRole', $dom);
$modversion['url']=__('IWmyrole', $dom);
$modversion['credits'] = 'pndocs/credits.txt';
$modversion['help'] = 'pndocs/help.txt';
$modversion['changelog'] = 'pndocs/changelog.txt';
$modversion['license'] = 'pndocs/license.txt';
$modversion['official'] = 0;
$modversion['author'] = 'Albert Pérez Monfort & Josep Ferràndiz Farré';
$modversion['contact'] = 'aperezm@xtec.es & jferran6@xtec.cat';
$modversion['admin'] = 1;
$modversion['securityschema'] = array('IWmyrole::' => '::');
$modversion['dependencies'] = array(array('modname' => 'IWmain',
						'minversion' => '2.0',
						'maxversion' => '',
						'status' => ModUtil::DEPENDENCY_REQUIRED));
