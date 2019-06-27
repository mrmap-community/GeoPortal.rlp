
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
            if(conTitle.includes(val) || conDescr.includes(val)){
                obj.show();
            }else{
                obj.hide();
            }
        });
    });
});