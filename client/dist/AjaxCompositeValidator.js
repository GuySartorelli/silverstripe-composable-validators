(()=>{var t,a={504:()=>{function t(a){return(t="function"==typeof Symbol&&"symbol"==typeof Symbol.iterator?function(t){return typeof t}:function(t){return t&&"function"==typeof Symbol&&t.constructor===Symbol&&t!==Symbol.prototype?"symbol":typeof t})(a)}!function(a){function e(){return a.isFunction(a.entwine)}function n(t){return a("<div/>").text(t).html()}function i(t){return t.replace(/\n/g,"<br>")}function r(t,a){var e=arguments.length>2&&void 0!==arguments[2]?arguments[2]:null;if("undefined"!=typeof ss&&void 0!==ss.i18n)return ss.i18n.inject(ss.i18n._t(t,a),e);if(e){var n=new RegExp("{([A-Za-z0-9_]*)}","g");a=a.replace(n,(function(t,a){return e[a]?e[a]:t}))}return a}function o(t,i){var r=arguments.length>2&&void 0!==arguments[2]?arguments[2]:null,o=n(t);a.isFunction(a.noticeAdd)&&a.noticeAdd({text:o,type:i,stayTime:5e3,inEffect:{left:"0",opacity:"show"}}),e()||null==r||a("body,html").animate({scrollTop:r.offset().top},500)}function s(t,a){var e="".concat(t.attr("id"),"_"),n=t.find("#".concat(e,"error"));n.text(a),a?(n.addClass("validation validation-bar"),n.show(),n.attr("tabindex",-1),n.focus()):(n.removeClass("validation"),n.hide())}function l(t,n){n.addClass("validationerror"),s(n,r("Signify_AjaxCompositeValidator.VALIDATION_ERRORS","There are validation errors on this form, please fix them before saving or publishing."));for(var l="".concat(n.attr("id"),"_"),d=0;d<t.length;d+=1){var c=t[d];if(c.fieldName){var u="".concat(l).concat(c.fieldName.replace(new RegExp(/_{2,}/g),"_")),f=a("#".concat(u).concat("_Holder")),v=null,m=(v=e()&&f.length?f:a("#".concat(u))).parents(".tab-pane").attr("aria-labelledby");a("#".concat(m)).addClass("font-icon-attention-1 tab-validation tab-validation--".concat(c.messageType));var b=a("<div/>").html(i(c.message)).addClass("js-ajax-validation message ".concat(c.messageType)).attr("id","".concat(u,"_validation-message"));e()?b.insertBefore(v):(v.addClass("holder-required"),b.addClass("form__message form__message--required"),b.insertAfter(v)),a("#".concat(u)).data("old-described-by",a("#".concat(u)).attr("aria-describedby")),a("#".concat(u)).attr("aria-describedby","".concat(u,"_validation-message"))}else{a("<div/>").html(i(c.message)).addClass("js-ajax-validation message ".concat(c.messageType)).insertAfter(n.find("#".concat(l,"error")))}}o(r("Signify_AjaxCompositeValidator.VALIDATION_ERROR_TOAST","Validation Error"),"error",n)}function d(t){s(t,""),t.removeClass("validationerror"),t.find(".holder-required").removeClass("holder-required"),t.find(".js-ajax-validation").remove(),t.find("a.ui-tabs-anchor").each((function(t,e){a(e).removeClass("font-icon-attention-1 tab-validation"),e.className=e.className.replace(new RegExp(/tab-validation--\w*/g,""))})),t.find("[aria-describedby]").each((function(t,e){var n=a(e);n.data("old-described-by")&&(n.attr("aria-describedby",n.data("old-described-by")),n.data("old-described-by",null))}))}function c(t,a){if(e()){if(a){var n=t.closest(".cms-container");n.length&&n.submitForm(t,a)}}else if("nocaptcha_handleCaptcha"==typeof yourFunctionName){var i=t.get(0);nocaptcha_handleCaptcha(i,i.submitWithoutEvent.bind(i))}else t.get(0).submitWithoutEvent()}function u(e,n,i){var s=arguments.length>3&&void 0!==arguments[3]?arguments[3]:a;if(e.preventDefault(),!n){var u,f,v,m,b=null!==(u=e.delegatedEvent)&&void 0!==u?u:e,p=null!==(f=b.originalEvent)&&void 0!==f?f:b,h=s(null!==(v=p.submitter)&&void 0!==v?v:p.target);if(h.hasClass("element-editor__hover-bar-area"))return!1;null!==(m=h.attr("name"))&&void 0!==m&&m.startsWith("action_")&&(n=h.get(0))}s(n).attr("disabled",!0);var g=void 0!==i?i:s(this);function y(a){var e=!1;!0!==a&&a.length&&(e=!0);var i=g.find("div.g-recaptcha");if(i.length>0&&"object"===("undefined"==typeof grecaptcha?"undefined":t(grecaptcha))){var o=i.data("widgetid");if(null!=o&&!grecaptcha.getResponse(o)){!0===a&&(a=[]);var d=r("Signify_AjaxCompositeValidator.CAPTCHA_VALIDATION_ERROR","Please answer the captcha.");a.push({fieldName:null,message:d,messageType:"required"}),e=!0}}return g.removeClass("js-validating"),e?(l(a,g),s(n).attr("disabled",!1),s(n).removeClass("loading"),!1):c(g,n)}function _(t,a,e){return o(r("Signify_AjaxCompositeValidator.CANNOT_VALIDATE","Could not validate. Aborting AJAX validation."),"error"),console.error("Error with AJAX validation request: ".concat(a,": ").concat(e)),c(g,n)}g.addClass("js-validating"),d(g);var A=g.data("validation-link"),C=g.serializeArray();return C.push({name:"action_app_ajaxValidate",value:"1"}),n&&C.push({name:"_original_action",value:n.getAttribute("name")}),s.ajax({type:"POST",url:A,data:C,success:y,error:_}),!1}if(!e())return a("form.js-multi-validator-ajax").on("submit",u),void a("form.js-multi-validator-ajax").each((function(t,e){e.submitWithoutEvent=e.submit,e.submit=function(){var t;"function"==typeof Event?t=new Event("submit",{bubbles:!0,cancelable:!0}):(t=document.createEvent("Event")).initEvent("submit",!0,!0),t.submitter=a(e).find('button[type="submit"], input[type="submit"]').get(0),e.dispatchEvent(t)}}));a.entwine("ss",(function(t){t("form.js-multi-validator-ajax").entwine({onsubmit:function(a,e){return u(a,e,this,t)}})}))}(jQuery)},1:()=>{}},e={};function n(t){var i=e[t];if(void 0!==i)return i.exports;var r=e[t]={exports:{}};return a[t](r,r.exports,n),r.exports}n.m=a,t=[],n.O=(a,e,i,r)=>{if(!e){var o=1/0;for(c=0;c<t.length;c++){for(var[e,i,r]=t[c],s=!0,l=0;l<e.length;l++)(!1&r||o>=r)&&Object.keys(n.O).every((t=>n.O[t](e[l])))?e.splice(l--,1):(s=!1,r<o&&(o=r));if(s){t.splice(c--,1);var d=i();void 0!==d&&(a=d)}}return a}r=r||0;for(var c=t.length;c>0&&t[c-1][2]>r;c--)t[c]=t[c-1];t[c]=[e,i,r]},n.o=(t,a)=>Object.prototype.hasOwnProperty.call(t,a),(()=>{var t={458:0,177:0};n.O.j=a=>0===t[a];var a=(a,e)=>{var i,r,[o,s,l]=e,d=0;for(i in s)n.o(s,i)&&(n.m[i]=s[i]);if(l)var c=l(n);for(a&&a(e);d<o.length;d++)r=o[d],n.o(t,r)&&t[r]&&t[r][0](),t[o[d]]=0;return n.O(c)},e=self.webpackChunksignify_composable_validators=self.webpackChunksignify_composable_validators||[];e.forEach(a.bind(null,0)),e.push=a.bind(null,e.push.bind(e))})(),n.O(void 0,[177],(()=>n(504)));var i=n.O(void 0,[177],(()=>n(1)));i=n.O(i)})();