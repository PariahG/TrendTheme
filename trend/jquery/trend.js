/* 
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */
$(document).ready(function() {
     /*  just in case this is needed
     */    
});

/* Scroll detector for top tag logo */
$(window).scroll(function(){
   var scrollpoint = $(this).scrollTop();
   var logoheight = 80;
   
   if (scrollpoint >= logoheight) {
       $('#top-logo').addClass('logo-scrolled');
       $('#top-logo').removeClass('logo-before');
   }
   
   if (scrollpoint < logoheight) {
       $('#top-logo').removeClass('logo-scrolled');
       $('#top-logo').addClass('logo-before');
   }
    
});

