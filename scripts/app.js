'use strict';
(function () {
    var app = angular.module("proshop", ["ngRoute", "ui.bootstrap"]);
    app.controller('errorController', function ($scope) { });
    app.config(function ($routeProvider, $locationProvider) {
        $locationProvider.html5Mode(true);
        $routeProvider
                .when("/", {
                    templateUrl: "/scripts/views/index.html",
                    controller: "HomeController"
                })
             .when("/index.html", {
                 templateUrl: "/scripts/views/index.html",
                 controller: "HomeController"
             })
              .when("/home", {
                    templateUrl: "/scripts/views/account.html",
                    controller: "AccountController"
                })
             .when("/login", {
                 templateUrl: "/scripts/views/login.html",
                 controller: "LoginController"
             })
            .when("/product-design", {
                templateUrl: "/scripts/views/product-design.html",
                controller: "ProductDesignController"
            })
            .when("/account", {
                templateUrl: "/scripts/views/account.html",
                controller: "AccountController"
            })
            .when("/product-details", {
                templateUrl: "/scripts/views/product-details.html",
                controller: "ProductDetails"
            })
            .when("/faq", {
                templateUrl: "/scripts/views/faq.html"               
            })
        .otherwise({
            redirectTo: "/error.html"
        });
    });
}());

