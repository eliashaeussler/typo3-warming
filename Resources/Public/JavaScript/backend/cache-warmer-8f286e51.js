import t from"@typo3/backend/action-button/immediate-action.js";import e from"@typo3/backend/notification.js";import r from"@typo3/core/ajax/ajax-request.js";import s from"@typo3/backend/modal.js";import{LitElement as i,html as o}from"lit";import n from"@typo3/core/event/regular-event.js";function a(t,e,r,s){var i,o=arguments.length,n=o<3?e:null===s?s=Object.getOwnPropertyDescriptor(e,r):s;if("object"==typeof Reflect&&"function"==typeof Reflect.decorate)n=Reflect.decorate(t,e,r,s);else for(var a=t.length-1;a>=0;a--)(i=t[a])&&(n=(o<3?i(n):o>3?i(e,r,n):i(e,r))||n);return o>3&&n&&Object.defineProperty(e,r,n),n}var l;"function"==typeof SuppressedError&&SuppressedError;const d=window,c=d.trustedTypes,u=c?c.createPolicy("lit-html",{createHTML:t=>t}):void 0,p="$lit$",h=`lit$${(Math.random()+"").slice(9)}$`,g="?"+h,m=`<${g}>`,f=document,v=()=>f.createComment(""),$=t=>null===t||"object"!=typeof t&&"function"!=typeof t,y=Array.isArray,b="[ \t\n\f\r]",w=/<(?:(!--|\/[^a-zA-Z])|(\/?[a-zA-Z][^>\s]*)|(\/?$))/g,A=/-->/g,_=/>/g,S=RegExp(`>|${b}(?:([^\\s"'>=/]+)(${b}*=${b}*(?:[^ \t\n\f\r"'\`<>=]|("|')|))|$)`,"g"),P=/'/g,x=/"/g,N=/^(?:script|style|textarea|title)$/i,R=Symbol.for("lit-noChange"),T=Symbol.for("lit-nothing"),O=new WeakMap,U=f.createTreeWalker(f,129,null,!1);function E(t,e){if(!Array.isArray(t)||!t.hasOwnProperty("raw"))throw Error("invalid template strings array");return void 0!==u?u.createHTML(e):e}const C=(t,e)=>{const r=t.length-1,s=[];let i,o=2===e?"<svg>":"",n=w;for(let e=0;e<r;e++){const r=t[e];let a,l,d=-1,c=0;for(;c<r.length&&(n.lastIndex=c,l=n.exec(r),null!==l);)c=n.lastIndex,n===w?"!--"===l[1]?n=A:void 0!==l[1]?n=_:void 0!==l[2]?(N.test(l[2])&&(i=RegExp("</"+l[2],"g")),n=S):void 0!==l[3]&&(n=S):n===S?">"===l[0]?(n=null!=i?i:w,d=-1):void 0===l[1]?d=-2:(d=n.lastIndex-l[2].length,a=l[1],n=void 0===l[3]?S:'"'===l[3]?x:P):n===x||n===P?n=S:n===A||n===_?n=w:(n=S,i=void 0);const u=n===S&&t[e+1].startsWith("/>")?" ":"";o+=n===w?r+m:d>=0?(s.push(a),r.slice(0,d)+p+r.slice(d)+h+u):r+h+(-2===d?(s.push(void 0),e):u)}return[E(t,o+(t[r]||"<?>")+(2===e?"</svg>":"")),s]};class M{constructor({strings:t,_$litType$:e},r){let s;this.parts=[];let i=0,o=0;const n=t.length-1,a=this.parts,[l,d]=C(t,e);if(this.el=M.createElement(l,r),U.currentNode=this.el.content,2===e){const t=this.el.content,e=t.firstChild;e.remove(),t.append(...e.childNodes)}for(;null!==(s=U.nextNode())&&a.length<n;){if(1===s.nodeType){if(s.hasAttributes()){const t=[];for(const e of s.getAttributeNames())if(e.endsWith(p)||e.startsWith(h)){const r=d[o++];if(t.push(e),void 0!==r){const t=s.getAttribute(r.toLowerCase()+p).split(h),e=/([.?@])?(.*)/.exec(r);a.push({type:1,index:i,name:e[2],strings:t,ctor:"."===e[1]?F:"?"===e[1]?I:"@"===e[1]?L:Y})}else a.push({type:6,index:i})}for(const e of t)s.removeAttribute(e)}if(N.test(s.tagName)){const t=s.textContent.split(h),e=t.length-1;if(e>0){s.textContent=c?c.emptyScript:"";for(let r=0;r<e;r++)s.append(t[r],v()),U.nextNode(),a.push({type:2,index:++i});s.append(t[e],v())}}}else if(8===s.nodeType)if(s.data===g)a.push({type:2,index:i});else{let t=-1;for(;-1!==(t=s.data.indexOf(h,t+1));)a.push({type:7,index:i}),t+=h.length-1}i++}}static createElement(t,e){const r=f.createElement("template");return r.innerHTML=t,r}}function k(t,e,r=t,s){var i,o,n,a;if(e===R)return e;let l=void 0!==s?null===(i=r._$Co)||void 0===i?void 0:i[s]:r._$Cl;const d=$(e)?void 0:e._$litDirective$;return(null==l?void 0:l.constructor)!==d&&(null===(o=null==l?void 0:l._$AO)||void 0===o||o.call(l,!1),void 0===d?l=void 0:(l=new d(t),l._$AT(t,r,s)),void 0!==s?(null!==(n=(a=r)._$Co)&&void 0!==n?n:a._$Co=[])[s]=l:r._$Cl=l),void 0!==l&&(e=k(t,l._$AS(t,e.values),l,s)),e}class H{constructor(t,e){this._$AV=[],this._$AN=void 0,this._$AD=t,this._$AM=e}get parentNode(){return this._$AM.parentNode}get _$AU(){return this._$AM._$AU}u(t){var e;const{el:{content:r},parts:s}=this._$AD,i=(null!==(e=null==t?void 0:t.creationScope)&&void 0!==e?e:f).importNode(r,!0);U.currentNode=i;let o=U.nextNode(),n=0,a=0,l=s[0];for(;void 0!==l;){if(n===l.index){let e;2===l.type?e=new j(o,o.nextSibling,this,t):1===l.type?e=new l.ctor(o,l.name,l.strings,this,t):6===l.type&&(e=new W(o,this,t)),this._$AV.push(e),l=s[++a]}n!==(null==l?void 0:l.index)&&(o=U.nextNode(),n++)}return U.currentNode=f,i}v(t){let e=0;for(const r of this._$AV)void 0!==r&&(void 0!==r.strings?(r._$AI(t,r,e),e+=r.strings.length-2):r._$AI(t[e])),e++}}class j{constructor(t,e,r,s){var i;this.type=2,this._$AH=T,this._$AN=void 0,this._$AA=t,this._$AB=e,this._$AM=r,this.options=s,this._$Cp=null===(i=null==s?void 0:s.isConnected)||void 0===i||i}get _$AU(){var t,e;return null!==(e=null===(t=this._$AM)||void 0===t?void 0:t._$AU)&&void 0!==e?e:this._$Cp}get parentNode(){let t=this._$AA.parentNode;const e=this._$AM;return void 0!==e&&11===(null==t?void 0:t.nodeType)&&(t=e.parentNode),t}get startNode(){return this._$AA}get endNode(){return this._$AB}_$AI(t,e=this){t=k(this,t,e),$(t)?t===T||null==t||""===t?(this._$AH!==T&&this._$AR(),this._$AH=T):t!==this._$AH&&t!==R&&this._(t):void 0!==t._$litType$?this.g(t):void 0!==t.nodeType?this.$(t):(t=>y(t)||"function"==typeof(null==t?void 0:t[Symbol.iterator]))(t)?this.T(t):this._(t)}k(t){return this._$AA.parentNode.insertBefore(t,this._$AB)}$(t){this._$AH!==t&&(this._$AR(),this._$AH=this.k(t))}_(t){this._$AH!==T&&$(this._$AH)?this._$AA.nextSibling.data=t:this.$(f.createTextNode(t)),this._$AH=t}g(t){var e;const{values:r,_$litType$:s}=t,i="number"==typeof s?this._$AC(t):(void 0===s.el&&(s.el=M.createElement(E(s.h,s.h[0]),this.options)),s);if((null===(e=this._$AH)||void 0===e?void 0:e._$AD)===i)this._$AH.v(r);else{const t=new H(i,this),e=t.u(this.options);t.v(r),this.$(e),this._$AH=t}}_$AC(t){let e=O.get(t.strings);return void 0===e&&O.set(t.strings,e=new M(t)),e}T(t){y(this._$AH)||(this._$AH=[],this._$AR());const e=this._$AH;let r,s=0;for(const i of t)s===e.length?e.push(r=new j(this.k(v()),this.k(v()),this,this.options)):r=e[s],r._$AI(i),s++;s<e.length&&(this._$AR(r&&r._$AB.nextSibling,s),e.length=s)}_$AR(t=this._$AA.nextSibling,e){var r;for(null===(r=this._$AP)||void 0===r||r.call(this,!1,!0,e);t&&t!==this._$AB;){const e=t.nextSibling;t.remove(),t=e}}setConnected(t){var e;void 0===this._$AM&&(this._$Cp=t,null===(e=this._$AP)||void 0===e||e.call(this,t))}}class Y{constructor(t,e,r,s,i){this.type=1,this._$AH=T,this._$AN=void 0,this.element=t,this.name=e,this._$AM=s,this.options=i,r.length>2||""!==r[0]||""!==r[1]?(this._$AH=Array(r.length-1).fill(new String),this.strings=r):this._$AH=T}get tagName(){return this.element.tagName}get _$AU(){return this._$AM._$AU}_$AI(t,e=this,r,s){const i=this.strings;let o=!1;if(void 0===i)t=k(this,t,e,0),o=!$(t)||t!==this._$AH&&t!==R,o&&(this._$AH=t);else{const s=t;let n,a;for(t=i[0],n=0;n<i.length-1;n++)a=k(this,s[r+n],e,n),a===R&&(a=this._$AH[n]),o||(o=!$(a)||a!==this._$AH[n]),a===T?t=T:t!==T&&(t+=(null!=a?a:"")+i[n+1]),this._$AH[n]=a}o&&!s&&this.j(t)}j(t){t===T?this.element.removeAttribute(this.name):this.element.setAttribute(this.name,null!=t?t:"")}}class F extends Y{constructor(){super(...arguments),this.type=3}j(t){this.element[this.name]=t===T?void 0:t}}const B=c?c.emptyScript:"";class I extends Y{constructor(){super(...arguments),this.type=4}j(t){t&&t!==T?this.element.setAttribute(this.name,B):this.element.removeAttribute(this.name)}}class L extends Y{constructor(t,e,r,s,i){super(t,e,r,s,i),this.type=5}_$AI(t,e=this){var r;if((t=null!==(r=k(this,t,e,0))&&void 0!==r?r:T)===R)return;const s=this._$AH,i=t===T&&s!==T||t.capture!==s.capture||t.once!==s.once||t.passive!==s.passive,o=t!==T&&(s===T||i);i&&this.element.removeEventListener(this.name,this,s),o&&this.element.addEventListener(this.name,this,t),this._$AH=t}handleEvent(t){var e,r;"function"==typeof this._$AH?this._$AH.call(null!==(r=null===(e=this.options)||void 0===e?void 0:e.host)&&void 0!==r?r:this.element,t):this._$AH.handleEvent(t)}}class W{constructor(t,e,r){this.element=t,this.type=6,this._$AN=void 0,this._$AM=e,this.options=r}get _$AU(){return this._$AM._$AU}_$AI(t){k(this,t)}}const q=d.litHtmlPolyfillSupport;null==q||q(M,j),(null!==(l=d.litHtmlVersions)&&void 0!==l?l:d.litHtmlVersions=[]).push("2.7.5");const z=1,D=2,V=t=>(...e)=>({_$litDirective$:t,values:e});let Q=class{constructor(t){}get _$AU(){return this._$AM._$AU}_$AT(t,e,r){this._$Ct=t,this._$AM=e,this._$Ci=r}_$AS(t,e){return this.update(t,e)}update(t,e){return this.render(...e)}};const J=V(class extends Q{constructor(t){var e;if(super(t),t.type!==z||"class"!==t.name||(null===(e=t.strings)||void 0===e?void 0:e.length)>2)throw Error("`classMap()` can only be used in the `class` attribute and must be the only part in the attribute.")}render(t){return" "+Object.keys(t).filter((e=>t[e])).join(" ")+" "}update(t,[e]){var r,s;if(void 0===this.it){this.it=new Set,void 0!==t.strings&&(this.nt=new Set(t.strings.join(" ").split(/\s/).filter((t=>""!==t))));for(const t in e)e[t]&&!(null===(r=this.nt)||void 0===r?void 0:r.has(t))&&this.it.add(t);return this.render(e)}const i=t.element.classList;this.it.forEach((t=>{t in e||(i.remove(t),this.it.delete(t))}));for(const t in e){const r=!!e[t];r===this.it.has(t)||(null===(s=this.nt)||void 0===s?void 0:s.has(t))||(r?(i.add(t),this.it.add(t)):(i.remove(t),this.it.delete(t)))}return R}}),Z=t=>e=>"function"==typeof e?((t,e)=>(customElements.define(t,e),e))(t,e):((t,e)=>{const{kind:r,elements:s}=e;return{kind:r,elements:s,finisher(e){customElements.define(t,e)}}})(t,e),K=(t,e)=>"method"===e.kind&&e.descriptor&&!("value"in e.descriptor)?{...e,finisher(r){r.createProperty(e.key,t)}}:{kind:"field",key:Symbol(),placement:"own",descriptor:{},originalKey:e.key,initializer(){"function"==typeof e.initializer&&(this[e.key]=e.initializer.call(this))},finisher(r){r.createProperty(e.key,t)}};function G(t){return(e,r)=>void 0!==r?((t,e,r)=>{e.constructor.createProperty(r,t)})(t,e,r):K(t,e)}var X;null===(X=window.HTMLSlotElement)||void 0===X||X.prototype.assignedElements;const tt="important",et=" !"+tt,rt=V(class extends Q{constructor(t){var e;if(super(t),t.type!==z||"style"!==t.name||(null===(e=t.strings)||void 0===e?void 0:e.length)>2)throw Error("The `styleMap` directive must be used in the `style` attribute and must be the only part in the attribute.")}render(t){return Object.keys(t).reduce(((e,r)=>{const s=t[r];return null==s?e:e+`${r=r.includes("-")?r:r.replace(/(?:^(webkit|moz|ms|o)|)(?=[A-Z])/g,"-$&").toLowerCase()}:${s};`}),"")}update(t,[e]){const{style:r}=t.element;if(void 0===this.ut){this.ut=new Set;for(const t in e)this.ut.add(t);return this.render(e)}this.ut.forEach((t=>{null==e[t]&&(this.ut.delete(t),t.includes("-")?r.removeProperty(t):r[t]="")}));for(const t in e){const s=e[t];if(null!=s){this.ut.add(t);const e="string"==typeof s&&s.endsWith(et);t.includes("-")||e?r.setProperty(t,e?s.slice(0,-11):s,e?tt:""):r[t]=s}}return R}});class st extends Q{constructor(t){if(super(t),this.et=T,t.type!==D)throw Error(this.constructor.directiveName+"() can only be used in child bindings")}render(t){if(t===T||null==t)return this.ft=void 0,this.et=t;if(t===R)return t;if("string"!=typeof t)throw Error(this.constructor.directiveName+"() called with a non-string value");if(t===this.et)return this.ft;this.et=t;const e=[t];return e.raw=e,this.ft={_$litType$:this.constructor.resultType,strings:e,values:[]}}}st.directiveName="unsafeHTML",st.resultType=1;const it=V(st);var ot,nt;let at;!function(t){t.approved="overlay-approved",t.check="actions-check",t.exclamationCircle="actions-exclamation-circle-alt",t.info="actions-info",t.listAlternative="actions-list-alternative",t.readonly="overlay-readonly",t.refresh="actions-refresh",t.rocket="actions-rocket",t.spinner="spinner-circle",t.viewPage="actions-view-page",t.warning="overlay-warning"}(ot||(ot={})),function(t){t.notificationShowReport="warming.notification.action.showReport",t.notificationActionRetry="warming.notification.action.retry",t.notificationAbortedTitle="warming.notification.aborted.title",t.notificationAbortedMessage="warming.notification.aborted.message",t.notificationErrorTitle="warming.notification.error.title",t.notificationErrorMessage="warming.notification.error.message",t.notificationNoSitesSelectedTitle="warming.notification.noSitesSelected.title",t.notificationNoSitesSelectedMessage="warming.notification.noSitesSelected.message",t.modalProgressTitle="warming.modal.progress.title",t.modalProgressButtonReport="warming.modal.progress.button.report",t.modalProgressButtonRetry="warming.modal.progress.button.retry",t.modalProgressButtonClose="warming.modal.progress.button.close",t.modalProgressFailedCounter="warming.modal.progress.failedCounter",t.modalProgressAllCounter="warming.modal.progress.allCounter",t.modalProgressPlaceholder="warming.modal.progress.placeholder",t.modalReportTitle="warming.modal.report.title",t.modalReportPanelFailed="warming.modal.report.panel.failed",t.modalReportPanelFailedSummary="warming.modal.report.panel.failed.summary",t.modalReportPanelSuccessful="warming.modal.report.panel.successful",t.modalReportPanelSuccessfulSummary="warming.modal.report.panel.successful.summary",t.modalReportPanelExcluded="warming.modal.report.panel.excluded",t.modalReportPanelExcludedSummary="warming.modal.report.panel.excluded.summary",t.modalReportPanelExcludedSitemaps="warming.modal.report.panel.excluded.sitemaps",t.modalReportPanelExcludedUrls="warming.modal.report.panel.excluded.urls",t.modalReportActionView="warming.modal.report.action.view",t.modalReportTotal="warming.modal.report.message.total",t.modalReportNoUrlsCrawled="warming.modal.report.message.noUrlsCrawled",t.modalSitesTitle="warming.modal.sites.title",t.modalSitesUserAgentActionSuccessful="warming.modal.sites.userAgent.action.successful",t.modalSitesButtonStart="warming.modal.sites.button.start"}(nt||(nt={}));const lt=new Uint8Array(16);function dt(){if(!at&&(at="undefined"!=typeof crypto&&crypto.getRandomValues&&crypto.getRandomValues.bind(crypto),!at))throw new Error("crypto.getRandomValues() not supported. See https://github.com/uuidjs/uuid#getrandomvalues-not-supported");return at(lt)}const ct=[];for(let t=0;t<256;++t)ct.push((t+256).toString(16).slice(1));var ut={randomUUID:"undefined"!=typeof crypto&&crypto.randomUUID&&crypto.randomUUID.bind(crypto)};function pt(t,e,r){if(ut.randomUUID&&!e&&!t)return ut.randomUUID();const s=(t=t||{}).random||(t.rng||dt)();if(s[6]=15&s[6]|64,s[8]=63&s[8]|128,e){r=r||0;for(let t=0;t<16;++t)e[r+t]=s[t];return e}return function(t,e=0){return(ct[t[e+0]]+ct[t[e+1]]+ct[t[e+2]]+ct[t[e+3]]+"-"+ct[t[e+4]]+ct[t[e+5]]+"-"+ct[t[e+6]]+ct[t[e+7]]+"-"+ct[t[e+8]]+ct[t[e+9]]+"-"+ct[t[e+10]]+ct[t[e+11]]+ct[t[e+12]]+ct[t[e+13]]+ct[t[e+14]]+ct[t[e+15]]).toLowerCase()}(s)}class ht{static formatString(t,...e){return e.reduce(((t,e,r)=>t.replace(new RegExp(`\\{${r}}`),e)),t)}static generateUniqueId(){return pt()}}let gt=class extends i{constructor(){super(),this.id=`tx-warming-report-panel-${ht.generateUniqueId()}`}createRenderRoot(){return this}render(){return o`
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
                          ${TYPO3.lang[nt.modalReportActionView]}
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
    `}};a([G({type:String})],gt.prototype,"title",void 0),a([G({type:String})],gt.prototype,"state",void 0),a([G({type:Array})],gt.prototype,"urls",void 0),a([G({attribute:!1})],gt.prototype,"id",void 0),gt=a([Z("warming-report-panel")],gt);let mt=class extends i{constructor(){super(...arguments),this.totalNumber=null}createRenderRoot(){return this}render(){return o`
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
    `}};var ft;a([G({type:String})],mt.prototype,"title",void 0),a([G({type:String})],mt.prototype,"body",void 0),a([G({type:String})],mt.prototype,"state",void 0),a([G({type:String})],mt.prototype,"icon",void 0),a([G({type:Number})],mt.prototype,"currentNumber",void 0),a([G({type:Number})],mt.prototype,"totalNumber",void 0),mt=a([Z("warming-report-summary-card")],mt);let vt=ft=class extends i{constructor(t){super(),this.progress=t}createRenderRoot(){return this}render(){if(0===this.progress.getTotalNumberOfCrawledUrls())return this.createEmptyCrawlingNotice();const t=this.progress.getNumberOfExcludedSitemaps()+this.progress.getNumberOfExcludedUrls();return o`
      <div class="card-container">
        ${this.progress.getNumberOfFailedUrls()>0?o`
          <warming-report-summary-card
            class="col-4"
            title="${TYPO3.lang[nt.modalReportPanelFailed]}"
            body="${TYPO3.lang[nt.modalReportPanelFailedSummary]}"
            state="danger"
            icon="overlay-readonly"
            currentNumber="${this.progress.getNumberOfFailedUrls()}"
            totalNumber="${this.progress.progress.current}"
          />
        `:""}

        ${this.progress.getNumberOfSuccessfulUrls()>0?o`
          <warming-report-summary-card
            class="col-4"
            title="${TYPO3.lang[nt.modalReportPanelSuccessful]}"
            body="${TYPO3.lang[nt.modalReportPanelSuccessfulSummary]}"
            state="success"
            icon="overlay-approved"
            currentNumber="${this.progress.getNumberOfSuccessfulUrls()}"
            totalNumber="${this.progress.progress.current}"
          />
        `:""}

        ${t>0?o`
          <warming-report-summary-card
            class="col-4"
            title="${TYPO3.lang[nt.modalReportPanelExcluded]}"
            body="${TYPO3.lang[nt.modalReportPanelExcludedSummary]}"
            state="warning"
            icon="overlay-warning"
            currentNumber="${this.progress.getNumberOfSuccessfulUrls()}"
          />
        `:""}
      </div>

      ${this.progress.getNumberOfFailedUrls()>0?o`
        <warming-report-panel
          title="${TYPO3.lang[nt.modalReportPanelFailed]}"
          state="danger"
          urls="${JSON.stringify(this.progress.urls.failed)}"
        />
      `:""}

      ${this.progress.getNumberOfSuccessfulUrls()>0?o`
        <warming-report-panel
          title="${TYPO3.lang[nt.modalReportPanelSuccessful]}"
          state="success"
          urls="${JSON.stringify(this.progress.urls.successful)}"
        />
      `:""}

      ${this.progress.getNumberOfExcludedSitemaps()>0?o`
        <warming-report-panel
          title="${TYPO3.lang[nt.modalReportPanelExcludedSitemaps]}"
          state="warning"
          urls="${JSON.stringify(this.progress.excluded.sitemaps)}"
        />
      `:""}

      ${this.progress.getNumberOfExcludedUrls()>0?o`
        <warming-report-panel
          title="${TYPO3.lang[nt.modalReportPanelExcludedUrls]}"
          state="warning"
          urls="${JSON.stringify(this.progress.excluded.urls)}"
        />
      `:""}
    `}createEmptyCrawlingNotice(){return o`
      <div class="callout callout-info">
        <div class="media">
          <div class="media-left">
              <span class="icon-emphasized">
                <typo3-backend-icon identifier="${ot.info}" />
              </span>
          </div>
          <div class="media-body">
            ${TYPO3.lang[nt.modalReportNoUrlsCrawled]}
          </div>
        </div>
      </div>
    `}static createModal(t,e){s.dismiss();const r=[{text:TYPO3.lang[nt.modalProgressButtonRetry],icon:ot.refresh,btnClass:"btn-default",trigger:e},{text:TYPO3.lang[nt.modalProgressButtonClose],btnClass:"btn-default",trigger:()=>s.dismiss()}];t.progress.current>0&&r.unshift({text:`${TYPO3.lang[nt.modalReportTotal]} ${t.progress.current}`,icon:ot.exclamationCircle,btnClass:"disabled border-0"}),s.advanced({title:TYPO3.lang[nt.modalReportTitle],content:new ft(t),size:s.sizes.large,buttons:r})}};var $t,yt,bt;vt=ft=a([Z("warming-report-modal")],vt),function(t){t.Failed="failed",t.Warning="warning",t.Success="success",t.Aborted="aborted",t.Unknown="unknown"}($t||($t={})),function(t){t.reportButton="tx-warming-open-report",t.retryButton="tx-warming-retry"}(bt||(bt={}));let wt=yt=class extends i{constructor(t){super(),this.progress=t,this.modal=s.currentModal}createRenderRoot(){return this}render(){const t=this.progress.progress.current>0,e=this.progress.getNumberOfFailedUrls(),r=this.progress.getProgressInPercent(),s={"progress-bar":!0,"progress-bar-striped":!0,active:t&&!this.progress.isFinished(),"progress-bar-animated":t&&!this.progress.isFinished(),"bg-danger":e>0&&this.progress.isFinished(),"bg-success":0===e&&this.progress.isFinished(),"progress-bar-danger":e>0&&this.progress.isFinished(),"progress-bar-success":0===e&&this.progress.isFinished(),"bg-warning":e>0,"progress-bar-warning":e>0&&!this.progress.isFinished()},i={width:`${t?r:0}%`};return o`
      <div class="tx-warming-progress-modal">
        <div class="tx-warming-progress-modal-progress progress">
          <div class=${J(s)}
               role="progressbar"
               aria-valuemin="0"
               aria-valuemax="${this.progress.progress.total}"
               aria-valuenow="${this.progress.progress.current}"
               style=${rt(i)}
          >
            ${t?`${r.toFixed(2)}%`:""}
          </div>
        </div>
        <div class="tx-warming-progress-modal-counter">
          ${t?"":o`
            <div class="tx-warming-progress-placeholder">
              ${TYPO3.lang[nt.modalProgressPlaceholder]}
            </div>
          `}
          <div>
            ${it(ht.formatString(TYPO3.lang[nt.modalProgressAllCounter],this.progress.progress.current.toString(),this.progress.progress.total.toString()))}
          </div>
          ${e>0?o`
            <div class="badge badge-danger">
              ${ht.formatString(TYPO3.lang[nt.modalProgressFailedCounter],e.toString())}
            </div>
          `:""}
        </div>
        ${this.progress.isFinished()?"":o`
          <div class="tx-warming-progress-modal-current-url">
            ${this.progress.getCurrentUrl()}
          </div>
        `}
      </div>
    `}finishProgress(t,e){const r=this.getReportButton();if(r.classList.remove("hidden"),new n("click",(()=>{vt.createModal(t,e)})).bindTo(r),t.state!==$t.Aborted){const t=this.getRetryButton();t.classList.remove("hidden"),new n("click",e).bindTo(t)}}getModal(){return this.modal}getFooter(){return this.modal.querySelector(".modal-footer")}getReportButton(){return this.getFooter().querySelector(`button[name=${bt.reportButton}]`)}getRetryButton(){return this.getFooter().querySelector(`button[name=${bt.retryButton}]`)}static createModal(t){const e=new yt(t);return s.dismiss(),e.modal=s.advanced({title:TYPO3.lang[nt.modalProgressTitle],content:e,size:s.sizes.small,staticBackdrop:!0,buttons:[{text:TYPO3.lang[nt.modalProgressButtonReport],icon:ot.listAlternative,btnClass:"btn-primary hidden",name:bt.reportButton},{text:TYPO3.lang[nt.modalProgressButtonRetry],icon:ot.refresh,btnClass:"btn-default hidden",name:bt.retryButton},{text:TYPO3.lang[nt.modalProgressButtonClose],btnClass:"btn-default",trigger:()=>s.dismiss()}]}),e}};a([G({attribute:!1,type:Object,hasChanged:()=>!0})],wt.prototype,"progress",void 0),a([G({attribute:!1})],wt.prototype,"modal",void 0),wt=yt=a([Z("warming-progress-modal")],wt);class At{static mergeUrlWithQueryParams(t,e){for(const[r,s]of e.entries())t.searchParams.append(r,s);return t}}class _t{constructor(t){this.state=$t.Unknown,this.progress={current:0,total:0},this.urls={current:"",failed:[],successful:[]},this.excluded={sitemaps:[],urls:[]},this.response={title:"",message:""},t&&this.update(t)}update(t){return t.state&&Object.values($t).includes(t.state)&&(this.state=t.state),t.progress&&(this.progress=t.progress),t.urls&&(this.urls=t.urls),t.excluded&&(this.excluded=t.excluded),t.title&&(this.response.title=t.title),t.messages&&(this.response.message=t.messages.join("\n\n")),this}getCurrentUrl(){return this.urls.current}getNumberOfFailedUrls(){return this.urls.failed.length}getNumberOfSuccessfulUrls(){return this.urls.successful.length}getTotalNumberOfCrawledUrls(){return this.progress.total}getProgressInPercent(){return 0!==this.progress.total?Number(this.progress.current/this.progress.total*100):Number(100)}getNumberOfExcludedSitemaps(){return this.excluded.sitemaps.length}getNumberOfExcludedUrls(){return this.excluded.urls.length}isFinished(){return this.progress.current>=this.progress.total}}class St{startRequestWithQueryParams(t,e){return this.progress=new _t,this.progressModal=wt.createModal(this.progress),this.request=new r(this.getUrl(t).toString()),this.progressModal.getModal().addEventListener("typo3-modal-hide",(()=>{this.abortWarmup()})),this.request.post({}).then((async t=>{const r=await t.resolve();return this.finishWarmup(r,e),this.progress})).catch((async t=>(this.reject(),await t.resolve(),this.progress)))}getUrl(t){const e=new URL(TYPO3.settings.ajaxUrls.tx_warming_cache_warmup_legacy,window.location.origin);return At.mergeUrlWithQueryParams(e,t)}cancelRequest(){this.request.abort()}finishWarmup(t,e){this.progress.update(t),this.progressModal.progress=this.progress,this.cancelRequest(),this.progressModal.finishProgress(this.progress,e)}abortWarmup(){this.cancelRequest(),this.progress.update({state:$t.Aborted})}reject(){this.cancelRequest(),s.dismiss()}}class Pt{startRequestWithQueryParams(t,e){return this.progress=new _t,this.progressModal=wt.createModal(this.progress),this.source=new EventSource(this.getUrl(t).toString(),{withCredentials:!0}),new Promise(((t,r)=>{this.progressModal.getModal().addEventListener("typo3-modal-hide",(()=>{this.abortWarmup(),t(this.progress)})),this.source.addEventListener("warmupProgress",(t=>this.updateProgress(t)),!1),this.source.addEventListener("warmupFinished",(r=>{this.finishWarmup(r,e),t(this.progress)}),!1),this.source.addEventListener("message",(()=>this.reject(r)),!1),this.source.addEventListener("error",(()=>this.reject(r)),!1)}))}getUrl(t){const e=new URL(TYPO3.settings.ajaxUrls.tx_warming_cache_warmup,window.location.origin);return At.mergeUrlWithQueryParams(e,t)}static isSupported(){return!!window.EventSource}closeSource(){return this.source.close(),EventSource.CLOSED===this.source.readyState}updateProgress(t){const e=JSON.parse(t.data);this.progress.update(e),this.progressModal.progress=this.progress}finishWarmup(t,e){const r=JSON.parse(t.data);this.progress.update(r),this.closeSource(),this.progressModal.progress=this.progress,this.progressModal.finishProgress(this.progress,e)}abortWarmup(){this.closeSource(),this.progress.update({state:$t.Aborted})}reject(t){this.closeSource(),s.dismiss(),t()}}class xt{constructor(){this.handler=this.initializeRequestHandler()}warmupCache(e,r,s={}){const i=this.buildQueryParams(e,r,s),o=()=>this.warmupCache(e,r,s);return this.handler.startRequestWithQueryParams(i,o).then((e=>{let r;return e.state===$t.Aborted&&(r={label:TYPO3.lang[nt.notificationActionRetry],action:new t(o)}),xt.showNotification(e,o,r),e}),(t=>(xt.errorNotification(),t)))}initializeRequestHandler(){return Pt.isSupported()?new Pt:new St}buildQueryParams(t,e,r={}){const s=new URLSearchParams({requestId:ht.generateUniqueId()});let i=0,o=0;for(const[e,r]of Object.entries(t)){const t=i++;s.set(`sites[${t}][site]`,e),r.forEach(((e,r)=>s.set(`sites[${t}][languageIds][${r}]`,(e??0).toString())))}for(const[t,r]of Object.entries(e)){const e=o++;s.set(`pages[${e}][page]`,t.toString()),r.forEach(((t,r)=>s.set(`pages[${e}][languageIds][${r}]`,(t??0).toString())))}for(const[t,e]of Object.entries(r))s.set(`configuration[${t}]`,e.toString());return s}static showNotification(r,s,i){let{title:o,message:n}=r.response;const a=[{label:TYPO3.lang[nt.notificationShowReport],action:new t((()=>{vt.createModal(r,s)}))}];switch(i&&a.push(i),r.state){case $t.Failed:e.error(o,n,0,a);break;case $t.Warning:e.warning(o,n,0,a);break;case $t.Success:e.success(o,n,15,a);break;case $t.Aborted:o=TYPO3.lang[nt.notificationAbortedTitle],n=TYPO3.lang[nt.notificationAbortedMessage],e.info(o,n,15,a);break;case $t.Unknown:e.notice(o,n,15);break;default:xt.errorNotification()}}static errorNotification(){e.error(TYPO3.lang[nt.notificationErrorTitle],TYPO3.lang[nt.notificationErrorMessage])}}export{xt as C,ot as I,nt as L,a as _,Z as e};
