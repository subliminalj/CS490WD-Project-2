$(document).ready(init);

function init() {
    $("#find-car").on("click", function () {
        showcars();
    }
    );
    $('#find-car-input').on('keypress', function (e) {
        if (e.which === 13) {
            //Disable textbox to prevent multiple submit
            $(this).attr("disabled", "disabled");
            showcars();
            $(this).removeAttr("disabled");
        }
    });
    showrented();
    showhistory();
}
function showcars(){
    $.ajax({
        method: "POST",
        url: "server/controller.php",
        dataType: "json",
        data: {type: "search"},//request type: info
        success: function (data) {
            alert("test");
            var search_template=$("#find-car-template").html();//get the info-template
            var html_maker=new htmlMaker(search_template);
            var html=html_maker.getHTML(data);//generate dynamic HTML for student-info
            $("#search_results").html(html);//show the student info in the info div
            $("div[name=rentcar]").on("click",function(){rent_car(this);});
        }
    });
}

function showrented(){
    $.ajax({
        method: "POST",
        url: "server/controller.php",
        dataType: "json",
        data: {type: "rented"},//request type: info
        success: function (data) {
            var rental_template=$("#rented-car-template").html();//get the info-template
            var html_maker=new htmlMaker(rental_template);
            var html=html_maker.getHTML(data);//generate dynamic HTML for student-info
            $("#rented_cars").html(html);//show the student info in the info div
            $("div[name=returncar]").on("click",function(){return_car(this);});
        }
    });
}
function showhistory(){
    $.ajax({
        method: "POST",
        url: "server/controller.php",
        dataType: "json",
        data: {type: "history"},//request type: info
        success: function (data) {
            alert("test");
            var history_template=$("#returned-car-template").html();
            var html_maker=new htmlMaker(history_template);
            var html=html_maker.getHTML(data);
            $("#returned_cars").html(html);       
            
            
        }
    });
}
function return_car(return_button){
    var return_id=$(return_button).attr("data-rental-id");
    alert("test");
     $.ajax({
        method: "POST",
        url: "server/controller.php",
        dataType: "text",
        data: {type: "return",return_id:return_id},
        success: function (data) {
            if ($.trim(data)=="success") {
                alert("Car has been returned");
                show_rented(); //refresh courses
            }
        }
    });   
}
function rent_car(rent_button){
    alert("helloooooooooo");
    var rent_id=$(rent_button).attr("id");
     $.ajax({
        method: "POST",
        url: "server/controller.php",
        dataType: "text",
        data: {type: "rent",id:rent_id},
        success: function (data) {
            if ($.trim(data)=="success") {
                alert("Car has been rented");
                show_rented(); //refresh courses
            }
        }
    });   
}
