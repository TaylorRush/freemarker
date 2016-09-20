// var myApp = angular.module('creativeManagement', ['ngRoute', 'projectsControllers', 'angular.filter', 'ui.bootstrap', 'propFilters']);
var myApp = angular.module('creativeManagement', ['ngAnimate', 'ngRoute', 'checklist-model', 'mainView', 'mgcrea.ngStrap', 'angular.filter']);
var urlApi="http://localhost:8888/db/public/api/"
// var urlApi="http://fm.chacon.mx/db/public/api/"


myApp.filter('cmFilter', ['$filter', function($filter) {
    return function(items, txtbox, refine) {
        var extraList = [];
        var currentList = [];
        var refineOn = false;
        angular.forEach(items, function(item) {
            var found = $filter('filter')([item], { $: txtbox }, false);
            if (found.length) currentList.push(item);
        });

        // function inArr(o, arr) {
        //     for (var i = 0; i < arr.length; i++) {
        //         if (angular.equals(arr[i], o)) {
        //             return true;
        //         }
        //     }
        //     return false;
        // }

        var findOne = function(haystack, arr) {
            return arr.some(function(v) {
                return haystack.map(function(c) {
                    return c.toLowerCase();
                }).indexOf(v.toLowerCase()) >= 0;
            });
        };



        // current list
        angular.forEach(currentList, function(obj, index) {
            var internalReq = [];
            var datePass = true;
            var sizePass = true;
            var typePass = true;
            var featurePass = true;
            var verticalPass = true;

            //handle vertical
            if (refine.vertical) {
                refineOn = true;
                if (obj['vertical_id']) {
                    if (obj['vertical_id'] != refine.vertical.id) {
                        verticalPass = false;
                    }
                } else {
                    verticalPass = false;
                }
            } else {
                verticalPass = true;
            }

            // handle DATE
            if (refine.month || refine.year) {
                refineOn = true;
                if (obj['close_date']) {
                    var mo = $filter('date')(obj['close_date'], "MMMM");
                    var yr = $filter('date')(obj['close_date'], "yyyy");

                    if (refine.month && refine.year) {
                        if (mo == refine.month && yr == refine.year) {
                            datePass = true;
                        } else {
                            datePass = false;
                        }

                    } else if (refine.month) {
                        if (mo != refine.month) {
                            datePass = false;
                        }
                    } else if (refine.year) {
                        if (yr != refine.year) {
                            datePass = false;
                        }
                    }
                } else {
                    datePass = false;
                }
            } else {
                datePass = true;
            }

            // handle TYPES
            if (refine.types.length) {
                refineOn = true;
                if (obj['types'].length) {
                    if (!findOne(refine.types, obj['types'])) {
                        typePass = false;
                    }
                } else {
                    typePass = false;
                }
            } else {
                typePass = true;
            }

            // handle FEATURES
            if (refine.features.length) {
                refineOn = true;
                if (obj['features'].length) {
                    if (!findOne(refine.features, obj['features'])) {
                        featurePass = false;
                    }
                } else {
                    featurePass = false;
                }
            } else {
                featurePass = true;
            }

            if (datePass && typePass && featurePass && verticalPass) {
                if (refine.sizes.length) {
                    refineOn = true;
                    if (obj['sizes'].length) {
                        if (findOne(refine.sizes, obj['sizes'])) {
                            extraList.push(obj);
                        }
                    }
                } else {
                    extraList.push(obj);
                }
            }

        });

        // console.log(extraList.length)
        if (extraList.length || refineOn) {
            return extraList;
        } else {
            return currentList;
        }
    };
}]);


myApp.factory('httpInterceptor', function($q, $rootScope, $log) {

    var numLoadings = 0;
    var dbRequests = 0;
    
    // var urlApi="http://localhost:8888/api2/public/api/"

    return {
        request: function(config) {

            numLoadings++;

            if (config['url'].startsWith(urlApi)) {
                dbRequests++;
                $rootScope.$broadcast("db_start");
            }

           // config['headers'] = {'x-api-key':'fs4eV0cN00wiyesJpmBD'}

            // Show loader
            $rootScope.$broadcast("loader_show");
            return config || $q.when(config)

        },
        response: function(response) {
            if (response.config['url'].startsWith(urlApi)) {
                if ((--dbRequests) === 0) {
                    $rootScope.$broadcast("db_end");
                }
            }
            if ((--numLoadings) === 0) {
                // Hide loader
                $rootScope.$broadcast("loader_hide");
            }

            return response || $q.when(response);

        },
        responseError: function(response) {
            if (response.config['url'].startsWith(urlApi)) {
                if ((--dbRequests) === 0) {
                    $rootScope.$broadcast("db_end");
                }
            }

            if (!(--numLoadings)) {
                // Hide loader
                $rootScope.$broadcast("loader_hide");
            }

            return $q.reject(response);
        }
    };
});



myApp.config(['$routeProvider', '$httpProvider', function($routeProvider, $httpProvider) {
    // $httpProvider.useApplyAsync(true);
    $httpProvider.interceptors.push('httpInterceptor');

    $routeProvider.
    when('/sales', {
        templateUrl: 'views/search.html',
        controller: 'SearchController'
    }).
    when('/creative_team/:pro', {
        templateUrl: 'views/search.html',
        controller: 'SearchController'
    }).
    when('/campaign/:itemID', {
        templateUrl: 'views/editCampaign.html',
        controller: 'CampaignEdit'
    }).
    // when('/project-detail/:itemId', {
    //     templateUrl: 'views/project-detail.html',
    //     controller: 'DetailProjectController'
    // }).
    otherwise({
        redirectTo: '/sales'
    });

}]);

myApp.directive("loader", function($rootScope) {
    return function($scope, element, attrs) {
        $scope.$on("loader_show", function() {
            return element.show();
        });
        return $scope.$on("loader_hide", function() {
            return element.hide();
        });
    };
});

myApp.directive('resize', ['$window', function($window) {
    return {
        link: link,
        restrict: 'A'
    };

    function link(scope, element, attrs) {
        scope.winSize = { width:$window.innerWidth, height:$window.innerHeight};

        function onResize() {
            // uncomment for only fire when $window.innerWidth change   
            //if (scope.width !== $window.innerWidth)
            {
                scope.winSize = { width:$window.innerWidth, height:$window.innerHeight};
                scope.$digest();
            }
        }

        function cleanUp() {
            angular.element($window).off('resize', onResize);
        }

        angular.element($window).on('resize', onResize);
        scope.$on('$destroy', cleanUp);
    }
}]);

myApp.directive('onFinishRender', ['$timeout', function ($timeout) {
    return {
        restrict: 'A',
        link: function (scope, element, attr) {
            if (scope.$last === true) {
                $timeout(function () {
                    scope.$emit(attr.onFinishRender);
                });
            }
        }
    }
}]);
// mainView.directive('focusOnCondition', ['$timeout',
//     function($timeout) {
//         var checkDirectivePrerequisites = function(attrs) {
//             if (!attrs.focusOnCondition && attrs.focusOnCondition != "") {
//                 throw "FocusOnCondition missing attribute to evaluate";
//             }
//         }

//         return {
//             restrict: "A",
//             link: function(scope, element, attrs, ctrls) {
//                 checkDirectivePrerequisites(attrs);

//                 scope.$watch(attrs.focusOnCondition, function(currentValue, lastValue) {
//                     if (currentValue == true) {
//                         $timeout(function() {
//                             element.focus();
//                         });
//                     }
//                 });
//             }
//         };
//     }
// ]);


