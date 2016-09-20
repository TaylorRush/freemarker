<?php
require_once 'config.php'; // Database setting constants [DB_HOST, DB_NAME, DB_USERNAME, DB_PASSWORD]
class dbHelper {
    private $db;
    private $err;
    function __construct() {
        $dsn = 'mysql:host='.DB_HOST.';dbname='.DB_NAME.';charset=utf8';
        try {
            $this->db = new PDO( $dsn, DB_USERNAME, DB_PASSWORD, array( PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION ) );
        } catch ( PDOException $e ) {
            $response["status"] = "error";
            $response["message"] = 'Connection failed: ' . $e->getMessage();
            $response["data"] = null;
            //echoResponse(200, $response);
            exit;
        }
    }

    function echoResponse($response) {
        echo json_encode($response, JSON_NUMERIC_CHECK);
    }

    function select( $table, $columns, $where ) {
        try{
         
            $a = array();
            $w = "";
            foreach ( $where as $key => $value ) {
                $w .= " and " .$key. " like :".$key;
                $a[":".$key] = $value;
            }
            $stmt = $this->db->prepare( "select ".$columns." from ".$table." where 1=1 ". $w );
            $stmt->execute( $a );
            $rows = $stmt->fetchAll( PDO::FETCH_ASSOC );
            if ( count( $rows )<=0 ) {
                $response["status"] = "warning";
                $response["message"] = "No data found.";
            }else {
                $response["status"] = "success";
                $response["message"] = "Data selected from database";
            }
            $response["data"] = $rows;
        }catch( PDOException $e ) {
            $response["status"] = "error";
            $response["message"] = 'Select Failed: ' .$e->getMessage();
            $response["data"] = null;
        }
        return $response;
    }

    // function selectUnique( $table, $columns, $where ) {
    //     try{
         
    //         $a = array();
    //         $w = "";
    //         foreach ( $where as $key => $value ) {
    //             $w .= " and " .$key. " like :".$key;
    //             $a[":".$key] = $value;
    //         }
    //         $stmt = $this->db->prepare( "select ".$columns." from ".$table." where 1=1 ". $w );
    //         $stmt->execute( $a );
    //         $rows = $stmt->fetchAll( PDO::FETCH_ASSOC );
    //         if ( count( $rows )<=0 ) {
    //             $response["status"] = "warning";
    //             $response["message"] = "No data found.";
    //         }else {
    //             $response["status"] = "success";
    //             $response["message"] = "Data selected from database";
    //         }
    //         $response["data"] = $rows;
    //     }catch( PDOException $e ) {
    //         $response["status"] = "error";
    //         $response["message"] = 'Select Failed: ' .$e->getMessage();
    //         $response["data"] = null;
    //     }
    //     return $response;
    // }

    function selectLike( $table, $columns, $where ) {
        try{
         
            $a = array();
            $w = "";
            foreach ( $where as $key => $value ) {
                $w .= " and " .$key. " like :".$key;
                $a[":".$key] = '%'.$value.'%';
            }
            $stmt = $this->db->prepare( "select ".$columns." from ".$table." where 1=1 ". $w );
            $stmt->execute( $a );
            $rows = $stmt->fetchAll( PDO::FETCH_ASSOC );
            if ( count( $rows )<=0 ) {
                $response["status"] = "warning";
                $response["message"] = "No data found.";
            }else {
                $response["status"] = "success";
                $response["message"] = "Data selected from database";
            }
            $response["data"] = $rows;
        }catch( PDOException $e ) {
            $response["status"] = "error";
            $response["message"] = 'Select Failed: ' .$e->getMessage();
            $response["data"] = null;
        }
        return $response;
    }

    // function selectOptions( $table, $columns, $key, $where ) {
    //     try{
    //         $w = "";
    //         $wKey = "";
    //         if(count($where) == 1){
    //             // $wKey .= $key
    //             $w .= implode(",", $where[$key]);
    //         } else {
    //             $response["status"] = "error";
    //             $response["message"] = "more than one item in array";
    //             return $response;
    //         }

