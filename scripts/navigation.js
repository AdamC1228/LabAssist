$(document).ready(function ($) {
    var href = document.location.href;
    var lastPathSegment = href.substr(href.lastIndexOf('/') + 1);
    $('.navigation a').each(function () {
        var linkPage = this.getAttribute("href");

        var n = lastPathSegment.indexOf('?');
        var cleanURL = lastPathSegment.substring(0, n != -1 ? n : lastPathSegment.length);
/*        
        console.log("Next");
        console.log("LinkPage: " + linkPage);
        console.log("LastPath: " + lastPathSegment);
        console.log("CleanURL: " + cleanURL);
        console.log(" ");
        */

        if (cleanURL == linkPage) {
            $(this).addClass("activePage");
            
            //Go back up the navigation and re-expand untill the root.
            var current = $(this);
            while(current.attr('class') !="navigation")
            {
                if(current.attr('class')=="wrap-collapsible")
                {
                    current.children('input').attr('checked',true);
                }
                current=$(current.parent());   
            }
        }
    });
});



// $(document).ready(function ($) {
//     var href = document.location.href;
//     var lastPathSegment = href.substr(href.lastIndexOf('/') + 1);
//     $('.navigation a').each(function () {
//         var linkPage = this.getAttribute("href");
// 
//         var n = lastPathSegment.indexOf('?');
//         var cleanURL = lastPathSegment.substring(0, n != -1 ? n : lastPathSegment.length);
// /*        
//         console.log("Next");
//         console.log("LinkPage: " + linkPage);
//         console.log("LastPath: " + lastPathSegment);
//         console.log("CleanURL: " + cleanURL);
//         console.log(" ");
//         */
// 
//         if (cleanURL == linkPage) {
//             $(this).addClass("activePage");
//             $(this).parent().addClass("showBlock");
//             $(this).parent().parent().addClass("showBlock");
//         }
//     });
// });
