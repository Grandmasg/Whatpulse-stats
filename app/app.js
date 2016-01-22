var app = angular.module('myApp', ['ui.router', 'ui.bootstrap', 'ngSanitize', 'highcharts-ng']);

app.config(function($stateProvider, $urlRouterProvider) {

	$urlRouterProvider.otherwise('/daily/offset=0/team=');

	$stateProvider.state('app', {
		abstract: true,
		views: {
			nav: {
				templateUrl: 'ajax/nav.html',
				controller: 'NavController'
			},
			date: {
				templateUrl: 'ajax/date.html',
				controller: 'DateController'
			},
			'': {
				templateUrl: 'ajax/content.html',
				controller: 'ContentController as Content'
			},
			footer: {
				templateUrl: 'ajax/footer.html',
				controller: 'FooterController'
			}
		}
	});

	$stateProvider.state('app.daily', {
		url: '/daily/offset=:offset/team=:team',
		templateUrl: 'ajax/daily.html',
		controller: 'DailyController'
	});
	
	$stateProvider.state('app.totals', {
		url: '/totals/offset=:offset/team=:team',
		templateUrl: 'ajax/totals.html',
		controller: 'TotalsController'
	});
	
	$stateProvider.state('app.xml', {
		url: '/xml/offset=:offset/team=:team',
		templateUrl: 'ajax/xml.html',
		controller: 'XmlController'
	});

});

app.filter('startFrom', function() {
	return function(input, start) {
		if(input) {
			start = +start; //parse to int
			return input.slice(start);
		}
		return [];
	};
});

app.filter('textOrNumber', function ($filter) {
	return function (input, fractionSize) {
		if (isNaN(input)) {
			return input;
		} else {
			return $filter('number')(input, fractionSize);
		};
	};
});

app.filter('highlight', function () {
	return function (text, search, caseSensitive) {
		if (text && (search || angular.isNumber(search))) {
			text = text.toString();
			search = search.toString();
			if (caseSensitive) {
				return text.split(search).join('<span class="highlightedText">' + search + '</span>');
			} else {
				return text.replace(new RegExp(search, 'gi'), '<span class="highlightedText">$&</span>');
			}
		} else {
			return text;
		}
	};
});

app.filter('sumByKey', function() {
	return function(data, key) {
		if (typeof(data) === 'undefined' || typeof(key) === 'undefined') {
			return 0;
		}

		var sum = 0;
		for (var i = data.length - 1; i >= 0; i--) {
			sum += parseInt(data[i][key]);
		}
 
		return sum;
	};
});

app.controller('DateController', function($http, $scope, $stateParams, $location) {
	$scope.Offset = ($location.path().substring(1).split('/')[1]).split('=')[1].toString();
	$scope.Team = ($location.path().substring(1).split('/')[2]).split('=')[1];
	$scope.CurrentRoute = $location.path().substring(1).split('/')[0];
	
	$scope.Offsetm5 = parseInt($scope.Offset -5);
	$scope.Offsetm3 = parseInt($scope.Offset -3);
	$scope.Offsetm2 = parseInt($scope.Offset -2);
	$scope.Offsetm1 = parseInt($scope.Offset -1);
	$scope.Offset = parseInt($scope.Offset);
	$scope.Offsetp1 = parseInt($scope.Offset + 1);
	$scope.Offsetp5 = parseInt($scope.Offset + 5);

	myDate = new Date();
	$scope.daym5 = parseInt(myDate.getTime()) + ((parseInt($scope.Offset)-5)*24*60*60*1000);
	$scope.daym3 = parseInt(myDate.getTime()) + ((parseInt($scope.Offset)-3)*24*60*60*1000);
	$scope.daym2 = parseInt(myDate.getTime()) + ((parseInt($scope.Offset)-2)*24*60*60*1000);
	$scope.daym1 = parseInt(myDate.getTime()) + ((parseInt($scope.Offset)-1)*24*60*60*1000);
	$scope.day = parseInt(myDate.getTime()) + (parseInt($scope.Offset)*24*60*60*1000);
	$scope.dayp1 = parseInt(myDate.getTime()) + ((parseInt($scope.Offset)+1)*24*60*60*1000);
	$scope.dayp5 = parseInt(myDate.getTime()) + ((parseInt($scope.Offset)+5)*24*60*60*1000);
});

