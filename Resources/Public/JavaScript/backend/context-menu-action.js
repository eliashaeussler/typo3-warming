import{C as e}from"./cache-warmer-f92255ad.js";import"@typo3/backend/action-button/immediate-action.js";import"@typo3/backend/notification.js";import"@typo3/core/ajax/ajax-request.js";import"@typo3/backend/modal.js";import"jquery";import"@typo3/backend/icons.js";class t extends Error{static create(){return new t("No site identifier found.")}}class a{warmupPageCache(t,i,r){if("pages"===t){const t=a.determineLanguage(r),n={};n[i]=[t],(new e).warmupCache({},n)}}warmupSiteCache(t,i,r){if("pages"===t){const t=a.determineLanguage(r),i={};i[a.determineSiteIdentifier(r)]=[t],(new e).warmupCache(i,{})}}static determineLanguage(e){return"languageId"in e&&"string"==typeof e.languageId?parseInt(e.languageId):null}static determineSiteIdentifier(e){if(!("siteIdentifier"in e)||"string"!=typeof e.siteIdentifier)throw t.create();return e.siteIdentifier}}var i=new a;export{a as ContextMenuAction,i as default};
