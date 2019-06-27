
$(document).ready(function(){
    /*
    * Filter tiles
    */
    $(document).on("input", ".tile-filter-input", function(){
        var elem = $(this);
        var val = elem.val();
        var elems = $(".tile");
        elems.each(function(i, elem){
            var obj = $(elem);
            var conTitle = obj.find(".tile-header").attr("data-name");
            var conDescr = obj.find(".tile-content-abstract-text").text().trim();
            if(conTitle.includes(val) || conDescr.includes(val)){
                obj.show();
            }else{
                obj.hide();
            }
        });
    });
});