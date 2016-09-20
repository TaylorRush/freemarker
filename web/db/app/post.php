<?php


/**
 *
 * 	POST functions
 * 
 */

function processCreatives($db, $campID, $data){
	$crvs = $data['creatives'];
	$response = baseResponse('success', 'creatives insert successful', array());
	foreach ( $crvs as $typeKey => $val ) {
		foreach ( $val as $crv) {
			$crv['type'] = $typeKey;
			$stat = insertCreative($db, $campID, $crv);
			array_push( $response['data'], $stat);
		}
	}
	return $response;
}

function processPreviews($db, $campID, $data){
	$crvs = $data['creatives'];
	$rev = $data['revision'];
	$server = ( array_key_exists( 'server', $data ) ) ? $data['server'] : 'QT'; 
	$baseURL = $data['client_name'].'/'.$data['campaign_name'].'/rev'.$rev.'/';
	$response = baseResponse('success', 'previews inserted successful', array());
	foreach ( $crvs as $typeKey => $val ) {
		foreach ( $val as $crv) {
			//creatives
			$crv['type'] = $typeKey;
			$crvID = insertCreative($db, $campID, $crv);

			$prvData = array('creatives_id'=> $crvID, 'campaigns_id' => $campID, 'rev' => $rev, 'server' => $server);
			$prvData['url_path'] =  ( array_key_exists( 'html_name', $crv ) ) ? $baseURL.$crv['name'].'/'.$crv['html_name'].$crv['extension'] : $baseURL.$crv['name'].$crv['extension'];
			$prvID = insertPreview($db, $prvData);
			array_push( $response['data'], $prvID);
		}
	}

	// if (count($response['data']) > 0){
	// 	$lastRev = $db->select('campaigns', 'preview_rev', array('id'=>$campID));
	// 	if ($lastRev['status'] =='success' &&  $lastRev['data'] < $rev){
	// 		$db->update( 'campaigns', array('preview_rev'=>$rev), array("id" => $id), array() );
	// 	}
	// }

	return $response;

}

function insertPreview($db, $data){
	$unique = validateUnique($db, 'previews', $data);
	if($unique['valid']){
		$insStat = $db->insert('previews', $data, array() );
		return $insStat['data'];
	}
	return $unique['data']['id'];
}

function insertCreative($db, $campID, $data){
	$uCrv = array('name' => $data['name'], 'campaigns_id' => $campID, 'extension' => $data['extension']);
	$unique = validateUnique($db, 'creatives', $uCrv);
	$crv_id;
	// print $unique['valid'];
	if($unique['valid']){
		// insert creative
		$crvData = array();
		$crvData['name'] = $data['name'];
		$crvData['campaigns_id'] = $campID;
		$crvData['sizes_list_id'] = getSizeID($db, $data['dimensions']['width'].'x'.$data['dimensions']['height'], true);
		$crvData['type'] = $data['type'];
		$crvData['extension'] = $data['extension'];
		$crvData['html_name'] = ( array_key_exists( 'html_name', $data )) ? $data['html_name'] : '';

		$insStat = $db->insert('creatives', $crvData, array() );
		if ($insStat['status'] == 'success'){
			$crv_id = $insStat['data'];
			insertCreativeVersion($db, $crv_id, $data['path'], true);
			// insert video feature
			if ( array_key_exists( 'video', $data )){
				if ($data['video']){
					$insVideo = inertFeature($db, $crv_id, 1);
				}
				
			}
			// insert expandable feature
			if ( array_key_exists( 'expandable', $data )){
				if( $data['expandable']){
					$insExp = inertFeature($db, $crv_id, 6);
				}
			}

		} else {
			$crv_id = 0;
		}
		

	} else {
		$crv_id = $unique['data']['id'];
		insertCreativeVersion($db, $crv_id, $data['path']);
	}

	return $crv_id;
}

function insertCreativeVersion($db, $id, $path, $force=false){
	if($path != 'QT' && $path != 'instapreview' || $force){
		$fields = array('creative_id'=>$id, 'path'=>$path);
		$unique = validateUnique($db, 'creative_versions', $fields);
		if($unique['valid']){
			$insStat = $db->insert('creative_versions', $fields, array() );
		}
	}
}

function insertNewSize($db, $data){
	$fields = array('size'=>$data['size']);
	$fields['name'] = ( array_key_exists( 'name', $data )) ? $data['name'] : '';
	$unique = validateUnique($db, 'size_list', $fields);
	if($unique['valid']){
		$insStat = $db->insert('size_list', $fields, array() );
		return $insStat['data'];
	}

	return $unique['data']['id'];
}

function insertClientCampaign($db, $data, $id){
	$unique = validateUnique($db, 'clients', array('id' => $id));
	if(!$unique['valid']){
		$data['client_id'] = $id;
		$ins = insertCampaign($db, $data);
		return $ins;
	} 

	return baseResponse('error', 'client id is not valid');
}



