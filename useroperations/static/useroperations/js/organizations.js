

$(document).ready(function(){

    $(document).on("click", ".organizations .tile-header", function(){
        var elem = $(this);
        var id = elem.attr("data-id");
        var name = elem.attr("data-name");
        var searchButton = $("#geoportal-search-button");
        var facet = ["Organizations", name, id].join(",");
        search.setParam("facet", facet);
        searchButton.click();
    });

    $(document).on("click", ".organizations .data-info-container", function(){
        var elem = $(this);
        var datatype = elem.attr("data-resource");
        var organizationId = elem.closest(".tile-content").siblings(".tile-header").attr("data-id");
        var organizationName = elem.closest(".tile-content").siblings(".tile-header").attr("data-name");
        search.setParam("singleResourceRequest", datatype);
        search.setParam("facet", ["Organizations", organizationName, organizationId].join(","));
        var tileContentImg = elem.parents(".tile").find(".tile-header-img");
        tileContentImg.click();
    });

});