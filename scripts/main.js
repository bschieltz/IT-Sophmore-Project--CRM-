/**
 * Created by Brian on 1/31/2015.
 */

function swapDisplay(){
    $(".infoTag").toggleClass("displayOn displayOff");
    $(".formTag").toggleClass("displayOn displayOff");
}

//event handler to edit button
$(function() {
    $('#editButton').on('click', function(event){
        event.preventDefault();
        swapDisplay();
    });
});

$(function() {
    $('#cancelButton').on('click', function(event){
        event.preventDefault();
        if (window.location.search.substring(1) != "") {
            swapDisplay();
            console.log(window.location.search.substring(1))
        } else {
            window.location.href = 'index.php';
        }
    });
});