    //         $stmt = $this->db->prepare( "select ".$columns." from ".$table." WHERE ".$key." IN ( ".$w." )" );
    //         $stmt->execute();
    //         $rows = $stmt->fetchAll( PDO::FETCH_ASSOC );
    //         if ( count( $rows )<=0 ) {
    //             $response["status"] = "warning";
    //             $response["message"] = "No data found.";
    //         }else {
    //             $response["status"] = "success";
    //             $response["message"] = "Data selected from database";
    //         }
    //         $response["data"] = $rows;
    //     }catch( PDOException $e ) {
    //         $response["status"] = "error";
    //         $response["message"] = 'Select Failed: ' .$e->getMessage();
    //         $response["data"] = null;
    //     }
    //     return $response;
    // }


    // function selectGroup( $table, $columns, $where, $group) {
    //     try{
         
    //         $a = array();
    //         $w = "";
    //         foreach ( $where as $key => $value ) {
    //             $w .= " and " .$key. " like :".$key;
    //             $a[":".$key] = $value;
    //         }
    //         // echo "select ".$columns." from ".$table." where 1=1 ". $w . " " . $group;
    //         $stmt = $this->db->prepare( "select ".$columns." from ".$table." where 1=1 ". $w . " GROUP BY " . $group);
    //         $stmt->execute( $a );
    //         $rows = $stmt->fetchAll( PDO::FETCH_ASSOC );
    //         if ( count( $rows )<=0 ) {
    //             $response["status"] = "warning";
    //             $response["message"] = "No data found.";
    //         }else {
    //             $response["status"] = "success";
    //             $response["message"] = "Data selected from database";
    //         }
    //         $response["data"] = $rows;
    //     }catch( PDOException $e ) {
    //         $response["status"] = "error";
    //         $response["message"] = 'Select Failed: ' .$e->getMessage();
    //         $response["data"] = null;
    //     }
    //     return $response;
    // }

    // function selectProjects( $table, $columns, $where ) {
    //     try{
            
    //         $a = array();
    //         $w = "";
    //         foreach ( $where as $key => $value ) {
    //             $w .= " and " .$key. " like :".$key;
    //             $a[":".$key] = $value;
    //         }
    //         $stmt = $this->db->prepare( "select ".$columns." from ".$table." where 1=1 ". $w . " and (NOT status = 'ignore') ORDER BY client_name ASC" );
    //         $stmt->execute( $a );
    //         $rows = $stmt->fetchAll( PDO::FETCH_ASSOC );
    //         if ( count( $rows )<=0 ) {
    //             $response["status"] = "warning";
    //             $response["message"] = "No data found.";
    //         }else {
    //             $response["status"] = "success";
    //             $response["message"] = "Data selected from database";
    //         }
    //         $response["data"] = $rows;
    //     }catch( PDOException $e ) {
    //         $response["status"] = "error";
    //         $response["message"] = 'Select Failed: ' .$e->getMessage();
    //         $response["data"] = null;
    //     }
    //     return $response;
    // }


    function selectSearchJoin($tableArray, $columns, $onArray, $where) {
        try{
            $a = array();
            $w = "";
            foreach ( $where as $key => $value ) {
                $w .= " and " .$key. " like :".$key;
                $a[":".$key] = '%'.$value.'%';
            }
            $stmt = $this->db->prepare( "SELECT ".$columns." FROM ".$tableArray['a']." INNER JOIN ".$tableArray['b']." ON ". $tableArray['a'].".".$onArray['a_id']." = ". $tableArray['b'].".".$onArray['b_id']." where 1=1 ". $w);
    
            $stmt->execute($a);
            $rows = $stmt->fetchAll( PDO::FETCH_ASSOC );
            if ( count( $rows )<=0 ) {
                $response["status"] = "warning";
                $response["message"] = "No data found.";
            }else {
                $response["status"] = "success";
                $response["message"] = "Data selected from database";
            }
            $response["data"] = $rows;
        }catch( PDOException $e ) {
            $response["status"] = "error";
            $response["message"] = 'Select Failed: ' .$e->getMessage();
            $response["data"] = null;
        }
        return $response;
    }