app.controller('NavController', function ($http, $scope, $location) {
	$scope.Offset = ($location.path().substring(1).split('/')[1]).split('=')[1];
	$scope.Team = ($location.path().substring(1).split('/')[2]).split('=')[1];
	$scope.CurrentRoute = $location.path().substring(1).split('/')[0];
	
	$http({
		method: 'POST',
		url: 'ajax/team.php',
		headers: {'Content-Type': 'application/x-www-form-urlencoded'},
		transformRequest: function(obj) {
			var str = [];
			for(var p in obj)
			str.push(encodeURIComponent(p) + "=" + encodeURIComponent(obj[p]));
			return str.join("&");
		},
		data: {get: 'team'}
	}).success(function(datateam){
		$scope.listteam = datateam;
	});
});

app.controller('DailyController', function($http, $scope, $stateParams, $location, $timeout, $log) {
	$scope.Offset = ($location.path().substring(1).split('/')[1]).split('=')[1];
	$scope.Team = ($location.path().substring(1).split('/')[2]).split('=')[1];
	$scope.CurrentRoute = $location.path().substring(1).split('/')[0];
	
	myDate = new Date();
	$scope.day = parseInt(myDate.getTime()) + (parseInt($scope.Offset)*24*60*60*1000);

	$scope.loading=true;
	var Offset = '';
	var Team = '';
	var diffrence_keys = [];
	var diffrence_clicks = [];
	var diffrence_DownloadMB = [];
	var diffrence_UploadMB = [];
	var diffrence_UptimeSeconds = [];
	var diffrence_Pulses = [];
	var username = [];
	if ($scope.Offset && $scope.Team == '') {
		$http({
			method: 'POST',
			url: 'ajax/daily.php',
			headers: {'Content-Type': 'application/x-www-form-urlencoded'},
			transformRequest: function(obj) {
				var str = [];
				for(var p in obj)
				str.push(encodeURIComponent(p) + "=" + encodeURIComponent(obj[p]));
				return str.join("&");
			},
			data: {get: 'daily', Offset: $scope.Offset}
		}).success(function(data){
			$scope.list = data;
			$scope.diffrence_keys = data.diffrence_keys;
			$scope.filteredItems = $scope.list.length; //Initially for no filter  
			$scope.totalItems = $scope.list.length;
			if ($scope.entryLimit > $scope.filteredItems ) {
				$scope.fteam = $scope.filteredItems;
			} else {
				$scope.fteam = $scope.entryLimit;
			}
			diffrence_keys.length = 0;
			diffrence_clicks.length = 0;
			diffrence_DownloadMB.length = 0;
			diffrence_UploadMB.length = 0;
			diffrence_UptimeSeconds.length = 0;
			diffrence_Pulses.length = 0;
			username.length = 0;
			for (var i = 0; i < $scope.fteam; i++) {
				diffrence_keys.push( $scope.list[i].diffrence_keys );
				diffrence_clicks.push( $scope.list[i].diffrence_clicks );
				diffrence_DownloadMB.push( $scope.list[i].diffrence_DownloadMB );
				diffrence_UploadMB.push( $scope.list[i].diffrence_UploadMB );
				diffrence_UptimeSeconds.push( $scope.list[i].diffrence_UptimeSeconds );
				diffrence_Pulses.push( $scope.list[i].diffrence_Pulses );
				username.push( $scope.list[i].Username );
			}
			$scope.loading=false;
		});
		Offset = $scope.Offset;
		Team = '';
	}
	if ($scope.Offset && $scope.Team || $scope.Offset == 0 && !$scope.Team == '') {
		$http({
			method: 'POST',
			url: 'ajax/daily.php',
			headers: {'Content-Type': 'application/x-www-form-urlencoded'},
			transformRequest: function(obj) {
				var str = [];
				for(var p in obj)
				str.push(encodeURIComponent(p) + "=" + encodeURIComponent(obj[p]));
				return str.join("&");
			},
			data: {get: 'daily', Offset: $scope.Offset, Team: $scope.Team}
		}).success(function(data){
			$scope.list = data;
			$scope.diffrence_keys = data.diffrence_keys;
			$scope.filteredItems = $scope.list.length; //Initially for no filter  
			$scope.totalItems = $scope.list.length;
			if ($scope.entryLimit > $scope.filteredItems ) {
				$scope.fteam = $scope.filteredItems;
			} else {
				$scope.fteam = $scope.entryLimit;
			}
			diffrence_keys.length = 0;
			diffrence_clicks.length = 0;
			diffrence_DownloadMB.length = 0;
			diffrence_UploadMB.length = 0;
			diffrence_UptimeSeconds.length = 0;
			diffrence_Pulses.length = 0;
			username.length = 0;
			for (var i = 0; i < $scope.fteam; i++) {
				diffrence_keys.push( $scope.list[i].diffrence_keys );
				diffrence_clicks.push( $scope.list[i].diffrence_clicks );
				diffrence_DownloadMB.push( $scope.list[i].diffrence_DownloadMB );
				diffrence_UploadMB.push( $scope.list[i].diffrence_UploadMB );
				diffrence_UptimeSeconds.push( $scope.list[i].diffrence_UptimeSeconds );
				diffrence_Pulses.push( $scope.list[i].diffrence_Pulses );
				username.push( $scope.list[i].Username );
			}
			$scope.loading=false;
		});
		Offset = $scope.Offset;
		var Team = '/Team=' + $scope.Team;
	}

	$scope.display = function(y) {
		if (y < 0) {
			return "(<img src=\"images/up.gif\" alt=\"\"> " + (-1*y) + ")";
		} else if (y > 0) {
			return "(<img src=\"images/down.gif\" alt=\"\"> " + y + ")";
		} else if (y == 0) {
			 return "(<img src=\"images/stil.gif\" alt=\"\">)";
		}
	};
	
	$scope.rank = function(x,y) {
		diff = x-y;
		if ( y == null || y == "" ) { return "(<img src=\"images/new.gif\" alt=\"\">)"; }
		else if ( x < y ) { return "(<img src=\"images/up.gif\" alt=\"\"> " + (diff*-1) + ")"; }
		else if ( x > y ) { return "(<img src=\"images/down.gif\" alt=\"\"> " + diff + ")"; }
		else if ( x == y ) { return "(<img src=\"images/stil.gif\" alt=\"\">)"; }
	};
	
	$scope.GB = function(x) {
		GB = 1024;
		TB = 1024 * 1024;
		if (x < 1024) {
			return Number(Math.round(x+'e2')+'e-2') + " MB";	
		} else if (x > GB && x < TB) {
			y = x / GB;
			return Number(Math.round(y+'e2')+'e-2') + " GB";
		} else if (x > TB) {
			y = x / TB;
			return Number(Math.round(y+'e2')+'e-2') + " TB";   
		}
	};

	$scope.maxSize = 5;
	$scope.currentPage = 1; //current page
	$scope.entryLimit = '25'; //max no of items to display in a page
	$scope.sortType = 'today'; // set the default sort type
	$scope.sortReverse = false;  // set the default sort order
	$('thead#daily tr th a#' + $scope.sortType + ' i').removeClass().addClass('glyphicon glyphicon-chevron-up pull-right');
	
	$scope.setPage = function (pageNo) {
		$scope.currentPage = pageNo;
	};

	$scope.pageChanged = function() {
		diffrence_keys.length = 0;
		diffrence_clicks.length = 0;
		diffrence_DownloadMB.length = 0;
		diffrence_UploadMB.length = 0;
		diffrence_UptimeSeconds.length = 0;
		diffrence_Pulses.length = 0;
		username.length = 0;
		$log.log('Page changed to: ' + $scope.currentPage);
		$scope.begin = ($scope.currentPage -1) * $scope.entryLimit;
		$scope.calc = $scope.currentPage * $scope.entryLimit;
		if ($scope.calc > $scope.filteredItems) {
			$scope.eind = $scope.filteredItems;
		} else {
			$scope.eind = $scope.calc;
		}
		 
		for (var i = $scope.begin; i < $scope.eind; i++) {
			diffrence_keys.push( $scope.list[i].diffrence_keys );
			diffrence_clicks.push( $scope.list[i].diffrence_clicks );
			diffrence_DownloadMB.push( $scope.list[i].diffrence_DownloadMB );
			diffrence_UploadMB.push( $scope.list[i].diffrence_UploadMB );
			diffrence_UptimeSeconds.push( $scope.list[i].diffrence_UptimeSeconds );
			diffrence_Pulses.push( $scope.list[i].diffrence_Pulses );
			username.push( $scope.list[i].Username );
		}
	};

	$scope.filter = function() {
		$timeout(function() { 
			$scope.filteredItems = $scope.filtered.length;
			$scope.numPages = Math.ceil($scope.filteredItems/$scope.entryLimit);
		}, 10);
	};
	
	$scope.sort_by = function(sortType) {
		if ($scope.sortType != sortType) {$scope.sortReverse = true;}
		$scope.sortType = sortType;
		$scope.sortReverse = !$scope.sortReverse;
		$('thead#daily tr th a i').each(function () {
			$(this).removeClass().addClass('glyphicon glyphicon-sort pull-right');  // reset sort icon for columns with existing icons
		});
		if ($scope.sortReverse)
			$('thead#daily tr th a#' + sortType + ' i').removeClass().addClass('glyphicon glyphicon-chevron-down pull-right');
		else
			$('thead#daily tr th a#' + sortType + ' i').removeClass().addClass('glyphicon glyphicon-chevron-up pull-right');
	};
	
	$scope.highchartsNG = {
		options: {
			colors: ['#2b908f', '#90ee7e', '#f45b5b', '#7798BF', '#aaeeee', '#ff0066', '#eeaaee', '#55BF3B', '#DF5353', '#7798BF', '#aaeeee'],
			chart: {
				type: 'column',
				backgroundColor: '#3d3d3d',
				plotBackgroundColor:  '#222222',
				borderColor: '#3D3D3D',
				borderWidth: 1,
				height: '425px'
			},
			legend: {
				itemStyle: {
					color: '#ffffff',
					fontWeight: 'bold'
				},
				itemHoverStyle: {
					color: '#dddddd'
				},
				itemHiddenStyle: {
					color: '#606063',
				},
				floating: true,
				align: 'right',
				verticalAlign: 'top',
				x: -10,
			}
		},
		title: {
			text: 'Daily graph',
			x: -40,
			y: 20,
			style: {
				color: '#FFFFFF',
				font: 'bold 24px "Trebuchet MS", Verdana, sans-serif'
			}
		},
		series: [{
			"name": "Keys",
			"data": diffrence_keys,
		}, {
			"name": "Clicks",
			"data": diffrence_clicks
		}, {
			"name": "Download",
			"data": diffrence_DownloadMB,
			visible: false
		}, {
			"name": "Upload",
			"data": diffrence_UploadMB,
			visible: false
		}, {
			"name": "Uptime",
			"data": diffrence_UptimeSeconds,
			visible: false
		}, {
			"name": "Pulses",
			"data": diffrence_Pulses,
			visible: false
		}],
		xAxis: {
			categories: username,
			gridLineColor: '#707073',
			labels: {
				style: {
					color: '#E0E0E3'
				}
			},
			lineColor: '#707073',
			minorGridLineColor: '#505053',
			tickColor: '#707073',
			title: {
				style: {
					color: '#A0A0A3'
					}
				}
			},
		yAxis: {
			gridLineColor: '#707073',
			labels: {
				style: {
					color: '#fff'
				}
			},
			lineColor: '#707073',
			minorGridLineColor: '#505053',
			tickColor: '#707073',
			tickWidth: 1,
			title: {
				enabled: false,
				text: null,
				color: '#A0A0A3'
			}
		},
		tooltip: {
		backgroundColor: 'rgba(0, 0, 0, 0.85)',
			style: {
				color: '#F0F0F0'
			}
		},
		labels: {
			style: {
				color: '#707073'
			}
		},
		size: {
			height: 400
		},
		legendBackgroundColor: 'rgba(0, 0, 0, 0.5)',
		background2: '#505053',
		dataLabelsColor: '#B0B0B3',
		textColor: '#C0C0C0',
		contrastTextColor: '#F0F0F3',
		maskColor: 'rgba(255,255,255,0.3)'
	};

 	$scope.radioModel = 'column';

	$scope.graphchange = function (graph) {
		this.highchartsNG.options.chart.type = graph;
	};
	
 	$scope.radioModel1 = '400';
	
	$scope.graphchange1 = function (graph) {
		this.highchartsNG.size.height = graph;
	};
	
	$scope.checkModel = {
		Keys: true,
		Clicks: true,
		Download: false,
		Upload: false,
		Uptime: false,
		Pulses: false
	};
	
	$scope.graphchange2 = function (graph) {
		var series = this.highchartsNG.series[graph];
		alert(this.highchartsNG.series[0].visible);
		if (series.visible) {
			series.hide();
		} else {
			series.show();
		}
	};

});

