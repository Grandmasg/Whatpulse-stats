<!DOCTYPE html>
<html ng-app="myApp" ng-app lang="en">
<head>
    <meta name="viewport" content="width=device-width, initial-scale=1, maximum-scale=1, user-scalable=no">
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8">
    <meta name="author" content="Grandmasg">
    <meta name="copyright" content="Grandmasg">
    <meta name="keywords" content="whatpulse teamstats">
    <meta name="description" content="Distrobuting Computing Statistics">
    <meta name="ROBOTS" content="NOFOLLOW">
    <meta http-equiv="Content-Type" content="text/html; charset=UTF-8">
    <title>Version 2.0</title>
    <link rel="icon" href="images/wp.gif" type="image/gif">
    <link href="css/bootstrap.css" rel="stylesheet">
    <link href="css/lightview/lightview.css" rel="stylesheet"/>
    <link rel="stylesheet" href="css/index.css" media="screen">
</head>
  <body>
    <ui-view name="nav"></ui-view>
    <ui-view name="date"></ui-view>
    <ui-view></ui-view>
    <ui-view name="footer"></ui-view>
  </body>           
<script src="js/angular.js"></script>
<script src="js/angular-ui-router.js"></script>
<script src="js/angular-translate.js"></script>
<script src="js/angular-sanitize.js"></script>
<script src="js/ui-bootstrap-tpls.js"></script>
<script src="app/app.js"></script>
<script src="js/jquery.js"></script>
<script src="https://ajax.googleapis.com/ajax/libs/swfobject/2.2/swfobject.js"></script>
<!--[if lt IE 9]>
  <script src="js/excanvas/excanvas.js"></script>
<![endif]-->
<script src="js/spinners/spinners.min.js"></script>
<script src="js/lightview/lightview.js"></script>
<script src="http://code.highcharts.com/stock/highstock.src.js"></script>
<script src="js/highcharts-ng.js"></script>
</html>