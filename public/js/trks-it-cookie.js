/**
Kindred - trks.it tracking functions
*/
/*\
|*|  A complete cookies reader/writer framework with full unicode support.
|*|
|*|  https://developer.mozilla.org/en-US/docs/DOM/document.cookie
|*|
|*|  This framework is released under the GNU Public License, version 3 or later.
|*|  http://www.gnu.org/licenses/gpl-3.0-standalone.html
|*|
|*|  Syntaxes:
|*|
|*|  * docCookies.setItem(name, value[, end[, path[, domain[, secure]]]])
|*|  * docCookies.getItem(name)
|*|  * docCookies.removeItem(name[, path], domain)
|*|  * docCookies.hasItem(name)
|*|  * docCookies.keys()
|*|
\*/

var docCookies = {
  getItem: function (sKey) {
    return decodeURIComponent(document.cookie.replace(new RegExp("(?:(?:^|.*;)\\s*" + encodeURIComponent(sKey).replace(/[\-\.\+\*]/g, "\\$&") + "\\s*\\=\\s*([^;]*).*$)|^.*$"), "$1")) || null;
  },
  setItem: function (sKey, sValue, vEnd, sPath, sDomain, bSecure) {
    if (!sKey || /^(?:expires|max\-age|path|domain|secure)$/i.test(sKey)) { return false; }
    var sExpires = "";
    if (vEnd) {
      switch (vEnd.constructor) {
        case Number:
          sExpires = vEnd === Infinity ? "; expires=Fri, 31 Dec 9999 23:59:59 GMT" : "; max-age=" + vEnd;
          break;
        case String:
          sExpires = "; expires=" + vEnd;
          break;
        case Date:
          sExpires = "; expires=" + vEnd.toUTCString();
          break;
      }
    }
    document.cookie = encodeURIComponent(sKey) + "=" + encodeURIComponent(sValue) + sExpires + (sDomain ? "; domain=" + sDomain : "") + (sPath ? "; path=" + sPath : "") + (bSecure ? "; secure" : "");
    return true;
  },
  removeItem: function (sKey, sPath, sDomain) {
    if (!sKey || !this.hasItem(sKey)) { return false; }
    document.cookie = encodeURIComponent(sKey) + "=; expires=Thu, 01 Jan 1970 00:00:00 GMT" + ( sDomain ? "; domain=" + sDomain : "") + ( sPath ? "; path=" + sPath : "");
    return true;
  },
  hasItem: function (sKey) {
    return (new RegExp("(?:^|;\\s*)" + encodeURIComponent(sKey).replace(/[\-\.\+\*]/g, "\\$&") + "\\s*\\=")).test(document.cookie);
  },
  keys: /* optional method: you can safely remove it! */ function () {
    var aKeys = document.cookie.replace(/((?:^|\s*;)[^\=]+)(?=;|$)|^\s*|\s*(?:\=[^;]*)?(?:\1|$)/g, "").split(/\s*(?:\=[^;]*)?;\s*/);
    for (var nIdx = 0; nIdx < aKeys.length; nIdx++) { aKeys[nIdx] = decodeURIComponent(aKeys[nIdx]); }
    return aKeys;
  }
};

jQuery(document).ready(function(){

setTimeout(function(){

	//grab the utmz cookie
	if( docCookies.hasItem('__utmz') ){
	    var utmz_cookie = {'utmz':docCookies.getItem('__utmz')};
	    //break apart the utmz cookie to track the source, medium and campaign
	    var original = docCookies.getItem('__utmz').split('|');
	
	    var original_source = original[0].split('=');
	    original_source = original_source[1];
	
	    var original_campaign = original[1].split('=');
	    original_campaign = original_campaign[1];
	
	    var original_medium = original[2].split('=');
	    original_medium = original_medium[1];
	    
	    //test to see if user already set cookie for domain
	    if( !docCookies.hasItem('trks_cookie') ){       
	   
	      var original_data = 'original_source='+original_source+"&original_campaign="+original_campaign+"&original_medium="+original_medium;
	      
	      create_trks_cookie(original_data,utmz_cookie);
	    }
		
		//test to see if the cookie source, medium or campaign have changed
		else if( docCookies.hasItem('trks_cookie') ){
			//create boolean as flag to determine if converting cookie needs to be updated
			var update = false;
			//grab the cookie
			var trks_cookie = docCookies.getItem('trks_cookie').split('&');
			
			var trks_source =  trks_cookie[0].split('=');
			trks_source =  trks_source[1];
			
			var trks_campaign =  trks_cookie[1].split('=');
			trks_campaign =  trks_campaign[1]; 
			
			var trks_medium =  trks_cookie[2].split('=');
			trks_medium =  trks_medium[1];
			
			//if the source, medium or campaign have changed, update the trks cookie and receive a new converting cookie from trksit.kindred.com
			if( trks_source != original_source ){
				update = true;
			}else if ( trks_medium != original_medium ){
				update = true;
			}else if ( trks_campaign != original_campaign ){
				update = true;
			}
			
			//if we don't have a trksit party cookies, we should update
			if(!docCookies.getItem('trks_party')){
				update = true;
			}
      
			if( update ){
      
				var original_data = 'original_source='+original_source+"&original_campaign="+original_campaign+"&original_medium="+original_medium;
        
				create_trks_cookie(original_data,utmz_cookie);
      }
        
    }
  }

  }, 400)

});

function create_trks_cookie(cookie_data,utmz){
  var trks_it_url = 'https://trksit.kindred.com/cookies';
  //send an ajax request to kindred trks.it domain to set the initial and the conversion UTMZ cookie
  jQuery.ajax({
    cache: false,
    type: 'POST',
    url: trks_it_url,
    data: jQuery.param(utmz),
    async: true,
    crossDomain: true,
    xhrFields: {withCredentials: true},
    success:function(response,status,xhr){
      //set the trks.it cookie with the utmz values. 
      //Expire this information after 30 days. 
      //Set it for the top-level domain so it works for subdomains
      var parts = location.hostname.split('.');
      var subdomain = parts.shift();
      var upperleveldomain = parts.join('.');
      
      docCookies.setItem('trks_cookie', cookie_data,2.592e6, "/", "."+upperleveldomain);
      
      var trksit_party = docCookies.getItem('trks_party');
      
      if(trksit_party){
		  if(trksit_party != response){
			  docCookies.setItem('trks_party', response,2.592e6, "/", "."+upperleveldomain);
			  _gaq.push(['_setCustomVar',1,'trks.it Party',response,1]);
			  _gaq.push(['_trackEvent', 'trks.it', 'Setting Party', response]);
		  }
      } else if(response != "") {
	      docCookies.setItem('trks_party', response,2.592e6, "/", "."+upperleveldomain);
	      _gaq.push(['_setCustomVar',1,'trks.it party',response,1]);
		  _gaq.push(['_trackEvent', 'trks.it', 'Setting Party', response]);      
	  }
      
       
	  

    },
    error:function(xhr,status,error){
    
    }
  });
}