app.controller('TotalsController', function($http, $scope, $stateParams, $location, $timeout, $log) {
	$scope.Offset = ($location.path().substring(1).split('/')[1]).split('=')[1];
	$scope.Team = ($location.path().substring(1).split('/')[2]).split('=')[1];
	$scope.CurrentRoute = $location.path().substring(1).split('/')[0];
	
	myDate = new Date();
	$scope.day = parseInt(myDate.getTime()) + (parseInt($scope.Offset)*24*60*60*1000);

	$scope.loading=true;
	var Offset = '';
	var Team = '';
	var keys = [];
	var clicks = [];
	var username = [];
	if ($scope.Offset && $scope.Team == '') {
		$http({
			method: 'POST',
			url: 'ajax/total.php',
			headers: {'Content-Type': 'application/x-www-form-urlencoded'},
			transformRequest: function(obj) {
				var str = [];
				for(var p in obj)
				str.push(encodeURIComponent(p) + "=" + encodeURIComponent(obj[p]));
				return str.join("&");
			},
			data: {get: 'total', Offset: $scope.Offset}
		}).success(function(data){
			$scope.list = data;
			$scope.filteredItems = $scope.list.length; //Initially for no filter  
			$scope.totalItems = $scope.list.length;
			if ($scope.entryLimit > $scope.filteredItems ) {
				$scope.fteam = $scope.filteredItems;
			} else {
				$scope.fteam = $scope.entryLimit;
			}
			keys.length = 0;
			clicks.length = 0;
			username.length = 0;
			for (var i = 0; i < $scope.fteam; i++) {
				keys.push( $scope.list[i].Keys1 );
				clicks.push( $scope.list[i].Clicks );
				username.push( $scope.list[i].Username );
			}
			$scope.loading=false;
		});
		Offset = $scope.Offset;
		Team = '';
	}
	if ($scope.Offset && $scope.Team || $scope.Offset == 0 && !$scope.Team == '') {
		$http({
			method: 'POST',
			url: 'ajax/total.php',
			headers: {'Content-Type': 'application/x-www-form-urlencoded'},
			transformRequest: function(obj) {
				var str = [];
				for(var p in obj)
				str.push(encodeURIComponent(p) + "=" + encodeURIComponent(obj[p]));
				return str.join("&");
			},
			data: {get: 'total', Offset: $scope.Offset, Team: $scope.Team}
		}).success(function(data){
			$scope.list = data;
			$scope.filteredItems = $scope.list.length; //Initially for no filter  
			$scope.totalItems = $scope.list.length;
			if ($scope.entryLimit > $scope.filteredItems ) {
				$scope.fteam = $scope.filteredItems;
			} else {
				$scope.fteam = $scope.entryLimit;
			}
			keys.length = 0;
			clicks.length = 0;
			username.length = 0;
			for (var i = 0; i < $scope.fteam; i++) {
				keys.push( $scope.list[i].Keys1 );
				clicks.push( $scope.list[i].Clicks );
				username.push( $scope.list[i].Username );
			}
			$scope.loading=false;
		});
		Offset = $scope.Offset;
		var Team = '/Team='+$scope.Team;
	}
	
	$scope.display = function(y) {
		if (y < 0) {
			return "(<img src=\"images/up.gif\" alt=\"\"> " + (-1*y) + ")";
		} else if (y > 0) {
			return "(<img src=\"images/down.gif\" alt=\"\"> " + y + ")";
		} else if (y == 0) {
			 return "(<img src=\"images/stil.gif\" alt=\"\">)";
		}
	};
	
	$scope.rank = function(x,y) {
		diff = x-y;
		if ( y == null || y == "" ) { return "(<img src=\"images/new.gif\" alt=\"\">)"; }
		else if ( x < y ) { return "(<img src=\"images/up.gif\" alt=\"\"> " + (diff*-1) + ")"; }
		else if ( x > y ) { return "(<img src=\"images/down.gif\" alt=\"\"> " + diff + ")"; }
		else if ( x == y ) { return "(<img src=\"images/stil.gif\" alt=\"\">)"; }
	};
	
	$scope.GB = function(x) {
		GB = 1024;
		TB = 1024 * 1024;
		if (x < 1024) {
			return Number(Math.round(x+'e2')+'e-2') + " MB";	
		} else if (x > GB && x < TB) {
		  y = x / GB;
		  return Number(Math.round(y+'e2')+'e-2') + " GB";
		} else if (x > TB) {
		  y = x / TB;
		  return Number(Math.round(y+'e2')+'e-2') + " TB";   
		}
	};
	
	$scope.maxSize = 5;
	$scope.currentPage = 1; //current page
	$scope.entryLimit = '25'; //max no of items to display in a page
	$scope.sortType = 'today'; // set the default sort type
	$scope.sortReverse = false;  // set the default sort order
	$('thead#totals tr th a#' + $scope.sortType + ' i').removeClass().addClass('glyphicon glyphicon-chevron-up pull-right');
   
	$scope.setPage = function (pageNo) {
		$scope.currentPage = pageNo;
	};

	$scope.pageChanged = function() {
		keys.length = 0;
		clicks.length = 0;
		username.length = 0;
		$log.log('Page changed to: ' + $scope.currentPage);
		$scope.begin = ($scope.currentPage -1) * $scope.entryLimit;
		$scope.calc = $scope.currentPage * $scope.entryLimit;
		if ($scope.calc > $scope.filteredItems) {
			$scope.eind = $scope.filteredItems;
		} else {
			$scope.eind = $scope.calc;
		}
		 
		for (var i = $scope.begin; i < $scope.eind; i++) {
			keys.push( $scope.list[i].Keys1 );
			clicks.push( $scope.list[i].Clicks );
			username.push( $scope.list[i].Username );
		}
	};
	
	$scope.filter = function() {
		$timeout(function() { 
			$scope.filteredItems = $scope.filtered.length;
			$scope.numPages = Math.ceil($scope.filteredItems/$scope.entryLimit);
		}, 10);
	};
	
	$scope.sort_by = function(sortType) {
		if ($scope.sortType != sortType) {$scope.sortReverse = true;}
		$scope.sortType = sortType;
		$scope.sortReverse = !$scope.sortReverse;
		$('thead#totals tr th a i').each(function () {
			$(this).removeClass().addClass('glyphicon glyphicon-sort pull-right');  // reset sort icon for columns with existing icons
		});
		if ($scope.sortReverse)
			$('thead#totals tr th a#' + sortType + ' i').removeClass().addClass('glyphicon glyphicon-chevron-down pull-right');
		else
			$('thead#totals tr th a#' + sortType + ' i').removeClass().addClass('glyphicon glyphicon-chevron-up pull-right');
	};
	
	$scope.highchartsNG = {
		options: {
			colors: ['#2b908f', '#90ee7e', '#f45b5b', '#7798BF', '#aaeeee', '#ff0066', '#eeaaee', '#55BF3B', '#DF5353', '#7798BF', '#aaeeee'],
			chart: {
				type: 'column',
				backgroundColor: '#3d3d3d',
				plotBackgroundColor:  '#222222',
				borderColor: '#3D3D3D',
				borderWidth: 1,
				height: '425px'
			},
			legend: {
				itemStyle: {
					color: '#ffffff',
					fontWeight: 'bold'
				},
				itemHoverStyle: {
					color: '#dddddd'
				},
				itemHiddenStyle: {
					color: '#606063',
				},
				floating: true,
				align: 'right',
				verticalAlign: 'top',
				x: -10,
			}
		},
		title: {
			text: 'Totals graph',
			x: -40,
			y: 20,
			style: {
				color: '#FFFFFF',
				font: 'bold 24px "Trebuchet MS", Verdana, sans-serif'
			}
		},
		series: [{
			"name": "Keys",
			"data": keys,
		}, {
			"name": "Clicks",
			"data": clicks
		}],
		xAxis: {
			categories: username,
			gridLineColor: '#707073',
			labels: {
				style: {
					color: '#E0E0E3'
				}
			},
			lineColor: '#707073',
			minorGridLineColor: '#505053',
			tickColor: '#707073',
			title: {
				style: {
					color: '#A0A0A3'
					}
				}
			},
		yAxis: {
			gridLineColor: '#707073',
			labels: {
				style: {
					color: '#fff'
				}
			},
			lineColor: '#707073',
			minorGridLineColor: '#505053',
			tickColor: '#707073',
			tickWidth: 1,
			title: {
				enabled: false,
				text: null,
				color: '#A0A0A3'
			}
		},
		tooltip: {
		backgroundColor: 'rgba(0, 0, 0, 0.85)',
			style: {
				color: '#F0F0F0'
			}
		},
		labels: {
			style: {
				color: '#707073'
			}
		},
		size: {
			height: 400
		},
		legendBackgroundColor: 'rgba(0, 0, 0, 0.5)',
		background2: '#505053',
		dataLabelsColor: '#B0B0B3',
		textColor: '#C0C0C0',
		contrastTextColor: '#F0F0F3',
		maskColor: 'rgba(255,255,255,0.3)'
	};

 	$scope.radioModel = 'column';

	$scope.graphchange = function (graph) {
		this.highchartsNG.options.chart.type = graph;
	};
	
 	$scope.radioModel1 = '400';
	
	$scope.graphchange1 = function (graph) {
		this.highchartsNG.size.height = graph;
	};

});

