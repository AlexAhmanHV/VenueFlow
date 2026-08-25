(function () {
    var script = document.currentScript;
    var slug = script.getAttribute('data-slug');
    var origin = new URL(script.src).origin;

    var iframe = document.createElement('iframe');
    iframe.src = origin + '/r/' + slug + '/book?embed=1';
    iframe.style.cssText = 'width:100%;border:0;display:block;min-height:400px';
    iframe.setAttribute('scrolling', 'no');
    script.insertAdjacentElement('afterend', iframe);

    window.addEventListener('message', function (event) {
        if (event.origin !== origin) return;
        if (event.data && typeof event.data.venueflowEmbedHeight === 'number') {
            iframe.style.height = event.data.venueflowEmbedHeight + 'px';
        }
    });
})();
