<!-- beginning of menu header - {packageName} {version} {copyright} {author} -->

<script language="JavaScript" type="text/javascript">
<!--

menuTopShift = {menuTopShift};
menuRightShift = {menuRightShift};
menuLeftShift = {menuLeftShift};

var thresholdY = {thresholdY};
var abscissaStep = {abscissaStep};

toBeHidden = new Array();
toBeHiddenLeft = new Array();
toBeHiddenTop = new Array();

{listl}
var numl = listl.length;

father = new Array();
for (i=1; i<={nodesCount}; i++) {
	father['L' + i] = '';
}
{father_keys}
{father_vals}
for (i=0; i<father_keys.length; i++) {
	father[father_keys[i]] = father_vals[i];
}

lwidth = new Array();
var lwidthDetected = 0;

function moveLayers()
{
	if (!lwidthDetected) {
		for (i=0; i<numl; i++) {
			lwidth[listl[i]] = getOffsetWidth(listl[i]);
		}
		lwidthDetected = 1;
	}
	if (IE4) {
		for (i=0; i<numl; i++) {
			setWidth(listl[i], abscissaStep);
		}
	}
{moveLayers}
}

back = new Array();
for (i=1; i<={nodesCount}; i++) {
	back['L' + i] = 0;
}

// -->
</script>

<!-- end of menu header - {packageName} {version} {copyright} {author} -->
