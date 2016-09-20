mainView.controller('CampaignEdit', ['$scope', '$http', '$window', '$routeParams', '$filter', function($scope, $http, $window, $routeParams, $filter, focus) {
    // var urlApi = urlApi + '';
    // var putCampaign = urlApi + 'campaigns/' + $routeParams.itemID;
    // var putClient = urlApi + 'clients/';
    // var urlApi + 'creatives/' = urlApi + 'creatives/';
    // 
    var previewQT = '';
    var previewInsta = 'http://instapreview.com/flash_ads/?'

    var allHTTPdone = false;
    $scope.dataDone = false;
    // passed campaign ID
    // $scope.campID = editId;
    $scope.campID = $routeParams.itemID;
    $scope.ownerOptions = ['LIN', 'MEG', 'ThirdParty'];
    $scope.statusOptions = ['active', 'archived'];
    $scope.shareOptions = ['NO', 'YES'];
    // $scope.allClientNames = [];
    $scope.verticalOptions = [];
    $scope.allTags = [];
    $scope.allFeatures = [];


    // Dynamic data
    $scope.campaignDataBackup = {};
    $scope.campaignData = {};
    $scope.creativesDataBackup = [];
    $scope.creativesData = [];
    $scope.setPreview = [];
    // $scope.setPreviewSelected = {};
    $scope.currentTags = [];
    $scope.tagInput = undefined;

    $scope.creativeExtras = {};
    // $scope.porfolio = false;
    // $scope.toolTip = false;
    // 


    $http.get(urlApi + 'extras/verticals')
        .then(function(response, status, headers, config) {
            $scope.verticalOptions = response.data.data;
        });

    // $http.get(urlApi + 'clients')
    //     .then(function(response, status, headers, config) {
    //         $scope.allClientNames = response.data.data;
    //     });

    // GET campaign data by ID
    $http.get(urlApi + 'campaigns/' + $routeParams.itemID)
        .then(function(response, status, headers, config) {
            if (response.data['status'] == 'success') {
                $scope.campaignData = response.data.data[0];
                angular.copy(response.data.data[0], $scope.campaignDataBackup);
            }
        });


    // GET previews for campaign
    $http.get(urlApi + 'campaigns/' + $routeParams.itemID + '/previews')
        .then(function(response, status, headers, config) {

            if (response.data['status'] == 'success') {
                angular.forEach(response.data.data, function(value) {
                    $scope.setPreview.push(value['rev']);
                });

                if ($scope.campaignData['preview_rev'] == null || $scope.campaignData['preview_rev'] == 0) {
                    $scope.campaignData.preview_rev = $scope.setPreview[$scope.setPreview.length - 1];
                }
            }

        });

    // GET creatives for campaign
    $http.get(urlApi + 'campaigns/' + $routeParams.itemID + '/creatives/full')
        .then(function(response, status, headers, config) {
            if (response.data['status'] == 'success') {
                $scope.creativesData = response.data.data;
                angular.copy(response.data.data, $scope.creativesDataBackup);

                populateFeatures();
                // if ($scope.campaignData['preview_rev'] == null || $scope.campaignData['preview_rev'] == 0) {
                //     $scope.campaignData.preview_rev = $scope.setPreview[$scope.setPreview.length - 1];
                // }
                // console.log($scope.creativesData);
                $scope.dataDone = true;
            }
        });

    $http.get(urlApi + 'extras/tags')
        .then(function(response, status, headers, config) {
            if (response.data['status'] == 'success') {
                $scope.allTags = response.data.data;
            }
        });

    $http.get(urlApi + 'extras/features')
        .then(function(response, status, headers, config) {
            if (response.data['status'] == 'success') {
                $scope.allFeatures = response.data.data;
            }

        });

    // GET tags for campaign
    $http.get(urlApi + 'campaigns/' + $routeParams.itemID + '/tags')
        .then(function(response, status, headers, config) {

            if (response.data['status'] == 'success') {
                var temp = [];
                angular.forEach(response.data.data, function(value) {
                    temp.push(value.tag_name);
                });

                $scope.currentTags = temp
            }

        });






    $scope.exitURL = function(page, link) {
        if (page == 'PHNX') {
            $window.open('https://platform.lin-digital.com/campaigns/io/' + link, '_blank');
        } else {
            $window.open('http://rmm.companyworkflow.com/projects/' + link + '/overview', '_blank');
        }
    }

    $scope.getVertical = function(vID){
        return found = $filter('filter')($scope.verticalOptions, { 'id': vID }, true);
    }

    $scope.updateCampaign = function(key, value) {
        var data = {};
        if (value != null) {
            data[key] = value;
        } else {
            data[key] = $scope.campaignData[key];
        }
        console.log(data)
        if($scope.campaignData[key] != $scope.campaignDataBackup[key]){
            $http.put(urlApi + 'campaigns/' + $routeParams.itemID, data)
            .then(function(response, status, headers, config) {
                console.log(response.data);
                $scope.campaignDataBackup[key] = $scope.campaignData[key];
            });
        }

       
    }


    $scope.updateClient = function() {
        if ($scope.campaignData.client_name != $scope.campaignDataBackup.client_name) {
            var passData = {};
            passData['client_name'] = $scope.campaignData.client_name;
            console.log(passData);
            $http.put(urlApi + 'clients/' + $scope.campaignDataBackup.client_id, passData)
                .then(function(response, status, headers, config) {
                    console.log(config)
                    console.log(response.data);
                    $scope.campaignDataBackup.client_name = $scope.campaignData.client_name;
                });
        }
    }

    //  $scope.typeaheadFn = function(query) {
    //   return $.map($scope.allClientNames, function(item) {
    //     return item;
    //   });
    // }


    $scope.checkFeature = function(crvID, ckBoxID, elm) {
        console.log('checking');
        var featureURL = urlApi + 'creatives/' + crvID + '/features';

        if ($scope.creativeExtras[crvID]['features'].indexOf(String(ckBoxID)) != -1) {
            console.log('checked');
            var sendTags = { features_list_id: ckBoxID };
            $http.post(featureURL, sendTags)
                .then(function(data, status, headers, config) {
                    console.log(data);
                });

        } else {
            var sendTags = { params: { creative_id: crvID, features_list_id: ckBoxID } };
            $http.delete(featureURL, sendTags)
                .then(function(data, status, headers, config) {
                    console.log(data);
                });
        }
    }



    // POST tags for campaign
    $scope.addTags = function(repeatScope, i, values) {
        var sendTags = { tags: values };
        var postTagsURL = urlApi + 'campaigns/' + $routeParams.itemID + '/tags';
        $http.post(postTagsURL, sendTags)
            .then(function(response, status, headers, config) {
                var addedTags = response.data.data['success'];
                var temp = [];
                angular.forEach(addedTags, function(value) {
                    $scope.currentTags.push(value);
                });
            });
        $scope.tagInput = '';
    }

    $scope.deleteTag = function(repeatScope, values, index) {
        console.log('deleting tags')
        var sendTags = { params: { tags: values } };
        var deleteTagsURL = urlApi + 'campaigns/' + $routeParams.itemID + '/tags';
        $http.delete(deleteTagsURL, sendTags)
            .then(function(response, status, headers) {
                if (response.data.data['success'][0] == values) {
                    $scope.currentTags.splice(index, 1);
                } else {
                    console.log('failed to delete: ' + values)
                }
            });
    }

    // $scope.saveSetPorfolio = function(id, obj) {
    //     obj['submited'] = true;
    //     var data = {};
    //     if (!obj['easy_name']) {
    //         return false;
    //     }
    //     // data['easy_name'] = obj['easy_name'];
    //     data['portfolio'] = obj['portfolio'];

    //     $http.put(urlApi + 'creatives/' + id, data)
    //         .then(function(response, status, headers, config) {
    //             console.log(response.data);
    //         });
    // };

    $scope.checkedPortfolio = function(id, obj, i) {
        if (obj.portfolio != obj.portfolio_init) {
            var data = {};
            data['portfolio'] = obj.portfolio;
            $http.put(urlApi + 'creatives/' + id, data)
                .then(function(response, status, headers, config) {
                    console.log(response.data);
                });

            obj.portfolio_init = obj.portfolio;
        }
    }

    $scope.setPortfolioInt = function(val) {
        val.portfolio = parseInt(val.portfolio);
        val.portfolio_init = val.portfolio;
    }



    

    // $scope.updatePorfolio = function() {
    //     console.log('saving');
    // }

    // $scope.inputField = function() {
    //     // var valid = ($scope.creativeExtras[id]['easy_name'].length > 0) ? false : true;
    //     // console.log($scope.creativeExtras[id]);
    //     // return valid;
    //     console.log('test');
    // }

    // $scope.$watch("creativeExtras[1068]['easy_name']", function(newValue, oldValue) {
    //     console.log(newValue);
    // }, true);

    // $scope.$watch("creativesData", function(newValue) {
    //    console.log(newValue)
    // }, true);
    // 
    //   $scope.$on('$viewContentLoaded', function(){
    //       console.log('all loaded');
    // });
    // 
    function populateFeatures() {
        angular.forEach($scope.creativesData, function(group) {
            //console.log(group.type)

            angular.forEach(group.items, function(crv) {
                if (!$scope.creativeExtras.hasOwnProperty(crv.id)) {
                    $scope.creativeExtras[crv.id] = {};
                    $scope.creativeExtras[crv.id]['features'] = [];
                    $scope.creativeExtras[crv.id]['previews'] = [];
                }

                if (crv.hasOwnProperty('features')) {
                    angular.forEach(crv.features, function(f) {
                        $scope.creativeExtras[crv.id].features.push(f.feature_id);
                    });
                }

                if (crv.hasOwnProperty('previews')) {
                    angular.forEach(crv.previews, function(p) { 
                        var serverParts = p['url_path'].split('/');
                        p['fullpath'] = (p['server'] == 'instapreview') ? previewInsta +'client='+serverParts[0]+'&proj='+serverParts[1]+'&revision='+p['rev']+'&size='+crv.size+'&name='+crv.name : '';
                        console.log(p)
                        $scope.creativeExtras[crv.id].previews.push(p);
                    });
                }


            });
        });
        $scope.allDone = true;
    }

    // $scope.$on("db_end", function() {
    //     console.log('db_end');
    //     if (!allHTTPdone) {
    //         console.log('all loaded');
    //         allHTTPdone = true;
    //         $scope.populateFeatures();
    //         if ($scope.campaignData['preview_rev'] == null || $scope.campaignData['preview_rev'] == 0) {
    //             $scope.campaignData.preview_rev = $scope.setPreview[$scope.setPreview.length - 1];
    //         }
    //         console.log($scope.creativesData);
    //         $scope.dataDone = true;
    //     }

    // });

}]);