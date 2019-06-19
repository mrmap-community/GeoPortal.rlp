
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
    document.cookie = cname + "=" + cvalue + ";";
}

function startSearch(){
    var inputTerms = $(".-js-simple-search-field").val().trim();
    search.setParam("terms", inputTerms);
    // collapse extended search if open
    var extendedSearchHeader = $(".-js-extended-search-header");
    if(extendedSearchHeader.hasClass("active")){
        extendedSearchHeader.click();
    }
    prepareAndSearch(); // search and render

}

/**
 * Resize the sidebar's height according to the body content height.
 * If the body does not provide enough content to wrap the sidebar, the body needs to be resized!
 */
function resizeSidebar(){
    var sidebar = $(".sidebar-wrapper");
    var content = $(".body-content .wrapper");
    var body = $(".body-content");
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
    iframe.attr("data-toggle", iframe.attr("src"));
    iframe.attr("src", oldSrc);
    iframe.toggleClass("mobile-viewer");
}

$(document).on("click", ".mobile-button", function(){
    // get wmc id
    var elem = $(this).parents(".tile-content").find(".tile-content-img");
    var id = elem.find("a").attr("href").split("=")[1];
    openInNewTab("/mapbender/extensions/mobilemap2/index.html?wmc_id=" + id);
});

$(document).on("click", ".mobile-map-toggler", function(){
    toggleMapViewers();
});

$(document).on("click", ".map-applications-toggler", function(){
    var elem = $(this);
    var applicationsList = $(".map-applications-list");
    applicationsList.slideToggle("medium");
});

