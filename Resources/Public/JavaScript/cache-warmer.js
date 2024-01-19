import t from"@typo3/backend/action-button/immediate-action.js";import e from"@typo3/backend/notification.js";import s from"@typo3/core/ajax/ajax-request.js";import r from"@typo3/backend/modal.js";import{LitElement as i,html as o}from"lit";import n from"@typo3/core/event/regular-event.js";function a(t,e,s,r){var i,o=arguments.length,n=o<3?e:null===r?r=Object.getOwnPropertyDescriptor(e,s):r;if("object"==typeof Reflect&&"function"==typeof Reflect.decorate)n=Reflect.decorate(t,e,s,r);else for(var a=t.length-1;a>=0;a--)(i=t[a])&&(n=(o<3?i(n):o>3?i(e,s,n):i(e,s))||n);return o>3&&n&&Object.defineProperty(e,s,n),n}"function"==typeof SuppressedError&&SuppressedError;const l=globalThis,c=l.trustedTypes,d=c?c.createPolicy("lit-html",{createHTML:t=>t}):void 0,h="$lit$",u=`lit$${(Math.random()+"").slice(9)}$`,p="?"+u,g=`<${p}>`,m=document,f=()=>m.createComment(""),$=t=>null===t||"object"!=typeof t&&"function"!=typeof t,y=Array.isArray,b="[ \t\n\f\r]",v=/<(?:(!--|\/[^a-zA-Z])|(\/?[a-zA-Z][^>\s]*)|(\/?$))/g,w=/-->/g,_=/>/g,A=RegExp(`>|${b}(?:([^\\s"'>=/]+)(${b}*=${b}*(?:[^ \t\n\f\r"'\`<>=]|("|')|))|$)`,"g"),S=/'/g,P=/"/g,E=/^(?:script|style|textarea|title)$/i,x=Symbol.for("lit-noChange"),O=Symbol.for("lit-nothing"),U=new WeakMap,R=m.createTreeWalker(m,129);function N(t,e){if(!Array.isArray(t)||!t.hasOwnProperty("raw"))throw Error("invalid template strings array");return void 0!==d?d.createHTML(e):e}const T=(t,e)=>{const s=t.length-1,r=[];let i,o=2===e?"<svg>":"",n=v;for(let e=0;e<s;e++){const s=t[e];let a,l,c=-1,d=0;for(;d<s.length&&(n.lastIndex=d,l=n.exec(s),null!==l);)d=n.lastIndex,n===v?"!--"===l[1]?n=w:void 0!==l[1]?n=_:void 0!==l[2]?(E.test(l[2])&&(i=RegExp("</"+l[2],"g")),n=A):void 0!==l[3]&&(n=A):n===A?">"===l[0]?(n=i??v,c=-1):void 0===l[1]?c=-2:(c=n.lastIndex-l[2].length,a=l[1],n=void 0===l[3]?A:'"'===l[3]?P:S):n===P||n===S?n=A:n===w||n===_?n=v:(n=A,i=void 0);const p=n===A&&t[e+1].startsWith("/>")?" ":"";o+=n===v?s+g:c>=0?(r.push(a),s.slice(0,c)+h+s.slice(c)+u+p):s+u+(-2===c?e:p)}return[N(t,o+(t[s]||"<?>")+(2===e?"</svg>":"")),r]};class C{constructor({strings:t,_$litType$:e},s){let r;this.parts=[];let i=0,o=0;const n=t.length-1,a=this.parts,[l,d]=T(t,e);if(this.el=C.createElement(l,s),R.currentNode=this.el.content,2===e){const t=this.el.content.firstChild;t.replaceWith(...t.childNodes)}for(;null!==(r=R.nextNode())&&a.length<n;){if(1===r.nodeType){if(r.hasAttributes())for(const t of r.getAttributeNames())if(t.endsWith(h)){const e=d[o++],s=r.getAttribute(t).split(u),n=/([.?@])?(.*)/.exec(e);a.push({type:1,index:i,name:n[2],strings:s,ctor:"."===n[1]?q:"?"===n[1]?I:"@"===n[1]?Y:H}),r.removeAttribute(t)}else t.startsWith(u)&&(a.push({type:6,index:i}),r.removeAttribute(t));if(E.test(r.tagName)){const t=r.textContent.split(u),e=t.length-1;if(e>0){r.textContent=c?c.emptyScript:"";for(let s=0;s<e;s++)r.append(t[s],f()),R.nextNode(),a.push({type:2,index:++i});r.append(t[e],f())}}}else if(8===r.nodeType)if(r.data===p)a.push({type:2,index:i});else{let t=-1;for(;-1!==(t=r.data.indexOf(u,t+1));)a.push({type:7,index:i}),t+=u.length-1}i++}}static createElement(t,e){const s=m.createElement("template");return s.innerHTML=t,s}}function M(t,e,s=t,r){if(e===x)return e;let i=void 0!==r?s._$Co?.[r]:s._$Cl;const o=$(e)?void 0:e._$litDirective$;return i?.constructor!==o&&(i?._$AO?.(!1),void 0===o?i=void 0:(i=new o(t),i._$AT(t,s,r)),void 0!==r?(s._$Co??=[])[r]=i:s._$Cl=i),void 0!==i&&(e=M(t,i._$AS(t,e.values),i,r)),e}let k=class{constructor(t,e){this._$AV=[],this._$AN=void 0,this._$AD=t,this._$AM=e}get parentNode(){return this._$AM.parentNode}get _$AU(){return this._$AM._$AU}u(t){const{el:{content:e},parts:s}=this._$AD,r=(t?.creationScope??m).importNode(e,!0);R.currentNode=r;let i=R.nextNode(),o=0,n=0,a=s[0];for(;void 0!==a;){if(o===a.index){let e;2===a.type?e=new j(i,i.nextSibling,this,t):1===a.type?e=new a.ctor(i,a.name,a.strings,this,t):6===a.type&&(e=new L(i,this,t)),this._$AV.push(e),a=s[++n]}o!==a?.index&&(i=R.nextNode(),o++)}return R.currentNode=m,r}p(t){let e=0;for(const s of this._$AV)void 0!==s&&(void 0!==s.strings?(s._$AI(t,s,e),e+=s.strings.length-2):s._$AI(t[e])),e++}};class j{get _$AU(){return this._$AM?._$AU??this._$Cv}constructor(t,e,s,r){this.type=2,this._$AH=O,this._$AN=void 0,this._$AA=t,this._$AB=e,this._$AM=s,this.options=r,this._$Cv=r?.isConnected??!0}get parentNode(){let t=this._$AA.parentNode;const e=this._$AM;return void 0!==e&&11===t?.nodeType&&(t=e.parentNode),t}get startNode(){return this._$AA}get endNode(){return this._$AB}_$AI(t,e=this){t=M(this,t,e),$(t)?t===O||null==t||""===t?(this._$AH!==O&&this._$AR(),this._$AH=O):t!==this._$AH&&t!==x&&this._(t):void 0!==t._$litType$?this.g(t):void 0!==t.nodeType?this.$(t):(t=>y(t)||"function"==typeof t?.[Symbol.iterator])(t)?this.T(t):this._(t)}k(t){return this._$AA.parentNode.insertBefore(t,this._$AB)}$(t){this._$AH!==t&&(this._$AR(),this._$AH=this.k(t))}_(t){this._$AH!==O&&$(this._$AH)?this._$AA.nextSibling.data=t:this.$(m.createTextNode(t)),this._$AH=t}g(t){const{values:e,_$litType$:s}=t,r="number"==typeof s?this._$AC(t):(void 0===s.el&&(s.el=C.createElement(N(s.h,s.h[0]),this.options)),s);if(this._$AH?._$AD===r)this._$AH.p(e);else{const t=new k(r,this),s=t.u(this.options);t.p(e),this.$(s),this._$AH=t}}_$AC(t){let e=U.get(t.strings);return void 0===e&&U.set(t.strings,e=new C(t)),e}T(t){y(this._$AH)||(this._$AH=[],this._$AR());const e=this._$AH;let s,r=0;for(const i of t)r===e.length?e.push(s=new j(this.k(f()),this.k(f()),this,this.options)):s=e[r],s._$AI(i),r++;r<e.length&&(this._$AR(s&&s._$AB.nextSibling,r),e.length=r)}_$AR(t=this._$AA.nextSibling,e){for(this._$AP?.(!1,!0,e);t&&t!==this._$AB;){const e=t.nextSibling;t.remove(),t=e}}setConnected(t){void 0===this._$AM&&(this._$Cv=t,this._$AP?.(t))}}class H{get tagName(){return this.element.tagName}get _$AU(){return this._$AM._$AU}constructor(t,e,s,r,i){this.type=1,this._$AH=O,this._$AN=void 0,this.element=t,this.name=e,this._$AM=r,this.options=i,s.length>2||""!==s[0]||""!==s[1]?(this._$AH=Array(s.length-1).fill(new String),this.strings=s):this._$AH=O}_$AI(t,e=this,s,r){const i=this.strings;let o=!1;if(void 0===i)t=M(this,t,e,0),o=!$(t)||t!==this._$AH&&t!==x,o&&(this._$AH=t);else{const r=t;let n,a;for(t=i[0],n=0;n<i.length-1;n++)a=M(this,r[s+n],e,n),a===x&&(a=this._$AH[n]),o||=!$(a)||a!==this._$AH[n],a===O?t=O:t!==O&&(t+=(a??"")+i[n+1]),this._$AH[n]=a}o&&!r&&this.O(t)}O(t){t===O?this.element.removeAttribute(this.name):this.element.setAttribute(this.name,t??"")}}class q extends H{constructor(){super(...arguments),this.type=3}O(t){this.element[this.name]=t===O?void 0:t}}class I extends H{constructor(){super(...arguments),this.type=4}O(t){this.element.toggleAttribute(this.name,!!t&&t!==O)}}class Y extends H{constructor(t,e,s,r,i){super(t,e,s,r,i),this.type=5}_$AI(t,e=this){if((t=M(this,t,e,0)??O)===x)return;const s=this._$AH,r=t===O&&s!==O||t.capture!==s.capture||t.once!==s.once||t.passive!==s.passive,i=t!==O&&(s===O||r);r&&this.element.removeEventListener(this.name,this,s),i&&this.element.addEventListener(this.name,this,t),this._$AH=t}handleEvent(t){"function"==typeof this._$AH?this._$AH.call(this.options?.host??this.element,t):this._$AH.handleEvent(t)}}class L{constructor(t,e,s){this.element=t,this.type=6,this._$AN=void 0,this._$AM=e,this.options=s}get _$AU(){return this._$AM._$AU}_$AI(t){M(this,t)}}const B=l.litHtmlPolyfillSupport;B?.(C,j),(l.litHtmlVersions??=[]).push("3.1.1");const F=1,W=2,z=t=>(...e)=>({_$litDirective$:t,values:e});let D=class{constructor(t){}get _$AU(){return this._$AM._$AU}_$AT(t,e,s){this._$Ct=t,this._$AM=e,this._$Ci=s}_$AS(t,e){return this.update(t,e)}update(t,e){return this.render(...e)}};const V=z(class extends D{constructor(t){if(super(t),t.type!==F||"class"!==t.name||t.strings?.length>2)throw Error("`classMap()` can only be used in the `class` attribute and must be the only part in the attribute.")}render(t){return" "+Object.keys(t).filter((e=>t[e])).join(" ")+" "}update(t,[e]){if(void 0===this.it){this.it=new Set,void 0!==t.strings&&(this.st=new Set(t.strings.join(" ").split(/\s/).filter((t=>""!==t))));for(const t in e)e[t]&&!this.st?.has(t)&&this.it.add(t);return this.render(e)}const s=t.element.classList;for(const t of this.it)t in e||(s.remove(t),this.it.delete(t));for(const t in e){const r=!!e[t];r===this.it.has(t)||this.st?.has(t)||(r?(s.add(t),this.it.add(t)):(s.remove(t),this.it.delete(t)))}return x}}),J=t=>(e,s)=>{void 0!==s?s.addInitializer((()=>{customElements.define(t,e)})):customElements.define(t,e)},Q=globalThis,Z=Q.ShadowRoot&&(void 0===Q.ShadyCSS||Q.ShadyCSS.nativeShadow)&&"adoptedStyleSheets"in Document.prototype&&"replace"in CSSStyleSheet.prototype,K=Symbol(),G=new WeakMap;let X=class{constructor(t,e,s){if(this._$cssResult$=!0,s!==K)throw Error("CSSResult is not constructable. Use `unsafeCSS` or `css` instead.");this.cssText=t,this.t=e}get styleSheet(){let t=this.o;const e=this.t;if(Z&&void 0===t){const s=void 0!==e&&1===e.length;s&&(t=G.get(e)),void 0===t&&((this.o=t=new CSSStyleSheet).replaceSync(this.cssText),s&&G.set(e,t))}return t}toString(){return this.cssText}};const tt=(t,e)=>{if(Z)t.adoptedStyleSheets=e.map((t=>t instanceof CSSStyleSheet?t:t.styleSheet));else for(const s of e){const e=document.createElement("style"),r=Q.litNonce;void 0!==r&&e.setAttribute("nonce",r),e.textContent=s.cssText,t.appendChild(e)}},et=Z?t=>t:t=>t instanceof CSSStyleSheet?(t=>{let e="";for(const s of t.cssRules)e+=s.cssText;return(t=>new X("string"==typeof t?t:t+"",void 0,K))(e)})(t):t,{is:st,defineProperty:rt,getOwnPropertyDescriptor:it,getOwnPropertyNames:ot,getOwnPropertySymbols:nt,getPrototypeOf:at}=Object,lt=globalThis,ct=lt.trustedTypes,dt=ct?ct.emptyScript:"",ht=lt.reactiveElementPolyfillSupport,ut=(t,e)=>t,pt={toAttribute(t,e){switch(e){case Boolean:t=t?dt:null;break;case Object:case Array:t=null==t?t:JSON.stringify(t)}return t},fromAttribute(t,e){let s=t;switch(e){case Boolean:s=null!==t;break;case Number:s=null===t?null:Number(t);break;case Object:case Array:try{s=JSON.parse(t)}catch(t){s=null}}return s}},gt=(t,e)=>!st(t,e),mt={attribute:!0,type:String,converter:pt,reflect:!1,hasChanged:gt};Symbol.metadata??=Symbol("metadata"),lt.litPropertyMetadata??=new WeakMap;class ft extends HTMLElement{static addInitializer(t){this._$Ei(),(this.l??=[]).push(t)}static get observedAttributes(){return this.finalize(),this._$Eh&&[...this._$Eh.keys()]}static createProperty(t,e=mt){if(e.state&&(e.attribute=!1),this._$Ei(),this.elementProperties.set(t,e),!e.noAccessor){const s=Symbol(),r=this.getPropertyDescriptor(t,s,e);void 0!==r&&rt(this.prototype,t,r)}}static getPropertyDescriptor(t,e,s){const{get:r,set:i}=it(this.prototype,t)??{get(){return this[e]},set(t){this[e]=t}};return{get(){return r?.call(this)},set(e){const o=r?.call(this);i.call(this,e),this.requestUpdate(t,o,s)},configurable:!0,enumerable:!0}}static getPropertyOptions(t){return this.elementProperties.get(t)??mt}static _$Ei(){if(this.hasOwnProperty(ut("elementProperties")))return;const t=at(this);t.finalize(),void 0!==t.l&&(this.l=[...t.l]),this.elementProperties=new Map(t.elementProperties)}static finalize(){if(this.hasOwnProperty(ut("finalized")))return;if(this.finalized=!0,this._$Ei(),this.hasOwnProperty(ut("properties"))){const t=this.properties,e=[...ot(t),...nt(t)];for(const s of e)this.createProperty(s,t[s])}const t=this[Symbol.metadata];if(null!==t){const e=litPropertyMetadata.get(t);if(void 0!==e)for(const[t,s]of e)this.elementProperties.set(t,s)}this._$Eh=new Map;for(const[t,e]of this.elementProperties){const s=this._$Eu(t,e);void 0!==s&&this._$Eh.set(s,t)}this.elementStyles=this.finalizeStyles(this.styles)}static finalizeStyles(t){const e=[];if(Array.isArray(t)){const s=new Set(t.flat(1/0).reverse());for(const t of s)e.unshift(et(t))}else void 0!==t&&e.push(et(t));return e}static _$Eu(t,e){const s=e.attribute;return!1===s?void 0:"string"==typeof s?s:"string"==typeof t?t.toLowerCase():void 0}constructor(){super(),this._$Ep=void 0,this.isUpdatePending=!1,this.hasUpdated=!1,this._$Em=null,this._$Ev()}_$Ev(){this._$Eg=new Promise((t=>this.enableUpdating=t)),this._$AL=new Map,this._$ES(),this.requestUpdate(),this.constructor.l?.forEach((t=>t(this)))}addController(t){(this._$E_??=new Set).add(t),void 0!==this.renderRoot&&this.isConnected&&t.hostConnected?.()}removeController(t){this._$E_?.delete(t)}_$ES(){const t=new Map,e=this.constructor.elementProperties;for(const s of e.keys())this.hasOwnProperty(s)&&(t.set(s,this[s]),delete this[s]);t.size>0&&(this._$Ep=t)}createRenderRoot(){const t=this.shadowRoot??this.attachShadow(this.constructor.shadowRootOptions);return tt(t,this.constructor.elementStyles),t}connectedCallback(){this.renderRoot??=this.createRenderRoot(),this.enableUpdating(!0),this._$E_?.forEach((t=>t.hostConnected?.()))}enableUpdating(t){}disconnectedCallback(){this._$E_?.forEach((t=>t.hostDisconnected?.()))}attributeChangedCallback(t,e,s){this._$AK(t,s)}_$EO(t,e){const s=this.constructor.elementProperties.get(t),r=this.constructor._$Eu(t,s);if(void 0!==r&&!0===s.reflect){const i=(void 0!==s.converter?.toAttribute?s.converter:pt).toAttribute(e,s.type);this._$Em=t,null==i?this.removeAttribute(r):this.setAttribute(r,i),this._$Em=null}}_$AK(t,e){const s=this.constructor,r=s._$Eh.get(t);if(void 0!==r&&this._$Em!==r){const t=s.getPropertyOptions(r),i="function"==typeof t.converter?{fromAttribute:t.converter}:void 0!==t.converter?.fromAttribute?t.converter:pt;this._$Em=r,this[r]=i.fromAttribute(e,t.type),this._$Em=null}}requestUpdate(t,e,s){if(void 0!==t){if(s??=this.constructor.getPropertyOptions(t),!(s.hasChanged??gt)(this[t],e))return;this.C(t,e,s)}!1===this.isUpdatePending&&(this._$Eg=this._$EP())}C(t,e,s){this._$AL.has(t)||this._$AL.set(t,e),!0===s.reflect&&this._$Em!==t&&(this._$ET??=new Set).add(t)}async _$EP(){this.isUpdatePending=!0;try{await this._$Eg}catch(t){Promise.reject(t)}const t=this.scheduleUpdate();return null!=t&&await t,!this.isUpdatePending}scheduleUpdate(){return this.performUpdate()}performUpdate(){if(!this.isUpdatePending)return;if(!this.hasUpdated){if(this.renderRoot??=this.createRenderRoot(),this._$Ep){for(const[t,e]of this._$Ep)this[t]=e;this._$Ep=void 0}const t=this.constructor.elementProperties;if(t.size>0)for(const[e,s]of t)!0!==s.wrapped||this._$AL.has(e)||void 0===this[e]||this.C(e,this[e],s)}let t=!1;const e=this._$AL;try{t=this.shouldUpdate(e),t?(this.willUpdate(e),this._$E_?.forEach((t=>t.hostUpdate?.())),this.update(e)):this._$Ej()}catch(e){throw t=!1,this._$Ej(),e}t&&this._$AE(e)}willUpdate(t){}_$AE(t){this._$E_?.forEach((t=>t.hostUpdated?.())),this.hasUpdated||(this.hasUpdated=!0,this.firstUpdated(t)),this.updated(t)}_$Ej(){this._$AL=new Map,this.isUpdatePending=!1}get updateComplete(){return this.getUpdateComplete()}getUpdateComplete(){return this._$Eg}shouldUpdate(t){return!0}update(t){this._$ET&&=this._$ET.forEach((t=>this._$EO(t,this[t]))),this._$Ej()}updated(t){}firstUpdated(t){}}ft.elementStyles=[],ft.shadowRootOptions={mode:"open"},ft[ut("elementProperties")]=new Map,ft[ut("finalized")]=new Map,ht?.({ReactiveElement:ft}),(lt.reactiveElementVersions??=[]).push("2.0.3");const $t={attribute:!0,type:String,converter:pt,reflect:!1,hasChanged:gt},yt=(t=$t,e,s)=>{const{kind:r,metadata:i}=s;let o=globalThis.litPropertyMetadata.get(i);if(void 0===o&&globalThis.litPropertyMetadata.set(i,o=new Map),o.set(s.name,t),"accessor"===r){const{name:r}=s;return{set(s){const i=e.get.call(this);e.set.call(this,s),this.requestUpdate(r,i,t)},init(e){return void 0!==e&&this.C(r,void 0,t),e}}}if("setter"===r){const{name:r}=s;return function(s){const i=this[r];e.call(this,s),this.requestUpdate(r,i,t)}}throw Error("Unsupported decorator location: "+r)};function bt(t){return(e,s)=>"object"==typeof s?yt(t,e,s):((t,e,s)=>{const r=e.hasOwnProperty(s);return e.constructor.createProperty(s,r?{...t,wrapped:!0}:t),r?Object.getOwnPropertyDescriptor(e,s):void 0})(t,e,s)}const vt="important",wt=" !"+vt,_t=z(class extends D{constructor(t){if(super(t),t.type!==F||"style"!==t.name||t.strings?.length>2)throw Error("The `styleMap` directive must be used in the `style` attribute and must be the only part in the attribute.")}render(t){return Object.keys(t).reduce(((e,s)=>{const r=t[s];return null==r?e:e+`${s=s.includes("-")?s:s.replace(/(?:^(webkit|moz|ms|o)|)(?=[A-Z])/g,"-$&").toLowerCase()}:${r};`}),"")}update(t,[e]){const{style:s}=t.element;if(void 0===this.ut)return this.ut=new Set(Object.keys(e)),this.render(e);for(const t of this.ut)null==e[t]&&(this.ut.delete(t),t.includes("-")?s.removeProperty(t):s[t]=null);for(const t in e){const r=e[t];if(null!=r){this.ut.add(t);const e="string"==typeof r&&r.endsWith(wt);t.includes("-")||e?s.setProperty(t,e?r.slice(0,-11):r,e?vt:""):s[t]=r}}return x}});class At extends D{constructor(t){if(super(t),this.et=O,t.type!==W)throw Error(this.constructor.directiveName+"() can only be used in child bindings")}render(t){if(t===O||null==t)return this.vt=void 0,this.et=t;if(t===x)return t;if("string"!=typeof t)throw Error(this.constructor.directiveName+"() called with a non-string value");if(t===this.et)return this.vt;this.et=t;const e=[t];return e.raw=e,this.vt={_$litType$:this.constructor.resultType,strings:e,values:[]}}}At.directiveName="unsafeHTML",At.resultType=1;const St=z(At);var Pt,Et;let xt;!function(t){t.approved="overlay-approved",t.check="actions-check",t.exclamationCircle="actions-exclamation-circle-alt",t.info="actions-info",t.listAlternative="actions-list-alternative",t.readonly="overlay-readonly",t.refresh="actions-refresh",t.rocket="actions-rocket",t.spinner="spinner-circle",t.viewPage="actions-view-page",t.warning="overlay-warning"}(Pt||(Pt={})),function(t){t.notificationShowReport="warming.notification.action.showReport",t.notificationActionRetry="warming.notification.action.retry",t.notificationAbortedTitle="warming.notification.aborted.title",t.notificationAbortedMessage="warming.notification.aborted.message",t.notificationErrorTitle="warming.notification.error.title",t.notificationErrorMessage="warming.notification.error.message",t.notificationNoSitesSelectedTitle="warming.notification.noSitesSelected.title",t.notificationNoSitesSelectedMessage="warming.notification.noSitesSelected.message",t.modalProgressTitle="warming.modal.progress.title",t.modalProgressButtonReport="warming.modal.progress.button.report",t.modalProgressButtonRetry="warming.modal.progress.button.retry",t.modalProgressButtonClose="warming.modal.progress.button.close",t.modalProgressFailedCounter="warming.modal.progress.failedCounter",t.modalProgressAllCounter="warming.modal.progress.allCounter",t.modalProgressPlaceholder="warming.modal.progress.placeholder",t.modalReportTitle="warming.modal.report.title",t.modalReportPanelFailed="warming.modal.report.panel.failed",t.modalReportPanelFailedSummary="warming.modal.report.panel.failed.summary",t.modalReportPanelSuccessful="warming.modal.report.panel.successful",t.modalReportPanelSuccessfulSummary="warming.modal.report.panel.successful.summary",t.modalReportPanelExcluded="warming.modal.report.panel.excluded",t.modalReportPanelExcludedSummary="warming.modal.report.panel.excluded.summary",t.modalReportPanelExcludedSitemaps="warming.modal.report.panel.excluded.sitemaps",t.modalReportPanelExcludedUrls="warming.modal.report.panel.excluded.urls",t.modalReportActionView="warming.modal.report.action.view",t.modalReportRequestId="warming.modal.report.message.requestId",t.modalReportTotal="warming.modal.report.message.total",t.modalReportNoUrlsCrawled="warming.modal.report.message.noUrlsCrawled",t.modalSitesTitle="warming.modal.sites.title",t.modalSitesUserAgentActionSuccessful="warming.modal.sites.userAgent.action.successful",t.modalSitesButtonStart="warming.modal.sites.button.start"}(Et||(Et={}));const Ot=new Uint8Array(16);function Ut(){if(!xt&&(xt="undefined"!=typeof crypto&&crypto.getRandomValues&&crypto.getRandomValues.bind(crypto),!xt))throw new Error("crypto.getRandomValues() not supported. See https://github.com/uuidjs/uuid#getrandomvalues-not-supported");return xt(Ot)}const Rt=[];for(let t=0;t<256;++t)Rt.push((t+256).toString(16).slice(1));var Nt={randomUUID:"undefined"!=typeof crypto&&crypto.randomUUID&&crypto.randomUUID.bind(crypto)};function Tt(t,e,s){if(Nt.randomUUID&&!e&&!t)return Nt.randomUUID();const r=(t=t||{}).random||(t.rng||Ut)();if(r[6]=15&r[6]|64,r[8]=63&r[8]|128,e){s=s||0;for(let t=0;t<16;++t)e[s+t]=r[t];return e}return function(t,e=0){return Rt[t[e+0]]+Rt[t[e+1]]+Rt[t[e+2]]+Rt[t[e+3]]+"-"+Rt[t[e+4]]+Rt[t[e+5]]+"-"+Rt[t[e+6]]+Rt[t[e+7]]+"-"+Rt[t[e+8]]+Rt[t[e+9]]+"-"+Rt[t[e+10]]+Rt[t[e+11]]+Rt[t[e+12]]+Rt[t[e+13]]+Rt[t[e+14]]+Rt[t[e+15]]}(r)}class Ct{static formatString(t,...e){return e.reduce(((t,e,s)=>t.replace(new RegExp(`\\{${s}}`),e)),t)}static generateUniqueId(){return Tt()}}let Mt=class extends i{constructor(){super(),this.id=`tx-warming-report-panel-${Ct.generateUniqueId()}`}createRenderRoot(){return this}render(){return o`
      <div class="panel panel-${this.state}">
        <div class="panel-heading">
          <h3 class="panel-title">
            <a class="collapsed"
               href="#${this.id}"
               data-bs-toggle="collapse"
               aria-controls="${this.id}"
               aria-expanded="false"
            >
              <span class="caret"></span>
              <strong> ${this.title} (${this.urls.length})</strong>
            </a>
          </h3>
        </div>
        <div id="${this.id}" class="panel-collapse collapse">
          <div class="table-fit">
            <table class="table table-striped table-hover">
              <tbody>
                ${this.urls.map((t=>o`
                  <tr>
                    <td>${t}</td>
                    <td class="col-control nowrap">
                      <div class="btn-group">
                        <a href="${t}" target="_blank" class="btn btn-default btn-sm nowrap">
                          <typo3-backend-icon identifier="actions-view-page" size="small" />
                          ${TYPO3.lang[Et.modalReportActionView]}
                        </a>
                      </div>
                    </td>
                  </tr>
                `))}
              </tbody>
            </table>
          </div>
        </div>
      </div>
    `}};a([bt({type:String})],Mt.prototype,"title",void 0),a([bt({type:String})],Mt.prototype,"state",void 0),a([bt({type:Array})],Mt.prototype,"urls",void 0),a([bt({attribute:!1})],Mt.prototype,"id",void 0),Mt=a([J("warming-report-panel")],Mt);let kt=class extends i{constructor(){super(...arguments),this.totalNumber=null}createRenderRoot(){return this}render(){return o`
      <div class="card card-${this.state} h-100">
        <div class="card-header">
          <div class="card-icon">
            <typo3-backend-icon identifier="${this.icon}" size="medium" />
          </div>
          <div class="card-header-body">
            <h1 class="card-title">${this.title}</h1>
            <span class="card-subtitle">${null!==this.totalNumber?o`<strong>${this.currentNumber}</strong>/${this.totalNumber}`:this.currentNumber.toString()}</span>
          </div>
        </div>
        <div class="card-body">
          <p class="card-text">${this.body}</p>
        </div>
      </div>
    `}};var jt;a([bt({type:String})],kt.prototype,"title",void 0),a([bt({type:String})],kt.prototype,"body",void 0),a([bt({type:String})],kt.prototype,"state",void 0),a([bt({type:String})],kt.prototype,"icon",void 0),a([bt({type:Number})],kt.prototype,"currentNumber",void 0),a([bt({type:Number})],kt.prototype,"totalNumber",void 0),kt=a([J("warming-report-summary-card")],kt);let Ht=jt=class extends i{constructor(t){super(),this.progress=t}createRenderRoot(){return this}render(){if(0===this.progress.getTotalNumberOfCrawledUrls())return this.createEmptyCrawlingNotice();const t=this.progress.getNumberOfExcludedSitemaps()+this.progress.getNumberOfExcludedUrls();return o`
      <div class="card-container">
        ${this.progress.getNumberOfFailedUrls()>0?o`
          <warming-report-summary-card
            class="col-4"
            title="${TYPO3.lang[Et.modalReportPanelFailed]}"
            body="${TYPO3.lang[Et.modalReportPanelFailedSummary]}"
            state="danger"
            icon="overlay-readonly"
            currentNumber="${this.progress.getNumberOfFailedUrls()}"
            totalNumber="${this.progress.progress.current}"
          />
        `:""}

        ${this.progress.getNumberOfSuccessfulUrls()>0?o`
          <warming-report-summary-card
            class="col-4"
            title="${TYPO3.lang[Et.modalReportPanelSuccessful]}"
            body="${TYPO3.lang[Et.modalReportPanelSuccessfulSummary]}"
            state="success"
            icon="overlay-approved"
            currentNumber="${this.progress.getNumberOfSuccessfulUrls()}"
            totalNumber="${this.progress.progress.current}"
          />
        `:""}

        ${t>0?o`
          <warming-report-summary-card
            class="col-4"
            title="${TYPO3.lang[Et.modalReportPanelExcluded]}"
            body="${TYPO3.lang[Et.modalReportPanelExcludedSummary]}"
            state="warning"
            icon="overlay-warning"
            currentNumber="${this.progress.getNumberOfSuccessfulUrls()}"
          />
        `:""}
      </div>

      <div class="panel-container">
        ${this.progress.getNumberOfFailedUrls()>0?o`
          <warming-report-panel
            title="${TYPO3.lang[Et.modalReportPanelFailed]}"
            state="danger"
            urls="${JSON.stringify(this.progress.urls.failed)}"
          />
        `:""}

        ${this.progress.getNumberOfSuccessfulUrls()>0?o`
          <warming-report-panel
            title="${TYPO3.lang[Et.modalReportPanelSuccessful]}"
            state="success"
            urls="${JSON.stringify(this.progress.urls.successful)}"
          />
        `:""}

        ${this.progress.getNumberOfExcludedSitemaps()>0?o`
          <warming-report-panel
            title="${TYPO3.lang[Et.modalReportPanelExcludedSitemaps]}"
            state="warning"
            urls="${JSON.stringify(this.progress.excluded.sitemaps)}"
          />
        `:""}

        ${this.progress.getNumberOfExcludedUrls()>0?o`
          <warming-report-panel
            title="${TYPO3.lang[Et.modalReportPanelExcludedUrls]}"
            state="warning"
            urls="${JSON.stringify(this.progress.excluded.urls)}"
          />
        `:""}
      </div>

      <small class="tx-warming-request-id">
        ${TYPO3.lang[Et.modalReportRequestId]} <code>${this.progress.requestId}</code
      ></small>
    `}createEmptyCrawlingNotice(){return o`
      <div class="callout callout-info">
        <div class="media">
          <div class="media-left">
              <span class="icon-emphasized">
                <typo3-backend-icon identifier="${Pt.info}" />
              </span>
          </div>
          <div class="media-body">
            ${TYPO3.lang[Et.modalReportNoUrlsCrawled]}
          </div>
        </div>
      </div>
    `}static createModal(t,e){r.dismiss();const s=[{text:TYPO3.lang[Et.modalProgressButtonRetry],icon:Pt.refresh,btnClass:"btn-default",trigger:e},{text:TYPO3.lang[Et.modalProgressButtonClose],btnClass:"btn-default",trigger:()=>r.dismiss()}];t.progress.current>0&&s.unshift({text:`${TYPO3.lang[Et.modalReportTotal]} ${t.progress.current}`,icon:Pt.exclamationCircle,btnClass:"disabled border-0"}),r.advanced({title:TYPO3.lang[Et.modalReportTitle],content:new jt(t),size:r.sizes.large,buttons:s})}};var qt,It,Yt;Ht=jt=a([J("warming-report-modal")],Ht),function(t){t.Failed="failed",t.Warning="warning",t.Success="success",t.Aborted="aborted",t.Unknown="unknown"}(qt||(qt={})),function(t){t.reportButton="tx-warming-open-report",t.retryButton="tx-warming-retry"}(Yt||(Yt={}));let Lt=It=class extends i{constructor(t){super(),this.progress=t,this.modal=r.currentModal}createRenderRoot(){return this}render(){const t=this.progress.progress.current>0,e=this.progress.getNumberOfFailedUrls(),s=this.progress.getProgressInPercent(),r={"progress-bar":!0,"progress-bar-striped":!0,active:t&&!this.progress.isFinished(),"progress-bar-animated":t&&!this.progress.isFinished(),"bg-danger":e>0&&this.progress.isFinished(),"bg-success":0===e&&this.progress.isFinished(),"progress-bar-danger":e>0&&this.progress.isFinished(),"progress-bar-success":0===e&&this.progress.isFinished(),"bg-warning":e>0,"progress-bar-warning":e>0&&!this.progress.isFinished()},i={width:`${t?s:0}%`};return o`
      <div class="tx-warming-progress-modal">
        <div class="tx-warming-progress-modal-progress progress">
          <div class=${V(r)}
               role="progressbar"
               aria-valuemin="0"
               aria-valuemax="${this.progress.progress.total}"
               aria-valuenow="${this.progress.progress.current}"
               style=${_t(i)}
          >
            ${t?`${s.toFixed(2)}%`:""}
          </div>
        </div>
        <div class="tx-warming-progress-modal-counter">
          ${t?"":o`
            <div class="tx-warming-progress-placeholder">
              ${TYPO3.lang[Et.modalProgressPlaceholder]}
            </div>
          `}
          <div>
            ${St(Ct.formatString(TYPO3.lang[Et.modalProgressAllCounter],this.progress.progress.current.toString(),this.progress.progress.total.toString()))}
          </div>
          ${e>0?o`
            <div class="badge badge-danger">
              ${Ct.formatString(TYPO3.lang[Et.modalProgressFailedCounter],e.toString())}
            </div>
          `:""}
        </div>
        ${this.progress.isFinished()?"":o`
          <div class="tx-warming-progress-modal-current-url">
            ${this.progress.getCurrentUrl()}
          </div>
        `}
      </div>
    `}finishProgress(t,e){const s=this.getReportButton();if(s.classList.remove("hidden"),new n("click",(()=>{Ht.createModal(t,e)})).bindTo(s),t.state!==qt.Aborted){const t=this.getRetryButton();t.classList.remove("hidden"),new n("click",e).bindTo(t)}}getModal(){return this.modal}getFooter(){return this.modal.querySelector(".modal-footer")}getReportButton(){return this.getFooter().querySelector(`button[name=${Yt.reportButton}]`)}getRetryButton(){return this.getFooter().querySelector(`button[name=${Yt.retryButton}]`)}static createModal(t){const e=new It(t);return r.dismiss(),e.modal=r.advanced({title:TYPO3.lang[Et.modalProgressTitle],content:e,size:r.sizes.small,staticBackdrop:!0,buttons:[{text:TYPO3.lang[Et.modalProgressButtonReport],icon:Pt.listAlternative,btnClass:"btn-primary hidden",name:Yt.reportButton},{text:TYPO3.lang[Et.modalProgressButtonRetry],icon:Pt.refresh,btnClass:"btn-default hidden",name:Yt.retryButton},{text:TYPO3.lang[Et.modalProgressButtonClose],btnClass:"btn-default",trigger:()=>r.dismiss()}]}),e}};a([bt({attribute:!1,type:Object,hasChanged:()=>!0})],Lt.prototype,"progress",void 0),a([bt({attribute:!1})],Lt.prototype,"modal",void 0),Lt=It=a([J("warming-progress-modal")],Lt);class Bt{static mergeUrlWithQueryParams(t,e){for(const[s,r]of e.entries())t.searchParams.append(s,r);return t}}class Ft{constructor(t){this.requestId=t,this.state=qt.Unknown,this.progress={current:0,total:0},this.urls={current:"",failed:[],successful:[]},this.excluded={sitemaps:[],urls:[]},this.response={title:"",message:""}}update(t){return t.state&&Object.values(qt).includes(t.state)&&(this.state=t.state),t.progress&&(this.progress=t.progress),t.urls&&(this.urls=t.urls),t.excluded&&(this.excluded=t.excluded),t.title&&(this.response.title=t.title),t.messages&&(this.response.message=t.messages.join("\n\n")),this}getCurrentUrl(){return this.urls.current}getNumberOfFailedUrls(){return this.urls.failed.length}getNumberOfSuccessfulUrls(){return this.urls.successful.length}getTotalNumberOfCrawledUrls(){return this.progress.total}getProgressInPercent(){return 0!==this.progress.total?Number(this.progress.current/this.progress.total*100):Number(100)}getNumberOfExcludedSitemaps(){return this.excluded.sitemaps.length}getNumberOfExcludedUrls(){return this.excluded.urls.length}isFinished(){return this.progress.current>=this.progress.total}}class Wt{startRequestWithQueryParams(t,e){return this.progress=new Ft(t.get("requestId")),this.progressModal=Lt.createModal(this.progress),this.request=new s(this.getUrl(t).toString()),this.progressModal.getModal().addEventListener("typo3-modal-hide",(()=>{this.abortWarmup()})),this.request.post({}).then((async t=>{const s=await t.resolve();return this.finishWarmup(s,e),this.progress})).catch((async t=>(this.reject(),await t.resolve(),this.progress)))}getUrl(t){const e=new URL(TYPO3.settings.ajaxUrls.tx_warming_cache_warmup_legacy,window.location.origin);return Bt.mergeUrlWithQueryParams(e,t)}cancelRequest(){this.request.abort()}finishWarmup(t,e){this.progress.update(t),this.progressModal.progress=this.progress,this.cancelRequest(),this.progressModal.finishProgress(this.progress,e)}abortWarmup(){this.cancelRequest(),this.progress.update({state:qt.Aborted})}reject(){this.cancelRequest(),r.dismiss()}}class zt{startRequestWithQueryParams(t,e){return this.progress=new Ft(t.get("requestId")),this.progressModal=Lt.createModal(this.progress),this.source=new EventSource(this.getUrl(t).toString(),{withCredentials:!0}),new Promise(((t,s)=>{this.progressModal.getModal().addEventListener("typo3-modal-hide",(()=>{this.abortWarmup(),t(this.progress)})),this.source.addEventListener("warmupProgress",(t=>this.updateProgress(t)),!1),this.source.addEventListener("warmupFinished",(s=>{this.finishWarmup(s,e),t(this.progress)}),!1),this.source.addEventListener("message",(()=>this.reject(s)),!1),this.source.addEventListener("error",(()=>this.reject(s)),!1)}))}getUrl(t){const e=new URL(TYPO3.settings.ajaxUrls.tx_warming_cache_warmup,window.location.origin);return Bt.mergeUrlWithQueryParams(e,t)}static isSupported(){return!!window.EventSource}closeSource(){return this.source.close(),EventSource.CLOSED===this.source.readyState}updateProgress(t){const e=JSON.parse(t.data);this.progress.update(e),this.progressModal.progress=this.progress}finishWarmup(t,e){const s=JSON.parse(t.data);this.progress.update(s),this.closeSource(),this.progressModal.progress=this.progress,this.progressModal.finishProgress(this.progress,e)}abortWarmup(){this.closeSource(),this.progress.update({state:qt.Aborted})}reject(t){this.closeSource(),r.dismiss(),t()}}class Dt{constructor(){this.handler=this.initializeRequestHandler()}warmupCache(e,s,r={}){const i=this.buildQueryParams(e,s,r),o=()=>this.warmupCache(e,s,r);return this.handler.startRequestWithQueryParams(i,o).then((e=>{let s;return e.state===qt.Aborted&&(s={label:TYPO3.lang[Et.notificationActionRetry],action:new t(o)}),Dt.showNotification(e,o,s),e}),(t=>(Dt.errorNotification(),t)))}initializeRequestHandler(){return zt.isSupported()?new zt:new Wt}buildQueryParams(t,e,s={}){const r=new URLSearchParams({requestId:Ct.generateUniqueId()});let i=0,o=0;for(const[e,s]of Object.entries(t)){const t=i++;r.set(`sites[${t}][site]`,e),s.forEach(((e,s)=>r.set(`sites[${t}][languageIds][${s}]`,(e??0).toString())))}for(const[t,s]of Object.entries(e)){const e=o++;r.set(`pages[${e}][page]`,t.toString()),s.forEach(((t,s)=>r.set(`pages[${e}][languageIds][${s}]`,(t??0).toString())))}for(const[t,e]of Object.entries(s))r.set(`configuration[${t}]`,e.toString());return r}static showNotification(s,r,i){let{title:o,message:n}=s.response;const a=[{label:TYPO3.lang[Et.notificationShowReport],action:new t((()=>{Ht.createModal(s,r)}))}];switch(i&&a.push(i),s.state){case qt.Failed:e.error(o,n,0,a);break;case qt.Warning:e.warning(o,n,0,a);break;case qt.Success:e.success(o,n,15,a);break;case qt.Aborted:o=TYPO3.lang[Et.notificationAbortedTitle],n=TYPO3.lang[Et.notificationAbortedMessage],e.info(o,n,15,a);break;case qt.Unknown:e.notice(o,n,15);break;default:Dt.errorNotification()}}static errorNotification(){e.error(TYPO3.lang[Et.notificationErrorTitle],TYPO3.lang[Et.notificationErrorMessage])}}export{Dt as C,Pt as I,Et as L,a as _,J as t};
