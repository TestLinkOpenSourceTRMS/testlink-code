var FCKImagePreloader = function()
{
	this._Images = new Array() ;
}

FCKImagePreloader.prototype = 
{
	AddImages : function( images )
	{
		if ( typeof( images ) == 'string' )
			images = images.split( ';' ) ;
		
		this._Images = this._Images.concat( images ) ;
	},
	
	Start : function()
	{
		var aImages = this._Images ;
		this._PreloadCount = aImages.length ;
	
		for ( var i = 0 ; i < aImages.length ; i++ )
		{
			var eImg = document.createElement( 'img' ) ;
			eImg.onload = eImg.onerror = _FCKImagePreloader_OnImage ;
			eImg._FCKImagePreloader = this ;
			eImg.src = aImages[i] ;
			
			_FCKImagePreloader_ImageCache.push( eImg ) ;
		}
	}
};

// All preloaded images must be placed in a global array, otherwise the preload
// magic will not happen.
var _FCKImagePreloader_ImageCache = new Array() ;

function _FCKImagePreloader_OnImage()
{
	var oImagePreloader = this._FCKImagePreloader ;
	
	if ( (--oImagePreloader._PreloadCount) == 0 && oImagePreloader.OnComplete )
		oImagePreloader.OnComplete() ;

	this._FCKImagePreloader = null ;
}