/**
 * Created by Brian on 1/31/2015.
 */

function showInfo(){
    $(".infoTag").addClass("displayOn");
    $(".infoTag").removeClass("displayOff");
    $(".formTag").addClass("displayOff");
    $(".formTag").removeClass("displayOn");
}

function showForm(){
    $(".infoTag").addClass("displayOff");
    $(".infoTag").removeClass("displayOn");
    $(".formTag").addClass("displayOn");
    $(".formTag").removeClass("displayOff");
}

function showList(){

}

//event handler to edit button
$(function() {
    $('#editButton').on('click', function(event){
        event.preventDefault();
        showForm();
    });
});

$(function() {
    $('#cancelButton').on('click', function(event){
        event.preventDefault();
        if (window.location.search.substring(1) != "") {
            showInfo();
            console.log(window.location.search.substring(1))
        } else {
            window.location.href = 'index.php';
        }
    });
});