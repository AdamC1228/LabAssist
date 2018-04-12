$('.message a').click(function(){
    
    if(localStorage.getItem('form') == 1)
    {
        localStorage.setItem('form',0);
    }
    else
    {
        localStorage.setItem('form',1);
    }
        
   $('form').animate({height: "toggle", opacity: "toggle"}, "slow");
   $("#sidno").focus();
   $('.error').text('');
});

$(document).ready(function() {
    var form = localStorage.getItem('form');
    if(form==null) 
    {
         localStorage.setItem('form',0);
    }
    else if (localStorage.getItem('form') ==1)
    {
           $('form').toggle();
    }

    $("#sidno").focus();
});
