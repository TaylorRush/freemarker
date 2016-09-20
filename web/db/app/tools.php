<?php


/**
 * Tools
 */

/**
 * group array by key
 * @param  array $arr array to group
 * @param  string $key name of key to group by
 * @return array      associative array by key
 */
function groupByKey($arr, $key, $removeField=false){
	$groupeItmes = array();
	foreach ( $arr as $row ) {
		$groupeItmes[$row[$key]][] = $row;
	}

	$finalData = array();
	foreach ( $groupeItmes as $row ) {
		$tmp = $row[0][$key];
		if($removeField){
			unset($row[0][$key]);
		}
		$finalData[] = array($key => $tmp, 'items' => $row );
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



function makeListByArrKey($asArr, $k, $unique=false){
	$list = array();
	foreach ($asArr as $row) {
		if ( array_key_exists($k, $row) ){
			if ($unique){
				if(!in_array($row[$k], $list)){
					$list[] = $row[$k];
				}
			} else {
				$list[] = $row[$k];
			}
			
		}
	}

	// if($unique){
	// 	natsort($list);
	// 	// reset($list);
	// }

	return $list;
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

// function clientLike($data){
// 	$customer = array();
// 	$items = array();
// 	if ( array_key_exists('client_name', $data) ){
// 		array_push($items, $data['client_name']);
// 	}
// 	// if ( array_key_exists('view_name', $data) ){
// 	// 	array_push($items, $data['view_name']);
// 	// }

// 	// $customer['view_name'] = $items;
// 	return $customer;
// }

function makeCustomer($data){
	$customer = array();
	if ( array_key_exists('client_name', $data) ){
		$customer['client_name'] =  $data['client_name'];
	}

	// if ( array_key_exists('view_name', $data) ){
	// 	$customer['view_name'] =  $data['view_name'];
	// }

	if ( array_key_exists('phnx_customer_id', $data) ){
		$customer['phnx_customer_id'] =  $data['phnx_customer_id'];
	}

	return $customer;
}
