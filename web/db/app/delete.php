<?php

/**
 *
 * 	DELETE functions
 * 
 */

function deleteFeature($db, $id, $data){
	$response = baseResponse("success", 'feature inserted successfully', array());
	$featInsert = array( 'creative_id' => $data['creative_id'], 'features_list_id' => $data['features_list_id']);

	// $chk = $db->select('creative_features', 'id', $featInsert);

	$delStat = $db->delete( 'creative_features', $featInsert );

	// if($chk['status'] != 'success'){
	// 	$insStat = $db->insert('creative_features', $featInsert, array() );
	// 	if($insStat['status'] != 'success'){
	// 		$response['message'] = 'could not delete feature';
	// 		$response['data'] = $insStat['data'];
	// 	}
	// } else {
	// 	$response['message'] = 'entrey exist';
	// 	$response['data'] = $chk['data'];
	// }

	return $delStat;
}

function deleteCampaignTag($db, $id, $data){
	$tags_table = 'tag_list';
	$campaing_tags_table = 'campaign_tags';
	$response = baseResponse("success", 'tags deleted successfully', array());

	

	$singletags = array_map('trim', explode(',', $data['tags']));
	$successTags = array();
	$failedTags = array();

	// print_r($singletags);
	// print_r($data);

	foreach ( $singletags as $tag ) {
		$tagID = getTagbyName($db, $tag);
		$deleteTag = array( 'tag_list_id' => $tagID['data'][0]['id'], 'campaign_id' => $id);
		$delStat = $db->delete( $campaing_tags_table, $deleteTag );

		if($delStat["status"] == "success"){
			array_push($successTags, $tag);
		} else{
			array_push($failedTags, $tag);
		}
	}

	if( count($successTags) !=  count($singletags) ){
		$response['message'] = 'some tags did not get deleted';
		$response['data']['failed'] = $failedTags;
	}

	$response['data']['success'] = $successTags;


	return $response;
}

