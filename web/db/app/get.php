<?php

/**
 *  GET functions
 *
 */

function getClients( $db ) {
	$results = $db->select( 'clients', '*', array() );
	return $results;
}

function getClient( $db, $id ) {
	$results = $db->select( 'clients', '*', array( 'id' => $id ) );
	return $results;
}

function getClientByName( $db, $name ) {
	$response = baseResponse();
	$test = $db->select( 'clients', '*', array( 'client_name' => $name ) );
	if ( $test['status'] == 'success' ) {
		if ( count( $test['data'] ) == 1 ) {
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

function getCampaignNameClient( $db, $client_id, $name ) {
	$results = $db->select( 'campaigns', '*', array( 'client_id' => $client_id, 'campaign_name' => $name ) );
	return $results;
}


function getClientsCampaigns( $db ) {
	// $tables = array('a' => 'campaigns', 'b'=>'clients');
	//    $fields = '*';
	//    $on = array('a_id' => 'client_id', 'b_id' => 'id');
	$pre = getCampaigns( $db );
	$results = array();
	$results['data'] = groupByKey( $pre['data'], 'client_name' );
	$results['status'] = $pre['status'];
	$results['message'] = $pre['message'];

	return $results;
}

function getClientCampaigns( $db, $client_id ) {
	$results = $db->select( 'campaigns', '*', array( 'client_id' => $client_id ) );
	return $results;
}

function getCampaigns( $db ) {
	$tables = array( 'a' => 'campaigns', 'b'=>'clients' );
	$fields = 'campaigns.id as campaign_id, campaigns.vertical_id, campaigns.campaign_name, campaigns.server_name, campaigns.status, campaigns.tw_id, campaigns.phnx_id, campaigns.pub_date, campaigns.owner, campaigns.sales_view, campaigns.preview_rev, campaigns.start_date, campaigns.close_date, campaigns.client_id, clients.client_name, clients.phnx_customer_id';
	$on = array( 'a_id' => 'client_id', 'b_id' => 'id' );
	$res = $db->selectInnerJoin( $tables, $fields, $on );
	return $res;
}

function getCampaignID( $db, $id ) {
	$tables = array( 'a' => 'campaigns', 'b'=>'clients' );
	$fields = '*';
	$on = array( 'a_id' => 'client_id', 'b_id' => 'id' );
	$where = array( 'joinField' => 'id', 'value' => $id );
	$res = $db->selectInnerJoin( $tables, $fields, $on, $where );
	return $res;
}


function getCampaignbyTWid( $db, $id ) {
	$results = $db->select( 'campaigns', '*', array( 'tw_id' => $id ) );
	return $results;
}

function getCampaignByName( $db, $name ) {
	$results = $db->select( 'campaigns', '*', array( 'campaign_name' => $name ) );
	return $results;
}

function getCampaignCratives( $db, $id ) {
	$tables = array( 'a' => 'creatives', 'b'=>'size_list' );
	$fields = 'creatives.id as id, creatives.name as name, creatives.type as type, creatives.extension as extension, creatives.portfolio as portfolio, size_list.size as size, size_list.name as size_name';
	$on = array( 'a_id' => 'sizes_list_id', 'b_id' => 'id' );
	$where = array( 'joinField' => 'campaigns_id', 'value' => $id );
	$pre = $db->selectInnerJoin( $tables, $fields, $on, $where );
	return $pre;
}

// function getCampaignCrativesFeatures($db, $id){
//     $tables = array('a' => 'creatives', 'b'=>'size_list');
//     $fields = 'creatives.*, size';
//     $on = array('a_id' => 'sizes_list_id', 'b_id' => 'id');
//     $where = array('joinField' => 'campaigns_id', 'value' => $id);
//     $pre = $db->selectInnerJoin( $tables, $fields, $on, $where);
//     return $pre;
// }

function getCrativePreviews( $db, $id ) {
	$results = $db->select( 'previews', 'id as preview_id, rev, url_path, server', array( 'creatives_id' => $id ) );
	return $results;
}

function getCrativeVersions( $db, $id ) {
	$results = $db->select( 'creative_versions', 'id as versions_id, path', array( 'creative_id' => $id ) );
	return $results;
}


// function getCampaignCrativesByType($db, $id, $fts = false){
//  $pre = getCampaignCratives($db, $id);

//  if ($fts){
//   $newData = array();
//   foreach ($pre['data'] as $obj) {
//    $crv = getCreativesIDFeatures($db, $obj['id']);
//    if ($crv['status'] == 'success'){
//     $obj['features'] = $crv['data'];
//    }
//    $newData[] = $obj;
//   }
//   $pre['data'] = $newData;
//  }

//  $res = array('status' => "success");
//     $res['data'] = groupByKey($pre['data'], 'type');

//     return $res;

// }

function getCampaignPreviews( $db, $campID, $list=false ) {
	$pre = $db->select( 'previews', '*', array( 'campaigns_id' => $campID ) );
	if ( $pre['status'] == 'success' ) {
		$res = array( 'status' => "success" );
		if($list){
			$res['data'] = makeListByArrKey( $pre['data'], 'rev', true );
		} else{
			$res['data'] = groupByKey( $pre['data'], 'rev' );
		}
		
		return $res;
	} else {
		return $pre;
	}

}

function getCampaignPreviewsRev( $db, $campID, $rev ) {
	$pre = $db->select( 'previews', '*', array( 'campaigns_id' => $campID, 'rev' => $rev ) );
	return $pre;
}



/**
 * gets single creative by ID and always includes size
 *
 * @param [type]  $db [description]
 * @param [type]  $id [description]
 * @return [type]     [description]
 */
function getCreativesID( $db, $id ) {
	$tables = array( 'a' => 'creatives', 'b'=>'size_list' );
	$fields = 'creatives.*, size';
	$on = array( 'a_id' => 'sizes_list_id', 'b_id' => 'id' );
	$where = array( 'joinField' => 'id', 'value' => $id );
	$pre = $db->selectInnerJoin( $tables, $fields, $on, $where );
	return $pre;
}

/**
 * gets the features of the corresponding creative ID
 *
 * @param [type]  $db [description]
 * @param [type]  $id [description]
 * @return [type]     [description]
 */
function getCreativesIDFeatures( $db, $id, $list=false ) {
	$results = baseResponse();
	$tables = array( 'a' => 'creative_features', 'b'=>'features_list' );
	$fields = 'features_list.id as id, features_list.name';
	$on = array( 'a_id' => 'features_list_id', 'b_id' => 'id' );
	$where = array( 'joinField' => 'creative_id', 'value' => $id );
	$pre = $db->selectInnerJoin( $tables, $fields, $on, $where );

	if ( $list ) {
		$results['data'] = makeListByArrKey( $pre['data'], 'name');
	} else {
		$results['data'] = $pre['data'];
	}
	$results['status'] = $pre['status'];
	$results['message'] = $pre['message'];

	return $results;
}

function getPortfolio( $db ) {
	$results = $db->select( 'creatives', '*', array( 'portfolio' => 1 ) );
	return $results;
}

function getCampaignTags( $db, $id, $list = false ) {
	$results = baseResponse();
	$tables = array( 'a' => 'campaign_tags', 'b'=>'tag_list' );
	$fields = '*';
	$on = array( 'a_id' => 'tag_list_id', 'b_id' => 'id' );
	$where = array( 'joinField' => 'campaign_id', 'value' => $id );
	$pre = $db->selectInnerJoin( $tables, $fields, $on, $where );

	if ( $list ) {
		$results['data'] = makeListByArrKey( $pre['data'], 'tag_name' );
	} else {
		$results['data'] = $pre['data'];
	}

	$results['message'] = $pre['message'];

	return $results;
}

function getCreativeInfo( $db, $id ) {
	$allCrvs = getCampaignCratives( $db, $id );

	$portfolio = '0';
	$types = array();
	$exts = array();
	$sizes = array();
	$sizenames = array();
	$features = array();

	foreach ( $allCrvs['data'] as $crv ) {
		if ( !in_array( $crv['type'], $types ) ) {
			array_push( $types, $crv['type'] );
		}
		if ( !in_array( $crv['extension'], $exts ) ) {
			array_push( $exts, $crv['extension'] );
		}
		if ( !in_array( $crv['size_name'], $sizenames ) ) {
			if($crv['size_name']){
				array_push( $sizenames, $crv['size_name'] );
			}
			
		}
		if ( !in_array( $crv['size'], $sizes ) ) {
			array_push( $sizes, $crv['size'] );
		}
		if ( $crv['portfolio'] == '1' ) {
			$portfolio = $crv['portfolio'];
			// print_r($crv);
		}
		
		// echo boolval($crv['portfolio']);

		// $features = array_unique( array_merge( $features, getCreativesIDFeatures( $db, $crv['id'], true )['data'] ) );
		$features = getCreativesIDFeatures( $db, $crv['id'], true )['data'];

	}



	return array( 'features' => $features, 'types'=>$types, 'extensions'=>$exts, 'sizes'=>$sizes, 'size_names'=>$sizenames, 'porfolio'=>$portfolio );
}


function getCampaignCreativesFull( $db, $id, $group=false, $features=false, $revisions=false, $versions=false ) {

	$pre = getCampaignCratives( $db, $id );

	$newData = array();
	foreach ( $pre['data'] as $obj ) {

		if($features){
		 $feat = getCreativesIDFeatures($db, $obj['id'], true);
		 if ($feat['status'] == 'success'){
		  $obj['features'] = $feat['data'];
		 }
		}

		if($revisions){
		 $revs = getCrativePreviews($db, $obj['id']);
		 if ($revs['status'] == 'success'){
		  $obj['previews'] = $revs['data'];
		 }
		}


		if($versions){
		 // $vers = getCrativeVersions ($db, $obj['id']);
		 // if ($vers['status'] == 'success'){
		 //  $obj['versions'] = $vers['data'];
		 // }
		}

		$newData[] = $obj;
	}
	$pre['data'] = $newData;
	$res = array( 'status' => "success" );
	$res['data'] = ( $group ) ? groupByKey( $pre['data'], 'type', true ) : $pre['data'];

	return $res;
}

function getTagbyName( $db, $tag ) {
	$tagInfo = $db->select( 'tag_list', '*', array( 'tag_name' => $tag ) );
	return $tagInfo;
}

function getTags( $db ) {
	$results = $db->select( 'tag_list', '*', array() );
	return $results;
}

function getFeatures( $db ) {
	$results = $db->select( 'features_list', '*', array() );
	return $results;
}

function getIgnore( $db ) {
	$results = $db->select( 'ignoreTW_list', 'name', array() );
	return $results;
}

function getSizeID( $db, $size, $add = false ) {
	$sizeArr = array( 'size' => $size );
	$results = $db->select( 'size_list', 'id', $sizeArr );
	if ( $results['status'] != 'success' && $add ) {
		$inst = insertNewSize( $db, $sizeArr );
		return $inst;
	}

	return $results['data'][0]['id'];
}

/**
 * [getClientIDbyName description]
 *
 * @param [type]  $db  [description]
 * @param string  or array  if array it must match clientLike return function
 * @param boolean $add [description]
 * @return string        client id
 */
function getClientIDbyName( $db, $customerArr, $add=false ) {
	$arr = array( 'client_name' => $customerArr['client_name'] );
	$results = $db->select( 'clients', 'id', $arr );
	if ( $results['status'] != 'success' && $add ) {
		$ins = insertClient( $db, $customerArr );
		if ( $ins['status'] == 'success' ) {
			return $ins['data'];
		} else {
			return null;
		}
	}
	// print_r($results);
	return $results['data'][0]['id'];
}


function serchCampaigns( $db, $where ) {
	$tables = array( 'a' => 'campaigns', 'b'=>'clients' );
	$fields = 'campaigns.id as campaign_id, campaigns.vertical_id, campaigns.campaign_name, campaigns.server_name, campaigns.status, campaigns.tw_id, campaigns.phnx_id, campaigns.pub_date, campaigns.owner, campaigns.sales_view, campaigns.preview_rev, campaigns.start_date, campaigns.close_date, clients.client_name, clients.phnx_customer_id';
	$on = array( 'a_id' => 'client_id', 'b_id' => 'id' );
	$res = $db->selectSearchJoin( $tables, $fields, $on, $where );
	return $res;
}

function serchClients( $db, $where ) {
	$results = $db->selectLike( 'clients', '*', $where );
	return $res;
}


function getVerticalsList( $db ) {
	$results = $db->select( 'verticals', '*', array() );
	return $results;
}


function getCMdata($db, $group = false, $sales_view=true){
    $results = baseResponse();
    $camps = getCampData($db, $sales_view);
    $campsFull = array();
    foreach ($camps['data'] as $row) {        
    	$row['tags'] = getCampaignTags($db, $row['campaign_id'], true)['data'];
    	// $row['preview_list'] = getCampaignPreviews($db,  $row['campaign_id'], true)['data'];
    	$revInfo = $db->select( 'previews', "MAX(rev), server", array('campaigns_id' => $row['campaign_id']) )['data'][0];
    	$row['preview_rev'] = ($row['preview_rev']) ? $row['preview_rev'] : $revInfo['MAX(rev)'];
    	$row['preview_server'] = $revInfo['server'];
        $merged = array_merge($row, getCreativeInfo($db, $row['campaign_id']));
    	$campsFull[] = $merged;
    }
   
    $results['data'] = ($group) ? groupByKey($campsFull, 'client_name', false) : $campsFull;
    $results['status'] = $camps['status'];
    $results['message'] = $camps['message'];

    return $results;
}

function getCampData($db, $sales_view=true){
    $tables = array('a' => 'campaigns', 'b'=>'clients');
    $fields = 'campaigns.id as campaign_id, campaigns.vertical_id, campaigns.campaign_name, campaigns.status, campaigns.tw_id, campaigns.phnx_id, campaigns.sales_view, campaigns.preview_rev, campaigns.start_date, campaigns.close_date, clients.client_name';
    $on = array('a_id' => 'client_id', 'b_id' => 'id');
    if($sales_view){
    	$where = array('joinField' => 'sales_view', 'value' => '1');
    	$res = $db->selectInnerJoin( $tables, $fields, $on, $where );
    } else {
    	$res = $db->selectInnerJoin( $tables, $fields, $on );
    }
    
    return $res;
}

function getFullCampaignData($db, $id, $group=false, $features=false, $revisions=false, $versions=false){

	$crvs = getCampaignCreativesFull($db, $id, $group, $features, $revisions, $versions);
	$tags = getCampaignTags($db, $id, true);
	$camp = getCampaignID( $db, $id );

	if($camp['status'] == 'success'){

		$camp['data'][0]['creatives'] = $crvs['data'];
		$camp['data'][0]['tags'] = $tags['data'];
	}

	return $camp;

}

