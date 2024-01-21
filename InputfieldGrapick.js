// trim, rtrim, ltrim

function trim(str, chr) {
  let rgxtrim = (!chr) ? new RegExp('^\\s+|\\s+$', 'g') : new RegExp('^'+chr+'+|'+chr+'+$', 'g');
  return str.replace(rgxtrim, '');
}
function rtrim(str, chr) {
  let rgxtrim = (!chr) ? new RegExp('\\s+$') : new RegExp(chr+'+$');
  return str.replace(rgxtrim, '');
}
function ltrim(str, chr) {
  let rgxtrim = (!chr) ? new RegExp('^\\s+') : new RegExp('^'+chr+'+');
  return str.replace(rgxtrim, '');
}

//color conversion functions

function RGBAToHexA(rgba) {

  let sep = rgba.indexOf(",") > -1 ? "," : " "; 
  rgba = rgba.substring(rgba.indexOf('(')+1).split(")")[0].split(sep);

  // Strip the slash if using space-separated syntax
  if (rgba.indexOf("/") > -1)
    rgba.splice(3,1);

  for (let R in rgba) {
    let r = rgba[R];
    if (r.indexOf("%") > -1) {
      let p = r.substr(0,r.length - 1) / 100;

      if (R < 3) {
        rgba[R] = Math.round(p * 255);
      } else {
        rgba[R] = p;
      }
    }
  }

  if(+rgba[3] == 255) {rgba[3] = 1;} //for some reason spectrum drops an alpha of 255 instead of 1 on creation.

  let r = (+rgba[0]).toString(16),
      g = (+rgba[1]).toString(16),
      b = (+rgba[2]).toString(16),
      a = Math.round((+rgba[3] || 1)*255).toString(16);

  if (r.length == 1)
    r = "0" + r;
  if (g.length == 1)
    g = "0" + g;
  if (b.length == 1)
    b = "0" + b;
  if (a.length == 1)
    a = "0" + a;

  return (a + r + g + b).toUpperCase();

}

function hex32ToRgb(hex) {
  let result = /^#?([a-f\d]{2})([a-f\d]{2})([a-f\d]{2})([a-f\d]{2})$/i.exec(hex);
  return result ? {
	a: parseInt(result[1], 16),
    r: parseInt(result[2], 16),
    g: parseInt(result[3], 16),
    b: parseInt(result[4], 16),
	full: 'rgba('+parseInt(result[2], 16) +', '+ parseInt(result[3], 16)+', '+ parseInt(result[4], 16)+', '+ Math.round(parseInt(result[1], 16)/255)+')',
  } : null;
}

function build_rule(ind) {

	let css_fn = '', shape = '', origin = false, angle = false, size = false, repeat = false;

	let ipt_style = document.getElementById(ind + '-style');
	let ipt_origin = document.getElementById(ind + '-origin');
	let ipt_angle = document.getElementById(ind + '-angle');
	let ipt_size = document.getElementById(ind + '-size');


	let sample_div = document.getElementById(ind+'-sample_target');

	let rule_span = document.getElementById(ind+'-rule');

	switch (ipt_style.value) {
		case 'radial-circle' :
			css_fn = 'radial-gradient';
			shape = 'circle at';
			origin = ipt_origin.value.replace(/_/g, ' ');
			break;
		case 'radial-ellipse' :
			css_fn = 'radial-gradient';
			shape = 'ellipse 125% 125% at';
			origin = ipt_origin.value.replace(/_/g, ' ');
			break;
		case 'repeating-linear' :
			css_fn = 'repeating-linear-gradient';
			angle = ipt_angle.value.replace(/\-/g,'');
			repeat = true;
			break;
		case 'repeating-radial-circle' :
			css_fn = 'repeating-radial-gradient';
			shape = 'circle  at';
			origin = ipt_origin.value.replace(/_/g, ' ');
			repeat = true;
			break;
		case 'repeating-radial-ellipse' :
			css_fn = 'repeating-radial-gradient';
			shape = 'ellipse 125% 125% at';
			origin = ipt_origin.value.replace(/_/g, ' ');
			repeat = true;
			break;
		case 'linear' :
		default : 
			css_fn = 'linear-gradient';
			angle = ipt_angle.value.replace(/\-/g,'');
			break;
	}

	let rule_out = css_fn+'(';

	rule_out += (shape !== '') ? shape + ' ' : '';

	rule_out += (origin) ?  origin + ', ' : '';
	rule_out += (angle) ? angle + ', ' : '';

	let rule_tmp = '';

	let loadStops = stopTxt[ind].value.split('|');

	for (let ls of loadStops) {
		let stopParts = ls.split('^');
		let clr = hex32ToRgb(stopParts[0]);
		if (repeat) {
			gr_size = (ipt_size.value!=0) ? +ipt_size.value : 100 ;
			px_size = (+stopParts[1]/100) * gr_size;
			rule_tmp += clr.full + ' ' + Math.round(px_size) +'px, ';
		} else {
			rule_tmp += clr.full + ' ' + stopParts[1] +'%, ';
		}
	}

	rule_out += rtrim(rule_tmp, ', ');

	rule_out += ")";

	sample_div.style.backgroundImage = rule_out;
	rule_span.innerHTML = rule_out;

	return;
}

