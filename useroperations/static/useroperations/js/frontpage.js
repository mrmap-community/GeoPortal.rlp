
/**
 * Returns the cookie if found
 */
function getCookie(cname) {
    var name = cname + "=";
    var decodedCookie = decodeURIComponent(document.cookie);
    var ca = decodedCookie.split(';');
    for(var i = 0; i <ca.length; i++) {
        var c = ca[i];
        while (c.charAt(0) == ' ') {
            c = c.substring(1);
        }
        if (c.indexOf(name) == 0) {
            return c.substring(name.length, c.length);
        }
    }
    return "";
}

function resizeIframe(obj) {
  obj.style.height = obj.contentWindow.document.body.scrollHeight + 'px';
}

function setCookie(cname, cvalue){
    document.cookie = cname + "=" + cvalue + ";path=/;";
}

function startSearch(){
    // since the all.js might be loaded slower or faster, we need to make sure it exists before we call prepareAndSearch()
    // which lives in all.js
    var script = $("#all-script");
    $(script).ready(function(){
        // Collect query parameters
        var inputTerms = $(".-js-simple-search-field").val().trim();
        search.setParam("terms", inputTerms);

        // collapse extended search if open
        var extendedSearchHeader = $(".-js-extended-search-header");
        if(extendedSearchHeader.hasClass("active")){
            extendedSearchHeader.click();
        }
        prepareAndSearch(); // search and render
    });

}

/**
 * Resize the sidebar's height according to the body content height.
 * If the body does not provide enough content to wrap the sidebar, the body needs to be resized!
 */
function resizeSidebar(){
    var sidebar = $(".sidebar-wrapper");
    var content = $(".body-content .wrapper");
    var body = $("#body-content");

    var contentLength = content.outerHeight();
    var sidebarLength = sidebar.outerHeight();

    if(sidebar.outerHeight() != body.outerHeight()){
        sidebar.outerHeight(body.outerHeight());
    }
}

function resizeMapOverlay(){
    var elem = $(this);
    var mapLayer = $(".map-viewer-overlay");
    var bodyContent = $(".body-content");
    mapLayer.outerHeight(bodyContent.outerHeight());
}

/*
 * Switch between mobile and default map viewer
 */
function toggleMapViewers(target){
    var iframe = $("#mapviewer");
    var oldSrc = iframe.attr("data-toggle");
    var src = iframe.attr("src");
    if(src !== oldSrc){
        iframe.attr("data-toggle", src);
        iframe.attr("src", oldSrc);
        iframe.toggleClass("mobile-viewer");
    }
}

function toggleSubMenu(elem){
    var elem = $(elem);
    elem.parents().children(".sidebar-area-content").slideToggle("slow");
}

function toggleMapviewer(){
    // for dsgvo not accepted
    if ($("#dsgvo").val() == "False"){
    window.location.href = "/change-profile";
    return;
    }

    // get preferred gui
    var toggler = $(".map-viewer-toggler");
    var preferred_gui = toggler.attr("data-gui");

    // start loading the iframe content
    var iframe = $("#mapviewer");
    var src = iframe.attr("src");
    var dataParams = iframe.attr("data-resource");

    // change mb_user_gui Parameter if default gui  differs
    var url = new URL(dataParams)
    var params = new URLSearchParams(url.search);
    if(preferred_gui == "Geoportal-RLP" || preferred_gui.length == 0 ){
        params.set('gui_id',"Geoportal-RLP")
    }else{
        params.set('gui_id', preferred_gui)
    }
    url.search = params.toString();
    dataParams = url.toString();
    var dataToggler = iframe.attr("data-toggle");

    if(dataParams !== src && (dataToggler == src || src == "about:blank")){
        iframe.attr("src", dataParams);
    }
    // resize the overlay
    var mapLayer = $(".map-viewer-overlay");
    resizeMapOverlay();
    // let the overlay slide in
    mapLayer.slideToggle("slow")
    mapLayer.toggleClass("closed");
    // close the sidebar
    if(!$(".sidebar-wrapper").hasClass("closed")){
        $(".sidebar-toggler").click();
    }
}

