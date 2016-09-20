<?php

/**
 *
 * 	GET functions
 * 
 */

function getClients($db){
	$results = $db->select('clients', '*', array() );
    return $results;
}

function getClient($db, $id){
	$results = $db->select('clients', '*', array('id' => $id) );
    return $results;
}

function getClientByName($db, $name){
	$response = baseResponse();
	$test = $db->select('clients', '*', array('client_name' => $name) );
	if($test['status'] == 'success'){
		if (count($test['data']) == 1){
			$response['status'] =  $test['status'];
			$response['message'] =  $test['message'];
			$response['data'] = $test['data'][0];
		} else {
			$response = $test;
		}
	} else {
		$response = $test;
	}
    return $response;
}

function getCampaignNameClient($db, $client_id, $name){
	$results = $db->select('campaigns', '*', array('client_id' => $client_id, 'campaign_name' => $name) );
    return $results;
}


function getClientsCampaigns($db){
	// $tables = array('a' => 'campaigns', 'b'=>'clients');
 //    $fields = '*';
 //    $on = array('a_id' => 'client_id', 'b_id' => 'id');
    $pre = getCampaigns($db);
    $results = array();
    $results['data'] = groupByKey($pre['data'], 'view_name');
    $results['status'] = $pre['status'];
    $results['message'] = $pre['message'];

    return $results;
}

function getClientCampaigns($db, $client_id) {
	$results = $db->select('campaigns', '*', array('client_id' => $client_id) );
    return $results;
}

function getCampaigns($db){
    $tables = array('a' => 'campaigns', 'b'=>'clients');
    $fields = 'campaigns.id as campaign_id, campaigns.vertical_id, campaigns.campaign_name, campaigns.server_name, campaigns.status, campaigns.tw_id, campaigns.phnx_id, campaigns.pub_date, campaigns.owner, campaigns.sales_view, campaigns.preview_rev, campaigns.start_date, campaigns.close_date, clients.client_name, clients.view_name, clients.phnx_customer_id';
    $on = array('a_id' => 'client_id', 'b_id' => 'id');
    $res = $db->selectInnerJoin( $tables, $fields, $on );
    return $res;
}

function getCampaignID($db, $id){
    $tables = array('a' => 'campaigns', 'b'=>'clients');
    $fields = '*';
    $on = array('a_id' => 'client_id', 'b_id' => 'id');
    $where = array('joinField' => 'id', 'value' => $id);
    $res = $db->selectInnerJoin( $tables, $fields, $on, $where);
    return $res;
}

function getCampaignByName($db, $name){
	$results = $db->select('campaigns', '*', array('campaign_name' => $name) );
    return $results;
}

function getCampaignCratives($db, $id){
    $tables = array('a' => 'creatives', 'b'=>'size_list');
    $fields = 'creatives.*, size_list.size';
    $on = array('a_id' => 'sizes_list_id', 'b_id' => 'id');
    $where = array('joinField' => 'campaigns_id', 'value' => $id);
    $pre = $db->selectInnerJoin( $tables, $fields, $on, $where);
    return $pre;
}

function getCampaignCrativesFeatures($db, $id){
    $tables = array('a' => 'creatives', 'b'=>'size_list');
    $fields = 'creatives.*, size';
    $on = array('a_id' => 'sizes_list_id', 'b_id' => 'id');
    $where = array('joinField' => 'campaigns_id', 'value' => $id);
    $pre = $db->selectInnerJoin( $tables, $fields, $on, $where);
    return $pre;
}

function getCrativePreviews($db, $id){
   $results = $db->select('previews', 'id as preview_id, rev, url_path', array('creatives_id' => $id) );
    return $results;
}


function getCampaignCrativesByType($db, $id, $fts = false){
	$pre = getCampaignCratives($db, $id);

	if ($fts){
		$newData = array();
		foreach ($pre['data'] as $obj) {
			$crv = getCreativesIDFeatures($db, $obj['id']);
			if ($crv['status'] == 'success'){
				$obj['features'] = $crv['data'];
			}
			$newData[] = $obj;
		}
		$pre['data'] = $newData;
	}

	$res = array('status' => "success");
    $res['data'] = groupByKey($pre['data'], 'type');

    return $res;

}

