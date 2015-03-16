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
$(document).ready(function() {
    $("div[class^='DashNote']").hide();
    $('a#expandRow1').toggle(function(){
        $('.DashNote1').show();
    }, function(){
        $('.DashNote1').hide();
    });
});

/*
$(function() {


    $("div[class^='DashNote']").css("display", "none");

    $('.expandButton1').on('click', function(event){
            event.preventDefault();
            $(".DashNote1").css("display",  "");
            $(".DashNote2").css("display",  "none");
            $(".DashNote3").css("display",  "none");
            $(".DashNote4").css("display",  "none");
            $(".DashNote5").css("display",  "none");
        }
    )
});

$(function() {
    $('.expandButton2').on('click', function(event){
            //event.off();
            //$("table[id=notesTable]").css("border",  "10px solid blue");
            event.preventDefault();
            $(".DashNote1").css("display",  "none");
            $(".DashNote2").css("display",  "");
            $(".DashNote3").css("display",  "none");
            $(".DashNote4").css("display",  "none");
            $(".DashNote5").css("display",  "none");
        }
    )
});
*/