

function orderTiles(orderBy){
    tiles = $(".tile-wrapper .tile");
    if(orderBy == "rank"){
        tiles.sort(function(a, b){
            a = $(a);
            b = $(b);
            rankA = parseInt(a.find(".tile-header").attr("data-num-resources"));
            rankB = parseInt(b.find(".tile-header").attr("data-num-resources"));
            return rankB - rankA;
        })
    }
    else if(orderBy == "title"){
        tiles.sort(function(a, b){
            a = $(a);
            b = $(b);
            titleA = a.find(".tile-header").attr("data-name");
            titleB = b.find(".tile-header").attr("data-name");
            return titleA > titleB;
        })
    }
    return tiles;
}


function toggleListView(toggleAsList){
    var wrapper = $(".tile-wrapper");
    var tiles = wrapper.find(".tile");

    if(toggleAsList){
        wrapper.addClass("tile-list-wrapper");
        //tiles.addClass("tile-list-element")
        // as list
        tiles.each(function(i, element){
            elem = $(element);
            var title = elem.find(".tile-title");
            var abstract = elem.find(".tile-content-abstract");

            // hide non abstract elements
            abstract.children().each(function(i, elem){
                elem = $(elem);
                if(!elem.hasClass("tile-content-abstract-text")){
                    elem.hide();
                }
            });

            var img = elem.find(".tile-header-img");
            img.insertBefore(title);
            abstract.insertAfter(title);
        });

    }else{
        wrapper.removeClass("tile-list-wrapper");
        tiles.removeClass("tile-list-element")
        // as tiles
        tiles.each(function(i, element){
            elem = $(element);
            var title = elem.find(".tile-title");
            var abstract = elem.find(".tile-content-abstract");

            // show non abstract elements
            abstract.children().each(function(i, elem){
                elem = $(elem);
                if(!elem.hasClass("tile-content-abstract-text")){
                    elem.show();
                }
            });

            var content = elem.find(".tile-content");
            var img = elem.find(".tile-header-img");
            img.insertAfter(title);
            abstract.appendTo(content);
        });


    }


}

$(document).ready(function(){

    $(".tile-wrapper").ready(function(){
        // make sure the order is being refreshed after a page reload
        var tileOrderBy = $(".tile-filter-order").change();
        var listViewTogglerVal = $(".switch input").is(":checked");
        toggleListView(listViewTogglerVal);
    });

    /*
    * Filter tiles
    */
    $(".tile-filter-input").on("input", function(){
        var elem = $(this);
        var val = elem.val().toUpperCase();
        var elems = $(".tile");

        elems.each(function(i, elem){
            var obj = $(elem);
            var conTitle = obj.find(".tile-header").attr("data-name").toUpperCase();
            var conDescr = obj.find(".tile-content-abstract-text").text().trim().toUpperCase();

            // check if there is a responsible organization as well
            var respOrg = obj.find(".organization-title");

            if (respOrg !== null){
                respOrg = respOrg.text().trim().toUpperCase()
            }else{
                respOrg = "";
            }

            if(conTitle.includes(val) || conDescr.includes(val) || respOrg.includes(val)){
                obj.show();
            }else{
                obj.hide();
            }
        });
    });

    $(".tile-filter-order").on("change", function(){
        var elem = $(this);
        var val = elem.val();
        var tileWrapper = $(".tile-wrapper");

        tileWrapper.html(orderTiles(val));

    });

    $(".switch input").on("change", function(){
        var elem = $(this);
        toggleListView(elem.is(":checked"));
    });
});