/**
 * Created by Brian on 1/31/2015.
 */

function showInfo(){
    $(".infoTag").addClass("displayOn");
    $(".formTag").addClass("displayOff");
}

function showForm(){
    $(".infoTag").addClass("displayOff");
    $(".formTag").addClass("displayOn");
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