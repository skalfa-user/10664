(function() { // Parallax effect
    
    var header = $(".ow_header_wrap");
    
    function parallax() {
        var top = $(document).scrollTop() / 1.5;
        
        header.css("backgroundPosition", "0px " + top + "px");
    }
    
    $(document).scroll(parallax);
    parallax();
})();

$(".ow_qs select").simpleselect(); // Custom select boxes