/**
 * Begin grapick implementation
 */

//globals

var upType, unAngle, gp = [];
const stopTxt = [], swType = [], swAngle = [], swOrigin = [], swSize = [];

const pwConfig = ProcessWire.config.grapicks;

console.log('pwConfig',pwConfig);

function pollingRun() {
	let done = true;
	for (let key in pwConfig) {
		if (pwConfig.hasOwnProperty(key)) {
			if(pwConfig[key]['loaded'] == false) {
				done = false;
				let ctrl = document.getElementById(key+'_grapick_control');
				//console.log(key, pwConfig[key], ctrl);
				if(ctrl) {
					createGrapick(key);
				}
				//console.log('Grapick field: ' + key);
				for (let key2 in pwConfig[key]) {
					//console.log('---->', key2, pwConfig[key][key2]);
				}
			}
		}
	}
	if(done) {
		//console.log('Done.');
		clearInterval(poll);
	}
}

var createGrapick = function(key) {
	gp[key] = new Grapick({
		el: '#' + key + '_grapick_control',
		colorEl: '<input id="' + key + '_colorpicker"/>', // I'll use this for the custom color picker
		direction: 'right',
		min: 0,
		max: 100,
		height: '2.5rem',
	});

	stopTxt[key] = document.getElementById(key + '-stops');
	swType[key] = document.getElementById(key + '-style');
	swAngle[key] = document.getElementById(key + '-angle');
	swOrigin[key] = document.getElementById(key + '-origin');
	swSize[key] = document.getElementById(key + '-size');

	swType[key].addEventListener('change', () => {build_rule(key);});
	swAngle[key].addEventListener('change', () => {build_rule(key);});
	swOrigin[key].addEventListener('change', () => {build_rule(key);});
	swSize[key].addEventListener('change', () => {build_rule(key);});

	gp[key].setColorPicker(handler => {
		const el = handler.getEl().querySelector('#' + key + '_colorpicker');
		const $el = $(el);

		$el.spectrum({
			color: handler.getColor(),
			showAlpha: true,
			clickoutFiresChange: true,
			change(color) {
			handler.setColor(color.toRgbString());
			},
			move(color) {
			handler.setColor(color.toRgbString(), 0);
			}
		});

		// return a function in order to destroy the custom color picker
		return () => {
		$el.spectrum('destroy');
		}
	});

	let pwStopsWrap = document.getElementById('wrap_'+key+'-stops');

	let loadStops = stopTxt[key].value.split('|');

	for (let ls of loadStops) {
		let stopParts = ls.split('^');
		let clr = hex32ToRgb(stopParts[0]);
		gp[key].addHandler(+stopParts[1], clr.full, 0);
	}
	
	gp[key].on('change', function(complete) {

		let live_stops = gp[key].getHandlers();
		let flat_stops = '';
		live_stops.forEach((lmt) => {
			let flat_stop = '';
				flat_stop += RGBAToHexA(lmt.color) + '^' + Math.round(lmt.position);
//						flat_stop += RGBAToHexA(lmt.color) + '^' + lmt.position;
			flat_stops += flat_stop + '|';
		});
		stopTxt[key].value = rtrim(flat_stops,"\\|");
		//Will allow an inputfield to be picked up as changed when using the advanced features of UserActivity.
		pwStopsWrap.classList.add('InputfieldStateChanged');
		build_rule(key);
	})
	gp[key].emit('change');
	pwConfig[key]['loaded'] = true;
};

var destroyGrapick = function(key) {
	gp[key].destroy();
	gp[key] = 0;
}

document.addEventListener('DOMContentLoaded', pollingRun, false);

var poll = setInterval(pollingRun, 1000);

document.addEventListener('DOMContentLoaded', () => {

	//Look for new grapick fields (to account for repeaters)

	// select the target node
	var target = document.getElementById('ProcessPageEdit');

	// create an observer instance
	var observer = new MutationObserver(function(mutations) {
		mutations.forEach(function(mutation) {
		var nodes = mutation.addedNodes;
		var node;
		for(var n = 0; node = nodes[n], n < nodes.length; n++) {
			test_id = node.id;
	        if(node.tagName == 'LI' && test_id.includes('repeater_item')) {
				getGrapickControlNodes();
			}
		};
		});
	});

	// configuration of the observer:
	var config = { attributes: false, childList: true, subtree: true, characterData: false };

	// pass in the target node, as well as the observer options
	observer.observe(target, config);
		
}, false);

function getGrapickControlNodes() {
	const nodeSet = document.querySelectorAll('[id*="_grapick_control"]').forEach(item => {
		keyName = item.id.substring(0,item.id.indexOf('_grapick_control'));
		pwConfig[keyName] = {'loaded':true};
		//console.log('post add',pwConfig);
		createGrapick(keyName);
	});
}
