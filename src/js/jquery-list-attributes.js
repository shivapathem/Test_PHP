// Author: Jerome Clyde C. Bulanadi

if(jQuery) {
	jQuery(document).ready(function() {
		jQuery.fn.listAttrs = function(prefix) {
			var list = [];
			var $elements = jQuery(this);
			var size = $elements.size();
			if (size == 0){
				return list;
			}
			jQuery($elements[0].attributes).each(function(index){
				if(jQuery(this)[0].nodeValue != null && jQuery(this)[0].nodeValue != '') {
					list.push(jQuery(this)[0].nodeName);
				};
			});
			return list;
		}
	});
}