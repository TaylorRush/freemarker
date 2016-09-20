<?php

ini_set( 'display_errors', true );


require __DIR__ . '/../vendor/autoload.php';
require __DIR__ . '/../db/dbHelper.php';
require __DIR__ . '/../app/tools.php';
require __DIR__ . '/../app/get.php';
require __DIR__ . '/../app/post.php';
require __DIR__ . '/../app/update.php';
require __DIR__ . '/../app/delete.php';
require __DIR__ . '/../app/dev.php';


$settings = [
'settings' => [
'displayErrorDetails' => true,

],
];


$c = new \Slim\Container( $settings );

// $c['cache'] = function () {
//     return new \Slim\HttpCache\CacheProvider();
// };


// $c['notFoundHandler'] = function ( $c ) {
//     return function ( $request, $response ) use ( $c ) {
//         return $c['response']
//         ->withStatus( 404 )
//         ->withHeader( 'Content-Type', 'text/html' )
//         ->write( 'Page not found' );
//     };
// };


// $c['errorHandler'] = function ( $c ) {
//     return function ( $request, $response, $exception ) use ( $c ) {
//         return $c['response']->withStatus( 500 )
//         ->withHeader( 'Content-Type', 'text/html' )
//         ->write( 'Something went wrong!' );
//     };
// };

// $c['notAllowedHandler'] = function ( $c ) {
//     return function ( $request, $response, $methods ) use ( $c ) {
//         return $c['response']
//         ->withStatus( 405 )
//         ->withHeader( 'Allow', implode( ', ', $methods ) )
//         ->withHeader( 'Content-type', 'text/html' )
//         ->write( 'Method must be one of: ' . implode( ', ', $methods ) );
//     };
// };


$c['db'] = function ( $c ) {
    return $db = new dbHelper();
};


$auth = function ( $request, $response, $next ) {
    $browser = true;
    $validKey = 'fs4eV0cN00wiyesJpmBD';

    if ( $request->hasHeader( 'HTTP_X_API_KEY' ) || $browser ) {
        if ( $browser ) {
            $response = $next( $request, $response );
        } else {
            if ( $request->getHeader( 'HTTP_X_API_KEY' )[0] == $validKey ) {
                $response = $next( $request, $response );
            } else {
                $response->getBody()->write( 'invalid key' );
            }
        }
    } else {
        $response->getBody()->write( 'invalid key' );
    }

    return $response;
};


$app = new Slim\App( $c );


//slim application routes
$app->get( '/', function ( $request, $response, $args ) {
        $response->write( "Welcome: API" );
        return $response;
    } );

