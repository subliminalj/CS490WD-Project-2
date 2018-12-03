$(document).ready(init);

function init() {
    $("#find-car").on("click",function(){
        showcars();
    }           
    );
    showcrentals()
}
function showcars(){
    $.ajax({
        method: "POST",
        url: "server/controller.php",
        dataType: "json",
        data: {type: "info"},//request type: info
        success: function (data) {
            var search_template=$("#find-car").html();//get the info-template
            var html_maker=new htmlMaker(search_template);
            var html=html_maker.getHTML(data);//generate dynamic HTML for student-info
            $("#search_results").html(html);//show the student info in the info div
        }
    });
}

function showcrentals(){
    $.ajax({
        method: "POST",
        url: "server/controller.php",
        dataType: "json",
        data: {type: "rented"},//request type: info
        success: function (data) {
            var search_template=$("#find-car-template").html();//get the info-template
            var html_maker=new htmlMaker(search_template);
            var html=html_maker.getHTML(data);//generate dynamic HTML for student-info
            $("#returned_cars").html(html);//show the student info in the info div
        }
    });
}
