

function orderOrganizationTiles(orderBy){
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


$(document).ready(function(){
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

        if(tileWrapper.hasClass("organizations")){
            tileWrapper.html(orderOrganizationTiles(val));
        }

    });
});