    function selectInnerJoin($tableArray, $columns, $onArray, $where = null) {
        try{
            if (is_null($where)){
                 $stmt = $this->db->prepare( "SELECT ".$columns." FROM ".$tableArray['a']." LEFT JOIN ".$tableArray['b']." ON ". $tableArray['a'].".".$onArray['a_id']." = ". $tableArray['b'].".".$onArray['b_id']." LIMIT 100");
             } else {
                 $stmt = $this->db->prepare( "SELECT ".$columns." FROM ".$tableArray['a']." INNER JOIN ".$tableArray['b']." ON ". $tableArray['a'].".".$onArray['a_id']." = ". $tableArray['b'].".".$onArray['b_id']." where ".$tableArray['a'].".".$where['joinField']." = '".$where['value']."' LIMIT 100" );
             }
           
            $stmt->execute();
            $rows = $stmt->fetchAll( PDO::FETCH_ASSOC );
            if ( count( $rows )<=0 ) {
                $response["status"] = "warning";
                $response["message"] = "No data found.";
            }else {
                $response["status"] = "success";
                $response["message"] = "Data selected from database";
            }
            $response["data"] = $rows;
        }catch( PDOException $e ) {
            $response["status"] = "error";
            $response["message"] = 'Select Failed: ' .$e->getMessage();
            $response["data"] = null;
        }
        return $response;
    }


    //  function selectCM($tableArray, $columns, $onArray, $where = null) {
    //     try{

    //         SELECT * FROM creatives
    //             LEFT JOIN size_list ON creatives.sizes_list_id=size_list.id
    //             LEFT OUTER JOIN creatives ON creatives.
    //                 Country.Code=City.CountryCode;


    //         if (is_null($where)){
    //              $stmt = $this->db->prepare( "SELECT ".$columns." FROM ".$tableArray['a']." LEFT JOIN ".$tableArray['b']." ON ". $tableArray['a'].".".$onArray['a_id']." = ". $tableArray['b'].".".$onArray['b_id']." LIMIT 40");
    //          } else {
    //              $stmt = $this->db->prepare( "SELECT ".$columns." FROM ".$tableArray['a']." INNER JOIN ".$tableArray['b']." ON ". $tableArray['a'].".".$onArray['a_id']." = ". $tableArray['b'].".".$onArray['b_id']." where ".$tableArray['a'].".".$where['joinField']." = '".$where['value']."' LIMIT 40" );
    //          }
           
    //         $stmt->execute();
    //         $rows = $stmt->fetchAll( PDO::FETCH_ASSOC );
    //         if ( count( $rows )<=0 ) {
    //             $response["status"] = "warning";
    //             $response["message"] = "No data found.";
    //         }else {
    //             $response["status"] = "success";
    //             $response["message"] = "Data selected from database";
    //         }
    //         $response["data"] = $rows;
    //     }catch( PDOException $e ) {
    //         $response["status"] = "error";
    //         $response["message"] = 'Select Failed: ' .$e->getMessage();
    //         $response["data"] = null;
    //     }
    //     return $response;
    // }

    // function selectProjectCreatives($pid) {
    //     try{
         
    //         // $a = array();
    //         // $w = "";
    //         // foreach ( $where as $key => $value ) {
    //         //     $w .= " and " .$key. " like :".$key;
    //         //     $a[":".$key] = $value;
    //         // }
    //         // SELECT * FROM `creative_info` INNER JOIN `all_creatives` ON creative_info.id = all_creatives.creative_id WHERE all_creatives.project_id = 379
    //         $stmt = $this->db->prepare( "SELECT * FROM creative_info INNER JOIN all_creatives ON creative_info.id = all_creatives.creative_id WHERE all_creatives.project_id = ".$pid );
    //         $stmt->execute( );
    //         $rows = $stmt->fetchAll( PDO::FETCH_ASSOC );
    //         if ( count( $rows )<=0 ) {
    //             $response["status"] = "warning";
    //             $response["message"] = "No data found.";
    //         }else {
    //             $response["status"] = "success";
    //             $response["message"] = "Data selected from database";
    //         }
    //         $response["data"] = $rows;
    //     }catch( PDOException $e ) {
    //         $response["status"] = "error";
    //         $response["message"] = 'Select Failed: ' .$e->getMessage();
    //         $response["data"] = null;
    //     }
    //     return $response;
    // }