$app->group( '/api',  function () {
        /**
         * CLIENTS
         */
        $this->group( '/clients', function () {

                /**
                 * GET
                 */
                $this->get( '', function ( $request, $response ) {
                        $db = $this->db;
                        $response = $response->withStatus( 201 );
                        $response = $response->withJson( getClients( $db ) );
                        return $response;
                    } );


                $this->get( '/{id:\d+}', function ( $request, $response, $args ) {
                        $db = $this->db;
                        $response = $response->withStatus( 201 );
                        $response = $response->withJson( getClient( $db, $args['id'] ) );
                        return $response;
                    } );

                $this->get( '/name/{name}', function ( $request, $response, $args ) {
                        $db = $this->db;
                        $response = $response->withStatus( 201 );
                        $response = $response->withJson( getClientByName( $db, $args['name'] ) );
                        return $response;
                    } );

                $this->get( '/campaigns', function ( $request, $response ) {
                        $db = $this->db;
                        $response = $response->withStatus( 201 );
                        $response = $response->withJson( getClientsCampaigns( $db ) );
                        return $response;
                    } );

                $this->get( '/{id:\d+}/campaigns', function ( $request, $response, $args ) {
                        $db = $this->db;
                        $response = $response->withStatus( 201 );
                        $response = $response->withJson( getClientCampaigns( $db, $args['id'] ) );
                        return $response;
                    } );

                $this->get( '/{id:\d+}/campaigns/{name}', function ( $request, $response, $args ) {
                        $db = $this->db;
                        $response = $response->withStatus( 201 );
                        $response = $response->withJson( getCampaignNameClient( $db, $args['id'], $args['name'] ) );
                        return $response;
                    } );

                $this->get( '/search[/{params:.*}]', function ( $request, $response, $args ) {
                        $db = $this->db;
                        $response = $response->withStatus( 201 );
                        $params = explode( '/', $request->getAttribute( 'params' ) );
                        $chunks = array_chunk( $params, 2 );

                        $where = array();
                        foreach ( $chunks as $c ) {
                            if ( count( $c ) == 2 ) {
                                $where[$c[0]] = $c[1];
                            }
                        }

                        $response = $response->withJson( serchCampaigns( $db, $where ) );
                        return $response;

                    } );

                /**
                 * POST - insert
                 */
                $this->post( '', function( $request, $response, $args ) {
                        $db = $this->db;
                        $response = $response->withStatus( 201 );

                        $response = $response->withJson( insertClient( $db, $request->getParams() ) );
                        return $response;
                    } );

                $this->post( '/{id:\d+}/campaigns', function( $request, $response, $args ) {
                        $db = $this->db;
                        $response = $response->withStatus( 201 );
                        $response = $response->withJson( insertClientCampaign( $db, $request->getParams(), $args['id'] ) );
                        return $response;
                    } );

                /**
                 * PUT - update
                 */
                $this->put( '/{id:\d+}', function ( $request, $response,  $args ) {
                        $db = $this->db;
                        $response = $response->withStatus( 201 );
                        $response = $response->withJson( updateClientByID( $db, $args['id'], $request->getParams() ) );
                        return $response;
                    } );

            } );

        /**
         * CAMPAIGNS
         */
        $this->group( '/campaigns', function () {
                /**
                 * GET - retrive
                 */
                    $this->get( '', function ( $request, $response ) {
                            $db = $this->db;
                            $response = $response->withStatus( 201 );
                            $response = $response->withJson( getCampaigns( $db ) );
                            // $response = $response->withJson( getCampaignsByCampaign($db) );
                            return $response;
                        } );

                    $this->get( '/{id:\d+}', function ( $request, $response, $args ) {
                            $db = $this->db;
                            $response = $response->withStatus( 201 );
                            $response = $response->withJson( getCampaignID( $db, $args['id'] ) );
                            return $response;
                        } );

                    $this->get( '/name/{name}', function ( $request, $response, $args ) {
                            $db = $this->db;
                            $response = $response->withStatus( 201 );
                            $response = $response->withJson( getCampaignByName( $db, $args['name'] ) );
                            return $response;
                        } );

                    $this->get( '/twid/{id:\d+}', function ( $request, $response, $args ) {
                            $db = $this->db;
                            $response = $response->withStatus( 201 );
                            $response = $response->withJson( getCampaignbyTWid( $db, $args['id'] ) );
                            return $response;
                        } );

                    // $this->get( '/teamwork/{id:\d+}', function ( $request, $response, $args ) {
                    //         $response->write( 'get campaign by teamwork id: ' . $args['id'] );
                    //     } );

                    $this->get( '/{id:\d+}/creatives', function ( $request, $response, $args ) {
                            $db = $this->db;
                            $response = $response->withStatus( 201 );
                            $response = $response->withJson( getCampaignCratives( $db, $args['id'] ) );
                            return $response;
                        } );

                    // $this->get( '/{id:\d+}/creatives/types', function ( $request, $response, $args ) {
                    //         $db = $this->db;
                    //         $response = $response->withStatus( 201 );
                    //         $response = $response->withJson( getCampaignCrativesByType( $db, $args['id'] ) );
                    //         return $response;
                    //     } );

                    // $this->get( '/{id:\d+}/creatives/types/features', function ( $request, $response, $args ) {
                    //         $db = $this->db;
                    //         $response = $response->withStatus( 201 );
                    //         $response = $response->withJson( getCampaignCrativesByType( $db, $args['id'], true ) );
                    //         return $response;
                    //     } );

                    $this->get( '/{id:\d+}/creatives/full', function ( $request, $response, $args ) {
                            $db = $this->db;
                            $response = $response->withStatus( 201 );
                            $group = true;
                            $features=true;
                            $revisions=true;
                            $versions=true;

                            $response = $response->withJson( getCampaignCreativesFull( $db, $args['id'], $group, $features, $revisions, $versions ) );
                            return $response;
                        } );

                    $this->get( '/{id:\d+}/previews', function ( $request, $response, $args ) {
                            $db = $this->db;
                            $response = $response->withStatus( 201 );
                            $response = $response->withJson( getCampaignPreviews( $db, $args['id'] ) );
                            return $response;
                        } );

                    $this->get( '/{id:\d+}/previews/{rev:\d+}', function ( $request, $response, $args ) {
                            $db = $this->db;
                            $response = $response->withStatus( 201 );
                            $response = $response->withJson( getCampaignPreviewsRev( $db, $args['id'], $args['rev'] ) );
                            return $response;
                        } );

                    $this->get( '/{id:\d+}/tags', function ( $request, $response, $args ) {
                            $db = $this->db;
                            $response = $response->withStatus( 201 );
                            $response = $response->withJson( getCampaignTags( $db, $args['id'] ) );
                            return $response;
                        } );

                    $this->get( '/{id:\d+}/features', function ( $request, $response, $args ) {
                            $db = $this->db;
                            $response = $response->withStatus( 201 );
                            $response = $response->withJson( getCampaignFeatures( $db, $args['id'] ) );
                            return $response;
                        } );

                    $this->get( '/search[/{params:.*}]', function ( $request, $response, $args ) {
                            $db = $this->db;
                            $response = $response->withStatus( 201 );
                            $params = explode( '/', $request->getAttribute( 'params' ) );
                            $chunks = array_chunk( $params, 2 );

                            $where = array();
                            foreach ( $chunks as $c ) {
                                if ( count( $c ) == 2 ) {
                                    $where[$c[0]] = $c[1];
                                }
                            }

                            $response = $response->withJson( serchCampaigns( $db, $where ) );
                            return $response;

                        } );
                /**
                 * POST - insert
                 */
                    $this->post( '', function ( $request, $response ) {
                            $db = $this->db;
                            $response = $response->withStatus( 201 );
                            $response = $response->withJson( insertCampaign( $db, $request->getParams() ) );
                            return $response;
                        } );

                    $this->post( '/{id:\d+}/creatives', function ( $request, $response, $args ) {
                            $db = $this->db;
                            $response = $response->withStatus( 201 );
                            $response = $response->withJson( processCreatives( $db, $args['id'], $request->getParams() ) );
                            return $response;

                        } );

                    $this->post( '/{id:\d+}/previews', function ( $request, $response, $args ) {
                            $db = $this->db;
                            $response = $response->withStatus( 201 );
                            $response = $response->withJson( processPreviews( $db, $args['id'], $request->getParams() ) );
                            return $response;

                        } );

                    $this->post( '/{id:\d+}/tags', function ( $request, $response, $args ) {
                            $db = $this->db;
                            $response = $response->withStatus( 201 );
                            $response = $response->withJson( insertTags( $db, $args['id'], $request->getParams() ) );
                            return $response;
                        } );

                /**
                 * PUT - update
                 */
                    $this->put( '/{id:\d+}', function ( $request, $response,  $args ) {
                            $db = $this->db;
                            $response = $response->withStatus( 201 );
                            $response = $response->withJson( updateCampaign( $db, $args['id'], $request->getParams() ) );
                            return $response;
                        } );

                // $this->put( '/{id:\d+}/vertical', function ( $request, $response, $args ) {
                //         $response->write( 'update vertical to campaign ' . $args['id'] );
                //     } );

                // $this->put( '/{id:\d+}/client', function ( $request, $response, $args ) {
                //         $response->write( 'update clients campaign ' . $args['id'] );
                //     } );

                // $this->put( '/{id:\d+}/previews/{rev:\d+}', function ( $request, $response, $args ) {
                //         // $db = $this->db;
                //         // $response = $response->withStatus( 201 );
                //         // $response = $response->withJson( updateCampaign($db, $args['id'], $request->getParams() ) );
                //         // return $response;
                //     } );

                /**
                 * DELETE - delete
                 */
                // $this->delete( '/{id:\d+}', function ( $request, $response,  $args ) {
                //         $response->write( 'delete campaign with id' . $args['id'] );
                //     } );

                // $this->delete( '/teamwork/{id:\d+}', function ( $request, $response, $args ) {
                //         $response->write( 'delete by teamworkID ' . $args['id'] );
                //     } );

                $this->delete( '/{id:\d+}/tags', function ( $request, $response, $args ) {
                        $db = $this->db;
                        // print_r($request->getQueryParams());
                        $response = $response->withStatus( 201 );
                        $response = $response->withJson( deleteCampaignTag( $db, $args['id'], $request->getQueryParams() ) );
                        return $response;
                    } );

            } );

        /**
         * CREATIVES
         */
        $this->group( '/creatives', function () {
                /**
                 * GET - retrive
                 */
                // $this->get( '', function ( $request, $response ) {
                //         $response->write( 'the whole creative list' );
                //     } );

                $this->get( '/{id:\d+}', function ( $request, $response, $args ) {
                        $db = $this->db;
                        $response = $response->withStatus( 201 );
                        $response = $response->withJson( getCreativesID( $db, $args['id'] ) );
                        return $response;
                    } );

                $this->get( '/{id:\d+}/features', function ( $request, $response, $args ) {
                        $db = $this->db;
                        $response = $response->withStatus( 201 );
                        $response = $response->withJson( getCreativesIDFeatures( $db, $args['id'] ) );
                        return $response;
                    } );

                $this->get( '/{id:\d+}/previews', function ( $request, $response, $args ) {
                        $db = $this->db;
                        $response = $response->withStatus( 201 );
                        $response = $response->withJson( getCrativePreviews( $db, $args['id'] ) );
                        return $response;
                    } );

                $this->get( '/portfolio', function ( $request, $response ) {
                        $db = $this->db;
                        $response = $response->withStatus( 201 );
                        $response = $response->withJson( getPortfolio( $db ) );
                        return $response;
                    } );

                // // testing
                // $this->get( '/size/{s:[0-9]{2,4}[x]{1}[0-9]{2,4}}', function ( $request, $response, $args ) {
                //         $response->write( 'get all creatives by size ' . $args['s'] );
                //     } );

                /**
                 * POST - insert
                 */
                // $this->post( '', function ( $request, $response ) {
                //         $response->write( 'insert creative' );
                //     } );

                $this->post( '/{id:\d+}/features', function ( $request, $response,  $args ) {
                        $db = $this->db;
                        $response = $response->withStatus( 201 );
                        $data = $request->getParams();
                        $fID = $data['features_list_id'];
                        $response = $response->withJson( inertFeature( $db, $args['id'], $fID ) );
                        return $response;
                    } );

                /**
                 * PUT - update
                 */
                $this->put( '/{id:\d+}', function ( $request, $response,  $args ) {
                        $db = $this->db;
                        $response = $response->withStatus( 201 );
                        $response = $response->withJson( updateCreative( $db, $args['id'], $request->getParams() ) );
                        return $response;
                    } );

                // $this->put( '/{id:\d+}/portfolio', function ( $request, $response,  $args ) {
                //         $response->write( 'update portfolio setting ' . $args['id'] );
                //     } );

                // $this->put( '/{id:\d+}/type', function ( $request, $response,  $args ) {
                //         $response->write( 'update type on creative ' . $args['id'] );
                //     } );


                /**
                 * DELETE - delete
                 */
                // $this->delete( '/{id:\d+}', function ( $request, $response,  $args ) {
                //         $response->write( 'delete campaign with id' . $args['id'] );
                //     } );

                $this->delete( '/{id:\d+}/features', function ( $request, $response,  $args ) {
                        $db = $this->db;
                        $response = $response->withStatus( 201 );
                        $response = $response->withJson( deleteFeature( $db, $args['id'], $request->getQueryParams() ) );
                        return $response;
                    } );
            } );


        /**
         * PREVIEWS
         */
        $this->group( '/previews', function () {
                /**
                 * GET - retrive
                 */
                // $this->get( '', function ( $request, $response ) {
                //         $response->write( 'the whole previews list' );
                //     } );

                // $this->get( '/{id:\d+}', function ( $request, $response, $args ) {
                //         $response->write( 'get specific preview ' . $args['id'] );
                //     } );

                // $this->get( '/rev/{num:\d+}', function ( $request, $response, $args ) {
                //         $response->write( 'get all previews by size ' . $args['num'] );
                //     } );

                /**
                 * POST - insert
                 */
                // $this->post( '', function ( $request, $response ) {
                //         $response->write( 'insert creative' );
                //     } );

                /**
                 * PUT - update
                 */
                // $this->put( '/{id:\d+}', function ( $request, $response,  $args ) {
                //         $response->write( 'update creative with id' . $args['id'] );
                //     } );

                /**
                 * DELETE - delete
                 */
                // $this->delete( '/{id:\d+}', function ( $request, $response,  $args ) {
                //         $response->write( 'delete campaign with id' . $args['id'] );
                //     } );
            } );


        /**
         * EXTRAS
         */
        $this->group( '/extras', function () {
                /**
                 * GET - retrive
                 */
                $this->get( '/verticals', function ( $request, $response ) {
                        $db = $this->db;
                        $response = $response->withStatus( 201 );
                        $response = $response->withJson( getVerticalsList( $db ) );
                        return $response;
                    } );

                $this->get( '/tags', function ( $request, $response ) {
                        $db = $this->db;
                        $response = $response->withStatus( 201 );
                        $response = $response->withJson( getTags( $db ) );
                        return $response;
                    } );

                $this->get( '/tags/{tag}', function ( $request, $response, $args ) {
                        $db = $this->db;
                        $response = $response->withStatus( 201 );
                        $response = $response->withJson( getTagbyName( $db, $args['tag'] ) );
                        return $response;
                    } );

                // $this->get( '/sizes', function ( $request, $response ) {
                //         $response->write( 'all sizes list' );
                //     } );

                $this->get( '/features', function ( $request, $response ) {
                        $db = $this->db;
                        $response = $response->withStatus( 201 );
                        $response = $response->withJson( getFeatures( $db ) );
                        return $response;
                    } );

                $this->get( '/ignore', function ( $request, $response ) {
                        $db = $this->db;
                        $response = $response->withStatus( 201 );
                        $response = $response->withJson( getIgnore( $db ) );
                        return $response;
                    } );

                /**
                 * POST - insert
                 */
                // $this->post( '/verticals', function ( $request, $response ) {
                //         $response->write( 'insert verticals list' );
                //     } );

                // $this->post( '/types', function ( $request, $response ) {
                //         $response->write( 'insert types list' );
                //     } );

                // $this->post( '/tags', function ( $request, $response ) {
                //         $response->write( 'insert tags list' );
                //     } );

                // $this->post( '/sizes', function ( $request, $response ) {
                //         $response->write( 'insert sizes list' );
                //     } );

                // $this->post( '/features', function ( $request, $response ) {
                //         $response->write( 'insert features list' );
                //     } );

                // $this->post( '/ignore', function ( $request, $response ) {
                //         $db = $this->db;
                //         $response = $response->withStatus( 201 );
                //         $response = $response->withJson( insertIgnore( $db, $request->getParams() ) );
                //         return $response;
                //     } );


                /**
                 * PUT - update
                 */
                // $this->put( '/verticals/{id:\d+}', function ( $request, $response, $args ) {
                //         $response->write( 'update verticals by id ' . $args['id'] );
                //     } );

                // $this->put( '/types/{id:\d+}', function ( $request, $response, $args ) {
                //         $response->write( 'update types by id ' . $args['id'] );
                //     } );

                // $this->put( '/tags/{id:\d+}', function ( $request, $response, $args ) {
                //         $response->write( 'update tags by id ' . $args['id'] );
                //     } );

                // $this->put( '/sizes/{id:\d+}', function ( $request, $response, $args ) {
                //         $response->write( 'update sizes by id ' . $args['id'] );
                //     } );

                // $this->put( '/features/{id:\d+}', function ( $request, $response, $args ) {
                //         $response->write( 'update features by id ' . $args['id'] );
                //     } );



                /**
                 * DELETE - delete
                 */
                // $this->delete( '/verticals/{id:\d+}', function ( $request, $response, $args ) {
                //         $response->write( 'delete verticals by id ' . $args['id'] );
                //     } );

                // $this->delete( '/types/{id:\d+}', function ( $request, $response, $args ) {
                //         $response->write( 'delete types by id ' . $args['id'] );
                //     } );

                // $this->delete( '/tags/{id:\d+}', function ( $request, $response, $args ) {
                //         $response->write( 'delete tags by id ' . $args['id'] );
                //     } );

                // $this->delete( '/sizes/{id:\d+}', function ( $request, $response, $args ) {
                //         $response->write( 'delete sizes by id ' . $args['id'] );
                //     } );

                // $this->delete( '/features/{id:\d+}', function ( $request, $response, $args ) {
                //         $response->write( 'delete features by id ' . $args['id'] );
                //     } );
            } );


        /**
         * CM STUFF
         */
        $this->group( '/cm', function () {

                /**
                 * GET - retrive
                 */
                $this->get( '/campaigns', function ( $request, $response ) {
                        $db = $this->db;
                        $response = $response->withStatus( 201 );
                        $response = $response->withJson( getCMdata( $db, false, false ) );
                        return $response;
                    } );

                $this->get( '/sales', function ( $request, $response ) {
                        $db = $this->db;
                        $response = $response->withStatus( 201 );
                        $response = $response->withJson( getCMdata( $db, false, true ) );
                        return $response;
                    } );

            } );

        /**
         * SEARCH
         */

        // $this->group( '/search', function () {
        //        // table/field/value
        //        $this->get( '[campaigns/{params:.*}]', function ( $request, $response, $args) {
        //                $db = $this->db;
        //                $response = $response->withStatus( 201 );

        //                $params = explode('/', $request->getAttribute('params'));
        //                // print_r($params);
        //                if (count($params) == 3){
        //                    $table = (count($params) >= 1) ? $params[0] : null;
        //                    $field = (count($params) >= 2) ? $params[1] : null;
        //                    $term = (count($params) >= 3) ? $params[2] : null;

        //                    $response = $response->withJson( serchCampaigns( $db, $table, $field, $term ) );
        //                    // echo $table;
        //                    //  echo '<br>';
        //                    //   echo $field;
        //                    //  echo '<br>';
        //                    //   echo $term;
        //                    //  echo '<br>';

        //                } else {
        //                     // echo 'outside';
        //                    $response =  $response->withJson( array('status' => 'error', 'message'=>'nothing to search') );

        //                }

        //                return $response;

        //            } );
        //    } );

        /**
         * DEV
         */
            $this->group( '/dev', function () {
                    $this->get( '/oldclients', function ( $request, $response ) {
                            $db = $this->db;
                            $response = $response->withStatus( 201 );
                            $response = $response->withJson( devGetOldClients( $db ) );
                            return $response;
                        } );

                    $this->get( '/oldclients/{name}', function ( $request, $response, $args ) {
                            $db = $this->db;
                            $response = $response->withStatus( 201 );
                            $response = $response->withJson( devGetOldByName( $db, $args['name'] ) );
                            return $response;
                        } );

                    $this->get( '/cmStuff', function ( $request, $response ) {
                            $db = $this->db;
                            $response = $response->withStatus( 201 );
                            $response = $response->withJson( getCMdata( $db, false ) );
                            return $response;
                        } );
                } );

    } )->add( $auth );




$app->run();
