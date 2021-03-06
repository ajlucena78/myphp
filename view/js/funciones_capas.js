//funciones de capas

var isNS4=(document.layers) ? 1 : 0;
var isIE4=((document.all) && !isNS4)? 1 : 0;
var isMoz=((document.getElementById) &&!(isNS4) && !(isIE4)) ? 1 :0;

function get_layer (name)
{
	if(isNS4)
	{
		layer=findLayer(name, document);
		return layer;
	}
	else 
	if (isIE4)
	{
		return document.all[name];
	}
	else
	if (isMoz)
	{
		return document.getElementById(name);
	}
	return null;	
}
function contenido_layer (layer,content)
{
	if(isNS4)
	{
		layer.document.write(content);
		layer.document.close();	
	}
	else if(isIE4||isMoz)
		{
			layer.innerHTML=content;
		}
}
function moveLayerTo(layer, x, y)
{
  if (isNS4)
    layer.moveTo(x, y);
  if (isIE4 || isMoz)
  {
    layer.style.left = x;
    layer.style.top  = y;
  }
}
function resize_layer (layer, w, h)
{
	layer.style.width = w;
	layer.style.height = h;
}
function getLeft (layer)
{
  if (isNS4)
    return(layer.left);
  if (isIE4)
    return(layer.style.pixelLeft);
  return(-1);
}
function getTop (layer)
{
  if (isNS4)
    return(layer.top);
  if (isIE4)
    return(layer.style.pixelTop);
  return(-1);
}
function getWidth(layer) {

  if (isNS4) {
    if (layer.document.width)
      return(layer.document.width);
    else
      return(layer.clip.right - layer.clip.left);
  }
  if (isIE4 || isMoz) {
    if (layer.style.pixelWidth)
      return(layer.style.pixelWidth);
    else
      return(layer.clientWidth);
  }
  return(-1);
}
function getHeight(layer) {

  if (isNS4) {
    if (layer.document.height)
      return(layer.document.height);
    else
      return(layer.clip.bottom - layer.clip.top);
  }
  if (isIE4 || isMoz) {
    if (false && layer.style.pixelHeight)
      return(layer.style.pixelHeight);
    else
      return(layer.clientHeight);
  }
  return(-1);
}
function clipLayer (layer, clipleft, cliptop, clipright, clipbottom)
{
  if (isNS4) {
    layer.clip.left   = clipleft;
    layer.clip.top    = cliptop;
    layer.clip.right  = clipright;
    layer.clip.bottom = clipbottom;
  }
  if (isIE4)
    layer.style.clip = 'rect(' + cliptop + ' ' +  clipright + ' ' + clipbottom + ' ' + clipleft +')';
}
function getzIndex(layer) {

  if (isNS4)
    return(layer.zIndex);
  if (isIE4)
    return(layer.style.zIndex);

  return(-1);
}

function setzIndex(layer, z) {

  if (isNS4)
    layer.zIndex = z;
  if (isIE4)
    layer.style.zIndex = z;
}

function getPageWidth() {

  if (isNS4)
    return(document.width);
  if (isIE4 || isMoz)
    return(document.body.scrollWidth);
  return(-1);
}

function getPageHeight()
{
  if (isNS4 || isMoz)
    return(document.height);
  if (isIE4)
    return(document.body.scrollHeight);
  return(-1);
}

function getPageScrollX() {

  if (isNS4 || isMoz)
    	return(window.pageXOffset);
  if (isIE4)
    	return(document.body.scrollLeft);
  return(-1);
}

function getPageScrollY()
{
  if (isNS4 || isMoz)
 	return(window.pageYOffset);
  if (isIE4)
   	return(document.body.scrollTop);
  return(-1);
}

function getWindowWidth() {
	
  if (isNS4)
    return(window.innerWidth);
  if (isIE4 || isMoz)
    return(document.body.clientWidth);
  return(-1);
}

function getWindowHeight()
{
  if (isNS4)
    return(window.innerHeight);
  if (isIE4 || isMoz)
    return(document.body.clientHeight);
  return(-1);
}