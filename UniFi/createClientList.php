<?php

$macList = array(
			array("Name" => "Mein Device 1", "MAC" => "11:22:33:44:55:66"),
			array("Name" => "Mein Device 2", "MAC" => "11:22:33:44:55:66", "PresentVarID" => 12345),
			);

// Eine weitere Lockup Tabelle für die Accesspoints und deren Alias.
$apList = array(
			array("Name" => "Dachboden", "MAC" => "24:a4:3c:be:32:3e"),
			array("Name" => "Flur unten", "MAC" => "24:a4:3c:a2:ed:38"),
			array("Name" => "Garten", "MAC" => "24:a4:3c:a2:f0:a6"),
			);

// ----------------------------------------------------------------------------------
// Ab hier dann bitte nichts mehr ändern
// ----------------------------------------------------------------------------------
include_once("../modules/KH_UniFi/UniFi/orga.php");

$parentID = IPS_GetParent($_IPS["SELF"]);

$htmlBoxID = IPS_GetVariableIDByName("ClientHTMLBox",$parentID);
$clientList = UniFi_GetClients($parentID);

$scriptResult = "";
		echo print_r($clientList);

if (is_object($clientList))
{
	foreach($clientList->data as $client)
	{
		echo print_r($client);
		
		$scriptResult .= "<tr style='height:20px;font-size:12px;'>";
		
		// Name über MACNummer rausfinden.
		// Nicht alle Geräte senden anständig ihren Namen mit. Deswegen über Liste arbeiten
		$clientName = "";
		foreach($macList as $key => $entry)
		   if ($entry["MAC"] == $client->mac)
			{
				$clientName = $entry["Name"];
				break;
			}

		// War nicht in Liste!
		if ($clientName == "")
				$clientName = $client->hostname;

		$scriptResult .= "<td>".$clientName."</td>";

		$scriptResult .= "<td style='text-align:center'>".$client->mac."</td>";
		$scriptResult .= "<td style='text-align:center'>".$client->ip."</td>";
		
		// Accesspoints übersetzen
		$apName = "";
		
		foreach($apList as $ap)
		   if ($ap["MAC"] == $client->ap_mac)
		   {
				$apName = $ap["Name"];
				break;
			}

		// War nicht in Liste!
		if ($apName == "")
			$apName = $client->ap_mac;


		$ident = str_replace(":","",$client->mac);
		$rootCatID = CreateCategory("Clients", "Clients" , $parentID);
		$catID = CreateCategory($clientName, $ident."_name" , $rootCatID);
		CreateVariable("MAC", 3,$client->mac , $ident."_mac", $catID);
		CreateVariable("IP", 3,$client->ip , $ident."_ip", $catID);
		CreateVariable("AP Name", 3,$apName , $ident."_apname", $catID);
		CreateVariable("Signal", 1,$client->signal , $ident."_signal", $catID);
		CreateVariable("Radio", 3,$client->radio , $ident."_radio", $catID);
		CreateVariable("TX Bytes", 1,$client->tx_bytes , $ident."_txbytes", $catID);
		CreateVariable("RX Bytes", 1,$client->rx_bytes , $ident."_rxbytes", $catID);
		CreateVariable("Uptime", 1,$client->uptime , $ident."_uptime", $catID);



		$scriptResult .= "<td style='text-align:center'>$apName</td>";
		$scriptResult .= "<td style='text-align:center'>".$client->essid."</td>";
		$scriptResult .= "<td style='text-align:center'><div style='width:100%;height:20px;overflow:hidden;'>";
		
		// Pegelumwandlung - Entspricht leider nicht dem Wert in der Webconsole. Kein Plan wie die das berechnen
		/* 100% -35 / 50% -65 / 1% -95 */
		if ($client->signal <= -95)
			$percent = 1;
		else if ($client->signal >= -35)
		   $percent = 100;
		else
			$percent = round(($client->signal + 95) / 0.6);

		if ($percent < 10)
			$levelImg = "0";
		else if ($percent <= 20)
			$levelImg = "1";
		else if ($percent <= 40)
			$levelImg = "2";
		else if ($percent <= 60)
			$levelImg = "3";
		else if ($percent <= 80)
			$levelImg = "4";
		else
			$levelImg = "5";

		$scriptResult .= "<img src='user/img/level/level$levelImg.png'> $percent% (Ch.".$client->channel.") ".$client->radio_proto;
		$scriptResult .= "</div></tr>";
		
	}
}

// ------------------------------------------------
// Anwesenheitserkennung (Testinstallation)
// ------------------------------------------------
foreach($macList as $key => $entry)
{
      $macList[$key]["Present"] = false;
}

if (is_object($clientList))
{
	foreach($clientList->data as $client)
	{

		// Name über MACNummer rausfinden.
		// Nicht alle Geräte senden anständig ihren Namen mit. Deswegen über Liste arbeiten
		$found = false;
		foreach($macList as $key => $entry)
		{
		   if ($entry["MAC"] == $client->mac)
			  $macList[$key]["Present"] = true;
		}
	}
}

foreach($macList as $key => $entry)
{
	// Nur einen Wert setzen, wenn auch ein Target vorhanden ist
	if (isset($entry["PresentVarID"]))
		SetValue($entry["PresentVarID"],$entry["Present"]);
}

// ------------------------------------------------
// ------------------------------------------------

$htmlBox = "";
$htmlBox .= "<table style='width:100%;font-size:14px;'>";
$htmlBox .= "<tr><td style='text-align:left;font-size:12px;' colspan='10'>Letzte Aktualisierung: ".date("H:i:s d.m.Y")."</td></tr>";
$htmlBox .= "<tr style='height:1px;'><td style='background-color:#aaaaaa;' colspan='10'></td></tr>";

if ($scriptResult == "")
	$htmlBox .= "<tr><td style=''>Keine Clients im WLAN</td></tr>";
else
{
	$htmlBox .= "<tr><th>Name</th><th>MAC</th><th>IP</th><th>AP</th><th>WLAN</th><th>Pegel</th></tr>";
	$htmlBox .= $scriptResult;
}

SetValue($htmlBoxID,$htmlBox);

?>