$(document).on("click", ".map-applications-list-entry", function(){
    var elem = $(this);
    var iframe = $("#mapviewer");
    // switch back to default map viewer if mobile is active
    if(iframe.hasClass("mobile-viewer")){
        toggleMapViewers();
    }

    iframeSrc = iframe.attr("src").toString();
    iframeDataParams = iframe.attr("data-params").toString();

    // locate gui id in attribute strings
    var iframeSrcArr = iframeSrc.split("&");
    for(var i in iframeSrcArr){
        if(iframeSrcArr[i].includes("myGui")){
            guiId = iframeSrcArr[i].split("=")[1];
            break;
        }
    }
    // replace gui id with new id
    iframeSrc = iframeSrc.replace(guiId, elem.attr("data-id"));
    iframeDataParams = iframeDataParams.replace(guiId, elem.attr("data-id"));
    iframe.attr("src", iframeSrc);
    iframe.attr("data-params", iframeDataParams);

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

$(document).on("click", ".map-viewer-toggler, #mapviewer-sidebar", function(){
    // for dsgvo not accepted
    if ($("#dsgvo").val() == "False"){
    window.location.href = "/change_profile";
    return;
    }

    // start loading the iframe content
    var iframe = $("#mapviewer");
    var src = iframe.attr("src");
    var dataParams = iframe.attr("data-params");
    if(dataParams !== src){
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
});

/*
 * Handles the sidebar toggler functionality
 */
$(document).on("click", ".sidebar-toggler", function(){
    var elem = $(this);
    var sidebar = $(".sidebar-wrapper");
    var bodyContent = $("#body-content");
    sidebar.toggleClass("closed");
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

$(document).on("click", ".sidebar-area-title", function(){
    var elem = $(this);
    elem.parents().children(".sidebar-area-content").slideToggle("slow");
});

$(document).on("click", "#geoportal-search-button", function(){
    // for dsgvo not accepted
    if ($("#dsgvo").val() == "False"){
        window.location.href = "/change_profile";
        return;
    }
    var elem = $(this);
    // check if the index page is already opened
    var index = $(".content-tabs.-js-tabs");
    if(index.length == 0){
        // no index page loaded for search -> load it!
        // we lose all searchbar data on reloading, so we need to save it until the page is reloaded
        window.sessionStorage.setItem("startSearch", true);
        window.sessionStorage.setItem("searchbarBackup", $(".-js-simple-search-field").val().trim());
        window.sessionStorage.setItem("isSpatialCheckboxChecked", $("#spatial-checkbox").is(":checked"));
        window.location.pathname = "/search";
    }else{
        startSearch();
    }
});


/**
 * Display info for favourite wmc tiles
 */
 $(document).on("mouseover mouseleave", ".tile-body", function(){
    var elem = $(this);
    var popup = elem.parents().children(".tile-info-popup");
    popup.toggle();
 });

 $(document).on("click", ".organizations .tile-content-img", function(){
     var elem = $(this);
     var id = elem.attr("data-id");
     var name = elem.attr("data-name");
     var searchButton = $("#geoportal-search-button");
     var facet = ["Organisationen", name, id].join(",");
     search.setParam("facet", facet);
     searchButton.click();
 });

 $(document).on("click", ".topics .tile-content-img", function(){
     var elem = $(this);
     var filterName = elem.attr("data-name");
     var filterId = elem.attr("data-id");
     var searchButton = $("#geoportal-search-button");
     search.setParam("facet", ["INSPIRE", filterName, filterId].join(","));
     searchButton.click();
 });

 $(document).on("click", ".organizations .data-info-container", function(){
     var elem = $(this);
     var datatype = elem.attr("data-resource");
     search.resources_rlp[datatype] = false;
     /*
     * ToDo: THis does not work yet due to the 'great' structure of the search engine...
     */
     var tileContentImg = elem.parents(".tile").find(".tile-content-img");
     tileContentImg.click();
 });


 $(document).on("click", ".tile-content-img", function(event){
    event.preventDefault();
    var elem = $(this);
    if(elem.attr("id") == "show-all-tile-content"){
        $("#geoportal-search-button").click();
        return;
    }
    href = elem.children("a").attr("href");
    startAjaxMapviewerCall(href);

 });
$(document).on("click", ".message-toggler", function(){
    var elem = $(this);
    elem.toggle();
    elem.parent().toggle();
});


// Password message popup
$(document).on('focus', "#id_password", function(){
    $("#password_message").fadeIn("slow");
    //document.getElementById("password_message").style.display = "block";
});

$(document).on('blur', "#id_password", function(){
    $("#password_message").fadeOut("slow");
    //document.getElementById("password_message").style.display = "none";
});

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


$(document).on('click', "#change-form-button", function(){

  var userLang = navigator.language || navigator.userLanguage;
  var PasswordInput = document.getElementById("id_password");
  var PasswordInputConfirm = document.getElementById("id_passwordconfirm");


  if(PasswordInput.value != PasswordInputConfirm.value) {
    if(userLang == "de") {
      alert("Passwörter stimmen nicht überein");
    } else {
      alert("Passwords do not match");
    }
    event.preventDefault();
  }

});

$(document).on("click", ".cookie-button", function(){
    var cookieBanner = $(".cookie-container");
    cookieBanner.toggle();
    // set cookie, so we know that the user already accepted on the next visit
    setCookie("Geoportal-RLP", true)
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

    // check if there was a search call from a non-search-index page on the geoportal
    var checkForSearchStart = window.sessionStorage.getItem("startSearch");
    window.sessionStorage.removeItem("startSearch");
    if(checkForSearchStart){
        // use and remove backup data from last page
        var searchbar = $(".-js-simple-search-field");
        var checkbox = $("#spatial-checkbox");
        if (window.sessionStorage.getItem("isSpatialCheckboxChecked") == 'true'){
            checkbox.prop("checked", true);
        }
        searchbar.val(window.sessionStorage.getItem("searchbarBackup"));
        window.sessionStorage.removeItem("searchbarBackup");
        window.sessionStorage.removeItem("isSpatialCheckboxChecked");
        startSearch();
    }

    var current_page_area = $(".current-page").parents(".sidebar-area-content");
    current_page_area.show();

});


/*
 * Things that should start when the document is fully loaded
 */
$(document).ready(function(){
    resizeSidebar();
    resizeMapOverlay();


    // show and auto hide messages
    $(".messages-container").delay(500).slideToggle("medium");
    $(".messages-container").delay(5000).slideToggle("medium");
});

