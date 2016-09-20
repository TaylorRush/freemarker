var mainView = angular.module('mainView', []);

mainView.controller('SearchController', ['$scope', '$http', '$window', '$filter', '$routeParams', '$modal', 'containsFilter', function($scope, $http, $window, $filter, $routeParams, $modal, containsFilter) {

    $scope.salesViewOn = true;
    if ('pro' in $routeParams) {
        $scope.salesViewOn = false;
    }
    var scrollWindow = angular.element(document.querySelector('#scroll-data'));
    var iniPos = scrollWindow.offset().top;
    $scope.$watch('winSize', function(old, newHeight) {
        scrollWindow.height(newHeight.height - iniPos);
    });


    $scope.backupData = [];
    $scope.verticalOptions = [];
    $scope.clientCount = { filtered: [] };
    $scope.searchBox = '';
    $scope.navStatus = 'closeNav';

    $scope.monthOptions = ['January', 'February', 'March', 'April', 'May', 'June', 'July', 'August', 'September', 'October', 'November', 'December'];
    $scope.yearOptions = ['2016', '2015', '2014', '2013', '2012']
    $scope.filterSelection = { sizes: [], types: [], features: [], month: '', year: '', vertical: '' };
    $scope.currList = [];

    var salesViewURL = urlApi + 'cm/sales';
    var allURL = urlApi + 'cm/campaigns';
    var urlPull = ($scope.salesViewOn) ? salesViewURL : allURL;

    // clients/campaigns
    $http.get(urlPull)
        .then(function(response, status, headers, config) {
            $scope.clients = response.data.data;
            $scope.backupData = response.data.data;
        });

    $http.get(urlApi + 'extras/verticals')
        .then(function(response, status, headers, config) {
            $scope.verticalOptions = response.data.data;
        });



    $scope.sizeCheck = function(checked, extra) {
        var idx = $scope.filterSelection.sizes.indexOf('other');

        if (arguments.length == 2) {
            console.log('remove all others');
            $scope.filterSelection.sizes.splice(0, $scope.filterSelection.sizes.length - 1);
        } else if (idx == 0 && checked) {
            $scope.filterSelection.sizes.splice(0, 1);
        }
    };

    $scope.clearFilter = function() {
        $scope.filterSelection = { sizes: [], types: [], features: [], month: '', year: '', vertical: '' };
        $scope.searchBox = "";
    }

    $scope.editCampaign = function(id) {
        window.location.href = '#/campaign/' + id;
    }

    $scope.gotoPreview = function(obj) {
        console.log(obj);
        var urlpath = (obj.preview_server == 'instapreview') ? 'http://instapreview.com/flash_ads/?client='+obj.client_name+'&proj='+ obj.campaign_name+'&revision='+obj.preview_rev : 'http://quicktransmit.com/api/campaigns/_previews/index.php?client='+obj.client_name+'&proj='+ obj.campaign_name+'&revision='+obj.preview_rev;
        $window.open(urlpath, '_blank');
        // window.location.href = '#/campaign/' + id;
    }

    $scope.openMenu = function() {
        $scope.navStatus = "openNav";
    }

    $scope.closeMenu = function() {
        $scope.navStatus = "closeNav";
    }

    $scope.$on('ngRepeatFinished', function(ngRepeatFinishedEvent) {
        iniPos = scrollWindow.offset().top;
        scrollWindow.height($window.innerHeight - iniPos);
    });

    // var myNewScope = $scope.$new(true);
    // // Pre-fetch an external template populated with a custom scope
    // var myOtherModal = $modal({ scope: myNewScope, templateUrl: '/cm/views/editmodal.html', show: false });
    // // Show when some event occurs (use $promise property to ensure the template has been loaded)
    // $scope.showModal = function(id) {
    //     myNewScope.id = id;
    //     myOtherModal.$promise.then(myOtherModal.show);
    // };


}]);