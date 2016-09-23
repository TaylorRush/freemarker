mainView.controller('CampaignEditController', ['$scope', '$http', '$window', '$routeParams', '$filter', function($scope, $http, $window, $routeParams, $filter, focus) {
    var previewQT = 'http://quicktransmit.com/api/campaigns/_previews/index.php?';
    var previewInsta = 'http://instapreview.com/flash_ads/?';
    var fileInsta = 'http://instapreview.com/design/';
    var fileQT = 'http://quicktransmit.com/api/campaigns/_previews/';
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
    $scope.currentTags = [];
    $scope.tagInput = '';
    $scope.creativeExtras = {};


    $http.get(urlApi + 'extras/verticals')
        .then(function(response, status, headers, config) {
            $scope.verticalOptions = response.data.data;
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

    $http.get(urlApi + 'cm/campaigns/' + $routeParams.itemID + '/editcampaign')
        .then(function(response, status, headers, config) {
            if (response.data['status'] == 'success') {

                $scope.campaignData = response.data.data[0];
                angular.copy(response.data.data[0], $scope.campaignDataBackup);

                $scope.creativesData = response.data.data[0].creatives;
                $scope.currentTags = response.data.data[0].tags;

                populateFeatures();
                $scope.dataDone = true;
            }
        });


    $scope.exitURL = function(page, link) {
        if (page == 'PHNX') {
            $window.open('https://platform.lin-digital.com/campaigns/io/' + link, '_blank');
        } else {
            $window.open('http://rmm.companyworkflow.com/projects/' + link + '/overview', '_blank');
        }
    }

    $scope.getVertical = function(vID) {
        return found = $filter('filter')($scope.verticalOptions, { 'id': vID }, true);
    }

    $scope.updateCampaign = function(key, value) {
        var data = {};
        if (value != null) {
            data[key] = value;
        } else {
            data[key] = $scope.campaignData[key];
        }
        // console.log(data)
        if ($scope.campaignData[key] != $scope.campaignDataBackup[key]) {
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
            // console.log(passData);
            $http.put(urlApi + 'clients/' + $scope.campaignDataBackup.client_id, passData)
                .then(function(response, status, headers, config) {
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
                // var temp = [];
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

    $scope.changePreviewLink = function(id){

        console.log(id);
    }


    function populateFeatures() {

        // var flashBase = 
        // 'I am an <code>HTML</code>string with ' +
        // '<a href="#">links!</a> and other <em>stuff</em>';
        // $scope.campaignData
        // 
        var typeMap = {
            "Standard": "Std",
            "RichMedia": "RM",
            "Static": "Static"
        }

        angular.forEach($scope.creativesData, function(group) {
            angular.forEach(group.items, function(crv) {
                if (!$scope.creativeExtras.hasOwnProperty(crv.id)) {
                    $scope.creativeExtras[crv.id] = {};
                    $scope.creativeExtras[crv.id]['features'] = [];
                    $scope.creativeExtras[crv.id]['previews'] = [];
                    $scope.creativeExtras[crv.id]['view_preview'] = '';
                    $scope.creativeExtras[crv.id]['active_url'] = '';

                }

                if (crv.hasOwnProperty('features')) {
                    angular.forEach(crv.features, function(f) {
                        $scope.creativeExtras[crv.id].features.push(f.feature_id);
                    });
                }

                if (crv.hasOwnProperty('previews')) {
                    angular.forEach(crv.previews, function(p) {

                        var intRev = parseInt(p['rev']);
                        if ($scope.setPreview.indexOf(intRev) == -1) {
                            $scope.setPreview.push(intRev);
                        }
                        var serverParts = p['url_path'].split('/');
                        var rootpart = 'client=' + serverParts[0] + '&proj=' + serverParts[1] + '&revision=' + p['rev'];

                        p['preview_link'] = (p['server'] == 'instapreview') ? previewInsta + rootpart + '&size=' + crv.size + '&name=' + crv.name : previewQT + rootpart + '&size=' + crv.size + '&tname=' + crv.name + '&type=' + typeMap[group.type];
                        p['file_path'] = (p['server'] == 'instapreview') ? fileInsta + p['url_path'] : fileQT + p['url_path'];
                        p['rev'] = parseInt(p['rev']);
                        $scope.creativeExtras[crv.id].previews.push(p);

                    });
                }

            });
        });

        $scope.setPreview = $filter('orderBy')($scope.setPreview, null, true)
        $scope.campaignData.preview_rev = parseInt($scope.campaignData.preview_rev);


        if ($scope.campaignData['preview_rev'] == null || $scope.campaignData['preview_rev'] == 0) {
            $scope.campaignData.preview_rev = $scope.setPreview[$scope.setPreview.length - 1];
        }
        $scope.allDone = true;
         console.log($scope.creativeExtras)
    }



}]);