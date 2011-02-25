/*
 * jQuery Form Plugin
 * version: 2.43 (12-MAR-2010)
 * @requires jQuery v1.3.2 or later
 */
(function(b){b.fn.ajaxSubmit=function(s){if(!this.length){a("ajaxSubmit: skipping submit process - no element selected");return this}if(typeof s=="function"){s={success:s}}var e=b.trim(this.attr("action"));if(e){e=(e.match(/^([^#]+)/)||[])[1]}e=e||window.location.href||"";s=b.extend({url:e,type:this.attr("method")||"GET",iframeSrc:/^https/i.test(window.location.href||"")?"javascript:false":"about:blank"},s||{});var u={};this.trigger("form-pre-serialize",[this,s,u]);if(u.veto){a("ajaxSubmit: submit vetoed via form-pre-serialize trigger");return this}if(s.beforeSerialize&&s.beforeSerialize(this,s)===false){a("ajaxSubmit: submit aborted via beforeSerialize callback");return this}var m=this.formToArray(s.semantic);if(s.data){s.extraData=s.data;for(var f in s.data){if(s.data[f] instanceof Array){for(var g in s.data[f]){m.push({name:f,value:s.data[f][g]})}}else{m.push({name:f,value:s.data[f]})}}}if(s.beforeSubmit&&s.beforeSubmit(m,this,s)===false){a("ajaxSubmit: submit aborted via beforeSubmit callback");return this}this.trigger("form-submit-validate",[m,this,s,u]);if(u.veto){a("ajaxSubmit: submit vetoed via form-submit-validate trigger");return this}var d=b.param(m);if(s.type.toUpperCase()=="GET"){s.url+=(s.url.indexOf("?")>=0?"&":"?")+d;s.data=null}else{s.data=d}var t=this,l=[];if(s.resetForm){l.push(function(){t.resetForm()})}if(s.clearForm){l.push(function(){t.clearForm()})}if(!s.dataType&&s.target){var p=s.success||function(){};l.push(function(k){var j=s.replaceTarget?"replaceWith":"html";b(s.target)[j](k).each(p,arguments)})}else{if(s.success){l.push(s.success)}}s.success=function(q,k,v){for(var n=0,j=l.length;n<j;n++){l[n].apply(s,[q,k,v||t,t])}};var c=b("input:file",this).fieldValue();var r=false;for(var i=0;i<c.length;i++){if(c[i]){r=true}}var h=false;if((c.length&&s.iframe!==false)||s.iframe||r||h){if(s.closeKeepAlive){b.get(s.closeKeepAlive,o)}else{o()}}else{b.ajax(s)}this.trigger("form-submit-notify",[this,s]);return this;function o(){var w=t[0];if(b(":input[name=submit]",w).length){alert('Error: Form elements must not be named "submit".');return}var q=b.extend({},b.ajaxSettings,s);var H=b.extend(true,{},b.extend(true,{},b.ajaxSettings),q);var v="jqFormIO"+(new Date().getTime());var D=b('<iframe id="'+v+'" name="'+v+'" src="'+q.iframeSrc+'" onload="(jQuery(this).data(\'form-plugin-onload\'))()" />');var F=D[0];D.css({position:"absolute",top:"-1000px",left:"-1000px"});var G={aborted:0,responseText:null,responseXML:null,status:0,statusText:"n/a",getAllResponseHeaders:function(){},getResponseHeader:function(){},setRequestHeader:function(){},abort:function(){this.aborted=1;D.attr("src",q.iframeSrc)}};var E=q.global;if(E&&!b.active++){b.event.trigger("ajaxStart")}if(E){b.event.trigger("ajaxSend",[G,q])}if(H.beforeSend&&H.beforeSend(G,H)===false){H.global&&b.active--;return}if(G.aborted){return}var k=false;var A=0;var j=w.clk;if(j){var y=j.name;if(y&&!j.disabled){q.extraData=q.extraData||{};q.extraData[y]=j.value;if(j.type=="image"){q.extraData[y+".x"]=w.clk_x;q.extraData[y+".y"]=w.clk_y}}}function x(){var K=t.attr("target"),I=t.attr("action");w.setAttribute("target",v);if(w.getAttribute("method")!="POST"){w.setAttribute("method","POST")}if(w.getAttribute("action")!=q.url){w.setAttribute("action",q.url)}if(!q.skipEncodingOverride){t.attr({encoding:"multipart/form-data",enctype:"multipart/form-data"})}if(q.timeout){setTimeout(function(){A=true;B()},q.timeout)}var J=[];try{if(q.extraData){for(var L in q.extraData){J.push(b('<input type="hidden" name="'+L+'" value="'+q.extraData[L]+'" />').appendTo(w)[0])}}D.appendTo("body");D.data("form-plugin-onload",B);w.submit()}finally{w.setAttribute("action",I);K?w.setAttribute("target",K):t.removeAttr("target");b(J).remove()}}if(q.forceSync){x()}else{setTimeout(x,10)}var z=100;function B(){if(k){return}var I=true;try{if(A){throw"timeout"}var J,M;M=F.contentWindow?F.contentWindow.document:F.contentDocument?F.contentDocument:F.document;var N=q.dataType=="xml"||M.XMLDocument||b.isXMLDoc(M);a("isXml="+N);if(!N&&(M.body==null||M.body.innerHTML=="")){if(--z){a("requeing onLoad callback, DOM not available");setTimeout(B,250);return}a("Could not access iframe DOM after 100 tries.");return}a("response detected");k=true;G.responseText=M.body?M.body.innerHTML:null;G.responseXML=M.XMLDocument?M.XMLDocument:M;G.getResponseHeader=function(P){var O={"content-type":q.dataType};return O[P]};if(q.dataType=="json"||q.dataType=="script"){var n=M.getElementsByTagName("textarea")[0];if(n){G.responseText=n.value}else{var L=M.getElementsByTagName("pre")[0];if(L){G.responseText=L.innerHTML}}}else{if(q.dataType=="xml"&&!G.responseXML&&G.responseText!=null){G.responseXML=C(G.responseText)}}J=b.httpData(G,q.dataType)}catch(K){a("error caught:",K);I=false;G.error=K;b.handleError(q,G,"error",K)}if(I){q.success(J,"success");if(E){b.event.trigger("ajaxSuccess",[G,q])}}if(E){b.event.trigger("ajaxComplete",[G,q])}if(E&&!--b.active){b.event.trigger("ajaxStop")}if(q.complete){q.complete(G,I?"success":"error")}setTimeout(function(){D.removeData("form-plugin-onload");D.remove();G.responseXML=null},100)}function C(n,I){if(window.ActiveXObject){I=new ActiveXObject("Microsoft.XMLDOM");I.async="false";I.loadXML(n)}else{I=(new DOMParser()).parseFromString(n,"text/xml")}return(I&&I.documentElement&&I.documentElement.tagName!="parsererror")?I:null}}};b.fn.ajaxForm=function(c){return this.ajaxFormUnbind().bind("submit.form-plugin",function(d){d.preventDefault();b(this).ajaxSubmit(c)}).bind("click.form-plugin",function(i){var h=i.target;var f=b(h);if(!(f.is(":submit,input:image"))){var d=f.closest(":submit");if(d.length==0){return}h=d[0]}var g=this;g.clk=h;if(h.type=="image"){if(i.offsetX!=undefined){g.clk_x=i.offsetX;g.clk_y=i.offsetY}else{if(typeof b.fn.offset=="function"){var j=f.offset();g.clk_x=i.pageX-j.left;g.clk_y=i.pageY-j.top}else{g.clk_x=i.pageX-h.offsetLeft;g.clk_y=i.pageY-h.offsetTop}}}setTimeout(function(){g.clk=g.clk_x=g.clk_y=null},100)})};b.fn.ajaxFormUnbind=function(){return this.unbind("submit.form-plugin click.form-plugin")};b.fn.formToArray=function(q){var p=[];if(this.length==0){return p}var d=this[0];var h=q?d.getElementsByTagName("*"):d.elements;if(!h){return p}for(var k=0,m=h.length;k<m;k++){var e=h[k];var f=e.name;if(!f){continue}if(q&&d.clk&&e.type=="image"){if(!e.disabled&&d.clk==e){p.push({name:f,value:b(e).val()});p.push({name:f+".x",value:d.clk_x},{name:f+".y",value:d.clk_y})}continue}var r=b.fieldValue(e,true);if(r&&r.constructor==Array){for(var g=0,c=r.length;g<c;g++){p.push({name:f,value:r[g]})}}else{if(r!==null&&typeof r!="undefined"){p.push({name:f,value:r})}}}if(!q&&d.clk){var l=b(d.clk),o=l[0],f=o.name;if(f&&!o.disabled&&o.type=="image"){p.push({name:f,value:l.val()});p.push({name:f+".x",value:d.clk_x},{name:f+".y",value:d.clk_y})}}return p};b.fn.formSerialize=function(c){return b.param(this.formToArray(c))};b.fn.fieldSerialize=function(d){var c=[];this.each(function(){var h=this.name;if(!h){return}var f=b.fieldValue(this,d);if(f&&f.constructor==Array){for(var g=0,e=f.length;g<e;g++){c.push({name:h,value:f[g]})}}else{if(f!==null&&typeof f!="undefined"){c.push({name:this.name,value:f})}}});return b.param(c)};b.fn.fieldValue=function(h){for(var g=[],e=0,c=this.length;e<c;e++){var f=this[e];var d=b.fieldValue(f,h);if(d===null||typeof d=="undefined"||(d.constructor==Array&&!d.length)){continue}d.constructor==Array?b.merge(g,d):g.push(d)}return g};b.fieldValue=function(c,j){var e=c.name,p=c.type,q=c.tagName.toLowerCase();if(typeof j=="undefined"){j=true}if(j&&(!e||c.disabled||p=="reset"||p=="button"||(p=="checkbox"||p=="radio")&&!c.checked||(p=="submit"||p=="image")&&c.form&&c.form.clk!=c||q=="select"&&c.selectedIndex==-1)){return null}if(q=="select"){var k=c.selectedIndex;if(k<0){return null}var m=[],d=c.options;var g=(p=="select-one");var l=(g?k+1:d.length);for(var f=(g?k:0);f<l;f++){var h=d[f];if(h.selected){var o=h.value;if(!o){o=(h.attributes&&h.attributes.value&&!(h.attributes.value.specified))?h.text:h.value}if(g){return o}m.push(o)}}return m}return c.value};b.fn.clearForm=function(){return this.each(function(){b("input,select,textarea",this).clearFields()})};b.fn.clearFields=b.fn.clearInputs=function(){return this.each(function(){var d=this.type,c=this.tagName.toLowerCase();if(d=="text"||d=="password"||c=="textarea"){this.value=""}else{if(d=="checkbox"||d=="radio"){this.checked=false}else{if(c=="select"){this.selectedIndex=-1}}}})};b.fn.resetForm=function(){return this.each(function(){if(typeof this.reset=="function"||(typeof this.reset=="object"&&!this.reset.nodeType)){this.reset()}})};b.fn.enable=function(c){if(c==undefined){c=true}return this.each(function(){this.disabled=!c})};b.fn.selected=function(c){if(c==undefined){c=true}return this.each(function(){var d=this.type;if(d=="checkbox"||d=="radio"){this.checked=c}else{if(this.tagName.toLowerCase()=="option"){var e=b(this).parent("select");if(c&&e[0]&&e[0].type=="select-one"){e.find("option").selected(false)}this.selected=c}}})};function a(){if(b.fn.ajaxSubmit.debug){var c="[jquery.form] "+Array.prototype.join.call(arguments,"");if(window.console&&window.console.log){window.console.log(c)}else{if(window.opera&&window.opera.postError){window.opera.postError(c)}}}}})(jQuery);
