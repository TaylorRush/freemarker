<?php

/**
 *
 * 	UPDATE functions
 * 
 */

function updateCampaign($db, $id, $data){
	$results = $db->update( 'campaigns', $data, array("id" => $id), array() );
	return $results;
}


function updateCreative($db, $id, $data){
	$results = $db->update( 'creatives', $data, array("id" => $id), array() );
	return $results;

}

function updateClientByID($db, $id, $data){
	$results = $db->update( 'clients', $data, array("id" => $id), array() );
	return $results;
}
