"use strict";(self.webpackChunksimple_secure_stripe=self.webpackChunksimple_secure_stripe||[]).push([[348],{348:(t,e,r)=>{r.d(e,{OZ:()=>c.O,hB:()=>o.h,iw:()=>i.i,k5:()=>n.k,m:()=>a.m,sX:()=>u.s});var n=r(1913),o=r(3516),i=(r(7601),r(6808)),a=r(3320),u=r(7057),c=(r(2479),r(2497));r(6433)},2497:(t,e,r)=>{r.d(e,{O:()=>u});var n=r(959),o=r(9288);function i(t,e){return function(t){if(Array.isArray(t))return t}(t)||function(t,e){var r=null==t?null:"undefined"!=typeof Symbol&&t[Symbol.iterator]||t["@@iterator"];if(null!=r){var n,o,i,a,u=[],c=!0,l=!1;try{if(i=(r=r.call(t)).next,0===e){if(Object(r)!==r)return;c=!1}else for(;!(c=(n=i.call(r)).done)&&(u.push(n.value),u.length!==e);c=!0);}catch(t){l=!0,o=t}finally{try{if(!c&&null!=r.return&&(a=r.return(),Object(a)!==a))return}finally{if(l)throw o}}return u}}(t,e)||function(t,e){if(!t)return;if("string"==typeof t)return a(t,e);var r=Object.prototype.toString.call(t).slice(8,-1);"Object"===r&&t.constructor&&(r=t.constructor.name);if("Map"===r||"Set"===r)return Array.from(t);if("Arguments"===r||/^(?:Ui|I)nt(?:8|16|32)(?:Clamped)?Array$/.test(r))return a(t,e)}(t,e)||function(){throw new TypeError("Invalid attempt to destructure non-iterable instance.\nIn order to be iterable, non-array objects must have a [Symbol.iterator]() method.")}()}function a(t,e){(null==e||e>t.length)&&(e=t.length);for(var r=0,n=new Array(e);r<e;r++)n[r]=t[r];return n}var u=function(t){var e=t.payment_method;!function(t){var e=t.name,r=t.width,a=t.node,u=t.className,c=i((0,n.useState)(window.innerWidth),2),l=c[0],s=c[1],f=(0,n.useCallback)((function(t){var e=(0,o.TK)(t);return e?parseInt(e):0}),[]),p=(0,n.useCallback)((function(t,e){return(0,o._B)(t,e)}),[]);(0,n.useEffect)((function(){var t="function"==typeof a?a():a;if(t){var n=f(e);(!n||r>n)&&p(e,r),t.clientWidth<r?t.classList.add(u):t.clientWidth>n&&t.classList.remove(u)}}),[l,a]),(0,n.useEffect)((function(){var t=function(){return s(window.innerWidth)};return window.addEventListener("resize",t),function(){return window.removeEventListener("resize",t)}}))}({name:"expressMaxWidth",width:t.width,node:(0,n.useCallback)((function(){var t=document.getElementById("express-payment-method-".concat(e));return t?t.parentNode:null}),[]),className:"sswps-express__sm"})}},3320:(t,e,r)=>{r.d(e,{m:()=>o});var n=r(959),o=function(){return(0,n.useRef)({}).current}},7057:(t,e,r)=>{r.d(e,{s:()=>h});var n=r(959),o=r(9288),i=r(6189);function a(t){return a="function"==typeof Symbol&&"symbol"==typeof Symbol.iterator?function(t){return typeof t}:function(t){return t&&"function"==typeof Symbol&&t.constructor===Symbol&&t!==Symbol.prototype?"symbol":typeof t},a(t)}function u(t){return function(t){if(Array.isArray(t))return d(t)}(t)||function(t){if("undefined"!=typeof Symbol&&null!=t[Symbol.iterator]||null!=t["@@iterator"])return Array.from(t)}(t)||p(t)||function(){throw new TypeError("Invalid attempt to spread non-iterable instance.\nIn order to be iterable, non-array objects must have a [Symbol.iterator]() method.")}()}function c(t,e){var r=Object.keys(t);if(Object.getOwnPropertySymbols){var n=Object.getOwnPropertySymbols(t);e&&(n=n.filter((function(e){return Object.getOwnPropertyDescriptor(t,e).enumerable}))),r.push.apply(r,n)}return r}function l(t){for(var e=1;e<arguments.length;e++){var r=null!=arguments[e]?arguments[e]:{};e%2?c(Object(r),!0).forEach((function(e){s(t,e,r[e])})):Object.getOwnPropertyDescriptors?Object.defineProperties(t,Object.getOwnPropertyDescriptors(r)):c(Object(r)).forEach((function(e){Object.defineProperty(t,e,Object.getOwnPropertyDescriptor(r,e))}))}return t}function s(t,e,r){return(e=function(t){var e=function(t,e){if("object"!==a(t)||null===t)return t;var r=t[Symbol.toPrimitive];if(void 0!==r){var n=r.call(t,e||"default");if("object"!==a(n))return n;throw new TypeError("@@toPrimitive must return a primitive value.")}return("string"===e?String:Number)(t)}(t,"string");return"symbol"===a(e)?e:String(e)}(e))in t?Object.defineProperty(t,e,{value:r,enumerable:!0,configurable:!0,writable:!0}):t[e]=r,t}function f(t,e){return function(t){if(Array.isArray(t))return t}(t)||function(t,e){var r=null==t?null:"undefined"!=typeof Symbol&&t[Symbol.iterator]||t["@@iterator"];if(null!=r){var n,o,i,a,u=[],c=!0,l=!1;try{if(i=(r=r.call(t)).next,0===e){if(Object(r)!==r)return;c=!1}else for(;!(c=(n=i.call(r)).done)&&(u.push(n.value),u.length!==e);c=!0);}catch(t){l=!0,o=t}finally{try{if(!c&&null!=r.return&&(a=r.return(),Object(a)!==a))return}finally{if(l)throw o}}return u}}(t,e)||p(t,e)||function(){throw new TypeError("Invalid attempt to destructure non-iterable instance.\nIn order to be iterable, non-array objects must have a [Symbol.iterator]() method.")}()}function p(t,e){if(t){if("string"==typeof t)return d(t,e);var r=Object.prototype.toString.call(t).slice(8,-1);return"Object"===r&&t.constructor&&(r=t.constructor.name),"Map"===r||"Set"===r?Array.from(t):"Arguments"===r||/^(?:Ui|I)nt(?:8|16|32)(?:Clamped)?Array$/.test(r)?d(t,e):void 0}}function d(t,e){(null==e||e>t.length)&&(e=t.length);for(var r=0,n=new Array(e);r<e;r++)n[r]=t[r];return n}var y=(0,o.f4)(),h=function(t){var e=t.getData,r=t.onClose,a=t.stripe,c=t.billing,s=t.shippingData,p=t.setPaymentMethod,d=t.exportedValues,h=t.canPay,v=(s.shippingAddress,s.needsShipping),m=s.shippingRates,g=c.billingData,b=c.cartTotalItems,w=c.currency,O=c.cartTotal,S=f((0,n.useState)(null),2),j=S[0],_=S[1],x=(0,n.useRef)({}),E=(0,n.useRef)(s),P=(0,n.useRef)(c);(0,n.useEffect)((function(){E.current=s,P.current=c}),[s,c]),(0,n.useEffect)((function(){if(a){var t={country:e("countryCode"),currency:null==w?void 0:w.code.toLowerCase(),total:{amount:O.value,label:O.label,pending:!0},requestPayerName:!0,requestPayerEmail:(0,o.zx)("email",g.country),requestPayerPhone:(0,o.zx)(v?"shipping-phone":"phone",g.country),requestShipping:v,displayItems:(0,o.xA)(b,w)};t.requestShipping&&(t.shippingOptions=(0,o.FM)(m)),x.current=t;var r=a.paymentRequest(x.current);r.canMakePayment().then((function(t){h(t)?_(r):_(null)}))}}),[a,O.value,g.country,m,b,w.code]);var k=(0,n.useCallback)((function(t){var r=E.current,n=t.shippingAddress,a=y(n);(0,i.Z)({method:"POST",url:(0,o.Bv)("shipping-address"),data:{address:a,payment_method:e("name"),page_id:"checkout"}}).then((function(e){t.updateWith(e.data.newData),r.setShippingAddress(l(l({},r.shippingAddress),a))})).catch((function(t){console.log(t)}))}),[]),A=(0,n.useCallback)((function(t){var r=t.shippingOption,n=E.current;(0,i.Z)({method:"POST",url:(0,o.Bv)("shipping-method"),data:{shipping_method:r.id,payment_method:e("name"),page_id:null}}).then((function(e){t.updateWith(e.data.newData),n.setSelectedRates.apply(n,u((0,o.NI)(r.id)))})).catch((function(t){console.log(t)}))}),[]),L=(0,n.useCallback)((function(t){var e=t.paymentMethod,r=t.payerName,n=void 0===r?null:r,o=t.payerEmail,i=void 0===o?null:o,a=t.payerPhone,u=void 0===a?null:a,c={payerName:n,payerEmail:i,payerPhone:u};null!=e&&e.billing_details.address&&(c=y(e.billing_details.address,c)),d.billingData=c,t.shippingAddress&&(d.shippingAddress=y(t.shippingAddress,{payerPhone:u})),p(e.id),t.complete("success")}),[]);return(0,n.useEffect)((function(){j&&(x.current.requestShipping&&(j.on("shippingaddresschange",k),j.on("shippingoptionchange",A)),j.on("cancel",r),j.on("paymentmethod",L))}),[r,j,k,L]),{paymentRequest:j}}},9288:(t,e,r)=>{r.d(e,{Bv:()=>C,FM:()=>Z,FT:()=>R,Gw:()=>q,NI:()=>U,NJ:()=>W,TK:()=>Q,W1:()=>M,_B:()=>X,cZ:()=>rt,e0:()=>et,f4:()=>z,fm:()=>T,jE:()=>H,n2:()=>N,pw:()=>B,xA:()=>K,zx:()=>F});var n=r(7420),o=r(6189);function i(t){return function(t){if(Array.isArray(t))return f(t)}(t)||function(t){if("undefined"!=typeof Symbol&&null!=t[Symbol.iterator]||null!=t["@@iterator"])return Array.from(t)}(t)||s(t)||function(){throw new TypeError("Invalid attempt to spread non-iterable instance.\nIn order to be iterable, non-array objects must have a [Symbol.iterator]() method.")}()}function a(){/*! regenerator-runtime -- Copyright (c) 2014-present, Facebook, Inc. -- license (MIT): https://github.com/facebook/regenerator/blob/main/LICENSE */a=function(){return t};var t={},e=Object.prototype,r=e.hasOwnProperty,n=Object.defineProperty||function(t,e,r){t[e]=r.value},o="function"==typeof Symbol?Symbol:{},i=o.iterator||"@@iterator",u=o.asyncIterator||"@@asyncIterator",c=o.toStringTag||"@@toStringTag";function l(t,e,r){return Object.defineProperty(t,e,{value:r,enumerable:!0,configurable:!0,writable:!0}),t[e]}try{l({},"")}catch(t){l=function(t,e,r){return t[e]=r}}function s(t,e,r,o){var i=e&&e.prototype instanceof y?e:y,a=Object.create(i.prototype),u=new P(o||[]);return n(a,"_invoke",{value:j(t,r,u)}),a}function f(t,e,r){try{return{type:"normal",arg:t.call(e,r)}}catch(t){return{type:"throw",arg:t}}}t.wrap=s;var d={};function y(){}function h(){}function v(){}var m={};l(m,i,(function(){return this}));var g=Object.getPrototypeOf,b=g&&g(g(k([])));b&&b!==e&&r.call(b,i)&&(m=b);var w=v.prototype=y.prototype=Object.create(m);function O(t){["next","throw","return"].forEach((function(e){l(t,e,(function(t){return this._invoke(e,t)}))}))}function S(t,e){function o(n,i,a,u){var c=f(t[n],t,i);if("throw"!==c.type){var l=c.arg,s=l.value;return s&&"object"==p(s)&&r.call(s,"__await")?e.resolve(s.__await).then((function(t){o("next",t,a,u)}),(function(t){o("throw",t,a,u)})):e.resolve(s).then((function(t){l.value=t,a(l)}),(function(t){return o("throw",t,a,u)}))}u(c.arg)}var i;n(this,"_invoke",{value:function(t,r){function n(){return new e((function(e,n){o(t,r,e,n)}))}return i=i?i.then(n,n):n()}})}function j(t,e,r){var n="suspendedStart";return function(o,i){if("executing"===n)throw new Error("Generator is already running");if("completed"===n){if("throw"===o)throw i;return A()}for(r.method=o,r.arg=i;;){var a=r.delegate;if(a){var u=_(a,r);if(u){if(u===d)continue;return u}}if("next"===r.method)r.sent=r._sent=r.arg;else if("throw"===r.method){if("suspendedStart"===n)throw n="completed",r.arg;r.dispatchException(r.arg)}else"return"===r.method&&r.abrupt("return",r.arg);n="executing";var c=f(t,e,r);if("normal"===c.type){if(n=r.done?"completed":"suspendedYield",c.arg===d)continue;return{value:c.arg,done:r.done}}"throw"===c.type&&(n="completed",r.method="throw",r.arg=c.arg)}}}function _(t,e){var r=e.method,n=t.iterator[r];if(void 0===n)return e.delegate=null,"throw"===r&&t.iterator.return&&(e.method="return",e.arg=void 0,_(t,e),"throw"===e.method)||"return"!==r&&(e.method="throw",e.arg=new TypeError("The iterator does not provide a '"+r+"' method")),d;var o=f(n,t.iterator,e.arg);if("throw"===o.type)return e.method="throw",e.arg=o.arg,e.delegate=null,d;var i=o.arg;return i?i.done?(e[t.resultName]=i.value,e.next=t.nextLoc,"return"!==e.method&&(e.method="next",e.arg=void 0),e.delegate=null,d):i:(e.method="throw",e.arg=new TypeError("iterator result is not an object"),e.delegate=null,d)}function x(t){var e={tryLoc:t[0]};1 in t&&(e.catchLoc=t[1]),2 in t&&(e.finallyLoc=t[2],e.afterLoc=t[3]),this.tryEntries.push(e)}function E(t){var e=t.completion||{};e.type="normal",delete e.arg,t.completion=e}function P(t){this.tryEntries=[{tryLoc:"root"}],t.forEach(x,this),this.reset(!0)}function k(t){if(t){var e=t[i];if(e)return e.call(t);if("function"==typeof t.next)return t;if(!isNaN(t.length)){var n=-1,o=function e(){for(;++n<t.length;)if(r.call(t,n))return e.value=t[n],e.done=!1,e;return e.value=void 0,e.done=!0,e};return o.next=o}}return{next:A}}function A(){return{value:void 0,done:!0}}return h.prototype=v,n(w,"constructor",{value:v,configurable:!0}),n(v,"constructor",{value:h,configurable:!0}),h.displayName=l(v,c,"GeneratorFunction"),t.isGeneratorFunction=function(t){var e="function"==typeof t&&t.constructor;return!!e&&(e===h||"GeneratorFunction"===(e.displayName||e.name))},t.mark=function(t){return Object.setPrototypeOf?Object.setPrototypeOf(t,v):(t.__proto__=v,l(t,c,"GeneratorFunction")),t.prototype=Object.create(w),t},t.awrap=function(t){return{__await:t}},O(S.prototype),l(S.prototype,u,(function(){return this})),t.AsyncIterator=S,t.async=function(e,r,n,o,i){void 0===i&&(i=Promise);var a=new S(s(e,r,n,o),i);return t.isGeneratorFunction(r)?a:a.next().then((function(t){return t.done?t.value:a.next()}))},O(w),l(w,c,"Generator"),l(w,i,(function(){return this})),l(w,"toString",(function(){return"[object Generator]"})),t.keys=function(t){var e=Object(t),r=[];for(var n in e)r.push(n);return r.reverse(),function t(){for(;r.length;){var n=r.pop();if(n in e)return t.value=n,t.done=!1,t}return t.done=!0,t}},t.values=k,P.prototype={constructor:P,reset:function(t){if(this.prev=0,this.next=0,this.sent=this._sent=void 0,this.done=!1,this.delegate=null,this.method="next",this.arg=void 0,this.tryEntries.forEach(E),!t)for(var e in this)"t"===e.charAt(0)&&r.call(this,e)&&!isNaN(+e.slice(1))&&(this[e]=void 0)},stop:function(){this.done=!0;var t=this.tryEntries[0].completion;if("throw"===t.type)throw t.arg;return this.rval},dispatchException:function(t){if(this.done)throw t;var e=this;function n(r,n){return a.type="throw",a.arg=t,e.next=r,n&&(e.method="next",e.arg=void 0),!!n}for(var o=this.tryEntries.length-1;o>=0;--o){var i=this.tryEntries[o],a=i.completion;if("root"===i.tryLoc)return n("end");if(i.tryLoc<=this.prev){var u=r.call(i,"catchLoc"),c=r.call(i,"finallyLoc");if(u&&c){if(this.prev<i.catchLoc)return n(i.catchLoc,!0);if(this.prev<i.finallyLoc)return n(i.finallyLoc)}else if(u){if(this.prev<i.catchLoc)return n(i.catchLoc,!0)}else{if(!c)throw new Error("try statement without catch or finally");if(this.prev<i.finallyLoc)return n(i.finallyLoc)}}}},abrupt:function(t,e){for(var n=this.tryEntries.length-1;n>=0;--n){var o=this.tryEntries[n];if(o.tryLoc<=this.prev&&r.call(o,"finallyLoc")&&this.prev<o.finallyLoc){var i=o;break}}i&&("break"===t||"continue"===t)&&i.tryLoc<=e&&e<=i.finallyLoc&&(i=null);var a=i?i.completion:{};return a.type=t,a.arg=e,i?(this.method="next",this.next=i.finallyLoc,d):this.complete(a)},complete:function(t,e){if("throw"===t.type)throw t.arg;return"break"===t.type||"continue"===t.type?this.next=t.arg:"return"===t.type?(this.rval=this.arg=t.arg,this.method="return",this.next="end"):"normal"===t.type&&e&&(this.next=e),d},finish:function(t){for(var e=this.tryEntries.length-1;e>=0;--e){var r=this.tryEntries[e];if(r.finallyLoc===t)return this.complete(r.completion,r.afterLoc),E(r),d}},catch:function(t){for(var e=this.tryEntries.length-1;e>=0;--e){var r=this.tryEntries[e];if(r.tryLoc===t){var n=r.completion;if("throw"===n.type){var o=n.arg;E(r)}return o}}throw new Error("illegal catch attempt")},delegateYield:function(t,e,r){return this.delegate={iterator:k(t),resultName:e,nextLoc:r},"next"===this.method&&(this.arg=void 0),d}},t}function u(t,e,r,n,o,i,a){try{var u=t[i](a),c=u.value}catch(t){return void r(t)}u.done?e(c):Promise.resolve(c).then(n,o)}function c(t){return function(){var e=this,r=arguments;return new Promise((function(n,o){var i=t.apply(e,r);function a(t){u(i,n,o,a,c,"next",t)}function c(t){u(i,n,o,a,c,"throw",t)}a(void 0)}))}}function l(t,e){return function(t){if(Array.isArray(t))return t}(t)||function(t,e){var r=null==t?null:"undefined"!=typeof Symbol&&t[Symbol.iterator]||t["@@iterator"];if(null!=r){var n,o,i,a,u=[],c=!0,l=!1;try{if(i=(r=r.call(t)).next,0===e){if(Object(r)!==r)return;c=!1}else for(;!(c=(n=i.call(r)).done)&&(u.push(n.value),u.length!==e);c=!0);}catch(t){l=!0,o=t}finally{try{if(!c&&null!=r.return&&(a=r.return(),Object(a)!==a))return}finally{if(l)throw o}}return u}}(t,e)||s(t,e)||function(){throw new TypeError("Invalid attempt to destructure non-iterable instance.\nIn order to be iterable, non-array objects must have a [Symbol.iterator]() method.")}()}function s(t,e){if(t){if("string"==typeof t)return f(t,e);var r=Object.prototype.toString.call(t).slice(8,-1);return"Object"===r&&t.constructor&&(r=t.constructor.name),"Map"===r||"Set"===r?Array.from(t):"Arguments"===r||/^(?:Ui|I)nt(?:8|16|32)(?:Clamped)?Array$/.test(r)?f(t,e):void 0}}function f(t,e){(null==e||e>t.length)&&(e=t.length);for(var r=0,n=new Array(e);r<e;r++)n[r]=t[r];return n}function p(t){return p="function"==typeof Symbol&&"symbol"==typeof Symbol.iterator?function(t){return typeof t}:function(t){return t&&"function"==typeof Symbol&&t.constructor===Symbol&&t!==Symbol.prototype?"symbol":typeof t},p(t)}function d(t,e){for(var r=0;r<e.length;r++){var n=e[r];n.enumerable=n.enumerable||!1,n.configurable=!0,"value"in n&&(n.writable=!0),Object.defineProperty(t,j(n.key),n)}}function y(t){var e=m();return function(){var r,n=b(t);if(e){var o=b(this).constructor;r=Reflect.construct(n,arguments,o)}else r=n.apply(this,arguments);return function(t,e){if(e&&("object"===p(e)||"function"==typeof e))return e;if(void 0!==e)throw new TypeError("Derived constructors may only return object or undefined");return function(t){if(void 0===t)throw new ReferenceError("this hasn't been initialised - super() hasn't been called");return t}(t)}(this,r)}}function h(t){var e="function"==typeof Map?new Map:void 0;return h=function(t){if(null===t||(r=t,-1===Function.toString.call(r).indexOf("[native code]")))return t;var r;if("function"!=typeof t)throw new TypeError("Super expression must either be null or a function");if(void 0!==e){if(e.has(t))return e.get(t);e.set(t,n)}function n(){return v(t,arguments,b(this).constructor)}return n.prototype=Object.create(t.prototype,{constructor:{value:n,enumerable:!1,writable:!0,configurable:!0}}),g(n,t)},h(t)}function v(t,e,r){return v=m()?Reflect.construct.bind():function(t,e,r){var n=[null];n.push.apply(n,e);var o=new(Function.bind.apply(t,n));return r&&g(o,r.prototype),o},v.apply(null,arguments)}function m(){if("undefined"==typeof Reflect||!Reflect.construct)return!1;if(Reflect.construct.sham)return!1;if("function"==typeof Proxy)return!0;try{return Boolean.prototype.valueOf.call(Reflect.construct(Boolean,[],(function(){}))),!0}catch(t){return!1}}function g(t,e){return g=Object.setPrototypeOf?Object.setPrototypeOf.bind():function(t,e){return t.__proto__=e,t},g(t,e)}function b(t){return b=Object.setPrototypeOf?Object.getPrototypeOf.bind():function(t){return t.__proto__||Object.getPrototypeOf(t)},b(t)}function w(t,e){var r=Object.keys(t);if(Object.getOwnPropertySymbols){var n=Object.getOwnPropertySymbols(t);e&&(n=n.filter((function(e){return Object.getOwnPropertyDescriptor(t,e).enumerable}))),r.push.apply(r,n)}return r}function O(t){for(var e=1;e<arguments.length;e++){var r=null!=arguments[e]?arguments[e]:{};e%2?w(Object(r),!0).forEach((function(e){S(t,e,r[e])})):Object.getOwnPropertyDescriptors?Object.defineProperties(t,Object.getOwnPropertyDescriptors(r)):w(Object(r)).forEach((function(e){Object.defineProperty(t,e,Object.getOwnPropertyDescriptor(r,e))}))}return t}function S(t,e,r){return(e=j(e))in t?Object.defineProperty(t,e,{value:r,enumerable:!0,configurable:!0,writable:!0}):t[e]=r,t}function j(t){var e=function(t,e){if("object"!==p(t)||null===t)return t;var r=t[Symbol.toPrimitive];if(void 0!==r){var n=r.call(t,e||"default");if("object"!==p(n))return n;throw new TypeError("@@toPrimitive must return a primitive value.")}return("string"===e?String:Number)(t)}(t,"string");return"symbol"===p(e)?e:String(e)}var _=wc.getSetting("stripeGeneralData"),x=_.publishableKey,E=_.stripeParams,P=wc.getSetting("stripeErrorMessages"),k=wc.getSetting("countryLocale",{}),A=/^([\w]+)\:(.+)$/,L=wc.getSetting("stripeGeneralData").routes,I={recipient:function(t,e){return t.first_name=e.split(" ").slice(0,-1).join(" "),t.last_name=e.split(" ").pop(),t},payerName:function(t,e){return t.first_name=e.split(" ").slice(0,-1).join(" "),t.last_name=e.split(" ").pop(),t},country:"country",addressLine:function(t,e){return e[0]&&(t.address_1=e[0]),e[1]&&(t.address_2=e[1]),t},line1:"address_1",line2:"address_2",city:"city",region:"state",state:"state",postalCode:"postcode",postal_code:"postcode",payerEmail:"email",payerPhone:"phone"},T=new Promise((function(t,e){(0,n.J)(x,E).then((function(e){t(e)})).catch((function(e){t({error:e})}))})),C=function(t){return null!=L&&L[t]?L[t]:console.log("".concat(t," is not a valid route"))},N=function(t){var e=arguments.length>1&&void 0!==arguments[1]?arguments[1]:{};return O({type:t.SUCCESS},e)},R=function(t,e){var r=arguments.length>2&&void 0!==arguments[2]?arguments[2]:{};return O({type:t.ERROR,message:D(e)},r)},D=function(t){return"string"==typeof t?t:null!=t&&t.code&&null!=P&&P[t.code]?P[t.code]:null!=t&&t.statusCode?null!=P&&P[t.statusCode]?P[t.statusCode]:t.statusMessage:t.message},M=function(t){var e={name:"".concat(t.first_name," ").concat(t.last_name),address:{city:t.city||null,country:t.country||null,line1:t.address_1||null,line2:t.address_2||null,postal_code:t.postcode||null,state:t.state||null}};return null!=t&&t.phone&&(e.phone=t.phone),null!=t&&t.email&&(e.email=t.email),e},q=function(t){return function(e){return e?wc.getSetting(t)[e]:wc.getSetting(t)}},B=function(t){!function(t,e){if("function"!=typeof e&&null!==e)throw new TypeError("Super expression must either be null or a function");t.prototype=Object.create(e&&e.prototype,{constructor:{value:t,writable:!0,configurable:!0}}),Object.defineProperty(t,"prototype",{writable:!1}),e&&g(t,e)}(i,t);var e,r,n,o=y(i);function i(t){var e;return function(t,e){if(!(t instanceof e))throw new TypeError("Cannot call a class as a function")}(this,i),(e=o.call(this,t.message)).error=t,e}return e=i,r&&d(e.prototype,r),n&&d(e,n),Object.defineProperty(e,"prototype",{writable:!1}),e}(h(Error)),G=function(t){var e=O({},k.default);return t&&null!=k&&k[t]&&(e=Object.entries(k[t]).reduce((function(t,e){var r=l(e,2),n=r[0],o=r[1];return t[n]=O(O({},t[n]),o),t}),e),["phone","shipping-phone","email"].forEach((function(t){var r=document.getElementById(t);r&&(e[t]={required:r.required})}))),e},F=function(t){var e=G(arguments.length>1&&void 0!==arguments[1]&&arguments[1]);return[t]in e&&e[t].required},U=function(t){var e=t.match(A);if(e){var r=e[1];return[e[2],r]}return[]},W=function(){var t=c(a().mark((function t(e){var r,n,i,u,c,l,s,f,p,d,y,h,v,m,g,b,w,j;return a().wrap((function(t){for(;;)switch(t.prev=t.next){case 0:if(r=e.redirectUrl,n=e.responseTypes,i=e.name,u=e.method,c=void 0===u?"handleCardAction":u,l=e.savePaymentMethod,s=void 0!==l&&l,f=e.data,p=void 0===f?{}:f,t.prev=1,!(d=r.match(/#response=(.+)/))){t.next=28;break}return y=JSON.parse(window.atob(decodeURIComponent(d[1]))),h=y.type,v=y.client_secret,m=y.order_id,g=y.order_key,t.next=7,T;case 7:if(b=t.sent,"intent"!==h){t.next=14;break}return t.next=11,b[c](v);case 11:w=t.sent,t.next=17;break;case 14:return t.next=16,b.confirmCardSetup(v);case 16:w=t.sent;case 17:if(!w.error){t.next=19;break}return t.abrupt("return",R(n,w.error));case 19:return p=O(O({},p),{},S({order_id:m,order_key:g},"".concat(i,"_save_source_key"),s)),t.next=22,(0,o.Z)({url:C("process/payment"),method:"POST",data:p});case 22:if(!(j=t.sent).messages){t.next=25;break}return t.abrupt("return",R(n,j.messages));case 25:return t.abrupt("return",N(n,{redirectUrl:j.redirect}));case 28:return t.abrupt("return",N(n));case 29:t.next=35;break;case 31:return t.prev=31,t.t0=t.catch(1),console.log(t.t0),t.abrupt("return",R(n,t.t0));case 35:case"end":return t.stop()}}),t,null,[[1,31]])})));return function(e){return t.apply(this,arguments)}}(),z=function(){var t=arguments.length>0&&void 0!==arguments[0]?arguments[0]:I;return function(e){var r=arguments.length>1&&void 0!==arguments[1]?arguments[1]:{},n={};e=O(O({},e),J(r));for(var o=0,i=Object.entries(t);o<i.length;o++){var a,u=l(i[o],2),c=u[0],s=u[1];null!==(a=e)&&void 0!==a&&a[c]&&("function"==typeof s?s(n,e[c]):n[s]=e[c])}return n}},J=function(t){return Object.keys(t).filter((function(e){return Boolean(t[e])})).reduce((function(e,r){return O(O({},e),{},S({},r,t[r]))}),{})},Z=function(t){var e=[];return t.forEach((function(t,r){t.shipping_rates.sort((function(t){return t.selected?-1:1}));var n=t.shipping_rates.map((function(t){var e=document.createElement("textarea");e.innerHTML=t.name;!function(t,e){var r,n=wc.getCurrency(e),o=n.prefix,i=n.suffix,a=n.decimalSeparator,u=n.minorUnit,c=n.thousandSeparator;if(""==t||void 0===t)return t;t="string"==typeof t?parseInt(t,10):t;var l="",s=(t=(t/=Math.pow(10,u)).toString().replace(".",a)).indexOf(a);s<0?u>0&&(t+="".concat(a).concat(new Array(u+1).join("0"))):(l=t.substr(s+1)).length<u&&(t+=new Array(u-l.length+1).join("0"));var f=t.match(new RegExp("(\\d+)\\".concat(a,"(\\d+)")));f&&(t=f[1],l=f[2]),t=t.replace(new RegExp("\\B(?=(\\d{3})+(?!\\d))","g"),"".concat(c)),t=(null===(r=l)||void 0===r?void 0:r.length)>0?t+a+l:t}(t.price,t.currency_code);return{id:$(r,t.rate_id),label:e.value,amount:parseInt(t.price,10)}}));e=[].concat(i(e),i(n))})),e},$=function(t,e){return"".concat(t,":").concat(e)},K=function(t,e){e.minorUnit;var r=[],n=["total_tax","total_shipping"];return t.forEach((function(t){(0<t.value||t.key&&n.includes(t.key))&&r.push({label:t.label,pending:!1,amount:t.value})})),r},Y={},H=function(t,e){var r=t.country,n=t.currency,o=t.total;return new Promise((function(t,i){var a=[r,n,o.amount].reduce((function(t,e){return"".concat(t,"-").concat(e)}));return n?a in Y?t(Y[a]):T.then((function(u){if(u.error)return i(u.error);u.paymentRequest({country:r,currency:n,total:o}).canMakePayment().then((function(r){return Y[a]=e(r),t(Y[a])}))})).catch(i):t(!1)}))},V=function(t){return"".concat("stripe:").concat(t)},X=function(t,e){var r=Math.floor((new Date).getTime()/1e3)+900;"sessionStorage"in window&&sessionStorage.setItem(V(t),JSON.stringify({value:e,exp:r}))},Q=function(t){if("sessionStorage"in window)try{var e=JSON.parse(sessionStorage.getItem(V(t)));if(e){var r=e.value,n=e.exp;if(!(Math.floor((new Date).getTime()/1e3)>n))return r;tt(V(t))}}catch(t){}return null},tt=function(t){"sessionStorage"in window&&sessionStorage.removeItem(V(t))},et={first_name:"",last_name:"",company:"",address_1:"",address_2:"",city:"",state:"",postcode:"",country:"",phone:""},rt=O(O({},et),{},{email:""})}}]);
//# sourceMappingURL=348.js.map