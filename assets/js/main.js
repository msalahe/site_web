import PerfectScrollbar from 'perfect-scrollbar';
import Stepper from 'bs-stepper';

$(document).ready(function() {
    "use strict";

    /* scrollbar */
    if ($(".quick-menu").length) {
        new PerfectScrollbar(".quick-menu");
    }

    if ($(".notify-list").length) {
        new PerfectScrollbar(".notify-list");
    }

    if ($(".search-content").length) {
        new PerfectScrollbar(".search-content");
    }

    /* Light & Dark Mode */
    $("#DarkMode").on("click", function () {
        $("html").attr("data-bs-theme") == "dark" ? $("html").attr("data-bs-theme", "light") : $("html").attr("data-bs-theme", "dark");
    });

    $(".dark-mode i").click(function () {
        $(this).text(function (i, v) {
            return v === 'dark_mode' ? 'light_mode' : 'dark_mode';
        });
    });

    $(".dark-mode").click(function () {
        $("html").attr("data-bs-theme", function (i, v) {
            return v === 'dark' ? 'light' : 'dark';
        });
    });

    // switcher 
    $("#LightTheme").on("click", function() {
        $("html").attr("data-bs-theme", "light");
    });

    $("#DarkTheme").on("click", function() {
        $("html").attr("data-bs-theme", "dark");
    });

    $("#SemiDarkTheme").on("click", function() {
        $("html").attr("data-bs-theme", "semi-dark");
    });

    $("#BoderedTheme").on("click", function() {
        $("html").attr("data-bs-theme", "bodered-theme");
    });

    /* search control */
    $(".search-control").click(function(){
        $(".search-popup").addClass("d-block");
        $(".search-close").addClass("d-block");
    });

    $(".search-close").click(function(){
        $(".search-popup").removeClass("d-block");
        $(".search-close").removeClass("d-block");
    });

    $(".mobile-search-btn").click(function(){
        $(".search-popup").addClass("d-block");
    });

    $(".mobile-search-close").click(function(){
        $(".search-popup").removeClass("d-block");
    });

    /* sidenav */
    $('#sidenav').metisMenu();

    /* menu active */
    for (var e = window.location, o = $(".metismenu li a").filter(function() {
        return this.href == e;
    }).addClass("").parent().addClass("mm-active"); o.is("li");) o = o.parent("").addClass("mm-show").parent("").addClass("mm-active");
});
