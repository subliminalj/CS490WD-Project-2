$(document).ready(init);

function init() {
    //logout event listener
    $("#logout-link").on("click", function(){
        logout();
    });
    //user clicks search button
    $("#find-car").on("click", function () {
        showcars();
    }
    );
    //user hits enter event listener
    $('#find-car-input').on('keypress', function (e) {
        if (e.which === 13) {
            //Disable textbox to prevent multiple submit
            $(this).attr("disabled", "disabled");
            showcars();
            $(this).removeAttr("disabled");
        }
    });
    //loads rented cars
    showrented();
    //loads car history
    showhistory();
}
//show car search results
function showcars(){
    //gets input value
    var search = $("#find-car-input").val();
    $.ajax({
        method: "POST",
        url: "server/controller.php",
        dataType: "json",
        data: {type: "search",search:search},//request type: search
        success: function (data) {
            var search_template=$("#find-car-template").html();//get the info-template
            var html_maker=new htmlMaker(search_template);
            var html=html_maker.getHTML(data);//generate dynamic HTML for searched cars
            $("#search_results").html(html);//show the car info div
            $(".car_rent").on("click",function(){rent_car(this);});
        }
    });
}
//shows rented cars
function showrented(){
    $.ajax({
        method: "POST",
        url: "server/controller.php",
        dataType: "json",
        data: {type: "rented"},//request type: rented
        success: function (data) {
            var rental_template=$("#rented-car-template").html();//get the info-template
            var html_maker=new htmlMaker(rental_template);
            var html=html_maker.getHTML(data);//generate dynamic HTML for rented-info
            $("#rented_cars").html(html);//show the rented cars
            $(".return_car").on("click",function(){return_car(this);});
        }
    });
}
//gets rental history 
function showhistory(){
    $.ajax({
        method: "POST",
        url: "server/controller.php",
        dataType: "json",
        data: {type: "history"},//request type: history
        success: function (data) {
            //template is filled with data
            var history_template=$("#returned-car-template").html();
            var html_maker=new htmlMaker(history_template);
            var html=html_maker.getHTML(data);
            $("#returned_cars").html(html);       
            
            
        }
    });
}
//returns car
function return_car(return_button){
    //id is stored and will be sent in the post
    var return_id=$(return_button).attr("data-rental-id");
     $.ajax({
        method: "POST",
        url: "server/controller.php",
        dataType: "text",
        data: {type: "return",return_id:return_id},
        success: function (data) {
            if ($.trim(data)=="success") {
                alert("Car has been returned");
                showrented(); //refresh rented
            }
            showhistory(); //refresh history
            showcars(); //refresh search results
        }
    });   
}
//rents car 
function rent_car(rent_button){
    //rental id is stored and posted
    var rent_id=$(rent_button).attr("id");
    $("#find-car-input").val();
     $.ajax({
        method: "POST",
        url: "server/controller.php",
        dataType: "text",
        data: {type: "rent",rental_id:rent_id},
        success: function (data) {
            if ($.trim(data)=="success") {
                alert("Car has been rented");
                showrented(); //refresh rented
            }
            showcars();  //refresh cars
        }
    });   
}
//logout function
function logout() {
    $.ajax({
        method: "POST",
        url: "server/controller.php",
        dataType: "text",
        data: {type: "logout"},
        success: function (data) {
            if ($.trim(data)=="success") {
                window.location.assign("index.html");
            }
        }
    });
}
