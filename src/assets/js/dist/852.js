(self.webpackChunksimple_secure_stripe=self.webpackChunksimple_secure_stripe||[]).push([[852],{5260:function(e,t,n){!function(e,t){"use strict";function n(e,t){var n=Object.keys(e);if(Object.getOwnPropertySymbols){var r=Object.getOwnPropertySymbols(e);t&&(r=r.filter((function(t){return Object.getOwnPropertyDescriptor(e,t).enumerable}))),n.push.apply(n,r)}return n}function r(e){for(var t=1;t<arguments.length;t++){var r=null!=arguments[t]?arguments[t]:{};t%2?n(Object(r),!0).forEach((function(t){c(e,t,r[t])})):Object.getOwnPropertyDescriptors?Object.defineProperties(e,Object.getOwnPropertyDescriptors(r)):n(Object(r)).forEach((function(t){Object.defineProperty(e,t,Object.getOwnPropertyDescriptor(r,t))}))}return e}function o(e){return o="function"==typeof Symbol&&"symbol"==typeof Symbol.iterator?function(e){return typeof e}:function(e){return e&&"function"==typeof Symbol&&e.constructor===Symbol&&e!==Symbol.prototype?"symbol":typeof e},o(e)}function c(e,t,n){return t in e?Object.defineProperty(e,t,{value:n,enumerable:!0,configurable:!0,writable:!0}):e[t]=n,e}function a(e,t){return u(e)||i(e,t)||s(e,t)||f()}function u(e){if(Array.isArray(e))return e}function i(e,t){var n=e&&("undefined"!=typeof Symbol&&e[Symbol.iterator]||e["@@iterator"]);if(null!=n){var r,o,c=[],a=!0,u=!1;try{for(n=n.call(e);!(a=(r=n.next()).done)&&(c.push(r.value),!t||c.length!==t);a=!0);}catch(e){u=!0,o=e}finally{try{a||null==n.return||n.return()}finally{if(u)throw o}}return c}}function s(e,t){if(e){if("string"==typeof e)return l(e,t);var n=Object.prototype.toString.call(e).slice(8,-1);return"Object"===n&&e.constructor&&(n=e.constructor.name),"Map"===n||"Set"===n?Array.from(e):"Arguments"===n||/^(?:Ui|I)nt(?:8|16|32)(?:Clamped)?Array$/.test(n)?l(e,t):void 0}}function l(e,t){(null==t||t>e.length)&&(t=e.length);for(var n=0,r=new Array(t);n<t;n++)r[n]=e[n];return r}function f(){throw new TypeError("Invalid attempt to destructure non-iterable instance.\nIn order to be iterable, non-array objects must have a [Symbol.iterator]() method.")}function p(e,t){return e(t={exports:{}},t.exports),t.exports}t=t&&Object.prototype.hasOwnProperty.call(t,"default")?t.default:t;var m="SECRET_DO_NOT_PASS_THIS_OR_YOU_WILL_BE_FIRED";function d(){}function y(){}y.resetWarningCache=d;var h=function(){function e(e,t,n,r,o,c){if(c!==m){var a=new Error("Calling PropTypes validators directly is not supported by the `prop-types` package. Use PropTypes.checkPropTypes() to call them. Read more at http://fb.me/use-check-prop-types");throw a.name="Invariant Violation",a}}function t(){return e}e.isRequired=e;var n={array:e,bool:e,func:e,number:e,object:e,string:e,symbol:e,any:e,arrayOf:t,element:e,elementType:e,instanceOf:t,node:e,objectOf:t,oneOf:t,oneOfType:t,shape:t,exact:t,checkPropTypes:y,resetWarningCache:d};return n.PropTypes=n,n},g=p((function(e){e.exports=h()})),E=function(e){var n=t.useRef(e);return t.useEffect((function(){n.current=e}),[e]),n.current},v=function(e){return null!==e&&"object"===o(e)},b=function(e){return v(e)&&"function"==typeof e.then},C=function(e){return v(e)&&"function"==typeof e.elements&&"function"==typeof e.createToken&&"function"==typeof e.createPaymentMethod&&"function"==typeof e.confirmCardPayment},w="[object Object]",k=function e(t,n){if(!v(t)||!v(n))return t===n;var r=Array.isArray(t);if(r!==Array.isArray(n))return!1;var o=Object.prototype.toString.call(t)===w;if(o!==(Object.prototype.toString.call(n)===w))return!1;if(!o&&!r)return t===n;var c=Object.keys(t),a=Object.keys(n);if(c.length!==a.length)return!1;for(var u={},i=0;i<c.length;i+=1)u[c[i]]=!0;for(var s=0;s<a.length;s+=1)u[a[s]]=!0;var l=Object.keys(u);if(l.length!==c.length)return!1;var f=t,p=n,m=function(t){return e(f[t],p[t])};return l.every(m)},O=function(e,t,n){return v(e)?Object.keys(e).reduce((function(o,a){var u=!v(t)||!k(e[a],t[a]);return n.includes(a)?(u&&console.warn("Unsupported prop change: options.".concat(a," is not a mutable property.")),o):u?r(r({},o||{}),{},c({},a,e[a])):o}),null):null},S="Invalid prop `stripe` supplied to `Elements`. We recommend using the `loadStripe` utility from `@stripe/stripe-js`. See https://stripe.com/docs/stripe-js/react#elements-props-stripe for details.",j=function(e){if(null===e||C(e))return e;throw new Error(S)},P=function(e){if(b(e))return{tag:"async",stripePromise:Promise.resolve(e).then(j)};var t=j(e);return null===t?{tag:"empty"}:{tag:"sync",stripe:t}},A=t.createContext(null);A.displayName="ElementsContext";var _=function(e,t){if(!e)throw new Error("Could not find Elements context; You need to wrap the part of your app that ".concat(t," in an <Elements> provider."));return e},x=t.createContext(null);x.displayName="CartElementContext";var R=function(e,t){if(!e)throw new Error("Could not find Elements context; You need to wrap the part of your app that ".concat(t," in an <Elements> provider."));return e},M=function(e){var n=e.stripe,r=e.options,o=e.children,c=t.useMemo((function(){return P(n)}),[n]),u=a(t.useState(null),2),i=u[0],s=u[1],l=a(t.useState(null),2),f=l[0],p=l[1],m=a(t.useState((function(){return{stripe:"sync"===c.tag?c.stripe:null,elements:"sync"===c.tag?c.stripe.elements(r):null}})),2),d=m[0],y=m[1];t.useEffect((function(){var e=!0,t=function(e){y((function(t){return t.stripe?t:{stripe:e,elements:e.elements(r)}}))};return"async"!==c.tag||d.stripe?"sync"!==c.tag||d.stripe||t(c.stripe):c.stripePromise.then((function(n){n&&e&&t(n)})),function(){e=!1}}),[c,d,r]);var h=E(n);t.useEffect((function(){null!==h&&h!==n&&console.warn("Unsupported prop change on Elements: You cannot change the `stripe` prop after setting it.")}),[h,n]);var g=E(r);return t.useEffect((function(){if(d.elements){var e=O(r,g,["clientSecret","fonts"]);e&&d.elements.update(e)}}),[r,g,d.elements]),t.useEffect((function(){var e=d.stripe;e&&e._registerWrapper&&e.registerAppInfo&&(e._registerWrapper({name:"react-stripe-js",version:"2.1.1"}),e.registerAppInfo({name:"react-stripe-js",version:"2.1.1",url:"https://stripe.com/docs/stripe-js/react"}))}),[d.stripe]),t.createElement(A.Provider,{value:d},t.createElement(x.Provider,{value:{cart:i,setCart:s,cartState:f,setCartState:p}},o))};M.propTypes={stripe:g.any,options:g.object};var T=function(e){var n=t.useContext(A);return _(n,e)},N=function(e){var n=t.useContext(x);return R(n,e)},B=function(){return T("calls useElements()").elements},I=function(){return T("calls useStripe()").stripe},L=function(){return N("calls useCartElement()").cart},Z=function(){return N("calls useCartElementState()").cartState},U=function(e){return(0,e.children)(T("mounts <ElementsConsumer>"))};U.propTypes={children:g.func.isRequired};var q=function(e,n,r){var o=!!r,c=t.useRef(r);t.useEffect((function(){c.current=r}),[r]),t.useEffect((function(){if(!o||!e)return function(){};var t=function(){c.current&&c.current.apply(c,arguments)};return e.on(n,t),function(){e.off(n,t)}}),[o,n,e,c])},D=function(e){return e.charAt(0).toUpperCase()+e.slice(1)},W=function(e,n){var r="".concat(D(e),"Element"),o=n?function(e){T("mounts <".concat(r,">")),N("mounts <".concat(r,">"));var n=e.id,o=e.className;return t.createElement("div",{id:n,className:o})}:function(n){var o,c=n.id,u=n.className,i=n.options,s=void 0===i?{}:i,l=n.onBlur,f=n.onFocus,p=n.onReady,m=n.onChange,d=n.onEscape,y=n.onClick,h=n.onLoadError,g=n.onLoaderStart,v=n.onNetworksChange,b=n.onCheckout,C=n.onLineItemClick,w=n.onConfirm,k=n.onCancel,S=n.onShippingAddressChange,j=n.onShippingRateChange,P=T("mounts <".concat(r,">")).elements,A=a(t.useState(null),2),_=A[0],x=A[1],R=t.useRef(null),M=t.useRef(null),B=N("mounts <".concat(r,">")),I=B.setCart,L=B.setCartState;q(_,"blur",l),q(_,"focus",f),q(_,"escape",d),q(_,"click",y),q(_,"loaderror",h),q(_,"loaderstart",g),q(_,"networkschange",v),q(_,"lineitemclick",C),q(_,"confirm",w),q(_,"cancel",k),q(_,"shippingaddresschange",S),q(_,"shippingratechange",j),"cart"===e?o=function(e){L(e),p&&p(e)}:p&&(o="expressCheckout"===e?p:function(){p(_)}),q(_,"ready",o),q(_,"change","cart"===e?function(e){L(e),m&&m(e)}:m),q(_,"checkout","cart"===e?function(e){L(e),b&&b(e)}:b),t.useLayoutEffect((function(){if(null===R.current&&P&&null!==M.current){var t=P.create(e,s);"cart"===e&&I&&I(t),R.current=t,x(t),t.mount(M.current)}}),[P,s,I]);var Z=E(s);return t.useEffect((function(){if(R.current){var e=O(s,Z,["paymentRequest"]);e&&R.current.update(e)}}),[s,Z]),t.useLayoutEffect((function(){return function(){if(R.current&&"function"==typeof R.current.destroy)try{R.current.destroy(),R.current=null}catch(e){}}}),[]),t.createElement("div",{id:c,className:u,ref:M})};return o.propTypes={id:g.string,className:g.string,onChange:g.func,onBlur:g.func,onFocus:g.func,onReady:g.func,onEscape:g.func,onClick:g.func,onLoadError:g.func,onLoaderStart:g.func,onNetworksChange:g.func,onCheckout:g.func,onLineItemClick:g.func,onConfirm:g.func,onCancel:g.func,onShippingAddressChange:g.func,onShippingRateChange:g.func,options:g.object},o.displayName=r,o.__elementType=e,o},F="undefined"==typeof window,Y=W("auBankAccount",F),H=W("card",F),J=W("cardNumber",F),V=W("cardExpiry",F),$=W("cardCvc",F),z=W("fpxBank",F),G=W("iban",F),K=W("idealBank",F),Q=W("p24Bank",F),X=W("epsBank",F),ee=W("payment",F),te=W("expressCheckout",F),ne=W("paymentRequestButton",F),re=W("linkAuthentication",F),oe=W("address",F),ce=W("shippingAddress",F),ae=W("cart",F),ue=W("paymentMethodMessaging",F),ie=W("affirmMessage",F),se=W("afterpayClearpayMessage",F);e.AddressElement=oe,e.AffirmMessageElement=ie,e.AfterpayClearpayMessageElement=se,e.AuBankAccountElement=Y,e.CardCvcElement=$,e.CardElement=H,e.CardExpiryElement=V,e.CardNumberElement=J,e.CartElement=ae,e.Elements=M,e.ElementsConsumer=U,e.EpsBankElement=X,e.ExpressCheckoutElement=te,e.FpxBankElement=z,e.IbanElement=G,e.IdealBankElement=K,e.LinkAuthenticationElement=re,e.P24BankElement=Q,e.PaymentElement=ee,e.PaymentMethodMessagingElement=ue,e.PaymentRequestButtonElement=ne,e.ShippingAddressElement=ce,e.useCartElement=L,e.useCartElementState=Z,e.useElements=B,e.useStripe=I,Object.defineProperty(e,"__esModule",{value:!0})}(t,n(959))},6189:(e,t,n)=>{"use strict";n.d(t,{Z:()=>b});var r=n(5053),o=n(4359),c=n(3656),a=n(208),u=n(6172),i=n(1683),s=n(697),l=n(6977),f=n(5338),p=n(7847),m=n(3642);const d={Accept:"application/json, */*;q=0.1"},y={credentials:"include"},h=[l.Z,i.Z,s.Z,u.Z];const g=e=>{if(e.status>=200&&e.status<300)return e;throw e};let E=e=>{const{url:t,path:n,data:o,parse:c=!0,...a}=e;let{body:u,headers:i}=e;i={...d,...i},o&&(u=JSON.stringify(o),i["Content-Type"]="application/json");return window.fetch(t||n||window.location.href,{...y,...a,body:u,headers:i}).then((e=>Promise.resolve(e).then(g).catch((e=>(0,m.N)(e,c))).then((e=>(0,m.O)(e,c)))),(e=>{if(e&&"AbortError"===e.name)throw e;throw{code:"fetch_error",message:(0,r.__)("You are probably offline.")}}))};function v(e){return h.reduceRight(((e,t)=>n=>t(n,e)),E)(e).catch((t=>"rest_cookie_invalid_nonce"!==t.code?Promise.reject(t):window.fetch(v.nonceEndpoint).then(g).then((e=>e.text())).then((t=>(v.nonceMiddleware.nonce=t,v(e))))))}v.use=function(e){h.unshift(e)},v.setFetchHandler=function(e){E=e},v.createNonceMiddleware=o.Z,v.createPreloadingMiddleware=a.Z,v.createRootURLMiddleware=c.Z,v.fetchAllMiddleware=u.Z,v.mediaUploadMiddleware=f.Z,v.createThemePreviewMiddleware=p.Z;const b=v}}]);
//# sourceMappingURL=852.js.map