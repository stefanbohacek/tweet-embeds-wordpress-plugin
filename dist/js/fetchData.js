var fetchData=function(data,cb,done){done=done||function(){/* noop */},fetch(window.ftf_aet.ajax_url,{method:"POST",credentials:"same-origin",headers:{"Content-Type":"application/x-www-form-urlencoded","Cache-Control":"no-cache"},body:new URLSearchParams(data)}).then(function(response){return response.json()}).then(function(response){// console.log('response', response);
cb(response)}).catch(function(error){console.error("tembeds_error",error)}).then(done)};export{fetchData};
//# sourceMappingURL=fetchData.js.map
