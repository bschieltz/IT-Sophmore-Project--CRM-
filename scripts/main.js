/**
 * Created by Brian on 1/31/2015.
 */

function swapDisplay(){
    //if (displayForm) {
        $(".infoTag").toggleClass("displayOn displayOff");
    //} else {

    //}
    //$(this).parent().css({"display":"none"});
}

$(function(){
    $("#reorder").click(function(event) {
        event.preventDefault();
        $(this).text("Done reordering");
        $(".infoTag").toggleClass("displayOn displayOff");
    });
});
