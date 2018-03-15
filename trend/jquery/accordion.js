/* 
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */
$(document).ready(function() {
     /*  ACCORDION
     *  This section of code controls the opening and closing of the clicked accordion
     */
    var clickedTab;
    var sameTab = false;
    $(".title").click(function(e) {
        //Close all open tabs
        $(".topics .title").removeClass("active");
        $(".topics .content").slideUp(300);
        $("#section-0 .content").stop();
        
        //Set clicked variable
        var currentAttrValue = $(this).attr("href");
        
        //Check if current clicked tab is the same as the previously clicked tab
        if(clickedTab === currentAttrValue){
            $(".topics .title").removeClass("active");
            $(".topics " + currentAttrValue + " .content").slideUp(300);
            
            //Revert clickedTab to not be equal to current tab
            clickedTab = null;
        }else{
            $(".topics " + currentAttrValue + " .title").addClass("active");
            $(".topics " + currentAttrValue + " .content").slideDown(300).addClass("active");
            
            //Set clicked tab variable to be equal to current tab.
            clickedTab = currentAttrValue;
        }

        //Ensure the page does not jump to the clicked tab.
        $("#section-0 .content").stop();
        e.preventDefault();
    });
});

