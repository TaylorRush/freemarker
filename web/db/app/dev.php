<?php

// DEV

function devGetOldClients($db){
	$results = $db->select('project_info', '*', array() );
    return $results;
}

function devGetOldByName($db, $pname){
	$results = $db->select('project_info', '*', array('server_name' => $pname) );
    return $results;
}

// function getCampCrvs($db, $id){
//     $tables = array('a' => 'creatives', 'b'=>'size_list');
//     $fields = 'creatives.*, size_list.size';
//     $on = array('a_id' => 'sizes_list_id', 'b_id' => 'id');
//     $where = array('joinField' => 'campaigns_id', 'value' => $id);
//     $pre = $db->selectInnerJoin( $tables, $fields, $on, $where);
//     $wFeatures = array();
// 	foreach ($pre['data'] as $row) {
// 		$row['features'] = getCreativesIDFeatures($db, $id);
// 	}


//     return $pre;
// }

