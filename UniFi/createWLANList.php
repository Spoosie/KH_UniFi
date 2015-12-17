<?php

include_once("../modules/KH_UniFi/UniFi/orga.php");

$parentID = IPS_GetParent($_IPS["SELF"]);
$wlanList = UniFi_GetWLANConfig($parentID);
$setWLANID = IPS_GetScriptIDByName ("setWLAN", $parentID );

foreach($wlanList->data as $wlan)
{
	$ident = $wlan->_id;

	$rootCatID = CreateCategory("WLAN", "WLAN" , $parentID);
	$catID = CreateCategory($wlan->name, $ident , $rootCatID);
	CreateVariable("ID", 3,$wlan->_id , $ident."_id", $catID);
	CreateVariable("Enabled", 0,$wlan->enabled , $ident."_enabled", $catID);

	$enabledID = IPS_GetVariableIDByName("Enabled",$catID);
	IPS_SetVariableCustomAction($enabledID, $setWLANID);
	IPS_SetVariableCustomProfile($enabledID, "~Switch");
	
	CreateVariable("Security", 3,$wlan->security , $ident."_security", $catID);
}

?>