/**
 * Reset the search catalogue source back to 'primary'.
 * If a user selects e.g. the european search catalogue, goes back to the landing page
 * and reopens the search module again, the european catalogue will still be selected. This is not the
 * behaviour we want.
 */
function resetSearchCatalogue(src){
    // reset catalogue source to primary if we are not in the search module
    if(!location.pathname.includes("search") && search.getParam("source") != src){
        search.setParam("source", src)
    }
}

/**
 * If the search page is reloaded, e.g. due to language changing or normal F5 reload,
 * we need to make sure the search starts again automatically. Otherwise the users will be confused and cry.
 */
function startAutomaticSearch(){
    // wait until the document is loaded completely, then start the automatic search!
    $(document).ready(function(){
        if(location.pathname.includes("search")){
            var searchBody = $(".search-overlay-content");
            if(searchBody.html().trim().length == 0){
                prepareAndSearch();
            }
        }

    });
}


$(document).on("click", ".mobile-button", function(){
    // get wmc id
    var elem = $(this).parents(".tile").find(".tile-header");
    var id = elem.attr("data-id");
    // get rid of 'WMC=' which is needed for the usual call
    id = id.split("=")[1];
    openInNewTab("/mapbender/extensions/mobilemap2/index.html?wmc_id=" + id);
});

$(document).on("click", ".map-viewer-selector", function(){
    var elem = $(this);
    var mapViewerSelector = $(".map-applications-toggler");

    elem.toggleClass("open")
    // close other menu
    if(mapViewerSelector.hasClass("open") && elem.hasClass("open")){
        mapViewerSelector.click();
    }

    var viewerList = $(".map-viewer-list");
    viewerList.slideToggle("medium");

    var sideBar = elem.closest(".map-sidebar");
    if((mapViewerSelector.hasClass("open") || elem.hasClass("open")) && !sideBar.hasClass("open")){
        sideBar.addClass("open");
    }else if(!mapViewerSelector.hasClass("open") && !elem.hasClass("open") && sideBar.hasClass("open")){
        sideBar.removeClass("open");
    }
});

$(document).on("click", ".map-applications-toggler", function(){
    var elem = $(this);
    var mapViewerSelector = $(".map-viewer-selector");

    elem.toggleClass("open")
    // close other menu
    if(mapViewerSelector.hasClass("open") && elem.hasClass("open")){
        mapViewerSelector.click();
    }

    var applicationsList = $(".map-applications-list");
    applicationsList.slideToggle("medium");

    var sideBar = elem.closest(".map-sidebar");
    if((mapViewerSelector.hasClass("open") || elem.hasClass("open")) && !sideBar.hasClass("open")){
        sideBar.addClass("open");
    }else if(!mapViewerSelector.hasClass("open") && !elem.hasClass("open") && sideBar.hasClass("open")){
        sideBar.removeClass("open");
    }
});

$(document).on("click", ".map-viewer-list-entry", function(){
    var elem = $(this);
    var iFrame = $("#mapviewer");

    // move viewport for user
    window.scrollTo({
        top:150,
        left:0,
        behavior:'smooth'
    });

    gui_id = elem.attr("data-resource");
    if(gui_id.includes("http")){
        // simply paste in the new url
        iFrame.attr("src", gui_id);
    }else{
        var srcUrl = null;
        if(!iFrame.attr("src").includes("gui_id")){
            // there is a url in the src which can not be changed directly. We need to go back to the fallback uri!
            srcUrl = iFrame.attr("data-resource");
        }else{
            // this is just another gui id, we need to put it inside the matching parameter
            srcUrl = iFrame.attr("src");
        }
        var url = new URL(srcUrl);
        var searchParams = new URLSearchParams(url.search);
        searchParams.set("gui_id", gui_id);

        url.search = searchParams.toString();
        src = url.toString();

        iFrame.attr("src", src);
    }

    // close menu
    $(".map-viewer-selector").click();
});

$(document).on("click", ".map-applications-list-entry", function(){
    var elem = $(this);
    var iframe = $("#mapviewer");

    // move viewport for user
    window.scrollTo({
        top:150,
        left:0,
        behavior:'smooth'
    });

    iframeSrc = iframe.attr("src").toString();
    iframeDataParams = iframe.attr("data-resource").toString();

    var srcUrl = new URL(iframeDataParams);
    var params = new URLSearchParams(srcUrl.search);
    params.set('gui_id',elem.attr("data-id"))

    srcUrl.search = params.toString();
    src = srcUrl.toString();

    iframe.attr("src", src);

    // close list menu
    $(".map-applications-toggler").click();

});

