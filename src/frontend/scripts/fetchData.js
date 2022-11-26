const fetchData = (data, cb, done) => {
  done = done || function(){ /* noop */ }

  fetch(window.ftf_aet.ajax_url, {
      method: 'POST',
      credentials: 'same-origin',
      headers: {
          'Content-Type': 'application/x-www-form-urlencoded',
          'Cache-Control': 'no-cache',
      },
      body: new URLSearchParams(data) })
      .then((response) => response.json())
      .then((response) => {
          // console.log('response', response);
          cb(response);
      })
      .catch((error) => {
          console.error('tembeds_error', error);
      })
      .then(done);  
};

export { fetchData };
