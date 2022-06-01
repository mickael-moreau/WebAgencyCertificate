/*!
Copyright Monwoo 2022, build by Miguel Monwoo, service@monwoo.com
*/
console.log("Starting Monwoo Widget BrowserProxy js processings");window.onload=function(){var body=document.getElementsByTagName("body")[0];body.insertAdjacentHTML("beforeend",'<button onclick="mwBpGoTopFunction()" id="goTopBtn" title="Go to top">⬆</button>');var goTopBtn=document.getElementById("goTopBtn");window.addEventListener("scroll",event=>{if(document.body.scrollTop>20||document.documentElement.scrollTop>20){goTopBtn.style.display="block"}else{goTopBtn.style.display="none"}})};function mwBpGoTopFunction(){document.body.scrollTop=0;document.documentElement.scrollTop=0}
