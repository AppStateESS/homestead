(function( $ ){

  var methods = {
     init : function( options ) {

       return this.each(function(){
         
         var $this = $(this),
             data = $this.data('tooltip'),
             tooltip = $('<div />', {
               text : $this.attr('title')
             });
         
         // If the plugin hasn't been initialized yet
         if ( ! data ) {
         
           /*
             Do more setup stuff here
           */

           $(this).data('tooltip', {
               target : $this,
               tooltip : tooltip
           });

         }
       });
     },
     destroy : function( ) {

       return this.each(function(){

         var $this = $(this),
             data = $this.data('tooltip');

         // Namespacing FTW
         $(window).unbind('.tooltip');
         data.tooltip.remove();
         $this.removeData('tooltip');

       })

     },
     reposition : function( ) { // ... },
     show : function( ) { // ... },
     hide : function( ) { // ... },
     update : function( content ) { // ...}
  };

  $.fn.appCardReader = function( method ) {
    
    if ( methods[method] ) {
      return methods[method].apply( this, Array.prototype.slice.call( arguments, 1 ));
    } else if ( typeof method === 'object' || ! method ) {
      return methods.init.apply( this, arguments );
    } else {
      $.error( 'Method ' +  method + ' does not exist on jQuery.appCardReader' );
    }    
  
  };

})( jQuery );