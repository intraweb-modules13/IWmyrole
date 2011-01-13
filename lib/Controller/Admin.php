<?php

/**
 * Main admin function. Create admin interface
 * @author: 	Josep FerrÃ ndiz (jferran6@xtec.cat)
 * @author:     Albert PÃ©rez Monfort (aperezm@xtec.cat)
 * @param:	Array with the id of the group that can change roles
 * @return:	Admin main page
*/
function IWmyrole_admin_main($args)
{
	$dom = ZLanguage::getModuleDomain('IWmyrole');
	$gid = FormUtil::getPassedValue('gid', isset($args['gid']) ? $args['gid'] : null, 'POST');	

	// Security check
	if (!SecurityUtil::checkPermission('IWmyrole::', "::", ACCESS_ADMIN)) {
		return LogUtil::registerError(__('Sorry! No authorization to access this module.', $dom), 403);
	}

	$groupsNotChangeable = ModUtil::getVar('IWmyrole','groupsNotChangeable');

	$sv = ModUtil::func('IWmain', 'user', 'genSecurityValue');
	$groups = ModUtil::func('IWmain', 'user', 'getAllGroups', array('sv' => $sv,
																	'less' => ModUtil::getVar('IWmyrole', 'rolegroup')));
																	
	foreach($groups as $group){
		$checked = false;
		
		if(strpos($groupsNotChangeable,'$'.$group['id'].'$') != false){
			$checked = true;	
		}
		
		$groupsArray[] = array('id' => $group['id'],
								'name' => $group['name'],
								'checked' => $checked);
	}

	$view = Zikula_View::getInstance('IWmyrole',false);
	
	// Gets the groups
	$sv = ModUtil::func('IWmain', 'user', 'genSecurityValue');
	$groups = ModUtil::func('IWmain', 'user', 'getAllGroups', array('sv' => $sv));

	$view -> assign('roleGroup', ModUtil::getVar('IWmyrole', 'rolegroup'));
	$view -> assign('groups', $groups);
	$view -> assign('groupsArray', $groupsArray);
	
	return $view -> fetch('IWmyrole_admin_main.htm');
}

/**
 * Show module information
 * @author: 	Josep FerrÃ ndiz (jferran6@xtec.cat)
 * @author:     Albert PÃ©rez Monfort (aperezm@xtec.cat)
 * @param:	none
 * @return:	Module information
*/
// InformaciÃ³ del mÃ²dul
function IWmyrole_admin_module()
{
	$dom = ZLanguage::getModuleDomain('IWmyrole');
	// Security check
	if (!SecurityUtil::checkPermission('IWmyrole::', "::", ACCESS_ADMIN)) {
		return LogUtil::registerError(__('Sorry! No authorization to access this module.', $dom), 403);
	}

	$module = ModUtil::func('IWmain', 'user', 'module_info', array('module_name' => 'IWmyrole',
										'type' => 'admin'));

	$view = Zikula_View::getInstance('IWmyrole',false);
	$view -> assign('module', $module);
	return $view -> fetch('IWmyrole_admin_module.htm');
} 

/**
 * Change the group that can change roles
 * @author: 	Josep FerrÃ ndiz (jferran6@xtec.cat)
 * @author:     Albert PÃ©rez Monfort (aperezm@xtec.cat)
 * @param:	Array with the id of the group that can change roles
 * @return:	
*/
// Canvia a la taula de permisos els relatius al IWmyrole
function IWmyrole_admin_changeGroup($args){
	$dom = ZLanguage::getModuleDomain('IWmyrole');
	$gid = FormUtil::getPassedValue('gid', isset($args['gid']) ? $args['gid'] : null, 'POST');
	$groups = FormUtil::getPassedValue('groups', isset($args['groups']) ? $args['groups'] : null, 'POST');
	
	// Security check
	if (!SecurityUtil::checkPermission('IWmyrole::', "::", ACCESS_ADMIN)) {
		return LogUtil::registerError(__('Sorry! No authorization to access this module.', $dom), 403);
	}

	// Confirm authorisation code
	if (!SecurityUtil::confirmAuthKey()) {
		return LogUtil::registerAuthidError (ModUtil::url('IWmyrole', 'admin', 'main'));
	}
	
	$groupsString = '$';
	foreach($groups as $group){
		$groupsString .= '$'.$group.'$';
	}

	if ($gid) {
	// Modify the permissions in group_perms
		$changePerms = ModUtil::apiFunc ('IWmyrole', 'admin', 'changePermissions', array('gid'=> $gid));
		if ($changePerms) {
			//Update module var with new value
			ModUtil::setVar('IWmyrole', 'rolegroup', $gid);
			Logutil::registerStatus(__('The group change has been made.', $dom));
		} else {
			Logutil::registerError(__('The group change has not been made.', $dom));
		}
	}
	
	ModUtil::setVar('IWmyrole','groupsNotChangeable', $groupsString);
	
	return System::redirect(ModUtil::url('IWmyrole', 'admin', 'main'));

}