function getCampaignPreviews($db, $campID){
    $pre = $db->select('previews', '*', array('campaigns_id' => $campID) );
    if($pre['status'] == 'success'){
    	$res = array('status' => "success");
	    $res['data'] = groupByKey($pre['data'], 'rev');
	    return $res;
    } else {
    	 return $pre;
    }
    
}

function getCampaignPreviewsRev($db, $campID, $rev){
    $pre = $db->select('previews', '*', array('campaigns_id' => $campID, 'rev' => $rev) );
    return $pre;
}

function getCreativesID($db, $id){
	$tables = array('a' => 'creatives', 'b'=>'size_list');
    $fields = 'creatives.*, size';
    $on = array('a_id' => 'sizes_list_id', 'b_id' => 'id');
    $where = array('joinField' => 'id', 'value' => $id);
    $pre = $db->selectInnerJoin( $tables, $fields, $on, $where);
    return $pre;
}

function getCreativesIDFeatures($db, $id){
	$tables = array('a' => 'creative_features', 'b'=>'features_list');
    $fields = 'creative_features.id as crv_feat_id, features_list.name, features_list.id as feature_id';
    $on = array('a_id' => 'features_list_id', 'b_id' => 'id');
    $where = array('joinField' => 'creative_id', 'value' => $id);
    $pre = $db->selectInnerJoin( $tables, $fields, $on, $where);
    return $pre;
}

function getPortfolio($db){
	$results = $db->select('creatives', '*', array('portfolio' => 1) );
    return $results;
}

function getCampaignTags($db, $id){
	$tables = array('a' => 'campaign_tags', 'b'=>'tag_list');
    $fields = '*';
    $on = array('a_id' => 'tag_list_id', 'b_id' => 'id');
    $where = array('joinField' => 'campaign_id', 'value' => $id);
    $pre = $db->selectInnerJoin( $tables, $fields, $on, $where);
    return $pre;
}

function getCampaignCreativesFull($db, $id){

	$pre = getCampaignCratives($db, $id);

	$newData = array();
		foreach ($pre['data'] as $obj) {
			$feat = getCreativesIDFeatures($db, $obj['id']);
			$revs = getCrativePreviews($db, $obj['id']);
			if ($feat['status'] == 'success'){
				$obj['features'] = $feat['data'];
			}
			if ($revs['status'] == 'success'){
				$obj['previews'] = $revs['data'];
			}
			$newData[] = $obj;
		}
		$pre['data'] = $newData;
		$res = array('status' => "success");
    	$res['data'] = groupByKey($pre['data'], 'type');

    return $res;
}

function getTagbyName($db, $tag){
	$tagInfo = $db->select('tag_list', '*', array('tag_name' => $tag) );
    return $tagInfo;
}

function getTags($db){
	$results = $db->select('tag_list', '*', array() );
    return $results;
}

function getFeatures($db){
	$results = $db->select('features_list', '*', array() );
    return $results;
}

function getIgnore($db){
	$results = $db->select('ignoreTW_list', 'name', array() );
    return $results;
}

function getSizeID($db, $size, $add = false){
	$sizeArr = array('size' => $size);
	$results = $db->select('size_list', 'id', $sizeArr );
	if($results['status'] != 'success' && $add){
		$inst = insertNewSize($db, $sizeArr);
		return $inst;
	}

    return $results['data'][0]['id'];
}

/**
 * [getClientIDbyName description]
 * @param  [type]  $db   [description]
 * @param  string or array  if array it must match clientLike return function
 * @param  boolean $add  [description]
 * @return string        client id
 */
function  getClientIDbyName($db, $customerArr, $add=false){
	$arr = array('client_name' => $customerArr['client_name']);
	$results = $db->select('clients', 'id', $arr );
	if($results['status'] != 'success' && $add){
		$ins = insertClient($db, $customerArr);
		// print_r($ins);
		if($ins['status'] == 'success'){
			return $ins['data'];
		} else{
			return null;
		}
	}
	// print_r($results);
    return $results['data'][0]['id'];
}


