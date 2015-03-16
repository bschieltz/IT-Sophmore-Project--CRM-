/**
 * Created by Brian on 1/31/2015.
 */

function showTag(tagToShow){
    var tags = [".infoTag",".formTag",".searchTag",".listTag"];
    for (var i = 0; i < tags.length; i++){
        $(tags[i]).addClass("displayOff");
    }

    for (var i = 0; i < tags.length; i++){
        if (tags[i] == tagToShow){
            $(tags[i]).removeClass("displayOff");
        }
    }
}

//event handler to edit button
$(function() {
    $('#editButton').on('click', function(event){
        event.preventDefault();
        showTag(".formTag");
    });
});

//event handler to add button
$(function() {
    $('#addButton').on('click', function(event){
        event.preventDefault();
        showTag(".formTag");
    });
});

$(function() {
    $('#cancelButton').on('click', function(event){
        event.preventDefault();
        if ((window.location.search.substring(1)).split("=")[0] == "BusinessID"
          || (window.location.search.substring(1)).split("=")[0] == "EmployeeID") {
            showTag(".infoTag");
            console.log(window.location.search.substring(1))
        } else if ((window.location.search.substring(1)).split("=")[0] == "Search") {
            showTag(".listTag");
            //window.location.href = 'business.php';
        } else {
            showTag(".searchTag");
        }
    });
});

/** ******************* For Dashboard *************************/
$(function() {
    $('#expandRow1').click(function() {
        $('.DashNote1').toggle();
    });
    $('#expandRow2').click(function() {
        $('.DashNote2').toggle();
    });
    $('#expandRow3').click(function() {
        $('.DashNote3').toggle();
    });
    $('#expandRow4').click(function() {
        $('.DashNote4').toggle();
    });
    $('#expandRow5').click(function() {
        $('.DashNote5').toggle();
    });
});

$(function() {
    $('#allContacts').click(function() {
        $('#allContacts').text('Hide Contacts');
        $('.allNotes').toggle();
    });
});