function insertCampaign($db, $data){	
	if(!array_key_exists('client_id', $data) && !array_key_exists( 'client_name', $data) ){
		return baseResponse('error', 'missing client_id or client_name');
	}

	$customer = makeCustomer($data);
	$cID = (array_key_exists( 'client_id', $data)) ? $data['client_id'] :  getClientIDbyName($db, $customer, true);
	if ($cID == null){
		return baseResponse('error', 'could not get client_id');
	}
	$date = new DateTime();
	$cData = array();
	$cData['client_id'] = $cID;
	$cData['campaign_name'] = $data['campaign_name'];
	$cData['tw_id'] = $data['tw_id'];
	$cData['server_name'] = $data['server_name'];

	$cData['phnx_id'] 		= ( array_key_exists('phnx_id', $data) )		? $data['phnx_id'] 		: 0		;
	$cData['status'] 		= ( array_key_exists('status', $data) )			? $data['status'] 		: 'active';
	$cData['start_date'] 	= ( array_key_exists('start_date', $data) )		? $data['start_date'] 	: $date->format('Y-m-d');
	$cData['pub_date'] 		= ( array_key_exists('pub_date', $data) )		? $data['pub_date'] 	: null;
	$cData['owner'] 		= ( array_key_exists('owner', $data) )			? $data['owner'] 		: 'LIN';
	$cData['project_size'] 	= ( array_key_exists('project_size', $data) )	? $data['project_size'] : null;
	$cData['close_date'] 	= ( array_key_exists('close_date', $data) )		? $data['close_date'] 	: $cData['pub_date'];


	$req = array('client_id','tw_id','server_name', 'campaign_name');

	$pass = verifyRequiredParams($cData, $req);
	if($pass['status'] != 'success'){
		return $pass;
	}

	
	$response = $db->insert('campaigns', $cData, array() );
	return $response;
}

/**
 * [insertClient description]
 * @param  [type] $db   database connection
 * @param  [array] $data must have a key of 'client_name'
 * @return [array]       array base response
 */
function insertClient($db, $data){
	$response = baseResponse('success', 'inserted');
	if(array_key_exists('client_name', $data)){
		$uArr = array('client_name' => $data['client_name']);
		$unique = validateUnique($db, 'clients', $uArr);
		// $data['view_name'] = ( array_key_exists('view_name', $data) ) ? $data['view_name'] : $data['client_name'];
		$data['phnx_customer_id'] =  ( array_key_exists('phnx_customer_id', $data) ) ? $data['phnx_customer_id'] : null;
		if($unique['valid']){
			$insStat = $db->insert('clients', $data, array() );
			$response['data'] = $insStat['data'];
			$response['status'] = $insStat['status'];
			$response['message'] = $insStat['message'];
		} else {
			$response['data'] = $unique['data']['id'];
			$response['message'] = 'existed';
		}
	} else {
		return baseResponse('error', 'missing required key: "client_name"');
	}
	
	return $response;
}

function inertFeature($db, $id, $fID){
	// print_r($data);
	$response = baseResponse("success", 'feature deleted successfully', array());
	$featInsert = array( 'creative_id' => $id, 'features_list_id' => $fID);

	$chk = $db->select('creative_features', 'id', $featInsert);

	if($chk['status'] != 'success'){
		$insStat = $db->insert('creative_features', $featInsert, array() );
		if($insStat['status'] != 'success'){
			$response['message'] = 'could not delete feature';
			$response['data'] = $insStat['data'];
		}
	} else {
		$response['message'] = 'entrey exist';
		$response['data'] = $chk['data'];
	}

	return $response;
}

function insertTags($db, $id, $data){
	
	$tags_table = 'tag_list';
	$campaing_tags_table = 'campaign_tags';
	$response = baseResponse("success", 'tags inserted successfully', array());

	if(is_string($data['tags'])){
		$singletags = array_map('trim', explode(',', $data['tags']));
	} else {
		$singletags = [$data['tags']['tag_name']];
	}
	

	$tagIdsList = array();

	$successTags = array();
	$failedTags = array();
	

	// MAKE SURE ALL TAGS ARE IN TABLE
	foreach ( $singletags as $tag ) {
		$tagArr = array( "tag_name" => "$tag" );
		$tagCheck = getTagbyName($db, $tag);
		if($tagCheck['status'] != "success"){
			$tagInsert = $db->insert( $tags_table, $tagArr, array() );
	
			if($tagInsert['status'] == 'success'){
				$tmp = array('id' => $tagInsert['data'], "tag_name" => $tag);
				array_push( $tagIdsList, $tmp);
			}
		} else{
			//found id
			$tmp = array('id' => $tagCheck['data'][0]['id'], "tag_name" => $tag);
			array_push( $tagIdsList, $tmp );
		}
	}

	foreach ( $tagIdsList as $arr ) {
		$campTags = array( 'tag_list_id' => $arr['id'], 'campaign_id' => $id);
		$checkPivotTag = $db->select( $campaing_tags_table, "id", $campTags );
		if($checkPivotTag['status'] != "success"){
			// not found insert
			$tagPivotInsert = $db->insert( $campaing_tags_table, $campTags, array() );
			if($tagPivotInsert['status'] == "success"){
				array_push($successTags, $arr["tag_name"]);
			} else{
				array_push($failedTags, $arr["tag_name"]);
			}
		} else {
			array_push($successTags, $arr["tag_name"]);
			//report errors
		}
	}
	if( count($successTags) !=  count($singletags) ){
		$response['message'] = 'some tags did not get saved';
		$response['data']['failed'] = $failedTags;
	}

	$response['data']['success'] = $successTags;
	return $response;
}