function serchCampaigns($db, $where){
	$tables = array('a' => 'campaigns', 'b'=>'clients');
    $fields = 'campaigns.id as campaign_id, campaigns.vertical_id, campaigns.campaign_name, campaigns.server_name, campaigns.status, campaigns.tw_id, campaigns.phnx_id, campaigns.pub_date, campaigns.owner, campaigns.sales_view, campaigns.preview_rev, campaigns.start_date, campaigns.close_date, clients.client_name, clients.view_name, clients.phnx_customer_id';
    $on = array('a_id' => 'client_id', 'b_id' => 'id');
    $res = $db->selectSearchJoin( $tables, $fields, $on, $where );
    return $res;
}

function serchClients($db, $where){
	$results = $db->selectLike('clients', '*', $where );
    return $res;
}


function getVerticalsList($db){
	$results = $db->select('verticals', '*', array() );
    return $results;
}


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
	$baseURL = $data['client_name'].'/'.$data['campaign_name'].'/rev'.$rev.'/';
	$response = baseResponse('success', 'previews inserted successful', array());
	foreach ( $crvs as $typeKey => $val ) {
		foreach ( $val as $crv) {
			//creatives
			$crv['type'] = $typeKey;
			$crvID = insertCreative($db, $campID, $crv);

			$prvData = array('creatives_id'=> $crvID, 'campaigns_id' => $campID, 'rev' => $rev);
			$prvData['url_path'] =  ( array_key_exists( 'html_name', $crv ) ) ? $baseURL.$crv['name'].'/'.$crv['html_name'].$crv['extension'] : $baseURL.$crv['name'].$crv['extension'];
			$prvID = insertPreview($db, $prvData);
			array_push( $response['data'], $prvID);
		}
	}
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
			insertCreativeVersion($db, $crv_id, $data['path']);
			// insert video feature
			if ( array_key_exists( 'video', $data ) && $data['video'] == ture ){
				$insVideo = inertFeature($db, $crv_id, 1);
			}
			// insert expandable feature
			if ( array_key_exists( 'expandable', $data ) && $data['expandable'] == ture){
				$insExp = inertFeature($db, $crv_id, 6);
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

function insertCreativeVersion($db, $id, $path){
	$fields = array('creative_id'=>$id, 'path'=>$path);
	$unique = validateUnique($db, 'creative_versions', $fields);
	if($unique['valid']){
		$insStat = $db->insert('creative_versions', $fields, array() );
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
		$data['view_name'] = ( array_key_exists('view_name', $data) ) ? $data['view_name'] : $data['client_name'];
		$data['phnx_customer_id'] =  ( array_key_exists('phnx_customer_id', $data) ) ? $data['phnx_customer_id'] : null;
		if($unique['valid']){
			$insStat = $db->insert('clients', $data, array() );
			$response['data'] = $insStat['data'];
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
	$changeOnly =array();
	if( array_key_exists('view_name', $data) ){
		$changeOnly['view_name'] = $data['view_name'];
	}
	if( array_key_exists('phnx_customer_id', $data) ){
		$changeOnly['phnx_customer_id'] = $data['phnx_customer_id'];
	}

	$results = $db->update( 'clients', $changeOnly, array("id" => $id), array() );
	return $results;
}





/**
 * Tools
 */

/**
 * group array by key
 * @param  array $arr array to group
 * @param  string $key name of key to group by
 * @return array      associative array by key
 */
function groupByKey($arr, $key){
	$groupeItmes = array();
	foreach ( $arr as $row ) {
		$groupeItmes[$row[$key]][] = $row;
	}

	$finalData = array();
	foreach ( $groupeItmes as $row ) {
		$finalData[] = array($key => $row[0][$key], 'items' => $row );
	}

	usort($finalData, sortByOrder($key));

	return $finalData;
}

/**
 * sort array by key string accending, used by "usort"
 * @param  string $key the name of the key to use when sorting
 * @return boolean      true/false to determine the comparing values
 */
function sortByOrder($key) {
	return function ($a, $b) use ($key) {
        return strcasecmp($a[$key], $b[$key]);
    };
}

/**
 * used to generate default array responds across the api
 * @param  string $status default is "success", so pass whaever status you want
 * @param  string $msg    additional message of message
 * @param  array  $data   default it will not create a data field, but can pass an array to get it started.
 * @return array         returns associative array with status, message and data
 */
function baseResponse( $status = "success", $msg = "", $data = null ) {
	$response = array();
	$response["status"] = $status;
	$response["message"] = $msg;
	if ( isset( $data ) ) {
		$response["data"] = $data;
	}

	return $response;
}



function validateUnique($db, $table, $uniqueArr){
	$response = array('valid' => false, 'data' => 0);
	$check = $db->select( $table, '*', $uniqueArr );
	if($check['status'] == 'success'){
		$response['valid'] = false;
		$response['data'] = $check['data'][0];
	} else {
		$response['valid'] = true;
	}
	return $response;
}

function verifyRequiredParams($data, $required){
	$response = baseResponse('success', 'all fields valid');
	$error = false;
    $errorColumns = "";
    foreach ( $required as $field ) {
        if ( !isset( $data[$field] ) || strlen( trim( $data[$field] ) ) <= 0 ) {
            $error = true;
            $errorColumns .= $field . ', ';
        }
    }

    if ( $error ) {
        $response["status"] = "error";
        $response["message"] = 'Required field(s) missing or empty';
        $response['data'] = $errorColumns;

        return $response;
       
    } 

    return $response;
}

function clientLike($data){
	$customer = array();
	$items = array();
	if ( array_key_exists('client_name', $data) ){
		array_push($items, $data['client_name']);
	}
	if ( array_key_exists('view_name', $data) ){
		array_push($items, $data['view_name']);
	}

	$customer['view_name'] = $items;
	return $customer;
}

function makeCustomer($data){
	$customer = array();
	if ( array_key_exists('client_name', $data) ){
		$customer['client_name'] =  $data['client_name'];
	}

	if ( array_key_exists('view_name', $data) ){
		$customer['view_name'] =  $data['view_name'];
	}

	if ( array_key_exists('phnx_customer_id', $data) ){
		$customer['phnx_customer_id'] =  $data['phnx_customer_id'];
	}

	return $customer;
}



// DEV

function devGetOldClients($db){
	$results = $db->select('project_info', '*', array() );
    return $results;
}

function devGetOldByName($db, $pname){
	$results = $db->select('project_info', '*', array('server_name' => $pname) );
    return $results;
}

function getCampCrvs($db, $id){
    $tables = array('a' => 'creatives', 'b'=>'size_list');
    $fields = 'creatives.*, size_list.size';
    $on = array('a_id' => 'sizes_list_id', 'b_id' => 'id');
    $where = array('joinField' => 'campaigns_id', 'value' => $id);
    $pre = $db->selectInnerJoin( $tables, $fields, $on, $where);
    $wFeatures = array();
	foreach ($pre['data'] as $row) {
		$row['features'] = getCreativesIDFeatures($db, $id);
	}


    return $pre;
}

function getCMdata($db){
    $pre = getCampData($db);
    // get creatives:
    $wCrv = array();
    foreach ($pre['data'] as $row) {
    	$row['creatives'] = getCampCrvs($db, $row['campaign_id'])['data'];
    	$row['tags'] = getCampaignTags($db, $row['campaign_id'])['data'];
    	print_r($row);
    }

   
    // $results['data'] = groupByKey($pre['data'], 'view_name');
    // $results['status'] = $pre['status'];
    // $results['message'] = $pre['message'];

    // return $results;
}

function getCampData($db){
    $tables = array('a' => 'campaigns', 'b'=>'clients');
    $fields = 'campaigns.id as campaign_id, campaigns.vertical_id, campaigns.campaign_name, campaigns.server_name, campaigns.status, campaigns.tw_id, campaigns.phnx_id, campaigns.pub_date, campaigns.owner, campaigns.sales_view, campaigns.preview_rev, campaigns.start_date, campaigns.close_date, clients.client_name, clients.view_name, clients.phnx_customer_id';
    $on = array('a_id' => 'client_id', 'b_id' => 'id');
    $res = $db->selectInnerJoin( $tables, $fields, $on );
    return $res;
}
