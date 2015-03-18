/**
 * Created by Brian on 1/31/2015.
 */

// tagToShow: CSS tag you want visible
// if you want an html element to display for more than one type of tag, add both
function showTag(tagToShow){
    var tags = [".infoTag",".formTag",".searchTag",".listTag"]; // list of tags the function can handle

    // turn off all flags first
    for (var i = 0; i < tags.length; i++){
        $(tags[i]).fadeOut(0);
        //$(tags[i]).addClass("displayOff");
    }

    // turn back on the one we want.  this allows some to have multiple tags
    for (var i = 0; i < tags.length; i++){
        if (tags[i] == tagToShow){
            $(tags[i]).fadeIn();
            //$(tags[i]).removeClass("displayOff");
        }
    }
}

//event handler to edit button
$(function() {
    $('#editButton').on('click', function(event){
        event.preventDefault();
        showTag(".formTag"); // show the form
    });
});

//event handler to add button
$(function() {
    $('#addButton').on('click', function(event){
        event.preventDefault();
        showTag(".formTag");  // also shows form
    });
});

// event handler for cancel button
// has different desired outcomes based on what it was doing
$(function() {
    $('#cancelButton').on('click', function(event){
        event.preventDefault();
        if ((window.location.search.substring(1)).split("=")[0] == "BusinessID"
          || (window.location.search.substring(1)).split("=")[0] == "EmployeeID") {
            showTag(".infoTag"); // on a specific business/employee, switch back to information
            console.log(window.location.search.substring(1))
        } else if ((window.location.search.substring(1)).split("=")[0] == "Search") {
            showTag(".listTag");  // show search list
            //window.location.href = 'business.php';
        } else {
            showTag(".searchTag"); // default is search box
        }
    });
});

/** ******************* For Dashboard *************************/

$(function(){
    $('.AIClass, .expandRow').click(function() {
        var clickedID=$(this).attr('id');
        var expandID = '#to' + clickedID;
        if ($(expandID).css('display') == 'none') {
            $(expandID).fadeIn();
        } else {
            $(expandID).fadeOut(100);
        }
    });
});

/*
$(function() {
    var expandRowClass = $('.expandRow').attr('id');

    var toExpand1 = $("li ~ .DashNote").attr('id');
});
*/
$(function() {
    $('#allContacts').click(function() {
        $('.allNotes').toggle();
    });
});

$(document).ready(function(){
    for (var i = 1; i <= 5; i++){
        $('#toExpandAI' + i).fadeOut(0);
    }
});