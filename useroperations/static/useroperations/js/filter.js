
$(document).ready(function(){
    /*
    * Filter tiles
    */
    $(document).on("input", ".tile-filter-input", function(){
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
});