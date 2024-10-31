  jQuery( function( $ ) {
    'use strick';
    
    $( "#credits-tabs, #iframes-tabs" ).tabs();
    
    $('.scroller').mousewheel(function(e, delta) {
        this.scrollLeft -= (delta * 40);
        e.preventDefault();
    });
    
    $( '#player .season' ).change( function() {
        season_select();
        episode_select();
        studio_select();
    });
    
    $( '#player .episode' ).change( function() {
        episode_select();
        studio_select();
    });
    
    $( '#player .studio' ).change( function() {
        studio_select();
    });
    
    function season_select() {
        var senum = $( '#player .season' ).val();
            i = 0;
        
        $( '#player .episode' ).html( '' );
        data[ senum ]['episodes'].forEach( function ( episode ) {
            $( '#player .episode' ).append( '<option value="' + i + '">' + episode['name'] + '</option>' );
            i++;
        });
    }
    
    function episode_select() {
        var senum = $( '#player .season' ).val(),
            epnum = $( '#player .episode' ).val();
            i = 0;
        
        $( '#player .studio' ).html( '' );
        studios.forEach( function ( studio ) {
            if ( 0 !== data[ senum ]['episodes'][ epnum ]['url'][ i ].length ) {
                $( '#player .studio' ).append( '<option value="' + i + '">' + studio + '</option>' );
            }
            i++;
        });
    }
    
    function studio_select() {
        var senum = $( '#player .season' ).val(),
            epnum = $( '#player .episode' ).val(),
            studio = $( '#player .studio' ).val();
            
        if ( undefined === data[0]['episodes'] ) {
            $( '#player iframe' ).attr( 'src', data[ studio ] );
        } else {
            $( '#player iframe' ).attr( 'src', data[ senum ]['episodes'][ epnum ]['url'][ studio ] );
        }
    }
  } );