    // function select2( $table, $columns, $where, $order ) {
    //     try{
    //         $a = array();
    //         $w = "";
    //         foreach ( $where as $key => $value ) {
    //             $w .= " and " .$key. " like :".$key;
    //             $a[":".$key] = $value;
    //         }
    //         $stmt = $this->db->prepare( "select ".$columns." from ".$table." where 1=1 ". $w." ".$order );
    //         $stmt->execute( $a );
    //         $rows = $stmt->fetchAll( PDO::FETCH_ASSOC );
    //         if ( count( $rows )<=0 ) {
    //             $response["status"] = "warning";
    //             $response["message"] = "No data found.";
    //         }else {
    //             $response["status"] = "success";
    //             $response["message"] = "Data selected from database";
    //         }
    //         $response["data"] = $rows;
    //     }catch( PDOException $e ) {
    //         $response["status"] = "error";
    //         $response["message"] = 'Select Failed: ' .$e->getMessage();
    //         $response["data"] = null;
    //     }
    //     return $response;
    // }

    // function findCreative( $tableArr, $where ) {
    //     try{
    //         $stmt = $this->db->prepare( "SELECT * FROM ".$tableArr[0]." INNER JOIN ".$tableArr[1]." ON ".$tableArr[0].".id = ".$tableArr[1].".creative_id WHERE ".$tableArr[0].".name = '".$where[0]."' AND ".$tableArr[0].".internal_path = '".$where[1]."' AND ".$tableArr[0].".type = '".$where[2]."' AND ".$tableArr[1].".project_id = ".$where[3] );
    //         $stmt->execute();
    //         $rows = $stmt->fetchAll( PDO::FETCH_ASSOC );
    //         if ( count( $rows )<=0 ) {
    //             $response["status"] = "warning";
    //             $response["message"] = "No data found.";
    //         }else {
    //             $response["status"] = "success";
    //             $response["message"] = "Data selected from database";
    //         }
    //         $response["data"] = $rows;
    //     }catch( PDOException $e ) {
    //         $response["status"] = "error";
    //         $response["message"] = 'Select Failed: ' .$e->getMessage();
    //         $response["data"] = null;
    //     }
    //     return $response;
    // }

    // function findSimpleCrv( $tableArr, $where ) {
    //     try{
    //         $a = array();
    //         $w = "";
    //         foreach ( $where as $key => $value ) {
    //             $w .= " and " .$key. " like :".$key;
    //             $a[":".$key] = $value;
    //         }

    //         $stmt = $this->db->prepare( "SELECT * FROM ".$tableArr[0]." INNER JOIN ".$tableArr[1]." ON ".$tableArr[0].".id = ".$tableArr[1].".creative_id WHERE ".$tableArr[0].".name = '".$where[0]."' AND ".$tableArr[0].".type = '".$where[1]."' AND ".$tableArr[1].".project_id = ".$where[2] );
    //         $stmt->execute();
    //         $rows = $stmt->fetchAll( PDO::FETCH_ASSOC );
    //         if ( count( $rows )<=0 ) {
    //             $response["status"] = "warning";
    //             $response["message"] = "No data found.";
    //         }else {
    //             $response["status"] = "success";
    //             $response["message"] = "Data selected from database";
    //         }
    //         $response["data"] = $rows;
    //     }catch( PDOException $e ) {
    //         $response["status"] = "error";
    //         $response["message"] = 'Select Failed: ' .$e->getMessage();
    //         $response["data"] = null;
    //     }
    //     return $response;
    // }