/**
 * Change user groups
 * @author: 	Josep FerrÃ ndiz (jferran6@xtec.cat)
 * @author:     Albert PÃ©rez Monfort (aperezm@xtec.cat)
 * @param:	args   Array with the groups id's
 * @return:	True if success or false otherwise
*/
function IWmyrole_admin_changeRole($args)
{
	$dom = ZLanguage::getModuleDomain('IWmyrole');
	// Get the parameters
	$roles = FormUtil::getPassedValue('roles', isset($args['roles']) ? $args['roles'] : null, 'POST');
	$setDefault = FormUtil::getPassedValue('setDefault', isset($args['setDefault']) ? $args['setDefault'] : null, 'POST');

	// Security check
	if (!SecurityUtil::checkPermission('IWmyrole::', "::", ACCESS_ADMIN)) {
		return LogUtil::registerError(__('Sorry! No authorization to access this module.', $dom), 403);
	}

	//Check if the group that can change roles have admin permisions. If not the block is not showed
	$correctGroupPermissions = ModUtil::apiFunc('IWmyrole', 'admin', 'correctGroupPermissions');

	if(!$correctGroupPermissions){
		$sv = ModUtil::func('IWmain', 'user', 'genSecurityValue');
		ModUtil::func('IWmain', 'user', 'userSetVar', array('uid' => UserUtil::getVar('uid'),
									'name' => 'invalidChange',
									'module' => 'IWmyrole',
									'lifetime' => 10,
									'nult' => true,
									'value' => 1,
									'sv' => $sv));
		return System::redirect($_SERVER['HTTP_REFERER']);
	}

	$uid = UserUtil::getVar('uid');

	//get the headlines saved in the user vars. It is renovate every 10 minutes
	$sv = ModUtil::func('IWmain', 'user', 'genSecurityValue');
	$exists = ModUtil::apiFunc('IWmain', 'user', 'userVarExists', array('name' => 'defaultRoles',
											'module' => 'IWmyrole',
											'uid' => $uid,
											'sv' => $sv));

	if(!$exists){
		//get user groups
		$sv = ModUtil::func('IWmain', 'user', 'genSecurityValue');
		$userGroups = ModUtil::func('IWmain', 'user', 'getAllUserGroups', array('sv'=> $sv,
												'uid'=> $uid));
		$i = 0;
		foreach($userGroups as $group){
			$groups .= $group['id'].'$$';
			$i++;
		}

		//set default roles
		$sv = ModUtil::func('IWmain', 'user', 'genSecurityValue');
		ModUtil::func('IWmain', 'user', 'userSetVar', array('uid' => $uid,
									'name' => 'defaultRoles',
									'module' => 'IWmyrole',
									'sv' => $sv,
									'value' => $groups));
	}

	if($setDefault == 1){
		$i = 0;
		foreach($roles as $group){
			$groups .= $group['id'].'$$';
			$i++;
		}
		//set default roles
		$sv = ModUtil::func('IWmain', 'user', 'genSecurityValue');
		ModUtil::func('IWmain', 'user', 'userSetVar', array('uid' => $uid,
									'name' => 'defaultRoles',
									'module' => 'IWmyrole',
									'sv' => $sv,
									'value' => $groups));
	}

	//Check if the group that can change roles have admin permisions. If not the block is not showed
	$correctGroupPermissions = ModUtil::apiFunc('IWmyrole', 'admin', 'correctGroupPermissions');

	if(!$correctGroupPermissions){
		return System::redirect(ModUtil::url());
	}

	// Esborrem la pertinenÃ§a a tots els grups excepte el de canvia de rol
	$delGroups = ModUtil::apiFunc('IWmyrole', 'admin', 'delUserGroups');

	if ($delGroups) {
		$addToGroup = ModUtil::apiFunc('IWmyrole', 'admin', 'addUserToGroup', array('roles' => $roles));
	}

	if(!$delGroups || !$addToGroup){
		LogUtil::registerError (__('Error in the role change', $dom));
	}

	ModUtil::func('IWmain', 'user', 'regenBlockNews', array('sv' => $sv));
	return true;
}

/**
 * Reset user groups membership
 * @author: 	Josep FerrÃ ndiz (jferran6@xtec.cat)
 * @author:     Albert PÃ©rez Monfort (aperezm@xtec.cat)
 * @param:	none
 * @return:	True if success or false otherwise
*/
function IWmyrole_admin_resetRoles($args)
{
	$dom = ZLanguage::getModuleDomain('IWmyrole');
	// Security check
	if (!SecurityUtil::checkPermission('IWmyrole::', "::", ACCESS_ADMIN)) {
		return LogUtil::registerError(__('Sorry! No authorization to access this module.', $dom), 403);
	}

	//get the headlines saved in the user vars. It is renovate every 10 minutes
	$sv = ModUtil::func('IWmain', 'user', 'genSecurityValue');
	$exists = ModUtil::apiFunc('IWmain', 'user', 'userVarExists', array('name' => 'defaultRoles',
											'module' => 'IWmyrole',
											'uid' => UserUtil::getVar('uid'),
											'sv' => $sv));
	if(!$exists){
		$sv = ModUtil::func('IWmain', 'user', 'genSecurityValue');
		ModUtil::func('IWmain', 'user', 'userSetVar', array('uid' => UserUtil::getVar('uid'),
									'name' => 'invalidChange',
									'module' => 'IWmyrole',
									'lifetime' => 10,
									'nult' => true,
									'value' => 1,
									'sv' => $sv));
		return System::redirect($_SERVER['HTTP_REFERER']);

	}

	// Esborrem la pertinenÃ§a a tots els grups excepte el de canvia de rol
	$delGroups = ModUtil::apiFunc('IWmyrole', 'admin', 'delUserGroups');

	// Esborrem la pertinenÃ§a a tots els grups excepte el de canvia de rol
	ModUtil::apiFunc('IWmyrole', 'admin', 'addUserToGroup', array('defaultRoles' => 1));

	ModUtil::func('IWmain', 'user', 'regenBlockNews', array('sv' => $sv));
	return true;
}