$(document).on("keypress", "#id_message", function(){
    var elem = $(this);
    var out = $(".foot-note span");
    var maxLength = elem.attr("maxlength");
    var restLength = maxLength - elem.val().length;
    if((restLength == 0 && !out.hasClass("warning")) ||
        (restLength > 0 && out.hasClass("warning"))){
        out.toggleClass("warning");
    }
    out.html(restLength);
});

/*
 * Handles the sidebar toggler functionality
 */
$(document).on("click", ".sidebar-toggler", function(){
    var elem = $(this);
    var sidebar = $(".sidebar-wrapper");
    var bodyContent = $("#body-content");
    sidebar.toggleClass("closed");
    var isClosed = sidebar.hasClass("closed");
    setCookie("sdbr-clsd", isClosed);
    bodyContent.toggleClass("sidebar-open");
});

/*
 * Handles the sidebar toggler functionality
 */
$(document).on("click", ".map-viewer-button", function(){
    var elem = $(this);
    var form = $("#map-viewer-selector");
    form.toggle("fast");
});

$(".body-content").change(function(){
});

$(document).on("click", "#geoportal-search-button", function(){
    // for dsgvo not accepted
    if ($("#dsgvo").val() == "False"){
        window.location.href = "/change-profile";
        return;
    }

    // check if the search page is already opened
    if(!window.location.pathname.includes("/search")){
        // no index page loaded for search -> load it!
        // we lose all searchbar data on reloading, so we need to save it until the page is reloaded
        //window.sessionStorage.setItem("startSearch", true);
        window.sessionStorage.setItem("searchbarBackup", $(".-js-simple-search-field").val().trim());
        window.sessionStorage.setItem("isSpatialCheckboxChecked", $("#spatial-checkbox").is(":checked"));
        window.location.href = "/search";
    }else{
        startSearch();
    }

});


 $(document).on("click", ".quickstart.search", function(event){
     event.preventDefault();
     var elem = $(this);
     var resource = elem.attr("data-resource");
     var searchButton = $("#geoportal-search-button");
     search.setParam("singleResourceRequest", resource);
     search.setParam("source", "primary");
     searchButton.click();
 });

 $(document).on("click", ".topics .tile-header", function(){
     var elem = $(this);
     var filterName = elem.attr("data-name");
     var filterId = elem.attr("data-id");
     var searchButton = $("#geoportal-search-button");
     search.setParam("facet", ["INSPIRE", filterName, filterId].join(","));
     searchButton.click();
 });

 $(document).on("hover", ".topics .tile-header", function(){
     var elem = $(this).children(".tile-header-img").children(".tile-img");
     elem.toggleClass("highlight");
 });


 $(document).on("click", ".favourite-wmcs .tile-header", function(event){
    event.preventDefault();
    var elem = $(this);
    if(elem.attr("id") == "show-all-tile-content"){
        $("#geoportal-search-button").click();
        return;
    }
    href = elem.attr("data-id");
    //if($("#mapviewer").hasClass("mobile-viewer")){
    //    toggleMapViewers();
    //}
    startAjaxMapviewerCall(href);

 });

$(document).on("click", ".message-toggler", function(){
    var elem = $(this);
    elem.toggle();
    elem.parent().toggle();
});

/*
// Password message popup
$(document).on('focus blur', "#id_password", function(){
    // use nice transition css hack from
    // https://css-tricks.com/content-jumping-avoid/
    $("#password_message").toggleClass("in");
    setTimeout(resizeSidebar, 1000);
});
*/