    // function selectALL( $table, $where ) {
    //     try{
    //         $a = array();
    //         $w = "";
    //         foreach ( $where as $key => $value ) {
    //             $w .= " and " .$key. " like :".$key;
    //             $a[":".$key] = $value;
    //         }
    //         $stmt = $this->db->prepare( "select * from ".$table." where 1=1 ". $w );
    //         $stmt->execute( $a );
    //         $rows = $stmt->fetchAll( PDO::FETCH_ASSOC );
    //         if ( count( $rows )<=0 ) {
    //             $response["STATUS"] = "warning";
    //             $response["message"] = "No data found.";
    //         }else {
    //             $response["STATUS"] = "OK";
    //             $response["message"] = "Data selected from database";
    //         }
    //         $response["data"] = $rows;
    //     }catch( PDOException $e ) {
    //         $response["STATUS"] = "error";
    //         $response["message"] = 'Select Failed: ' .$e->getMessage();
    //         $response["data"] = null;
    //     }
    //     return $response;
    // }
    function insert( $table, $columnsArray, $requiredColumnsArray ) {
        $this->verifyRequiredParams( $columnsArray, $requiredColumnsArray );

        try{
            $a = array();
            $c = "";
            $v = "";
            foreach ( $columnsArray as $key => $value ) {
                $c .= $key. ", ";
                $v .= ":".$key. ", ";
                $a[":".$key] = $value;
            }
            $c = rtrim( $c, ', ' );
            $v = rtrim( $v, ', ' );
            $stmt =  $this->db->prepare( "INSERT INTO $table($c) VALUES($v)" );
            $stmt->execute( $a );
            $affected_rows = $stmt->rowCount();
            $lastInsertId = $this->db->lastInsertId();
            $response["status"] = "success";
            $response["message"] = $affected_rows." row inserted into database";
            $response["data"] = $lastInsertId;
        }catch( PDOException $e ) {
            $response["status"] = "error";
            $response["message"] = 'Insert Failed: ' .$e->getMessage();
            $response["data"] = 0;
        }
        return $response;
    }
    function update( $table, $columnsArray, $where, $requiredColumnsArray ) {
        $this->verifyRequiredParams( $columnsArray, $requiredColumnsArray );
        try{
            $a = array();
            $w = "";
            $c = "";
            foreach ( $where as $key => $value ) {
                $w .= " and " .$key. " = :".$key;
                $a[":".$key] = $value;
            }
            foreach ( $columnsArray as $key => $value ) {
                $c .= $key. " = :".$key.", ";
                $a[":".$key] = $value;
            }
            $c = rtrim( $c, ", " );

            $stmt =  $this->db->prepare( "UPDATE $table SET $c WHERE 1=1 ".$w );
            $stmt->execute( $a );
            $affected_rows = $stmt->rowCount();
            if ( $affected_rows<=0 ) {
                $response["status"] = "warning";
                $response["message"] = "No row updated";
            }else {
                $response["status"] = "success";
                $response["message"] = $affected_rows." row(s) updated in database";
            }
        }catch( PDOException $e ) {
            $response["status"] = "error";
            $response["message"] = "Update Failed: " .$e->getMessage();
        }
        return $response;
    }
    function delete( $table, $where ) {
        if ( count( $where )<=0 ) {
            $response["status"] = "warning";
            $response["message"] = "Delete Failed: At least one condition is required";
        }else {
            try{
                $a = array();
                $w = "";
                foreach ( $where as $key => $value ) {
                    $w .= " and " .$key. " = :".$key;
                    $a[":".$key] = $value;
                }
                $stmt =  $this->db->prepare( "DELETE FROM $table WHERE 1=1 ".$w );
                $stmt->execute( $a );
                $affected_rows = $stmt->rowCount();
                if ( $affected_rows<=0 ) {
                    $response["status"] = "warning";
                    $response["message"] = "No row deleted";
                }else {
                    $response["status"] = "success";
                    $response["message"] = $affected_rows." row(s) deleted from database";
                }
            }catch( PDOException $e ) {
                $response["status"] = "error";
                $response["message"] = 'Delete Failed: ' .$e->getMessage();
            }
        }
        return $response;
    }

    function verifyRequiredParams( $inArray, $requiredColumns ) {
        $error = false;
        $errorColumns = "";
        foreach ( $requiredColumns as $field ) {
            // strlen($inArray->$field);
            if ( !isset( $inArray->$field ) || strlen( trim( $inArray->$field ) ) <= 0 ) {
                $error = true;
                $errorColumns .= $field . ', ';
            }
        }

        if ( $error ) {
            $response = array();
            $response["status"] = "error";
            $response["message"] = 'Required field(s) ' . rtrim( $errorColumns, ', ' ) . ' is missing or empty';
            $this->echoResponse($response);
            exit;
        }
    }

   
}

?>
