<?php

	// WLAN Enabled Variable triggert dieses Skript
	
	SetValue($_IPS["VARIABLE"],$_IPS["VALUE"]);
	
	// ID dieses WLANs rausfinden
	$parentID = IPS_GetParent($_IPS["VARIABLE"]);
	$idID = IPS_GetVariableIDByName("ID", $parentID);
	$groupID = GetValue($idID);
	
	$config["enabled"] = $_IPS["VALUE"];
	$parentID = IPS_GetParent($_IPS["SELF"]);
	UniFi_SetWLANConfig($parentID,$groupID,$config)

?>