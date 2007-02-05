// Contributors 
// Ilkka Huotari at http://www.editsite.net
// Mathieu 'p01' HENRI at http://www.p01.org/
// http://seky.nahory.net/2005/04/rounded-corners/
// Steven Wittens at http://www.acko.net/anti-aliased-nifty-corners
// Original Nifty Corners by Alessandro Fulciniti at http://pro.html.it/esempio/nifty/
function NiftyCheck() {
  if(!document.getElementById || !document.createElement) {
    return false;
  }
  var b = navigator.userAgent.toLowerCase();
  if (b.indexOf("msie 5") > 0 && b.indexOf("opera") == -1) {
    return false;
  }
  return true;
}

function Rounded(className, sizex, sizey, sizex_b, sizey_b) {
	var bk;
	if (!NiftyCheck()) return;
	if (typeof(sizex_b) == 'undefined')
		sizex_b = sizex;
	if (typeof(sizey_b) == 'undefined')
		sizey_b = sizey;
	var v = getElements(className);
	var l = v.length;
	for (var i = 0; i < l; i++) {
		color = get_current_style(v[i],"background-color","transparent");
		bk = get_current_style(v[i].parentNode,"background-color","transparent");
		AddRounded(v[i], bk, color, sizex, sizey, true);
		AddRounded(v[i], bk, color, sizex_b, sizey_b, false);
	}
}

Math.sqr = function (x) {
  return x*x;
};

function Blend(a, b, alpha) {

  var ca = Array(
    parseInt('0x' + a.substring(1, 3)), 
    parseInt('0x' + a.substring(3, 5)), 
    parseInt('0x' + a.substring(5, 7))
  );
  var cb = Array(
    parseInt('0x' + b.substring(1, 3)), 
    parseInt('0x' + b.substring(3, 5)), 
    parseInt('0x' + b.substring(5, 7))
  );
  return '#' + ('0'+Math.round(ca[0] + (cb[0] - ca[0])*alpha).toString(16)).slice(-2).toString(16)
             + ('0'+Math.round(ca[1] + (cb[1] - ca[1])*alpha).toString(16)).slice(-2).toString(16)
             + ('0'+Math.round(ca[2] + (cb[2] - ca[2])*alpha).toString(16)).slice(-2).toString(16);

  return '#' + ('0'+Math.round(ca[0] + (cb[0] - ca[0])*alpha).toString(16)).slice(-2).toString(16)
             + ('0'+Math.round(ca[1] + (cb[1] - ca[1])*alpha).toString(16)).slice(-2).toString(16)
             + ('0'+Math.round(ca[2] + (cb[2] - ca[2])*alpha).toString(16)).slice(-2).toString(16);
}

function AddRounded(el, bk, color, sizex, sizey, top) {
  if (!sizex && !sizey)
	return;
  var i, j;
  var d = document.createElement("div");
  d.style.backgroundColor = bk;
  var lastarc = 0;
  for (i = 1; i <= sizey; i++) {
    var coverage, arc2, arc3;
    // Find intersection of arc with bottom of pixel row
    arc = Math.sqrt(1.0 - Math.sqr(1.0 - i / sizey)) * sizex;
    // Calculate how many pixels are bg, fg and blended.
    var n_bg = sizex - Math.ceil(arc);
    var n_fg = Math.floor(lastarc);
    var n_aa = sizex - n_bg - n_fg;
    // Create pixel row wrapper
    var x = document.createElement("div");
    var y = d;
    x.style.margin = "0px " + n_bg + "px";
	x.style.height='1px';
	x.style.overflow='hidden';
    // Make a wrapper per anti-aliased pixel (at least one)
    for (j = 1; j <= n_aa; j++) {
      // Calculate coverage per pixel
      // (approximates circle by a line within the pixel)
      if (j == 1) {
        if (j == n_aa) {
          // Single pixel
          coverage = ((arc + lastarc) * .5) - n_fg;
        }
        else {
          // First in a run
          arc2 = Math.sqrt(1.0 - Math.sqr((sizex - n_bg - j + 1) / sizex)) * sizey;
          coverage = (arc2 - (sizey - i)) * (arc - n_fg - n_aa + 1) * .5;
          // Coverage is incorrect. Why?
          coverage = 0;
        }
      }
      else if (j == n_aa) {
        // Last in a run
        arc2 = Math.sqrt(1.0 - Math.sqr((sizex - n_bg - j + 1) / sizex)) * sizey;
        coverage = 1.0 - (1.0 - (arc2 - (sizey - i))) * (1.0 - (lastarc - n_fg)) * .5;
      }
      else {
        // Middle of a run
        arc3 = Math.sqrt(1.0 - Math.sqr((sizex - n_bg - j) / sizex)) * sizey;
        arc2 = Math.sqrt(1.0 - Math.sqr((sizex - n_bg - j + 1) / sizex)) * sizey;
        coverage = ((arc2 + arc3) * .5) - (sizey - i);
      }
      
      x.style.backgroundColor = Blend(bk, color, coverage);
	  if (top)
	      y.appendChild(x);
      else
	      y.insertBefore(x, y.firstChild);
      y = x;
      var x = document.createElement("div");
		x.style.height='1px';
		x.style.overflow='hidden';
      x.style.margin = "0px 1px";
    }
    x.style.backgroundColor = color;
    if (top)
	    y.appendChild(x);
    else
		y.insertBefore(x, y.firstChild);
    lastarc = arc;
  }
  if (top)
	  el.insertBefore(d, el.firstChild);
  else
	  el.appendChild(d);
}

function getElements(className) {
	var elements = [];
	var el = document.getElementsByTagName('DIV');  
	var regexp=new RegExp("\\b"+className+"\\b");
	for (var i = 0; i < el.length; i++) 
	{
		if (regexp.test(el[i].className)) 
			elements.push(el[i]);
	}
	return elements;
}

function get_current_style(element,property,not_accepted)
{
  var ee,i,val,apr;
  try
  {
    var cs=document.defaultView.getComputedStyle(element,'');
    val=cs.getPropertyValue(property);
  }
  catch(ee)
  {
    if(element.currentStyle)
  	{
	    apr=property.split("-");
	    for(i=1;i<apr.length;i++) apr[i]=apr[i].toUpperCase();
	    apr=apr.join("");
	    val=element.currentStyle.getAttribute(apr);
   }
  }
  if((val.indexOf("rgba") > -1 || val==not_accepted) && element.parentNode)
  {
	 if(element.parentNode != document) 
		 val=get_current_style(element.parentNode,property,not_accepted);
	 else
		 val = '#FFFFFF';
  }
  if (val.indexOf("rgb") > -1 && val.indexOf("rgba") == -1)
	  val = rgb2hex(val);
  if (val.length == 4)
	  val = '#'+val.substring(1,1)+val.substring(1,1)+val.substring(2,1)+val.substring(2,1)+val.substring(3,1)+val.substring(3,1);
  return val;
}

function rgb2hex(value)
{
	var x = 255;
	var hex = '';
	var i;
	var regexp=/([0-9]+)[, ]+([0-9]+)[, ]+([0-9]+)/;
	var array=regexp.exec(value);
	for(i=1;i<4;i++) hex += ('0'+parseInt(array[i]).toString(16)).slice(-2);
	return '#'+hex;
}