/*
DEPRECATED - REMOVE WHEN SURE THAT EVERYTHING IS SAFE


// Client side password validation
$(document).on('keyup', "#id_password", function(){

  var PasswordInput = document.getElementById("id_password");
  var letter = document.getElementById("letter");
  var capital = document.getElementById("capital");
  var number = document.getElementById("number");
  var length = document.getElementById("length");

  // Validate lowercase letters
  if(PasswordInput.value.match(/[a-z]/g)) {
    letter.classList.remove("invalid");
    letter.classList.add("valid");
  } else {
    letter.classList.remove("valid");
    letter.classList.add("invalid");
  }

  // Validate capital letters
  if(PasswordInput.value.match(/[A-Z]/g)) {
    capital.classList.remove("invalid");
    capital.classList.add("valid");
  } else {
    capital.classList.remove("valid");
    capital.classList.add("invalid");
  }

  // Validate numbers
  if(PasswordInput.value.match(/[0-9]/g)) {
    number.classList.remove("invalid");
    number.classList.add("valid");
  } else {
    number.classList.remove("valid");
    number.classList.add("invalid");
  }

  // Validate length
  if(PasswordInput.value.length >= 9) {
    length.classList.remove("invalid");
    length.classList.add("valid");
  } else {
    length.classList.remove("valid");
    length.classList.add("invalid");
  }

});

*/


$(document).on('click', "#change-form-button", function(){

  var userLang = navigator.language || navigator.userLanguage;
  var PasswordInput = document.getElementById("id_password");
  var PasswordInputConfirm = document.getElementById("id_passwordconfirm");

  /*
  if(PasswordInput.value != PasswordInputConfirm.value) {
    if(userLang == "de") {
      alert("Passwörter stimmen nicht überein");
    } else {
      alert("Passwords do not match");
    }
    event.preventDefault();

  }
  */

});


//captcha refresh
$(function() {
    // Add refresh button after field (this can be done in the template as well)
    $('img.captcha').after(
            $('<a href="#void" class="captcha-refresh">↻</a>')
            );

    // Click-handler for the refresh-link
    $('.captcha-refresh').click(function(){
        var $form = $(this).parents('form');
        var url = location.protocol + "//" + window.location.hostname + ":"
                  + location.port + "/captcha/refresh/";

        // Make the AJAX-call
        $.getJSON(url, {}, function(json) {
            $form.find('input[name="captcha_0"]').val(json.key);
            $form.find('img.captcha').attr('src', json.image_url);
        });

        return false;
    });
});




$(window).resize(function(){
    resizeSidebar();
    resizeMapOverlay();
});


/*
 * Contains functions that shall be executed when the page is reloaded
 */
$(window).on("load", function(param){
    resizeSidebar();
    resizeMapOverlay();

    var searchbar = $(".-js-simple-search-field");
    var checkbox = $("#spatial-checkbox");
    if (window.sessionStorage.getItem("isSpatialCheckboxChecked") == 'true'){
        checkbox.prop("checked", true);
    }

    var searchbarBackup = window.sessionStorage.getItem("searchbarBackup");
    if (searchbarBackup !== null){
        searchbar.val(searchbarBackup);
        window.sessionStorage.removeItem("searchbarBackup");
    }
    window.sessionStorage.removeItem("isSpatialCheckboxChecked");

    var current_page_area = $(".current-page").parents(".sidebar-area-content");
    current_page_area.show();

    // check if a service is called via GET (wmc or wms)
    var route = location.pathname;
    var params = location.search;
    if(route.includes("/map")){
        var params = location.search;
        if(params.length > 0 ){
            params = params.replace("?", "");
            startAjaxMapviewerCall(params);
        }
    }

});

$(document).on("scroll", function(){
    var searchbar = $(".middle-header-top");
    // check if searchbar is out of viewport
    var searchbarPositionHeight = searchbar.outerHeight() + searchbar.innerHeight();
    // get viewport Y offset
    var viewportOffset = window.pageYOffset;
    if(searchbarPositionHeight <= viewportOffset){
        // make searchbar sticky to the viewport top
        searchbar.addClass("sticky-top");
    }else{
        // revert this effect
        searchbar.removeClass("sticky-top");
    }
})


/*
 * Things that should start when the document is fully loaded
 */
$(document).ready(function(){
    resizeSidebar();
    resizeMapOverlay();

    resetSearchCatalogue("primary");
    startAutomaticSearch();


    // show and auto hide messages
    $(".messages-container").delay(500).slideToggle("medium");
    $(".messages-container").delay(5000).slideToggle("medium");
});

