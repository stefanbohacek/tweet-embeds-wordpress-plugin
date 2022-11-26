const getTweetId = (url) => url.match(/status\/(\d+)/g)[0].replace('status/', '');

export { getTweetId };