app.controller('XmlController', function($http, $scope, $stateParams, $location, $timeout, $log) {
	$scope.Offset = ($location.path().substring(1).split('/')[1]).split('=')[1];
	$scope.Team = ($location.path().substring(1).split('/')[2]).split('=')[1];
	$scope.CurrentRoute = $location.path().substring(1).split('/')[0];
	
	myDate = new Date();
	$scope.day = parseInt(myDate.getTime()) + (parseInt($scope.Offset)*24*60*60*1000);
	
	$scope.loading=true;
	
	if ($scope.Offset && $scope.Team == '') {
		$http({
			method: 'POST',
			url: 'ajax/xml.php',
			headers: {'Content-Type': 'application/x-www-form-urlencoded'},
			transformRequest: function(obj) {
				var str = [];
				for(var p in obj)
				str.push(encodeURIComponent(p) + "=" + encodeURIComponent(obj[p]));
				return str.join("&");
			},
			data: {get: 'xml', Offset: $scope.Offset}
		}).success(function(data){
			$scope.list = htmlUnescape(data);
			$scope.loading=false;
		});
	}
});

function htmlUnescape(value){
    return String(value)
        .replace(/&#091;/g, '[')
        .replace(/&#093;/g, "]");
}

app.controller('ContentController', function() {});

app.controller('FooterController', function() {});